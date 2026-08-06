<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Users } from 'lucide-vue-next';
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
import { create, edit, index, show } from '@/routes/admin/customers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Customers', href: index() },
        ],
    },
});

const props = defineProps<{
    customers: {
        data: Array<Record<string, unknown>>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: { search: string | null };
    canManageCustomers: boolean;
}>();

const search = ref(props.filters.search ?? '');

const applySearch = () => {
    router.get(
        index().url,
        { search: search.value || undefined },
        { preserveState: true },
    );
};
</script>

<template>
    <Head title="Admin · Customers" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Customers</h1>
                <p class="text-sm text-muted-foreground">
                    Search by name, email, phone, or customer reference.
                </p>
            </div>
            <Button
                v-if="canManageCustomers"
                as-child
                class="bg-brand-ink text-white hover:bg-brand-ink-soft"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add customer
                </Link>
            </Button>
        </div>

        <form class="flex gap-2" @submit.prevent="applySearch">
            <Input
                v-model="search"
                placeholder="Search customers…"
                class="max-w-md"
            />
            <Button type="submit" variant="outline">
                <Search class="size-4" />
                Search
            </Button>
        </form>

        <Card v-if="customers.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="820px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="34%">
                            Customer
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%">
                            Reference
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="26%">
                            Contact
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="12%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="8%">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="row in customers.data"
                            :key="row.id as number"
                        >
                            <DataTableCell>
                                <div class="flex items-center gap-3">
                                    <InitialsAvatar
                                        :name="(row.name as string) ?? ''"
                                    />
                                    <Link
                                        :href="show(row.id as number)"
                                        class="min-w-0 truncate font-medium hover:underline"
                                    >
                                        {{ row.name }}
                                    </Link>
                                </div>
                            </DataTableCell>
                            <DataTableCell class="truncate tabular-nums">
                                {{ row.customer_reference ?? '—' }}
                            </DataTableCell>
                            <DataTableCell>
                                <div class="truncate">{{ row.email }}</div>
                                <div
                                    v-if="row.phone"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ row.phone }}
                                </div>
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status as string"
                                    class="capitalize"
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
                                        :href="edit(row.id as number)"
                                        :title="`Edit ${row.name}`"
                                    >
                                        <Pencil class="size-4" />
                                        <span class="sr-only">
                                            Edit {{ row.name }}
                                        </span>
                                    </Link>
                                </Button>
                            </DataTableCell>
                        </DataTableRow>
                    </DataTableBody>
                </DataTable>
            </CardContent>

            <DataTableFooter
                :links="customers.links"
                :from="customers.from"
                :to="customers.to"
                :total="customers.total"
                noun="customers"
            />
        </Card>

        <Card v-else>
            <CardContent>
                <EmptyState
                    :icon="Users"
                    title="No customers found"
                    description="Try a different search term or add a new customer account."
                >
                    <Button
                        as-child
                        class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                    >
                        <Link :href="create()">Add customer</Link>
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>
    </div>
</template>
