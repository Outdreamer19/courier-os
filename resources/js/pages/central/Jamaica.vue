<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BellRing,
    Check,
    ChevronDown,
    ExternalLink,
    Globe2,
    ShieldCheck,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BlurRevealText from '@/components/marketing/BlurRevealText.vue';
import FeatureShowcase from '@/components/marketing/FeatureShowcase.vue';
import FloatingFace from '@/components/marketing/FloatingFace.vue';
import AdminDashboardMock from '@/components/marketing/jamaica/AdminDashboardMock.vue';
import BrandSwitcherMock from '@/components/marketing/jamaica/BrandSwitcherMock.vue';
import ChatPileUp from '@/components/marketing/jamaica/ChatPileUp.vue';
import CustomerPortalMock from '@/components/marketing/jamaica/CustomerPortalMock.vue';
import PackageJourney from '@/components/marketing/jamaica/PackageJourney.vue';
import RatesMock from '@/components/marketing/jamaica/RatesMock.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import PeopleSection from '@/components/marketing/PeopleSection.vue';
import StackingFeatureCards from '@/components/marketing/StackingFeatureCards.vue';
import TrustBar from '@/components/marketing/TrustBar.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    currentCampaign,
    trackMarketing,
    whatsappLink,
} from '@/composables/useMarketingTracking';
import { useScrollReveal } from '@/composables/useScrollReveal';

/**
 * /jamaica is the landing page for paid Instagram, Facebook, WhatsApp and
 * Google traffic, so it is built as a funnel rather than a brochure:
 *
 *   hook (the parcel journey) → pain (the WhatsApp pile-up) → each pain
 *   answered → how it works → live demo (Blue Mahoe Express) → the offer →
 *   objections → one last ask.
 *
 * Every call to action is either "WhatsApp us" (the channel this audience
 * already lives in, and the one Click-to-WhatsApp ads land in) or sign up.
 * The nav runs in minimal mode so ad traffic has nowhere else to wander.
 */
const props = defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
    sales: {
        whatsapp: string;
        founding: { setup: number; spotsTotal: number; spotsLeft: number };
    };
    liveDemo: { url: string; name: string } | null;
}>();

const { itemDelay } = useScrollReveal();

const money = (n: number) => `US$${Number.isInteger(n) ? n : n.toFixed(2)}`;

const foundingOpen = computed(() => props.sales.founding.spotsLeft > 0);
const setupNow = computed(() =>
    foundingOpen.value ? props.sales.founding.setup : props.pricing.setup,
);

const campaign = ref<string | null>(null);

const demoLink = computed(() =>
    whatsappLink(
        props.sales.whatsapp,
        `Hi, I'm interested in CourierOS for my shipping business in Jamaica. Can you show me a demo?${campaign.value ? ` (${campaign.value})` : ''}`,
    ),
);

const foundingLink = computed(() =>
    whatsappLink(
        props.sales.whatsapp,
        `Hi, I'd like to claim a founding spot on CourierOS (setup ${money(props.sales.founding.setup)}).${campaign.value ? ` (${campaign.value})` : ''}`,
    ),
);

const onWhatsApp = (placement: string) =>
    trackMarketing('Lead', { content_name: 'whatsapp', placement });
const onDemo = (placement: string) =>
    trackMarketing('ViewContent', { content_name: 'live_demo', placement });
const onSignup = (placement: string) =>
    trackMarketing('InitiateCheckout', {
        placement,
        value: setupNow.value,
        currency: 'USD',
    });

/* Sticky mobile bar appears once the hero's own buttons have scrolled away. */
const heroCtas = ref<HTMLElement | null>(null);
const showStickyBar = ref(false);
let heroObserver: IntersectionObserver | null = null;

onMounted(() => {
    campaign.value = currentCampaign();
    trackMarketing('ViewContent', { content_name: 'jamaica' });

    if (heroCtas.value && typeof IntersectionObserver !== 'undefined') {
        heroObserver = new IntersectionObserver(([entry]) => {
            showStickyBar.value =
                !entry.isIntersecting && entry.boundingClientRect.top < 0;
        });
        heroObserver.observe(heroCtas.value);
    }
});

onBeforeUnmount(() => heroObserver?.disconnect());

