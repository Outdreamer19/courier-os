<script setup lang="ts">
import BlurRevealText from './BlurRevealText.vue';
import ProductScreenshotFrame from './ProductScreenshotFrame.vue';

const stats = [
    {
        prefix: '$',
        target: 79,
        suffix: '/mo',
        label: 'Starting price, no hidden fees',
    },
    {
        prefix: '',
        target: 24,
        suffix: '/7',
        label: 'Automated tracking & WhatsApp updates',
    },
    {
        prefix: '',
        target: 100,
        suffix: '%',
        label: 'Tenant data isolated by design',
    },
    {
        prefix: '<',
        target: 1,
        suffix: ' day',
        label: 'To launch your branded site',
    },
];
</script>

<template>
    <section
        class="stats-banner fade-in-section py-20 text-marketing-ink"
    >
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange-deep uppercase"
                >
                    Real-time intelligence &amp; control
                </p>
                <BlurRevealText
                    text="See every package, pre-alert, and payment the moment it happens"
                    :stagger="55"
                    class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
                />
            </div>

            <div class="mt-12">
                <!-- Same asset as the hero's Customer Portal tab. The frame
                     crops to 16/8, so the empty page background below the
                     cards is trimmed off here rather than shown. -->
                <ProductScreenshotFrame
                    src="/images/marketing/customer-portal.png"
                    webp="/images/marketing/customer-portal.webp"
                    alt="CourierOS customer portal showing active package and pre-alert counts, amount due, the assigned Florida shipping address and quick actions"
                    label="Customer portal screenshot"
                    aspect="aspect-[16/8]"
                />
            </div>

            <dl class="mt-14 grid grid-cols-2 gap-8 sm:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="text-center text-marketing-cream"
                >
                    <dt class="sr-only">{{ stat.label }}</dt>
                    <dd class="text-3xl font-semibold sm:text-4xl">
                        {{ stat.prefix }}{{ stat.target }}{{ stat.suffix }}
                    </dd>
                    <p class="mt-2 text-sm text-marketing-cream/75">
                        {{ stat.label }}
                    </p>
                </div>
            </dl>
        </div>
    </section>
</template>

<style scoped>
/*
 * Light three-stop ramp: white → brand orange → deep ink. Replaces the
 * previous near-black start so the band stays bright while still landing
 * on a dark close.
 */
.stats-banner {
    background: linear-gradient(
        145deg,
        #ffffff 0%,
        #ff7a00 48%,
        #210d02 100%
    );
}
</style>
