<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import { terms, privacy, shipping, refund, restricted } from '@/routes/legal';

defineProps<{
    eyebrow: string;
    title: string;
    intro: string;
    sections: { heading: string; body: string }[];
    showPlaceholderNotice?: boolean;
}>();

const legalNav = [
    { name: 'Terms & Conditions', href: terms() },
    { name: 'Privacy Policy', href: privacy() },
    { name: 'Shipping Policy', href: shipping() },
    { name: 'Refund & Claims', href: refund() },
    { name: 'Restricted Items', href: restricted() },
];
</script>

<template>
    <section class="relative overflow-hidden bg-brand-ink text-brand-cream">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(30,142,62,0.18),transparent_45%)]"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="public-section-label-on-dark">
                {{ eyebrow }}
            </p>
            <h1
                class="mt-3 text-balance text-3xl font-semibold tracking-tight sm:text-4xl"
            >
                {{ title }}
            </h1>
            <p class="mt-4 max-w-2xl text-brand-cream/80">{{ intro }}</p>
        </div>
    </section>

    <section class="bg-background">
        <div
            class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[220px_1fr] lg:px-8"
        >
            <nav class="space-y-1 text-sm">
                <p
                    class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                >
                    Legal
                </p>
                <Link
                    v-for="link in legalNav"
                    :key="link.name"
                    :href="link.href"
                    class="block rounded-md px-3 py-2 text-foreground/80 transition hover:bg-secondary hover:text-foreground"
                >
                    {{ link.name }}
                </Link>
            </nav>

            <article class="prose prose-neutral max-w-none space-y-6">
                <div
                    v-if="showPlaceholderNotice ?? true"
                    class="flex items-start gap-3 rounded-lg border border-brand-green/30 bg-brand-green/5 p-4 text-sm"
                >
                    <Info class="mt-0.5 size-4 text-brand-green" />
                    <p class="text-foreground/80">
                        This is placeholder content for MVP. Final wording will
                        be reviewed by the business owner and a legal advisor
                        before launch.
                    </p>
                </div>

                <section
                    v-for="section in sections"
                    :key="section.heading"
                    class="space-y-2"
                >
                    <h2 class="text-xl font-semibold tracking-tight">
                        {{ section.heading }}
                    </h2>
                    <p class="leading-relaxed text-muted-foreground whitespace-pre-line">
                        {{ section.body }}
                    </p>
                </section>
            </article>
        </div>
    </section>
</template>
