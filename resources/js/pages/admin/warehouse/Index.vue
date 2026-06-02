<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit, index } from '@/routes/admin/warehouse';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Warehouse', href: index() },
        ],
    },
});

defineProps<{
    addresses: Array<{
        id: number;
        name: string;
        city: string;
        state: string;
        is_active: boolean;
        single_line: string;
    }>;
}>();
</script>

<template>
    <Head title="Admin · Warehouse" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Warehouse addresses</h1>

        <Card>
            <CardContent class="divide-y pt-6">
                <div
                    v-for="address in addresses"
                    :key="address.id"
                    class="flex items-center justify-between gap-4 py-4"
                >
                    <div>
                        <p class="font-medium">{{ address.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ address.single_line }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <StatusBadge
                            :status="address.is_active ? 'active' : 'inactive'"
                            :label="address.is_active ? 'Active' : 'Inactive'"
                        />
                        <Button as-child variant="outline" size="sm">
                            <Link :href="edit(address.id)">Edit</Link>
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
