<?php

namespace App\Services\Platform;

use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use App\Models\PlatformSupportThreadRead;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PlatformSupportMessageFromPlatform;
use App\Notifications\PlatformSupportMessageFromTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PlatformSupportService
{
    /**
     * @return Builder<PlatformSupportThread>
     */
    public function threadsForTenant(int $tenantId): Builder
    {
        return PlatformSupportThread::query()
            ->where('tenant_id', $tenantId)
            ->with(['tenant:id,name,subdomain', 'createdBy:id,name,email'])
            ->withCount('messages')
            ->latest('last_message_at');
    }

    /**
     * @return Builder<PlatformSupportThread>
     */
    public function threadsForPlatform(): Builder
    {
        return PlatformSupportThread::query()
            ->with(['tenant:id,name,subdomain', 'createdBy:id,name,email'])
            ->withCount('messages')
            ->latest('last_message_at');
    }

    public function createThread(
        Tenant $tenant,
        User $author,
        string $subject,
        string $body,
        string $authorSide,
    ): PlatformSupportThread {
        return DB::transaction(function () use ($tenant, $author, $subject, $body, $authorSide) {
            $thread = PlatformSupportThread::query()->create([
                'tenant_id' => $tenant->id,
                'subject' => $subject,
                'status' => PlatformSupportThread::STATUS_OPEN,
                'created_by_user_id' => $author->id,
                'last_message_at' => now(),
            ]);

            $message = $this->storeMessage($thread, $author, $body, $authorSide);
            $this->markRead($thread, $author);
            $this->notifyRecipients($thread, $message, $author);

            return $thread->fresh(['tenant', 'messages.user', 'createdBy']) ?? $thread;
        });
    }

    public function reply(
        PlatformSupportThread $thread,
        User $author,
        string $body,
        string $authorSide,
    ): PlatformSupportMessage {
        return DB::transaction(function () use ($thread, $author, $body, $authorSide) {
            if ($thread->isClosed()) {
                $thread->forceFill([
                    'status' => PlatformSupportThread::STATUS_OPEN,
                    'closed_at' => null,
                    'closed_by_user_id' => null,
                ])->save();
            }

            $message = $this->storeMessage($thread, $author, $body, $authorSide);
            $thread->forceFill(['last_message_at' => now()])->save();
            $this->markRead($thread, $author);
            $this->notifyRecipients($thread, $message, $author);

            return $message;
        });
    }

    public function close(PlatformSupportThread $thread, User $actor): PlatformSupportThread
    {
        $thread->forceFill([
            'status' => PlatformSupportThread::STATUS_CLOSED,
            'closed_at' => now(),
            'closed_by_user_id' => $actor->id,
        ])->save();

        return $thread;
    }

    public function reopen(PlatformSupportThread $thread): PlatformSupportThread
    {
        $thread->forceFill([
            'status' => PlatformSupportThread::STATUS_OPEN,
            'closed_at' => null,
            'closed_by_user_id' => null,
        ])->save();

        return $thread;
    }

    public function markRead(PlatformSupportThread $thread, User $user): void
    {
        $latestId = (int) ($thread->messages()->max('id') ?? 0);

        PlatformSupportThreadRead::query()->updateOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id' => $user->id,
            ],
            [
                'last_read_at' => now(),
                'last_read_message_id' => $latestId,
            ],
        );
    }

    public function unreadCountFor(User $user): int
    {
        if ($user->isPlatformOwner()) {
            return $this->countUnread(
                PlatformSupportThread::query(),
                $user,
                PlatformSupportMessage::SIDE_TENANT,
            );
        }

        if (! $user->tenant_id || ! in_array($user->role, [User::ROLE_OWNER, User::ROLE_ADMIN], true)) {
            return 0;
        }

        return $this->countUnread(
            PlatformSupportThread::query()->where('tenant_id', $user->tenant_id),
            $user,
            PlatformSupportMessage::SIDE_PLATFORM,
        );
    }

    public function isUnreadFor(PlatformSupportThread $thread, User $user): bool
    {
        $otherSide = $user->isPlatformOwner()
            ? PlatformSupportMessage::SIDE_TENANT
            : PlatformSupportMessage::SIDE_PLATFORM;

        $lastReadMessageId = PlatformSupportThreadRead::query()
            ->where('thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->value('last_read_message_id');

        $query = PlatformSupportMessage::query()
            ->where('thread_id', $thread->id)
            ->where('author_side', $otherSide);

        if ($lastReadMessageId !== null) {
            $query->where('id', '>', (int) $lastReadMessageId);
        }

        return $query->exists();
    }

    /**
     * @param  Builder<PlatformSupportThread>  $threads
     */
    private function countUnread(Builder $threads, User $user, string $otherSide): int
    {
        $threadIds = (clone $threads)->pluck('id');

        if ($threadIds->isEmpty()) {
            return 0;
        }

        $reads = PlatformSupportThreadRead::query()
            ->where('user_id', $user->id)
            ->whereIn('thread_id', $threadIds)
            ->pluck('last_read_message_id', 'thread_id');

        $count = 0;

        foreach ($threadIds as $threadId) {
            $query = PlatformSupportMessage::query()
                ->where('thread_id', $threadId)
                ->where('author_side', $otherSide);

            $lastReadMessageId = $reads[$threadId] ?? null;

            if ($lastReadMessageId !== null) {
                $query->where('id', '>', (int) $lastReadMessageId);
            }

            if ($query->exists()) {
                $count++;
            }
        }

        return $count;
    }

    private function storeMessage(
        PlatformSupportThread $thread,
        User $author,
        string $body,
        string $authorSide,
    ): PlatformSupportMessage {
        return PlatformSupportMessage::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $author->id,
            'author_side' => $authorSide,
            'body' => $body,
        ]);
    }

    private function notifyRecipients(
        PlatformSupportThread $thread,
        PlatformSupportMessage $message,
        User $author,
    ): void {
        if ($message->author_side === PlatformSupportMessage::SIDE_TENANT) {
            $owners = User::query()
                ->withoutGlobalScope('tenant')
                ->where('role', User::ROLE_PLATFORM_OWNER)
                ->whereNull('tenant_id')
                ->get();

            if ($owners->isNotEmpty()) {
                Notification::send($owners, new PlatformSupportMessageFromTenant($thread, $message));
            }

            return;
        }

        $recipients = $this->tenantRecipients($thread)
            ->reject(fn (User $user) => $user->is($author))
            ->values();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new PlatformSupportMessageFromPlatform($thread, $message));
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function tenantRecipients(PlatformSupportThread $thread): Collection
    {
        $participantIds = PlatformSupportMessage::query()
            ->where('thread_id', $thread->id)
            ->where('author_side', PlatformSupportMessage::SIDE_TENANT)
            ->pluck('user_id')
            ->unique()
            ->all();

        $participants = User::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $thread->tenant_id)
            ->whereIn('role', [User::ROLE_OWNER, User::ROLE_ADMIN])
            ->whereIn('id', $participantIds)
            ->get();

        if ($participants->isNotEmpty()) {
            return $participants;
        }

        $creator = User::query()
            ->withoutGlobalScope('tenant')
            ->whereKey($thread->created_by_user_id)
            ->where('tenant_id', $thread->tenant_id)
            ->whereIn('role', [User::ROLE_OWNER, User::ROLE_ADMIN])
            ->first();

        if ($creator) {
            return collect([$creator]);
        }

        return User::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $thread->tenant_id)
            ->where('role', User::ROLE_OWNER)
            ->get();
    }
}
