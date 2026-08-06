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
import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableFooter,
    DataTableHeader,
    DataTableHeaderCell,
    DataTableRow,
} from '@/components/data-table';
import EmptyState from '@/components/EmptyState.vue';
import InitialsAvatar from '@/components/InitialsAvatar.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
        from: number | null;
        to: number | null;
        total: number;
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
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
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
                        class="flex size-10 items-center justify-center rounded-lg bg-brand-ink/10"
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

        <Card v-if="messages.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="900px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="32%">
                            Subject
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="26%">
                            From
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="14%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="16%">
                            Received
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="12%" align="right">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="msg in messages.data"
                            :key="msg.id"
                        >
                            <DataTableCell>
                                <Link
                                    :href="show(msg.id)"
                                    class="block truncate font-medium hover:underline"
                                >
                                    {{ msg.subject }}
                                </Link>
                            </DataTableCell>
                            <DataTableCell>
                                <div class="flex items-center gap-3">
                                    <InitialsAvatar :name="msg.name" />
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ msg.name }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{ msg.email }}
                                        </p>
                                    </div>
                                </div>
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="msg.status"
                                    :label="msg.status_label"
                                />
                            </DataTableCell>
                            <DataTableCell
                                muted
                                class="truncate text-xs tabular-nums"
                            >
                                {{ formatDate(msg.created_at) }}
                            </DataTableCell>
                            <DataTableCell>
                                <div
                                    class="flex items-center justify-end gap-1 opacity-70 transition-opacity group-hover:opacity-100"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="icon-sm"
                                        class="text-muted-foreground hover:border-brand-ink/50 hover:bg-brand-ink/10 hover:text-brand-ink dark:hover:bg-brand-ink/25 dark:hover:text-brand-cream"
                                    >
                                        <Link
                                            :href="show(msg.id)"
                                            :title="`View ${msg.subject}`"
                                        >
                                            <Eye class="size-4" />
                                            <span class="sr-only">View</span>
                                        </Link>
                                    </Button>
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="outline"
                                                size="icon-sm"
                                                class="text-muted-foreground hover:border-destructive/50 hover:bg-destructive/10 hover:text-destructive"
                                                :title="`Delete ${msg.subject}`"
                                            >
                                                <Trash2 class="size-4" />
                                                <span class="sr-only">
                                                    Delete
                                                </span>
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Delete message?
                                                </DialogTitle>
                                                <DialogDescription>
                                                    This permanently removes "{{
                                                        msg.subject
                                                    }}" from {{ msg.name }}.
                                                    This cannot be undone.
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
                            </DataTableCell>
                        </DataTableRow>
                    </DataTableBody>
                </DataTable>
            </CardContent>

            <DataTableFooter
                :links="messages.links"
                :from="messages.from"
                :to="messages.to"
                :total="messages.total"
                noun="messages"
            />
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
    </div>
</template>
