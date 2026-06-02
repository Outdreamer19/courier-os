<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit, index, show, update } from '@/routes/admin/customers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Customers', href: index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    customer: {
        id: number;
        name: string;
        email: string;
        status: string;
        phone: string | null;
        whatsapp_number: string | null;
        jamaica_address: string | null;
        parish: string | null;
        customer_reference: string;
    };
}>();
</script>

<template>
    <Head title="Admin · Edit customer" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Edit customer</h1>
            <p class="text-sm text-muted-foreground">
                Reference: {{ customer.customer_reference }}
            </p>
        </div>

        <Form
            v-bind="update.form(customer.id)"
            class="max-w-2xl space-y-4"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" :default-value="customer.name" required />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="customer.email"
                    required
                />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    name="phone"
                    :default-value="customer.phone ?? ''"
                />
            </div>
            <div class="grid gap-2">
                <Label for="whatsapp_number">WhatsApp</Label>
                <Input
                    id="whatsapp_number"
                    name="whatsapp_number"
                    :default-value="customer.whatsapp_number ?? ''"
                />
            </div>
            <div class="grid gap-2">
                <Label for="parish">Parish</Label>
                <Input
                    id="parish"
                    name="parish"
                    :default-value="customer.parish ?? ''"
                />
            </div>
            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <select
                    id="status"
                    name="status"
                    class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                    :default-value="customer.status"
                >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    :disabled="processing"
                >
                    Save changes
                </Button>
                <Button as-child variant="outline">
                    <Link :href="show(customer.id)">Back</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
