<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CircleCheck, Info } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useBrand } from '@/composables/useBrand';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { estimateShipping, formatMoney } from '@/lib/shippingEstimate';
import type { RateTier } from '@/lib/shippingEstimate';
import { contact, register } from '@/routes';
import type { RateSnapshot } from '@/types/auth';

const props = defineProps<{
    rate: RateSnapshot | null;
    rateTiers: RateTier[];
}>();

const { brand, name: brandName } = useBrand();

const weight = ref<string>('1');
const declaredValue = ref<string>('');

const tiers = computed(() =>
    props.rateTiers.length > 0
        ? props.rateTiers
        : props.rate
          ? [props.rate as RateTier]
          : [],
);

const parsedWeight = computed(() => {
    const value = parseFloat(weight.value);

    return Number.isFinite(value) && value > 0 ? value : 0;
});

const estimate = computed(() =>
    estimateShipping(parsedWeight.value, tiers.value),
);

const estimatedShipping = computed(() => estimate.value.amount);

const activeTier = computed(() => estimate.value.tier);

const currency = computed(
    () =>
        activeTier.value?.currency ??
        props.rate?.currency ??
        brand.value?.currency ??
        'USD',
);

const formatted = (value: number) => formatMoney(currency.value, value);

const { sectionDelay, itemDelay } = useScrollReveal();

watch(weight, (value) => {
    if (value === '') {
        weight.value = '';

        return;
    }

    const numeric = parseFloat(value);

    if (!Number.isFinite(numeric) || numeric < 0) {
        weight.value = '0';
    }
});
</script>

<template>
    <Head title="Rates & Shipping Calculator" />

    <section
        class="fade-in-section relative overflow-hidden bg-brand-ink text-brand-cream"
        :style="sectionDelay(0)"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_70%_30%,rgba(30,142,62,0.18),transparent_50%)]"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="public-section-label-on-dark">Transparent pricing</p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
            >
                Estimate your shipping cost
            </h1>
            <p class="mt-4 max-w-2xl text-brand-cream/80">
                Our MVP rate is a single shipping method. Enter the weight of
                your package to see the estimated cost. Final charges are
                confirmed by our team after the package is processed at the
                Florida warehouse.
            </p>
        </div>
    </section>

    <section class="fade-in-section bg-background" :style="sectionDelay(1)">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_1fr]">
                <Card class="fade-in-item" :style="itemDelay(0)">
                    <CardHeader>
                        <CardTitle>Shipping calculator</CardTitle>
                        <CardDescription>
                            Estimates use the active database rate, not a
                            hard-coded value, so admin changes appear here
                            automatically.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="weight">Weight (lbs)</Label>
                                <Input
                                    id="weight"
                                    v-model="weight"
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    inputmode="decimal"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="declared-value">
                                    Declared value (optional)
                                </Label>
                                <Input
                                    id="declared-value"
                                    v-model="declaredValue"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="USD value of item"
                                    inputmode="decimal"
                                />
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-brand-green/30 bg-brand-green/5 p-5"
                        >
                            <p class="public-section-label">
                                Estimated shipping
                            </p>
                            <p
                                class="mt-2 text-4xl font-semibold tracking-tight"
                            >
                                {{ formatted(estimatedShipping) }}
                            </p>
                            <p
                                v-if="activeTier"
                                class="mt-2 text-sm text-muted-foreground"
                            >
                                Tier:
                                <span class="font-medium text-foreground">
                                    {{
                                        activeTier.name ?? activeTier.tier_label
                                    }}
                                </span>
                                · {{ formatted(activeTier.rate_per_lb) }} / lb ·
                                minimum
                                {{ formatted(activeTier.minimum_charge) }}.
                            </p>
                        </div>

                        <div
                            class="flex items-start gap-2 rounded-lg border border-border bg-muted/40 p-4 text-sm text-muted-foreground"
                        >
                            <Info
                                class="mt-0.5 size-4 shrink-0 text-brand-green"
                            />
                            <p>
                                Final charges are confirmed by {{ brandName }}
                                after the package is weighed at the warehouse.
                                Declared value is collected for customs and
                                insurance purposes — it does not change the
                                freight rate.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card class="fade-in-item" :style="itemDelay(1)">
                        <CardHeader>
                            <CardTitle>{{
                                props.rate?.name ?? 'Standard Air Shipping'
                            }}</CardTitle>
                            <CardDescription>
                                One shipping method for MVP — weight tiers are
                                wired up and ready to be expanded.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-start gap-2">
                                    <CircleCheck
                                        class="mt-0.5 size-4 text-brand-green"
                                    />
                                    <span>
                                        From
                                        {{
                                            formatted(
                                                props.rate?.rate_per_lb ?? 500,
                                            )
                                        }}
                                        per lb
                                    </span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CircleCheck
                                        class="mt-0.5 size-4 text-brand-green"
                                    />
                                    <span>
                                        Minimum charge
                                        {{
                                            formatted(
                                                props.rate?.minimum_charge ??
                                                    500,
                                            )
                                        }}
                                    </span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CircleCheck
                                        class="mt-0.5 size-4 text-brand-green"
                                    />
                                    <span>Pickup only</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <CircleCheck
                                        class="mt-0.5 size-4 text-brand-green"
                                    />
                                    <span>Pay online or in person</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    <Card
                        class="fade-in-item border-dashed"
                        :style="itemDelay(2)"
                    >
                        <CardHeader>
                            <CardTitle class="text-base"
                                >Weight tiers</CardTitle
                            >
                            <CardDescription>
                                Active rates from the database — managed in
                                admin.
                            </CardDescription>
                        </CardHeader>
                        <CardContent
                            class="space-y-2 text-sm text-muted-foreground"
                        >
                            <p
                                v-for="tier in tiers"
                                :key="tier.id ?? tier.tier_label"
                            >
                                {{ tier.tier_label ?? tier.name }} ·
                                {{ formatted(tier.rate_per_lb) }} / lb
                            </p>
                        </CardContent>
                    </Card>

                    <div
                        class="fade-in-item rounded-xl border border-border bg-card p-6"
                        :style="itemDelay(3)"
                    >
                        <h3 class="text-base font-semibold">Ready to ship?</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Create your free {{ brandName }} account to get your
                            overseas shipping address.
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Button as-child class="public-cta">
                                <Link :href="register()">Create account</Link>
                            </Button>
                            <Button as-child variant="outline">
                                <Link :href="contact()">Ask a question</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
