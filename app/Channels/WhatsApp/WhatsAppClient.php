<?php

namespace App\Channels\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Meta WhatsApp Cloud API.
 *
 * Inject this class (or mock it in tests) rather than calling Http::post() directly.
 * One instance is created per send; callers supply the per-tenant token + phone number ID.
 */
class WhatsAppClient
{
    public function __construct(
        private readonly string $apiToken,
        private readonly string $phoneNumberId,
        private readonly string $apiVersion = 'v19.0',
    ) {}

    /**
     * Send a template message to the given recipient phone number.
     *
     * Returns true on success, false if Meta returns an error (already logged).
     */
    public function send(WhatsAppMessage $message, string $recipientPhone): bool
    {
        try {
            $response = $this->http()
                ->post($this->endpoint(), $message->toPayload($recipientPhone));

            if ($response->failed()) {
                Log::warning('WhatsApp send failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'recipient' => $recipientPhone,
                    'template' => $message->templateName,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp client exception', [
                'message' => $e->getMessage(),
                'recipient' => $recipientPhone,
                'template' => $message->templateName,
            ]);

            return false;
        }
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->apiToken)
            ->acceptJson()
            ->asJson();
    }

    private function endpoint(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
    }
}
