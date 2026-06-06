<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\InvoiceFeed\InvoiceFeedBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceFeedWebhookController extends Controller
{
    public function __invoke(Request $request, InvoiceFeedBillingService $billing): JsonResponse
    {
        $rawBody = $request->getContent();

        if (! $this->verifySignature($request, $rawBody)) {
            Log::warning('InvoiceFeed webhook rejected due to invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            Log::warning('InvoiceFeed webhook rejected due to invalid JSON payload');

            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        if (! $this->isInvoicePaidEvent($request, $payload)) {
            Log::warning('InvoiceFeed webhook rejected due to unsupported event', [
                'header_event' => $request->header('X-INVOICEFEED-EVENT'),
                'payload_event' => data_get($payload, 'event'),
                'payload_status' => data_get($payload, 'status'),
            ]);

            return response()->json(['message' => 'Unsupported event.'], 422);
        }

        $package = $billing->handleInvoicePaidWebhook($payload);

        if (! $package) {
            Log::warning('InvoiceFeed webhook could not match a package', [
                'external_reference' => data_get($payload, 'external_reference'),
                'invoice_id' => data_get($payload, 'invoice_id'),
            ]);

            return response()->json(['message' => 'Package not found.'], 404);
        }

        Log::info('InvoiceFeed webhook processed successfully', [
            'package_id' => $package->id,
            'external_reference' => data_get($payload, 'external_reference'),
            'invoice_id' => data_get($payload, 'invoice_id'),
        ]);

        return response()->json([
            'message' => 'Webhook processed.',
            'package_id' => $package->id,
        ]);
    }

    private function verifySignature(Request $request, string $rawBody): bool
    {
        $secret = config('invoicefeed.webhook_secret');

        if (! filled($secret)) {
            return false;
        }

        $signature = $request->header('X-INVOICEFEED-SIGNATURE');

        if (filled($signature)) {
            $expected = hash_hmac('sha256', $rawBody, (string) $secret);

            return hash_equals($expected, (string) $signature);
        }

        $legacySecret = $request->header('X-INVOICEFEED-SECRET');

        if (filled($legacySecret)) {
            return hash_equals((string) $secret, (string) $legacySecret);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isInvoicePaidEvent(Request $request, array $payload): bool
    {
        if ($this->usesLegacySecretAuth($request)) {
            return strtolower((string) data_get($payload, 'status', '')) === 'paid'
                || data_get($payload, 'event') === 'invoice.paid';
        }

        return $request->header('X-INVOICEFEED-EVENT') === 'invoice.paid'
            && data_get($payload, 'event') === 'invoice.paid';
    }

    private function usesLegacySecretAuth(Request $request): bool
    {
        return filled($request->header('X-INVOICEFEED-SECRET'))
            && ! filled($request->header('X-INVOICEFEED-SIGNATURE'));
    }
}
