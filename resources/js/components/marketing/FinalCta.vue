<script setup lang="ts">
import BlurRevealText from './BlurRevealText.vue';
</script>

<template>
    <section
        class="cta-slats fade-in-section relative overflow-hidden py-28 text-white"
    >
        <div
            class="relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8"
        >
            <BlurRevealText
                text="Launch your courier platform today"
                class="text-4xl font-semibold tracking-tight text-balance drop-shadow-[0_1px_12px_rgba(33,13,2,0.18)] sm:text-5xl"
            />
            <p
                class="mx-auto mt-5 max-w-xl text-base text-white/85 drop-shadow-[0_1px_8px_rgba(33,13,2,0.15)]"
            >
                Create your branded courier site in minutes. Payment details are
                handled securely by Stripe — we never see your card.
            </p>
            <a
                href="/signup"
                class="mt-9 inline-flex items-center gap-2 rounded-xl bg-marketing-ink px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-marketing-ink/25 transition hover:-translate-y-0.5 hover:shadow-xl"
            >
                Create Your Account →
            </a>
        </div>
    </section>
</template>

<style scoped>
/*
 * Two stacked backgrounds: a horizontal colour ramp through the marketing
 * palette, and a repeating vertical "slat" overlay that shades each band from
 * light to dark before snapping back — the louvered-blind look from the
 * reference. Layer order matters: the overlay is listed first so it paints on
 * top of the ramp.
 */
.cta-slats {
    --slat-width: 46px;

    background-image:
        repeating-linear-gradient(
            90deg,
            rgba(255, 255, 255, 0.24) 0px,
            rgba(255, 255, 255, 0.04) calc(var(--slat-width) * 0.48),
            rgba(0, 0, 0, 0.02) calc(var(--slat-width) * 0.87),
            rgba(0, 0, 0, 0.1) var(--slat-width)
        ),
        linear-gradient(
            90deg,
            #efe6f7 0%,
            #cdb4e8 12%,
            #b483d8 24%,
            #d1568f 35%,
            #ea4635 47%,
            #ff7a00 64%,
            #ffb01a 78%,
            #fcd519 90%,
            #fff2c4 100%
        );
}

/* Narrower slats on small screens so the ribbing doesn't read as three fat
   stripes across a phone. */
@media (max-width: 640px) {
    .cta-slats {
        --slat-width: 28px;
    }
}
</style>
