<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import PreAlertForm from '@/components/customer/PreAlertForm.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index, update } from '@/routes/portal/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pre-alerts', href: index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const props = defineProps<{
    preAlert: {
        id: number;
        merchant_name: string;
        order_number: string | null;
        tracking_number: string | null;
        carrier: string;
        expected_delivery_date: string | null;
        item_description: string;
        declared_value: number | null;
        customer_notes: string | null;
        has_invoice: boolean;
        invoice_url: string | null;
    };
    carriers: Record<string, string>;
}>();

const handleSubmit = (form: InertiaForm<Record<string, unknown>>) => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(update(props.preAlert.id).url, {
            forceFormData: true,
        });
};
</script>

<template>
    <Head title="Edit pre-alert" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Edit pre-alert</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                You can edit this pre-alert while it is still
                <strong>Submitted</strong> or <strong>Under Review</strong>.
            </p>
        </div>

        <Card class="max-w-3xl">
            <CardHeader>
                <CardTitle>{{ preAlert.merchant_name }}</CardTitle>
            </CardHeader>
            <CardContent>
                <PreAlertForm
                    :carriers="carriers"
                    :initial="preAlert"
                    submit-label="Save changes"
                    @submit="handleSubmit"
                />
            </CardContent>
        </Card>
    </div>
</template>
