<?php

namespace App\Notifications;

use App\Channels\WhatsApp\WhatsAppMessage;
use App\Enums\PackageStatus;
use App\Models\Package;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackageStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Package $package,
        public ?PackageStatus $oldStatus,
        public PackageStatus $newStatus,
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
        $this->package->loadMissing('tenant');
        $tenant = $this->package->tenant;
        $reference = $this->package->package_reference;
        $tenantName = $tenant?->name ?? app(TenantConfig::class)->name();
        $portalUrl = $tenant
            ? $tenant->url('/portal/packages/'.$this->package->id)
            : url('/portal/packages/'.$this->package->id);

        $mail = (new MailMessage)
            ->subject("Package {$reference} status update")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Your package {$reference} status has been updated.")
            ->line('New status: '.$this->newStatus->label());

        if ($this->oldStatus) {
            $mail->line('Previous status: '.$this->oldStatus->label());
        }

        return $mail
            ->action('View package', $portalUrl)
            ->line('Thank you for shipping with '.$tenantName.'.');
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::template(
            (string) config('services.whatsapp.templates.package_status_changed', 'package_status_update'),
        )->bodyParams([
            $notifiable->name,
            $this->package->package_reference,
            $this->newStatus->label(),
        ]);
    }
}
