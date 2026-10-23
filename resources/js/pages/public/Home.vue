<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Bell,
    Check,
    ChevronDown,
    FileText,
    MapPin,
    MessageCircle,
    PackageCheck,
    Plane,
    ShoppingBag,
    UserPlus,
    Warehouse,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useBrand } from '@/composables/useBrand';
import { estimateShipping, formatMoney } from '@/lib/shippingEstimate';
import type { RateTier } from '@/lib/shippingEstimate';
import { rates, register } from '@/routes';
import type { RateSnapshot, WarehouseSnapshot } from '@/types/auth';

/**
 * The public homepage every courier gets on their own subdomain.
 *
 * Nothing here may name a courier, a country or a city literally: the name,
 * colours, warehouse, rates and WhatsApp number all come from the tenant, so
 * the same page reads as Blue Mahoe Express on the demo and as each real
 * courier on theirs. Colours arrive through the brand-* tokens, which
 * PublicLayout rescopes to the tenant's own primary and accent.
 */
const props = defineProps<{
    rate: RateSnapshot | null;
    rateTiers?: RateTier[];
}>();

const page = usePage();
const { brand, name: brandName } = useBrand();

const warehouse = computed(
    () => (page.props.warehouse as WarehouseSnapshot | null) ?? null,
);
const whatsappUrl = computed(() => brand.value?.whatsapp_url ?? null);

const currency = computed(
    () => props.rate?.currency ?? brand.value?.currency ?? 'USD',
);

const tiers = computed<RateTier[]>(() => {
    if (props.rateTiers?.length) {
        return props.rateTiers;
    }

    return props.rate ? [props.rate as RateTier] : [];
});

const perLb = computed(() =>
    formatMoney(currency.value, props.rate?.rate_per_lb ?? 0),
);

const warehouseArea = computed(() =>
    warehouse.value ? `${warehouse.value.city}, ${warehouse.value.state}` : null,
);

const examples = computed(() =>
    [1, 3, 5, 10].map((lbs) => ({
        lbs,
        amount: formatMoney(
            currency.value,
            estimateShipping(lbs, tiers.value).amount,
        ),
    })),
);

const stores = [
    'Amazon',
    'SHEIN',
    'Walmart',
    'Target',
    'eBay',
    'Best Buy',
    'Nike',
    'Temu',
];

const steps = computed(() => [
    {
        icon: UserPlus,
        title: 'Sign up free',
        body: `Create your ${brandName.value} account and get your own customer number and US address straight away.`,
    },
    {
        icon: ShoppingBag,
        title: 'Shop any US store',
        body: 'Check out as normal and use your US address as the delivery address.',
    },
    {
        icon: FileText,
        title: 'Send a pre-alert',
        body: 'Upload the invoice in your account so we can match the package the moment it lands.',
    },
    {
        icon: PackageCheck,
        title: 'Collect it',
        body: 'Follow every step in your account and we will message you when it is ready.',
    },
]);

const faqs = computed(() => [
    {
        q: 'How much does it cost to sign up?',
        a: `Nothing. Your ${brandName.value} account and US address are free. You only pay shipping when a package comes through.`,
    },
    {
        q: 'What address do I use when I shop?',
        a: warehouseArea.value
            ? `Your account shows our warehouse address in ${warehouseArea.value}, with your customer number on it. Use that as the delivery address at checkout.`
            : 'Your account shows our US warehouse address with your customer number on it. Use that as the delivery address at checkout.',
    },
    {
        q: 'How is shipping worked out?',
        a: `By weight. The current rate is ${perLb.value} per lb${props.rate?.minimum_charge ? `, with a minimum of ${formatMoney(currency.value, props.rate.minimum_charge)}` : ''}. We weigh every package and confirm the final amount before you pay.`,
    },
    {
        q: 'What is a pre-alert and do I need one?',
        a: 'A pre-alert tells us a package is on its way. Upload the invoice from your order and we match it to you as soon as it arrives, which keeps things moving quickly.',
    },
    {
        q: 'How will I know when my package is ready?',
        a: 'Every status change shows in your account, and we send you an update by email and WhatsApp when your package is ready.',
    },
]);

const timeline = [
    { label: 'Received in warehouse', done: true },
    { label: 'On the plane', done: true },
    { label: 'Arrived and sorted', done: true },
    { label: 'Ready for you', done: false, current: true },
];
</script>

