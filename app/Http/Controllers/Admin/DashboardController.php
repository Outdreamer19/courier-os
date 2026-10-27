<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $customers = User::query()->where('role', User::ROLE_CUSTOMER);
        $totalCustomers = (clone $customers)->count();
        $newCustomersThisMonth = (clone $customers)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $preAlertsPending = PreAlert::query()
            ->whereIn('status', [PreAlertStatus::Submitted, PreAlertStatus::UnderReview])
            ->count();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_customers' => $totalCustomers,
                'new_customers_this_month' => $newCustomersThisMonth,
                'total_pre_alerts' => PreAlert::query()->count(),
                'pre_alerts_pending' => $preAlertsPending,
                'total_packages' => Package::query()->count(),
                'packages_ready_for_pickup' => Package::query()
                    ->where('status', PackageStatus::ReadyForPickup)
                    ->count(),
                'unpaid_packages' => Package::query()->outstandingPayment()->count(),
                'new_contact_messages' => ContactMessage::query()
                    ->where('status', ContactMessage::STATUS_NEW)
                    ->count(),
            ],
            'money' => $this->moneySummary(),
            'pipeline' => $this->pipeline(),
            'charts' => [
                'new_users' => $this->newUsersChart(),
                'packages_over_time' => $this->packagesOverTimeChart(),
                'pre_alerts_by_status' => $this->preAlertsByStatusChart(),
            ],
            'recent_contact_messages' => ContactMessage::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'subject', 'status', 'created_at']),
            'recent_pre_alerts' => PreAlert::query()
                ->with('user:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (PreAlert $pa) => [
                    'id' => $pa->id,
                    'merchant_name' => $pa->merchant_name,
                    'status_label' => $pa->status->label(),
                    'customer_name' => $pa->user?->name,
                    'created_at' => $pa->created_at?->toIso8601String(),
                ]),
            'recent_packages' => Package::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'package_reference', 'status', 'payment_status', 'amount_due', 'created_at'])
                ->map(fn (Package $pkg) => [
                    'id' => $pkg->id,
                    'package_reference' => $pkg->package_reference,
                    'status_label' => $pkg->status->label(),
                    'payment_status_label' => $pkg->payment_status->label(),
                    'amount_due' => (float) $pkg->amount_due,
                ]),
        ]);
    }

    /**
     * What is owed now and what came in, month by month.
     *
     * @return array{outstanding: float, collected_this_month: float, collected: array{labels: list<string>, data: list<float>}}
     */
    private function moneySummary(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->endOfMonth();

            $labels[] = $start->format('M');
            $data[] = (float) Package::query()
                ->where('payment_status', PaymentStatus::Paid)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount_due');
        }

        return [
            'outstanding' => (float) Package::query()->outstandingPayment()->sum('amount_due'),
            'collected_this_month' => (float) end($data),
            'collected' => ['labels' => $labels, 'data' => $data],
        ];
    }

    /**
     * Packages grouped by where they physically are on the Florida to
     * Jamaica run, for the journey strip on the dashboard.
     *
     * @return list<array{key: string, label: string, count: int}>
     */
    private function pipeline(): array
    {
        $counts = Package::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $sum = fn (PackageStatus ...$statuses): int => array_sum(array_map(
            fn (PackageStatus $status) => (int) ($counts[$status->value] ?? 0),
            $statuses,
        ));

        return [
            ['key' => 'expected', 'label' => 'Expected', 'count' => $sum(PackageStatus::AwaitingArrival)],
            ['key' => 'warehouse', 'label' => 'At Florida warehouse', 'count' => $sum(PackageStatus::ReceivedAtFloridaWarehouse, PackageStatus::Processing)],
            ['key' => 'transit', 'label' => 'In transit', 'count' => $sum(PackageStatus::InTransitToJamaica)],
            ['key' => 'customs', 'label' => 'Landed and customs', 'count' => $sum(PackageStatus::ArrivedInJamaica, PackageStatus::CustomsProcessing, PackageStatus::OnHold)],
            ['key' => 'ready', 'label' => 'Ready for pickup', 'count' => $sum(PackageStatus::ReadyForPickup)],
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function newUsersChart(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $start->format('M');
            $data[] = User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function packagesOverTimeChart(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $start->format('M');
            $data[] = Package::query()
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function preAlertsByStatusChart(): array
    {
        $counts = PreAlert::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];

        foreach (PreAlertStatus::cases() as $status) {
            $labels[] = $status->label();
            $data[] = (int) ($counts[$status->value] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
