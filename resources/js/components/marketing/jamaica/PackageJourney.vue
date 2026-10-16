<script setup lang="ts">
import { Check, Package, Plane } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useInViewLoop } from '@/composables/useInViewLoop';
import PhoneFrame from './PhoneFrame.vue';
import ScaleToFit from './ScaleToFit.vue';

/**
 * Hero visual for the Jamaica funnel: one parcel travelling Miami to Kingston
 * while the customer's phone (on a fictional courier's branded portal) ticks
 * through each status, ending on the WhatsApp "ready for pickup" message.
 *
 * It is the whole product promise in one loop: the customer always knows
 * where their package is, and the courier never had to answer a DM.
 */
const root = ref<HTMLElement | null>(null);

const steps = [
    { label: 'Pre-alert submitted', time: 'Mon 9:14am' },
    { label: 'Received at Miami warehouse', time: 'Tue 2:40pm' },
    { label: 'On the flight to Kingston', time: 'Wed 6:05am' },
    { label: 'Cleared customs', time: 'Thu 11:30am' },
    { label: 'Ready for pickup', time: 'Thu 3:12pm' },
];

const { step } = useInViewLoop(root, {
    steps: steps.length,
    interval: 1500,
    hold: 4200,
});

const ready = computed(() => step.value === steps.length - 1);
const flying = computed(() => step.value >= 2);

/** Miami (top left) to Kingston on Jamaica's south coast, in canvas pixels. */
const route = 'M 110 190 C 60 330, 230 380, 166 486';
</script>

