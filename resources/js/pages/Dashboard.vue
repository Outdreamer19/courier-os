<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Boxes,
    Building2,
    CircleDollarSign,
    Headset,
    MapPinned,
    Package,
    PlusCircle,
    Receipt,
} from 'lucide-vue-next';
import { computed } from 'vue';
import CopyButton from '@/components/CopyButton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { contact, dashboard } from '@/routes';
import { edit as profileEdit } from '@/routes/portal/profile';
import { create as createPreAlert, index as preAlertsIndex, show as showPreAlert } from '@/routes/portal/pre-alerts';
import { index as packagesIndex, show as showPackage } from '@/routes/portal/packages';
import { shippingAddress } from '@/routes/portal';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
        ],
    },
});

const props = defineProps<{
    profile: {
        customer_reference: string;
        phone: string | null;
        whatsapp_number: string | null;
        jamaica_address: string | null;
        parish: string | null;
    } | null;
    warehouse: {
        name: string;
        customer_name: string;
        customer_reference: string;
        address_line_1: string;
        address_line_2: string | null;
        city: string;
        state: string;
        zip: string;
        phone: string | null;
        instructions: string | null;
        full_address: string;
    } | null;
    stats: {
        active_packages: number;
        pre_alerts: number;
        amount_due: number;
        currency: string;
    };
    recentPreAlerts: Array<{
        id: number;
        merchant_name: string;
        status: string;
        status_label: string;
        created_at: string | null;
    }>;
    recentPackages: Array<{
        id: number;
        package_reference: string;
        status: string;
        status_label: string;
        payment_status: string;
        amount_due: number;
    }>;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const reference = computed(() => props.profile?.customer_reference ?? '—');

const formattedAmountDue = computed(() => {
    return `${props.stats.currency} $${props.stats.amount_due.toLocaleString()}`;
});

const statCards = computed(() => [
    {
        title: 'Active packages',
        value: props.stats.active_packages,
        icon: Package,
        helper: 'Packages currently in transit or being processed',
    },
    {
        title: 'Pre-alerts',
        value: props.stats.pre_alerts,
        icon: Receipt,
        helper: 'Pre-alerts you have submitted',
    },
    {
        title: 'Amount due',
        value: formattedAmountDue.value,
        icon: CircleDollarSign,
        helper: 'Payable online or in person at pickup',
    },
]);

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString();
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="rounded-xl border border-border bg-card p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-gold"
                    >
                        Welcome back
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        Hello, {{ user?.name ?? 'there' }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Your SHIP DJM customer reference is
                        <span class="font-medium text-foreground">
                            {{ reference }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <CopyButton
                        v-if="profile"
                        :value="reference"
                        label="Copy reference"
                        success-message="Customer reference copied"
                    />
                    <Button
                        as-child
                        class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    >
                        <Link :href="contact()">
                            <Headset class="size-4" />
                            <span>Support</span>
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card
                v-for="card in statCards"
                :key="card.title"
                class="border-border bg-card"
            >
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ card.title }}
                    </CardTitle>
                    <component
                        :is="card.icon"
                        class="size-4 text-brand-gold"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold tracking-tight">
                        {{ card.value }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ card.helper }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Building2 class="size-4 text-brand-gold" />
                                Your Florida shipping address
                            </CardTitle>
                            <CardDescription>
                                Use this exact address at checkout — including
                                your customer reference.
                            </CardDescription>
                        </div>
                        <CopyButton
                            v-if="warehouse"
                            :value="warehouse.full_address"
                            label="Copy address"
                            success-message="Full address copied"
                        />
                    </div>
                </CardHeader>
                <CardContent v-if="warehouse" class="space-y-4 text-sm">
                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                                Name
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ warehouse.customer_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                                Suite (your reference)
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ warehouse.customer_reference }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                                Address
                            </dt>
                            <dd class="mt-0.5 font-medium">
                                {{ warehouse.address_line_1 }}
                                <span v-if="warehouse.address_line_2">
                                    , {{ warehouse.address_line_2 }}
                                </span>
                                — {{ warehouse.city }}, {{ warehouse.state }}
                                {{ warehouse.zip }}
                            </dd>
                        </div>
                    </dl>

                    <Button as-child variant="outline" size="sm">
                        <Link :href="shippingAddress()">
                            <MapPinned class="size-4" />
                            View full address
                        </Link>
                    </Button>
                </CardContent>
                <CardContent v-else class="text-sm text-muted-foreground">
                    No active warehouse address is configured yet.
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Boxes class="size-4 text-brand-gold" />
                        Quick actions
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <Button
                        as-child
                        class="w-full justify-start bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    >
                        <Link :href="createPreAlert()">
                            <PlusCircle class="size-4" />
                            Submit pre-alert
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="w-full justify-start">
                        <Link :href="packagesIndex()">
                            <Package class="size-4" />
                            View my packages
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="w-full justify-start">
                        <Link :href="profileEdit()">
                            <MapPinned class="size-4" />
                            Update profile
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Recent pre-alerts</CardTitle>
                        <CardDescription>Latest submissions</CardDescription>
                    </div>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="preAlertsIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul
                        v-if="recentPreAlerts.length"
                        class="divide-y text-sm"
                    >
                        <li
                            v-for="item in recentPreAlerts"
                            :key="item.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div>
                                <Link
                                    :href="showPreAlert(item.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ item.merchant_name }}
                                </Link>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(item.created_at) }}
                                </p>
                            </div>
                            <StatusBadge
                                :status="item.status"
                                :label="item.status_label"
                            />
                        </li>
                    </ul>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-border py-12 text-center text-sm text-muted-foreground"
                    >
                        <Receipt class="size-8 text-muted-foreground/60" />
                        <p>No pre-alerts yet.</p>
                        <Button
                            as-child
                            size="sm"
                            class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                        >
                            <Link :href="createPreAlert()">Submit one now</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Recent packages</CardTitle>
                        <CardDescription>Status at a glance</CardDescription>
                    </div>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="packagesIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul
                        v-if="recentPackages.length"
                        class="divide-y text-sm"
                    >
                        <li
                            v-for="item in recentPackages"
                            :key="item.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div>
                                <Link
                                    :href="showPackage(item.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ item.package_reference }}
                                </Link>
                                <p class="text-xs text-muted-foreground">
                                    {{ stats.currency }} ${{
                                        item.amount_due.toLocaleString()
                                    }}
                                    due
                                </p>
                            </div>
                            <StatusBadge
                                :status="item.status"
                                :label="item.status_label"
                            />
                        </li>
                    </ul>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-border py-12 text-center text-sm text-muted-foreground"
                    >
                        <Package class="size-8 text-muted-foreground/60" />
                        <p>No packages linked to your account yet.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
