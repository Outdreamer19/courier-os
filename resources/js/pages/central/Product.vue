<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Bell,
    Building2,
    Check,
    CreditCard,
    FileText,
    Globe,
    LineChart,
    MessageSquare,
    PackageSearch,
    ShieldCheck,
    Users,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import BlurRevealText from '@/components/marketing/BlurRevealText.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import ProductTour from '@/components/marketing/ProductTour.vue';
import RateCalculatorDemo from '@/components/marketing/RateCalculatorDemo.vue';
import WorkflowSteps from '@/components/marketing/WorkflowSteps.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import type { TourStop, WorkflowStep } from '@/types/marketing';

defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

const { itemDelay } = useScrollReveal();

const shot = (name: string) => ({
    image: `/images/marketing/${name}.png`,
    webp: `/images/marketing/${name}.webp`,
});

const tourStops: TourStop[] = [
    {
        id: 'prealerts',
        label: 'Pre-alerts',
        title: 'Every pre-alert lands in one queue',
        body: 'Customers submit the merchant, tracking number and invoice before the parcel ships. CourierOS matches it to the package the moment it arrives at your warehouse — no more digging through WhatsApp to work out whose box this is.',
        bullets: [
            'Automatic matching on tracking number',
            'Flag issues and request a better invoice in one click',
            'Filter by status, customer or merchant instantly',
        ],
        alt: 'The CourierOS pre-alerts queue showing merchant, customer, tracking number and status for each submission',
        ...shot('admin-pre-alerts'),
    },
    {
        id: 'packages',
        label: 'Packages',
        title: 'One screen for the whole warehouse',
        body: 'Reference, customer, status and amount due for every parcel you hold. Update a status and the customer is notified and their portal updates — you do not send a single message by hand.',
        bullets: [
            'Search and filter by status or payment state',
            'Bulk status updates as a shipment clears',
            'Outstanding balance visible on every row',
        ],
        alt: 'The CourierOS package list showing reference, customer, delivery status and amount due',
        ...shot('admin-packages'),
    },
    {
        id: 'portal',
        label: 'Customer portal',
        title: 'Your customers stop calling to ask',
        body: 'Each customer gets their own branded login with their Florida address, their packages, their balance and a live status timeline. The questions that used to eat your afternoon answer themselves.',
        bullets: [
            'Personal Florida address with their suite reference',
            'Live status timeline on every package',
            'Pay online, or see exactly what to bring to pickup',
        ],
        alt: 'The CourierOS customer dashboard showing a personal Florida shipping address, active packages and amount due',
        ...shot('customer-dashboard'),
    },
    {
        id: 'tracking',
        label: 'Tracking',
        title: 'A status timeline customers actually trust',
        body: 'Received in Florida, shipped, arrived, ready for pickup, collected — each step timestamped and visible to the customer as it happens. No more "any update?" at nine at night.',
        bullets: [
            'Timestamped step-by-step history',
            'Payment status and amount due alongside',
            'Works the same on a phone as on a laptop',
        ],
        alt: 'A CourierOS package detail page showing the status timeline from Florida warehouse through to pickup',
        ...shot('customer-package-detail'),
    },
    {
        id: 'rates',
        label: 'Rates',
        title: 'Quote a customer in three seconds',
        body: 'Set your weight bands, per-pound rates and minimum charge once. Staff quote from the same tiers you bill from, so the number on the phone is the number on the invoice.',
        bullets: [
            'Unlimited weight tiers with minimum charges',
            'Built-in calculator for phone and chat quotes',
            'Rates feed package charges automatically',
        ],
        alt: 'The CourierOS shipping rates screen with weight tiers and a rate calculator quoting a 5 lb package',
        ...shot('admin-shipping-rates'),
    },
    {
        id: 'reports',
        label: 'Reports',
        title: 'Know what the business actually made',
        body: 'Twelve months of revenue, package volume and outstanding balance, plus your top customers by spend. Export the whole thing to CSV when your accountant asks.',
        bullets: [
            'Revenue and volume trends month by month',
            'Outstanding balance across unpaid packages',
            'Top customers ranked by revenue',
        ],
        alt: 'The CourierOS reports screen showing revenue by month, package volume and top customers',
        ...shot('admin-reports'),
    },
    {
        id: 'inbox',
        label: 'Inbox',
        title: 'Customer questions in one thread, not five apps',
        body: 'Messages from your public contact form land in a shared inbox your whole team can see, with a status on each one, so nothing sits unanswered in somebody’s personal DMs.',
        bullets: [
            'Shared inbox for the whole team',
            'New, read and resolved states',
            'Search by name, email or subject',
        ],
        alt: 'The CourierOS contact inbox showing customer messages with new, read and resolved statuses',
        ...shot('admin-contact-inbox'),
    },
];

