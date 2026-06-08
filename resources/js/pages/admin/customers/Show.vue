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
    customer: Record<string, unknown> & {
        id: number;
        name: string;
        email: string;
        status: string;
        customer_reference: string;
        trn?: string | null;
        phone?: string | null;
        jamaica_address?: string | null;
        parish?: string | null;
        date_of_birth?: string | null;
        authorised_pickup_person?: {
            full_name: string;
            phone: string;
            relationship_note: string | null;
            id_number: string | null;
        } | null;
        whatsapp_url?: string | null;
    };
    preAlerts: Array<{ id: number; merchant_name: string; status_label: string }>;
    packages: Array<{
        id: number;
        package_reference: string;
        status_label: string;
        amount_due: number;
    }>;
    canManageCustomers: boolean;
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
                    :status="customer.status"
                    :label="customer.status"
                />
            </div>
            <div class="flex gap-2">
                <WhatsAppButton :url="customer.whatsapp_url ?? null" />
                <Button v-if="canManageCustomers" as-child variant="outline">
                    <Link :href="editCustomer(customer.id)">Edit</Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Customer details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <p class="text-muted-foreground">TRN</p>
                    <p class="font-medium">{{ customer.trn || '—' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Phone</p>
                    <p class="font-medium">{{ customer.phone || '—' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Date of birth</p>
                    <p class="font-medium">{{ customer.date_of_birth || '—' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Parish</p>
                    <p class="font-medium">{{ customer.parish || '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-muted-foreground">Address</p>
                    <p class="font-medium">{{ customer.jamaica_address || '—' }}</p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Authorised pickup person</CardTitle>
            </CardHeader>
            <CardContent class="text-sm">
                <template v-if="customer.authorised_pickup_person">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-muted-foreground">Name</p>
                            <p class="font-medium">
                                {{ customer.authorised_pickup_person.full_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Phone</p>
                            <p class="font-medium">
                                {{ customer.authorised_pickup_person.phone }}
                            </p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Relationship / note</p>
                            <p class="font-medium">
                                {{
                                    customer.authorised_pickup_person
                                        .relationship_note || '—'
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">ID / TRN</p>
                            <p class="font-medium">
                                {{
                                    customer.authorised_pickup_person.id_number ||
                                    '—'
                                }}
                            </p>
                        </div>
                    </div>
                </template>
                <p v-else class="text-muted-foreground">
                    No authorised pickup person on file.
                </p>
            </CardContent>
        </Card>

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
                    <Button
                        v-if="canManageCustomers"
                        as-child
                        size="sm"
                        variant="outline"
                    >
                        <Link
                            :href="
                                createPackage({
                                    query: { user_id: customer.id },
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
