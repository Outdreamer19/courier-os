<?php

namespace App\Http\Controllers\Customer;

use App\Enums\Carrier;
use App\Enums\PreAlertStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePreAlertRequest;
use App\Http\Requests\Customer\UpdatePreAlertRequest;
use App\Models\ActivityLog;
use App\Models\PreAlert;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PreAlertController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PreAlert::class);

        $preAlerts = $request->user()
            ->preAlerts()
            ->latest()
            ->paginate(10)
            ->through(fn (PreAlert $preAlert) => $this->listPayload($preAlert));

        return Inertia::render('customer/pre-alerts/Index', [
            'preAlerts' => $preAlerts,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PreAlert::class);

        return Inertia::render('customer/pre-alerts/Create', [
            'carriers' => Carrier::options(),
        ]);
    }

    public function store(StorePreAlertRequest $request): RedirectResponse
    {
        $this->authorize('create', PreAlert::class);

        $data = $request->safe()->except('invoice');
        $data['user_id'] = $request->user()->id;
        $data['status'] = PreAlertStatus::Submitted;

        if ($request->hasFile('invoice')) {
            $data['invoice_path'] = $this->storeInvoice($request);
        }

        $preAlert = PreAlert::create($data);

        $this->activityLogger->log(
            ActivityLog::ACTION_PRE_ALERT_CREATED,
            "{$request->user()->name} submitted a pre-alert for {$preAlert->merchant_name}.",
            $request->user(),
            $preAlert,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pre-alert submitted successfully.',
        ]);

        return to_route('portal.pre-alerts.show', ['pre_alert' => $preAlert]);
    }

    public function show(Request $request, PreAlert $preAlert): Response
    {
        $this->authorize('view', $preAlert);

        return Inertia::render('customer/pre-alerts/Show', [
            'preAlert' => $this->detailPayload($preAlert),
        ]);
    }

    public function edit(PreAlert $preAlert): Response|RedirectResponse
    {
        $this->authorize('view', $preAlert);

        if (! $preAlert->isEditable()) {
            return redirect()
                ->route('portal.pre-alerts.show', $preAlert)
                ->with('error', 'This pre-alert can no longer be edited.');
        }

        return Inertia::render('customer/pre-alerts/Edit', [
            'preAlert' => $this->detailPayload($preAlert),
            'carriers' => Carrier::options(),
        ]);
    }

    public function update(UpdatePreAlertRequest $request, PreAlert $preAlert): RedirectResponse
    {
        $this->authorize('update', $preAlert);

        $data = $request->safe()->except('invoice');

        if ($request->hasFile('invoice')) {
            if ($preAlert->invoice_path) {
                Storage::disk(config('shipdjm.invoice_uploads.disk'))
                    ->delete($preAlert->invoice_path);
            }

            $data['invoice_path'] = $this->storeInvoice($request);
        }

        $preAlert->update($data);

        $this->activityLogger->log(
            ActivityLog::ACTION_PRE_ALERT_UPDATED,
            "{$request->user()->name} updated pre-alert for {$preAlert->merchant_name}.",
            $request->user(),
            $preAlert,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pre-alert updated.',
        ]);

        return to_route('portal.pre-alerts.show', ['pre_alert' => $preAlert]);
    }

    public function cancel(Request $request, PreAlert $preAlert): RedirectResponse
    {
        $this->authorize('cancel', $preAlert);

        $preAlert->update(['status' => PreAlertStatus::Cancelled]);

        $this->activityLogger->log(
            ActivityLog::ACTION_PRE_ALERT_CANCELLED,
            "{$request->user()->name} cancelled pre-alert for {$preAlert->merchant_name}.",
            $request->user(),
            $preAlert,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pre-alert cancelled.',
        ]);

        return to_route('portal.pre-alerts.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function listPayload(PreAlert $preAlert): array
    {
        return [
            'id' => $preAlert->id,
            'merchant_name' => $preAlert->merchant_name,
            'tracking_number' => $preAlert->tracking_number,
            'status' => $preAlert->status->value,
            'status_label' => $preAlert->status->label(),
            'expected_delivery_date' => $preAlert->expected_delivery_date?->toDateString(),
            'created_at' => $preAlert->created_at?->toIso8601String(),
            'has_invoice' => $preAlert->invoice_path !== null,
            'is_editable' => $preAlert->isEditable(),
            'is_cancellable' => $preAlert->isCancellable(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(PreAlert $preAlert): array
    {
        return [
            ...$this->listPayload($preAlert),
            'order_number' => $preAlert->order_number,
            'carrier' => $preAlert->carrier->value,
            'carrier_label' => $preAlert->carrier->label(),
            'item_description' => $preAlert->item_description,
            'declared_value' => $preAlert->declared_value,
            'customer_notes' => $preAlert->customer_notes,
            'is_editable' => $preAlert->isEditable(),
            'is_cancellable' => $preAlert->isCancellable(),
            'invoice_url' => $preAlert->invoice_path
                ? route('portal.pre-alerts.invoice', $preAlert)
                : null,
        ];
    }

    private function storeInvoice(StorePreAlertRequest|UpdatePreAlertRequest $request): string
    {
        $disk = config('shipdjm.invoice_uploads.disk', 'local');
        $directory = config('shipdjm.invoice_uploads.directory', 'invoices');

        return $request->file('invoice')->store($directory, $disk);
    }
}
