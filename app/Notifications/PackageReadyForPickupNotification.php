<?php

namespace App\Notifications;

use App\Channels\WhatsApp\WhatsAppMessage;
use App\Models\Package;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackageReadyForPickupNotification extends Notification
{
    use Queueable;

    public function __construct(public Package $package) {}

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
        $reference = $this->package->package_reference;
        $tenantConfig = app(TenantConfig::class);
        $currency = $tenantConfig->currency();
        $amount = number_format((float) $this->package->amount_due, 2);
        $tenantName = $tenantConfig->name();

        return (new MailMessage)
            ->subject("Package {$reference} is ready for pickup")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Great news — package **{$reference}** is ready for pickup.")
            ->line("Amount due: {$currency} \${$amount} (pay online when available, or in person at pickup).")
            ->line("Please bring a valid ID and your {$tenantName} customer reference.")
            ->action('View package details', url('/portal/packages/'.$this->package->id))
            ->line("Thank you for shipping with {$tenantName}.");
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $currency = app(TenantConfig::class)->currency();
        $amount = number_format((float) $this->package->amount_due, 2);

        return WhatsAppMessage::template(
            (string) config('services.whatsapp.templates.package_ready_for_pickup', 'package_ready_for_pickup'),
        )->bodyParams([
            $notifiable->name,
            $this->package->package_reference,
            "{$currency} {$amount}",
        ]);
    }
}
