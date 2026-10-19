<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowRight, Check, Globe } from 'lucide-vue-next';

/**
 * Shown when an owner (or anyone) visits a courier site that signed up but
 * has not paid yet. Replaces the old "not currently active" error with a way
 * to finish checkout.
 */
const props = defineProps<{
    tenant: { name: string; host: string; checkout_url: string };
    pricing: {
        monthly: number;
        setup: number;
        currency: string;
        founding_setup?: number | null;
    };
}>();

const money = (value: number) =>
    Number.isInteger(value) ? String(value) : value.toFixed(2);

const setup = props.pricing.founding_setup ?? props.pricing.setup;
</script>

<template>
    <Head title="Finish setting up" />

    <div
        class="courieros-brand flex min-h-screen items-center justify-center bg-marketing-cream px-6 py-12 font-marketing text-marketing-ink"
    >
        <div class="w-full max-w-lg">
            <a href="/" class="mb-10 flex items-center justify-center gap-2.5">
                <span
                    class="flex size-8 items-center justify-center rounded-lg bg-marketing-ink text-sm font-bold text-marketing-amber"
                    >C</span
                >
                <span class="text-lg font-semibold tracking-tight"
                    >CourierOS</span
                >
            </a>

            <div
                class="rounded-2xl border border-marketing-border bg-white p-8 shadow-[0_30px_70px_-40px_rgba(33,13,2,0.45)]"
            >
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    One step left
                </p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight">
                    Finish setting up {{ tenant.name }}
                </h1>
                <p
                    class="mt-3 text-base leading-relaxed text-marketing-ink-muted"
                >
                    Your account is saved and your web address is reserved. The
                    site goes live as soon as payment is complete.
                </p>

                <div
                    class="mt-6 flex items-center gap-3 rounded-xl bg-marketing-eggshell/60 px-4 py-3 text-sm"
                >
                    <Globe
                        class="size-4 shrink-0 text-marketing-orange"
                        aria-hidden="true"
                    />
                    <span class="truncate font-medium">{{ tenant.host }}</span>
                </div>

                <ul class="mt-6 space-y-2.5 text-sm">
                    <li class="flex items-center gap-2.5">
                        <Check
                            class="size-4 text-marketing-orange"
                            aria-hidden="true"
                        />
                        US${{ money(pricing.monthly) }} a month, cancel anytime
                    </li>
                    <li class="flex items-center gap-2.5">
                        <Check
                            class="size-4 text-marketing-orange"
                            aria-hidden="true"
                        />
                        <span>
                            <template v-if="pricing.founding_setup">
                                <span
                                    class="text-marketing-ink-muted line-through"
                                    >US${{ money(pricing.setup) }}</span
                                >{{ ' ' }}
                            </template>
                            US${{ money(setup) }} one-time setup<template
                                v-if="pricing.founding_setup"
                            >
                                (founding price)</template
                            >
                        </span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <Check
                            class="size-4 text-marketing-orange"
                            aria-hidden="true"
                        />
                        Paid securely by card through Stripe
                    </li>
                </ul>

                <a
                    :href="tenant.checkout_url"
                    class="mt-8 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-marketing-ink px-6 text-sm font-semibold text-marketing-cream shadow-lg shadow-marketing-ink/15 transition hover:-translate-y-0.5 hover:bg-marketing-ink/90"
                >
                    Complete payment
                    <ArrowRight class="size-4" aria-hidden="true" />
                </a>
            </div>

            <p class="mt-6 text-center text-sm text-marketing-ink-muted">
                Questions first?
                <a
                    href="mailto:hello@courieros.co"
                    class="font-medium text-marketing-ink underline underline-offset-4"
                    >hello@courieros.co</a
                >
            </p>
        </div>
    </div>
</template>
