<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\CustomerWarehouseAddress;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(public User $user) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->user->loadMissing('tenant');
        $tenant = $this->user->tenant;
        $tenantConfig = app(TenantConfig::class);
        $tenantName = $tenant?->name ?? $tenantConfig->name();
        $reference = $this->user->customerReference();
        $dashboardUrl = $tenant ? $tenant->url('/dashboard') : url('/dashboard');
        $shippingAddressUrl = $tenant ? $tenant->url('/portal/shipping-address') : url('/portal/shipping-address');
        $warehouse = CustomerWarehouseAddress::forUser($this->user);

        $mail = (new MailMessage)
            ->subject("Welcome to {$tenantName}!")
            ->greeting('Hello '.$this->user->name.',')
            ->line("Welcome to {$tenantName} — we're glad to have you on board.");

        if ($reference) {
            $mail->line("Your customer reference number is **{$reference}**. Use it whenever you contact us or fill out a pre-alert.");
        }

        if ($warehouse) {
            $mail->line('Use this US shipping address whenever you shop online — packages sent here are matched to your account automatically:');

            foreach (explode("\n", (string) $warehouse['full_address']) as $addressLine) {
                $mail->line('**'.$addressLine.'**');
            }
        }

        return $mail
            ->line('Once you have a tracking number, submit a pre-alert from your dashboard so we know to expect your package.')
            ->action('Go to your dashboard', $dashboardUrl)
            ->line("Need your shipping address again later? You'll always find it at ".$shippingAddressUrl)
            ->line("Thanks for shipping with {$tenantName}.");
    }
}
