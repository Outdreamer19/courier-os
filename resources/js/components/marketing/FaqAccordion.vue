<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import BlurRevealText from './BlurRevealText.vue';

const faqs = [
    { q: 'What is CourierOS?', a: 'CourierOS is a platform for Caribbean courier and freight-forwarding businesses to run pre-alerts, package tracking, billing, and customer communication — all from one branded site.' },
    { q: 'How fast can I launch?', a: 'Sign up, choose a subdomain, and your branded site is live in minutes. You can connect a custom domain later.' },
    { q: 'Do I need technical skills?', a: 'No. Branding, shipping rates, and your warehouse address are all configured from your admin dashboard — no code required.' },
    { q: 'How does billing work?', a: `CourierOS is $79/month plus a one-time $349 setup fee, billed securely through Stripe. Cancel anytime.` },
    { q: 'Can my customers get WhatsApp updates?', a: 'Yes — status notifications can be sent by email and WhatsApp.' },
    { q: 'Is my data separated from other courier businesses?', a: 'Yes. Every courier business on CourierOS has fully isolated data by design.' },
];

const openIndex = ref<number | null>(0);
</script>

<template>
    <section id="faq" class="bg-marketing-eggshell/40 py-20 fade-in-section">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">FAQ</p>
                <BlurRevealText
                    text="Frequently Asked Questions"
                    class="mt-2 text-balance text-3xl font-semibold tracking-tight text-marketing-ink sm:text-4xl"
                />
            </div>

            <div class="mt-10 space-y-3">
                <Collapsible
                    v-for="(faq, index) in faqs"
                    :key="faq.q"
                    :open="openIndex === index"
                    class="rounded-xl border border-marketing-border bg-white"
                    @update:open="(open) => (openIndex = open ? index : null)"
                >
                    <CollapsibleTrigger class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left text-base font-semibold text-marketing-ink">
                        {{ faq.q }}
                        <ChevronDown
                            class="size-4 shrink-0 text-marketing-ink-muted transition-transform"
                            :class="{ 'rotate-180': openIndex === index }"
                        />
                    </CollapsibleTrigger>
                    <CollapsibleContent class="px-6 pb-4 text-sm leading-relaxed text-marketing-ink-muted">
                        {{ faq.a }}
                    </CollapsibleContent>
                </Collapsible>
            </div>
        </div>
    </section>
</template>
