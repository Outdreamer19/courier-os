import type { Ref } from 'vue';
import { onMounted, onUnmounted, ref } from 'vue';

interface UseCountUpOptions {
    target: number;
    duration?: number;
    decimals?: number;
}

/**
 * Animates a number from 0 to `target` once the returned `elementRef` enters
 * the viewport. Respects prefers-reduced-motion by jumping straight to the
 * target value.
 */
export function useCountUp({
    target,
    duration = 1400,
    decimals = 0,
}: UseCountUpOptions): {
    elementRef: Ref<HTMLElement | null>;
    value: Ref<number>;
} {
    const elementRef = ref<HTMLElement | null>(null);
    const value = ref(0);
    let observer: IntersectionObserver | null = null;

    const prefersReducedMotion = () =>
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const animate = () => {
        if (prefersReducedMotion()) {
            value.value = target;

            return;
        }

        const start = performance.now();

        const step = (now: number) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            value.value = Number((target * eased).toFixed(decimals));

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    };

    onMounted(() => {
        if (!elementRef.value) {
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animate();
                        observer?.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.4 },
        );

        observer.observe(elementRef.value);
    });

    onUnmounted(() => observer?.disconnect());

    return { elementRef, value };
}
