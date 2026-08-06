<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, index, store } from '@/routes/admin/customers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Customers', href: index() },
            { title: 'Add', href: create() },
        ],
    },
});
</script>

<template>
    <Head title="Admin · Add customer" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold tracking-tight">Add customer</h1>

        <Form
            v-bind="store.form()"
            class="max-w-2xl space-y-4"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" required />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" name="email" type="email" required />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input id="password" name="password" type="password" required />
                <InputError :message="errors.password" />
            </div>
            <div class="grid gap-2">
                <Label for="trn">TRN</Label>
                <Input id="trn" name="trn" required />
                <InputError :message="errors.trn" />
            </div>
            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input id="phone" name="phone" required />
                <InputError :message="errors.phone" />
            </div>
            <div class="grid gap-2">
                <Label for="jamaica_address">Address</Label>
                <textarea
                    id="jamaica_address"
                    name="jamaica_address"
                    rows="3"
                    required
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                />
                <InputError :message="errors.jamaica_address" />
            </div>
            <div class="grid gap-2">
                <Label for="date_of_birth">Date of birth</Label>
                <Input id="date_of_birth" name="date_of_birth" type="date" required />
                <InputError :message="errors.date_of_birth" />
            </div>
            <div class="grid gap-2">
                <Label for="parish">Parish</Label>
                <Input id="parish" name="parish" />
            </div>
            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <select
                    id="status"
                    name="status"
                    class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="flex gap-3">
                <Button
                    type="submit"
                    class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                    :disabled="processing"
                >
                    Create customer
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
