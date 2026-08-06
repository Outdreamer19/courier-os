<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformSupport\StorePlatformSupportMessageRequest;
use App\Http\Requests\PlatformSupport\StorePlatformSupportThreadRequest;
use App\Http\Requests\PlatformSupport\UpdatePlatformSupportThreadRequest;
use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use App\Models\Tenant;
use App\Services\Platform\PlatformSupportService;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlatformSupportController extends Controller
{
    public function __construct(private PlatformSupportService $support) {}

    public function index(): Response
    {
        $user = request()->user();
        $tenantId = $this->currentTenantId();

        $threads = $this->support->threadsForTenant($tenantId)
            ->paginate(20)
            ->through(fn (PlatformSupportThread $thread) => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'status' => $thread->status,
                'messages_count' => $thread->messages_count ?? 0,
                'last_message_at' => $thread->last_message_at?->toIso8601String(),
                'last_message_human' => $thread->last_message_at?->diffForHumans(),
                'preview' => optional($thread->messages()->latest('id')->first(), fn ($m) => str($m->body)->limit(100)->toString()),
                'unread' => $this->support->isUnreadFor($thread, $user),
            ]);

        return Inertia::render('admin/platform-support/Index', [
            'threads' => $threads,
        ]);
    }

    public function show(PlatformSupportThread $thread): Response
    {
        $thread = $this->scopedThread($thread);
        $user = request()->user();
        $this->support->markRead($thread, $user);

        $thread->load([
            'messages' => fn ($q) => $q->with('user:id,name,email,role')->orderBy('created_at'),
        ]);

        return Inertia::render('admin/platform-support/Show', [
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'status' => $thread->status,
                'last_message_at' => $thread->last_message_at?->toIso8601String(),
                'closed_at' => $thread->closed_at?->toIso8601String(),
                'messages' => $thread->messages->map(fn (PlatformSupportMessage $message) => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'author_side' => $message->author_side,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'created_at_human' => $message->created_at?->diffForHumans(),
                    'user' => [
                        'id' => $message->user?->id,
                        'name' => $message->user?->name,
                        'email' => $message->user?->email,
                    ],
                ]),
            ],
        ]);
    }

    public function store(StorePlatformSupportThreadRequest $request): RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail($this->currentTenantId());
        $data = $request->validated();

        $thread = $this->support->createThread(
            $tenant,
            $request->user(),
            $data['subject'],
            $data['body'],
            PlatformSupportMessage::SIDE_TENANT,
        );

        return redirect()
            ->route('admin.platform-support.show', $thread)
            ->with('success', 'Message sent to CourierOS.');
    }

    public function storeMessage(
        StorePlatformSupportMessageRequest $request,
        PlatformSupportThread $thread,
    ): RedirectResponse {
        $thread = $this->scopedThread($thread);

        $this->support->reply(
            $thread,
            $request->user(),
            $request->validated('body'),
            PlatformSupportMessage::SIDE_TENANT,
        );

        return redirect()
            ->route('admin.platform-support.show', $thread)
            ->with('success', 'Reply sent.');
    }

    public function update(
        UpdatePlatformSupportThreadRequest $request,
        PlatformSupportThread $thread,
    ): RedirectResponse {
        $thread = $this->scopedThread($thread);
        $status = $request->validated('status');

        if ($status === PlatformSupportThread::STATUS_CLOSED) {
            $this->support->close($thread, $request->user());
        } else {
            $this->support->reopen($thread);
        }

        return redirect()
            ->route('admin.platform-support.show', $thread)
            ->with('success', $status === PlatformSupportThread::STATUS_CLOSED
                ? 'Conversation closed.'
                : 'Conversation reopened.');
    }

    private function scopedThread(PlatformSupportThread $thread): PlatformSupportThread
    {
        if ((int) $thread->tenant_id !== $this->currentTenantId()) {
            throw new NotFoundHttpException;
        }

        return $thread;
    }

    private function currentTenantId(): int
    {
        $tenant = app(TenantManager::class)->current();

        if (! $tenant) {
            throw new NotFoundHttpException;
        }

        return (int) $tenant->id;
    }
}
