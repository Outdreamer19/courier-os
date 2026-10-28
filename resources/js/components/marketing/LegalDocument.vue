<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';

export type LegalSection = {
    heading: string;
    paragraphs?: string[];
    list?: string[];
    after?: string[];
};

defineProps<{
    title: string;
    updated: string;
    intro: string;
    sections: LegalSection[];
}>();

const legalNav = [
    { name: 'Terms of Service', href: '/legal/terms' },
    { name: 'Privacy Policy', href: '/legal/privacy' },
    { name: 'Refunds & Cancellation', href: '/legal/refund' },
];
</script>

<template>
    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <MarketingNav />

        <header class="mx-auto max-w-4xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">
            <p
                class="text-xs font-semibold tracking-[0.18em] text-marketing-ink-muted uppercase"
            >
                Legal
            </p>
            <h1
                class="mt-3 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
            >
                {{ title }}
            </h1>
            <p class="mt-3 text-sm text-marketing-ink-muted">
                Last updated {{ updated }}
            </p>
            <p class="mt-5 max-w-2xl leading-relaxed text-marketing-ink-muted">
                {{ intro }}
            </p>
        </header>

        <div
            class="mx-auto grid max-w-6xl gap-10 px-4 pb-20 sm:px-6 lg:grid-cols-[220px_1fr] lg:px-8"
        >
            <nav class="space-y-1 text-sm lg:sticky lg:top-24 lg:self-start">
                <Link
                    v-for="link in legalNav"
                    :key="link.href"
                    :href="link.href"
                    class="block rounded-md px-3 py-2 text-marketing-ink/80 transition hover:bg-marketing-ink/5 hover:text-marketing-ink"
                >
                    {{ link.name }}
                </Link>
            </nav>

            <article
                class="space-y-9 rounded-2xl border border-marketing-border bg-white/70 p-6 sm:p-10"
            >
                <section
                    v-for="section in sections"
                    :key="section.heading"
                    class="space-y-3"
                >
                    <h2 class="text-lg font-semibold tracking-tight">
                        {{ section.heading }}
                    </h2>
                    <p
                        v-for="(p, i) in section.paragraphs ?? []"
                        :key="'p' + i"
                        class="leading-relaxed text-marketing-ink-muted"
                    >
                        {{ p }}
                    </p>
                    <ul
                        v-if="section.list?.length"
                        class="list-disc space-y-1.5 pl-5 leading-relaxed text-marketing-ink-muted"
                    >
                        <li v-for="(item, i) in section.list" :key="'l' + i">
                            {{ item }}
                        </li>
                    </ul>
                    <p
                        v-for="(p, i) in section.after ?? []"
                        :key="'a' + i"
                        class="leading-relaxed text-marketing-ink-muted"
                    >
                        {{ p }}
                    </p>
                </section>
            </article>
        </div>

        <MarketingFooter />
    </div>
</template>
