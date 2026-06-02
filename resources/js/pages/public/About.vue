<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Compass, HandHelping, Heart, ShieldCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { contact, register } from '@/routes';

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
        title: 'Made for Jamaica',
        body: 'Designed for Jamaican shoppers — references, currency, parishes and all.',
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
    <Head title="About SHIP DJM" />

    <section class="relative overflow-hidden bg-brand-ink text-brand-cream fade-in-section" :style="sectionDelay(0)">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(30,142,62,0.2),transparent_45%)]"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8">
            <p class="public-section-label-on-dark">
                About SHIP DJM
            </p>
            <h1
                class="mt-3 text-balance text-4xl font-semibold tracking-tight sm:text-5xl"
            >
                Making online shopping and package forwarding effortless for Jamaicans.
            </h1>
            <p class="mt-6 max-w-2xl text-base text-brand-cream/80 sm:text-lg">
                SHIP DJM exists because shopping from international stores
                shouldn't be the hard part of bringing things home. We give you
                a Florida shipping address, handle your packages with care,
                and keep you informed every step of the way.
            </p>
        </div>
    </section>

    <section class="bg-background fade-in-section" :style="sectionDelay(1)">
        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_1fr]">
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Our mission
                    </h2>
                    <p class="text-muted-foreground">
                        Make online shopping and package forwarding easier,
                        faster, and more transparent for customers across
                        Jamaica.
                    </p>
                    <p class="text-muted-foreground">
                        Every customer gets a unique reference number,
                        access to a Florida warehouse address, and a personal
                        portal to track their packages from order to pickup.
                    </p>
                </div>
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        What to expect
                    </h2>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li>
                            • Submit a pre-alert with your invoice/receipt so
                            we know what's coming.
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

    <section class="bg-brand-green-muted/40 fade-in-section" :style="sectionDelay(2)">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(value, index) in values"
                    :key="value.title"
                    class="rounded-xl border border-border bg-card p-6 fade-in-item"
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

    <section class="bg-background fade-in-section" :style="sectionDelay(3)">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[260px_1fr] lg:items-start">
                <div class="rounded-xl border border-border bg-card p-4">
                    <div
                        class="flex aspect-[4/5] items-center justify-center rounded-lg border border-dashed border-border bg-muted/40 text-center text-xs font-medium uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Founder image
                        <br />
                        placeholder
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="public-section-label">
                        Founder message
                    </p>
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        "We're building SHIP DJM to make shipping simple, clear,
                        and dependable for every customer in Jamaica."
                    </h2>
                    <p class="text-muted-foreground">
                        From day one, our focus is trust: clear rates, better
                        communication, and a smoother way to move your online
                        orders from checkout to pickup.
                    </p>
                    <p class="text-muted-foreground">
                        Thank you for trusting SHIP DJM with your packages. We
                        are committed to improving the experience every step of
                        the way.
                    </p>
                    <p class="text-sm font-medium text-foreground">
                        — Founder, SHIP DJM
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-background fade-in-section" :style="sectionDelay(4)">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2
                class="text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
            >
                Ready to get started?
            </h2>
            <p class="mt-3 text-muted-foreground">
                Create your free account or reach out — we're happy to help.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <Button
                    as-child
                    class="public-cta"
                >
                    <Link :href="register()">Create account</Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="contact()">Contact us</Link>
                </Button>
            </div>
        </div>
    </section>
</template>
