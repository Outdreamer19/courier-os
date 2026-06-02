<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/components/StatusBadge.vue';
import WhatsAppButton from '@/components/WhatsAppButton.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as editCustomer, index as customersIndex } from '@/routes/admin/customers';
import { create as createPackage, edit as editPackage } from '@/routes/admin/packages';
import { show as showPreAlert } from '@/routes/admin/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Customers', href: customersIndex() },
            { title: 'Profile', href: '#' },
        ],
    },
});

defineProps<{
    customer: Record<string, unknown>;
    preAlerts: Array<{ id: number; merchant_name: string; status_label: string }>;
    packages: Array<{
        id: number;
        package_reference: string;
        status_label: string;
        amount_due: number;
    }>;
}>();
</script>

<template>
    <Head :title="`Admin · ${customer.name}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ customer.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ customer.customer_reference }} · {{ customer.email }}
                </p>
                <StatusBadge
                    class="mt-2"
                    :status="customer.status as string"
                    :label="customer.status as string"
                />
            </div>
            <div class="flex gap-2">
                <WhatsAppButton :url="customer.whatsapp_url as string | null" />
                <Button as-child variant="outline">
                    <Link :href="editCustomer(customer.id as number)">Edit</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Recent pre-alerts</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <Link
                        v-for="pa in preAlerts"
                        :key="pa.id"
                        :href="showPreAlert(pa.id)"
                        class="flex justify-between rounded-md border p-3 hover:bg-muted/50"
                    >
                        <span>{{ pa.merchant_name }}</span>
                        <span class="text-muted-foreground">{{ pa.status_label }}</span>
                    </Link>
                    <p v-if="!preAlerts.length" class="text-muted-foreground">
                        No pre-alerts yet.
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Recent packages</CardTitle>
                    <Button as-child size="sm" variant="outline">
                        <Link
                            :href="
                                createPackage({
                                    query: { user_id: customer.id as number },
                                })
                            "
                        >
                            Add package
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <Link
                        v-for="pkg in packages"
                        :key="pkg.id"
                        :href="editPackage(pkg.id)"
                        class="flex justify-between rounded-md border p-3 hover:bg-muted/50"
                    >
                        <span>{{ pkg.package_reference }}</span>
                        <span class="text-muted-foreground">{{ pkg.status_label }}</span>
                    </Link>
                    <p v-if="!packages.length" class="text-muted-foreground">
                        No packages yet.
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