const workflow: WorkflowStep[] = [
    {
        actor: 'Your customer',
        title: 'They shop online with their Florida address',
        body: 'Every customer gets a Florida address with their own suite reference the moment they sign up. They copy it straight into checkout at Amazon, SHEIN or anywhere else.',
    },
    {
        actor: 'Your customer',
        title: 'They submit a pre-alert',
        body: 'Merchant, tracking number, value and invoice — filed from their portal before the parcel even leaves the warehouse. You know what is coming and what it is worth.',
    },
    {
        actor: 'Your warehouse',
        title: 'The parcel arrives and matches itself',
        body: 'Log the package against the tracking number and CourierOS links it to the waiting pre-alert and the right customer automatically. Weight goes in, the charge comes out of your rate tiers.',
    },
    {
        actor: 'Your team',
        title: 'You move it through the workflow',
        body: 'Shipped, arrived, ready for pickup. Each status change notifies the customer by email or WhatsApp and updates their portal — you never write the message.',
    },
    {
        actor: 'Your customer',
        title: 'They pay and collect',
        body: 'They pay by card through your own Stripe account, or in person at pickup with their reference and ID. Either way the balance clears and the package closes out.',
    },
    {
        actor: 'You',
        title: 'You see what the month was worth',
        body: 'Revenue, volume and outstanding balance update as you go, so the end of the month is a report you read, not a night you spend rebuilding a spreadsheet.',
    },
];

const beforeAfter = {
    before: [
        'Pre-alerts buried in WhatsApp and email',
        'A spreadsheet that only one person can safely edit',
        '"Any update?" messages every evening',
        'Rates quoted from memory, invoiced differently',
        'No idea what you actually earned last month',
        'Everything stops when your one staff member is out',
    ],
    after: [
        'Pre-alerts filed in a structured queue',
        'One shared system your whole team works from',
        'Customers check their own status timeline',
        'Staff quote from the tiers you bill from',
        'Revenue and volume, twelve months at a glance',
        'Roles and logins for everyone who needs one',
    ],
};

const capabilities = [
    {
        icon: PackageSearch,
        title: 'Pre-alerts & matching',
        body: 'Structured intake with automatic package matching and issue flags.',
    },
    {
        icon: Bell,
        title: 'Status notifications',
        body: 'Email and WhatsApp updates fire on every status change.',
    },
    {
        icon: CreditCard,
        title: 'Payments via your Stripe',
        body: 'Customers pay you directly. CourierOS never touches the money.',
    },
    {
        icon: LineChart,
        title: 'Revenue reporting',
        body: 'Twelve-month revenue, volume and balance, exportable to CSV.',
    },
    {
        icon: Users,
        title: 'Customer accounts',
        body: 'Self-serve portal, personal Florida address, authorised pickup people.',
    },
    {
        icon: Building2,
        title: 'Warehouse addresses',
        body: 'Manage the intake addresses your customers ship to.',
    },
    {
        icon: ShieldCheck,
        title: 'Roles & audit log',
        body: 'Owner, admin and staff permissions with a full activity trail.',
    },
    {
        icon: Globe,
        title: 'Your brand, your domain',
        body: 'A branded subdomain out of the box, custom domain on Enterprise.',
    },
    {
        icon: FileText,
        title: 'Invoices & receipts',
        body: 'Generate invoices from pre-alerts and packages without leaving the app.',
    },
    {
        icon: MessageSquare,
        title: 'Shared contact inbox',
        body: 'Public form messages triaged by the team, not lost in a personal DM.',
    },
    {
        icon: BadgeCheck,
        title: 'Rate tiers & calculator',
        body: 'Weight bands, minimums and a quoting tool that matches your billing.',
    },
    {
        icon: ShieldCheck,
        title: 'Isolated tenant data',
        body: 'Your customers and packages are separated from every other business.',
    },
];