<template>
    <Head title="Shop any US store. We'll bring it home." />

    <div class="overflow-x-clip">
        <!-- Hero -->
        <section class="relative overflow-hidden bg-brand-ink text-white">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div
                    class="absolute -top-40 -right-32 size-[620px] rounded-full bg-brand-green opacity-50 blur-[120px]"
                />
                <div
                    class="absolute -bottom-48 -left-40 size-[520px] rounded-full bg-brand-gold opacity-[0.14] blur-[120px]"
                />
                <div
                    class="absolute inset-0 opacity-[0.07] [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]"
                />
            </div>

            <div
                class="relative mx-auto grid max-w-7xl gap-14 px-4 pt-16 pb-20 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:gap-10 lg:px-8 lg:pt-24 lg:pb-28"
            >
                <div class="hero-rise max-w-xl">
                    <p
                        v-if="warehouseArea"
                        class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-sm text-white/80"
                    >
                        <span class="size-2 rounded-full bg-brand-gold" />
                        US warehouse in {{ warehouseArea }}
                    </p>

                    <h1
                        class="mt-6 text-[2.75rem] leading-[1.02] font-bold tracking-tight text-balance sm:text-6xl lg:text-[4.25rem]"
                    >
                        Shop any US store.
                        <span class="text-brand-gold">We'll bring it home.</span>
                    </h1>

                    <p class="mt-6 text-lg leading-relaxed text-white/75">
                        {{ brandName }} gives you your own US shipping address.
                        Shop Amazon, SHEIN, Walmart and more, then follow every
                        package from our warehouse right to you.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
                        <Link
                            :href="register()"
                            class="site-btn-accent inline-flex h-12 items-center gap-2 rounded-full px-6 text-base font-semibold"
                        >
                            Get your free US address
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="rates()"
                            class="inline-flex h-12 items-center rounded-full border border-white/25 px-6 text-base font-semibold text-white transition hover:bg-white/10"
                        >
                            Work out shipping
                        </Link>
                    </div>

                    <ul
                        class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-sm text-white/70"
                    >
                        <li class="flex items-center gap-2">
                            <Check class="size-4 text-brand-gold" />
                            From {{ perLb }} per lb
                        </li>
                        <li class="flex items-center gap-2">
                            <Check class="size-4 text-brand-gold" />
                            Free to sign up
                        </li>
                        <li class="flex items-center gap-2">
                            <Check class="size-4 text-brand-gold" />
                            Updates on WhatsApp
                        </li>
                    </ul>
                </div>

                <!-- Product picture: their address and a package on its way -->
                <div
                    class="hero-rise relative mx-auto w-full max-w-md lg:mr-0"
                    style="animation-delay: 120ms"
                    aria-hidden="true"
                >
                    <div
                        class="rounded-3xl bg-white p-6 text-slate-900 shadow-2xl shadow-black/30"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase"
                            >
                                Your US address
                            </p>
                            <span
                                class="rounded-full bg-brand-green-muted px-2.5 py-1 text-xs font-semibold text-brand-green"
                            >
                                Active
                            </span>
                        </div>
                        <div class="mt-4 space-y-1 text-[15px] leading-snug">
                            <p class="font-semibold">Your name</p>
                            <template v-if="warehouse">
                                <p>{{ warehouse.address_line_1 }}</p>
                                <p v-if="warehouse.address_line_2">
                                    {{ warehouse.address_line_2 }}
                                    <span
                                        class="ml-1 rounded-md bg-brand-gold/20 px-1.5 py-0.5 font-mono text-[13px] font-semibold"
                                        >+ your number</span
                                    >
                                </p>
                                <p>
                                    {{ warehouse.city }}, {{ warehouse.state }}
                                    {{ warehouse.zip }}
                                </p>
                            </template>
                            <template v-else>
                                <p>Our US warehouse</p>
                                <p>
                                    Suite
                                    <span
                                        class="rounded-md bg-brand-gold/20 px-1.5 py-0.5 font-mono text-[13px] font-semibold"
                                        >your number</span
                                    >
                                </p>
                            </template>
                        </div>
                    </div>

                    <div
                        class="relative -mt-4 ml-6 rounded-3xl bg-white p-6 text-slate-900 shadow-2xl shadow-black/30 sm:ml-12"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-slate-500">
                                    Amazon order · 3.2 lb
                                </p>
                                <p class="mt-0.5 text-lg font-semibold">
                                    Running shoes
                                </p>
                            </div>
                            <span
                                class="site-btn-accent rounded-full px-2.5 py-1 text-xs font-semibold"
                            >
                                Ready
                            </span>
                        </div>
                        <ol class="mt-5 space-y-3.5">
                            <li
                                v-for="step in timeline"
                                :key="step.label"
                                class="flex items-center gap-3 text-sm"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full"
                                    :class="
                                        step.done
                                            ? 'bg-brand-green text-white'
                                            : 'site-btn-accent ring-4 ring-brand-gold/25'
                                    "
                                >
                                    <Check v-if="step.done" class="size-3.5" />
                                    <MapPin v-else class="size-3.5" />
                                </span>
                                <span
                                    :class="
                                        step.current
                                            ? 'font-semibold'
                                            : 'text-slate-600'
                                    "
                                >
                                    {{ step.label }}
                                </span>
                            </li>
                        </ol>
                    </div>

                    <div
                        class="absolute -bottom-11 -left-2 flex items-center gap-2.5 rounded-2xl bg-white px-3.5 py-2.5 text-slate-900 shadow-xl shadow-black/25 ring-1 ring-black/5 sm:-left-8"
                    >
                        <span
                            class="flex size-8 items-center justify-center rounded-full bg-[#25D366] text-white"
                        >
                            <MessageCircle class="size-4" />
                        </span>
                        <div class="leading-tight">
                            <p class="text-xs text-slate-500">{{ brandName }}</p>
                            <p class="text-sm font-semibold">
                                Your package is ready
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stores -->
        <section class="border-b border-border bg-background">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-8 sm:px-6 lg:flex-row lg:gap-10 lg:px-8"
            >
                <p
                    class="shrink-0 text-sm font-medium whitespace-nowrap text-muted-foreground"
                >
                    Shop from the stores you love
                </p>
                <ul
                    class="flex flex-wrap items-center justify-center gap-x-8 gap-y-2 lg:justify-between lg:flex-1"
                >
                    <li
                        v-for="store in stores"
                        :key="store"
                        class="text-lg font-bold tracking-tight text-foreground/35"
                    >
                        {{ store }}
                    </li>
                </ul>
            </div>
        </section>

        <!-- How it works -->
        <section class="bg-background">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="site-eyebrow">How it works</p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-balance sm:text-5xl"
                    >
                        From checkout to your hands in four steps
                    </h2>
                </div>

                <ol class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li
                        v-for="(step, index) in steps"
                        :key="step.title"
                        class="relative rounded-3xl border border-border bg-card p-7"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="flex size-12 items-center justify-center rounded-2xl bg-brand-green-muted text-brand-green"
                            >
                                <component :is="step.icon" class="size-5" />
                            </span>
                            <span
                                class="text-5xl font-bold tracking-tight text-foreground/[0.07]"
                            >
                                {{ index + 1 }}
                            </span>
                        </div>
                        <h3 class="mt-6 text-lg font-semibold">
                            {{ step.title }}
                        </h3>
                        <p class="mt-2 leading-relaxed text-muted-foreground">
                            {{ step.body }}
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- What you get -->
        <section class="bg-brand-green-muted">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="site-eyebrow">Why {{ brandName }}</p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-balance sm:text-5xl"
                    >
                        You always know where your package is
                    </h2>
                </div>

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <article class="rounded-3xl bg-card p-8 shadow-sm">
                        <span
                            class="flex size-12 items-center justify-center rounded-2xl bg-brand-green text-white"
                        >
                            <Plane class="size-5" />
                        </span>
                        <h3 class="mt-6 text-xl font-semibold">
                            Track every step
                        </h3>
                        <p class="mt-2 leading-relaxed text-muted-foreground">
                            See when your package lands in the warehouse, gets
                            on the plane and is ready for you. No chasing, no
                            guessing.
                        </p>
                    </article>

                    <article class="rounded-3xl bg-card p-8 shadow-sm">
                        <span
                            class="flex size-12 items-center justify-center rounded-2xl bg-brand-green text-white"
                        >
                            <Bell class="size-5" />
                        </span>
                        <h3 class="mt-6 text-xl font-semibold">
                            Updates on WhatsApp and email
                        </h3>
                        <p class="mt-2 leading-relaxed text-muted-foreground">
                            We tell you the moment something changes, so you
                            find out your package is ready before you think to
                            ask.
                        </p>
                    </article>

                    <article class="rounded-3xl bg-card p-8 shadow-sm">
                        <span
                            class="flex size-12 items-center justify-center rounded-2xl bg-brand-green text-white"
                        >
                            <Warehouse class="size-5" />
                        </span>
                        <h3 class="mt-6 text-xl font-semibold">
                            One account for everything
                        </h3>
                        <p class="mt-2 leading-relaxed text-muted-foreground">
                            Your address, pre-alerts, invoices and what you owe,
                            all in one place you can open on your phone.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Rates -->
        <section class="bg-background">
            <div
                class="mx-auto grid max-w-7xl gap-12 px-4 py-24 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8"
            >
                <div class="max-w-xl">
                    <p class="site-eyebrow">Simple pricing</p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-balance sm:text-5xl"
                    >
                        Pay by the pound. Nothing hidden.
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-muted-foreground">
                        Signing up is free. When a package comes through we
                        weigh it and you pay for the weight, at the rate you can
                        see right here.
                    </p>
                    <Link
                        :href="rates()"
                        class="mt-8 inline-flex items-center gap-2 font-semibold text-brand-green hover:underline"
                    >
                        Work out your exact cost
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <div
                    class="rounded-3xl bg-brand-ink p-8 text-white shadow-xl sm:p-10"
                >
                    <p class="text-sm text-white/60">
                        {{ props.rate?.name ?? 'Standard air' }}
                    </p>
                    <p class="mt-2 text-5xl font-bold tracking-tight">
                        {{ perLb }}
                        <span class="text-lg font-medium text-white/60"
                            >per lb</span
                        >
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div
                            v-for="example in examples"
                            :key="example.lbs"
                            class="rounded-2xl bg-white/[0.07] p-4"
                        >
                            <p class="text-sm text-white/60">
                                {{ example.lbs }} lb
                            </p>
                            <p class="mt-1 font-semibold">
                                {{ example.amount }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 text-sm text-white/55">
                        Examples are estimates. We confirm the final amount
                        after weighing.
                    </p>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="border-t border-border bg-background">
            <div
                class="mx-auto grid max-w-7xl gap-12 px-4 py-24 sm:px-6 lg:grid-cols-[1fr_1.4fr] lg:px-8"
            >
                <div>
                    <p class="site-eyebrow">Questions</p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-balance sm:text-5xl"
                    >
                        Good to know
                    </h2>
                    <p class="mt-5 text-lg text-muted-foreground">
                        Anything else, just ask. We reply fast.
                    </p>
                </div>

                <div class="divide-y divide-border border-y border-border">
                    <details
                        v-for="(faq, index) in faqs"
                        :key="faq.q"
                        class="group py-5"
                        :open="index === 0"
                    >
                        <summary
                            class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-semibold [&::-webkit-details-marker]:hidden"
                        >
                            {{ faq.q }}
                            <ChevronDown
                                class="size-5 shrink-0 text-muted-foreground transition group-open:rotate-180"
                            />
                        </summary>
                        <p
                            class="mt-3 max-w-2xl leading-relaxed text-muted-foreground"
                        >
                            {{ faq.a }}
                        </p>
                    </details>
                </div>
            </div>
        </section>

        <!-- Final call -->
        <section class="bg-background px-4 pb-24 sm:px-6 lg:px-8">
            <div
                class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-brand-green px-6 py-16 text-center text-white sm:px-12 sm:py-20"
            >
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute -top-32 -right-24 size-96 rounded-full bg-white opacity-10 blur-3xl"
                />
                <h2
                    class="relative mx-auto max-w-2xl text-3xl font-bold tracking-tight text-balance sm:text-5xl"
                >
                    Your next order could be on its way to you
                </h2>
                <p class="relative mx-auto mt-5 max-w-xl text-lg text-white/80">
                    Get your free US address with {{ brandName }} in under a
                    minute.
                </p>
                <div class="relative mt-9 flex flex-wrap justify-center gap-3">
                    <Link
                        :href="register()"
                        class="site-btn-accent inline-flex h-12 items-center gap-2 rounded-full px-6 text-base font-semibold"
                    >
                        Create free account
                        <ArrowRight class="size-4" />
                    </Link>
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-12 items-center gap-2 rounded-full border border-white/30 px-6 text-base font-semibold text-white transition hover:bg-white/10"
                    >
                        <MessageCircle class="size-4" />
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
        </section>
    </div>
</template>
