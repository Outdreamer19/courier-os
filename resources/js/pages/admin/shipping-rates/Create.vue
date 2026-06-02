<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, index, store } from '@/routes/admin/shipping-rates';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Rates', href: index() },
            { title: 'Add', href: create() },
        ],
    },
});

defineProps<{ currency: string }>();
</script>

<template>
    <Head title="Add shipping rate" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Add shipping rate</h1>

        <Form v-bind="store.form()" class="max-w-xl space-y-4" v-slot="{ processing }">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" required />
            </div>
            <div class="grid gap-2">
                <Label for="method">Method</Label>
                <Input id="method" name="method" value="standard" required />
            </div>
            <div class="grid gap-2">
                <Label for="currency">Currency</Label>
                <Input id="currency" name="currency" :default-value="currency" required />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="rate_per_lb">Rate per lb</Label>
                    <Input id="rate_per_lb" name="rate_per_lb" type="number" step="0.01" required />
                </div>
                <div class="grid gap-2">
                    <Label for="minimum_charge">Minimum charge</Label>
                    <Input id="minimum_charge" name="minimum_charge" type="number" step="0.01" required />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="min_weight_lbs">Min weight (lb)</Label>
                    <Input id="min_weight_lbs" name="min_weight_lbs" type="number" step="0.1" />
                </div>
                <div class="grid gap-2">
                    <Label for="max_weight_lbs">Max weight (lb)</Label>
                    <Input id="max_weight_lbs" name="max_weight_lbs" type="number" step="0.1" />
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0" />
                <input type="checkbox" name="is_active" value="1" checked />
                Active
            </label>
            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-gold text-brand-ink"
                    :disabled="processing"
                >
                    Save rate
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
