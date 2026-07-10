<?php

namespace App\Notifications;

use App\Channels\WhatsApp\WhatsAppMessage;
use App\Enums\PreAlertStatus;
use App\Models\PreAlert;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PreAlertStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PreAlert $preAlert,
        public ?PreAlertStatus $oldStatus,
        public PreAlertStatus $newStatus,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (app(TenantConfig::class)->hasWhatsAppApi()) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Pre-alert update — '.$this->preAlert->merchant_name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your pre-alert for '.$this->preAlert->merchant_name.' has been updated.')
            ->line('New status: '.$this->newStatus->label());

        if ($this->oldStatus) {
            $mail->line('Previous status: '.$this->oldStatus->label());
        }

        return $mail
            ->action('View pre-alert', url('/portal/pre-alerts/'.$this->preAlert->id))
            ->line('Thank you for shipping with '.app(TenantConfig::class)->name().'.');
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::template(
            (string) config('services.whatsapp.templates.package_status_changed', 'package_status_update'),
        )->bodyParams([
            $notifiable->name,
            $this->preAlert->merchant_name,
            $this->newStatus->label(),
        ]);
    }
}
