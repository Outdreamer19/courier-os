<script setup lang="ts">
import {
    ArrowRight,
    BellRing,
    ClipboardCheck,
    MousePointerClick,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import BlurRevealText from './BlurRevealText.vue';
import FloatingFace from './FloatingFace.vue';
import HeroBackdrop from './HeroBackdrop.vue';
import AdminDashboardMock from './jamaica/AdminDashboardMock.vue';
import CustomerPortalMock from './jamaica/CustomerPortalMock.vue';

/**
 * Both tabs are coded, on-brand product mocks (see components/marketing/jamaica)
 * rather than screenshots: the old captures carried Today Shipping's navy and
 * red theme, which clashed with the marketing palette and blurred when scaled.
 */
const props = defineProps<{
    pricing?: {
        monthly: number;
        setup: number;
        currency: string;
        founding_setup?: number | null;
    };
    /** Public demo courier login, e.g. https://demo.courieros.co/login */
    liveDemoUrl?: string | null;
}>();

const money = (value: number) =>
    Number.isInteger(value) ? String(value) : value.toFixed(2);

const priceLine = computed(() => {
    if (!props.pricing) {
        return 'US$63 a month + one-time setup · Cancel anytime';
    }

    const setup = props.pricing.founding_setup ?? props.pricing.setup;
    const setupLabel = props.pricing.founding_setup
        ? `US$${money(setup)} founding setup`
        : `US$${money(setup)} one-time setup`;

    return `US$${money(props.pricing.monthly)} a month + ${setupLabel} · Cancel anytime`;
});

const tabs = [
    { key: 'admin', label: 'Admin Dashboard' },
    { key: 'portal', label: 'Customer Portal' },
] as const;

const active = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

const prefersReducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const select = (index: number) => {
    active.value = index;

    // Restart the rotation so a manual pick isn't yanked away a beat later.
    if (timer) {
        clearInterval(timer);
        start();
    }
};

const start = () => {
    if (prefersReducedMotion()) {
        return;
    }

    timer = setInterval(() => {
        active.value = (active.value + 1) % tabs.length;
    }, 5500);
};

onMounted(start);
onUnmounted(() => clearInterval(timer));

const proofPoints = [
    { value: 'Under 10 min', label: 'From signup to a live branded site' },
    { value: 'Your name', label: 'Your brand on every page, not ours' },
    { value: 'JMD & USD', label: 'Rates, invoices and totals in-currency' },
];
</script>

<template>
    <!--
        Deliberately NOT using `fade-in-section` here. That class starts at
        opacity 0 and waits on the IntersectionObserver set up after hydration,
        which leaves the largest element on the page invisible for as long as
        the bundle takes to boot — bad for both perceived speed and LCP. The
        hero animates itself in with `hero-rise` instead, which is pure CSS and
        runs on first paint.
    -->
    <section
        id="top"
        class="relative isolate overflow-hidden bg-marketing-cream"
    >
        <HeroBackdrop />

        <div
            class="relative mx-auto max-w-5xl px-4 pt-20 pb-14 text-center sm:px-6 lg:px-8"
        >
            <!--
                Live demo pill replaces the old "New · Now welcoming..." badge,
                which repeated the announcement bar word for word. Pointing at
                the demo here makes it obvious there is something to try
                without an account.
            -->
            <a
                v-if="liveDemoUrl"
                :href="liveDemoUrl"
                class="hero-rise group inline-flex items-center gap-2.5 rounded-full border border-marketing-border bg-white/85 py-1.5 pr-3 pl-2 text-xs font-medium text-marketing-ink shadow-sm backdrop-blur transition hover:border-marketing-orange/40 hover:bg-white"
            >
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold tracking-wide text-emerald-700 uppercase"
                >
                    <span class="relative flex size-1.5">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75 motion-reduce:hidden"
                        />
                        <span
                            class="relative inline-flex size-1.5 rounded-full bg-emerald-500"
                        />
                    </span>
                    Live
                </span>
                <span class="sm:hidden">Try the live demo. No signup</span>
                <span class="hidden sm:inline"
                    >Click around a real courier site. No signup needed</span
                >
                <ArrowRight
                    class="size-3.5 text-marketing-ink-muted transition group-hover:translate-x-0.5 group-hover:text-marketing-orange"
                    aria-hidden="true"
                />
            </a>

            <!--
                `immediate` rather than the scroll observer: the h1 is above
                the fold, so it reveals as soon as the component mounts. The
                underlined phrase rides in as the final "word".
            -->
            <BlurRevealText
                as="h1"
                text="Run your courier business"
                immediate
                :delay="80"
                :stagger="90"
                class="mx-auto mt-6 max-w-4xl text-[2.6rem] leading-[1.05] font-semibold tracking-tight text-balance text-marketing-ink sm:text-6xl"
            >
                <span class="relative inline-block whitespace-nowrap">
                    <span class="relative z-10">without the chaos</span>
                    <!-- Hand-drawn underline instead of a flat highlight -->
                    <svg
                        class="absolute -bottom-2 left-0 z-0 w-full"
                        height="14"
                        viewBox="0 0 300 14"
                        preserveAspectRatio="none"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M3 9.5C58 4 128 2.5 297 6.5"
                            stroke="var(--marketing-orange)"
                            stroke-width="6"
                            stroke-linecap="round"
                            opacity="0.9"
                        />
                    </svg>
                </span>
            </BlurRevealText>

            <p
                class="hero-rise mx-auto mt-7 max-w-2xl text-lg text-pretty text-marketing-ink-muted sm:text-xl"
                style="animation-delay: 0.16s"
            >
                Your customers get their own Florida address, send pre-alerts and
                track every package on your branded site. You receive, bill and
                update them from one dashboard.
            </p>

            <div
                class="hero-rise mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row"
                style="animation-delay: 0.24s"
            >
                <a
                    href="/signup"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marketing-ink px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-marketing-ink/15 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-marketing-ink/20 sm:w-auto"
                >
                    Get started
                    <ArrowRight class="size-4" aria-hidden="true" />
                </a>
                <a
                    v-if="liveDemoUrl"
                    :href="liveDemoUrl"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-marketing-orange/60 bg-white px-7 py-3 text-sm font-semibold text-marketing-ink transition hover:-translate-y-0.5 hover:border-marketing-orange hover:shadow-lg hover:shadow-marketing-orange/15 sm:w-auto"
                >
                    <MousePointerClick
                        class="size-4 text-marketing-orange"
                        aria-hidden="true"
                    />
                    Try the live demo
                </a>
                <a
                    v-else
                    href="#product"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-marketing-border bg-white/70 px-7 py-3.5 text-sm font-semibold text-marketing-ink backdrop-blur transition hover:bg-white sm:w-auto"
                >
                    See how it works
                </a>
            </div>

            <p
                class="hero-rise mt-4 text-xs text-marketing-ink-muted"
                style="animation-delay: 0.3s"
            >
                {{ priceLine }}
            </p>
        </div>

        <!-- Product -->
        <div class="relative mx-auto max-w-5xl px-4 pb-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-center gap-2">
                <button
                    v-for="(tab, index) in tabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="
                        active === index
                            ? 'bg-white text-marketing-ink shadow-sm ring-1 ring-marketing-border'
                            : 'text-marketing-ink-muted hover:bg-white/60'
                    "
                    :aria-pressed="active === index"
                    @click="select(index)"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Real faces either side of the product (wide screens) -->
            <FloatingFace
                photo="owner-laptop"
                alt="A smiling young woman working on a laptop"
                label="New pre-alert"
                sublabel="Matched to a package"
                :icon="ClipboardCheck"
                class="absolute top-24 -left-24 z-10 hidden xl:block"
            />
            <FloatingFace
                photo="customer-phone"
                alt="A smiling woman checking her phone outdoors"
                label="Ready for pickup"
                sublabel="WhatsApp update sent"
                :icon="BellRing"
                :delay="-3"
                class="absolute -right-24 bottom-10 z-10 hidden xl:block"
            />

            <!-- Browser chrome + live mock -->
            <div
                class="hero-rise relative overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_40px_90px_-40px_rgba(33,13,2,0.45)]"
                style="animation-delay: 0.36s"
            >
                <div
                    class="flex items-center gap-1.5 border-b border-marketing-border bg-marketing-eggshell/60 px-4 py-2.5"
                >
                    <span
                        class="size-2.5 rounded-full bg-marketing-orange/50"
                    />
                    <span class="size-2.5 rounded-full bg-marketing-amber/60" />
                    <span class="size-2.5 rounded-full bg-marketing-olive/40" />
                    <span
                        class="mx-auto rounded-md bg-white/70 px-3 py-0.5 text-[10px] text-marketing-ink-muted"
                    >
                        yourcourier.courieros.co
                    </span>
                </div>

                <!-- Aspect matched to the screenshot (1920×1053) rather than a
                     round 16/9, so object-cover has nothing to crop and the
                     app sidebar isn't sliced off at the left edge. -->
                <div class="relative aspect-[1100/690] w-full">
                    <!--
                        Both panels stay mounted and crossfade via opacity,
                        rather than being swapped through <Transition>.
                        Mounting/unmounting them left the outgoing node stuck
                        holding both enter-from and leave-from classes at
                        opacity 0, so the frame went blank. Keeping both in the
                        DOM has no such failure mode, and it means the
                        screenshot is already decoded when the tab is clicked.
                    -->
                    <div
                        v-for="(tab, index) in tabs"
                        :key="tab.key"
                        class="absolute inset-0 transition-opacity duration-500 ease-out"
                        :class="
                            active === index
                                ? 'opacity-100'
                                : 'pointer-events-none opacity-0'
                        "
                        :aria-hidden="active !== index"
                    >
                        <AdminDashboardMock v-if="tab.key === 'admin'" bare />
                        <div
                            v-else
                            class="flex h-full items-center justify-center gap-10 bg-marketing-ink px-6"
                        >
                            <div
                                class="hidden max-w-[240px] text-white sm:block"
                            >
                                <p
                                    class="text-2xl font-semibold tracking-tight"
                                >
                                    Their own Florida address, in their pocket.
                                </p>
                                <p class="mt-3 text-sm text-white/65">
                                    Customers copy their address, send
                                    pre-alerts and track every package without
                                    messaging you.
                                </p>
                            </div>
                            <div class="w-[30%] max-w-[190px] py-4">
                                <CustomerPortalMock />
                            </div>
                        </div>
                    </div>

                    <!-- Glass sheen sweep -->
                    <div
                        class="pointer-events-none absolute inset-0 overflow-hidden"
                        aria-hidden="true"
                    >
                        <div
                            class="hero-sheen absolute inset-y-0 -left-1/3 w-1/3 bg-gradient-to-r from-transparent via-white/45 to-transparent"
                        />
                    </div>
                </div>
            </div>

            <!-- Proof points -->
            <dl
                class="mt-10 grid gap-6 border-t border-marketing-border pt-8 sm:grid-cols-3"
            >
                <div v-for="point in proofPoints" :key="point.value">
                    <dt class="text-lg font-semibold text-marketing-ink">
                        {{ point.value }}
                    </dt>
                    <dd class="mt-1 text-sm text-marketing-ink-muted">
                        {{ point.label }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>
</template>
