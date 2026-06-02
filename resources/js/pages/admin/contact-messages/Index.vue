<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index, show } from '@/routes/admin/contact-messages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Contact inbox', href: index() },
        ],
    },
});

const props = defineProps<{
    messages: { data: Array<Record<string, unknown>> };
    filters: { status: string | null };
    statuses: Record<string, string>;
}>();

const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(index().url, { status: status.value || undefined }, { preserveState: true });
};
</script>

<template>
    <Head title="Admin · Contact inbox" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <h1 class="text-2xl font-semibold tracking-tight">Contact inbox</h1>

        <form class="flex gap-2" @submit.prevent="applyFilters">
            <select v-model="status" class="h-9 rounded-md border px-3 text-sm">
                <option value="">All</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
            <Button type="submit" variant="outline">Filter</Button>
        </form>

        <Card>
            <CardContent class="divide-y pt-6">
                <Link
                    v-for="msg in messages.data"
                    :key="msg.id as number"
                    :href="show(msg.id as number)"
                    class="flex items-center justify-between gap-4 py-4 text-sm hover:bg-muted/30"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ msg.subject }}</p>
                        <p class="truncate text-muted-foreground">
                            {{ msg.name }} · {{ msg.email }}
                        </p>
                    </div>
                    <StatusBadge
                        :status="msg.status as string"
                        :label="msg.status as string"
                    />
                </Link>
            </CardContent>
        </Card>
    </div>
</template>
