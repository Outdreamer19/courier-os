<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { PlusCircle, Receipt } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { create, index, show } from '@/routes/portal/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pre-alerts', href: index() },
        ],
    },
});

type PreAlertRow = {
    id: number;
    merchant_name: string;
    tracking_number: string | null;
    status: string;
    status_label: string;
    expected_delivery_date: string | null;
    created_at: string | null;
    has_invoice: boolean;
};

defineProps<{
    preAlerts: {
        data: PreAlertRow[];
        links: unknown;
        meta: unknown;
    };
}>();

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString();
};
</script>

<template>
    <Head title="Pre-alerts" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Pre-alerts</h1>
                <p class="text-sm text-muted-foreground">
                    Tell us what you ordered before your package arrives at our
                    Florida warehouse.
                </p>
            </div>
            <Button
                as-child
                class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
            >
                <Link :href="create()">
                    <PlusCircle class="size-4" />
                    Submit pre-alert
                </Link>
            </Button>
        </div>

        <Card v-if="preAlerts.data.length">
            <CardHeader>
                <CardTitle>Your pre-alerts</CardTitle>
                <CardDescription>
                    Click a row to view details or upload an invoice.
                </CardDescription>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4 font-medium">Merchant</th>
                            <th class="pb-3 pr-4 font-medium">Tracking</th>
                            <th class="pb-3 pr-4 font-medium">Status</th>
                            <th class="pb-3 pr-4 font-medium">Expected</th>
                            <th class="pb-3 font-medium">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in preAlerts.data"
                            :key="row.id"
                            class="border-b border-border/60 last:border-0"
                        >
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(row.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ row.merchant_name }}
                                </Link>
                                <span
                                    v-if="row.has_invoice"
                                    class="ml-2 text-xs text-muted-foreground"
                                >
                                    · invoice
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.tracking_number ?? '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status"
                                    :label="row.status_label"
                                />
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ formatDate(row.expected_delivery_date) }}
                            </td>
                            <td class="py-3 text-muted-foreground">
                                {{ formatDate(row.created_at) }}
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
                <Receipt class="size-10 text-muted-foreground/50" />
                <p class="text-sm text-muted-foreground">
                    You haven't submitted any pre-alerts yet.
                </p>
                <Button
                    as-child
                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                >
                    <Link :href="create()">Submit your first pre-alert</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
