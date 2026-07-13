<script setup lang="ts">
import ProductScreenshotFrame from './ProductScreenshotFrame.vue';

const stats = [
    { prefix: '$', target: 79, suffix: '/mo', label: 'Starting price, no hidden fees' },
    { prefix: '', target: 24, suffix: '/7', label: 'Automated tracking & WhatsApp updates' },
    { prefix: '', target: 100, suffix: '%', label: 'Tenant data isolated by design' },
    { prefix: '<', target: 1, suffix: ' day', label: 'To launch your branded site' },
];
</script>

<template>
    <section class="bg-marketing-ink py-20 text-marketing-cream fade-in-section">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-marketing-orange">
                    Real-time intelligence &amp; control
                </p>
                <h2 class="mt-2 text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
                    See every package, pre-alert, and payment the moment it happens
                </h2>
            </div>

            <div class="mt-12">
                <ProductScreenshotFrame
                    alt="Customer portal screenshot"
                    label="Customer portal screenshot"
                    aspect="aspect-[16/8]"
                />
            </div>

            <dl class="mt-14 grid grid-cols-2 gap-8 sm:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <dt class="sr-only">{{ stat.label }}</dt>
                    <dd class="text-3xl font-semibold sm:text-4xl">
                        {{ stat.prefix }}{{ stat.target }}{{ stat.suffix }}
                    </dd>
                    <p class="mt-2 text-sm text-marketing-cream/70">{{ stat.label }}</p>
                </div>
            </dl>
        </div>
    </section>
</template>
