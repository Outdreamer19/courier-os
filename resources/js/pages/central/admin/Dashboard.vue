<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PlatformLayout from '@/layouts/PlatformLayout.vue';

type Stats = {
    currency: string;
    monthly_price: number;
    setup_fee: number;
    mrr: number;
    arr: number;
    arpa: number;
    setup_revenue: number;
    active_subscriptions: number;
    trialing_subscriptions: number;
    past_due_subscriptions: number;
    cancelled_subscriptions: number;
    total_tenants: number;
    active_tenants: number;
    pending_tenants: number;
    suspended_tenants: number;
    cancelled_tenants: number;
    conversion_pct: number;
    new_tenants_this_month: number;
    new_tenants_last_month: number;
    tenant_growth_pct: number | null;
};

type Usage = {
    total_customers: number;
    new_customers_30d: number;
    total_packages: number;
    packages_30d: number;
    pre_alerts_30d: number;
    open_enquiries: number;
};

type TenantRow = {
    id: number;
    name: string;
    subdomain: string;
    url: string;
    status: string;
    currency: string;
    subscription_status: string;
    customers: number;
    packages: number;
    packages_30d: number;
    last_activity_human: string | null;
    joined_at: string | null;
    health: string;
};

type Attention = {
    type: string;
    severity: string;
    title: string;
    detail: string;
};

