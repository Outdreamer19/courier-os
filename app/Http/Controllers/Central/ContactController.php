<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlatformLeadRequest;
use App\Models\PlatformLead;
use App\Models\User;
use App\Notifications\PlatformLeadReceived;
use App\Support\Demo\DemoMode;
use App\Support\Leads\LeadForm;
use App\Support\Leads\LeadQualifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * The CourierOS contact page: a three step brief that tells Shane where a
 * prospect is, what they need and how to reach them, so the first reply can
 * already be the right one.
 *
 * GET /contact is shared with tenant sites, so the tenant ContactController
 * hands off to show() on the central domain. The POST has its own URL in
 * routes/central.php behind the `central` guard.
 */
class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('central/Contact', [
            'options' => LeadForm::forFrontend(),
            'sales' => [
                'whatsapp' => (string) config('courieros.marketing.sales_whatsapp') ?: null,
            ],
            'liveDemo' => DemoMode::url() ? [
                'url' => DemoMode::url(),
                'name' => DemoMode::COURIER_NAME,
            ] : null,
        ])->withViewData([
            'meta' => [
                'title' => 'Contact us | CourierOS',
                'description' => 'Tell us about your courier business in three quick steps and we will come back with the right setup, pricing and next steps.',
            ],
        ]);
    }

    public function store(StorePlatformLeadRequest $request, LeadQualifier $qualifier): RedirectResponse
    {
        $data = $request->validated();

        // Honeypot tripped: look successful, keep nothing.
        if (filled($data['website'] ?? null)) {
            return $this->thanks();
        }

        $data = Arr::except($data, ['website']);
        $data['needs'] = array_values(array_unique($data['needs']));

        $lead = PlatformLead::create([
            ...$data,
            ...$qualifier->assess($data),
            'status' => PlatformLead::STATUS_NEW,
        ]);

        $this->notifyOwners($lead);

        return $this->thanks();
    }

    private function thanks(): RedirectResponse
    {
        return redirect()
            ->to('/contact')
            ->with('success', 'Thanks, your brief is with us. We will be in touch shortly.');
    }

    /**
     * The lead is already saved, so a mail failure must not turn into an
     * error page for the person who just filled the form in.
     */
    private function notifyOwners(PlatformLead $lead): void
    {
        try {
            $owners = User::query()
                ->withoutGlobalScope('tenant')
                ->where('role', User::ROLE_PLATFORM_OWNER)
                ->whereNull('tenant_id')
                ->get();

            if ($owners->isNotEmpty()) {
                Notification::send($owners, new PlatformLeadReceived($lead));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