<template>
    <div ref="root">
        <ScaleToFit :width="600" :height="640">
            <div
                class="relative h-full w-full overflow-hidden rounded-[32px] bg-marketing-ink"
            >
                <!-- Sea glow -->
                <div
                    aria-hidden="true"
                    class="absolute inset-0 bg-[radial-gradient(70%_55%_at_30%_70%,rgba(255,122,0,0.22),transparent_70%),radial-gradient(50%_40%_at_20%_10%,rgba(252,213,25,0.12),transparent_70%)]"
                />

                <!-- Dot-matrix sea with Jamaica drawn in -->
                <svg
                    aria-hidden="true"
                    class="absolute inset-0"
                    width="600"
                    height="640"
                    viewBox="0 0 600 640"
                >
                    <defs>
                        <pattern
                            id="pj-dots"
                            width="16"
                            height="16"
                            patternUnits="userSpaceOnUse"
                        >
                            <circle
                                cx="2"
                                cy="2"
                                r="1.3"
                                fill="rgba(255,251,245,0.16)"
                            />
                        </pattern>
                        <radialGradient id="pj-fade" cx="0.35" cy="0.5" r="0.6">
                            <stop offset="0" stop-color="white" />
                            <stop offset="1" stop-color="black" />
                        </radialGradient>
                        <mask id="pj-mask">
                            <rect
                                width="600"
                                height="640"
                                fill="url(#pj-fade)"
                            />
                        </mask>
                    </defs>
                    <rect
                        width="600"
                        height="640"
                        fill="url(#pj-dots)"
                        mask="url(#pj-mask)"
                    />
                    <path
                        transform="translate(30 430) scale(1.3)"
                        d="M0 24 C 10 14, 24 9, 42 6 C 62 2, 82 2, 100 6 C 118 9, 136 14, 148 22 C 154 27, 152 32, 144 35 C 132 38, 120 40, 110 43 C 102 46, 96 50, 86 49 C 76 47, 66 42, 54 40 C 40 38, 26 36, 14 33 C 6 31, -2 28, 0 24 Z"
                        fill="rgba(0,155,58,0.22)"
                        stroke="rgba(0,155,58,0.65)"
                        stroke-width="1.2"
                    />
                    <path
                        :d="route"
                        fill="none"
                        stroke="rgba(252,213,25,0.35)"
                        stroke-width="2"
                        stroke-dasharray="4 8"
                        stroke-linecap="round"
                    />
                    <path
                        :d="route"
                        fill="none"
                        stroke="#fcd519"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        pathLength="1"
                        stroke-dasharray="1"
                        :stroke-dashoffset="flying ? 0 : 1"
                        class="route-draw"
                        :class="{ 'is-moving': flying }"
                    />
                </svg>

                <!-- City pins -->
                <span
                    class="absolute top-[190px] left-[110px] size-[12px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-marketing-amber"
                />
                <div class="absolute top-[160px] left-[132px]">
                    <p class="text-[13px] font-semibold text-white">Miami</p>
                    <p class="text-[11px] text-white/45">Your US warehouse</p>
                </div>
                <div class="absolute top-[522px] left-[120px]">
                    <p class="text-[13px] font-semibold text-white">Kingston</p>
                    <p class="text-[11px] text-white/45">Your customers</p>
                </div>
                <span
                    class="absolute top-[486px] left-[166px] size-[14px] -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] bg-marketing-ink transition-colors duration-500"
                    :class="ready ? 'border-[#25d366]' : 'border-white/50'"
                />

                <!-- The parcel -->
                <div
                    class="parcel absolute top-0 left-0 flex size-[38px] items-center justify-center rounded-full bg-marketing-amber text-marketing-ink shadow-[0_0_0_6px_rgba(252,213,25,0.18)]"
                    :class="{ 'is-moving': flying }"
                    :style="{
                        offsetPath: `path('${route}')`,
                        offsetDistance: flying ? '100%' : '0%',
                    }"
                >
                    <Plane v-if="step === 2" class="size-[18px] rotate-90" />
                    <Package v-else class="size-[18px]" />
                </div>

                <!-- Customer phone -->
                <div
                    class="absolute top-[44px] right-[34px] h-[560px] w-[272px]"
                >
                    <PhoneFrame>
                        <div
                            class="flex h-full flex-col bg-[#fbfaf7] pt-[46px] text-marketing-ink"
                        >
                            <div class="flex items-center gap-2.5 px-4">
                                <span
                                    class="flex size-8 items-center justify-center rounded-[10px] bg-[#0f766e] text-[13px] font-bold text-white"
                                    >IE</span
                                >
                                <div>
                                    <p
                                        class="text-[13px] leading-tight font-semibold"
                                    >
                                        Island Express
                                    </p>
                                    <p
                                        class="text-[11px] text-marketing-ink-muted"
                                    >
                                        My packages
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mx-3 mt-4 rounded-2xl bg-white p-3.5 shadow-[0_1px_0_rgba(33,13,2,0.06),0_8px_24px_-14px_rgba(33,13,2,0.25)]"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p
                                            class="text-[13px] font-semibold tabular-nums"
                                        >
                                            PKG-1042
                                        </p>
                                        <p
                                            class="text-[11px] text-marketing-ink-muted"
                                        >
                                            SHEIN · 3.2 lb
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold transition-colors duration-500"
                                        :class="
                                            ready
                                                ? 'bg-[#25d366]/15 text-[#128c4a]'
                                                : 'bg-marketing-amber/30 text-marketing-olive'
                                        "
                                    >
                                        {{ ready ? 'Ready' : 'On the way' }}
                                    </span>
                                </div>

                                <ol class="mt-3.5">
                                    <li
                                        v-for="(s, i) in steps"
                                        :key="s.label"
                                        class="flex gap-2.5"
                                    >
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="flex size-[18px] items-center justify-center rounded-full transition-all duration-500"
                                                :class="
                                                    i <= step
                                                        ? i === steps.length - 1
                                                            ? 'bg-[#25d366] text-white'
                                                            : 'bg-marketing-ink text-white'
                                                        : 'border border-marketing-ink/15 bg-white text-transparent'
                                                "
                                            >
                                                <Check
                                                    class="size-[11px]"
                                                    :stroke-width="3"
                                                />
                                            </span>
                                            <span
                                                v-if="i < steps.length - 1"
                                                class="my-0.5 h-[22px] w-[2px] rounded transition-colors duration-500"
                                                :class="
                                                    i < step
                                                        ? 'bg-marketing-ink'
                                                        : 'bg-marketing-ink/10'
                                                "
                                            />
                                        </div>
                                        <div class="-mt-px pb-1">
                                            <p
                                                class="text-[12px] leading-tight font-medium transition-colors duration-500"
                                                :class="
                                                    i <= step
                                                        ? 'text-marketing-ink'
                                                        : 'text-marketing-ink/35'
                                                "
                                            >
                                                {{ s.label }}
                                            </p>
                                            <p
                                                class="text-[10px] transition-opacity duration-500"
                                                :class="
                                                    i <= step
                                                        ? 'text-marketing-ink-muted opacity-100'
                                                        : 'opacity-0'
                                                "
                                            >
                                                {{ s.time }}
                                            </p>
                                        </div>
                                    </li>
                                </ol>
                            </div>

                            <div
                                class="mx-3 mt-3 flex items-center justify-between rounded-2xl bg-marketing-ink px-3.5 py-3 text-white transition-all duration-500"
                                :class="
                                    ready
                                        ? 'translate-y-0 opacity-100'
                                        : 'translate-y-3 opacity-0'
                                "
                            >
                                <div>
                                    <p class="text-[10px] text-white/60">
                                        Balance due
                                    </p>
                                    <p
                                        class="text-[15px] font-semibold tabular-nums"
                                    >
                                        JMD 3,850
                                    </p>
                                </div>
                                <span
                                    class="rounded-lg bg-marketing-amber px-3 py-1.5 text-[11px] font-semibold text-marketing-ink"
                                    >Pay now</span
                                >
                            </div>

                            <div
                                class="mx-3 mt-3 flex items-center gap-3 rounded-2xl bg-white p-3 shadow-[0_1px_0_rgba(33,13,2,0.06)]"
                            >
                                <span
                                    class="flex size-8 items-center justify-center rounded-lg bg-marketing-eggshell text-[10px] font-semibold"
                                    >AMZ</span
                                >
                                <div class="flex-1">
                                    <p
                                        class="text-[12px] font-semibold tabular-nums"
                                    >
                                        PKG-1039
                                    </p>
                                    <p
                                        class="text-[10px] text-marketing-ink-muted"
                                    >
                                        Amazon · 5.0 lb
                                    </p>
                                </div>
                                <span
                                    class="rounded-full bg-marketing-eggshell px-2 py-0.5 text-[10px] font-semibold text-marketing-ink-muted"
                                    >Picked up</span
                                >
                            </div>
                        </div>

                        <!-- WhatsApp notification -->
                        <div
                            class="absolute inset-x-2 top-[38px] z-20 rounded-2xl bg-white/95 p-3 shadow-[0_16px_40px_-12px_rgba(33,13,2,0.45)] backdrop-blur transition-all duration-500 ease-out"
                            :class="
                                ready
                                    ? 'translate-y-0 opacity-100'
                                    : '-translate-y-24 opacity-0'
                            "
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex size-5 items-center justify-center rounded-[6px] bg-[#25d366]"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="size-3.5 fill-white"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-3.2-.7-2.7-1.1-4.4-3.8-4.5-4-.1-.2-1.1-1.4-1.1-2.7s.7-1.9.9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.7 1.2 1.6 2 1.1 1 2 1.3 2.3 1.4.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.3.1.2.1.6-.1 1.1Z"
                                        />
                                    </svg>
                                </span>
                                <p class="text-[11px] font-semibold">
                                    WhatsApp
                                </p>
                                <p
                                    class="ml-auto text-[10px] text-marketing-ink-muted"
                                >
                                    now
                                </p>
                            </div>
                            <p
                                class="mt-1.5 text-[12px] leading-snug font-semibold"
                            >
                                Island Express
                            </p>
                            <p
                                class="text-[12px] leading-snug text-marketing-ink/80"
                            >
                                Hi Shauna, your SHEIN package (3.2 lb) is ready
                                for pickup. Balance JMD 3,850.
                            </p>
                        </div>
                    </PhoneFrame>
                </div>

                <!-- Caption chip -->
                <div
                    class="absolute top-[34px] left-[34px] max-w-[230px] rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 backdrop-blur"
                >
                    <p class="text-[13px] leading-snug text-white/85">
                        Every update sent for you. No more "did my package
                        reach?"
                    </p>
                </div>
            </div>
        </ScaleToFit>
    </div>
</template>

<style scoped>
.parcel {
    offset-rotate: 0deg;
}

/* Only animate on the way out; the loop reset snaps back instead of flying
   the parcel home in reverse. */
.parcel.is-moving {
    transition: offset-distance 1.4s cubic-bezier(0.65, 0, 0.35, 1);
}

.route-draw.is-moving {
    transition: stroke-dashoffset 1.4s cubic-bezier(0.65, 0, 0.35, 1);
}

@media (prefers-reduced-motion: reduce) {
    .parcel,
    .route-draw {
        transition: none;
    }
}
</style>