const answers = [
    {
        asked: 'Did my package reach yet?',
        answer: 'Live tracking in their portal, plus a WhatsApp message at every step.',
    },
    {
        asked: 'How much for a 6 lb box?',
        answer: 'A rate calculator on your site, using your own prices.',
    },
    {
        asked: "What's the Miami address again?",
        answer: 'Every customer gets their own Miami address, saved in their account.',
    },
    {
        asked: 'How much I owe you?',
        answer: 'Invoices they can see and pay online, or in person at pickup.',
    },
];

const fullPackage = [
    'Your own branded website and web address',
    'Hosting, security and updates included',
    'Customer portal with their own Miami address',
    'Pre-alerts that match to packages automatically',
    'Rate calculator using your prices',
    'WhatsApp and email status updates',
    'Online payments, plus cash and in-store recording',
    'Admin console for intake, staff and reports',
    'Unlimited customers and packages',
];

const comparison = [
    {
        title: 'A one-off website',
        subtitle: 'A page with your logo on it',
        rows: [true, false, false, false, false],
    },
    {
        title: 'WhatsApp and a spreadsheet',
        subtitle: 'What most couriers run on today',
        rows: [false, 'By hand', false, false, false],
    },
    {
        title: 'CourierOS',
        subtitle: 'The whole business, from day one',
        featured: true,
        rows: [true, true, true, true, true],
    },
];
const comparisonLabels = [
    'Branded website',
    'Pre-alerts and tracking',
    'Customer self-service portal',
    'Online payments',
    'Improves as you grow',
];

const faqs = computed(() => [
    {
        q: 'I have never run a courier business. Can I still do this?',
        a: "Yes. That is exactly who the setup call is for. We set up your site, rates and Miami address with you, and walk you through receiving your first package. You don't need a developer or a course.",
    },
    {
        q: 'I already have customers on WhatsApp. Do I lose them?',
        a: 'No. You send them your new link, they register in a minute and get their own Miami address. Your business name, your customers, just without the chaos.',
    },
    {
        q: 'How much does it cost, really?',
        a: `${money(props.pricing.monthly)} a month and a one-time setup fee of ${money(setupNow.value)}${foundingOpen.value ? ` (normally ${money(props.pricing.setup)})` : ''}. That covers the whole platform with unlimited customers, packages and staff. No per-package fees, and no cut of what your customers pay you.`,
    },
    {
        q: 'Do I need a Miami warehouse?',
        a: "You need a US address where packages are received, usually a freight forwarder's warehouse in Miami. If you don't have one yet, ask us on WhatsApp and we'll point you to options.",
    },
    {
        q: 'How do my customers pay me?',
        a: 'Online through your own Stripe account, or cash and card in person, recorded against the package. The money goes to you, never to CourierOS.',
    },
    {
        q: 'Can I leave if it is not for me?',
        a: 'Yes. Billing is month to month and you can cancel any time.',
    },
    {
        q: 'Is my business data kept private?',
        a: 'Yes. Every business on CourierOS has its own separate data. No other courier can see your customers or packages.',
    },
]);

const openFaq = ref<number | null>(0);
</script>

