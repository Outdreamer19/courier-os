<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, index, store } from '@/routes/admin/admin-users';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Admin users', href: index() },
            { title: 'Add', href: create() },
        ],
    },
});

defineProps<{
    roles: Record<string, string>;
}>();
</script>

<template>
    <Head title="Add admin user" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Add admin user</h1>

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
                <Label for="role">Role</Label>
                <select
                    id="role"
                    name="role"
                    class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                    required
                >
                    <option
                        v-for="(label, value) in roles"
                        :key="value"
                        :value="value"
                        :disabled="value === 'owner'"
                    >
                        {{ label }}
                    </option>
                </select>
                <InputError :message="errors.role" />
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
                    class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                    :disabled="processing"
                >
                    Create admin user
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
