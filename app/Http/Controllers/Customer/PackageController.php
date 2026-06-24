<?php

namespace App\Http\Controllers\Customer;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Package::class);

        $packages = $request->user()
            ->packages()
            ->latest()
            ->paginate(10)
            ->through(fn (Package $package) => $this->listPayload($package));

        return Inertia::render('customer/packages/Index', [
            'packages' => $packages,
            'currency' => app(TenantConfig::class)->currency(),
        ]);
    }

    public function show(Request $request, Package $package): Response
    {
        $this->authorize('view', $package);

        return Inertia::render('customer/packages/Show', [
            'package' => $this->detailPayload($package),
            'currency' => app(TenantConfig::class)->currency(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function listPayload(Package $package): array
    {
        return [
            'id' => $package->id,
            'package_reference' => $package->package_reference,
            'merchant_name' => $package->merchant_name,
            'tracking_number' => $package->tracking_number,
            'status' => $package->status->value,
            'status_label' => $package->status->label(),
            'payment_status' => $package->payment_status->value,
            'payment_status_label' => $package->payment_status->label(),
            'amount_due' => (float) $package->amount_due,
            'billing_status' => $package->billing_status?->value,
            'billing_status_label' => $package->billing_status?->label(),
            'invoice_status' => $package->invoicefeed_status,
            'invoice_url' => $package->invoicefeed_public_invoice_url
                ?? $package->invoicefeed_invoice_url,
            'payment_url' => $package->invoicefeed_payment_url,
            'is_paid' => $package->payment_status === PaymentStatus::Paid,
            'can_pay_online' => filled($package->invoicefeed_payment_url)
                && $package->payment_status->isOutstanding(),
            'weight_lbs' => $package->weight_lbs !== null ? (float) $package->weight_lbs : null,
            'updated_at' => $package->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(Package $package): array
    {
        return [
            ...$this->listPayload($package),
            'carrier' => $package->carrier?->value,
            'carrier_label' => $package->carrier?->label(),
            'declared_value' => $package->declared_value !== null ? (float) $package->declared_value : null,
            'payment_method' => $package->payment_method?->value,
            'payment_method_label' => $package->payment_method?->label(),
            'paid_at' => $package->paid_at?->toIso8601String(),
            'customer_visible_notes' => $package->customer_visible_notes,
            'received_at_warehouse_at' => $package->received_at_warehouse_at?->toIso8601String(),
            'shipped_to_jamaica_at' => $package->shipped_to_jamaica_at?->toIso8601String(),
            'arrived_in_jamaica_at' => $package->arrived_in_jamaica_at?->toIso8601String(),
            'ready_for_pickup_at' => $package->ready_for_pickup_at?->toIso8601String(),
            'picked_up_at' => $package->picked_up_at?->toIso8601String(),
            'timeline' => $this->timeline($package),
        ];
    }

    /**
     * @return list<array{label: string, at: string|null, complete: bool}>
     */
    private function timeline(Package $package): array
    {
        $steps = [
            ['label' => 'Received at Florida warehouse', 'at' => $package->received_at_warehouse_at],
            ['label' => 'Shipped to Jamaica', 'at' => $package->shipped_to_jamaica_at],
            ['label' => 'Arrived in Jamaica', 'at' => $package->arrived_in_jamaica_at],
            ['label' => 'Ready for pickup', 'at' => $package->ready_for_pickup_at],
            ['label' => 'Picked up', 'at' => $package->picked_up_at],
        ];

        return collect($steps)->map(fn (array $step) => [
            'label' => $step['label'],
            'at' => $step['at']?->toIso8601String(),
            'complete' => $step['at'] !== null,
        ])->all();
    }
}
