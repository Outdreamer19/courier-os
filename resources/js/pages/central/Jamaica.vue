<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Check,
    ChevronDown,
    Globe2,
    MapPinned,
    MessageCircleMore,
    Rocket,
    ShieldCheck,
    Wallet,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import BlurRevealText from '@/components/marketing/BlurRevealText.vue';
import FeatureShowcase from '@/components/marketing/FeatureShowcase.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import ProductScreenshotFrame from '@/components/marketing/ProductScreenshotFrame.vue';
import StackingFeatureCards from '@/components/marketing/StackingFeatureCards.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useScrollReveal } from '@/composables/useScrollReveal';

const props = defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

const { itemDelay } = useScrollReveal();

const proofPoints = [
    { value: 'Minutes', label: 'To get your branded site live' },
    { value: `$${props.pricing.monthly}/mo`, label: 'Flat price, no per-package fees' },
    { value: '0%', label: 'Cut of what your customers pay you' },
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

const comparison = [
    {
        title: 'A one-off website',
        subtitle: 'An agency builds you a page',
        rows: [
            { label: 'Branded site', has: true },
            { label: 'Runs pre-alerts & tracking', has: false },
            { label: 'Customer self-serve portal', has: false },
            { label: 'Takes online payments', has: false },
            { label: 'Updates as your business grows', has: false },
        ],
    },
    {
        title: 'Spreadsheets & WhatsApp',
        subtitle: 'What most operators use today',
        rows: [
            { label: 'Branded site', has: false },
            { label: 'Runs pre-alerts & tracking', has: 'Manually' },
            { label: 'Customer self-serve portal', has: false },
            { label: 'Takes online payments', has: false },
            { label: 'Updates as your business grows', has: false },
        ],
    },
    {
        title: 'CourierOS',
        subtitle: 'Everything, from day one',
        featured: true,
        rows: [
            { label: 'Branded site', has: true },
            { label: 'Runs pre-alerts & tracking', has: true },
            { label: 'Customer self-serve portal', has: true },
            { label: 'Takes online payments', has: true },
            { label: 'Updates as your business grows', has: true },
        ],
    },
];

const faqs = [
    {
        q: 'How long does it take to get up and running?',
        a: 'Your branded site is live within minutes of signing up. Setting your rates and warehouse address takes another few minutes. Most operators are ready to send their first customer a link the same day.',
    },
    {
        q: 'How much does it cost?',
        a: `$${props.pricing.monthly}/month plus a one-time $${props.pricing.setup} setup fee, billed securely through Stripe — that's the whole platform, unlimited customers, packages and staff. No per-package fees. If you don't already have a Florida warehouse relationship, that's a separate arrangement — ask us and we'll point you to one.`,
    },
    {
        q: 'What do I actually need to do to launch?',
        a: 'Sign up, choose your subdomain and rates, confirm your Florida/US warehouse address, then share your link. From there your customers register themselves and start submitting pre-alerts.',
    },
    {
        q: 'Do I need to know how to code, or hire someone?',
        a: 'No. Branding, rates and your warehouse address are all set from your own admin dashboard. No code, no developer, no waiting on an agency.',
    },
    {
        q: 'Why CourierOS instead of building this myself?',
        a: 'A one-off website doesn’t track packages, take payments, or give your customers a portal. Spreadsheets and WhatsApp groups don’t scale. CourierOS is the operations software behind the business, not just a page with your logo on it.',
    },
    {
        q: 'Is my business data kept separate from other CourierOS businesses?',
        a: 'Yes. Every courier business on CourierOS has fully isolated data by design — nothing is shared or visible across accounts.',
    },
    {
        q: 'Do my customers pay CourierOS anything?',
        a: 'Never. Your customers pay you, through your own Stripe account. CourierOS only bills you, the courier business.',
    },
];

const openFaq = ref<number | null>(0);
</script>

<template>
    <Head title="CourierOS for Jamaica">
        <meta
            name="description"
            content="Start or upgrade a shop-and-ship courier business in Jamaica. A branded website, hosting, and the operations software behind it — one package, one price."
        />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="CourierOS for Jamaica" />
        <meta
            property="og:description"
            content="Start or upgrade a shop-and-ship courier business in Jamaica. A branded website, hosting, and the operations software behind it — one package, one price."
        />
        <meta
            property="og:image"
            content="/images/marketing/admin-dashboard.png"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="CourierOS for Jamaica" />
        <meta
            name="twitter:description"
            content="Start or upgrade a shop-and-ship courier business in Jamaica. A branded website, hosting, and the operations software behind it — one package, one price."
        />
        <meta
            name="twitter:image"
            content="/images/marketing/admin-dashboard.png"
        />
    </Head>

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <MarketingNav />

        <!-- Hero -->
        <section class="relative overflow-hidden pt-16 pb-10 sm:pt-24">
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
                    text="Always Wanted to Start a Courier Business? We Build It With You."
                    class="mt-3 text-4xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-5xl"
                />
                <p class="mx-auto mt-5 max-w-xl text-base text-pretty text-marketing-ink-muted">
                    Whether you're starting from scratch or already running one
                    out of WhatsApp and a spreadsheet, CourierOS is the branded
                    website, customer portal and admin console behind it —
                    one package, one price, live in minutes.
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
                <p class="mt-4 text-xs text-marketing-ink-muted">
                    See exactly what you get before you pay anything.
                </p>
            </div>

            <!-- Hero visual -->
            <div class="relative mx-auto mt-12 max-w-4xl px-4 sm:px-6 lg:px-8">
                <ProductScreenshotFrame
                    src="/images/marketing/admin-dashboard.png"
                    webp="/images/marketing/admin-dashboard.webp"
                    label="Admin dashboard"
                    alt="The CourierOS admin dashboard showing customer, pre-alert and package counts alongside sign-up, volume and pre-alert status charts — this is what running your business looks like from day one"
                />

                <!-- Proof points -->
                <dl class="mt-10 grid gap-6 border-t border-marketing-border pt-8 sm:grid-cols-3">
                    <div v-for="point in proofPoints" :key="point.label" class="text-center">
                        <dt class="text-lg font-semibold text-marketing-ink">{{ point.value }}</dt>
                        <dd class="mt-1 text-sm text-marketing-ink-muted">{{ point.label }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Two segments -->
        <section class="py-16">
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

        <!-- How it works -->
        <StackingFeatureCards
            eyebrow="How it works"
            title="From sign-up to your first customer"
            body="Four steps, most of them done the same day you start."
        >
            <FeatureShowcase
                eyebrow="Day 1"
                title="Sign up & brand your site"
                body="Pick a subdomain, add your name and colors, and your site is live — no waiting on a developer or an agency."
                :bullets="['Live in minutes', 'No code required']"
            >
                <template #visual>
                    <ProductScreenshotFrame
                        src="/images/marketing/courieros-login.png"
                        webp="/images/marketing/courieros-login.webp"
                        label="Sign in"
                        alt="The CourierOS sign-in screen for a newly branded courier business"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                eyebrow="Day 1–2"
                title="Set your rates & warehouse address"
                body="Enter your per-pound rate and your Florida address. Both are editable any time from your dashboard, so you can adjust as you learn."
                :bullets="['Weight-based rate calculator', 'Editable warehouse address']"
            >
                <template #visual>
                    <ProductScreenshotFrame
                        src="/images/marketing/admin-shipping-rates.png"
                        webp="/images/marketing/admin-shipping-rates.webp"
                        label="Shipping rates"
                        alt="The CourierOS admin screen for setting shipping rates by weight"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                eyebrow="This week"
                title="Bring your customers on"
                body="Share your link. Customers register themselves, get their own reference number and Florida address, and submit pre-alerts without calling you."
                :bullets="['Customers self-serve', 'No phone calls needed']"
            >
                <template #visual>
                    <ProductScreenshotFrame
                        src="/images/marketing/customer-pre-alert-create.png"
                        webp="/images/marketing/customer-pre-alert-create.webp"
                        label="Submit a pre-alert"
                        alt="A CourierOS customer submitting a pre-alert with invoice and shipment details"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                eyebrow="Ongoing"
                title="Run it like a business"
                body="Track every package to pickup, bill online or in person, and see your revenue and volume at a glance instead of guessing."
                :bullets="['Full status timeline', 'Revenue & volume reporting']"
            >
                <template #visual>
                    <ProductScreenshotFrame
                        src="/images/marketing/admin-reports.png"
                        webp="/images/marketing/admin-reports.webp"
                        label="Reports"
                        alt="The CourierOS reports screen showing revenue by month, package volume and top customers"
                    />
                </template>
            </FeatureShowcase>
        </StackingFeatureCards>

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
                    <p class="mt-4 text-xs text-marketing-ink-muted">
                        That's the software cost, no per-package fees. If you don't
                        already have a Florida warehouse relationship, that's a
                        separate arrangement — ask us and we'll point you to one.
                    </p>
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

        <!-- Why CourierOS -->
        <section class="fade-in-section bg-marketing-eggshell/40 py-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase">Why us</p>
                    <BlurRevealText
                        text="Everyone else gives you a piece. This is the whole business."
                        class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl"
                    />
                </div>

                <div class="mt-12 grid gap-6 lg:grid-cols-3">
                    <div
                        v-for="(option, index) in comparison"
                        :key="option.title"
                        class="fade-in-item rounded-2xl border p-6"
                        :class="
                            option.featured
                                ? 'border-marketing-orange bg-marketing-ink text-white shadow-[0_30px_80px_-32px_rgba(226,83,10,0.55)]'
                                : 'border-marketing-border bg-white'
                        "
                        :style="itemDelay(index)"
                    >
                        <p class="text-lg font-semibold" :class="option.featured ? 'text-white' : 'text-marketing-ink'">
                            {{ option.title }}
                        </p>
                        <p class="mt-1 text-sm" :class="option.featured ? 'text-white/75' : 'text-marketing-ink-muted'">
                            {{ option.subtitle }}
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li
                                v-for="row in option.rows"
                                :key="row.label"
                                class="flex items-start gap-2.5 text-sm"
                                :class="option.featured ? 'text-white/90' : 'text-marketing-ink'"
                            >
                                <Check
                                    v-if="row.has === true"
                                    class="mt-0.5 size-4 shrink-0"
                                    :class="option.featured ? 'text-marketing-amber' : 'text-marketing-orange'"
                                />
                                <X
                                    v-else-if="row.has === false"
                                    class="mt-0.5 size-4 shrink-0 opacity-50"
                                />
                                <span
                                    v-else
                                    class="mt-0.5 size-4 shrink-0 text-center text-[10px] font-bold opacity-70"
                                    >~</span
                                >
                                <span>{{ row.label }}{{ typeof row.has === 'string' ? ` (${row.has})` : '' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Case study -->
        <section class="fade-in-section py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl border border-marketing-border bg-white">
                    <div class="flex items-center gap-4 border-b border-marketing-border bg-marketing-eggshell/50 px-8 py-6">
                        <img
                            src="/images/tenants/today-shipping/logo.png"
                            alt="Today Shipping logo"
                            class="h-10 w-auto"
                        />
                        <div>
                            <p class="text-xs font-semibold tracking-[0.16em] text-marketing-orange uppercase">Already live</p>
                            <h2 class="text-xl font-semibold text-marketing-ink">Today Shipping runs on CourierOS</h2>
                        </div>
                    </div>
                    <div class="grid gap-6 p-8 sm:grid-cols-[1fr_auto] sm:items-center">
                        <p class="text-sm leading-relaxed text-marketing-ink-muted">
                            Today Shipping is a real Jamaican courier business
                            running its site, pre-alerts, tracking and billing on
                            CourierOS today — proof this isn't a concept, it's a
                            working platform you can be running on too.
                        </p>
                        <img
                            src="/images/tenants/today-shipping/parcel.png"
                            alt="A Today Shipping parcel ready for pickup"
                            class="h-24 w-24 rounded-xl object-cover justify-self-center sm:justify-self-end"
                        />
                    </div>
                    <!--
                        TODO(Shane): swap the paragraph above for a direct quote
                        and real numbers (packages/month, time saved) once you
                        have them from Today Shipping — that's the strongest
                        proof point for both segments and only you can get it.
                    -->
                </div>

                <!-- Assurance -->
                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="flex items-start gap-2.5 rounded-xl border border-marketing-border bg-white p-4 text-sm text-marketing-ink-muted">
                        <ShieldCheck class="mt-0.5 size-4 shrink-0 text-marketing-orange" />
                        <span>Your data is isolated from every other business on CourierOS.</span>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-xl border border-marketing-border bg-white p-4 text-sm text-marketing-ink-muted">
                        <Wallet class="mt-0.5 size-4 shrink-0 text-marketing-orange" />
                        <span>Your customers pay you, not CourierOS. Cancel anytime.</span>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-xl border border-marketing-border bg-white p-4 text-sm text-marketing-ink-muted">
                        <Globe2 class="mt-0.5 size-4 shrink-0 text-marketing-orange" />
                        <span>Rates and totals shown in JMD & USD for your customers.</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="fade-in-section bg-marketing-eggshell/40 py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase">FAQ</p>
                    <BlurRevealText
                        text="What people starting out in Jamaica ask us"
                        class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl"
                    />
                </div>

                <div class="mt-10 space-y-3">
                    <Collapsible
                        v-for="(faq, index) in faqs"
                        :key="faq.q"
                        :open="openFaq === index"
                        class="rounded-xl border border-marketing-border bg-white"
                        @update:open="(open) => (openFaq = open ? index : null)"
                    >
                        <CollapsibleTrigger class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left text-base font-semibold text-marketing-ink">
                            {{ faq.q }}
                            <ChevronDown
                                class="size-4 shrink-0 text-marketing-ink-muted transition-transform"
                                :class="{ 'rotate-180': openFaq === index }"
                            />
                        </CollapsibleTrigger>
                        <CollapsibleContent class="px-6 pb-4 text-sm leading-relaxed text-marketing-ink-muted">
                            {{ faq.a }}
                        </CollapsibleContent>
                    </Collapsible>
                </div>

                <div class="mt-8 flex items-start gap-2.5 rounded-xl border border-marketing-border bg-white p-4 text-sm text-marketing-ink-muted">
                    <MapPinned class="mt-0.5 size-4 shrink-0 text-marketing-orange" aria-hidden="true" />
                    <p>
                        <span class="font-semibold text-marketing-ink">Don't have a Florida address yet? </span>
                        You'll need a US warehouse relationship before you can start
                        taking in packages — ask us and we'll point you to one.
                    </p>
                </div>
            </div>
        </section>

        <FinalCta />

        <MarketingFooter />
    </div>
</template>
