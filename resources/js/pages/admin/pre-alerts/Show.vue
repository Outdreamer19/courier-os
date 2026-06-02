<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/components/StatusBadge.vue';
import WhatsAppButton from '@/components/WhatsAppButton.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as editPackage } from '@/routes/admin/packages';
import { index, update } from '@/routes/admin/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Pre-alerts', href: index() },
            { title: 'Review', href: '#' },
        ],
    },
});

const props = defineProps<{
    preAlert: Record<string, unknown> & {
        id: number;
        customer: {
            id: number;
            name: string;
            reference: string;
            whatsapp_url: string | null;
        };
        package: { id: number; package_reference: string } | null;
    };
    statuses: Record<string, string>;
}>();
</script>

<template>
    <Head :title="`Pre-alert · ${preAlert.merchant_name}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ preAlert.merchant_name }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ preAlert.customer.name }} · {{ preAlert.customer.reference }}
                </p>
                <StatusBadge
                    class="mt-2"
                    :status="preAlert.status as string"
                    :label="preAlert.status_label as string"
                />
            </div>
            <div class="flex items-center gap-2">
                <WhatsAppButton :url="preAlert.customer.whatsapp_url" />
                <a
                    v-if="preAlert.invoice_url"
                    :href="preAlert.invoice_url as string"
                    class="text-sm font-medium underline"
                    target="_blank"
                    rel="noopener"
                >
                    View invoice
                </a>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Shipment details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 text-sm sm:grid-cols-2">
                <p>
                    <span class="text-muted-foreground">Tracking:</span>
                    {{ preAlert.tracking_number ?? '—' }}
                </p>
                <p>
                    <span class="text-muted-foreground">Carrier:</span>
                    {{ preAlert.carrier_label }}
                </p>
                <p class="sm:col-span-2">
                    <span class="text-muted-foreground">Description:</span>
                    {{ preAlert.item_description }}
                </p>
            </CardContent>
        </Card>

        <Card v-if="preAlert.package">
            <CardContent class="flex items-center justify-between py-4 text-sm">
                <span>
                    Linked package:
                    <strong>{{ preAlert.package.package_reference }}</strong>
                </span>
                <Button as-child variant="outline" size="sm">
                    <Link :href="editPackage(preAlert.package.id)">Open package</Link>
                </Button>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Admin review</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(preAlert.id)"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                            :default-value="preAlert.status"
                        >
                            <option
                                v-for="(label, value) in statuses"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="admin_notes">Internal notes</Label>
                        <textarea
                            id="admin_notes"
                            name="admin_notes"
                            rows="4"
                            class="w-full rounded-md border border-input px-3 py-2 text-sm"
                            :default-value="(preAlert.admin_notes as string) ?? ''"
                        />
                    </div>
                    <Button
                        type="submit"
                        class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                        :disabled="processing"
                    >
                        Save review
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
