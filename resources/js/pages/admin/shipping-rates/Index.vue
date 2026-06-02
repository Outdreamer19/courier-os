<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/shipping-rates';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Shipping rates', href: index() },
        ],
    },
});

defineProps<{
    rates: Array<{
        id: number;
        name: string;
        tier_label?: string;
        rate_per_lb: number;
        minimum_charge: number;
        is_active: boolean;
    }>;
    currency: string;
}>();
</script>

<template>
    <Head title="Admin · Shipping rates" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Shipping rates</h1>
            <Button as-child class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft">
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add rate
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="divide-y pt-6">
                <div
                    v-for="rate in rates"
                    :key="rate.id"
                    class="flex items-center justify-between gap-4 py-4"
                >
                    <div>
                        <p class="font-medium">{{ rate.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ rate.tier_label ?? 'All weights' }} ·
                            {{ currency }} ${{ rate.rate_per_lb }}/lb · min ${{
                                rate.minimum_charge
                            }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <StatusBadge
                            :status="rate.is_active ? 'active' : 'inactive'"
                            :label="rate.is_active ? 'Active' : 'Inactive'"
                        />
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="edit(rate.id)">Edit</Link>
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
