<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import BlurRevealText from './BlurRevealText.vue';
import HeroBackdrop from './HeroBackdrop.vue';
import HeroProductMock from './HeroProductMock.vue';

/**
 * A tab either points at a real product screenshot or falls back to the
 * rendered mock in HeroProductMock. Drop a screenshot into
 * public/images/marketing/ and add `image` here to swap one over.
 */
const tabs = [
    {
        key: 'admin',
        label: 'Admin Dashboard',
        image: {
            webp: '/images/marketing/admin-dashboard.webp',
            png: '/images/marketing/admin-dashboard.png',
            alt: 'CourierOS admin dashboard showing customer, pre-alert and package counts alongside sign-up, volume and pre-alert status charts',
        },
    },
    {
        key: 'portal',
        label: 'Customer Portal',
        image: {
            webp: '/images/marketing/customer-portal.webp',
            png: '/images/marketing/customer-portal.png',
            alt: 'CourierOS customer portal showing a welcome banner with the customer reference, active package and pre-alert counts, amount due, the assigned Florida shipping address and quick actions',
        },
    },
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
    { value: 'Your domain', label: 'Customers never see CourierOS' },
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
            <span
                class="hero-rise inline-flex items-center gap-2 rounded-full border border-marketing-border bg-white/80 px-4 py-1.5 text-xs font-medium text-marketing-ink shadow-sm backdrop-blur"
            >
                <span
                    class="rounded-full bg-marketing-orange px-2 py-0.5 text-[10px] font-bold tracking-wide text-white uppercase"
                >
                    New
                </span>
                Now onboarding Caribbean courier businesses
            </span>

            <!--
                `immediate` rather than the scroll observer: the h1 is above
                the fold, so it reveals as soon as the component mounts. The
                underlined phrase rides in as the final "word".
            -->
            <BlurRevealText
                as="h1"
                text="Run Your Courier Business"
                immediate
                :delay="80"
                :stagger="90"
                class="mx-auto mt-6 max-w-3xl text-5xl leading-[1.05] font-semibold tracking-tight text-balance text-marketing-ink sm:text-6xl"
            >
                <span class="relative inline-block">
                    <span class="relative z-10">Like a Platform</span>
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
                class="hero-rise mx-auto mt-6 max-w-xl text-lg text-marketing-ink-muted"
                style="animation-delay: 0.16s"
            >
                Pre-alerts, package tracking, billing, and customer updates — on
                your own branded site. Launch in minutes, not months.
            </p>

            <div
                class="hero-rise mt-8 flex flex-wrap items-center justify-center gap-3"
                style="animation-delay: 0.24s"
            >
                <a
                    href="/signup"
                    class="inline-flex items-center gap-2 rounded-lg bg-marketing-ink px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-marketing-ink/15 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-marketing-ink/20"
                >
                    Start Free Trial →
                </a>
                <a
                    href="#product"
                    class="rounded-lg border border-marketing-border bg-white/70 px-6 py-3 text-sm font-semibold text-marketing-ink backdrop-blur transition hover:bg-white"
                >
                    See How It Works
                </a>
            </div>

            <p
                class="hero-rise mt-4 text-xs text-marketing-ink-muted"
                style="animation-delay: 0.3s"
            >
                No card required to explore · Cancel anytime
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
                        todayshippingja.com
                    </span>
                </div>

                <!-- Aspect matched to the screenshot (1920×1053) rather than a
                     round 16/9, so object-cover has nothing to crop and the
                     app sidebar isn't sliced off at the left edge. -->
                <div class="relative aspect-[1920/1053] w-full">
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
                        <!--
                            Real screenshot where we have one, rendered mock
                            otherwise. The admin screenshot is the LCP element,
                            so it is eager and high priority rather than lazy.
                        -->
                        <picture v-if="tab.image">
                            <source
                                :srcset="tab.image.webp"
                                type="image/webp"
                            />
                            <img
                                :src="tab.image.png"
                                :alt="tab.image.alt"
                                width="1920"
                                height="1053"
                                fetchpriority="high"
                                decoding="async"
                                class="h-full w-full object-cover object-top"
                            />
                        </picture>
                        <HeroProductMock
                            v-else
                            :view="tab.key"
                            class="h-full w-full"
                        />
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
