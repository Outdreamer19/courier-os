<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    Download,
    Globe,
    LoaderCircle,
    Lock,
    Mail,
    Receipt,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted } from 'vue';
import PaymentMethodIcons from '@/components/marketing/PaymentMethodIcons.vue';

/**
 * Shown when a courier signed up but has not paid yet: their own site sends
 * every visitor here instead of a bare "not currently active" error. Built to
 * reassure before money changes hands: what they are paying, what happens
 * next, who handles the card, and how to reach a human.
 */
const props = defineProps<{
    tenant: { name: string; host: string; checkout_url: string };
    pricing: {
        monthly: number;
        setup: number;
        currency: string;
        founding_setup?: number | null;
    };
    /** Back from Checkout but Stripe has not confirmed the payment yet. */
    processing?: boolean;
}>();

const money = (value: number) =>
    Number.isInteger(value) ? String(value) : value.toFixed(2);

const setupDue = computed(
    () => props.pricing.founding_setup ?? props.pricing.setup,
);
const dueToday = computed(() => props.pricing.monthly + setupDue.value);

const steps = [
    { label: 'Account created', done: true },
    { label: 'Web address reserved', done: true },
    { label: 'Payment', current: true },
    { label: 'Your site goes live', done: false },
];

const included = [
    'Your branded site and customer portal',
    'Pre-alerts, package tracking and rates',
    'Email and WhatsApp updates to customers',
    'Unlimited packages and customers',
];

// While Stripe confirms, check back a few times on our own so the owner is
// not left wondering whether to pay again.
let timer: ReturnType<typeof setTimeout> | undefined;
onMounted(() => {
    if (props.processing) {
        timer = setTimeout(() => window.location.reload(), 5000);
    }
});
onUnmounted(() => clearTimeout(timer));
</script>

