<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit, index, update } from '@/routes/admin/admin-users';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Admin users', href: index() },
            { title: 'Edit', href: '#' },
        ],
    },
});

defineProps<{
    admin: {
        id: number;
        name: string;
        email: string;
        role: string;
        status: string;
    };
    roles: Record<string, string>;
    canChangeRole: boolean;
}>();
</script>

<template>
    <Head title="Edit admin user" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold">Edit admin user</h1>

        <Form
            v-bind="update.form(admin.id)"
            class="max-w-2xl space-y-4"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" :default-value="admin.name" required />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="admin.email"
                    required
                />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="password">New password</Label>
                <Input id="password" name="password" type="password" />
                <InputError :message="errors.password" />
            </div>
            <div v-if="canChangeRole" class="grid gap-2">
                <Label for="role">Role</Label>
                <select
                    id="role"
                    name="role"
                    class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                    :default-value="admin.role"
                >
                    <option
                        v-for="(label, value) in roles"
                        :key="value"
                        :value="value"
                        :disabled="value === 'owner' && admin.role !== 'owner'"
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
                    :default-value="admin.status"
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
                    Save changes
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Back</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
