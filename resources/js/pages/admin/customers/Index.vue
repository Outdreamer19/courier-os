<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    customers: { data: Array<Record<string, unknown>> };
    filters: { search: string | null };
}>();

const search = ref(props.filters.search ?? '');

const applySearch = () => {
    router.get(index().url, { search: search.value || undefined }, { preserveState: true });
};
</script>

<template>
    <Head title="Admin · Customers" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Customers</h1>
                <p class="text-sm text-muted-foreground">
                    Search by name, email, phone, or customer reference.
                </p>
            </div>
            <Button
                as-child
                class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add customer
                </Link>
            </Button>
        </div>

        <form class="flex gap-2" @submit.prevent="applySearch">
            <Input v-model="search" placeholder="Search customers…" class="max-w-md" />
            <Button type="submit" variant="outline">
                <Search class="size-4" />
                Search
            </Button>
        </form>

        <Card>
            <CardHeader>
                <CardTitle>All customers</CardTitle>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4 font-medium">Reference</th>
                            <th class="pb-3 pr-4 font-medium">Name</th>
                            <th class="pb-3 pr-4 font-medium">Email</th>
                            <th class="pb-3 pr-4 font-medium">Status</th>
                            <th class="pb-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in customers.data"
                            :key="row.id as number"
                            class="border-b border-border/60"
                        >
                            <td class="py-3 pr-4 font-medium">
                                {{ row.customer_reference ?? '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(row.id as number)"
                                    class="hover:underline"
                                >
                                    {{ row.name }}
                                </Link>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.email }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status as string"
                                    :label="row.status as string"
                                />
                            </td>
                            <td class="py-3 text-right">
                                <Button as-child variant="ghost" size="sm">
                                    <Link :href="edit(row.id as number)">Edit</Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
