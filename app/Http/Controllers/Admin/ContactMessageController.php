<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactMessageRequest;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();

        $messages = ContactMessage::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ContactMessage $message) => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'subject' => $message->subject,
                'status' => $message->status,
                'status_label' => $this->statusLabel($message->status),
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/contact-messages/Index', [
            'messages' => $messages,
            'filters' => [
                'status' => $status ?: null,
                'search' => $search ?: null,
            ],
            'statuses' => $this->statusOptions(),
            'counts' => [
                'total' => ContactMessage::query()->count(),
                'new' => ContactMessage::query()->where('status', ContactMessage::STATUS_NEW)->count(),
                'read' => ContactMessage::query()->where('status', ContactMessage::STATUS_READ)->count(),
                'resolved' => ContactMessage::query()->where('status', ContactMessage::STATUS_RESOLVED)->count(),
            ],
        ]);
    }

    public function show(ContactMessage $contactMessage): Response
    {
        if ($contactMessage->status === ContactMessage::STATUS_NEW) {
            $contactMessage->update([
                'status' => ContactMessage::STATUS_READ,
                'handled_at' => now(),
            ]);
            $contactMessage->refresh();
        }

        return Inertia::render('admin/contact-messages/Show', [
            'message' => [
                'id' => $contactMessage->id,
                'name' => $contactMessage->name,
                'email' => $contactMessage->email,
                'phone' => $contactMessage->phone,
                'subject' => $contactMessage->subject,
                'body' => $contactMessage->message,
                'status' => $contactMessage->status,
                'status_label' => $this->statusLabel($contactMessage->status),
                'admin_notes' => $contactMessage->admin_notes,
                'created_at' => $contactMessage->created_at?->toIso8601String(),
                'handled_at' => $contactMessage->handled_at?->toIso8601String(),
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function update(
        UpdateContactMessageRequest $request,
        ContactMessage $contactMessage,
    ): RedirectResponse {
        $data = $request->validated();

        if ($data['status'] === ContactMessage::STATUS_RESOLVED) {
            $data['handled_at'] = $contactMessage->handled_at ?? now();
        }

        $contactMessage->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Message updated.']);

        return to_route('admin.contact-messages.show', ['contact_message' => $contactMessage]);
    }

    public function destroy(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()?->hasAdminPermission('delete_records'), 403);

        $this->activityLogger->log(
            ActivityLog::ACTION_RECORD_DELETED,
            "{$request->user()->name} deleted contact message from {$contactMessage->name}.",
            $request->user(),
            $contactMessage,
        );

        $contactMessage->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Message deleted.']);

        return to_route('admin.contact-messages.index');
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            ContactMessage::STATUS_NEW => 'New',
            ContactMessage::STATUS_READ => 'Read',
            ContactMessage::STATUS_RESOLVED => 'Resolved',
        ];
    }

    private function statusLabel(string $status): string
    {
        return $this->statusOptions()[$status] ?? ucfirst($status);
    }
}
