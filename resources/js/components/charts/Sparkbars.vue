<script setup lang="ts">
import { computed } from 'vue';

/**
 * A tiny bar strip for stat cards. Pure SVG, so it renders instantly; each
 * bar grows from the baseline with a short stagger.
 */
const props = defineProps<{
    data: number[];
    /** Tailwind text colour class; bars use currentColor. */
    colorClass?: string;
}>();

const bars = computed(() => {
    const max = Math.max(1, ...props.data);
    const count = props.data.length || 1;
    const gap = 4;
    const width = (100 - gap * (count - 1)) / count;

    return props.data.map((value, index) => {
        const height = Math.max(8, (value / max) * 100);

        return {
            x: index * (width + gap),
            y: 100 - height,
            width,
            height,
            last: index === count - 1,
            delay: `${index * 70}ms`,
        };
    });
});
</script>

<template>
    <svg
        viewBox="0 0 100 100"
        preserveAspectRatio="none"
        :class="colorClass ?? 'text-primary'"
        aria-hidden="true"
    >
        <rect
            v-for="(bar, index) in bars"
            :key="index"
            :x="bar.x"
            :y="bar.y"
            :width="bar.width"
            :height="bar.height"
            rx="2.5"
            fill="currentColor"
            :opacity="bar.last ? 1 : 0.32"
            class="spark-bar"
            :style="{ animationDelay: bar.delay }"
        />
    </svg>
</template>
