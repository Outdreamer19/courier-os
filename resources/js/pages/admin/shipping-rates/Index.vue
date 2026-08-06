<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Calculator,
    Check,
    CheckCircle2,
    CircleDollarSign,
    Copy,
    Package,
    Plane,
    Plus,
    Scale,
    Ship,
    Truck,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
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

/* What the customer actually pays per pound once the handling fee and any
 * minimum charge are folded in — the number staff get asked for on a call. */
const effectiveRatePerLb = computed(() =>
    parsedWeight.value > 0 ? calculation.value.amount / parsedWeight.value : 0,
);

const COPY_ICONS = { idle: Copy, copied: Check, failed: X } as const;
const COPY_LABELS = { idle: 'Copy', copied: 'Copied', failed: 'Press ⌘C' } as const;

const copyState = ref<'idle' | 'copied' | 'failed'>('idle');
let copyResetTimer: ReturnType<typeof setTimeout> | undefined;

/**
 * The async Clipboard API is unreliable here: it rejects on insecure origins
 * and, more often, hangs or throws "Document is not focused" when the window
 * has lost focus — which is exactly the case when someone alt-tabs back from
 * the phone call they're quoting. So race it against a short timeout and fall
 * back to the synchronous execCommand path, which has no focus requirement.
 */
const writeToClipboard = async (text: string) => {
    if (navigator.clipboard?.writeText && window.isSecureContext) {
        try {
            await Promise.race([
                navigator.clipboard.writeText(text),
                // Generous: this guards against a hang, not against slowness.
                // Too short and a legitimate write gets cut off and reported
                // as a failure.
                new Promise((_, reject) =>
                    setTimeout(() => reject(new Error('clipboard timeout')), 2000),
                ),
            ]);

            return true;
        } catch {
            // Fall through to the legacy path below.
        }
    }

    const scratch = document.createElement('textarea');
    scratch.value = text;
    scratch.setAttribute('readonly', '');
    scratch.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
    document.body.appendChild(scratch);
    scratch.select();

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        document.body.removeChild(scratch);
    }
};

const copyQuote = async () => {
    const ok = await writeToClipboard(formatted(calculation.value.amount));

    copyState.value = ok ? 'copied' : 'failed';
    clearTimeout(copyResetTimer);
    copyResetTimer = setTimeout(() => (copyState.value = 'idle'), 1800);
};

