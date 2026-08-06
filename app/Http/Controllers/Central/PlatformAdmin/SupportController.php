<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformSupport\StorePlatformSupportMessageRequest;
use App\Http\Requests\PlatformSupport\StorePlatformSupportThreadRequest;
use App\Http\Requests\PlatformSupport\UpdatePlatformSupportThreadRequest;
use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use App\Models\Tenant;
use App\Services\Platform\PlatformSupportService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __construct(private PlatformSupportService $support) {}

    public function index(): Response
    {
        $user = request()->user();

        $threads = $this->support->threadsForPlatform()
            ->paginate(20)
            ->through(fn (PlatformSupportThread $thread) => $this->threadSummary($thread, $user));

        return Inertia::render('central/admin/support/Index', [
            'threads' => $threads,
            'tenants' => Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name', 'subdomain']),
            'prefillTenantId' => request()->integer('tenant_id') ?: null,
        ]);
    }

    public function show(PlatformSupportThread $thread): Response
    {
        $user = request()->user();
        $this->support->markRead($thread, $user);

        $thread->load([
            'tenant:id,name,subdomain,status',
            'createdBy:id,name,email',
            'messages' => fn ($q) => $q->with('user:id,name,email,role')->orderBy('created_at'),
        ]);

        return Inertia::render('central/admin/support/Show', [
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'status' => $thread->status,
                'last_message_at' => $thread->last_message_at?->toIso8601String(),
                'closed_at' => $thread->closed_at?->toIso8601String(),
                'tenant' => [
                    'id' => $thread->tenant?->id,
                    'name' => $thread->tenant?->name,
                    'subdomain' => $thread->tenant?->subdomain,
                    'status' => $thread->tenant?->status,
                ],
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
        $data = $request->validated();
        $tenant = Tenant::query()->findOrFail($data['tenant_id']);

        $thread = $this->support->createThread(
            $tenant,
            $request->user(),
            $data['subject'],
            $data['body'],
            PlatformSupportMessage::SIDE_PLATFORM,
        );

        return redirect()
            ->route('central.platform.support.show', $thread)
            ->with('success', 'Support message sent.');
    }

    public function storeMessage(
        StorePlatformSupportMessageRequest $request,
        PlatformSupportThread $thread,
    ): RedirectResponse {
        $this->support->reply(
            $thread,
            $request->user(),
            $request->validated('body'),
            PlatformSupportMessage::SIDE_PLATFORM,
        );

        return redirect()
            ->route('central.platform.support.show', $thread)
            ->with('success', 'Reply sent.');
    }

    public function update(
        UpdatePlatformSupportThreadRequest $request,
        PlatformSupportThread $thread,
    ): RedirectResponse {
        $status = $request->validated('status');

        if ($status === PlatformSupportThread::STATUS_CLOSED) {
            $this->support->close($thread, $request->user());
        } else {
            $this->support->reopen($thread);
        }

        return redirect()
            ->route('central.platform.support.show', $thread)
            ->with('success', $status === PlatformSupportThread::STATUS_CLOSED
                ? 'Conversation closed.'
                : 'Conversation reopened.');
    }

    /**
     * @return array<string, mixed>
     */
    private function threadSummary(PlatformSupportThread $thread, $user): array
    {
        $latest = $thread->messages()->latest('id')->first();

        return [
            'id' => $thread->id,
            'subject' => $thread->subject,
            'status' => $thread->status,
            'messages_count' => $thread->messages_count ?? $thread->messages()->count(),
            'last_message_at' => $thread->last_message_at?->toIso8601String(),
            'last_message_human' => $thread->last_message_at?->diffForHumans(),
            'preview' => $latest ? str($latest->body)->limit(100)->toString() : null,
            'unread' => $this->support->isUnreadFor($thread, $user),
            'tenant' => [
                'id' => $thread->tenant?->id,
                'name' => $thread->tenant?->name,
                'subdomain' => $thread->tenant?->subdomain,
            ],
        ];
    }
}