<template>
    <Head title="Finish setting up" />

    <div
        class="courieros-brand relative min-h-screen overflow-hidden bg-marketing-cream font-marketing text-marketing-ink"
    >
        <div
            class="pointer-events-none absolute -top-40 -right-32 size-[520px] rounded-full bg-marketing-orange/10 blur-3xl"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-48 -left-32 size-[520px] rounded-full bg-marketing-amber/15 blur-3xl"
            aria-hidden="true"
        />

        <div class="relative mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-14">
            <header class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-marketing-ink text-sm font-bold text-marketing-amber"
                        >C</span
                    >
                    <span class="text-lg font-semibold tracking-tight"
                        >CourierOS</span
                    >
                </a>
                <span
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-marketing-ink-muted"
                >
                    <Lock class="size-3.5" aria-hidden="true" />
                    Secure checkout
                </span>
            </header>

            <div
                v-if="processing"
                class="mt-8 flex items-start gap-3 rounded-xl border border-marketing-orange/30 bg-white px-4 py-3 text-sm"
                role="status"
            >
                <LoaderCircle
                    class="mt-0.5 size-4 shrink-0 animate-spin text-marketing-orange"
                    aria-hidden="true"
                />
                <p>
                    <strong class="font-semibold"
                        >We are confirming your payment with Stripe.</strong
                    >
                    This page refreshes by itself. If you have already paid,
                    please do not pay again.
                </p>
            </div>

            <div
                class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)] lg:gap-14"
            >
                <!-- Left: where they are and what they get -->
                <section>
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                    >
                        One step left
                    </p>
                    <h1
                        class="mt-3 text-3xl leading-tight font-semibold tracking-tight text-balance sm:text-4xl"
                    >
                        {{ tenant.name }} is ready to go live
                    </h1>
                    <p
                        class="mt-4 max-w-md text-base leading-relaxed text-marketing-ink-muted"
                    >
                        Your account is saved and your web address is reserved.
                        Complete payment and your site switches on straight
                        away.
                    </p>

                    <ol class="mt-8 grid gap-3" aria-label="Setup progress">
                        <li
                            v-for="(step, index) in steps"
                            :key="step.label"
                            class="flex items-center gap-3 text-sm"
                            :aria-current="step.current ? 'step' : undefined"
                        >
                            <span
                                class="flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
                                :class="
                                    step.done
                                        ? 'bg-marketing-ink text-marketing-amber'
                                        : step.current
                                          ? 'bg-marketing-orange text-white ring-4 ring-marketing-orange/20'
                                          : 'border border-marketing-ink/15 bg-white text-marketing-ink-muted'
                                "
                            >
                                <Check
                                    v-if="step.done"
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                                <template v-else>{{ index + 1 }}</template>
                            </span>
                            <span
                                :class="
                                    step.current
                                        ? 'font-semibold'
                                        : step.done
                                          ? ''
                                          : 'text-marketing-ink-muted'
                                "
                                >{{ step.label }}</span
                            >
                        </li>
                    </ol>

                    <div
                        class="mt-8 flex items-center gap-3 rounded-xl border border-marketing-border bg-white px-4 py-3"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-marketing-orange/10 text-marketing-orange"
                        >
                            <Globe class="size-4" aria-hidden="true" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs text-marketing-ink-muted"
                                >Your web address</span
                            >
                            <span class="block truncate font-semibold">{{
                                tenant.host
                            }}</span>
                        </span>
                    </div>

                    <div class="mt-8 hidden lg:block">
                        <p class="text-sm font-semibold">Everything included</p>
                        <ul class="mt-3 grid gap-2.5 text-sm sm:grid-cols-2">
                            <li
                                v-for="item in included"
                                :key="item"
                                class="flex items-start gap-2.5"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                    aria-hidden="true"
                                />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Right: the order and the button -->
                <section aria-labelledby="order-heading">
                    <div
                        class="rounded-2xl border border-marketing-border bg-white p-6 shadow-[0_30px_70px_-40px_rgba(33,13,2,0.45)] sm:p-7"
                    >
                        <h2
                            id="order-heading"
                            class="flex items-center gap-2 text-sm font-semibold"
                        >
                            <Receipt
                                class="size-4 text-marketing-orange"
                                aria-hidden="true"
                            />
                            Order summary
                        </h2>

                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt>
                                    CourierOS Starter
                                    <span
                                        class="block text-xs text-marketing-ink-muted"
                                        >Billed monthly</span
                                    >
                                </dt>
                                <dd class="font-medium tabular-nums">
                                    US${{ money(pricing.monthly) }}
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt>
                                    One-time setup
                                    <span
                                        v-if="pricing.founding_setup"
                                        class="mt-0.5 block w-fit rounded-full bg-marketing-amber/30 px-2 py-0.5 text-[11px] font-semibold"
                                        >Founding price</span
                                    >
                                </dt>
                                <dd class="text-right font-medium tabular-nums">
                                    <span
                                        v-if="pricing.founding_setup"
                                        class="block text-xs font-normal text-marketing-ink-muted line-through"
                                        >US${{ money(pricing.setup) }}</span
                                    >
                                    US${{ money(setupDue) }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            class="mt-5 flex items-end justify-between border-t border-marketing-border pt-5"
                        >
                            <div>
                                <p class="text-sm font-semibold">Due today</p>
                                <p class="text-xs text-marketing-ink-muted">
                                    Then US${{ money(pricing.monthly) }} a month
                                </p>
                            </div>
                            <p
                                class="text-3xl font-semibold tracking-tight tabular-nums"
                            >
                                US${{ money(dueToday) }}
                            </p>
                        </div>

                        <a
                            :href="tenant.checkout_url"
                            class="mt-6 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-marketing-ink px-6 text-sm font-semibold text-marketing-cream shadow-lg shadow-marketing-ink/20 transition hover:-translate-y-0.5 hover:bg-marketing-ink/90 focus-visible:ring-4 focus-visible:ring-marketing-orange/40 focus-visible:outline-none"
                        >
                            <Lock class="size-4" aria-hidden="true" />
                            Pay securely
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </a>

                        <div class="mt-5 flex flex-col items-center gap-3">
                            <PaymentMethodIcons />
                            <p
                                class="flex items-center gap-1.5 text-xs text-marketing-ink-muted"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="size-3.5"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"
                                        fill="#635BFF"
                                    />
                                </svg>
                                Payments processed by Stripe
                            </p>
                        </div>

                        <ul
                            class="mt-6 space-y-2.5 border-t border-marketing-border pt-5 text-xs text-marketing-ink-muted"
                        >
                            <li class="flex items-start gap-2">
                                <ShieldCheck
                                    class="mt-px size-3.5 shrink-0 text-marketing-ink"
                                    aria-hidden="true"
                                />
                                Your card details go straight to Stripe. We
                                never see or store them.
                            </li>
                            <li class="flex items-start gap-2">
                                <Check
                                    class="mt-px size-3.5 shrink-0 text-marketing-ink"
                                    aria-hidden="true"
                                />
                                Cancel anytime. No contract, no per-package
                                fees.
                            </li>
                            <li class="flex items-start gap-2">
                                <Download
                                    class="mt-px size-3.5 shrink-0 text-marketing-ink"
                                    aria-hidden="true"
                                />
                                Your data stays yours. Export it whenever you
                                like.
                            </li>
                        </ul>
                    </div>

                    <p
                        class="mt-5 flex items-center justify-center gap-1.5 text-center text-sm text-marketing-ink-muted"
                    >
                        <Mail class="size-4" aria-hidden="true" />
                        Questions first?
                        <a
                            href="mailto:hello@courieros.co"
                            class="font-medium text-marketing-ink underline underline-offset-4"
                            >hello@courieros.co</a
                        >
                    </p>

                    <div class="mt-10 lg:hidden">
                        <p class="text-sm font-semibold">Everything included</p>
                        <ul class="mt-3 grid gap-2.5 text-sm">
                            <li
                                v-for="item in included"
                                :key="item"
                                class="flex items-start gap-2.5"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                                    aria-hidden="true"
                                />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
