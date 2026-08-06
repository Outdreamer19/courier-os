<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Compass, HandHelping, Heart, ShieldCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useBrand } from '@/composables/useBrand';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { contact, register } from '@/routes';

const { name: brandName } = useBrand();

const values = [
    {
        icon: HandHelping,
        title: 'Customer-first service',
        body: 'Real humans answer your questions. We treat every package like our own.',
    },
    {
        icon: ShieldCheck,
        title: 'Reliable handling',
        body: 'Tracking, pre-alerts, and clear status updates from the moment your order lands.',
    },
    {
        icon: Compass,
        title: 'Built for your market',
        body: 'Local references, local currency and local addresses — configured for where you ship.',
    },
    {
        icon: Heart,
        title: 'Building together',
        body: 'We listen to feedback and improve every week. Your experience shapes the platform.',
    },
];

const { sectionDelay, itemDelay } = useScrollReveal();
</script>

<template>
    <Head :title="`About ${brandName}`" />

    <section
        class="fade-in-section relative overflow-hidden bg-brand-ink text-brand-cream"
        :style="sectionDelay(0)"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(30,142,62,0.2),transparent_45%)]"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8">
            <p class="public-section-label-on-dark">About {{ brandName }}</p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
            >
                Making online shopping and package forwarding effortless.
            </h1>
            <p class="mt-6 max-w-2xl text-base text-brand-cream/80 sm:text-lg">
                {{ brandName }} exists because shopping from international
                stores shouldn't be the hard part of bringing things home. We
                give you your own overseas shipping address, handle your
                packages with care, and keep you informed every step of the way.
            </p>
        </div>
    </section>

    <section class="fade-in-section bg-background" :style="sectionDelay(1)">
        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_1fr]">
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Our mission
                    </h2>
                    <p class="text-muted-foreground">
                        Make online shopping and package forwarding easier,
                        faster, and more transparent for every customer we
                        serve.
                    </p>
                    <p class="text-muted-foreground">
                        Every customer gets a unique reference number, access to
                        an overseas warehouse address, and a personal portal to
                        track their packages from order to pickup.
                    </p>
                </div>
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        What to expect
                    </h2>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li>
                            • Submit a pre-alert with your invoice/receipt so we
                            know what's coming.
                        </li>
                        <li>
                            • Track status from warehouse arrival to
                            ready-for-pickup.
                        </li>
                        <li>
                            • Receive email and (soon) WhatsApp updates on key
                            milestones.
                        </li>
                        <li>
                            • Pickup-only for now — delivery is on our roadmap.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section
        class="fade-in-section bg-brand-green-muted/40"
        :style="sectionDelay(2)"
    >
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(value, index) in values"
                    :key="value.title"
                    class="fade-in-item rounded-xl border border-border bg-card p-6"
                    :style="itemDelay(index)"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-brand-green/10 text-brand-green"
                    >
                        <component :is="value.icon" class="size-5" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold">
                        {{ value.title }}
                    </h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ value.body }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="fade-in-section bg-background" :style="sectionDelay(3)">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[260px_1fr] lg:items-start">
                <div class="rounded-xl border border-border bg-card p-4">
                    <div
                        class="flex aspect-[4/5] items-center justify-center rounded-lg border border-dashed border-border bg-muted/40 text-center text-xs font-medium tracking-[0.16em] text-muted-foreground uppercase"
                    >
                        Founder image
                        <br />
                        placeholder
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="public-section-label">Founder message</p>
                    <h2
                        class="text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        "We're building {{ brandName }} to make shipping simple,
                        clear, and dependable for every customer we serve."
                    </h2>
                    <p class="text-muted-foreground">
                        From day one, our focus is trust: clear rates, better
                        communication, and a smoother way to move your online
                        orders from checkout to pickup.
                    </p>
                    <p class="text-muted-foreground">
                        Thank you for trusting {{ brandName }} with your
                        packages. We are committed to improving the experience
                        every step of the way.
                    </p>
                    <p class="text-sm font-medium text-foreground">
                        — Founder, {{ brandName }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="fade-in-section bg-background" :style="sectionDelay(4)">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2
                class="text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
            >
                Ready to get started?
            </h2>
            <p class="mt-3 text-muted-foreground">
                Create your free account or reach out — we're happy to help.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <Button as-child class="public-cta">
                    <Link :href="register()">Create account</Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="contact()">Contact us</Link>
                </Button>
            </div>
        </div>
    </section>
</template>