<template>
    <Head title="Start or grow your shipping business in Jamaica">
        <meta
            name="description"
            content="Your own branded courier website, customer portal with Miami address, tracking, WhatsApp updates and payments. Set up with you this week."
        />
    </Head>

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <!-- Founding offer bar -->
        <a
            v-if="foundingOpen"
            :href="foundingLink"
            target="_blank"
            rel="noopener"
            class="block bg-marketing-ink px-4 py-2.5 text-center text-[13px] text-white/85 transition hover:text-white"
            @click="onWhatsApp('founding-bar')"
        >
            <span class="font-semibold text-marketing-amber"
                >Founding offer:</span
            >
            setup {{ money(sales.founding.setup) }} instead of
            {{ money(pricing.setup) }} for the first
            {{ sales.founding.spotsTotal }} Jamaican couriers.
            <span
                class="font-semibold whitespace-nowrap underline underline-offset-4"
                >{{ sales.founding.spotsLeft }} spots left</span
            >
        </a>

        <MarketingNav
            minimal
            :cta-href="demoLink"
            cta-label="WhatsApp us"
            @cta="onWhatsApp('nav')"
        />

        <!-- 1. Hook -->
        <section class="relative overflow-hidden">
            <div
                class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-10 px-4 pt-10 pb-16 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:gap-14 lg:px-8 lg:pt-16 lg:pb-24"
            >
                <div>
                    <p
                        class="inline-flex items-center gap-2 rounded-full border border-marketing-border bg-white px-3 py-1.5 text-[13px] font-medium text-marketing-ink"
                    >
                        <span class="flex gap-0.5" aria-hidden="true">
                            <span
                                class="size-2 rounded-full bg-marketing-ink"
                            />
                            <span
                                class="size-2 rounded-full bg-marketing-amber"
                            />
                            <span class="size-2 rounded-full bg-[#009b3a]" />
                        </span>
                        Built for Jamaican shop-and-ship couriers
                    </p>
                    <BlurRevealText
                        as="h1"
                        immediate
                        text="Always wanted your own courier business? We build it with you."
                        class="mt-5 text-[2.6rem] leading-[1.02] font-semibold tracking-[-0.035em] text-balance sm:text-6xl"
                    />
                    <p
                        class="mt-6 max-w-xl text-lg leading-relaxed text-pretty text-marketing-ink-muted"
                    >
                        Your own branded website, a portal where customers get
                        their Miami address and track every package, WhatsApp
                        updates and online payments. Starting out or already
                        shipping from a WhatsApp group, we set it up with you.
                    </p>

                    <div
                        ref="heroCtas"
                        class="mt-8 flex flex-col gap-3 sm:flex-row"
                    >
                        <a
                            :href="demoLink"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-marketing-amber px-6 py-4 text-base font-semibold text-marketing-ink shadow-[0_10px_30px_-10px_rgba(226,83,10,0.55)] transition hover:-translate-y-0.5 hover:brightness-95"
                            @click="onWhatsApp('hero')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="size-5 fill-current"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-3.2-.7-2.7-1.1-4.4-3.8-4.5-4-.1-.2-1.1-1.4-1.1-2.7s.7-1.9.9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.7 1.2 1.6 2 1.1 1 2 1.3 2.3 1.4.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.3.1.2.1.6-.1 1.1Z"
                                />
                            </svg>
                            Get a free demo on WhatsApp
                        </a>
                        <a
                            v-if="liveDemo"
                            :href="liveDemo.url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-marketing-ink/15 bg-white px-6 py-4 text-base font-semibold text-marketing-ink transition hover:border-marketing-ink/30"
                            @click="onDemo('hero')"
                        >
                            Try the live demo
                            <ExternalLink class="size-4" aria-hidden="true" />
                        </a>
                        <a
                            v-else
                            href="#offer"
                            class="inline-flex items-center justify-center rounded-xl border border-marketing-ink/15 bg-white px-6 py-4 text-base font-semibold text-marketing-ink transition hover:border-marketing-ink/30"
                        >
                            See the price
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-marketing-ink-muted">
                        {{ money(pricing.monthly) }} a month. No per-package
                        fees. Cancel any time.
                    </p>
                </div>

                <div class="relative">
                    <PackageJourney />
                    <FloatingFace
                        photo="customer-smile"
                        alt="A smiling woman sitting in her car"
                        label="Package ready"
                        sublabel="Sent to her on WhatsApp"
                        :icon="BellRing"
                        class="absolute -bottom-16 left-6 hidden sm:block"
                    />
                </div>
            </div>
        </section>

        <TrustBar />

        <!-- 2. Pain -->
        <section class="bg-marketing-ink py-20 text-white sm:py-28">
            <div
                class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8"
            >
                <div class="lg:order-2">
                    <BlurRevealText
                        text="Sound familiar?"
                        class="text-4xl font-semibold tracking-[-0.03em] sm:text-5xl"
                    />
                    <p
                        class="mt-5 max-w-md text-lg leading-relaxed text-white/70"
                    >
                        If your phone looks like this every morning, the
                        business is running you. Every one of these messages is
                        something your customers could check for themselves.
                    </p>
                    <p
                        class="mt-4 max-w-md text-lg leading-relaxed text-white/70"
                    >
                        CourierOS answers them before they are asked, so you
                        spend the day receiving packages and getting paid, not
                        replying.
                    </p>
                </div>
                <div class="lg:order-1">
                    <ChatPileUp />
                </div>
            </div>

            <div class="mx-auto mt-16 max-w-6xl px-4 sm:px-6 lg:px-8">
                <dl
                    class="grid gap-px overflow-hidden rounded-2xl bg-white/10 sm:grid-cols-2"
                >
                    <div
                        v-for="(item, index) in answers"
                        :key="item.asked"
                        class="fade-in-item bg-marketing-ink p-6 sm:p-7"
                        :style="itemDelay(index)"
                    >
                        <dt class="text-base text-white/50">
                            "{{ item.asked }}"
                        </dt>
                        <dd
                            class="mt-2 text-lg leading-snug font-medium text-white"
                        >
                            {{ item.answer }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- 3. How it works -->
        <StackingFeatureCards
            eyebrow="How it works"
            title="From your first call with us to your first customer"
            body="Most couriers are taking pre-alerts within a week."
        >
            <FeatureShowcase
                eyebrow="Day 1"
                title="Your name, your colours, your web address"
                body="We set up your branded site with you on a call. Your customers only ever see your business, never ours."
                :bullets="['Your logo and colours', 'Your own web address']"
            >
                <template #visual>
                    <BrandSwitcherMock />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                eyebrow="Day 1"
                title="Set your rates and Miami address"
                body="Put in your per-pound prices and your warehouse address. Change them whenever you like and your site updates straight away."
                :bullets="[
                    'Rate calculator for customers',
                    'Prices in JMD or USD',
                ]"
            >
                <template #visual>
                    <RatesMock />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                eyebrow="This week"
                title="Customers sign themselves up"
                body="Share your link on WhatsApp and Instagram. Customers register, copy their Miami address and send pre-alerts without messaging you."
                :bullets="[
                    'Their own Miami address',
                    'Pre-alerts with invoice upload',
                ]"
            >
                <template #visual>
                    <CustomerPortalMock />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                wide-visual
                eyebrow="Every day after"
                title="Run it like a real business"
                body="See every package from Miami to pickup, who has paid, and what you made this month, all in one place."
                :bullets="[
                    'Every package, one screen',
                    'Money collected, month by month',
                ]"
            >
                <template #visual>
                    <AdminDashboardMock />
                </template>
            </FeatureShowcase>
        </StackingFeatureCards>

        <PeopleSection
            :whatsapp-href="demoLink"
            @whatsapp="onWhatsApp('founder')"
        />

        <!-- 4. Live demo -->
        <section
            v-if="liveDemo"
            id="demo"
            class="fade-in-section scroll-mt-24 py-16 sm:py-20 lg:pt-0"
        >
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col gap-8 rounded-3xl bg-white p-8 shadow-[0_30px_60px_-40px_rgba(33,13,2,0.4)] ring-1 ring-marketing-border sm:flex-row sm:items-center sm:p-10"
                >
                    <img
                        src="/images/demo/blue-mahoe-logo.svg"
                        :alt="liveDemo.name"
                        class="size-16 self-start sm:size-20 sm:self-center"
                    />
                    <div class="flex-1">
                        <h2
                            class="text-2xl font-semibold tracking-tight sm:text-3xl"
                        >
                            Don't take our word for it. Click around.
                        </h2>
                        <p
                            class="mt-3 text-base leading-relaxed text-marketing-ink-muted"
                        >
                            {{ liveDemo.name }} is a made up courier running on
                            CourierOS. Log in as a customer or as the courier
                            and try it all: pre-alerts, tracking, receiving
                            packages, reports. Yours works the same way, with
                            your name on it.
                        </p>
                        <a
                            :href="liveDemo.url"
                            target="_blank"
                            rel="noopener"
                            class="mt-5 inline-flex items-center gap-2 text-base font-semibold text-marketing-ink underline decoration-marketing-orange decoration-2 underline-offset-4 hover:decoration-marketing-ink"
                            @click="onDemo('demo_section')"
                        >
                            Open the live demo
                            <ExternalLink class="size-4" aria-hidden="true" />
                        </a>
                        <p class="mt-2 text-sm text-marketing-ink-muted">
                            No signup. One tap logins on the demo page.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Offer -->
        <section id="offer" class="scroll-mt-24 pb-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-[28px] bg-marketing-ink text-white shadow-[0_50px_100px_-50px_rgba(226,83,10,0.7)]"
                >
                    <div
                        class="grid gap-10 p-8 sm:p-12 lg:grid-cols-[1fr_1.1fr]"
                    >
                        <div>
                            <BlurRevealText
                                text="Everything to run the business. One price."
                                class="text-3xl leading-tight font-semibold tracking-[-0.03em] sm:text-4xl"
                            />
                            <div class="mt-8 flex items-end gap-2">
                                <span
                                    class="text-6xl font-semibold tracking-tight tabular-nums"
                                    >{{ money(pricing.monthly) }}</span
                                >
                                <span class="pb-2 text-white/60">a month</span>
                            </div>
                            <p class="mt-3 text-base text-white/75">
                                One-time setup
                                <template v-if="foundingOpen">
                                    <span class="text-white/45 line-through">{{
                                        money(pricing.setup)
                                    }}</span>
                                    <span
                                        class="ml-1 font-semibold text-marketing-amber"
                                        >{{ money(sales.founding.setup) }}</span
                                    >
                                </template>
                                <span v-else class="font-semibold text-white">{{
                                    money(pricing.setup)
                                }}</span>
                            </p>
                            <p
                                v-if="foundingOpen"
                                class="mt-5 rounded-xl bg-white/[0.07] p-4 text-sm leading-relaxed text-white/80"
                            >
                                <span class="font-semibold text-marketing-amber"
                                    >Founding Jamaica cohort.</span
                                >
                                The first
                                {{ sales.founding.spotsTotal }} couriers to
                                launch get the reduced setup and a direct line
                                to us while we build what they ask for.
                                <span class="font-semibold text-white"
                                    >{{ sales.founding.spotsLeft }} of
                                    {{ sales.founding.spotsTotal }} spots
                                    left.</span
                                >
                            </p>
                        </div>

                        <div>
                            <ul class="space-y-3">
                                <li
                                    v-for="item in fullPackage"
                                    :key="item"
                                    class="flex items-start gap-3 text-base text-white/90"
                                >
                                    <Check
                                        class="mt-1 size-4 shrink-0 text-marketing-amber"
                                        :stroke-width="3"
                                        aria-hidden="true"
                                    />
                                    {{ item }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-3 border-t border-white/10 bg-white/[0.03] p-8 sm:flex-row sm:items-center sm:px-12"
                    >
                        <a
                            :href="foundingOpen ? foundingLink : demoLink"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-marketing-amber px-6 py-4 text-base font-semibold text-marketing-ink transition hover:brightness-95"
                            @click="onWhatsApp('offer')"
                        >
                            {{
                                foundingOpen
                                    ? 'Claim a founding spot on WhatsApp'
                                    : 'Get a free demo on WhatsApp'
                            }}
                        </a>
                        <a
                            href="/signup"
                            class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-4 text-base font-semibold text-white transition hover:border-white/40"
                            @click="onSignup('offer')"
                        >
                            Sign up now
                        </a>
                        <p
                            class="text-sm text-white/55 sm:ml-auto sm:max-w-[200px]"
                        >
                            Month to month. No per-package fees.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div
                        class="flex items-start gap-3 rounded-2xl bg-white p-4 text-sm text-marketing-ink-muted ring-1 ring-marketing-border"
                    >
                        <Wallet
                            class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                            aria-hidden="true"
                        />
                        Your customers pay you directly. We never take a cut.
                    </div>
                    <div
                        class="flex items-start gap-3 rounded-2xl bg-white p-4 text-sm text-marketing-ink-muted ring-1 ring-marketing-border"
                    >
                        <ShieldCheck
                            class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                            aria-hidden="true"
                        />
                        Your data is kept separate from every other courier.
                    </div>
                    <div
                        class="flex items-start gap-3 rounded-2xl bg-white p-4 text-sm text-marketing-ink-muted ring-1 ring-marketing-border"
                    >
                        <Globe2
                            class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                            aria-hidden="true"
                        />
                        Prices and totals in JMD and USD for your customers.
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Why not the alternatives -->
        <section class="fade-in-section bg-marketing-eggshell/50 py-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <BlurRevealText
                    text="Everyone else gives you a piece. This is the whole business."
                    class="mx-auto max-w-2xl text-center text-3xl font-semibold tracking-[-0.03em] text-balance sm:text-4xl"
                />
                <div class="mt-12 grid gap-4 lg:grid-cols-3">
                    <div
                        v-for="(option, index) in comparison"
                        :key="option.title"
                        class="fade-in-item rounded-2xl p-6"
                        :class="
                            option.featured
                                ? 'bg-marketing-ink text-white'
                                : 'bg-white ring-1 ring-marketing-border'
                        "
                        :style="itemDelay(index)"
                    >
                        <p class="text-lg font-semibold">{{ option.title }}</p>
                        <p
                            class="mt-1 text-sm"
                            :class="
                                option.featured
                                    ? 'text-white/65'
                                    : 'text-marketing-ink-muted'
                            "
                        >
                            {{ option.subtitle }}
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li
                                v-for="(has, i) in option.rows"
                                :key="i"
                                class="flex items-start gap-2.5 text-sm"
                            >
                                <Check
                                    v-if="has === true"
                                    class="mt-0.5 size-4 shrink-0"
                                    :class="
                                        option.featured
                                            ? 'text-marketing-amber'
                                            : 'text-marketing-orange'
                                    "
                                    :stroke-width="3"
                                />
                                <X
                                    v-else-if="has === false"
                                    class="mt-0.5 size-4 shrink-0 opacity-40"
                                />
                                <span
                                    v-else
                                    class="mt-0.5 size-4 shrink-0 text-center text-xs font-bold opacity-60"
                                    >~</span
                                >
                                <span
                                    :class="has === false ? 'opacity-55' : ''"
                                >
                                    {{ comparisonLabels[i]
                                    }}<template v-if="typeof has === 'string'">
                                        ({{ has.toLowerCase() }})</template
                                    >
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. Objections -->
        <section class="fade-in-section py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <BlurRevealText
                    text="What couriers in Jamaica ask us first"
                    class="text-center text-3xl font-semibold tracking-[-0.03em] text-balance sm:text-4xl"
                />
                <div class="mt-10 space-y-3">
                    <Collapsible
                        v-for="(faq, index) in faqs"
                        :key="faq.q"
                        :open="openFaq === index"
                        class="rounded-2xl bg-white ring-1 ring-marketing-border"
                        @update:open="(open) => (openFaq = open ? index : null)"
                    >
                        <CollapsibleTrigger
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left text-base font-semibold"
                        >
                            {{ faq.q }}
                            <ChevronDown
                                class="size-4 shrink-0 text-marketing-ink-muted transition-transform"
                                :class="{ 'rotate-180': openFaq === index }"
                            />
                        </CollapsibleTrigger>
                        <CollapsibleContent
                            class="px-6 pb-5 text-base leading-relaxed text-marketing-ink-muted"
                        >
                            {{ faq.a }}
                        </CollapsibleContent>
                    </Collapsible>
                </div>
            </div>
        </section>

        <!-- 8. Last ask -->
        <section
            class="relative overflow-hidden bg-marketing-ink py-24 text-white"
        >
            <div
                aria-hidden="true"
                class="absolute inset-0 bg-[radial-gradient(60%_80%_at_50%_120%,rgba(255,122,0,0.45),transparent_70%)]"
            />
            <div class="relative mx-auto max-w-2xl px-4 text-center sm:px-6">
                <BlurRevealText
                    text="Your customers are already shopping in the US. Be the one they ship with."
                    class="text-3xl leading-tight font-semibold tracking-[-0.03em] text-balance sm:text-5xl"
                />
                <p class="mx-auto mt-5 max-w-lg text-lg text-white/70">
                    Send us a message. We'll show you the platform on a quick
                    call and answer anything, no pressure.
                </p>
                <a
                    :href="demoLink"
                    target="_blank"
                    rel="noopener"
                    class="mt-9 inline-flex items-center gap-2.5 rounded-xl bg-marketing-amber px-8 py-4 text-base font-semibold text-marketing-ink transition hover:-translate-y-0.5 hover:brightness-95"
                    @click="onWhatsApp('final')"
                >
                    Get a free demo on WhatsApp
                </a>
                <p class="mt-4 text-sm text-white/50">
                    Prefer to start straight away?
                    <a
                        href="/signup"
                        class="font-semibold text-white underline underline-offset-4"
                        @click="onSignup('final')"
                        >Sign up here</a
                    >
                </p>
            </div>
        </section>

        <MarketingFooter />

        <!-- Sticky mobile CTA -->
        <Transition name="bar">
            <div
                v-if="showStickyBar"
                class="fixed inset-x-0 bottom-0 z-50 border-t border-marketing-border bg-marketing-cream/95 p-3 backdrop-blur md:hidden"
                :style="{
                    paddingBottom: 'max(0.75rem, env(safe-area-inset-bottom))',
                }"
            >
                <div class="flex gap-2">
                    <a
                        :href="demoLink"
                        target="_blank"
                        rel="noopener"
                        class="flex-1 rounded-xl bg-marketing-amber px-4 py-3.5 text-center text-[15px] font-semibold text-marketing-ink"
                        @click="onWhatsApp('sticky')"
                    >
                        Free demo on WhatsApp
                    </a>
                    <a
                        href="#offer"
                        class="rounded-xl border border-marketing-ink/15 bg-white px-4 py-3.5 text-[15px] font-semibold"
                    >
                        Price
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.bar-enter-active,
.bar-leave-active {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.bar-enter-from,
.bar-leave-to {
    transform: translateY(100%);
}
</style>
