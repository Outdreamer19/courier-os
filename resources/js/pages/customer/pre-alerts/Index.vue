<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, PlusCircle, Receipt, XCircle } from 'lucide-vue-next';
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
import { dashboard } from '@/routes';
import { cancel, create, edit, index, show } from '@/routes/portal/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pre-alerts', href: index() },
        ],
    },
});

type PreAlertRow = {
    id: number;
    merchant_name: string;
    tracking_number: string | null;
    status: string;
    status_label: string;
    expected_delivery_date: string | null;
    created_at: string | null;
    has_invoice: boolean;
    is_editable: boolean;
    is_cancellable: boolean;
};

defineProps<{
    preAlerts: {
        data: PreAlertRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
}>();

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString();
};
</script>

<template>
    <Head title="Pre-alerts" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Pre-alerts
                </h1>
                <p class="text-sm text-muted-foreground">
                    Create, edit, and cancel pre-alerts before your packages
                    arrive at our Florida warehouse.
                </p>
            </div>
            <Button
                as-child
                class="bg-brand-ink text-white hover:bg-brand-ink-soft"
            >
                <Link :href="create()">
                    <PlusCircle class="size-4" />
                    Submit pre-alert
                </Link>
            </Button>
        </div>

        <Card v-if="preAlerts.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="880px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="26%">
                            Merchant
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="18%">
                            Tracking
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="20%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="14%">
                            Expected
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="22%" align="right">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow
                            v-for="row in preAlerts.data"
                            :key="row.id"
                        >
                            <DataTableCell>
                                <Link
                                    :href="show(row.id)"
                                    class="block truncate font-medium hover:underline"
                                >
                                    {{ row.merchant_name }}
                                </Link>
                                <div
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ formatDate(row.created_at) }}
                                    <span v-if="row.has_invoice"
                                        >· invoice</span
                                    >
                                </div>
                            </DataTableCell>
                            <DataTableCell muted class="truncate tabular-nums">
                                {{ row.tracking_number ?? '—' }}
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="row.status"
                                    :label="row.status_label"
                                />
                            </DataTableCell>
                            <DataTableCell
                                muted
                                class="truncate text-xs tabular-nums"
                            >
                                {{ formatDate(row.expected_delivery_date) }}
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
                                            :href="show(row.id)"
                                            title="View pre-alert"
                                        >
                                            <Eye class="size-4" />
                                            <span class="sr-only">View</span>
                                        </Link>
                                    </Button>
                                    <Button
                                        v-if="row.is_editable"
                                        as-child
                                        variant="outline"
                                        size="icon-sm"
                                        class="text-muted-foreground hover:border-brand-ink/50 hover:bg-brand-ink/10 hover:text-brand-ink dark:hover:bg-brand-ink/25 dark:hover:text-brand-cream"
                                    >
                                        <Link
                                            :href="edit(row.id)"
                                            title="Edit pre-alert"
                                        >
                                            <Pencil class="size-4" />
                                            <span class="sr-only">Edit</span>
                                        </Link>
                                    </Button>
                                    <Dialog v-if="row.is_cancellable">
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="outline"
                                                size="icon-sm"
                                                class="text-muted-foreground hover:border-destructive/50 hover:bg-destructive/10 hover:text-destructive"
                                                title="Cancel pre-alert"
                                            >
                                                <XCircle class="size-4" />
                                                <span class="sr-only">
                                                    Cancel
                                                </span>
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Cancel this pre-alert?
                                                </DialogTitle>
                                                <DialogDescription>
                                                    Your pre-alert for
                                                    {{ row.merchant_name }} will
                                                    be marked as cancelled.
                                                </DialogDescription>
                                            </DialogHeader>
                                            <DialogFooter>
                                                <DialogClose as-child>
                                                    <Button variant="outline">
                                                        Keep pre-alert
                                                    </Button>
                                                </DialogClose>
                                                <Form
                                                    v-bind="cancel.form(row.id)"
                                                >
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                    >
                                                        Yes, cancel
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
                :links="preAlerts.links"
                :from="preAlerts.from"
                :to="preAlerts.to"
                :total="preAlerts.total"
                noun="pre-alerts"
            />
        </Card>

        <Card v-else>
            <CardContent>
                <EmptyState
                    :icon="Receipt"
                    title="No pre-alerts yet"
                    description="Submit a pre-alert with your invoice so we can match incoming packages to your account."
                >
                    <Button
                        as-child
                        class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                    >
                        <Link :href="create()"
                            >Submit your first pre-alert</Link
                        >
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>
    </div>
</template>