const props = defineProps<{
    stats: Stats;
    usage: Usage;
    signups: { month: string; label: string; count: number }[];
    tenantHealth: TenantRow[];
    attention: Attention[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const greeting = computed(() => {
    const hour = new Date().getHours();
    const part =
        hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';

    const name = user.value?.name?.trim() ?? '';
    const parts = name.split(/\s+/).filter(Boolean);
    let who = 'there';

    if (
        parts.length > 0 &&
        parts.length <= 2 &&
        !/courieros|platform|owner|admin/i.test(parts[0] ?? '')
    ) {
        who = parts[0]!;
    } else if (user.value?.email) {
        const local = user.value.email.split('@')[0] ?? '';
        who = local ? local.charAt(0).toUpperCase() + local.slice(1) : 'there';
    }

    return `${part}, ${who}`;
});

const money = (value: number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.stats.currency || 'USD',
        maximumFractionDigits: 0,
    }).format(value);

const num = (value: number) => new Intl.NumberFormat('en-US').format(value);

const formatDelta = (value: number) =>
    `${value >= 0 ? '+' : ''}${value}%`;

/**
 * Catalyst-style overview stats: label, large value, lime/pink badge + period
 * copy. Layout is a 4-column row (2×2 below xl) with a top hairline each.
 */
const kpis = computed(() => {
    const customerDelta =
        props.usage.total_customers > 0
            ? Math.round(
                  (props.usage.new_customers_30d /
                      Math.max(props.usage.total_customers, 1)) *
                      100,
              )
            : null;

    // Packages: treat 30d share of all-time as a soft activity signal when we
    // don't have a prior-period compare yet.
    const packageDelta =
        props.usage.total_packages > 0
            ? Math.round(
                  (props.usage.packages_30d /
                      Math.max(props.usage.total_packages, 1)) *
                      100,
              )
            : null;

    return [
        {
            label: 'Monthly recurring revenue',
            value: money(props.stats.mrr),
            delta: props.stats.mrr > 0 ? props.stats.tenant_growth_pct : 0,
            period: 'from last month',
        },
        {
            label: 'Active tenants',
            value: num(props.stats.active_tenants),
            delta: props.stats.tenant_growth_pct ?? 0,
            period: 'from last month',
        },
        {
            label: 'Packages processed',
            value: num(props.usage.packages_30d),
            delta: packageDelta,
            period: 'of all-time volume',
        },
        {
            label: 'End customers',
            value: num(props.usage.total_customers),
            delta: customerDelta,
            period: 'new in 30 days',
        },
    ];
});

const healthStyles: Record<
    string,
    { dot: string; badge: string; label: string }
> = {
    healthy: {
        dot: 'bg-emerald-500',
        badge: 'bg-lime-400/20 text-lime-700',
        label: 'Healthy',
    },
    quiet: {
        dot: 'bg-amber-500',
        badge: 'bg-amber-400/20 text-amber-700',
        label: 'Quiet',
    },
    at_risk: {
        dot: 'bg-rose-500',
        badge: 'bg-pink-400/15 text-pink-700',
        label: 'At risk',
    },
    onboarding: {
        dot: 'bg-sky-500',
        badge: 'bg-sky-400/20 text-sky-700',
        label: 'Onboarding',
    },
    offline: {
        dot: 'bg-zinc-400',
        badge: 'bg-zinc-400/15 text-zinc-600',
        label: 'Offline',
    },
};

const health = (key: string) => healthStyles[key] ?? healthStyles.offline;

const billingLabel = (status: string) =>
    status === 'none' ? 'Not billed' : status.replace('_', ' ');
</script>

<template>
    <Head title="Platform overview" />

    <PlatformLayout current="overview" :attention="attention">
        <h1 class="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
            {{ greeting }}
        </h1>

        <div class="mt-8 flex items-end justify-between">
            <h2 class="text-base/7 font-semibold text-zinc-950 sm:text-sm/6">
                Overview
            </h2>
            <div>
                <select
                    name="period"
                    class="relative block w-full appearance-none rounded-lg border border-zinc-950/10 bg-white py-1.5 pr-8 pl-3 text-sm/6 text-zinc-950 shadow-xs focus:border-zinc-950/20 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    aria-label="Period"
                >
                    <option selected>Last 30 days</option>
                    <option>Last week</option>
                    <option>Last two weeks</option>
                    <option>Last month</option>
                    <option>Last quarter</option>
                </select>
            </div>
        </div>

        <!-- Catalyst stats: hairline + label + value + badge row, 4-up -->
        <div class="mt-4 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="kpi in kpis" :key="kpi.label">
                <hr
                    role="presentation"
                    class="w-full border-t border-zinc-950/10"
                />
                <div class="mt-6 text-lg/6 font-medium sm:text-sm/6">
                    {{ kpi.label }}
                </div>
                <div class="mt-3 text-3xl/8 font-semibold sm:text-2xl/8">
                    {{ kpi.value }}
                </div>
                <div class="mt-3 text-sm/6 sm:text-xs/6">
                    <template v-if="kpi.delta !== null && kpi.delta !== undefined">
                        <span
                            class="inline-flex items-center gap-x-1.5 rounded-md px-1.5 py-0.5 text-sm/5 font-medium sm:text-xs/5"
                            :class="
                                kpi.delta >= 0
                                    ? 'bg-lime-400/20 text-lime-700'
                                    : 'bg-pink-400/15 text-pink-700'
                            "
                        >
                            {{ formatDelta(kpi.delta) }}
                        </span>
                        {{ ' ' }}
                        <span class="text-zinc-500">{{ kpi.period }}</span>
                    </template>
                    <span v-else class="text-zinc-500">{{ kpi.period }}</span>
                </div>
            </div>
        </div>

        <section v-if="attention.length" class="mt-10">
            <h2 class="text-base/7 font-semibold text-zinc-950 sm:text-sm/6">
                Needs your attention
            </h2>
            <ul
                class="mt-4 divide-y divide-zinc-950/5 rounded-lg ring-1 ring-zinc-950/5"
            >
                <li
                    v-for="(item, index) in attention"
                    :key="index"
                    class="flex gap-3 px-4 py-3"
                >
                    <span
                        class="mt-1.5 size-2 shrink-0 rounded-full"
                        :class="
                            item.severity === 'danger'
                                ? 'bg-rose-500'
                                : 'bg-amber-500'
                        "
                    />
                    <div>
                        <p class="text-sm/6 font-medium text-zinc-950">
                            {{ item.title }}
                        </p>
                        <p class="text-sm/6 text-zinc-500">{{ item.detail }}</p>
                    </div>
                </li>
            </ul>
        </section>

        <div class="mt-14 flex items-center justify-between gap-4">
            <h2 class="text-base/7 font-semibold text-zinc-950 sm:text-sm/6">
                Tenant health
            </h2>
            <Link
                href="/platform/tenants"
                class="text-sm/6 font-medium text-zinc-950 underline decoration-zinc-950/20 underline-offset-4 hover:decoration-zinc-950"
            >
                View all
            </Link>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm/6 text-zinc-950">
                <thead class="text-zinc-500">
                    <tr class="border-b border-zinc-950/10">
                        <th class="py-3 pr-3 pl-0 font-medium">Business</th>
                        <th class="px-3 py-3 font-medium">Health</th>
                        <th class="px-3 py-3 font-medium">Billing</th>
                        <th class="px-3 py-3 text-right font-medium">
                            Customers
                        </th>
                        <th class="px-3 py-3 text-right font-medium">
                            Packages (30d)
                        </th>
                        <th class="py-3 pr-0 pl-3 font-medium">Last activity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tenant in tenantHealth"
                        :key="tenant.id"
                        class="border-b border-zinc-950/5 last:border-0"
                    >
                        <td class="py-4 pr-3 pl-0">
                            <p class="font-medium">{{ tenant.name }}</p>
                            <p class="text-xs text-zinc-500">
                                {{ tenant.subdomain }} · {{ tenant.currency }}
                            </p>
                        </td>
                        <td class="px-3 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md px-1.5 py-0.5 text-xs/5 font-medium"
                                :class="health(tenant.health).badge"
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="health(tenant.health).dot"
                                />
                                {{ health(tenant.health).label }}
                            </span>
                        </td>
                        <td class="px-3 py-4 capitalize text-zinc-600">
                            {{ billingLabel(tenant.subscription_status) }}
                        </td>
                        <td class="px-3 py-4 text-right tabular-nums">
                            {{ num(tenant.customers) }}
                        </td>
                        <td class="px-3 py-4 text-right tabular-nums">
                            {{ num(tenant.packages_30d) }}
                            <span class="text-xs text-zinc-400"
                                >/ {{ num(tenant.packages) }}</span
                            >
                        </td>
                        <td class="py-4 pr-0 pl-3 text-zinc-500">
                            {{ tenant.last_activity_human ?? 'No activity' }}
                        </td>
                    </tr>
                    <tr v-if="tenantHealth.length === 0">
                        <td
                            colspan="6"
                            class="py-12 text-center text-sm text-zinc-500"
                        >
                            No tenants yet. Your first signup will appear here.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </PlatformLayout>
</template>
