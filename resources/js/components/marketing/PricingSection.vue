<script setup lang="ts">
defineProps<{ pricing: { monthly: number; setup: number; currency: string } }>();

const starterFeatures = [
    'Your own branded subdomain',
    'Pre-alerts & package tracking',
    'Customer self-serve portal',
    'Stripe-powered billing',
    'Email & WhatsApp notifications',
    'Weight-based rate calculator',
];

const enterpriseFeatures = [
    'Everything in Starter',
    'Multiple warehouse locations',
    'Custom domain & integrations',
    'Dedicated onboarding support',
];
</script>

<template>
    <section id="pricing" class="bg-white py-20 fade-in-section">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">Pricing</p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl">
                    Choose the Right Plan
                </h2>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                <div class="rounded-2xl border-2 border-marketing-amber bg-marketing-cream p-8 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
                    <p class="text-sm font-semibold text-marketing-ink-muted">Starter</p>
                    <p class="mt-2 flex items-baseline gap-1 text-marketing-ink">
                        <span class="text-4xl font-semibold">${{ pricing.monthly }}</span>
                        <span class="text-sm text-marketing-ink-muted">/month</span>
                    </p>
                    <p class="mt-1 text-xs text-marketing-ink-muted">
                        + ${{ pricing.setup }} one-time setup · {{ pricing.currency }} · cancel anytime
                    </p>
                    <a
                        href="/signup"
                        class="mt-6 block rounded-lg bg-marketing-amber px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:brightness-95"
                    >
                        Start Free Trial
                    </a>
                    <ul class="mt-6 space-y-3 text-sm text-marketing-ink">
                        <li v-for="f in starterFeatures" :key="f" class="flex items-center gap-2">
                            <span class="text-marketing-orange">✓</span> {{ f }}
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-marketing-border bg-marketing-eggshell/40 p-8">
                    <p class="text-sm font-semibold text-marketing-ink-muted">Enterprise</p>
                    <p class="mt-2 text-4xl font-semibold text-marketing-ink">Custom</p>
                    <p class="mt-1 text-xs text-marketing-ink-muted">For larger, multi-location operations</p>
                    <a
                        href="mailto:hello@courieros.co"
                        class="mt-6 block rounded-lg border border-marketing-ink px-6 py-3 text-center text-sm font-semibold text-marketing-ink transition hover:bg-marketing-ink hover:text-marketing-cream"
                    >
                        Contact Sales
                    </a>
                    <ul class="mt-6 space-y-3 text-sm text-marketing-ink">
                        <li v-for="f in enterpriseFeatures" :key="f" class="flex items-center gap-2">
                            <span class="text-marketing-orange">✓</span> {{ f }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
