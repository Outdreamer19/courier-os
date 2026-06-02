<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index, show } from '@/routes/portal/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Packages', href: index() },
        ],
    },
});

type PackageRow = {
    id: number;
    package_reference: string;
    merchant_name: string | null;
    tracking_number: string | null;
    status: string;
    status_label: string;
    payment_status: string;
    payment_status_label: string;
    amount_due: number;
    weight_lbs: number | null;
    updated_at: string | null;
};

const props = defineProps<{
    packages: {
        data: PackageRow[];
    };
    currency: string;
}>();

const formatMoney = (amount: number) => {
    return `${props.currency} $${amount.toLocaleString()}`;
};
</script>

<template>
    <Head title="My packages" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">My packages</h1>
            <p class="text-sm text-muted-foreground">
                Track package status, amount due, and pickup readiness.
            </p>
        </div>

        <Card v-if="packages.data.length">
            <CardHeader>
                <CardTitle>All packages</CardTitle>
                <CardDescription>Pickup only — no home delivery.</CardDescription>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4 font-medium">Reference</th>
                            <th class="pb-3 pr-4 font-medium">Merchant</th>
                            <th class="pb-3 pr-4 font-medium">Status</th>
                            <th class="pb-3 pr-4 font-medium">Payment</th>
                            <th class="pb-3 font-medium">Amount due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in packages.data"
                            :key="row.id"
                            class="border-b border-border/60 last:border-0"
                        >
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(row.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ row.package_reference }}
                                </Link>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.merchant_name ?? '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status"
                                    :label="row.status_label"
                                />
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.payment_status"
                                    :label="row.payment_status_label"
                                />
                            </td>
                            <td class="py-3 font-medium">
                                {{ formatMoney(row.amount_due) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Card v-else>
            <CardContent
                class="flex flex-col items-center justify-center gap-3 py-16 text-center"
            >
                <Package class="size-10 text-muted-foreground/50" />
                <p class="text-sm text-muted-foreground">
                    No packages are linked to your account yet.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
