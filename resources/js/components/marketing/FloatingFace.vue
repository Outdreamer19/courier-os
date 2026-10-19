<script setup lang="ts">
import type { Component } from 'vue';

/**
 * A small portrait with a status chip, floated beside a product mock so the
 * hero shows a person, not only software. Photos are licensed stock models
 * (public/images/people/CREDITS.md); the chip describes what the product
 * does for them and never names them.
 */
defineProps<{
    photo: string;
    alt: string;
    label: string;
    sublabel?: string;
    icon: Component;
    /** Seconds to offset the float so two faces don't bob in sync. */
    delay?: number;
}>();
</script>

<template>
    <figure
        class="face-float pointer-events-none w-40 select-none"
        :style="{ animationDelay: `${delay ?? 0}s` }"
    >
        <picture>
            <source
                :srcset="`/images/people/${photo}.webp`"
                type="image/webp"
            />
            <img
                :src="`/images/people/${photo}.jpg`"
                :alt="alt"
                loading="lazy"
                class="aspect-[4/5] w-full rounded-3xl object-cover object-[50%_25%] shadow-[0_30px_60px_-25px_rgba(33,13,2,0.55)] ring-4 ring-white"
            />
        </picture>
        <figcaption
            class="relative z-10 -mt-7 mr-[-2.5rem] ml-4 flex items-center gap-2.5 rounded-2xl bg-white p-2.5 pr-3.5 shadow-xl ring-1 ring-marketing-border"
        >
            <span
                class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-marketing-amber text-marketing-ink"
            >
                <component :is="icon" class="size-4" />
            </span>
            <span class="min-w-0 text-left">
                <span
                    class="block text-[12px] leading-tight font-semibold text-marketing-ink"
                    >{{ label }}</span
                >
                <span
                    v-if="sublabel"
                    class="block text-[11px] leading-snug text-marketing-ink-muted"
                    >{{ sublabel }}</span
                >
            </span>
        </figcaption>
    </figure>
</template>

<style scoped>
@keyframes face-float {
    0%,
    100% {
        transform: translateY(0) rotate(-1deg);
    }
    50% {
        transform: translateY(-10px) rotate(1deg);
    }
}

.face-float {
    animation: face-float 6s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .face-float {
        animation: none;
    }
}
</style>
