<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PreAlertStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePreAlertRequest;
use App\Models\ActivityLog;
use App\Models\PreAlert;
use App\Notifications\PreAlertStatusChangedNotification;
use App\Services\ActivityLogger;
use App\Support\WhatsappLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreAlertController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PreAlert::class);

        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->value();

        $preAlerts = PreAlert::query()
            ->with(['user:id,name,email', 'user.customerProfile:user_id,customer_reference'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('merchant_name', 'like', "%{$search}%")
                        ->orWhere('tracking_number', 'like', "%{$search}%")
                        ->orWhere('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (PreAlert $preAlert) => [
                'id' => $preAlert->id,
                'merchant_name' => $preAlert->merchant_name,
                'tracking_number' => $preAlert->tracking_number,
                'status' => $preAlert->status->value,
                'status_label' => $preAlert->status->label(),
                'customer_name' => $preAlert->user?->name,
                'customer_reference' => $preAlert->user?->customerProfile?->customer_reference,
                'created_at' => $preAlert->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/pre-alerts/Index', [
            'preAlerts' => $preAlerts,
            'filters' => ['status' => $status ?: null, 'search' => $search],
            'statuses' => collect(PreAlertStatus::cases())->mapWithKeys(
                fn (PreAlertStatus $s) => [$s->value => $s->label()],
            ),
        ]);
    }

    public function show(PreAlert $preAlert): Response
    {
        $this->authorize('view', $preAlert);

        $preAlert->load(['user.customerProfile', 'package']);

        return Inertia::render('admin/pre-alerts/Show', [
            'preAlert' => [
                'id' => $preAlert->id,
                'merchant_name' => $preAlert->merchant_name,
                'order_number' => $preAlert->order_number,
                'tracking_number' => $preAlert->tracking_number,
                'carrier_label' => $preAlert->carrier->label(),
                'status' => $preAlert->status->value,
                'status_label' => $preAlert->status->label(),
                'expected_delivery_date' => $preAlert->expected_delivery_date?->toDateString(),
                'item_description' => $preAlert->item_description,
                'declared_value' => $preAlert->declared_value,
                'customer_notes' => $preAlert->customer_notes,
                'admin_notes' => $preAlert->admin_notes,
                'created_at' => $preAlert->created_at?->toIso8601String(),
                'customer' => [
                    'id' => $preAlert->user?->id,
                    'name' => $preAlert->user?->name,
                    'email' => $preAlert->user?->email,
                    'reference' => $preAlert->user?->customerProfile?->customer_reference,
                    'whatsapp_url' => WhatsappLink::forPhone(
                        $preAlert->user?->customerProfile?->whatsapp_number
                            ?? $preAlert->user?->customerProfile?->phone,
                        "Hi, this is Ship'd JM regarding your pre-alert for ".$preAlert->merchant_name.'.',
                    ),
                ],
                'package' => $preAlert->package ? [
                    'id' => $preAlert->package->id,
                    'package_reference' => $preAlert->package->package_reference,
                ] : null,
                'invoice_url' => $preAlert->invoice_path
                    ? route('admin.pre-alerts.invoice', ['pre_alert' => $preAlert])
                    : null,
            ],
            'statuses' => collect(PreAlertStatus::cases())->mapWithKeys(
                fn (PreAlertStatus $s) => [$s->value => $s->label()],
            ),
        ]);
    }

    public function update(UpdatePreAlertRequest $request, PreAlert $preAlert): RedirectResponse
    {
        $this->authorize('update', $preAlert);

        $oldStatus = $preAlert->status;
        $preAlert->update($request->validated());
        $preAlert->refresh();

        if ($oldStatus !== $preAlert->status && $preAlert->user) {
            $preAlert->user->notify(new PreAlertStatusChangedNotification(
                $preAlert,
                $oldStatus,
                $preAlert->status,
            ));

            $this->activityLogger->log(
                ActivityLog::ACTION_PRE_ALERT_STATUS_CHANGED,
                "{$request->user()->name} changed pre-alert status for {$preAlert->merchant_name} from {$oldStatus->label()} to {$preAlert->status->label()}.",
                $request->user(),
                $preAlert,
                ['old_status' => $oldStatus->value, 'new_status' => $preAlert->status->value],
            );
        } else {
            $this->activityLogger->log(
                ActivityLog::ACTION_PRE_ALERT_UPDATED,
                "{$request->user()->name} updated pre-alert for {$preAlert->merchant_name}.",
                $request->user(),
                $preAlert,
            );
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pre-alert updated.']);

        return to_route('admin.pre-alerts.show', ['pre_alert' => $preAlert]);
    }
}
