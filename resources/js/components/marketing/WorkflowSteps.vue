<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { WorkflowStep } from '@/types/marketing';

defineProps<{ steps: WorkflowStep[] }>();

const list = ref<HTMLElement | null>(null);
/** Index of the furthest step scrolled into view; drives the spine fill. */
const reached = ref(-1);

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined' || !list.value) {
        // No observer support — show every step rather than an empty spine.
        reached.value = Number.MAX_SAFE_INTEGER;

        return;
    }

    const items = Array.from(
        list.value.querySelectorAll<HTMLElement>('[data-step]'),
    );

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const index = Number(
                    (entry.target as HTMLElement).dataset.step ?? -1,
                );

                // Monotonic: scrolling back up keeps earlier steps lit, so the
                // spine never appears to drain behind the reader.
                reached.value = Math.max(reached.value, index);
                observer?.unobserve(entry.target);
            });
        },
        { threshold: 0.5, rootMargin: '0px 0px -15% 0px' },
    );

    items.forEach((item) => observer?.observe(item));
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <ol ref="list" class="relative mt-12 space-y-10">
        <!-- Spine: a muted rail with a warm fill that grows as steps land. -->
        <span
            aria-hidden="true"
            class="absolute top-2 bottom-2 left-[15px] w-px bg-marketing-border sm:left-[19px]"
        >
            <span
                class="block w-px origin-top bg-marketing-orange transition-transform duration-700 ease-out"
                :style="{
                    height: '100%',
                    transform: `scaleY(${
                        reached < 0
                            ? 0
                            : Math.min((reached + 1) / steps.length, 1)
                    })`,
                }"
            />
        </span>

        <li
            v-for="(step, index) in steps"
            :key="step.title"
            :data-step="index"
            class="relative flex gap-5 pl-0 transition-all duration-700 ease-out sm:gap-6"
            :class="
                index <= reached
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-4 opacity-45'
            "
        >
            <span
                class="relative z-10 grid size-8 shrink-0 place-items-center rounded-full border text-sm font-semibold transition-colors duration-500 sm:size-10"
                :class="
                    index <= reached
                        ? 'border-marketing-orange bg-marketing-orange text-white'
                        : 'border-marketing-border bg-marketing-cream text-marketing-ink-muted'
                "
                aria-hidden="true"
            >
                {{ index + 1 }}
            </span>

            <div class="pt-0.5">
                <p
                    class="text-[0.65rem] font-semibold tracking-[0.16em] text-marketing-ink-muted uppercase"
                >
                    {{ step.actor }}
                </p>
                <h3 class="mt-1.5 text-lg font-semibold sm:text-xl">
                    {{ step.title }}
                </h3>
                <p
                    class="mt-2 max-w-xl text-sm leading-relaxed text-pretty text-marketing-ink-muted"
                >
                    {{ step.body }}
                </p>
            </div>
        </li>
    </ol>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    li,
    span {
        transition: none !important;
    }
}
</style>
