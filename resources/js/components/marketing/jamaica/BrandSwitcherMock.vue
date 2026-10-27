<script setup lang="ts">
import { Lock } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';
import ScaleToFit from './ScaleToFit.vue';

/**
 * "Your brand, not ours": the same storefront re-skinned for three fictional
 * Jamaican couriers, cycling. Answers the first question every operator asks,
 * which is whether their customers will see CourierOS or them.
 */
const root = ref<HTMLElement | null>(null);

const brands = [
    {
        name: 'Island Express',
        initials: 'IE',
        sub: 'islandexpress',
        color: '#0f766e',
        soft: '#ccfbf1',
        town: 'Kingston',
    },
    {
        name: 'Yaad Ship',
        initials: 'YS',
        sub: 'yaadship',
        color: '#c2410c',
        soft: '#ffedd5',
        town: 'Montego Bay',
    },
    {
        name: 'Blessed Courier',
        initials: 'BC',
        sub: 'blessedcourier',
        color: '#6d28d9',
        soft: '#ede9fe',
        town: 'Mandeville',
    },
];

const { step } = useInViewLoop(root, {
    steps: brands.length,
    interval: 2600,
    hold: 2600,
});

const brand = computed(() => brands[step.value]);
</script>

<template>
    <div ref="root">
        <ScaleToFit :width="640" :height="460">
            <div
                class="flex h-full w-full flex-col overflow-hidden rounded-[22px] border border-marketing-ink/10 bg-white text-marketing-ink shadow-[0_40px_80px_-40px_rgba(33,13,2,0.45)]"
                role="img"
                aria-label="A courier website with the business's own name, colours and web address, shown for three example businesses"
            >
                <div
                    class="flex items-center gap-3 border-b border-marketing-ink/8 bg-[#f6f3ec] px-4 py-3"
                >
                    <div class="flex gap-1.5">
                        <span class="size-3 rounded-full bg-marketing-ink/15" />
                        <span class="size-3 rounded-full bg-marketing-ink/15" />
                        <span class="size-3 rounded-full bg-marketing-ink/15" />
                    </div>
                    <div
                        class="flex flex-1 items-center gap-2 rounded-lg bg-white px-3 py-1.5 text-[13px] text-marketing-ink-muted"
                    >
                        <Lock class="size-3.5" />
                        <Transition name="swap" mode="out-in">
                            <span :key="brand.sub"
                                ><span
                                    class="font-semibold text-marketing-ink"
                                    >{{ brand.sub }}</span
                                >.courieros.co</span
                            >
                        </Transition>
                    </div>
                </div>

                <Transition name="swap" mode="out-in">
                    <div
                        :key="brand.name"
                        class="flex flex-1 flex-col"
                        :style="{ '--b': brand.color, '--s': brand.soft }"
                    >
                        <div
                            class="flex items-center justify-between px-7 py-4"
                        >
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex size-9 items-center justify-center rounded-[10px] text-[13px] font-bold text-white"
                                    :style="{ background: brand.color }"
                                    >{{ brand.initials }}</span
                                >
                                <span class="text-[16px] font-semibold">{{
                                    brand.name
                                }}</span>
                            </div>
                            <div
                                class="flex items-center gap-5 text-[13px] text-marketing-ink-muted"
                            >
                                <span>Rates</span>
                                <span>Track</span>
                                <span
                                    class="rounded-lg px-3.5 py-2 font-semibold text-white"
                                    :style="{ background: brand.color }"
                                    >Sign up</span
                                >
                            </div>
                        </div>

                        <div
                            class="mx-7 flex flex-1 items-center gap-6 rounded-2xl px-8 pb-2"
                            :style="{ background: brand.soft }"
                        >
                            <div class="flex-1">
                                <p
                                    class="text-[30px] leading-[1.08] font-semibold tracking-tight"
                                >
                                    Shop in the US.<br />Pick up in
                                    {{ brand.town }}.
                                </p>
                                <p
                                    class="mt-3 text-[14px] text-marketing-ink/70"
                                >
                                    Your own Florida address, live tracking and
                                    WhatsApp updates.
                                </p>
                                <span
                                    class="mt-5 inline-block rounded-xl px-5 py-3 text-[14px] font-semibold text-white"
                                    :style="{ background: brand.color }"
                                    >Get my Florida address</span
                                >
                            </div>
                            <div
                                class="w-[190px] rounded-2xl bg-white p-4 shadow-[0_20px_40px_-24px_rgba(33,13,2,0.35)]"
                            >
                                <p class="text-[12px] font-semibold">
                                    Rate check
                                </p>
                                <p
                                    class="mt-2 text-[11px] text-marketing-ink-muted"
                                >
                                    5 lb package
                                </p>
                                <p
                                    class="text-[24px] font-semibold tabular-nums"
                                    :style="{ color: brand.color }"
                                >
                                    JMD 2,900
                                </p>
                                <div
                                    class="mt-3 h-2 rounded-full bg-marketing-ink/8"
                                >
                                    <div
                                        class="h-2 w-1/2 rounded-full"
                                        :style="{ background: brand.color }"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="h-7" />
                    </div>
                </Transition>
            </div>
        </ScaleToFit>
    </div>
</template>

<style scoped>
.swap-enter-active,
.swap-leave-active {
    transition:
        opacity 0.3s ease,
        filter 0.3s ease;
}

.swap-enter-from,
.swap-leave-to {
    opacity: 0;
    filter: blur(6px);
}
</style>
