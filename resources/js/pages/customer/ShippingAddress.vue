<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
import CopyButton from '@/components/CopyButton.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { shippingAddress } from '@/routes/portal';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'My shipping address', href: shippingAddress() },
        ],
    },
});

defineProps<{
    warehouse: {
        name: string;
        customer_name: string;
        customer_reference: string;
        address_line_1: string;
        address_line_2: string | null;
        city: string;
        state: string;
        zip: string;
        phone: string | null;
        instructions: string | null;
        full_address: string;
    } | null;
}>();
</script>

<template>
    <Head title="My shipping address" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-gold"
            >
                Checkout address
            </p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                My Florida shipping address
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                Use this exact address when shopping online. Your customer
                reference must appear in the address line (suite) so we can
                match packages to your account.
            </p>
        </div>

        <Card v-if="warehouse">
            <CardHeader>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="size-4 text-brand-gold" />
                            {{ warehouse.name }}
                        </CardTitle>
                        <CardDescription>
                            Copy each field or the full block below.
                        </CardDescription>
                    </div>
                    <CopyButton
                        :value="warehouse.full_address"
                        label="Copy all"
                        success-message="Full address copied"
                    />
                </div>
            </CardHeader>
            <CardContent class="space-y-4 text-sm">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            Your name
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.customer_name }}
                            <CopyButton
                                :value="warehouse.customer_name"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            Suite (your reference)
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.customer_reference }}
                            <CopyButton
                                :value="warehouse.customer_reference"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            Address line 1
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.address_line_1 }}
                            <CopyButton
                                :value="warehouse.address_line_1"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                    <div v-if="warehouse.address_line_2">
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            Address line 2
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.address_line_2 }}
                            <CopyButton
                                :value="warehouse.address_line_2"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            City / State / ZIP
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.city }}, {{ warehouse.state }}
                            {{ warehouse.zip }}
                            <CopyButton
                                :value="`${warehouse.city}, ${warehouse.state} ${warehouse.zip}`"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                    <div v-if="warehouse.phone">
                        <dt class="text-xs uppercase tracking-wider text-muted-foreground">
                            Phone
                        </dt>
                        <dd class="mt-0.5 flex items-center gap-2 font-medium">
                            {{ warehouse.phone }}
                            <CopyButton
                                :value="warehouse.phone"
                                variant="ghost"
                                size="icon-sm"
                            />
                        </dd>
                    </div>
                </dl>

                <div
                    v-if="warehouse.instructions"
                    class="rounded-lg border border-brand-gold/30 bg-brand-gold/5 p-4 leading-relaxed text-foreground/80"
                >
                    {{ warehouse.instructions }}
                </div>
            </CardContent>
        </Card>

        <Card v-else>
            <CardContent class="py-12 text-center text-sm text-muted-foreground">
                No active warehouse address is configured yet. Please contact
                support.
            </CardContent>
        </Card>
    </div>
</template>
