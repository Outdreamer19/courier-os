<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Check, Minus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AnimatedPrice from '@/components/marketing/AnimatedPrice.vue';
import BillingToggle from '@/components/marketing/BillingToggle.vue';
import BlurRevealText from '@/components/marketing/BlurRevealText.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';

const props = defineProps<{
    pricing: {
        monthly: number;
        setup: number;
        currency: string;
        annualMonthly: number;
        annualTotal: number;
        annualSaving: number;
        annualDiscountPercent: number;
    };
}>();

const { itemDelay } = useScrollReveal();

const yearly = ref(false);

const activeMonthly = computed(() =>
    yearly.value ? props.pricing.annualMonthly : props.pricing.monthly,
);

const billingNote = computed(() =>
    yearly.value
        ? `billed yearly at $${props.pricing.annualTotal.toLocaleString()}`
        : 'billed monthly',
);

const starterFeatures = [
    'Your own branded subdomain',
    'Unlimited customers & packages',
    'Pre-alerts with automatic package matching',
    'Customer self-serve portal',
    'Weight-based rate calculator',
    'Email & WhatsApp status notifications',
    'Stripe-powered customer billing',
    'Revenue & volume reporting',
];

const enterpriseFeatures = [
    'Everything in Starter',
    'Multiple warehouse locations',
    'Custom domain & SSL',
    'Dedicated onboarding & data migration',
    'Priority support with named contact',
    'Custom integrations & API access',
];

type Comparison = {
    feature: string;
    starter: string | boolean;
    enterprise: string | boolean;
};

const comparisonGroups: { group: string; rows: Comparison[] }[] = [
    {
        group: 'Warehouse operations',
        rows: [
            { feature: 'Packages tracked', starter: 'Unlimited', enterprise: 'Unlimited' },
            { feature: 'Pre-alert intake & matching', starter: true, enterprise: true },
            { feature: 'Status timeline & history', starter: true, enterprise: true },
            { feature: 'Warehouse locations', starter: '1', enterprise: 'Unlimited' },
            { feature: 'Activity audit log', starter: true, enterprise: true },
        ],
    },
    {
        group: 'Customers & billing',
        rows: [
            { feature: 'Customer self-serve portal', starter: true, enterprise: true },
            { feature: 'Per-customer Florida address', starter: true, enterprise: true },
            { feature: 'Weight-tier rate calculator', starter: true, enterprise: true },
            { feature: 'Online card payments (Stripe)', starter: true, enterprise: true },
            { feature: 'Invoices & receipts', starter: true, enterprise: true },
        ],
    },
    {
        group: 'Brand & team',
        rows: [
            { feature: 'Branded subdomain', starter: true, enterprise: true },
            { feature: 'Custom domain', starter: false, enterprise: true },
            { feature: 'Admin & staff roles', starter: true, enterprise: true },
            { feature: 'Team seats', starter: 'Unlimited', enterprise: 'Unlimited' },
        ],
    },
    {
        group: 'Support',
        rows: [
            { feature: 'Email support', starter: true, enterprise: true },
            { feature: 'Guided onboarding', starter: 'Self-serve', enterprise: 'Done for you' },
            { feature: 'Data migration', starter: false, enterprise: true },
            { feature: 'Named account contact', starter: false, enterprise: true },
        ],
    },
];

const faqs = [
    {
        q: 'What is the setup fee for?',
        a: `The one-time $${props.pricing.setup} covers configuring your branded site, importing your rate tiers and warehouse address, and getting your first customers onboarded. You pay it once, on your first invoice.`,
    },
    {
        q: 'Do you charge per package or per customer?',
        a: 'No. Packages, pre-alerts, customers and staff seats are all unlimited on every plan. Your bill does not move when you have a busy month.',
    },
    {
        q: 'Can I switch between monthly and yearly?',
        a: 'Yes. Switching to yearly applies the discount from your next renewal, and switching back takes effect at the end of the term you have already paid for.',
    },
    {
        q: 'What happens if I cancel?',
        a: 'You keep access until the end of your billing period, and you can export your customers, packages and revenue history to CSV at any time before or after cancelling.',
    },
    {
        q: 'Do my customers pay CourierOS anything?',
        a: 'Never. Your customers pay you, through your own Stripe account. CourierOS only bills you, the courier business.',
    },
    {
        q: 'Is there a free trial?',
        a: 'You can create your account and configure your entire site before entering payment details, so you can see exactly what you are buying first.',
    },
];

const openFaq = ref<number | null>(0);
</script>

