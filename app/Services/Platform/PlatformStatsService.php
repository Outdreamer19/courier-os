<?php

namespace App\Services\Platform;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Cashier\Subscription;

/**
 * Aggregates platform-wide metrics for the CourierOS owner: revenue, tenant
 * health, product adoption, and the short list of accounts that need
 * attention today.
 *
 * Every query runs in central context (no tenant bound), where the
 * BelongsToTenant global scope is a no-op — so counts naturally span every
 * tenant. Tenant-scoped models are still queried with an explicit
 * withoutGlobalScope('tenant') so the numbers stay correct even if this
 * service is ever resolved from inside a tenant request.
 */
class PlatformStatsService
{
    /**
     * Headline numbers: what the business earns and how it is trending.
     *
     * @return array<string, int|float|null>
     */
    public function overview(): array
    {
        $monthlyPrice = (float) config('courieros.pricing.monthly', 79);
        $setupFee = (float) config('courieros.pricing.setup', 349);

        $active = Subscription::query()->where('stripe_status', 'active')->count();
        $trialing = Subscription::query()->where('stripe_status', 'trialing')->count();
        $pastDue = Subscription::query()->where('stripe_status', 'past_due')->count();
        $cancelled = Subscription::query()
            ->whereIn('stripe_status', ['canceled', 'incomplete_expired'])
            ->count();

        $mrr = $active * $monthlyPrice;

        $thisMonth = $this->tenantsCreatedBetween(now()->startOfMonth(), now());
        $lastMonth = $this->tenantsCreatedBetween(
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow()->endOfMonth(),
        );

        $totalTenants = Tenant::query()->count();
        $activeTenants = Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count();
        $pendingTenants = Tenant::query()->where('status', Tenant::STATUS_PENDING)->count();

        return [
            'currency' => (string) config('courieros.pricing.currency', 'USD'),
            'monthly_price' => $monthlyPrice,
            'setup_fee' => $setupFee,

            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'arpa' => $active > 0 ? round($mrr / $active, 2) : 0.0,
            'setup_revenue' => ($active + $trialing) * $setupFee,

            'active_subscriptions' => $active,
            'trialing_subscriptions' => $trialing,
            'past_due_subscriptions' => $pastDue,
            'cancelled_subscriptions' => $cancelled,

            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'pending_tenants' => $pendingTenants,
            'suspended_tenants' => Tenant::query()->where('status', Tenant::STATUS_SUSPENDED)->count(),
            'cancelled_tenants' => Tenant::query()->where('status', Tenant::STATUS_CANCELLED)->count(),

            // Signed up but never converted to a paying/active account.
            'conversion_pct' => $totalTenants > 0
                ? (int) round(($activeTenants / $totalTenants) * 100)
                : 0,

            'new_tenants_this_month' => $thisMonth,
            'new_tenants_last_month' => $lastMonth,
            'tenant_growth_pct' => $lastMonth > 0
                ? (int) round((($thisMonth - $lastMonth) / $lastMonth) * 100)
                : null,
        ];
    }

