<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InvoiceDropZone from '@/components/customer/InvoiceDropZone.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    carriers: Record<string, string>;
    initial?: {
        merchant_name: string;
        order_number: string | null;
        tracking_number: string | null;
        carrier: string;
        expected_delivery_date: string | null;
        item_description: string;
        declared_value: number | null;
        customer_notes: string | null;
        has_invoice?: boolean;
        invoice_url?: string | null;
    };
    submitLabel: string;
    processingLabel?: string;
}>();

const emit = defineEmits<{
    submit: [form: ReturnType<typeof useForm>];
}>();

const form = useForm({
    merchant_name: props.initial?.merchant_name ?? '',
    order_number: props.initial?.order_number ?? '',
    tracking_number: props.initial?.tracking_number ?? '',
    carrier: props.initial?.carrier ?? 'amazon_logistics',
    expected_delivery_date: props.initial?.expected_delivery_date ?? '',
    item_description: props.initial?.item_description ?? '',
    declared_value: props.initial?.declared_value?.toString() ?? '',
    customer_notes: props.initial?.customer_notes ?? '',
    invoice: null as File | null,
});

const submit = () => {
    emit('submit', form);
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2 sm:col-span-2">
                <Label for="merchant_name">Merchant / store name *</Label>
                <Input
                    id="merchant_name"
                    v-model="form.merchant_name"
                    required
                    placeholder="Amazon, Walmart, SHEIN…"
                />
                <InputError :message="form.errors.merchant_name" />
            </div>

            <div class="grid gap-2">
                <Label for="order_number">Order number</Label>
                <Input id="order_number" v-model="form.order_number" />
                <InputError :message="form.errors.order_number" />
            </div>

            <div class="grid gap-2">
                <Label for="tracking_number">Tracking number</Label>
                <Input id="tracking_number" v-model="form.tracking_number" />
                <InputError :message="form.errors.tracking_number" />
            </div>

            <div class="grid gap-2">
                <Label for="carrier">Carrier *</Label>
                <select
                    id="carrier"
                    v-model="form.carrier"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    required
                >
                    <option
                        v-for="(label, value) in carriers"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
                <InputError :message="form.errors.carrier" />
            </div>

            <div class="grid gap-2">
                <Label for="expected_delivery_date">Expected delivery date</Label>
                <Input
                    id="expected_delivery_date"
                    v-model="form.expected_delivery_date"
                    type="date"
                />
                <InputError :message="form.errors.expected_delivery_date" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="item_description">Item description *</Label>
                <textarea
                    id="item_description"
                    v-model="form.item_description"
                    rows="4"
                    required
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    placeholder="Briefly describe what you ordered"
                />
                <InputError :message="form.errors.item_description" />
            </div>

            <div class="grid gap-2">
                <Label for="declared_value">Declared value (USD)</Label>
                <Input
                    id="declared_value"
                    v-model="form.declared_value"
                    type="number"
                    min="0"
                    step="0.01"
                />
                <InputError :message="form.errors.declared_value" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="customer_notes">Notes</Label>
                <textarea
                    id="customer_notes"
                    v-model="form.customer_notes"
                    rows="3"
                    class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                />
                <InputError :message="form.errors.customer_notes" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label>Invoice / receipt</Label>
                <InvoiceDropZone
                    v-model="form.invoice"
                    :error="form.errors.invoice"
                />
                <p
                    v-if="initial?.has_invoice && initial.invoice_url"
                    class="text-sm text-muted-foreground"
                >
                    An invoice is already on file.
                    <a
                        :href="initial.invoice_url"
                        class="font-medium text-foreground underline"
                        target="_blank"
                        rel="noopener"
                    >
                        View current file
                    </a>
                    — drop a new file above to replace it.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <Button
                type="submit"
                class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                :disabled="form.processing"
            >
                {{ form.processing ? (processingLabel ?? 'Saving…') : submitLabel }}
            </Button>
        </div>
    </form>
</template>
