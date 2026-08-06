<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Package } from 'lucide-vue-next';
import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableFooter,
    DataTableHeader,
    DataTableHeaderCell,
    DataTableRow,
} from '@/components/data-table';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { statusTone as paymentTone } from '@/lib/statusTone';
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
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
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

        <Card v-if="packages.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="760px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="24%">
                            Reference
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="22%">
                            Merchant
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="26%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%" align="right">
                            Amount due
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="8%">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="row in packages.data"
                            :key="row.id"
                        >
                            <DataTableCell>
                                <Link
                                    :href="show(row.id)"
                                    class="block truncate font-medium tabular-nums hover:underline"
                                >
                                    {{ row.package_reference }}
                                </Link>
                                <div
                                    v-if="row.tracking_number"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ row.tracking_number }}
                                </div>
                            </DataTableCell>
                            <DataTableCell muted class="truncate">
                                {{ row.merchant_name ?? '—' }}
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="row.status"
                                    :label="row.status_label"
                                />
                            </DataTableCell>
                            <DataTableCell
                                align="right"
                                class="whitespace-nowrap"
                            >
                                <div
                                    class="font-medium tabular-nums"
                                    :class="
                                        row.amount_due > 0
                                            ? 'text-foreground'
                                            : 'text-muted-foreground/60'
                                    "
                                >
                                    {{ formatMoney(row.amount_due) }}
                                </div>
                                <div
                                    class="mt-0.5 inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="
                                        paymentTone(row.payment_status).text
                                    "
                                >
                                    <span
                                        class="size-1.5 shrink-0 rounded-full"
                                        :class="
                                            paymentTone(row.payment_status).dot
                                        "
                                    />
                                    {{ row.payment_status_label }}
                                </div>
                            </DataTableCell>
                            <DataTableCell align="right">
                                <Button
                                    as-child
                                    variant="outline"
                                    size="icon-sm"
                                    class="text-muted-foreground opacity-70 transition-all group-hover:opacity-100 hover:border-brand-ink/50 hover:bg-brand-ink/10 hover:text-brand-ink dark:hover:bg-brand-ink/25 dark:hover:text-brand-cream"
                                >
                                    <Link
                                        :href="show(row.id)"
                                        :title="`View ${row.package_reference}`"
                                    >
                                        <ArrowUpRight class="size-4" />
                                        <span class="sr-only">
                                            View {{ row.package_reference }}
                                        </span>
                                    </Link>
                                </Button>
                            </DataTableCell>
                        </DataTableRow>
                    </DataTableBody>
                </DataTable>
            </CardContent>

            <DataTableFooter
                :links="packages.links"
                :from="packages.from"
                :to="packages.to"
                :total="packages.total"
                noun="packages"
            />
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
