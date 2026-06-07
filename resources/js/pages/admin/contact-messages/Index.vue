<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Eye,
    Inbox,
    Mail,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { dashboard as adminDashboard } from '@/routes/admin';
import { destroy, index, show } from '@/routes/admin/contact-messages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Contact inbox', href: index() },
        ],
    },
});

type MessageRow = {
    id: number;
    name: string;
    email: string;
    subject: string;
    status: string;
    status_label: string;
    created_at: string | null;
};

const props = defineProps<{
    messages: {
        data: MessageRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { status: string | null; search: string | null };
    statuses: Record<string, string>;
    counts: { total: number; new: number; read: number; resolved: number };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        index().url,
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true },
    );
};

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Admin · Contact inbox" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Contact inbox
                </h1>
                <p class="text-sm text-muted-foreground">
                    Review and respond to messages from the public contact form.
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-muted"
                    >
                        <Inbox class="size-5 text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ counts.total }}</p>
                        <p class="text-xs text-muted-foreground">Total</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-brand-gold/15"
                    >
                        <Mail class="size-5 text-brand-ink" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ counts.new }}</p>
                        <p class="text-xs text-muted-foreground">New</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-muted"
                    >
                        <Eye class="size-5 text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ counts.read }}</p>
                        <p class="text-xs text-muted-foreground">Read</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-brand-green/15"
                    >
                        <CheckCircle2 class="size-5 text-brand-green" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">
                            {{ counts.resolved }}
                        </p>
                        <p class="text-xs text-muted-foreground">Resolved</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <form
            class="flex flex-wrap items-center gap-2"
            @submit.prevent="applyFilters"
        >
            <Input
                v-model="search"
                placeholder="Search name, email, or subject…"
                class="max-w-sm"
            />
            <select
                v-model="status"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
            >
                <option value="">All statuses</option>
                <option
                    v-for="(label, value) in statuses"
                    :key="value"
                    :value="value"
                >
                    {{ label }}
                </option>
            </select>
            <Button type="submit" variant="outline">
                <Search class="size-4" />
                Filter
            </Button>
        </form>

        <Card v-if="messages.data.length">
            <CardHeader>
                <CardTitle>Messages</CardTitle>
                <CardDescription>
                    Open a message to triage, reply, or add internal notes.
                </CardDescription>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4 font-medium">Subject</th>
                            <th class="pb-3 pr-4 font-medium">From</th>
                            <th class="pb-3 pr-4 font-medium">Status</th>
                            <th class="pb-3 pr-4 font-medium">Received</th>
                            <th class="pb-3 font-medium text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="msg in messages.data"
                            :key="msg.id"
                            class="border-b border-border/60 last:border-0"
                        >
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(msg.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ msg.subject }}
                                </Link>
                            </td>
                            <td class="py-3 pr-4">
                                <p class="font-medium">{{ msg.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ msg.email }}
                                </p>
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="msg.status"
                                    :label="msg.status_label"
                                />
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ formatDate(msg.created_at) }}
                            </td>
                            <td class="py-3">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button as-child variant="ghost" size="sm">
                                        <Link :href="show(msg.id)">
                                            <Eye class="size-4" />
                                            View
                                        </Link>
                                    </Button>
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                            >
                                                <Trash2 class="size-4" />
                                                Delete
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Delete message?
                                                </DialogTitle>
                                                <DialogDescription>
                                                    This permanently removes
                                                    "{{ msg.subject }}" from
                                                    {{ msg.name }}. This cannot
                                                    be undone.
                                                </DialogDescription>
                                            </DialogHeader>
                                            <DialogFooter>
                                                <DialogClose as-child>
                                                    <Button variant="outline">
                                                        Cancel
                                                    </Button>
                                                </DialogClose>
                                                <Form
                                                    v-bind="
                                                        destroy.form(msg.id)
                                                    "
                                                >
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                    >
                                                        Delete message
                                                    </Button>
                                                </Form>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Card v-else>
            <CardContent>
                <EmptyState
                    :icon="Inbox"
                    title="No messages found"
                    description="Try adjusting your filters or check back when someone submits the contact form."
                />
            </CardContent>
        </Card>

        <PaginationLinks :links="messages.links" />
    </div>
</template>
