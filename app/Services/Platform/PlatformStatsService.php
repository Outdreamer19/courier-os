<?php

namespace App\Services\Platform;

use App\Models\Tenant;
use Laravel\Cashier\Subscription;

/**
 * Aggregates platform-wide metrics for the CourierOS owner: tenant counts,
 * monthly recurring revenue, and signup trends. Runs in central context so
 * no tenant scope applies.
 */
class PlatformStatsService
{
    /**
     * The monthly subscription price in whole currency units.
     */
    private const MONTHLY_PRICE = 79;

    /**
     * @return array<string, int|float>
     */
    public function overview(): array
    {
        $total = Tenant::query()->count();
        $active = Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count();
        $suspended = Tenant::query()->where('status', Tenant::STATUS_SUSPENDED)->count();
        $pending = Tenant::query()->where('status', Tenant::STATUS_PENDING)->count();

        $activeSubscriptions = Subscription::query()
            ->where('stripe_status', 'active')
            ->count();

        return [
            'total_tenants' => $total,
            'active_tenants' => $active,
            'suspended_tenants' => $suspended,
            'pending_tenants' => $pending,
            'active_subscriptions' => $activeSubscriptions,
            'mrr' => $activeSubscriptions * self::MONTHLY_PRICE,
        ];
    }

    /**
     * Tenant signups grouped by month for the last 6 months.
     *
     * @return list<array{month: string, count: int}>
     */
    public function signupsByMonth(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = Tenant::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($tenant) => $tenant->created_at->format('Y-m'))
            ->map->count();

        $series = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i)->format('Y-m');
            $series[] = ['month' => $month, 'count' => (int) ($rows[$month] ?? 0)];
        }

        return $series;
    }
}
