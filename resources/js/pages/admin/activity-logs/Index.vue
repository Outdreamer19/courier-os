<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index } from '@/routes/admin/activity-logs';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Activity logs', href: index() },
        ],
    },
});

const props = defineProps<{
    logs: {
        data: Array<{
            id: number;
            action_label: string;
            description: string;
            created_at: string | null;
            user: { id: number; name: string; email: string; role: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        action: string | null;
        user_id: number | null;
        date_from: string | null;
        date_to: string | null;
        search: string | null;
    };
    actions: Record<string, string>;
    users: Array<{ id: number; label: string }>;
}>();

const search = ref(props.filters.search ?? '');
const action = ref(props.filters.action ?? '');
const userId = ref(props.filters.user_id?.toString() ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(
        index().url,
        {
            search: search.value || undefined,
            action: action.value || undefined,
            user_id: userId.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        { preserveState: true },
    );
};

const formatDate = (value: string | null) => {
    if (!value) return '—';
    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head title="Activity logs" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold">Activity logs</h1>
            <p class="text-sm text-muted-foreground">
                Review important actions across the platform.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Filters</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="grid gap-4 md:grid-cols-2 xl:grid-cols-5" @submit.prevent="applyFilters">
                    <div class="grid gap-2">
                        <Label for="search">Search</Label>
                        <Input id="search" v-model="search" placeholder="Description or user" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="action">Action type</Label>
                        <select
                            id="action"
                            v-model="action"
                            class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                        >
                            <option value="">All actions</option>
                            <option
                                v-for="(label, value) in actions"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="user_id">User</Label>
                        <select
                            id="user_id"
                            v-model="userId"
                            class="flex h-9 w-full rounded-md border border-input px-3 text-sm"
                        >
                            <option value="">All users</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id.toString()"
                            >
                                {{ user.label }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="date_from">From</Label>
                        <Input id="date_from" v-model="dateFrom" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="date_to">To</Label>
                        <Input id="date_to" v-model="dateTo" type="date" />
                    </div>
                    <div class="md:col-span-2 xl:col-span-5">
                        <Button type="submit">Apply filters</Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Recent activity</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    class="rounded-md border p-4"
                >
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium">{{ log.description }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ log.action_label }}
                                <span v-if="log.user">
                                    · {{ log.user.name }} ({{ log.user.email }})
                                </span>
                            </p>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDate(log.created_at) }}
                        </p>
                    </div>
                </div>
                <p v-if="!logs.data.length" class="text-sm text-muted-foreground">
                    No activity found for the selected filters.
                </p>
                <PaginationLinks :links="logs.links" />
            </CardContent>
        </Card>
    </div>
</template>