const objections = [
    {
        q: 'I already have a system that works. Why switch?',
        a: 'Most courier businesses we talk to run on a spreadsheet plus WhatsApp. That works until two people need to edit at once, or a customer asks about a package from three months ago. CourierOS keeps the same workflow you already have — pre-alert, receive, ship, pickup — but makes it something your whole team and all your customers can see at the same time.',
    },
    {
        q: 'How long does it take to get running?',
        a: 'You can create your account, set your branding, add your rate tiers and your warehouse address inside an afternoon. Importing your existing customers is the longest part, and on Enterprise we do that migration for you.',
    },
    {
        q: 'Will my customers actually use the portal?',
        a: 'They use it because it answers the question they were going to message you about anyway — where is my package and what do I owe. Every status update links straight back to their timeline.',
    },
    {
        q: 'Does CourierOS take a cut of my revenue?',
        a: 'No. Your customers pay into your own Stripe account. CourierOS charges you a flat monthly platform fee and nothing else — no per-package fee, no percentage.',
    },
    {
        q: 'What if I have more than one warehouse?',
        a: 'Starter covers a single warehouse address, which is what most operations need. Multiple locations are part of Enterprise — get in touch and we will walk through your setup.',
    },
    {
        q: 'Can I get my data out?',
        a: 'Yes, at any time. Customers, packages and revenue history all export to CSV from the admin, whether or not you are still a customer.',
    },
];

const openObjection = ref<number | null>(0);
</script>

