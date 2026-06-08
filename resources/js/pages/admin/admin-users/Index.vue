<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PaginationLinks from '@/components/PaginationLinks.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/admin-users';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Admin users', href: index() },
        ],
    },
});

defineProps<{
    admins: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: string;
            role_label: string;
            status: string;
            created_at: string | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    roles: Record<string, string>;
}>();
</script>

<template>
    <Head title="Admin users" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Admin users</h1>
                <p class="text-sm text-muted-foreground">
                    Manage who can access the admin portal and their roles.
                </p>
            </div>
            <Button as-child class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft">
                <Link :href="create()">Add admin user</Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Team members</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="admin in admins.data"
                    :key="admin.id"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-md border p-4"
                >
                    <div>
                        <p class="font-medium">{{ admin.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ admin.email }}</p>
                        <p class="text-sm text-muted-foreground">{{ admin.role_label }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <StatusBadge :status="admin.status" :label="admin.status" />
                        <Button as-child size="sm" variant="outline">
                            <Link :href="edit(admin.id)">Edit</Link>
                        </Button>
                    </div>
                </div>
                <p v-if="!admins.data.length" class="text-sm text-muted-foreground">
                    No admin users found.
                </p>
                <PaginationLinks :links="admins.links" />
            </CardContent>
        </Card>
    </div>
</template>
