<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CircleCheck,
    Clipboard,
    PackageCheck,
    Receipt,
    UserPlus,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { rates, register } from '@/routes';
import type { RateSnapshot } from '@/types/auth';

const props = defineProps<{
    rate: RateSnapshot | null;
}>();

const ratePerLbDisplay = computed(() => {
    const value = props.rate?.rate_per_lb ?? 500;
    const currency = props.rate?.currency ?? 'JMD';

    return `${currency} $${value.toLocaleString()}`;
});

const howItWorks = [
    {
        icon: UserPlus,
        title: 'Create your account',
        body: 'Sign up in minutes and get your unique SHIP DJM reference number.',
    },
    {
        icon: Clipboard,
        title: 'Shop online as normal',
        body: 'Buy from your favorite stores and place your orders like you usually do.',
    },
    {
        icon: Receipt,
        title: 'Submit your pre-alert',
        body: 'Upload your invoice and shipment details so our team can match your package quickly.',
    },
    {
        icon: PackageCheck,
        title: 'Track updates and collect',
        body: 'Follow status updates in your dashboard and pick up once your package is ready.',
    },
];

const whyShipDjm = [
    {
        title: 'Clear package visibility from day one',
        body: 'Track pre-alerts and package updates in one timeline so you always know what is happening next.',
        image: '/img/shipdjm-tracking.png',
        imageWebp: '/img/shipdjm-tracking.webp',
        imageAlt: 'Logistics warehouse shelves with organized parcels',
    },
    {
        title: 'Pre-alert workflow that is actually simple',
        body: 'Submit invoice details in minutes and let the SHIP DJM team handle package matching behind the scenes.',
        image: '/img/shipdjm-prealert-workflow.png',
        imageWebp: '/img/shipdjm-prealert-workflow.webp',
        imageAlt: 'Customer reviewing order and shipment details on laptop',
    },
    {
        title: 'Built for Jamaican customers, not retrofitted',
        body: 'From account references to pickup-first operations, the platform is designed around local customer reality.',
        image: '/img/shipdjm-jamaica.png',
        imageWebp: '/img/shipdjm-jamaica.webp',
        imageAlt: 'Urban Caribbean-style street scene with vibrant tones',
    },
    {
        title: 'Support you can actually reach',
        body: 'Get timely updates by email and WhatsApp, plus responsive help when you need clarity on a shipment.',
        image: '/img/shipdjm-support.png',
        imageWebp: '/img/shipdjm-support.webp',
        imageAlt: 'Customer support professional wearing headset and smiling',
    },
];

const faqs = [
    {
        q: 'What is a pre-alert?',
        a: 'A pre-alert tells us a package is on the way. You share the invoice and shipment details so we can match it to your account as soon as it lands at the warehouse.',
    },
    {
        q: 'What address do I ship to?',
        a: 'Once you sign up, your customer dashboard shows the Florida warehouse address that you use at checkout — with your customer reference attached so nothing gets lost.',
    },
    {
        q: 'How are rates calculated?',
        a: `Our current MVP rate is ${ratePerLbDisplay.value} per lb on a single shipping method. Pricing updates may include weight tiers later — admin always sets the final charge after weighing.`,
    },
    {
        q: 'When do I pay?',
        a: 'Once your package is processed we calculate the amount due. You can pay online (coming soon) or in person at pickup.',
    },
    {
        q: 'Where do I collect packages?',
        a: 'Pickup-only for now. Once your shipment is ready you will see the pickup location and instructions in your dashboard.',
    },
];

const { sectionDelay, itemDelay } = useScrollReveal();
</script>

