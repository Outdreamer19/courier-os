<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    preAlerts: { data: Array<Record<string, unknown>> };
    filters: { status: string | null; search: string | null };
    statuses: Record<string, string>;
}>();

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
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
            <Button type="submit" variant="outline">
                <Search class="size-4" />
                Filter
            </Button>
        </form>

        <Card>
            <CardContent class="overflow-x-auto pt-6">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4">Merchant</th>
                            <th class="pb-3 pr-4">Customer</th>
                            <th class="pb-3 pr-4">Tracking</th>
                            <th class="pb-3 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in preAlerts.data"
                            :key="row.id as number"
                            class="border-b border-border/60"
                        >
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(row.id as number)"
                                    class="font-medium hover:underline"
                                >
                                    {{ row.merchant_name }}
                                </Link>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.customer_name }}
                                <span class="block text-xs">
                                    {{ row.customer_reference }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.tracking_number ?? '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status_label as string"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