    /**
     * Adoption: how much real work is happening across every tenant. Revenue
     * without throughput is revenue about to churn.
     *
     * @return array<string, int>
     */
    public function usage(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_customers' => $this->scoped(User::query())
                ->where('role', User::ROLE_CUSTOMER)
                ->count(),
            'new_customers_30d' => $this->scoped(User::query())
                ->where('role', User::ROLE_CUSTOMER)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'total_packages' => $this->scoped(Package::query())->count(),
            'packages_30d' => $this->scoped(Package::query())
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'pre_alerts_30d' => $this->scoped(PreAlert::query())
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'open_enquiries' => $this->scoped(ContactMessage::query())
                ->where('status', ContactMessage::STATUS_NEW)
                ->count(),
        ];
    }

    /**
     * Tenant signups grouped by month.
     *
     * @return list<array{month: string, label: string, count: int}>
     */
    public function signupsByMonth(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = Tenant::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Tenant $tenant) => $tenant->created_at->format('Y-m'))
            ->map->count();

        $series = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);

            $series[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'count' => (int) ($rows[$month->format('Y-m')] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Per-tenant health board — the most useful single view for an operator:
     * who is live, who is actually using it, and who has gone quiet.
     *
     * @return list<array<string, mixed>>
     */
    public function tenantHealth(int $limit = 25): array
    {
        $tenants = Tenant::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        if ($tenants->isEmpty()) {
            return [];
        }

        $ids = $tenants->pluck('id')->all();
        $thirtyDaysAgo = now()->subDays(30);

        $customers = $this->countByTenant(
            $this->scoped(User::query())
                ->where('role', User::ROLE_CUSTOMER)
                ->whereIn('tenant_id', $ids)
        );

        $packages = $this->countByTenant(
            $this->scoped(Package::query())->whereIn('tenant_id', $ids)
        );

        $packages30d = $this->countByTenant(
            $this->scoped(Package::query())
                ->whereIn('tenant_id', $ids)
                ->where('created_at', '>=', $thirtyDaysAgo)
        );

        $lastActivity = $this->scoped(ActivityLog::query())
            ->whereIn('tenant_id', $ids)
            ->selectRaw('tenant_id, MAX(created_at) as last_at')
            ->groupBy('tenant_id')
            ->pluck('last_at', 'tenant_id');

        $subscriptions = Subscription::query()
            ->whereIn('tenant_id', $ids)
            ->orderBy('id')
            ->get()
            ->keyBy('tenant_id');

        return $tenants->map(function (Tenant $tenant) use (
            $customers,
            $packages,
            $packages30d,
            $lastActivity,
            $subscriptions,
        ) {
            $raw = $lastActivity[$tenant->id] ?? null;
            $lastAt = $raw ? Date::parse($raw) : null;
            $recent = (int) ($packages30d[$tenant->id] ?? 0);

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'url' => $this->tenantUrl($tenant),
                'status' => $tenant->status,
                'currency' => $tenant->currency,
                'subscription_status' => $this->subscriptionStatus($tenant, $subscriptions->get($tenant->id)),
                'trial_ends_at' => $tenant->trial_ends_at?->toDateString(),
                'customers' => (int) ($customers[$tenant->id] ?? 0),
                'packages' => (int) ($packages[$tenant->id] ?? 0),
                'packages_30d' => $recent,
                'last_activity_human' => $lastAt?->diffForHumans(),
                'joined_at' => $tenant->created_at?->toDateString(),
                'health' => $this->healthLabel($tenant, $recent, $lastAt),
            ];
        })->values()->all();
    }

    /**
     * The short list of things the owner should actually act on today.
     *
     * @return list<array{type: string, severity: string, title: string, detail: string}>
     */
    public function attention(): array
    {
        $items = [];

        foreach (Tenant::query()->where('status', Tenant::STATUS_PENDING)->orderBy('created_at')->get() as $tenant) {
            $items[] = [
                'type' => 'onboarding',
                'severity' => 'warning',
                'title' => "{$tenant->name} hasn't finished checkout",
                'detail' => 'Signed up '.($tenant->created_at?->diffForHumans() ?? 'recently')
                    .' and is still pending. Nudge them, or activate the tenant manually.',
            ];
        }

        $pastDue = Subscription::query()->where('stripe_status', 'past_due')->count();

        if ($pastDue > 0) {
            $items[] = [
                'type' => 'billing',
                'severity' => 'danger',
                'title' => $pastDue.' '.($pastDue === 1 ? 'subscription is' : 'subscriptions are').' past due',
                'detail' => 'Payment failed in Stripe. Chase before the dunning window closes.',
            ];
        }

        $trialsEnding = Tenant::query()
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($trialsEnding as $tenant) {
            $items[] = [
                'type' => 'trial',
                'severity' => 'warning',
                'title' => "{$tenant->name}'s trial ends ".$tenant->trial_ends_at->diffForHumans(),
                'detail' => 'Convert them before access is cut off.',
            ];
        }

        $suspended = Tenant::query()->where('status', Tenant::STATUS_SUSPENDED)->count();

        if ($suspended > 0) {
            $items[] = [
                'type' => 'suspended',
                'severity' => 'danger',
                'title' => $suspended.' '.($suspended === 1 ? 'tenant is' : 'tenants are').' suspended',
                'detail' => 'Their staff and customers are locked out right now.',
            ];
        }

        // A live tenant with no throughput in a month is the classic
        // pre-churn signal: paying, but not using the product.
        $thirtyDaysAgo = now()->subDays(30);

        $quiet = Tenant::query()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->where('created_at', '<', $thirtyDaysAgo)
            ->get();

        foreach ($quiet as $tenant) {
            $recent = $this->scoped(Package::query())
                ->where('tenant_id', $tenant->id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();

            if ($recent === 0) {
                $items[] = [
                    'type' => 'churn_risk',
                    'severity' => 'warning',
                    'title' => "{$tenant->name} has processed nothing in 30 days",
                    'detail' => 'Live account with no package activity — reach out before renewal.',
                ];
            }
        }

        return array_values($items);
    }

    /**
     * Typed against CarbonInterface, not Illuminate\Support\Carbon: the app
     * sets Date::use(CarbonImmutable::class), so now() returns a
     * CarbonImmutable, which is not a subclass of Illuminate's Carbon.
     */
    private function tenantsCreatedBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return Tenant::query()->whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    private function scoped(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Collection<int, int>
     */
    private function countByTenant(Builder $query): Collection
    {
        return $query
            ->selectRaw('tenant_id, COUNT(*) as aggregate')
            ->groupBy('tenant_id')
            ->pluck('aggregate', 'tenant_id');
    }

    private function subscriptionStatus(Tenant $tenant, ?Subscription $subscription): string
    {
        if ($subscription) {
            return (string) $subscription->stripe_status;
        }

        if ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) {
            return 'trialing';
        }

        return 'none';
    }

    private function healthLabel(Tenant $tenant, int $packages30d, ?CarbonInterface $lastActivity): string
    {
        if ($tenant->isSuspended() || $tenant->isCancelled()) {
            return 'offline';
        }

        if ($tenant->isPending()) {
            return 'onboarding';
        }

        if ($packages30d > 0) {
            return 'healthy';
        }

        if ($lastActivity && $lastActivity->gt(now()->subDays(30))) {
            return 'quiet';
        }

        return 'at_risk';
    }

    private function tenantUrl(Tenant $tenant): string
    {
        if ($tenant->custom_domain) {
            return 'https://'.$tenant->custom_domain;
        }

        return 'https://'.$tenant->subdomain.'.'.config('courieros.central_domain');
    }
}
