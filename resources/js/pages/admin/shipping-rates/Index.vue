<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Calculator,
    CheckCircle2,
    CircleDollarSign,
    Package,
    Plane,
    Plus,
    Scale,
    Ship,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    estimateShipping,
    formatMoney
    
} from '@/lib/shippingEstimate';
import type {RateTier} from '@/lib/shippingEstimate';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/shipping-rates';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Shipping rates', href: index() },
        ],
    },
});

type RateRow = RateTier & {
    id: number;
    method: string;
    is_active: boolean;
};

const props = defineProps<{
    rates: RateRow[];
    currency: string;
}>();

const methodIcon = (method: string) => {
    const value = method.toLowerCase();

    if (value.includes('sea') || value.includes('ocean')) {
return Ship;
}

    if (value.includes('ground') || value.includes('road')) {
return Truck;
}

    if (value.includes('air') || value.includes('standard')) {
return Plane;
}

    return Package;
};

const totalRates = computed(() => props.rates.length);
const activeRates = computed(() => props.rates.filter((rate) => rate.is_active));
const inactiveCount = computed(() => totalRates.value - activeRates.value.length);
const averageRatePerLb = computed(() => {
    if (!activeRates.value.length) {
return 0;
}

    const sum = activeRates.value.reduce((total, rate) => total + rate.rate_per_lb, 0);

    return sum / activeRates.value.length;
});

const formatted = (value: number) => formatMoney(props.currency, value);

/* ---- Rate calculator (admin tool — deliberately not styled like the
 * public marketing calculator: a compact breakdown table instead of a
 * single hero number, plus quick-weight presets for common parcel sizes). */
const presetWeights = [1, 2, 5, 10, 20, 30];
const weight = ref<string>('5');

const parsedWeight = computed(() => {
    const value = parseFloat(weight.value);

    return Number.isFinite(value) && value > 0 ? value : 0;
});

const sortedActiveTiers = computed(() =>
    [...activeRates.value].sort(
        (a, b) => (a.min_weight_lbs ?? 0) - (b.min_weight_lbs ?? 0),
    ),
);

const calculation = computed(() => estimateShipping(parsedWeight.value, sortedActiveTiers.value));
const matchedTier = computed(() => calculation.value.tier);
const baseCharge = computed(() =>
    matchedTier.value ? parsedWeight.value * matchedTier.value.rate_per_lb : 0,
);
const handlingFee = computed(() => matchedTier.value?.handling_fee ?? 0);
const minimumApplied = computed(
    () =>
        Boolean(matchedTier.value) &&
        calculation.value.amount === matchedTier.value?.minimum_charge &&
        baseCharge.value + handlingFee.value < (matchedTier.value?.minimum_charge ?? 0),
);
</script>

