<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-scoped reporting queries.
 *
 * All queries run through Eloquent models that carry the BelongsToTenant
 * scope, so results are automatically filtered to the active tenant.
 */
class ReportService
{
    /**
     * Revenue collected (paid packages) grouped by calendar month for the
     * past $months months (oldest → newest).
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function revenueByMonth(int $months = 12): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $start->format('M Y');
            $data[] = (float) Package::query()
                ->where('payment_status', PaymentStatus::Paid)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount_due');
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Package count grouped by calendar month for the past $months months.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function packageVolumeByMonth(int $months = 12): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $start->format('M Y');
            $data[] = Package::query()
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Total outstanding amount across all unpaid/pending packages.
     */
    public function unpaidTotal(): float
    {
        return (float) Package::query()
            ->outstandingPayment()
            ->sum('amount_due');
    }

    /**
     * Count of packages with outstanding payment.
     */
    public function unpaidCount(): int
    {
        return Package::query()->outstandingPayment()->count();
    }

    /**
     * Top $limit customers by total revenue paid, including their package count.
     *
     * @return list<array{customer_id: int, name: string, email: string, total_paid: float, package_count: int}>
     */
    public function topCustomers(int $limit = 10): array
    {
        return Package::query()
            ->select(
                'user_id',
                DB::raw('SUM(amount_due) as total_paid'),
                DB::raw('COUNT(*) as package_count'),
            )
            ->where('payment_status', PaymentStatus::Paid)
            ->groupBy('user_id')
            ->orderByDesc('total_paid')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get()
            ->map(fn (Package $pkg) => [
                'customer_id' => $pkg->user_id,
                'name' => $pkg->user?->name ?? '—',
                'email' => $pkg->user?->email ?? '—',
                'total_paid' => (float) $pkg->total_paid,
                'package_count' => (int) $pkg->package_count,
            ])
            ->all();
    }

    /**
     * Flat rows suitable for CSV export — one row per month with revenue and volume.
     *
     * @return list<array{month: string, revenue: float, packages: int}>
     */
    public function monthlyCsvRows(int $months = 12): array
    {
        $revenue = $this->revenueByMonth($months);
        $volume = $this->packageVolumeByMonth($months);

        $rows = [];
        foreach ($revenue['labels'] as $i => $label) {
            $rows[] = [
                'month' => $label,
                'revenue' => $revenue['data'][$i],
                'packages' => $volume['data'][$i],
            ];
        }

        return $rows;
    }
}
