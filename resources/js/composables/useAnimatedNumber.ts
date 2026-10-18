import { onMounted, ref, watch } from 'vue';
import type { Ref } from 'vue';

/**
 * Counts a number up from zero when the component mounts (and eases to new
 * values after that). Respects prefers-reduced-motion by jumping straight to
 * the value.
 */
export function useAnimatedNumber(
    target: Ref<number> | (() => number),
    duration = 1100,
): Ref<number> {
    const read = () => (typeof target === 'function' ? target() : target.value);
    const display = ref(0);

    const reduced = () =>
        typeof window === 'undefined' ||
        window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    const animate = (from: number, to: number) => {
        if (reduced() || from === to) {
            display.value = to;

            return;
        }

        const start = performance.now();
        const step = (now: number) => {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 4);
            display.value = from + (to - from) * eased;

            if (t < 1) {
                requestAnimationFrame(step);
            } else {
                display.value = to;
            }
        };

        requestAnimationFrame(step);
    };

    onMounted(() => animate(0, read()));
    watch(read, (to, from) => animate(from ?? 0, to));

    return display;
}