onBeforeUnmount(() => clearTimeout(copyResetTimer));
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
            <Button as-child class="bg-brand-ink text-white hover:bg-brand-ink-soft">
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
                    <div class="flex size-10 items-center justify-center rounded-lg bg-brand-ink/10">
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
                        <Button as-child class="bg-brand-ink text-white hover:bg-brand-ink-soft">
                            <Link :href="create()">Add rate</Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <!-- Deliberately the one card on this page with a coloured spine and
                 a solid quote panel: it's a tool, not a readout, so it should
                 pull the eye away from the tier list beside it. -->
            <Card
                class="gap-0 overflow-hidden border-brand-ink/15 py-0 lg:sticky lg:top-6"
            >
                <div
                    class="h-1 w-full bg-gradient-to-r from-brand-ink via-brand-ink-soft to-brand-green"
                />

                <CardHeader class="bg-brand-ink/[0.04] pt-5 pb-5">
                    <CardTitle class="flex items-center gap-2.5">
                        <span
                            class="flex size-8 items-center justify-center rounded-lg bg-brand-ink/10 text-brand-ink"
                        >
                            <Calculator class="size-4" />
                        </span>
                        Rate calculator
                    </CardTitle>
                    <CardDescription class="pt-1">
                        Quote a customer over the phone or chat. Active tiers only.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-5 border-t pt-5 pb-6">
                    <div class="space-y-2.5">
                        <Label for="calc-weight" class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Package weight
                        </Label>
                        <div class="relative">
                            <Input
                                id="calc-weight"
                                v-model="weight"
                                type="number"
                                min="0"
                                step="0.1"
                                inputmode="decimal"
                                class="h-12 pr-12 text-lg font-semibold tabular-nums focus-visible:border-brand-ink focus-visible:ring-brand-ink/20"
                            />
                            <span
                                class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm font-medium text-muted-foreground"
                            >
                                lb
                            </span>
                        </div>

                        <!-- Segmented control rather than loose pills: the presets
                             are one choice, so they should look like one object. -->
                        <div
                            class="flex overflow-hidden rounded-lg border divide-x"
                        >
                            <button
                                v-for="preset in presetWeights"
                                :key="preset"
                                type="button"
                                class="flex-1 py-1.5 text-xs font-medium tabular-nums transition-colors"
                                :class="
                                    parsedWeight === preset
                                        ? 'bg-brand-ink text-white'
                                        : 'text-muted-foreground hover:bg-brand-ink/5 hover:text-brand-ink'
                                "
                                @click="weight = String(preset)"
                            >
                                {{ preset }}
                            </button>
                        </div>
                    </div>

                    <template v-if="matchedTier">
                        <div class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Matched tier</span>
                                <Badge
                                    variant="outline"
                                    class="border-brand-ink/25 bg-brand-ink/10 font-semibold text-brand-ink"
                                >
                                    {{ matchedTier.name ?? matchedTier.tier_label }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">
                                    {{ parsedWeight }} lb ×
                                    {{ formatted(matchedTier.rate_per_lb) }}/lb
                                </span>
                                <span class="font-medium tabular-nums">
                                    {{ formatted(baseCharge) }}
                                </span>
                            </div>
                            <div
                                v-if="handlingFee"
                                class="flex items-center justify-between gap-3"
                            >
                                <span class="text-muted-foreground">Handling fee</span>
                                <span class="font-medium tabular-nums">
                                    +{{ formatted(handlingFee) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Minimum charge</span>
                                <span
                                    class="font-medium tabular-nums"
                                    :class="
                                        minimumApplied
                                            ? 'text-brand-ink'
                                            : 'text-muted-foreground line-through decoration-muted-foreground/40'
                                    "
                                >
                                    {{ formatted(matchedTier.minimum_charge) }}
                                </span>
                            </div>
                        </div>

                        <!-- The one solid block on the card. Everything above is
                             working-out; this is the number you read aloud. -->
                        <div
                            class="rounded-xl bg-gradient-to-br from-brand-ink to-brand-ink-soft p-4 text-brand-cream shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="text-[11px] font-medium tracking-[0.14em] text-brand-cream/60 uppercase"
                                    >
                                        Total quote
                                    </p>
                                    <p
                                        class="mt-1 text-3xl leading-none font-semibold tabular-nums"
                                    >
                                        {{ formatted(calculation.amount) }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="flex shrink-0 items-center gap-1.5 rounded-md border border-white/20 px-2.5 py-1.5 text-xs font-medium text-brand-cream/90 transition-colors hover:bg-white/10"
                                    :title="`Copy ${formatted(calculation.amount)}`"
                                    @click="copyQuote"
                                >
                                    <component
                                        :is="COPY_ICONS[copyState]"
                                        class="size-3.5"
                                    />
                                    {{ COPY_LABELS[copyState] }}
                                </button>
                            </div>
                            <p class="mt-3 border-t border-white/15 pt-2.5 text-xs text-brand-cream/70">
                                <template v-if="minimumApplied">
                                    Minimum charge applied — the weight-based total was
                                    {{ formatted(baseCharge + handlingFee) }}.
                                </template>
                                <template v-else>
                                    Works out to
                                    {{ formatted(effectiveRatePerLb) }} per lb.
                                </template>
                            </p>
                        </div>
                    </template>

                    <div
                        v-else
                        class="rounded-lg border border-dashed py-8 text-center"
                    >
                        <Calculator class="mx-auto size-6 text-muted-foreground/40" />
                        <p class="mt-2 px-6 text-sm text-muted-foreground">
                            Enter a weight and activate at least one rate tier to see
                            a quote.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
