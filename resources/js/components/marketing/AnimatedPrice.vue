<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';

/**
 * Tweens between two prices so flipping the billing toggle reads as the number
 * moving rather than swapping. Respects `prefers-reduced-motion` by jumping
 * straight to the target.
 */
const props = withDefaults(
    defineProps<{
        value: number;
        duration?: number;
    }>(),
    { duration: 450 },
);

const displayed = ref(props.value);

let frame: number | undefined;

const prefersReducedMotion = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Ease-out cubic — fast start, soft landing. */
const ease = (t: number) => 1 - Math.pow(1 - t, 3);

watch(
    () => props.value,
    (to, from) => {
        if (frame !== undefined) {
            cancelAnimationFrame(frame);
        }

        if (prefersReducedMotion() || to === from) {
            displayed.value = to;

            return;
        }

        const start = performance.now();
        const delta = to - from;

        const step = (now: number) => {
            const progress = Math.min((now - start) / props.duration, 1);

            displayed.value = from + delta * ease(progress);

            if (progress < 1) {
                frame = requestAnimationFrame(step);
            } else {
                displayed.value = to;
                frame = undefined;
            }
        };

        frame = requestAnimationFrame(step);
    },
);

onBeforeUnmount(() => {
    if (frame !== undefined) {
        cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <!-- `tabular-nums` keeps the digits from jittering mid-tween. -->
    <span class="tabular-nums">{{ Math.round(displayed).toLocaleString() }}</span>
</template>
