<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index, update } from '@/routes/admin/shipping-rates';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Rates', href: index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const props = defineProps<{
    rate: {
        id: number;
        name: string;
        method: string;
        currency: string;
        rate_per_lb: number;
        minimum_charge: number;
        handling_fee: number | null;
        min_weight_lbs: number | null;
        max_weight_lbs: number | null;
        is_active: boolean;
    };
}>();
</script>

<template>
    <Head title="Edit shipping rate" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Edit rate</h1>

        <Form
            v-bind="update.form(rate.id)"
            class="max-w-xl space-y-4"
            v-slot="{ processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" :default-value="rate.name" required />
            </div>
            <div class="grid gap-2">
                <Label for="method">Method</Label>
                <Input id="method" name="method" :default-value="rate.method" required />
            </div>
            <div class="grid gap-2">
                <Label for="currency">Currency</Label>
                <Input id="currency" name="currency" :default-value="rate.currency" required />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="rate_per_lb">Rate per lb</Label>
                    <Input
                        id="rate_per_lb"
                        name="rate_per_lb"
                        type="number"
                        step="0.01"
                        :default-value="rate.rate_per_lb"
                        required
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="minimum_charge">Minimum</Label>
                    <Input
                        id="minimum_charge"
                        name="minimum_charge"
                        type="number"
                        step="0.01"
                        :default-value="rate.minimum_charge"
                        required
                    />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="min_weight_lbs">Min weight</Label>
                    <Input
                        id="min_weight_lbs"
                        name="min_weight_lbs"
                        type="number"
                        step="0.1"
                        :default-value="rate.min_weight_lbs ?? ''"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="max_weight_lbs">Max weight</Label>
                    <Input
                        id="max_weight_lbs"
                        name="max_weight_lbs"
                        type="number"
                        step="0.1"
                        :default-value="rate.max_weight_lbs ?? ''"
                    />
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0" />
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    :checked="rate.is_active"
                />
                Active
            </label>
            <Button
                type="submit"
                class="bg-brand-gold text-white"
                :disabled="processing"
            >
                Save changes
            </Button>
        </Form>
    </div>
</template>
