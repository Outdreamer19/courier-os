<?php

namespace App\Notifications;

use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformSupportMessageFromPlatform extends Notification implements ShouldQueue
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
        $excerpt = str($this->message->body)->limit(140)->toString();
        $url = $thread->tenant
            ? $thread->tenant->url('/admin/platform-support/'.$thread->id)
            : url('/admin/platform-support/'.$thread->id);

        return (new MailMessage)
            ->subject('CourierOS replied: '.$thread->subject)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('CourierOS platform support replied to your conversation.')
            ->line('Subject: '.$thread->subject)
            ->line($excerpt)
            ->action('View conversation', $url);
    }
}
