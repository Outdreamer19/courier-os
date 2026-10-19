<script setup lang="ts">
import { BellRing, ClipboardCheck } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
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
            <span
                class="hero-rise inline-flex items-center gap-2 rounded-full border border-marketing-border bg-white/80 px-4 py-1.5 text-xs font-medium text-marketing-ink shadow-sm backdrop-blur"
            >
                <span
                    class="rounded-full bg-marketing-orange px-2 py-0.5 text-[10px] font-bold tracking-wide text-white uppercase"
                >
                    New
                </span>
                Now welcoming Caribbean courier businesses
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
                    Get Started →
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
                US$63 a month + one-time setup · Cancel anytime
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
                                    Their own Miami address, in their pocket.
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
