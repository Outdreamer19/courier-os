<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index, update } from '@/routes/admin/warehouse';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Warehouse', href: index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const props = defineProps<{
    warehouse: {
        id: number;
        name: string;
        address_line_1: string;
        address_line_2: string | null;
        city: string;
        state: string;
        zip: string;
        phone: string | null;
        instructions: string | null;
        is_active: boolean;
    };
}>();
</script>

<template>
    <Head title="Edit warehouse address" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Edit warehouse address</h1>

        <Form
            v-bind="update.form(warehouse.id)"
            class="max-w-2xl space-y-4"
            v-slot="{ processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" :default-value="warehouse.name" required />
            </div>
            <div class="grid gap-2">
                <Label for="address_line_1">Address line 1</Label>
                <Input
                    id="address_line_1"
                    name="address_line_1"
                    :default-value="warehouse.address_line_1"
                    required
                />
            </div>
            <div class="grid gap-2">
                <Label for="address_line_2">Address line 2</Label>
                <Input
                    id="address_line_2"
                    name="address_line_2"
                    :default-value="warehouse.address_line_2 ?? ''"
                />
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2">
                    <Label for="city">City</Label>
                    <Input id="city" name="city" :default-value="warehouse.city" required />
                </div>
                <div class="grid gap-2">
                    <Label for="state">State</Label>
                    <Input id="state" name="state" :default-value="warehouse.state" required />
                </div>
                <div class="grid gap-2">
                    <Label for="zip">ZIP</Label>
                    <Input id="zip" name="zip" :default-value="warehouse.zip" required />
                </div>
            </div>
            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input id="phone" name="phone" :default-value="warehouse.phone ?? ''" />
            </div>
            <div class="grid gap-2">
                <Label for="instructions">Checkout instructions</Label>
                <textarea
                    id="instructions"
                    name="instructions"
                    rows="4"
                    class="w-full rounded-md border px-3 py-2 text-sm"
                    :default-value="warehouse.instructions ?? ''"
                />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0" />
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    :checked="warehouse.is_active"
                />
                Set as active warehouse (deactivates others)
            </label>
            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-ink text-white"
                    :disabled="processing"
                >
                    Save address
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Back</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
