<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    Inbox,
    Mail,
    Package,
    PackageCheck,
    PackagePlus,
    Plane,
    ShieldCheck,
    Store,
    Users,
    Wallet,
    Warehouse,
} from 'lucide-vue-next';
import { computed } from 'vue';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useAnimatedNumber } from '@/composables/useAnimatedNumber';
import { chartStatusColors } from '@/lib/chartColors';
import { useMoney } from '@/lib/money';
import { dashboard as adminDashboard } from '@/routes/admin';
import {
    index as contactIndex,
    show as showContact,
} from '@/routes/admin/contact-messages';
import { index as customersIndex } from '@/routes/admin/customers';
import {
    create as createPackage,
    edit as editPackage,
    index as packagesIndex,
} from '@/routes/admin/packages';
import {
    index as preAlertsIndex,
    show as showPreAlert,
} from '@/routes/admin/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Dashboard', href: adminDashboard() },
        ],
    },
});

const props = defineProps<{
    stats: {
        total_customers: number;
        new_customers_this_month: number;
        total_pre_alerts: number;
        pre_alerts_pending: number;
        total_packages: number;
        packages_ready_for_pickup: number;
        unpaid_packages: number;
        new_contact_messages: number;
    };
    money: {
        outstanding: number;
        collected_this_month: number;
        collected: { labels: string[]; data: number[] };
    };
    pipeline: { key: string; label: string; count: number }[];
    charts: {
        new_users: { labels: string[]; data: number[] };
        packages_over_time: { labels: string[]; data: number[] };
        pre_alerts_by_status: { labels: string[]; data: number[] };
    };
    recent_contact_messages: {
        id: number;
        name: string;
        email: string;
        subject: string;
        status: string;
        created_at: string;
    }[];
    recent_pre_alerts: {
        id: number;
        merchant_name: string;
        status_label: string;
        customer_name: string | null;
        created_at?: string;
    }[];
    recent_packages: {
        id: number;
        package_reference: string;
        status_label: string;
        payment_status_label: string;
        amount_due: number;
    }[];
}>();

const page = usePage();
const { currency, format: formatMoney } = useMoney();

const firstName = computed(
    () => (page.props.auth?.user?.name ?? '').split(' ')[0] || 'there',
);
const brandName = computed(() => page.props.brand?.name ?? 'your courier');

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    return hour < 18 ? 'Good afternoon' : 'Good evening';
});

const today = new Date().toLocaleDateString(undefined, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
});

const compactMoney = (value: number) =>
    `${currency.value} ${new Intl.NumberFormat(undefined, {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(value)}`;

const inFlight = computed(() =>
    props.pipeline
        .filter((stage) => stage.key !== 'expected')
        .reduce((sum, stage) => sum + stage.count, 0),
);

/* ── Packages chart header ─────────────────────────────────────────── */
const packagesSeries = computed(() => props.charts.packages_over_time.data);
const packagesTotal = computed(() =>
    packagesSeries.value.reduce((sum, n) => sum + n, 0),
);
const packagesTotalShown = useAnimatedNumber(() => packagesTotal.value);
const packagesChange = computed(() => {
    const series = packagesSeries.value;
    const previous = series.at(-2) ?? 0;
    const current = series.at(-1) ?? 0;

    if (!previous) {
        return null;
    }

    return Math.round(((current - previous) / previous) * 100);
});

/* ── Pre-alert status legend ───────────────────────────────────────── */
const preAlertLegend = computed(() => {
    const { labels, data } = props.charts.pre_alerts_by_status;
    const total = data.reduce((sum, n) => sum + n, 0) || 1;

    return labels.map((label, index) => ({
        label,
        count: data[index],
        percent: Math.round((data[index] / total) * 100),
        color: chartStatusColors[index % chartStatusColors.length],
    }));
});

/* ── Journey strip ─────────────────────────────────────────────────── */
const stageIcons: Record<string, unknown> = {
    expected: PackagePlus,
    warehouse: Warehouse,
    transit: Plane,
    customs: ShieldCheck,
    ready: Store,
};
</script>

