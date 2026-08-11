<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Check, MapPinned, MessageCircleMore, Rocket, Wallet } from 'lucide-vue-next';
import BlurRevealText from '@/components/marketing/BlurRevealText.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';

defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

const { itemDelay } = useScrollReveal();

const fullPackage = [
    'Your own branded website — subdomain included, custom domain later',
    'Hosting included, nothing extra to set up',
    'Admin portal to run intake, packages and staff',
    'Customer portal with their own Florida warehouse address',
    'Pre-alerts with automatic package matching',
    'Weight-based rate calculator',
    'Email & WhatsApp status notifications',
    'Online payments via Stripe, plus in-person payment recording',
    'Revenue & package reporting',
];

const segments = [
    {
        icon: Rocket,
        eyebrow: 'Starting from scratch',
        title: 'Launch a real shipping business',
        body: 'Already helping friends and family shop and ship? CourierOS gives you the branded site, portal and billing to turn it into a real business — no developer, no course required.',
    },
    {
        icon: MessageCircleMore,
        eyebrow: 'Already running one',
        title: 'Get off WhatsApp and spreadsheets',
        body: 'Running pre-alerts through DMs and a spreadsheet works until it doesn’t. Bring your customers and your brand over, and run the same business without the chaos.',
    },
];
</script>

<template>
    <Head title="CourierOS for Jamaica">
        <meta
            name="description"
            content="Start or upgrade a shop-and-ship courier business in Jamaica. A branded website, hosting, and the operations software behind it — one package, one price."
        />
    </Head>

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <MarketingNav />

        <!-- Hero -->
        <section class="relative overflow-hidden pt-16 pb-14 sm:pt-24">
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(60%_60%_at_50%_0%,rgba(255,122,0,0.14),transparent_70%)]"
            />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <p class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase">
                    CourierOS for Jamaica
                </p>
                <BlurRevealText
                    as="h1"
                    immediate
                    text="Your Shop-and-Ship Business, Built for You"
                    class="mt-3 text-4xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-5xl"
                />
                <p class="mx-auto mt-5 max-w-xl text-base text-pretty text-marketing-ink-muted">
                    Whether you're starting a courier business from scratch or
                    already running one out of WhatsApp and a spreadsheet,
                    CourierOS is the branded website, customer portal and
                    admin console behind it — one package, one price.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a
                        href="/signup"
                        class="inline-flex items-center gap-2 rounded-lg bg-marketing-ink px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-marketing-ink/15 transition hover:-translate-y-0.5 hover:shadow-xl"
                    >
                        Get the Full Package →
                    </a>
                    <a
                        href="mailto:hello@courieros.co?subject=CourierOS%20Jamaica"
                        class="rounded-lg border border-marketing-border bg-white/70 px-6 py-3 text-sm font-semibold text-marketing-ink backdrop-blur transition hover:bg-white"
                    >
                        Talk to Us First
                    </a>
                </div>
            </div>
        </section>

        <!-- Two segments -->
        <section class="pb-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div
                        v-for="(segment, index) in segments"
                        :key="segment.title"
                        class="fade-in-item rounded-2xl border border-marketing-border bg-white p-8"
                        :style="itemDelay(index)"
                    >
                        <div class="flex size-10 items-center justify-center rounded-lg bg-marketing-orange/12 text-marketing-orange">
                            <component :is="segment.icon" class="size-5" />
                        </div>
                        <p class="mt-4 text-xs font-semibold tracking-[0.16em] text-marketing-orange uppercase">
                            {{ segment.eyebrow }}
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-marketing-ink">{{ segment.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-marketing-ink-muted">{{ segment.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- The Full Package -->
        <section class="fade-in-section py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-marketing-border bg-white p-8 sm:p-10">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold tracking-[0.16em] text-marketing-orange uppercase">
                                The Full Package
                            </p>
                            <h2 class="mt-1 text-2xl font-semibold text-marketing-ink">
                                Everything to run the business, in one plan
                            </h2>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-semibold text-marketing-ink">
                                ${{ pricing.monthly }}<span class="text-sm font-medium text-marketing-ink-muted">/mo</span>
                            </p>
                            <p class="text-xs text-marketing-ink-muted">
                                + ${{ pricing.setup }} one-time setup · {{ pricing.currency }}
                            </p>
                        </div>
                    </div>

                    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                        <li
                            v-for="item in fullPackage"
                            :key="item"
                            class="flex items-start gap-2.5 text-sm text-marketing-ink"
                        >
                            <Check class="mt-0.5 size-4 shrink-0 text-marketing-orange" aria-hidden="true" />
                            <span>{{ item }}</span>
                        </li>
                    </ul>

                    <a
                        href="/signup"
                        class="mt-8 inline-flex items-center gap-2 rounded-lg bg-marketing-ink px-6 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        Get the Full Package →
                    </a>
                </div>
            </div>
        </section>

        <!-- Founding cohort -->
        <section class="pb-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-start gap-4 rounded-2xl bg-marketing-eggshell/60 p-6 sm:flex-row sm:items-center sm:p-8">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-marketing-ink text-marketing-amber">
                        <Wallet class="size-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-marketing-ink">Founding Jamaica Cohort</p>
                        <p class="mt-1 text-sm text-marketing-ink-muted">
                            The first businesses to launch on CourierOS from Jamaica get a
                            reduced setup fee. Message us before you sign up to check availability.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Case study -->
        <section class="fade-in-section bg-marketing-eggshell/40 py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-marketing-border bg-white p-8">
                    <p class="text-xs font-semibold tracking-[0.16em] text-marketing-orange uppercase">
                        Already live
                    </p>
                    <h2 class="mt-1 text-xl font-semibold text-marketing-ink">Today Shipping runs on CourierOS</h2>
                    <p class="mt-3 text-sm leading-relaxed text-marketing-ink-muted">
                        Today Shipping is a real Jamaican courier business
                        running its site, pre-alerts, tracking and billing on
                        CourierOS today — proof this isn't a concept, it's a
                        working platform.
                    </p>
                    <!--
                        TODO(Shane): swap this for a direct quote and real
                        numbers (packages/month, time saved) once you have
                        them from Today Shipping — that's the strongest proof
                        point for both segments and only you can get it.
                    -->
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="pt-4">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-marketing-border bg-white p-2 sm:p-4">
                    <div class="flex items-start gap-2.5 p-4 text-sm text-marketing-ink-muted">
                        <MapPinned class="mt-0.5 size-4 shrink-0 text-marketing-orange" aria-hidden="true" />
                        <p>
                            <span class="font-semibold text-marketing-ink">Don't have a Florida address yet? </span>
                            You'll need a US warehouse relationship before you can start
                            taking in packages — ask us and we'll point you to one.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <FaqAccordion />

        <FinalCta />

        <MarketingFooter />
    </div>
</template>
