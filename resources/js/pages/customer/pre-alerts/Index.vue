<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, PlusCircle, Receipt, XCircle } from 'lucide-vue-next';
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Pre-alerts</h1>
                <p class="text-sm text-muted-foreground">
                    Create, edit, and cancel pre-alerts before your packages
                    arrive at our Florida warehouse.
                </p>
            </div>
            <Button
                as-child
                class="bg-brand-gold text-white hover:bg-brand-gold-soft"
            >
                <Link :href="create()">
                    <PlusCircle class="size-4" />
                    Submit pre-alert
                </Link>
            </Button>
        </div>

        <Card v-if="preAlerts.data.length">
            <CardHeader>
                <CardTitle>Your pre-alerts</CardTitle>
                <CardDescription>
                    View details, edit while under review, or cancel if plans
                    change.
                </CardDescription>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="border-b text-muted-foreground">
                            <th class="pb-3 pr-4 font-medium">Merchant</th>
                            <th class="pb-3 pr-4 font-medium">Tracking</th>
                            <th class="pb-3 pr-4 font-medium">Status</th>
                            <th class="pb-3 pr-4 font-medium">Expected</th>
                            <th class="pb-3 pr-4 font-medium">Submitted</th>
                            <th class="pb-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in preAlerts.data"
                            :key="row.id"
                            class="border-b border-border/60 last:border-0"
                        >
                            <td class="py-3 pr-4">
                                <Link
                                    :href="show(row.id)"
                                    class="font-medium hover:underline"
                                >
                                    {{ row.merchant_name }}
                                </Link>
                                <span
                                    v-if="row.has_invoice"
                                    class="ml-2 text-xs text-muted-foreground"
                                >
                                    · invoice
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ row.tracking_number ?? '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                <StatusBadge
                                    :status="row.status"
                                    :label="row.status_label"
                                />
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ formatDate(row.expected_delivery_date) }}
                            </td>
                            <td class="py-3 pr-4 text-muted-foreground">
                                {{ formatDate(row.created_at) }}
                            </td>
                            <td class="py-3">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button as-child variant="ghost" size="sm">
                                        <Link :href="show(row.id)">
                                            <Eye class="size-4" />
                                            View
                                        </Link>
                                    </Button>
                                    <Button
                                        v-if="row.is_editable"
                                        as-child
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Link :href="edit(row.id)">
                                            <Pencil class="size-4" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Dialog v-if="row.is_cancellable">
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                            >
                                                <XCircle class="size-4" />
                                                Cancel
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
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
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
                        class="bg-brand-gold text-white hover:bg-brand-gold-soft"
                    >
                        <Link :href="create()">Submit your first pre-alert</Link>
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>

        <PaginationLinks :links="preAlerts.links" />
    </div>
</template>
