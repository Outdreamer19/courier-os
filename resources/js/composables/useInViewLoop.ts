import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Drives the looping product animations on the marketing pages.
 *
 * Each mock advances a `step` counter on a timer, but only while it is on
 * screen: nine looping mocks ticking away below the fold would burn battery
 * on the cheap Android phones a lot of Instagram traffic arrives on. Visitors
 * who ask for reduced motion get the finished state and no timer at all.
 */
export function useInViewLoop(
    el: Ref<HTMLElement | null>,
    options: { steps: number; interval: number; hold?: number },
) {
    const step = ref(0);
    const inView = ref(false);
    /** Latches true the first time the mock is seen, for one-off entrances. */
    const seen = ref(false);
    const reduced = ref(false);

    let timer: ReturnType<typeof setTimeout> | undefined;
    let observer: IntersectionObserver | null = null;

    const tick = () => {
        const isLast = step.value >= options.steps - 1;
        const wait = isLast
            ? (options.hold ?? options.interval * 2)
            : options.interval;

        timer = setTimeout(() => {
            step.value = isLast ? 0 : step.value + 1;
            tick();
        }, wait);
    };

    const start = () => {
        if (timer || reduced.value) {
            return;
        }

        tick();
    };

    const stop = () => {
        clearTimeout(timer);
        timer = undefined;
    };

    onMounted(() => {
        reduced.value = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (reduced.value) {
            step.value = options.steps - 1;
            inView.value = true;
            seen.value = true;

            return;
        }

        if (typeof IntersectionObserver === 'undefined' || !el.value) {
            inView.value = true;
            seen.value = true;
            start();

            return;
        }

        observer = new IntersectionObserver(
            ([entry]) => {
                inView.value = entry.isIntersecting;
                seen.value ||= entry.isIntersecting;

                if (entry.isIntersecting) {
                    start();
                } else {
                    stop();
                }
            },
            { threshold: 0.2 },
        );
        observer.observe(el.value);
    });

    onBeforeUnmount(() => {
        stop();
        observer?.disconnect();
    });

    return { step, inView, seen, reduced };
}
