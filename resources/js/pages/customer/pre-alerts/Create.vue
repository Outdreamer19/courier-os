<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import PreAlertForm from '@/components/customer/PreAlertForm.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { create, index, store } from '@/routes/portal/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pre-alerts', href: index() },
            { title: 'Submit', href: create() },
        ],
    },
});

defineProps<{
    carriers: Record<string, string>;
}>();

const handleSubmit = (form: InertiaForm<Record<string, unknown>>) => {
    form.post(store().url, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Submit pre-alert" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Submit a pre-alert
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                Upload your invoice or receipt so we know what package to expect
                at our Florida warehouse.
            </p>
        </div>

        <Card class="max-w-3xl">
            <CardHeader>
                <CardTitle>Pre-alert details</CardTitle>
                <CardDescription>
                    Fields marked with * are required.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <PreAlertForm
                    :carriers="carriers"
                    submit-label="Submit pre-alert"
                    processing-label="Submitting…"
                    @submit="handleSubmit"
                />
            </CardContent>
        </Card>
    </div>
</template>
