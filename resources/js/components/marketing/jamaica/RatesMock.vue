<script setup lang="ts">
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';
import ScaleToFit from './ScaleToFit.vue';

/**
 * Rates are the operator's own numbers. The calculator customers see on the
 * branded site reads straight from them, so "how much for 6 lb?" stops being
 * a message and becomes a slider.
 */
const root = ref<HTMLElement | null>(null);

const weights = [1, 3, 6, 10];
const first = 900;
const additional = 500;

const { step } = useInViewLoop(root, {
    steps: weights.length,
    interval: 1300,
    hold: 2400,
});

const weight = computed(() => weights[step.value]);
const price = computed(() => first + (weight.value - 1) * additional);

const table = [
    { band: 'First pound', price: 'JMD 900' },
    { band: 'Each extra pound', price: 'JMD 500' },
    { band: 'Over 50 lb', price: 'Quote' },
];
</script>

<template>
    <div ref="root">
        <ScaleToFit :width="640" :height="460">
            <div
                class="flex h-full w-full gap-5 rounded-[22px] border border-marketing-ink/10 bg-[#fbfaf7] p-6 text-marketing-ink shadow-[0_40px_80px_-40px_rgba(33,13,2,0.45)]"
                role="img"
                aria-label="The shipping rates screen, where the business sets its own per-pound prices, next to the rate calculator customers use"
            >
                <section
                    class="flex flex-1 flex-col rounded-2xl border border-marketing-ink/8 bg-white p-5"
                >
                    <p class="text-[15px] font-semibold">Your rates</p>
                    <p class="text-[12px] text-marketing-ink-muted">
                        Change them any time. Customers see the update
                        instantly.
                    </p>
                    <div class="mt-4 space-y-2.5">
                        <div
                            v-for="row in table"
                            :key="row.band"
                            class="flex items-center justify-between rounded-xl bg-[#f6f3ec] px-3.5 py-3"
                        >
                            <span class="text-[13px]">{{ row.band }}</span>
                            <span
                                class="text-[13px] font-semibold tabular-nums"
                                >{{ row.price }}</span
                            >
                        </div>
                    </div>
                    <div
                        class="mt-auto rounded-xl border border-marketing-ink/8 px-3.5 py-3"
                    >
                        <p class="text-[11px] text-marketing-ink-muted">
                            Miami warehouse
                        </p>
                        <p class="text-[13px] font-medium">
                            1234 Logistics Way, Miami, FL 33101
                        </p>
                    </div>
                </section>

                <section
                    class="flex w-[250px] flex-col rounded-2xl bg-marketing-ink p-5 text-white"
                >
                    <p class="text-[15px] font-semibold">What will it cost?</p>
                    <p class="text-[12px] text-white/55">
                        On your customers' screen
                    </p>

                    <p class="mt-7 text-[12px] text-white/60">Package weight</p>
                    <p class="text-[22px] font-semibold tabular-nums">
                        {{ weight }} lb
                    </p>
                    <div class="relative mt-3 h-2 rounded-full bg-white/12">
                        <div
                            class="absolute inset-y-0 left-0 rounded-full bg-marketing-amber transition-[width] duration-700 ease-out"
                            :style="{ width: `${(weight / 10) * 100}%` }"
                        />
                        <div
                            class="absolute top-1/2 size-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-marketing-ink bg-marketing-amber transition-[left] duration-700 ease-out"
                            :style="{ left: `${(weight / 10) * 100}%` }"
                        />
                    </div>

                    <div class="mt-auto rounded-xl bg-white/[0.07] p-4">
                        <p class="text-[12px] text-white/60">
                            Estimated freight
                        </p>
                        <p
                            class="text-[30px] leading-tight font-semibold text-marketing-amber tabular-nums"
                        >
                            JMD {{ price.toLocaleString('en-JM') }}
                        </p>
                    </div>
                </section>
            </div>
        </ScaleToFit>
    </div>
</template>
