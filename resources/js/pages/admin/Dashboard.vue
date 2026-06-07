<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    Inbox,
    Mail,
    Package,
    PackageCheck,
    Receipt,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as contactIndex, show as showContact } from '@/routes/admin/contact-messages';
import { index as customersIndex } from '@/routes/admin/customers';
import { index as packagesIndex } from '@/routes/admin/packages';
import { index as preAlertsIndex, show as showPreAlert } from '@/routes/admin/pre-alerts';
import { edit as editPackage } from '@/routes/admin/packages';

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
    charts: {
        new_users: { labels: string[]; data: number[] };
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
    }[];
    recent_packages: {
        id: number;
        package_reference: string;
        status_label: string;
        payment_status_label: string;
        amount_due: number;
    }[];
}>();

const tiles = computed(() => [
    {
        title: 'Customers',
        value: props.stats.total_customers,
        helper: `${props.stats.new_customers_this_month} new this month`,
        icon: Users,
        href: customersIndex(),
    },
    {
        title: 'Pre-alerts',
        value: props.stats.total_pre_alerts,
        helper: `${props.stats.pre_alerts_pending} pending review`,
        icon: Receipt,
        href: preAlertsIndex(),
    },
    {
        title: 'Packages',
        value: props.stats.total_packages,
        helper: `${props.stats.packages_ready_for_pickup} ready for pickup`,
        icon: Package,
        href: packagesIndex(),
    },
    {
        title: 'Unpaid packages',
        value: props.stats.unpaid_packages,
        helper: 'Outstanding payment',
        icon: AlertCircle,
        href: packagesIndex({ query: { payment_status: 'unpaid' } }),
    },
    {
        title: 'New contact messages',
        value: props.stats.new_contact_messages,
        helper: 'Awaiting triage',
        icon: Inbox,
        href: contactIndex({ query: { status: 'new' } }),
    },
]);
</script>

<template>
    <Head title="Admin · Dashboard" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="rounded-xl border border-border bg-card p-6">
            <p
                class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-gold"
            >
                Admin overview
            </p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                Operations dashboard
            </h1>
            <p class="text-sm text-muted-foreground">
                Manage customers, pre-alerts, packages, and contact messages.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="tile in tiles"
                :key="tile.title"
                :href="tile.href"
                class="block rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
                <Card class="h-full transition-colors hover:bg-muted/30">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ tile.title }}
                        </CardTitle>
                        <component :is="tile.icon" class="size-4 text-brand-gold" />
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-semibold tracking-tight">
                            {{ tile.value }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ tile.helper }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="size-4 text-brand-gold" />
                        New customers
                    </CardTitle>
                    <CardDescription>
                        Customer sign-ups over the last six months
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <BarChart
                        :labels="charts.new_users.labels"
                        :data="charts.new_users.data"
                        label="New customers"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Receipt class="size-4 text-brand-gold" />
                        Pre-alerts by status
                    </CardTitle>
                    <CardDescription>
                        Current distribution across all pre-alerts
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <DoughnutChart
                        :labels="charts.pre_alerts_by_status.labels"
                        :data="charts.pre_alerts_by_status.data"
                    />
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-1">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <Mail class="size-4 text-brand-gold" />
                            Contact
                        </CardTitle>
                        <CardDescription>Latest submissions</CardDescription>
                    </div>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="contactIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul v-if="recent_contact_messages.length" class="divide-y text-sm">
                        <li
                            v-for="message in recent_contact_messages"
                            :key="message.id"
                            class="py-3"
                        >
                            <Link
                                :href="showContact(message.id)"
                                class="font-medium hover:underline"
                            >
                                {{ message.subject }}
                            </Link>
                            <p class="text-xs text-muted-foreground">
                                {{ message.name }}
                            </p>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">No messages yet.</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Recent pre-alerts</CardTitle>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="preAlertsIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul v-if="recent_pre_alerts.length" class="space-y-3 text-sm">
                        <li
                            v-for="pa in recent_pre_alerts"
                            :key="pa.id"
                            class="flex items-center justify-between gap-2"
                        >
                            <Link
                                :href="showPreAlert(pa.id)"
                                class="font-medium hover:underline"
                            >
                                {{ pa.merchant_name }}
                            </Link>
                            <StatusBadge
                                :status="pa.status_label"
                                :label="pa.status_label"
                            />
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">None yet.</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="flex items-center gap-2">
                        <PackageCheck class="size-4 text-brand-gold" />
                        Recent packages
                    </CardTitle>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="packagesIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul v-if="recent_packages.length" class="space-y-3 text-sm">
                        <li
                            v-for="pkg in recent_packages"
                            :key="pkg.id"
                            class="flex items-center justify-between gap-2"
                        >
                            <Link
                                :href="editPackage(pkg.id)"
                                class="font-medium hover:underline"
                            >
                                {{ pkg.package_reference }}
                            </Link>
                            <span class="text-xs text-muted-foreground">
                                {{ pkg.status_label }}
                            </span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">None yet.</p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
