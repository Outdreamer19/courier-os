<script setup lang="ts">
import BlurRevealText from './BlurRevealText.vue';

/**
 * Home-page bridge into /product: three real screens, side by side, with the
 * one-line reason each matters. The full guided tour lives on the product
 * page — this is the trailer.
 */
const previews = [
    {
        name: 'admin-pre-alerts',
        title: 'Pre-alert queue',
        body: 'Every incoming parcel declared before it lands, matched automatically on arrival.',
        alt: 'The CourierOS pre-alerts queue listing merchant, customer, tracking number and status',
    },
    {
        name: 'customer-package-detail',
        title: 'Customer tracking',
        body: 'A timestamped status timeline your customers check instead of messaging you.',
        alt: 'A CourierOS package page showing a status timeline from Florida warehouse to pickup',
    },
    {
        name: 'admin-reports',
        title: 'Revenue reporting',
        body: 'Twelve months of revenue, volume and outstanding balance, exportable to CSV.',
        alt: 'The CourierOS reports screen showing revenue by month and package volume',
    },
];
</script>

<template>
    <section id="product" class="fade-in-section scroll-mt-20 py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    The product
                </p>
                <BlurRevealText
                    text="Real screens, not mockups"
                    class="mt-2 text-3xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-4xl"
                />
                <p class="mt-4 text-base text-pretty text-marketing-ink-muted">
                    This is a live CourierOS courier business. Everything below
                    ships on day one.
                </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                <a
                    v-for="(preview, index) in previews"
                    :key="preview.name"
                    href="/product"
                    class="fade-in-item group block overflow-hidden rounded-2xl border border-marketing-border bg-white transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_70px_-32px_rgba(33,13,2,0.5)]"
                    :style="{ '--fade-delay': `${index * 110}ms` }"
                >
                    <div
                        class="aspect-16/10 overflow-hidden border-b border-marketing-border bg-marketing-eggshell/40"
                    >
                        <picture>
                            <source
                                :srcset="`/images/marketing/${preview.name}.webp`"
                                type="image/webp"
                            />
                            <img
                                :src="`/images/marketing/${preview.name}.png`"
                                :alt="preview.alt"
                                width="1440"
                                height="900"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-cover object-left-top transition-transform duration-500 group-hover:scale-[1.03]"
                            />
                        </picture>
                    </div>
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-marketing-ink">
                            {{ preview.title }}
                        </h3>
                        <p
                            class="mt-1.5 text-sm leading-relaxed text-pretty text-marketing-ink-muted"
                        >
                            {{ preview.body }}
                        </p>
                    </div>
                </a>
            </div>

            <div class="mt-10 text-center">
                <a
                    href="/product"
                    class="inline-flex items-center gap-2 rounded-xl bg-marketing-ink px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-marketing-ink/20 transition hover:-translate-y-0.5 hover:shadow-xl"
                >
                    Take the full product tour →
                </a>
            </div>
        </div>
    </section>
</template>
