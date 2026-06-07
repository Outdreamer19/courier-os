<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
    packages: { data: Array<Record<string, unknown>> };
    filters: Record<string, string | null>;
    statuses: Record<string, string>;
    paymentStatuses: Record<string, string>;
    currency: string;
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');

const applyFilters = () => {
    router.get(index().url, {
        search: search.value || undefined,
        status: status.value || undefined,
        payment_status: paymentStatus.value || undefined,
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Admin · Packages" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight">Packages</h1>
            <Button
                as-child
                class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
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
                <option v-for="(l, v) in statuses" :key="v" :value="v">{{ l }}</option>
            </select>
            <select v-model="paymentStatus" class="h-9 rounded-md border px-3 text-sm">
                <option value="">All payments</option>
                <option v-for="(l, v) in paymentStatuses" :key="v" :value="v">
                    {{ l }}
                </option>
            </select>
            <Button type="submit" variant="outline">
                <Search class="size-4" />
            </Button>
        </form>

        <Card v-if="packages.data.length">
            <CardContent class="overflow-x-auto pt-6">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4">Reference</th>
                            <th class="pb-3 pr-4">Customer</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3 pr-4">Payment</th>
                            <th class="pb-3 pr-4">Due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in packages.data"
                            :key="row.id as number"
                            class="border-b border-border/60"
                        >
                            <td class="py-3 pr-4 font-medium">
                                {{ row.package_reference }}
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.customer_name }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status_label as string"
                                />
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.payment_status as string"
                                    :label="row.payment_status_label as string"
                                />
                            </td>
                            <td class="py-3 pr-4">
                                {{ currency }} ${{
                                    (row.amount_due as number).toLocaleString()
                                }}
                            </td>
                            <td class="py-3 text-right">
                                <Button as-child variant="ghost" size="sm">
                                    <Link :href="edit(row.id as number)">Manage</Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
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
                        class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    >
                        <Link :href="create()">Add package</Link>
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>
    </div>
</template>
