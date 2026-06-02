<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, index, store } from '@/routes/admin/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Packages', href: index() },
            { title: 'Add', href: create() },
        ],
    },
});

const props = defineProps<{
    customers: Array<{ id: number; label: string }>;
    preAlerts: Array<{ id: number; user_id: number; label: string }>;
    carriers: Record<string, string>;
    statuses: Record<string, string>;
    paymentStatuses: Record<string, string>;
    defaultUserId: number | null;
    defaultPreAlertId: number | null;
    currency: string;
}>();

const form = useForm({
    user_id: props.defaultUserId ?? ('' as number | ''),
    pre_alert_id: props.defaultPreAlertId ?? ('' as number | ''),
    tracking_number: '',
    merchant_name: '',
    carrier: 'usps',
    weight_lbs: '',
    declared_value: '',
    amount_due: '',
    auto_calculate_amount: true,
    status: 'awaiting_arrival',
    payment_status: 'unpaid',
    customer_visible_notes: '',
    admin_notes: '',
});

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Admin · Add package" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold tracking-tight">Add package</h1>

        <form class="max-w-2xl space-y-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label>Customer</Label>
                <select
                    v-model="form.user_id"
                    class="h-9 w-full rounded-md border px-3 text-sm"
                    required
                >
                    <option value="" disabled>Select customer</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">
                        {{ c.label }}
                    </option>
                </select>
                <InputError :message="form.errors.user_id" />
            </div>

            <div class="grid gap-2">
                <Label>Link pre-alert (optional)</Label>
                <select v-model="form.pre_alert_id" class="h-9 w-full rounded-md border px-3 text-sm">
                    <option value="">None</option>
                    <option
                        v-for="pa in preAlerts.filter(
                            (p) => !form.user_id || p.user_id === Number(form.user_id),
                        )"
                        :key="pa.id"
                        :value="pa.id"
                    >
                        {{ pa.label }}
                    </option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label>Merchant</Label>
                    <Input v-model="form.merchant_name" />
                </div>
                <div class="grid gap-2">
                    <Label>Tracking</Label>
                    <Input v-model="form.tracking_number" />
                </div>
                <div class="grid gap-2">
                    <Label>Carrier</Label>
                    <select v-model="form.carrier" class="h-9 w-full rounded-md border px-3 text-sm">
                        <option v-for="(l, v) in carriers" :key="v" :value="v">{{ l }}</option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label>Weight (lb)</Label>
                    <Input v-model="form.weight_lbs" type="number" step="0.01" min="0" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.auto_calculate_amount" type="checkbox" />
                Auto-calculate amount due from active rate
            </label>

            <div class="grid gap-2" v-if="!form.auto_calculate_amount">
                <Label>Amount due ({{ currency }})</Label>
                <Input v-model="form.amount_due" type="number" step="0.01" min="0" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label>Package status</Label>
                    <select v-model="form.status" class="h-9 w-full rounded-md border px-3 text-sm">
                        <option v-for="(l, v) in statuses" :key="v" :value="v">{{ l }}</option>
                    </select>
                </div>
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
            </div>

            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    :disabled="form.processing"
                >
                    Create package
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
