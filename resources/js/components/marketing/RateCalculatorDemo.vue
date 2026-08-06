<script setup lang="ts">
import { Calculator } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AnimatedPrice from './AnimatedPrice.vue';

/**
 * A playable copy of the admin rate calculator.
 *
 * The point of the section is that a visitor can feel the tool before signing
 * up, so the tiers below are illustrative sample data — the real calculator
 * reads whatever weight tiers the courier configures in their own admin.
 */
type Tier = {
    name: string;
    /** Inclusive lower bound, in pounds. */
    from: number;
    /** Exclusive upper bound, or null for "and above". */
    to: number | null;
    perLb: number;
    minimum: number;
};

const tiers: Tier[] = [
    { name: 'Light Air', from: 0, to: 5, perLb: 900, minimum: 900 },
    { name: 'Standard Air', from: 5, to: 20, perLb: 750, minimum: 3750 },
    { name: 'Bulk Air', from: 20, to: null, perLb: 620, minimum: 15000 },
];

const presets = [1, 2, 5, 10, 20, 30];

const weight = ref(5);

const matched = computed<Tier>(
    () =>
        tiers.find(
            (tier) =>
                weight.value >= tier.from &&
                (tier.to === null || weight.value < tier.to),
        ) ?? tiers[tiers.length - 1],
);

const rawCharge = computed(() => weight.value * matched.value.perLb);

/** The floor applies when the weight-based charge falls under it. */
const minimumApplies = computed(() => rawCharge.value < matched.value.minimum);

const total = computed(() =>
    Math.max(rawCharge.value, matched.value.minimum),
);

const format = (value: number) => value.toLocaleString();

const setWeight = (value: number) => {
    // Clamp rather than trust the input: `type="number"` still lets a user
    // paste a negative or absurd value straight into the field.
    weight.value = Math.min(Math.max(Number(value) || 0, 0), 150);
};
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_30px_80px_-35px_rgba(33,13,2,0.45)]"
    >
        <div
            class="flex items-center gap-2.5 border-b border-marketing-border bg-marketing-eggshell/50 px-6 py-4"
        >
            <span
                class="grid size-8 place-items-center rounded-lg bg-marketing-orange/12 text-marketing-orange"
            >
                <Calculator class="size-4" aria-hidden="true" />
            </span>
            <div>
                <p class="text-sm font-semibold">Rate calculator</p>
                <p class="text-xs text-marketing-ink-muted">
                    Try it — quote a package the way your staff would
                </p>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <label
                for="demo-weight"
                class="text-xs font-semibold tracking-[0.14em] text-marketing-ink-muted uppercase"
            >
                Package weight (lbs)
            </label>

            <div class="mt-2 flex items-center gap-3">
                <input
                    id="demo-weight"
                    :value="weight"
                    type="number"
                    min="0"
                    max="150"
                    step="0.5"
                    inputmode="decimal"
                    class="w-full rounded-xl border border-marketing-border bg-white px-4 py-3 text-lg font-semibold tabular-nums transition focus:border-marketing-orange focus:ring-2 focus:ring-marketing-orange/25 focus:outline-none"
                    @input="
                        setWeight(
                            ($event.target as HTMLInputElement).valueAsNumber,
                        )
                    "
                />
                <span class="text-sm font-medium text-marketing-ink-muted"
                    >lb</span
                >
            </div>

            <input
                :value="weight"
                type="range"
                min="0"
                max="50"
                step="0.5"
                aria-label="Package weight slider"
                class="demo-slider mt-4 w-full"
                @input="
                    setWeight(($event.target as HTMLInputElement).valueAsNumber)
                "
            />

            <div class="mt-4 flex flex-wrap gap-2">
                <button
                    v-for="preset in presets"
                    :key="preset"
                    type="button"
                    class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                    :class="
                        weight === preset
                            ? 'border-marketing-ink bg-marketing-ink text-marketing-cream'
                            : 'border-marketing-border text-marketing-ink-muted hover:border-marketing-ink/25 hover:text-marketing-ink'
                    "
                    @click="setWeight(preset)"
                >
                    {{ preset }} lb
                </button>
            </div>

            <dl
                class="mt-6 space-y-2.5 rounded-xl bg-marketing-eggshell/45 p-5 text-sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-marketing-ink-muted">Matched tier</dt>
                    <dd>
                        <Transition name="tier" mode="out-in">
                            <span
                                :key="matched.name"
                                class="inline-flex rounded-full bg-marketing-orange/12 px-2.5 py-1 text-xs font-semibold text-marketing-orange-deep"
                            >
                                {{ matched.name }}
                            </span>
                        </Transition>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-marketing-ink-muted">Rate applied</dt>
                    <dd class="font-medium tabular-nums">
                        JMD $<AnimatedPrice :value="matched.perLb" /> / lb
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-marketing-ink-muted">
                        {{ weight }} lb × rate
                    </dt>
                    <dd
                        class="font-medium tabular-nums transition-colors"
                        :class="
                            minimumApplies
                                ? 'text-marketing-ink-muted line-through'
                                : ''
                        "
                    >
                        JMD $<AnimatedPrice :value="rawCharge" />
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-marketing-ink-muted">Minimum charge</dt>
                    <dd
                        class="font-medium tabular-nums transition-colors"
                        :class="
                            minimumApplies
                                ? ''
                                : 'text-marketing-ink-muted line-through'
                        "
                    >
                        JMD $<AnimatedPrice :value="matched.minimum" />
                    </dd>
                </div>
            </dl>

            <div
                class="mt-4 flex flex-wrap items-baseline justify-between gap-2 rounded-xl bg-marketing-ink px-5 py-4 text-marketing-cream"
            >
                <span
                    class="text-xs font-semibold tracking-[0.14em] uppercase opacity-70"
                >
                    Total quote
                </span>
                <span class="text-3xl font-semibold tracking-tight">
                    JMD $<AnimatedPrice :value="total" />
                </span>
            </div>

            <p class="mt-3 text-xs text-marketing-ink-muted">
                Sample tiers. In your admin you set your own weight bands, per-lb
                rates, minimums and currency — the calculator follows.
            </p>
        </div>
    </div>
</template>

<style scoped>
/*
 * Range inputs can't be styled with utility classes across engines, so the
 * track and thumb are drawn here for both the WebKit and Firefox pseudo-
 * elements. They have to be written as separate rules — browsers drop the
 * whole selector list if one selector is unrecognised.
 */
.demo-slider {
    appearance: none;
    height: 0.375rem;
    border-radius: 9999px;
    background: var(--marketing-eggshell);
    cursor: pointer;
}

.demo-slider:focus-visible {
    outline: 2px solid var(--marketing-orange);
    outline-offset: 4px;
}

.demo-slider::-webkit-slider-thumb {
    appearance: none;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 9999px;
    background: var(--marketing-orange);
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(226, 83, 10, 0.45);
    transition: transform 150ms ease;
}

.demo-slider::-webkit-slider-thumb:hover {
    transform: scale(1.12);
}

.demo-slider::-moz-range-thumb {
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 9999px;
    background: var(--marketing-orange);
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(226, 83, 10, 0.45);
}

.tier-enter-active,
.tier-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.tier-enter-from {
    opacity: 0;
    transform: translateY(4px);
}

.tier-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

@media (prefers-reduced-motion: reduce) {
    .demo-slider::-webkit-slider-thumb,
    .tier-enter-active,
    .tier-leave-active {
        transition: none;
    }
}
</style>
