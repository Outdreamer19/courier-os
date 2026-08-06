<?php

namespace App\Notifications;

use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformSupportMessageFromTenant extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlatformSupportThread $thread,
        public PlatformSupportMessage $message,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $thread = $this->thread->loadMissing('tenant');
        $tenantName = $thread->tenant?->name ?? 'Tenant';
        $excerpt = str($this->message->body)->limit(140)->toString();
        $url = url('/platform/support/'.$thread->id);

        return (new MailMessage)
            ->subject("Support: {$tenantName} — {$thread->subject}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$tenantName} sent a support message.")
            ->line('Subject: '.$thread->subject)
            ->line($excerpt)
            ->action('View conversation', $url);
    }
}