<template>
    <Head title="Pricing">
        <meta
            name="description"
            content="One flat price to run your entire courier business on CourierOS. Unlimited packages, customers and staff — no per-parcel fees."
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
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    Pricing
                </p>
                <BlurRevealText
                    as="h1"
                    immediate
                    text="Choose The Perfect Plan"
                    class="mt-3 text-4xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-5xl"
                />
                <p
                    class="mx-auto mt-5 max-w-xl text-base text-pretty text-marketing-ink-muted"
                >
                    One flat price for the whole business. Unlimited packages,
                    unlimited customers, unlimited staff — your bill never moves
                    because you had a good month.
                </p>

                <div class="mt-10">
                    <BillingToggle
                        v-model="yearly"
                        :discount-percent="pricing.annualDiscountPercent"
                    />
                </div>
            </div>
        </section>

        <!-- Plan cards -->
        <section class="pb-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-start gap-6 lg:grid-cols-2">
                    <!-- Starter — the highlighted, most-popular plan -->
                    <div
                        class="fade-in-item relative overflow-hidden rounded-3xl p-px shadow-[0_30px_80px_-32px_rgba(226,83,10,0.55)] transition-transform duration-300 hover:-translate-y-1"
                        :style="itemDelay(0)"
                    >
                        <div class="plan-gradient absolute inset-0" />
                        <div
                            class="relative flex h-full flex-col rounded-[calc(1.5rem-1px)] p-8 text-white sm:p-10"
                        >
                            <span
                                class="absolute top-0 right-0 rounded-bl-2xl bg-marketing-ink px-4 py-2 text-[0.65rem] font-bold tracking-[0.15em] text-marketing-amber uppercase"
                            >
                                Most Popular
                            </span>

                            <p class="text-xl font-semibold">Starter</p>
                            <p class="mt-2 max-w-xs text-sm text-white/85">
                                Everything a single-warehouse courier business
                                needs to run end to end.
                            </p>

                            <p class="mt-8 flex items-baseline gap-1">
                                <span class="text-5xl font-semibold tracking-tight"
                                    >$<AnimatedPrice :value="activeMonthly"
                                /></span>
                                <span class="text-sm font-medium text-white/80"
                                    >/mo</span
                                >
                            </p>
                            <p class="mt-1 text-sm text-white/75">
                                {{ billingNote }}
                            </p>
                            <p class="mt-1 text-xs text-white/70">
                                + ${{ pricing.setup.toLocaleString() }} one-time
                                setup · {{ pricing.currency }} · cancel anytime
                            </p>

                            <Transition name="saving">
                                <p
                                    v-if="yearly"
                                    class="mt-3 inline-flex w-fit rounded-full bg-marketing-ink/85 px-3 py-1 text-xs font-semibold text-marketing-amber"
                                >
                                    You save ${{
                                        pricing.annualSaving.toLocaleString()
                                    }}
                                    a year
                                </p>
                            </Transition>

                            <a
                                href="/signup"
                                class="mt-8 block rounded-xl bg-white px-6 py-3.5 text-center text-sm font-semibold text-marketing-ink shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                Get Started Now
                            </a>

                            <p class="mt-8 text-sm font-semibold">
                                What do you get:
                            </p>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li
                                    v-for="f in starterFeatures"
                                    :key="f"
                                    class="flex items-start gap-2.5"
                                >
                                    <Check
                                        class="mt-0.5 size-4 shrink-0"
                                        aria-hidden="true"
                                    />
                                    <span>{{ f }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Enterprise -->
                    <div
                        class="fade-in-item flex h-full flex-col rounded-3xl border border-marketing-border bg-white p-8 transition-transform duration-300 hover:-translate-y-1 sm:p-10"
                        :style="itemDelay(1)"
                    >
                        <p class="text-xl font-semibold">Enterprise</p>
                        <p
                            class="mt-2 max-w-xs text-sm text-marketing-ink-muted"
                        >
                            For multi-location operations that need a custom
                            domain and hands-on migration.
                        </p>

                        <p class="mt-8 text-5xl font-semibold tracking-tight">
                            Custom
                        </p>
                        <p class="mt-1 text-sm text-marketing-ink-muted">
                            priced on your volume and locations
                        </p>
                        <p class="mt-1 text-xs text-marketing-ink-muted">
                            Annual agreements available · {{ pricing.currency }}
                        </p>

                        <a
                            href="mailto:hello@courieros.co?subject=CourierOS%20Enterprise"
                            class="mt-8 block rounded-xl border border-marketing-ink px-6 py-3.5 text-center text-sm font-semibold text-marketing-ink transition hover:-translate-y-0.5 hover:bg-marketing-ink hover:text-marketing-cream"
                        >
                            Contact Sales
                        </a>

                        <p class="mt-8 text-sm font-semibold">
                            What do you get:
                        </p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li
                                v-for="f in enterpriseFeatures"
                                :key="f"
                                class="flex items-start gap-2.5"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                    aria-hidden="true"
                                />
                                <span>{{ f }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <p
                    class="mt-8 text-center text-sm text-marketing-ink-muted"
                >
                    Your customers never pay CourierOS — they pay you, through
                    your own Stripe account.
                </p>
            </div>
        </section>

        <!-- Comparison table -->
        <section class="fade-in-section py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        Compare
                    </p>
                    <BlurRevealText
                        text="Every feature, side by side"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                </div>

                <div
                    class="mt-10 overflow-hidden rounded-2xl border border-marketing-border bg-white"
                >
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-marketing-border bg-marketing-eggshell/50"
                            >
                                <th
                                    scope="col"
                                    class="px-4 py-4 font-semibold sm:px-6"
                                >
                                    Feature
                                </th>
                                <th
                                    scope="col"
                                    class="w-28 px-2 py-4 text-center font-semibold sm:w-40 sm:px-6"
                                >
                                    Starter
                                </th>
                                <th
                                    scope="col"
                                    class="w-28 px-2 py-4 text-center font-semibold sm:w-40 sm:px-6"
                                >
                                    Enterprise
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            v-for="group in comparisonGroups"
                            :key="group.group"
                        >
                            <tr class="bg-marketing-cream/70">
                                <th
                                    colspan="3"
                                    scope="colgroup"
                                    class="px-4 py-2.5 text-left text-xs font-semibold tracking-[0.14em] text-marketing-ink-muted uppercase sm:px-6"
                                >
                                    {{ group.group }}
                                </th>
                            </tr>
                            <tr
                                v-for="row in group.rows"
                                :key="row.feature"
                                class="border-t border-marketing-border/70 transition-colors hover:bg-marketing-eggshell/30"
                            >
                                <th
                                    scope="row"
                                    class="px-4 py-3.5 font-normal text-pretty sm:px-6"
                                >
                                    {{ row.feature }}
                                </th>
                                <td
                                    v-for="tier in ['starter', 'enterprise'] as const"
                                    :key="tier"
                                    class="px-2 py-3.5 text-center sm:px-6"
                                >
                                    <Check
                                        v-if="row[tier] === true"
                                        class="mx-auto size-4 text-marketing-orange"
                                        :aria-label="`Included in ${tier}`"
                                    />
                                    <Minus
                                        v-else-if="row[tier] === false"
                                        class="mx-auto size-4 text-marketing-ink-muted/50"
                                        :aria-label="`Not included in ${tier}`"
                                    />
                                    <span
                                        v-else
                                        class="text-xs font-medium text-marketing-ink-muted sm:text-sm"
                                    >
                                        {{ row[tier] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Pricing FAQ -->
        <section class="fade-in-section bg-marketing-eggshell/40 py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        Billing questions
                    </p>
                    <BlurRevealText
                        text="Before you sign up"
                        class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                    />
                </div>

                <div class="mt-10 space-y-3">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.q"
                        class="overflow-hidden rounded-xl border border-marketing-border bg-white"
                    >
                        <h3>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left text-base font-semibold"
                                :aria-expanded="openFaq === index"
                                @click="openFaq = openFaq === index ? null : index"
                            >
                                {{ faq.q }}
                                <span
                                    class="grid size-5 shrink-0 place-items-center text-marketing-ink-muted transition-transform duration-300"
                                    :class="{ 'rotate-45': openFaq === index }"
                                    aria-hidden="true"
                                >
                                    +
                                </span>
                            </button>
                        </h3>
                        <div
                            class="grid transition-[grid-template-rows] duration-300 ease-out"
                            :class="
                                openFaq === index
                                    ? 'grid-rows-[1fr]'
                                    : 'grid-rows-[0fr]'
                            "
                        >
                            <div class="overflow-hidden">
                                <p
                                    class="px-6 pb-4 text-sm leading-relaxed text-marketing-ink-muted"
                                >
                                    {{ faq.a }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-8 text-center text-sm text-marketing-ink-muted">
                    Still deciding?
                    <a
                        href="/product"
                        class="font-semibold text-marketing-orange underline-offset-4 hover:underline"
                        >See exactly what you get →</a
                    >
                </p>
            </div>
        </section>

        <FinalCta />

        <MarketingFooter />
    </div>
</template>

<style scoped>
/*
 * The highlighted plan card borrows the warm ramp from the final CTA so the
 * two "loud" moments on the page read as the same brand, rather than two
 * unrelated gradients.
 */
.plan-gradient {
    border-radius: inherit;
    background: linear-gradient(
        150deg,
        #e2530a 0%,
        #ff7a00 38%,
        #d1568f 78%,
        #b483d8 100%
    );
}

.saving-enter-active,
.saving-leave-active {
    transition:
        opacity 250ms ease,
        transform 250ms ease;
}

.saving-enter-from,
.saving-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

@media (prefers-reduced-motion: reduce) {
    .saving-enter-active,
    .saving-leave-active {
        transition: none;
    }
}
</style>