<template>
    <Head title="Admin · Dashboard" />

    <div class="mx-auto flex w-full max-w-[1400px] flex-col gap-7 p-5 lg:p-8">
        <!-- Greeting -->
        <header
            class="dash-rise flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <p
                    class="flex items-center gap-2 text-xs font-semibold tracking-[0.16em] text-muted-foreground uppercase"
                >
                    <span class="relative flex size-2">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"
                        />
                        <span
                            class="relative inline-flex size-2 rounded-full bg-emerald-500"
                        />
                    </span>
                    {{ today }}
                </p>
                <h1
                    class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-[28px]"
                >
                    {{ greeting }}, {{ firstName }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Here is everything moving through {{ brandName }} right now.
                </p>
            </div>

            <div class="flex flex-wrap gap-2.5">
                <Button as-child variant="outline" class="rounded-xl bg-card">
                    <Link :href="preAlertsIndex()">
                        <ClipboardCheck class="size-4" />
                        Review pre-alerts
                    </Link>
                </Button>
                <Button as-child class="rounded-xl shadow-sm">
                    <Link :href="createPackage()">
                        <PackagePlus class="size-4" />
                        Receive a package
                    </Link>
                </Button>
            </div>
        </header>

        <!-- KPIs -->
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard
                title="Customers"
                :value="stats.total_customers"
                :helper="`${stats.new_customers_this_month} joined this month`"
                :icon="Users"
                tone="indigo"
                :href="customersIndex()"
                :spark="charts.new_users.data"
                :delay="60"
            />
            <StatCard
                title="Packages in flight"
                :value="inFlight"
                :helper="`${stats.packages_ready_for_pickup} ready for pickup`"
                :icon="Package"
                tone="brand"
                :href="packagesIndex()"
                :spark="charts.packages_over_time.data"
                :delay="120"
            />
            <StatCard
                title="Collected this month"
                :value="money.collected_this_month"
                :formatter="compactMoney"
                :helper="`${compactMoney(money.outstanding)} still owed`"
                :icon="Wallet"
                tone="emerald"
                :href="packagesIndex({ query: { payment_status: 'unpaid' } })"
                :spark="money.collected.data"
                :delay="180"
            />
            <StatCard
                title="Pre-alerts to review"
                :value="stats.pre_alerts_pending"
                :helper="`${stats.total_pre_alerts} pre-alerts in total`"
                :icon="ClipboardCheck"
                tone="amber"
                :href="preAlertsIndex()"
                :progress="
                    stats.total_pre_alerts
                        ? 1 - stats.pre_alerts_pending / stats.total_pre_alerts
                        : 1
                "
                :progress-label="`${stats.total_pre_alerts - stats.pre_alerts_pending} handled`"
                :delay="240"
            />
        </section>

        <!-- Journey: Florida to pickup -->
        <section
            class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6"
            style="--rise-delay: 300ms"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        Where packages are right now
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        From the Florida warehouse to your pickup counter
                    </p>
                </div>
                <Link
                    :href="packagesIndex()"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline"
                >
                    All packages <ArrowRight class="size-4" />
                </Link>
            </div>

            <ol
                class="relative mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
            >
                <svg
                    class="pointer-events-none absolute top-6 right-[10%] left-[10%] hidden h-2 w-[80%] text-primary/40 lg:block"
                    viewBox="0 0 100 2"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                >
                    <line
                        x1="0"
                        y1="1"
                        x2="100"
                        y2="1"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-dasharray="4 6"
                        vector-effect="non-scaling-stroke"
                        class="route-dash"
                    />
                </svg>
                <li
                    v-for="(stage, index) in pipeline"
                    :key="stage.key"
                    class="group relative flex flex-col items-center text-center"
                >
                    <span
                        :class="[
                            'relative z-10 flex size-12 items-center justify-center rounded-2xl ring-4 ring-card transition duration-300 group-hover:scale-110',
                            stage.key === 'ready'
                                ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/25'
                                : 'bg-primary/10 text-primary',
                        ]"
                    >
                        <component
                            :is="stageIcons[stage.key]"
                            class="icon-bob size-5"
                            :style="{ animationDelay: `${index * 0.4}s` }"
                        />
                    </span>
                    <span
                        class="mt-3 text-2xl font-bold tracking-tight text-foreground tabular-nums"
                    >
                        {{ stage.count }}
                    </span>
                    <span class="text-xs font-medium text-muted-foreground">
                        {{ stage.label }}
                    </span>
                </li>
            </ol>
        </section>

        <!-- Packages over time + pre-alert mix -->
        <section class="grid gap-5 xl:grid-cols-3">
            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6 xl:col-span-2"
                style="--rise-delay: 360ms"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">
                            Packages received
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Last six months
                        </p>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-2xl font-bold tracking-tight text-foreground tabular-nums"
                        >
                            {{
                                Math.round(packagesTotalShown).toLocaleString()
                            }}
                        </p>
                        <span
                            v-if="packagesChange !== null"
                            :class="[
                                'mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                packagesChange >= 0
                                    ? 'bg-emerald-500/10 text-emerald-700'
                                    : 'bg-rose-500/10 text-rose-700',
                            ]"
                        >
                            {{ packagesChange >= 0 ? '+' : ''
                            }}{{ packagesChange }}% vs last month
                        </span>
                    </div>
                </div>
                <LineChart
                    class="mt-6"
                    :labels="charts.packages_over_time.labels"
                    :data="charts.packages_over_time.data"
                    label="Packages"
                    height-class="h-72"
                />
            </div>

            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6"
                style="--rise-delay: 420ms"
            >
                <h2 class="text-base font-semibold text-foreground">
                    Pre-alerts by status
                </h2>
                <p class="text-sm text-muted-foreground">
                    Every pre-alert on file
                </p>

                <DoughnutChart
                    class="mt-6"
                    :labels="charts.pre_alerts_by_status.labels"
                    :data="charts.pre_alerts_by_status.data"
                    height-class="h-52"
                >
                    <template #center="{ total }">
                        <span
                            class="text-3xl font-bold tracking-tight text-foreground tabular-nums"
                        >
                            {{ total }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            pre-alerts
                        </span>
                    </template>
                </DoughnutChart>

                <ul class="mt-6 grid gap-2.5">
                    <li
                        v-for="item in preAlertLegend"
                        :key="item.label"
                        class="flex items-center gap-3 text-sm"
                    >
                        <span
                            class="size-2.5 shrink-0 rounded-full"
                            :style="{ backgroundColor: item.color }"
                        />
                        <span class="flex-1 text-muted-foreground">
                            {{ item.label }}
                        </span>
                        <span
                            class="font-semibold text-foreground tabular-nums"
                        >
                            {{ item.count }}
                        </span>
                        <span
                            class="w-10 text-right text-xs text-muted-foreground tabular-nums"
                        >
                            {{ item.percent }}%
                        </span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Customers + recent packages -->
        <section class="grid gap-5 xl:grid-cols-3">
            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6"
                style="--rise-delay: 480ms"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">
                            New customers
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Sign ups per month
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-2.5 py-1 text-xs font-semibold text-indigo-700"
                    >
                        <Users class="size-3.5" />
                        {{ stats.new_customers_this_month }} this month
                    </span>
                </div>
                <BarChart
                    class="mt-6"
                    :labels="charts.new_users.labels"
                    :data="charts.new_users.data"
                    label="New customers"
                    color="#6366F1"
                    height-class="h-60"
                />
            </div>

            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6 xl:col-span-2"
                style="--rise-delay: 540ms"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">
                            Latest packages
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Newest arrivals and what is owed on each
                        </p>
                    </div>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="rounded-lg"
                    >
                        <Link :href="packagesIndex()">View all</Link>
                    </Button>
                </div>

                <ul
                    v-if="recent_packages.length"
                    class="mt-4 divide-y divide-border"
                >
                    <li
                        v-for="pkg in recent_packages"
                        :key="pkg.id"
                        class="group flex items-center gap-4 py-3.5"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <PackageCheck class="icon-hop size-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="editPackage(pkg.id)"
                                class="font-semibold text-foreground hover:text-primary"
                            >
                                {{ pkg.package_reference }}
                            </Link>
                            <p class="text-xs text-muted-foreground">
                                {{ pkg.payment_status_label }}
                            </p>
                        </div>
                        <StatusBadge
                            class="hidden sm:inline-flex"
                            :status="pkg.status_label"
                            :label="pkg.status_label"
                        />
                        <span
                            class="w-28 text-right text-sm font-semibold text-foreground tabular-nums"
                        >
                            {{
                                pkg.amount_due
                                    ? formatMoney(pkg.amount_due)
                                    : 'Not weighed'
                            }}
                        </span>
                    </li>
                </ul>
                <p v-else class="mt-6 text-sm text-muted-foreground">
                    No packages yet.
                </p>
            </div>
        </section>

        <!-- Pre-alerts + inbox -->
        <section class="grid gap-5 lg:grid-cols-2">
            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6"
                style="--rise-delay: 600ms"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">
                            Recent pre-alerts
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            What customers told you is coming
                        </p>
                    </div>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="rounded-lg"
                    >
                        <Link :href="preAlertsIndex()">View all</Link>
                    </Button>
                </div>
                <ul
                    v-if="recent_pre_alerts.length"
                    class="mt-4 divide-y divide-border"
                >
                    <li
                        v-for="pa in recent_pre_alerts"
                        :key="pa.id"
                        class="flex items-center gap-4 py-3"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-500/12 text-xs font-bold text-amber-700"
                        >
                            {{ pa.merchant_name.charAt(0) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="showPreAlert(pa.id)"
                                class="font-semibold text-foreground hover:text-primary"
                            >
                                {{ pa.merchant_name }}
                            </Link>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ pa.customer_name ?? 'Customer' }}
                            </p>
                        </div>
                        <StatusBadge
                            :status="pa.status_label"
                            :label="pa.status_label"
                        />
                    </li>
                </ul>
                <p v-else class="mt-6 text-sm text-muted-foreground">
                    None yet.
                </p>
            </div>

            <div
                class="dash-rise rounded-2xl border border-border bg-card p-5 sm:p-6"
                style="--rise-delay: 660ms"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span
                            class="relative flex size-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600"
                        >
                            <Inbox class="icon-bob size-5" />
                            <span
                                v-if="stats.new_contact_messages"
                                class="absolute -top-1 -right-1 flex size-4"
                            >
                                <span
                                    class="absolute inline-flex size-full animate-ping rounded-full bg-rose-400 opacity-70"
                                />
                                <span
                                    class="relative inline-flex size-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white"
                                >
                                    {{ stats.new_contact_messages }}
                                </span>
                            </span>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-foreground">
                                Contact inbox
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Messages from your website
                            </p>
                        </div>
                    </div>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="rounded-lg"
                    >
                        <Link :href="contactIndex()">Open inbox</Link>
                    </Button>
                </div>
                <ul
                    v-if="recent_contact_messages.length"
                    class="mt-4 divide-y divide-border"
                >
                    <li
                        v-for="message in recent_contact_messages"
                        :key="message.id"
                        class="flex items-start gap-4 py-3"
                    >
                        <Mail
                            :class="[
                                'mt-0.5 size-4 shrink-0',
                                message.status === 'new'
                                    ? 'text-rose-500'
                                    : 'text-muted-foreground',
                            ]"
                        />
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="showContact(message.id)"
                                :class="[
                                    'block truncate hover:text-primary',
                                    message.status === 'new'
                                        ? 'font-semibold text-foreground'
                                        : 'text-foreground',
                                ]"
                            >
                                {{ message.subject }}
                            </Link>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ message.name }}
                            </p>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-6 text-sm text-muted-foreground">
                    No messages yet.
                </p>
            </div>
        </section>
    </div>
</template>
