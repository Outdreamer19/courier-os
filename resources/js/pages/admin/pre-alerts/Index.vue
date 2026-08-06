<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowUpRight, Receipt, Search } from 'lucide-vue-next';
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
import { dashboard as adminDashboard } from '@/routes/admin';
import { index, show } from '@/routes/admin/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Pre-alerts', href: index() },
        ],
    },
});

const props = defineProps<{
    preAlerts: {
        data: Array<Record<string, unknown>>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: { status: string | null; search: string | null };
    statuses: Record<string, string>;
}>();

const formatDate = (value: unknown) => {
    if (typeof value !== 'string') {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        index().url,
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true },
    );
};
</script>

<template>
    <Head title="Admin · Pre-alerts" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold tracking-tight">Pre-alerts</h1>

        <form class="flex flex-wrap gap-2" @submit.prevent="applyFilters">
            <Input v-model="search" placeholder="Search…" class="max-w-xs" />
            <select
                v-model="status"
                class="h-9 rounded-md border border-input px-3 text-sm"
            >
                <option value="">All statuses</option>
                <option
                    v-for="(label, value) in statuses"
                    :key="value"
                    :value="value"
                >
                    {{ label }}
                </option>
            </select>
            <Button type="submit" variant="outline">
                <Search class="size-4" />
                Filter
            </Button>
        </form>

        <Card v-if="preAlerts.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="880px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="24%">
                            Merchant
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="28%">
                            Customer
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%">
                            Tracking
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="8%">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="row in preAlerts.data"
                            :key="row.id as number"
                        >
                            <DataTableCell>
                                <Link
                                    :href="show(row.id as number)"
                                    class="block truncate font-medium hover:underline"
                                >
                                    {{ row.merchant_name }}
                                </Link>
                                <div
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ formatDate(row.created_at) }}
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
                            <DataTableCell muted class="truncate tabular-nums">
                                {{ row.tracking_number ?? '—' }}
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status_label as string"
                                />
                            </DataTableCell>
                            <DataTableCell align="right">
                                <Button
                                    as-child
                                    variant="outline"
                                    size="icon-sm"
                                    class="text-muted-foreground opacity-70 transition-all group-hover:opacity-100 hover:border-brand-ink/50 hover:bg-brand-ink/10 hover:text-brand-ink dark:hover:bg-brand-ink/25 dark:hover:text-brand-cream"
                                >
                                    <Link
                                        :href="show(row.id as number)"
                                        :title="`Open ${row.merchant_name} pre-alert`"
                                    >
                                        <ArrowUpRight class="size-4" />
                                        <span class="sr-only">
                                            Open pre-alert
                                        </span>
                                    </Link>
                                </Button>
                            </DataTableCell>
                        </DataTableRow>
                    </DataTableBody>
                </DataTable>
            </CardContent>

            <DataTableFooter
                :links="preAlerts.links"
                :from="preAlerts.from"
                :to="preAlerts.to"
                :total="preAlerts.total"
                noun="pre-alerts"
            />
        </Card>

        <Card v-else>
            <CardContent>
                <EmptyState
                    :icon="Receipt"
                    title="No pre-alerts found"
                    description="Try adjusting your search or status filters."
                />
            </CardContent>
        </Card>
    </div>
</template>
