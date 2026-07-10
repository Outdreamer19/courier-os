<?php

namespace App\Channels\WhatsApp;

use App\Support\Tenancy\TenantConfig;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Laravel notification channel for the Meta WhatsApp Cloud API.
 *
 * Register via:
 *   Notification::extend('whatsapp', fn ($app) => $app->make(WhatsAppChannel::class));
 *
 * The notifiable must implement routeNotificationForWhatsApp() returning a
 * phone number string (or null to skip delivery silently).
 *
 * The notification must implement toWhatsApp(object $notifiable): WhatsAppMessage.
 */
class WhatsAppChannel
{
    public function __construct(private readonly TenantConfig $tenantConfig) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->tenantConfig->hasWhatsAppApi()) {
            return;
        }

        if (! method_exists($notification, 'toWhatsApp')) {
            Log::warning('WhatsAppChannel: notification missing toWhatsApp()', [
                'notification' => $notification::class,
            ]);

            return;
        }

        /** @var string|null $phone */
        $phone = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (! filled($phone)) {
            return;
        }

        /** @var WhatsAppMessage $message */
        $message = $notification->toWhatsApp($notifiable);

        $client = new WhatsAppClient(
            apiToken: (string) $this->tenantConfig->whatsappApiToken(),
            phoneNumberId: (string) $this->tenantConfig->whatsappPhoneNumberId(),
            apiVersion: (string) config('services.whatsapp.api_version', 'v19.0'),
        );

        $client->send($message, $phone);
    }
}
