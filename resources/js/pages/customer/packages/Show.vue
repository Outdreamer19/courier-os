<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, Circle } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index } from '@/routes/portal/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Packages', href: index() },
            { title: 'Details', href: '#' },
        ],
    },
});

const props = defineProps<{
    package: {
        id: number;
        package_reference: string;
        merchant_name: string | null;
        tracking_number: string | null;
        carrier_label: string | null;
        status: string;
        status_label: string;
        payment_status: string;
        payment_status_label: string;
        payment_method_label: string | null;
        amount_due: number;
        weight_lbs: number | null;
        declared_value: number | null;
        paid_at: string | null;
        customer_visible_notes: string | null;
        billing_status_label: string | null;
        invoice_status: string | null;
        invoice_url: string | null;
        payment_url: string | null;
        is_paid: boolean;
        can_pay_online: boolean;
        timeline: Array<{
            label: string;
            at: string | null;
            complete: boolean;
        }>;
    };
    currency: string;
}>();

const formatMoney = (amount: number) => {
    return `${props.currency} $${amount.toLocaleString()}`;
};

const formatDateTime = (value: string | null) => {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleString();
};

const invoiceStatusLabel = () => {
    if (props.package.is_paid) {
        return 'Paid';
    }

    if (props.package.invoice_status) {
        return props.package.invoice_status
            .replace(/_/g, ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase());
    }

    return props.package.billing_status_label ?? 'Not invoiced';
};
</script>

<template>
    <Head :title="`Package ${package.package_reference}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ package.package_reference }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <StatusBadge
                    :status="package.status"
                    :label="package.status_label"
                />
                <StatusBadge
                    :status="package.payment_status"
                    :label="package.payment_status_label"
                />
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.2fr_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle>Package details</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Merchant</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ package.merchant_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Tracking</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ package.tracking_number ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Carrier</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ package.carrier_label ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Weight</dt>
                            <dd class="mt-0.5 font-medium">
                                {{
                                    package.weight_lbs != null
                                        ? `${package.weight_lbs} lb`
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div
                            v-if="package.customer_visible_notes"
                            class="sm:col-span-2"
                        >
                            <dt class="text-muted-foreground">Notes</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ package.customer_visible_notes }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Payment</CardTitle>
                    <CardDescription>
                        Pay online or in person when you collect your package.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Amount due</p>
                        <p class="text-3xl font-semibold tracking-tight">
                            {{ formatMoney(package.amount_due) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">
                            Invoice status
                        </p>
                        <p class="font-medium">{{ invoiceStatusLabel() }}</p>
                    </div>

                    <div
                        v-if="package.is_paid"
                        class="inline-flex items-center gap-2 rounded-full bg-brand-green/10 px-3 py-1 text-sm font-medium text-brand-green"
                    >
                        <CheckCircle2 class="size-4" />
                        Paid
                    </div>

                    <div v-else class="flex flex-col gap-2">
                        <Button
                            v-if="package.invoice_url"
                            as-child
                            variant="outline"
                            class="w-full"
                        >
                            <a
                                :href="package.invoice_url"
                                target="_blank"
                                rel="noopener"
                            >
                                View Invoice
                            </a>
                        </Button>

                        <Button
                            v-if="package.can_pay_online && package.payment_url"
                            as-child
                            class="w-full bg-brand-ink text-white hover:bg-brand-ink-soft"
                        >
                            <a
                                :href="package.payment_url"
                                target="_blank"
                                rel="noopener"
                            >
                                Pay Now
                            </a>
                        </Button>
                    </div>

                    <div
                        class="rounded-lg border border-brand-green/30 bg-brand-green/5 p-4 text-sm leading-relaxed"
                    >
                        <strong>In-person payment:</strong> You can pay when
                        you pick up your package. Bring your customer reference
                        and a valid ID.
                    </div>

                    <p
                        v-if="package.paid_at"
                        class="text-sm text-muted-foreground"
                    >
                        Paid
                        <span v-if="package.payment_method_label">
                            via {{ package.payment_method_label }}
                        </span>
                        on {{ formatDateTime(package.paid_at) }}.
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Status timeline</CardTitle>
                <CardDescription>Pickup-only workflow</CardDescription>
            </CardHeader>
            <CardContent>
                <ol class="space-y-4">
                    <li
                        v-for="step in package.timeline"
                        :key="step.label"
                        class="flex gap-3"
                    >
                        <CheckCircle2
                            v-if="step.complete"
                            class="mt-0.5 size-5 shrink-0 text-brand-green"
                        />
                        <Circle
                            v-else
                            class="mt-0.5 size-5 shrink-0 text-muted-foreground/40"
                        />
                        <div>
                            <p
                                class="font-medium"
                                :class="
                                    step.complete
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ step.label }}
                            </p>
                            <p
                                v-if="step.at"
                                class="text-xs text-muted-foreground"
                            >
                                {{ formatDateTime(step.at) }}
                            </p>
                        </div>
                    </li>
                </ol>
            </CardContent>
        </Card>
    </div>
</template>
