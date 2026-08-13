<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import BlurRevealText from './BlurRevealText.vue';

/**
 * Condensed pricing teaser for the home page. The full table, the annual
 * toggle and the plan comparison live on /pricing — this section exists only
 * to answer "what does it cost" without sending anyone off the page.
 */
defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

const included = [
    'Unlimited packages & customers',
    'Branded customer portal',
    'Pre-alerts with auto-matching',
    'Rate tiers & quoting calculator',
    'Email & WhatsApp notifications',
    'Revenue & volume reporting',
];
</script>

<template>
    <section id="pricing" class="fade-in-section scroll-mt-20 bg-white py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    Pricing
                </p>
                <BlurRevealText
                    text="One flat price, however busy you get"
                    class="mt-2 text-3xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-4xl"
                />
                <p class="mt-4 text-base text-pretty text-marketing-ink-muted">
                    No per-package fees and no cut of your revenue. Your
                    customers pay you, through your own Stripe account.
                </p>
            </div>

            <div
                class="mt-12 grid items-center gap-8 rounded-3xl border border-marketing-border bg-marketing-cream p-8 sm:p-10 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1fr)] lg:gap-12"
            >
                <div>
                    <p class="text-sm font-semibold text-marketing-ink-muted">
                        Starter
                    </p>
                    <p class="mt-2 flex items-baseline gap-1">
                        <span class="text-5xl font-semibold tracking-tight"
                            >${{ pricing.monthly }}</span
                        >
                        <span class="text-sm text-marketing-ink-muted"
                            >/month</span
                        >
                    </p>
                    <p class="mt-2 text-xs text-marketing-ink-muted">
                        + ${{ pricing.setup }} one-time setup ·
                        {{ pricing.currency }} · cancel anytime
                    </p>

                    <div class="mt-7 flex flex-col gap-2.5 sm:flex-row">
                        <a
                            href="/signup"
                            class="rounded-xl bg-marketing-amber px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:-translate-y-0.5 hover:brightness-95"
                        >
                            Get Started
                        </a>
                        <a
                            href="/pricing"
                            class="rounded-xl border border-marketing-border bg-white px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:-translate-y-0.5 hover:border-marketing-ink/25"
                        >
                            Compare plans →
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-marketing-ink-muted">
                        Save 20% on yearly billing · Enterprise pricing
                        available for multi-warehouse operations.
                    </p>
                </div>

                <ul class="grid gap-3 text-sm sm:grid-cols-2">
                    <li
                        v-for="item in included"
                        :key="item"
                        class="flex items-start gap-2.5 text-marketing-ink"
                    >
                        <Check
                            class="mt-0.5 size-4 shrink-0 text-marketing-orange"
                            aria-hidden="true"
                        />
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
