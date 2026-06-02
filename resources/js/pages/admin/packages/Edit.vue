<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import WhatsAppButton from '@/components/WhatsAppButton.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit, index, update } from '@/routes/admin/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Packages', href: index() },
            { title: 'Manage', href: '#' },
        ],
    },
});

const props = defineProps<{
    package: Record<string, unknown> & {
        id: number;
        package_reference: string;
        user_id: number;
        pre_alert_id: number | null;
        carrier: string | null;
        weight_lbs: number | null;
        amount_due: number;
        status: string;
        payment_status: string;
        payment_method: string | null;
        admin_notes: string | null;
        customer_visible_notes: string | null;
    };
    preAlerts: Array<{ id: number; label: string }>;
    carriers: Record<string, string>;
    statuses: Record<string, string>;
    paymentStatuses: Record<string, string>;
    paymentMethods: Record<string, string>;
    currency: string;
}>();

const form = useForm({
    pre_alert_id: props.package.pre_alert_id ?? '',
    tracking_number: props.package.tracking_number ?? '',
    merchant_name: props.package.merchant_name ?? '',
    carrier: props.package.carrier ?? 'usps',
    weight_lbs: props.package.weight_lbs?.toString() ?? '',
    declared_value: '',
    amount_due: props.package.amount_due.toString(),
    auto_calculate_amount: false,
    status: props.package.status,
    payment_status: props.package.payment_status,
    payment_method: props.package.payment_method ?? '',
    payment_notes: '',
    customer_visible_notes: props.package.customer_visible_notes ?? '',
    admin_notes: props.package.admin_notes ?? '',
});

const submit = () => {
    form.put(update(props.package.id).url);
};
</script>

<template>
    <Head :title="`Package · ${package.package_reference}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ package.package_reference }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ package.customer_name }} · {{ package.customer_reference }}
                </p>
            </div>
            <WhatsAppButton :url="package.whatsapp_url" />
        </div>

        <form class="max-w-2xl space-y-6" @submit.prevent="submit">
            <section class="space-y-4 rounded-lg border p-4">
                <h2 class="font-medium">Package details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label>Pre-alert</Label>
                        <select
                            v-model="form.pre_alert_id"
                            class="h-9 w-full rounded-md border px-3 text-sm"
                        >
                            <option value="">None</option>
                            <option v-for="pa in preAlerts" :key="pa.id" :value="pa.id">
                                {{ pa.label }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Weight (lb)</Label>
                        <Input v-model="form.weight_lbs" type="number" step="0.01" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Amount due ({{ currency }})</Label>
                        <Input v-model="form.amount_due" type="number" step="0.01" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.auto_calculate_amount" type="checkbox" />
                    Recalculate amount from weight when saving
                </label>
                <div class="grid gap-2">
                    <Label>Status</Label>
                    <select v-model="form.status" class="h-9 w-full rounded-md border px-3 text-sm">
                        <option v-for="(l, v) in statuses" :key="v" :value="v">{{ l }}</option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label>Customer-visible notes</Label>
                    <textarea
                        v-model="form.customer_visible_notes"
                        rows="3"
                        class="w-full rounded-md border px-3 py-2 text-sm"
                    />
                </div>
            </section>

            <section class="space-y-4 rounded-lg border p-4">
                <h2 class="font-medium">Payment</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label>Payment status</Label>
                        <select
                            v-model="form.payment_status"
                            class="h-9 w-full rounded-md border px-3 text-sm"
                        >
                            <option v-for="(l, v) in paymentStatuses" :key="v" :value="v">
                                {{ l }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Payment method</Label>
                        <select
                            v-model="form.payment_method"
                            class="h-9 w-full rounded-md border px-3 text-sm"
                        >
                            <option value="">—</option>
                            <option v-for="(l, v) in paymentMethods" :key="v" :value="v">
                                {{ l }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label>Payment note (appended to admin notes)</Label>
                    <Input
                        v-model="form.payment_notes"
                        placeholder="e.g. Paid in person at pickup"
                    />
                </div>
            </section>

            <section class="space-y-4 rounded-lg border p-4">
                <h2 class="font-medium">Internal notes</h2>
                <textarea
                    v-model="form.admin_notes"
                    rows="4"
                    class="w-full rounded-md border px-3 py-2 text-sm"
                />
            </section>

            <section
                v-if="package.status_history.length"
                class="space-y-3 rounded-lg border p-4"
            >
                <h2 class="font-medium">Status history</h2>
                <ol class="space-y-2 text-sm">
                    <li
                        v-for="entry in package.status_history"
                        :key="entry.id"
                        class="border-l-2 border-brand-gold/40 pl-3"
                    >
                        <p class="font-medium">
                            {{ entry.old_status_label ?? 'Created' }}
                            → {{ entry.new_status_label }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ entry.changed_by ?? 'System' }}
                            <span v-if="entry.created_at">
                                ·
                                {{
                                    new Date(entry.created_at).toLocaleString()
                                }}
                            </span>
                        </p>
                    </li>
                </ol>
            </section>

            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    :disabled="form.processing"
                >
                    Save package
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Back to list</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
