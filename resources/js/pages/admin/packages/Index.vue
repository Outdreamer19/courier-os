<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Plus, Search, SlidersHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableFooter,
    DataTableHeader,
    DataTableHeaderCell,
    DataTableRow,
} from '@/components/data-table';
import EmptyState from '@/components/EmptyState.vue';
import InitialsAvatar from '@/components/InitialsAvatar.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { formatMoney } from '@/lib/money';
import { statusTone as paymentTone } from '@/lib/statusTone';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Packages', href: index() },
        ],
    },
});

const props = defineProps<{
    packages: {
        data: Array<Record<string, unknown>>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: Record<string, string | null>;
    statuses: Record<string, string>;
    paymentStatuses: Record<string, string>;
    currency: string;
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');

const applyFilters = () => {
    router.get(
        index().url,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_status: paymentStatus.value || undefined,
        },
        { preserveState: true },
    );
};
</script>

<template>
    <Head title="Admin · Packages" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight">Packages</h1>
            <Button
                as-child
                class="bg-brand-ink text-white hover:bg-brand-ink-soft"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add package
                </Link>
            </Button>
        </div>

        <form class="flex flex-wrap gap-2" @submit.prevent="applyFilters">
            <Input v-model="search" placeholder="Search…" class="max-w-xs" />
            <select v-model="status" class="h-9 rounded-md border px-3 text-sm">
                <option value="">All statuses</option>
                <option v-for="(l, v) in statuses" :key="v" :value="v">
                    {{ l }}
                </option>
            </select>
            <select
                v-model="paymentStatus"
                class="h-9 rounded-md border px-3 text-sm"
            >
                <option value="">All payments</option>
                <option v-for="(l, v) in paymentStatuses" :key="v" :value="v">
                    {{ l }}
                </option>
            </select>
            <Button type="submit" variant="outline">
                <Search class="size-4" />
            </Button>
        </form>

        <Card v-if="packages.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="820px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="20%">
                            Reference
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="28%">
                            Customer
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="24%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%" align="right">
                            Due
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="8%">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="row in packages.data"
                            :key="row.id as number"
                        >
                            <DataTableCell>
                                <div class="truncate font-medium tabular-nums">
                                    {{ row.package_reference }}
                                </div>
                                <div
                                    v-if="row.merchant_name"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ row.merchant_name }}
                                </div>
                            </DataTableCell>
                            <DataTableCell>
                                <div class="flex items-center gap-3">
                                    <InitialsAvatar
                                        :name="
                                            (row.customer_name as string) ?? ''
                                        "
                                    />
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">
                                            {{ row.customer_name ?? '—' }}
                                        </div>
                                        <div
                                            v-if="row.customer_reference"
                                            class="truncate text-xs text-muted-foreground tabular-nums"
                                        >
                                            {{ row.customer_reference }}
                                        </div>
                                    </div>
                                </div>
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status_label as string"
                                />
                            </DataTableCell>
                            <!-- Payment state lives with the money rather than in
                                 a second badge column: one pill per row keeps
                                 the lifecycle status the thing you scan for. -->
                            <DataTableCell
                                align="right"
                                class="whitespace-nowrap"
                            >
                                <!-- Nothing owed yet reads as a placeholder, not
                                     a figure worth chasing. -->
                                <div
                                    class="font-medium tabular-nums"
                                    :class="
                                        (row.amount_due as number) > 0
                                            ? 'text-foreground'
                                            : 'text-muted-foreground/60'
                                    "
                                >
                                    {{
                                        formatMoney(
                                            row.amount_due as number,
                                            currency,
                                        )
                                    }}
                                </div>
                                <div
                                    class="mt-0.5 inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="
                                        paymentTone(
                                            row.payment_status as string,
                                        ).text
                                    "
                                >
                                    <span
                                        class="size-1.5 shrink-0 rounded-full"
                                        :class="
                                            paymentTone(
                                                row.payment_status as string,
                                            ).dot
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
                                        :href="edit(row.id as number)"
                                        :title="`Manage ${row.package_reference}`"
                                    >
                                        <SlidersHorizontal class="size-4" />
                                        <span class="sr-only">
                                            Manage {{ row.package_reference }}
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
            <CardContent>
                <EmptyState
                    :icon="Package"
                    title="No packages found"
                    description="Adjust your filters or add a package when one arrives at the warehouse."
                >
                    <Button
                        as-child
                        class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                    >
                        <Link :href="create()">Add package</Link>
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>
    </div>
</template>