<template>
    <Head title="Shop Online. Ship to Jamaica.">
        <link
            rel="preload"
            as="image"
            href="/img/shipdjm-hero.webp"
            type="image/webp"
            fetchpriority="high"
        />
    </Head>

    <div>
    <!-- Hero -->
    <section
        class="relative flex min-h-[min(560px,78vh)] items-start overflow-hidden bg-brand-ink text-brand-cream fade-in-section sm:min-h-[min(620px,82vh)]"
        :style="sectionDelay(0)"
    >
        <div
            aria-hidden="true"
            class="absolute inset-0"
        >
            <picture>
                <source srcset="/img/shipdjm-hero.webp" type="image/webp" />
                <img
                    src="/img/shipdjm-hero.png"
                    alt="SHIP DJM customer support team helping shoppers with package forwarding"
                    width="1774"
                    height="887"
                    fetchpriority="high"
                    decoding="async"
                    class="h-full w-full object-cover object-[72%_center] sm:object-[68%_center]"
                />
            </picture>
            <div
                class="absolute inset-0 bg-gradient-to-r from-brand-ink/90 via-brand-ink/45 to-brand-ink/20 sm:from-brand-ink/88 sm:via-brand-ink/35 sm:to-brand-ink/15"
            />
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_12%_40%,rgba(30,142,62,0.28),transparent_45%),radial-gradient(circle_at_85%_35%,rgba(30,142,62,0.18),transparent_50%),radial-gradient(circle_at_50%_100%,rgba(255,210,70,0.08),transparent_40%)]"
            />
            <div class="absolute inset-y-0 left-0 w-[58%] max-w-3xl bg-gradient-to-r from-black/35 to-transparent sm:w-[52%]" />
        </div>
        <div
            class="relative mx-auto w-full max-w-7xl pl-2 pr-6 pt-6 pb-14 sm:pl-3 sm:pt-8 lg:pl-4 lg:pt-10"
        >
            <div class="max-w-2xl space-y-5 text-left sm:space-y-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-brand-green/40 bg-brand-green/15 px-3 py-1 text-xs font-medium uppercase tracking-[0.18em] text-brand-green-soft"
                >
                    <span class="size-1.5 rounded-full bg-brand-green-soft"></span>
                    Customer-first logistics support
                </span>
                <h1
                    class="text-balance text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl"
                >
                    Shop Online.
                    <span class="text-brand-green-soft">Ship to Jamaica.</span>
                    <br />
                    Track everything in one place.
                </h1>
                <p class="max-w-xl text-base text-brand-cream/80 sm:text-lg">
                    SHIP DJM helps customers in Jamaica shop from stores like
                    Amazon, Walmart, SHEIN and more, then ship packages through
                    our Florida warehouse for pickup in Jamaica.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Button
                        as-child
                        size="lg"
                        class="public-cta"
                    >
                        <Link :href="register()">Create account</Link>
                    </Button>
                    <Button
                        as-child
                        size="lg"
                        variant="outline"
                        class="border-brand-green/40 bg-transparent text-brand-cream hover:bg-brand-green/15"
                    >
                        <Link :href="rates()">Calculate shipping</Link>
                    </Button>
                </div>
                <dl class="flex flex-wrap gap-x-8 gap-y-2 pt-4 text-sm">
                    <div>
                        <dt class="text-brand-cream/60">Base rate</dt>
                        <dd class="font-semibold text-brand-green-soft">
                            {{ ratePerLbDisplay }} / lb
                        </dd>
                    </div>
                    <div>
                        <dt class="text-brand-cream/60">Delivery</dt>
                        <dd class="font-semibold">Pickup only · for now</dd>
                    </div>
                    <div>
                        <dt class="text-brand-cream/60">Reference format</dt>
                        <dd class="font-semibold">DJM-000001</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="bg-background fade-in-section" :style="sectionDelay(1)">
        <div
            class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p class="public-section-label">
                    How it works
                </p>
                <h2
                    class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    Four practical steps, start to finish
                </h2>
                <p class="mt-4 text-muted-foreground">
                    Everything is streamlined for customers: register, shop,
                    pre-alert, and collect with clear status visibility.
                </p>
            </div>

            <ol
                class="mt-12 grid gap-5 sm:grid-cols-2"
            >
                <li
                    v-for="(step, index) in howItWorks"
                    :key="step.title"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-7 transition hover:border-brand-green/50 hover:shadow-xl fade-in-item"
                    :style="itemDelay(index)"
                >
                    <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-green/80 via-brand-green/30 to-transparent"
                    />
                    <div
                        class="flex size-12 items-center justify-center rounded-lg bg-brand-green/12 text-brand-green ring-1 ring-brand-green/25"
                    >
                        <component :is="step.icon" class="size-5" />
                    </div>
                    <p
                        class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                    >
                        Step {{ index + 1 }}
                    </p>
                    <h3 class="mt-1 text-lg font-semibold">{{ step.title }}</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ step.body }}
                    </p>
                </li>
            </ol>
        </div>
    </section>

    <!-- Why -->
    <section class="bg-brand-green-muted/50 fade-in-section" :style="sectionDelay(2)">
        <div
            class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-green"
                >
                    Why SHIP DJM
                </p>
                <h2
                    class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    Built for Jamaican shoppers — not bolted on
                </h2>
            </div>
            <div class="mt-12 grid gap-8 lg:grid-cols-2">
                <article
                    v-for="(item, index) in whyShipDjm"
                    :key="item.title"
                    class="group overflow-hidden rounded-2xl border border-border/80 bg-card fade-in-item shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                    :style="itemDelay(index, 120)"
                >
                    <div class="relative aspect-[16/10] overflow-hidden">
                        <picture>
                            <source
                                :srcset="item.imageWebp"
                                type="image/webp"
                            />
                            <img
                                :src="item.image"
                                :alt="item.imageAlt"
                                width="1448"
                                height="1086"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            />
                        </picture>
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/15 to-transparent"
                        />
                        <p
                            class="absolute bottom-3 left-3 rounded-full border border-white/20 bg-black/35 px-3 py-1 text-[11px] font-medium uppercase tracking-[0.16em] text-white/90"
                        >
                            SHIP DJM advantage
                        </p>
                    </div>
                    <div class="space-y-3 p-6">
                        <h3 class="text-xl font-semibold tracking-tight">
                            {{ item.title }}
                        </h3>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ item.body }}
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Rate preview -->
    <section class="bg-background fade-in-section" :style="sectionDelay(3)">
        <div
            class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div
                class="grid gap-10 rounded-2xl border border-brand-green/25 bg-gradient-to-br from-brand-green/10 via-brand-green-muted/40 to-brand-gold/5 p-10 lg:grid-cols-2 lg:items-center"
            >
                <div>
                    <p class="public-section-label">
                        Transparent pricing
                    </p>
                    <h2
                        class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Simple, weight-based rates
                    </h2>
                    <p class="mt-4 text-muted-foreground">
                        Our base rate for MVP is
                        <span class="font-semibold text-foreground">
                            {{ ratePerLbDisplay }} per lb
                        </span>
                        on a single shipping method. Pricing for heavier
                        packages will be tuned by our admin team — and rates
                        always come from the database, not hard-coded.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Button
                            as-child
                            class="bg-brand-ink text-brand-cream hover:bg-brand-ink-soft"
                        >
                            <Link :href="rates()">Calculate shipping</Link>
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="register()">Create account</Link>
                        </Button>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                    >
                        {{ props.rate?.name ?? 'Standard Air Shipping' }}
                    </p>
                    <p class="mt-2 text-4xl font-semibold tracking-tight">
                        {{ ratePerLbDisplay }}
                        <span class="text-base text-muted-foreground">
                            / lb
                        </span>
                    </p>
                    <ul class="mt-6 space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <CircleCheck
                                class="mt-0.5 size-4 text-brand-green"
                            />
                            <span>One shipping method for MVP</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <CircleCheck
                                class="mt-0.5 size-4 text-brand-green"
                            />
                            <span>Minimum charge {{ props.rate?.currency ?? 'JMD' }} ${{ (props.rate?.minimum_charge ?? 500).toLocaleString() }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <CircleCheck
                                class="mt-0.5 size-4 text-brand-green"
                            />
                            <span>Weight tiers coming soon</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <CircleCheck
                                class="mt-0.5 size-4 text-brand-green"
                            />
                            <span>Pickup only for now</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="bg-brand-green-muted/40 fade-in-section" :style="sectionDelay(4)">
        <div
            class="mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p class="public-section-label">
                    Quick answers
                </p>
                <h2
                    class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    Frequently asked questions
                </h2>
            </div>

            <dl class="mt-10 space-y-4">
                <div
                    v-for="(faq, index) in faqs"
                    :key="faq.q"
                    class="rounded-xl border border-border bg-card p-6 fade-in-item"
                    :style="itemDelay(index, 100)"
                >
                    <dt class="text-base font-semibold">{{ faq.q }}</dt>
                    <dd class="mt-2 text-sm text-muted-foreground">
                        {{ faq.a }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="relative overflow-hidden bg-brand-ink text-brand-cream fade-in-section" :style="sectionDelay(5)">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_50%,rgba(30,142,62,0.15),transparent_50%)]"
            aria-hidden="true"
        />
        <div
            class="relative mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 lg:px-8"
        >
            <h2
                class="text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
            >
                Start shipping with
                <span class="text-brand-green-soft">SHIP DJM</span> today.
            </h2>
            <p
                class="mx-auto mt-4 max-w-2xl text-base text-brand-cream/70 sm:text-lg"
            >
                Create your free account, get your Florida warehouse address,
                and never lose track of an online order again.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Button
                    as-child
                    size="lg"
                    class="public-cta"
                >
                    <Link :href="register()">Create account</Link>
                </Button>
                <Button
                    as-child
                    size="lg"
                    variant="outline"
                    class="border-brand-green/40 bg-transparent text-brand-cream hover:bg-brand-green/15"
                >
                    <Link :href="rates()">See rates</Link>
                </Button>
            </div>
        </div>
    </section>
    </div>
</template>
