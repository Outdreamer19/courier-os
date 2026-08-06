<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import BlurRevealText from './BlurRevealText.vue';

withDefaults(
    defineProps<{
        eyebrow?: string;
        title: string;
        body?: string;
    }>(),
    { eyebrow: '', body: '' },
);

/**
 * The sticky heading has to stay visible while the cards pin underneath it, so
 * the cards' own sticky offset depends on how tall the heading actually renders
 * (which changes with viewport width once the title wraps). Measuring it beats
 * hardcoding a magic number that silently breaks at some breakpoint.
 */
const headingRef = ref<HTMLElement | null>(null);
const sectionRef = ref<HTMLElement | null>(null);

let observer: ResizeObserver | null = null;

onMounted(() => {
    if (!headingRef.value || !sectionRef.value) {
        return;
    }

    const sync = () => {
        if (!headingRef.value || !sectionRef.value) {
            return;
        }

        // Deliberately overlap the heading's bottom padding. Butting the two
        // up exactly leaves a seam where the eyebrow bleeds through above the
        // card as the section scrolls away; the heading has pb-10 to spare.
        const height = headingRef.value.getBoundingClientRect().height - 24;

        sectionRef.value.style.setProperty('--stack-card-top', `${height}px`);
    };

    sync();

    observer = new ResizeObserver(sync);
    observer.observe(headingRef.value);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <section
        ref="sectionRef"
        class="stack-section fade-in-section bg-marketing-cream pb-20 lg:pb-0"
    >
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div
                ref="headingRef"
                class="stack-heading bg-marketing-cream pt-16 pb-10 text-center lg:pt-20"
            >
                <p
                    v-if="eyebrow"
                    class="text-xs font-semibold tracking-[0.2em] text-marketing-orange uppercase"
                >
                    {{ eyebrow }}
                </p>
                <BlurRevealText
                    :text="title"
                    class="mt-2 text-3xl font-semibold tracking-tight text-balance text-marketing-ink sm:text-4xl"
                />
                <p
                    v-if="body"
                    class="mx-auto mt-3 max-w-lg text-sm text-marketing-ink-muted"
                >
                    {{ body }}
                </p>
            </div>

            <!--
                The bottom padding is load-bearing on large screens: a sticky
                element can only travel within its containing block, so without
                it the final card has nowhere to pin and scrolls up behind the
                sticky heading instead of settling under it.
            -->
            <div class="stack space-y-8 lg:space-y-0 lg:pb-40">
                <slot />
            </div>
        </div>
    </section>
</template>

<style scoped>
.stack-section {
    /* Fallback until the ResizeObserver reports the real heading height. */
    --stack-card-top: 13rem;
    /* Height of the sticky MarketingNav header (h-18). */
    --stack-nav-offset: 4.5rem;
    /* How much of each pinned card peeks out above the one stacked on top. */
    --stack-peek: 1.25rem;
}

@media (min-width: 1024px) {
    .stack-heading {
        position: sticky;
        top: var(--stack-nav-offset);
        z-index: 10;
    }

    /*
     * Cards sit *above* the heading so that the final card — which has no
     * sticky travel left at the end of the section — slides cleanly over it
     * instead of disappearing behind it with its title sliced in half.
     */
    .stack :deep(> *) {
        position: sticky;
        top: calc(var(--stack-nav-offset) + var(--stack-card-top));
        z-index: 20;
    }

    /*
     * Each successive card pins a little lower so the top edge of the cards
     * beneath it stays visible, which is what reads as a "stack" rather than
     * one card simply replacing another.
     */
    .stack :deep(> *:nth-child(2)) {
        top: calc(
            var(--stack-nav-offset) + var(--stack-card-top) + var(--stack-peek)
        );
    }

    .stack :deep(> *:nth-child(3)) {
        top: calc(
            var(--stack-nav-offset) + var(--stack-card-top) +
                (var(--stack-peek) * 2)
        );
    }

    .stack :deep(> *:nth-child(4)) {
        top: calc(
            var(--stack-nav-offset) + var(--stack-card-top) +
                (var(--stack-peek) * 3)
        );
    }

    .stack :deep(> *:nth-child(5)) {
        top: calc(
            var(--stack-nav-offset) + var(--stack-card-top) +
                (var(--stack-peek) * 4)
        );
    }
}
</style>