<template>
    <Head title="Product">
        <meta
            name="description"
            content="See exactly what CourierOS gives you: pre-alert matching, package tracking, a branded customer portal, rate tiers, payments and revenue reporting."
        />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="CourierOS Product" />
        <meta
            property="og:description"
            content="See exactly what CourierOS gives you: pre-alert matching, package tracking, a branded customer portal, rate tiers, payments and revenue reporting."
        />
        <meta
            property="og:image"
            content="/images/marketing/admin-dashboard.png"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="CourierOS Product" />
        <meta
            name="twitter:description"
            content="See exactly what CourierOS gives you: pre-alert matching, package tracking, a branded customer portal, rate tiers, payments and revenue reporting."
        />
        <meta
            name="twitter:image"
            content="/images/marketing/admin-dashboard.png"
        />
    </Head>

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <MarketingNav />

        <!-- Hero -->
        <section class="relative overflow-hidden pt-16 pb-20 sm:pt-24">
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[36rem] bg-[radial-gradient(70%_55%_at_50%_0%,rgba(255,122,0,0.16),transparent_72%)]"
            />

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        The product
                    </p>
                    <BlurRevealText
                        as="h1"
                        immediate
                        text="Everything your courier business runs on, in one place"
                        class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl lg:text-6xl"
                    />
                    <p
                        class="mx-auto mt-6 max-w-2xl text-lg text-pretty text-marketing-ink-muted"
                    >
                        Pre-alerts, package tracking, a branded customer portal,
                        rate tiers, payments and reporting — built specifically
                        for Caribbean courier and freight-forwarding
                        businesses.
                    </p>

                    <div
                        class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <a
                            href="/signup"
                            class="w-full rounded-xl bg-marketing-ink px-8 py-3.5 text-center text-sm font-semibold text-white shadow-lg shadow-marketing-ink/20 transition hover:-translate-y-0.5 hover:shadow-xl sm:w-auto"
                        >
                            Get Started
                        </a>
                        <a
                            href="#tour"
                            class="w-full rounded-xl border border-marketing-border bg-white px-8 py-3.5 text-center text-sm font-semibold transition hover:-translate-y-0.5 hover:border-marketing-ink/25 sm:w-auto"
                        >
                            Take the tour ↓
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-marketing-ink-muted">
                        ${{ pricing.monthly }}/month · unlimited packages and
                        customers · cancel anytime
                    </p>
                </div>

                <!-- Hero shot with floating callouts -->
                <div class="relative mx-auto mt-14 max-w-5xl">
                    <div
                        class="hero-shot overflow-hidden rounded-2xl border border-marketing-border bg-white shadow-[0_40px_100px_-40px_rgba(33,13,2,0.6)]"
                    >
                        <div
                            class="flex items-center gap-1.5 border-b border-marketing-border bg-marketing-eggshell/60 px-4 py-2.5"
                        >
                            <span
                                class="size-2.5 rounded-full bg-marketing-orange/50"
                            />
                            <span
                                class="size-2.5 rounded-full bg-marketing-amber/60"
                            />
                            <span
                                class="size-2.5 rounded-full bg-marketing-olive/40"
                            />
                        </div>
                        <picture>
                            <source
                                srcset="/images/marketing/admin-overview.webp"
                                type="image/webp"
                            />
                            <img
                                src="/images/marketing/admin-overview.png"
                                alt="The CourierOS admin dashboard showing package counts, revenue and recent activity"
                                width="1440"
                                height="900"
                                fetchpriority="high"
                                decoding="async"
                                class="block w-full"
                            />
                        </picture>
                    </div>

                    <!-- Decorative on small screens where there is no room. -->
                    <div
                        aria-hidden="true"
                        class="float-card absolute -top-5 -left-4 hidden rounded-xl border border-marketing-border bg-white px-4 py-3 shadow-lg lg:block"
                    >
                        <p
                            class="text-[0.6rem] font-semibold tracking-[0.14em] text-marketing-ink-muted uppercase"
                        >
                            Pre-alert matched
                        </p>
                        <p class="mt-1 text-sm font-semibold">
                            TSL-TRK-001 → Andre C.
                        </p>
                    </div>
                    <div
                        aria-hidden="true"
                        class="float-card float-card--delayed absolute -right-4 -bottom-5 hidden rounded-xl border border-marketing-border bg-white px-4 py-3 shadow-lg lg:block"
                    >
                        <p
                            class="text-[0.6rem] font-semibold tracking-[0.14em] text-marketing-ink-muted uppercase"
                        >
                            Ready for pickup
                        </p>
                        <p class="mt-1 text-sm font-semibold">
                            Customer notified ✓
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Before / after -->
        <section class="fade-in-section border-y border-marketing-border py-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        Why change
                    </p>
                    <BlurRevealText
                        text="The spreadsheet was fine until it wasn't"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-2">
                    <div
                        class="fade-in-item rounded-2xl border border-marketing-border bg-marketing-eggshell/40 p-7"
                        :style="itemDelay(0)"
                    >
                        <p
                            class="text-xs font-semibold tracking-[0.16em] text-marketing-ink-muted uppercase"
                        >
                            Running it manually
                        </p>
                        <ul class="mt-5 space-y-3.5 text-sm">
                            <li
                                v-for="line in beforeAfter.before"
                                :key="line"
                                class="flex items-start gap-3 text-marketing-ink-muted"
                            >
                                <X
                                    class="mt-0.5 size-4 shrink-0 text-marketing-ink-muted/60"
                                    aria-hidden="true"
                                />
                                <span>{{ line }}</span>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="fade-in-item rounded-2xl border-2 border-marketing-orange/35 bg-white p-7 shadow-[0_24px_70px_-32px_rgba(226,83,10,0.45)]"
                        :style="itemDelay(1)"
                    >
                        <p
                            class="text-xs font-semibold tracking-[0.16em] text-marketing-orange uppercase"
                        >
                            Running it on CourierOS
                        </p>
                        <ul class="mt-5 space-y-3.5 text-sm">
                            <li
                                v-for="line in beforeAfter.after"
                                :key="line"
                                class="flex items-start gap-3"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                    aria-hidden="true"
                                />
                                <span>{{ line }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive tour -->
        <section id="tour" class="fade-in-section scroll-mt-20 py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        Guided tour
                    </p>
                    <BlurRevealText
                        text="See the actual product"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                    <p class="mt-4 text-base text-pretty text-marketing-ink-muted">
                        Not mockups — these are real screens from a live
                        CourierOS courier business. Pick a tab, or let it run.
                    </p>
                </div>

                <ProductTour :stops="tourStops" />
            </div>
        </section>

        <!-- Workflow -->
        <section
            class="fade-in-section border-y border-marketing-border bg-marketing-eggshell/35 py-20 sm:py-24"
        >
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        How it works
                    </p>
                    <BlurRevealText
                        text="From checkout to pickup, without a single manual message"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                </div>

                <WorkflowSteps :steps="workflow" />
            </div>
        </section>

        <!-- Interactive rate calculator -->
        <section class="fade-in-section py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div
                    class="grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16"
                >
                    <div>
                        <p
                            class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                        >
                            Try it yourself
                        </p>
                        <BlurRevealText
                            text="Quote a package right now"
                            class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                        />
                        <p
                            class="mt-5 text-base leading-relaxed text-pretty text-marketing-ink-muted"
                        >
                            This is the same calculator your staff use when a
                            customer calls. Drag the weight, watch the tier
                            switch, and see the minimum charge kick in — then
                            imagine doing it without opening a spreadsheet.
                        </p>
                        <ul class="mt-6 space-y-3 text-sm">
                            <li
                                v-for="line in [
                                    'Your own weight bands and per-pound rates',
                                    'Minimum charges applied automatically',
                                    'The quote your staff give is the charge you bill',
                                ]"
                                :key="line"
                                class="flex items-start gap-3"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                    aria-hidden="true"
                                />
                                <span>{{ line }}</span>
                            </li>
                        </ul>
                    </div>

                    <RateCalculatorDemo />
                </div>
            </div>
        </section>

        <!-- Capability grid -->
        <section
            class="fade-in-section border-y border-marketing-border bg-white py-20 sm:py-24"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        What's included
                    </p>
                    <BlurRevealText
                        text="All of it, on every plan"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                    <p class="mt-4 text-base text-marketing-ink-muted">
                        No feature gates on the parts you need to operate. The
                        plans differ on scale and support, not capability.
                    </p>
                </div>

                <div
                    class="mt-12 grid gap-x-8 gap-y-9 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="(item, index) in capabilities"
                        :key="item.title"
                        class="fade-in-item group"
                        :style="itemDelay(index, 40)"
                    >
                        <span
                            class="inline-grid size-10 place-items-center rounded-xl bg-marketing-orange/10 text-marketing-orange transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:bg-marketing-orange group-hover:text-white"
                        >
                            <component
                                :is="item.icon"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <h3 class="mt-4 text-base font-semibold">
                            {{ item.title }}
                        </h3>
                        <p
                            class="mt-1.5 text-sm leading-relaxed text-pretty text-marketing-ink-muted"
                        >
                            {{ item.body }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Objections -->
        <section class="fade-in-section py-20 sm:py-24">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        Straight answers
                    </p>
                    <BlurRevealText
                        text="The questions you're actually asking"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                </div>

                <div class="mt-10 space-y-3">
                    <div
                        v-for="(item, index) in objections"
                        :key="item.q"
                        class="overflow-hidden rounded-xl border border-marketing-border bg-white"
                    >
                        <h3>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left text-base font-semibold"
                                :aria-expanded="openObjection === index"
                                @click="
                                    openObjection =
                                        openObjection === index ? null : index
                                "
                            >
                                {{ item.q }}
                                <span
                                    class="grid size-5 shrink-0 place-items-center text-marketing-ink-muted transition-transform duration-300"
                                    :class="{
                                        'rotate-45': openObjection === index,
                                    }"
                                    aria-hidden="true"
                                >
                                    +
                                </span>
                            </button>
                        </h3>
                        <div
                            class="grid transition-[grid-template-rows] duration-300 ease-out"
                            :class="
                                openObjection === index
                                    ? 'grid-rows-[1fr]'
                                    : 'grid-rows-[0fr]'
                            "
                        >
                            <div class="overflow-hidden">
                                <p
                                    class="px-6 pb-4 text-sm leading-relaxed text-pretty text-marketing-ink-muted"
                                >
                                    {{ item.a }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-8 text-center text-sm text-marketing-ink-muted">
                    Ready to talk numbers?
                    <a
                        href="/pricing"
                        class="font-semibold text-marketing-orange underline-offset-4 hover:underline"
                        >See pricing →</a
                    >
                </p>
            </div>
        </section>

        <FinalCta />

        <MarketingFooter />
    </div>
</template>

<style scoped>
@keyframes hero-shot-in {
    from {
        opacity: 0;
        transform: perspective(1400px) rotateX(7deg) translateY(28px);
    }
    to {
        opacity: 1;
        transform: perspective(1400px) rotateX(0deg) translateY(0);
    }
}

.hero-shot {
    animation: hero-shot-in 1s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes float-card-in {
    from {
        opacity: 0;
        transform: translateY(12px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.float-card {
    animation: float-card-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.55s both;
}

.float-card--delayed {
    animation-delay: 0.8s;
}

@media (prefers-reduced-motion: reduce) {
    .hero-shot,
    .float-card {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
</style>
