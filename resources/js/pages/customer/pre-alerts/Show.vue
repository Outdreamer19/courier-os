<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Pencil, XCircle } from 'lucide-vue-next';
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
import { cancel, edit, index } from '@/routes/portal/pre-alerts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pre-alerts', href: index() },
            { title: 'Details', href: '#' },
        ],
    },
});

const props = defineProps<{
    preAlert: {
        id: number;
        merchant_name: string;
        order_number: string | null;
        tracking_number: string | null;
        carrier_label: string;
        status: string;
        status_label: string;
        expected_delivery_date: string | null;
        item_description: string;
        declared_value: number | null;
        customer_notes: string | null;
        is_editable: boolean;
        is_cancellable: boolean;
        invoice_url: string | null;
        created_at: string | null;
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
    <Head :title="`Pre-alert — ${preAlert.merchant_name}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ preAlert.merchant_name }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <StatusBadge
                        :status="preAlert.status"
                        :label="preAlert.status_label"
                    />
                    <span class="text-sm text-muted-foreground">
                        Submitted {{ formatDate(preAlert.created_at) }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="preAlert.is_editable"
                    as-child
                    variant="outline"
                >
                    <Link :href="edit(preAlert.id)">
                        <Pencil class="size-4" />
                        Edit pre-alert
                    </Link>
                </Button>
                <Dialog v-if="preAlert.is_cancellable">
                    <DialogTrigger as-child>
                        <Button variant="outline" class="text-destructive hover:text-destructive">
                            <XCircle class="size-4" />
                            Cancel pre-alert
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Cancel this pre-alert?</DialogTitle>
                            <DialogDescription>
                                Your pre-alert for
                                {{ preAlert.merchant_name }} will be marked as
                                cancelled. You can submit a new pre-alert later
                                if your shipment is still on the way.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline">Keep pre-alert</Button>
                            </DialogClose>
                            <Form v-bind="cancel.form(preAlert.id)">
                                <Button type="submit" variant="destructive">
                                    Yes, cancel
                                </Button>
                            </Form>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Shipment details</CardTitle>
                <CardDescription>
                    We use this information to match incoming packages to your
                    account.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">Order number</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ preAlert.order_number ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Tracking number</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ preAlert.tracking_number ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Carrier</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ preAlert.carrier_label }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Expected delivery</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ formatDate(preAlert.expected_delivery_date) }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-muted-foreground">Item description</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ preAlert.item_description }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Declared value</dt>
                        <dd class="mt-0.5 font-medium">
                            {{
                                preAlert.declared_value != null
                                    ? `$${preAlert.declared_value}`
                                    : '—'
                            }}
                        </dd>
                    </div>
                    <div v-if="preAlert.customer_notes" class="sm:col-span-2">
                        <dt class="text-muted-foreground">Your notes</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ preAlert.customer_notes }}
                        </dd>
                    </div>
                    <div v-if="preAlert.invoice_url" class="sm:col-span-2">
                        <dt class="text-muted-foreground">Invoice / receipt</dt>
                        <dd class="mt-0.5">
                            <a
                                :href="preAlert.invoice_url"
                                class="font-medium text-foreground underline"
                                target="_blank"
                                rel="noopener"
                            >
                                View uploaded file
                            </a>
                        </dd>
                    </div>
                </dl>
            </CardContent>
        </Card>
    </div>
</template>