<template>
    <Head title="Admin · Shipping rates" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Shipping rates</h1>
                <p class="text-sm text-muted-foreground">
                    Manage weight tiers and pricing used to calculate package charges.
                </p>
            </div>
            <Button as-child class="bg-brand-gold text-white hover:bg-brand-gold-soft">
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add rate
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-muted">
                        <CircleDollarSign class="size-5 text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ totalRates }}</p>
                        <p class="text-xs text-muted-foreground">Total rate tiers</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-brand-green/15">
                        <CheckCircle2 class="size-5 text-brand-green" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ activeRates.length }}</p>
                        <p class="text-xs text-muted-foreground">Active</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-muted">
                        <Scale class="size-5 text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ inactiveCount }}</p>
                        <p class="text-xs text-muted-foreground">Inactive</p>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-3 pt-6">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-brand-gold/15">
                        <CircleDollarSign class="size-5 text-brand-ink" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold">{{ formatted(averageRatePerLb) }}</p>
                        <p class="text-xs text-muted-foreground">Avg rate / lb</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr] lg:items-start">
            <Card v-if="rates.length">
                <CardHeader>
                    <CardTitle>Rate tiers</CardTitle>
                    <CardDescription>
                        Ordered by minimum weight. Inactive tiers are ignored by the
                        estimator.
                    </CardDescription>
                </CardHeader>
                <CardContent class="divide-y pt-2">
                    <div
                        v-for="rate in rates"
                        :key="rate.id"
                        class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted"
                            >
                                <component
                                    :is="methodIcon(rate.method)"
                                    class="size-4 text-muted-foreground"
                                />
                            </div>
                            <div>
                                <p class="font-medium">{{ rate.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ rate.tier_label ?? 'All weights' }} ·
                                    {{ formatted(rate.rate_per_lb) }}/lb · min
                                    {{ formatted(rate.minimum_charge) }}
                                    <span v-if="rate.handling_fee">
                                        · +{{ formatted(rate.handling_fee) }} handling</span
                                    >
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <StatusBadge
                                :status="rate.is_active ? 'active' : 'inactive'"
                                :label="rate.is_active ? 'Active' : 'Inactive'"
                            />
                            <Button as-child variant="ghost" size="sm">
                                <Link :href="edit(rate.id)">Edit</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-else>
                <CardContent>
                    <EmptyState
                        :icon="CircleDollarSign"
                        title="No shipping rates yet"
                        description="Add a rate tier so package charges can be calculated automatically."
                    >
                        <Button as-child class="bg-brand-gold text-white hover:bg-brand-gold-soft">
                            <Link :href="create()">Add rate</Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <Card class="border-brand-ink/10 bg-brand-ink text-brand-cream lg:sticky lg:top-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-brand-cream">
                        <Calculator class="size-4 text-brand-gold" />
                        Rate calculator
                    </CardTitle>
                    <CardDescription class="text-brand-cream/70">
                        Internal tool for quoting a customer over the phone or chat.
                        Uses active tiers only.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="space-y-2">
                        <Label for="calc-weight" class="text-brand-cream/80">Package weight (lbs)</Label>
                        <Input
                            id="calc-weight"
                            v-model="weight"
                            type="number"
                            min="0"
                            step="0.1"
                            inputmode="decimal"
                            class="border-white/15 bg-white/5 text-brand-cream placeholder:text-brand-cream/40"
                        />
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <button
                                v-for="preset in presetWeights"
                                :key="preset"
                                type="button"
                                class="rounded-full border border-white/15 px-2.5 py-1 text-xs text-brand-cream/80 transition-colors hover:border-brand-gold hover:text-brand-gold"
                                :class="{
                                    'border-brand-gold text-brand-gold': parsedWeight === preset,
                                }"
                                @click="weight = String(preset)"
                            >
                                {{ preset }} lb
                            </button>
                        </div>
                    </div>

                    <div v-if="matchedTier" class="space-y-2 rounded-lg border border-white/10 bg-white/5 p-4 text-sm">
                        <div class="flex items-center justify-between text-brand-cream/70">
                            <span>Matched tier</span>
                            <span class="font-medium text-brand-cream">
                                {{ matchedTier.name ?? matchedTier.tier_label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-brand-cream/70">
                            <span>Rate applied</span>
                            <span class="font-medium text-brand-cream">
                                {{ formatted(matchedTier.rate_per_lb) }} / lb
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-brand-cream/70">
                            <span>{{ parsedWeight }} lb × rate</span>
                            <span class="font-medium text-brand-cream">{{ formatted(baseCharge) }}</span>
                        </div>
                        <div v-if="handlingFee" class="flex items-center justify-between text-brand-cream/70">
                            <span>Handling fee</span>
                            <span class="font-medium text-brand-cream">{{ formatted(handlingFee) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-brand-cream/70">
                            <span>Minimum charge</span>
                            <span class="font-medium text-brand-cream">
                                {{ formatted(matchedTier.minimum_charge) }}
                                <span v-if="minimumApplied" class="text-brand-gold">(applied)</span>
                            </span>
                        </div>
                        <div class="mt-2 flex items-center justify-between border-t border-white/10 pt-3">
                            <span class="text-sm font-medium text-brand-cream/90">Total quote</span>
                            <span class="text-xl font-semibold text-brand-gold">
                                {{ formatted(calculation.amount) }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-brand-cream/60">
                        Enter a weight and activate at least one rate tier to see a
                        quote.
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
