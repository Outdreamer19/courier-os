<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, useSlots } from 'vue';

/**
 * Splits a line of copy into words and un-blurs them one after another.
 *
 * Section headings observe their own intersection so the reveal fires as the
 * heading scrolls up; the hero passes `immediate` because it is above the fold
 * and should never wait on an observer to become legible.
 */
const props = withDefaults(
    defineProps<{
        /** Heading copy. Split on whitespace — each word animates on its own. */
        text: string;
        /** Rendered tag. Keep the real heading level, this is presentational. */
        as?: string;
        /** Animate on mount instead of waiting to scroll into view. */
        immediate?: boolean;
        /** Milliseconds before the first word starts. */
        delay?: number;
        /** Milliseconds between consecutive words. */
        stagger?: number;
    }>(),
    { as: 'h2', immediate: false, delay: 0, stagger: 70 },
);

/** Matches the animation duration in `.blur-reveal.is-revealed`. */
const DURATION = 850;

const slots = useSlots();

const words = computed(() => props.text.split(/\s+/).filter(Boolean));

const el = ref<HTMLElement | null>(null);
const revealed = ref(false);
/**
 * Once the last word has landed the animation state is dropped entirely, so a
 * long page isn't left holding a `filter` and a `will-change` hint on every
 * heading it has already shown.
 */
const settled = ref(false);

let observer: IntersectionObserver | null = null;
let timer: ReturnType<typeof setTimeout> | undefined;

const wordStyle = (index: number) => ({
    '--blur-delay': `${props.delay + index * props.stagger}ms`,
});

/** Slotted trailing content picks up where the split words left off. */
const tailStyle = computed(() => ({
    '--blur-delay': `${props.delay + words.value.length * props.stagger}ms`,
}));

const reveal = () => {
    if (revealed.value) {
        return;
    }

    revealed.value = true;

    const total =
        props.delay +
        (words.value.length + (slots.default ? 1 : 0)) * props.stagger +
        DURATION;

    timer = setTimeout(() => (settled.value = true), total);
};

// Above the fold, or no observer support at all — nothing to wait for.
onMounted(() => {
    if (props.immediate || typeof IntersectionObserver === 'undefined') {
        reveal();

        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    reveal();
                    observer?.disconnect();
                }
            });
        },
        { threshold: 0.25, rootMargin: '0px 0px -6% 0px' },
    );

    if (el.value) {
        observer.observe(el.value);
    }
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
    clearTimeout(timer);
});
</script>

<template>
    <component
        :is="as"
        ref="el"
        class="blur-reveal"
        :class="{ 'is-revealed': revealed, 'is-settled': settled }"
    >
        <!--
            `{{ ' ' }}` rather than literal whitespace between the spans: the
            template compiler condenses whitespace-only nodes that span a line
            break, which would run every word together.
        -->
        <template v-for="(word, index) in words" :key="`${index}-${word}`">
            <span class="blur-reveal-word" :style="wordStyle(index)">{{
                word
            }}</span>
            {{ ' ' }}
        </template>
        <span v-if="$slots.default" class="blur-reveal-word" :style="tailStyle">
            <slot />
        </span>
    </component>
</template>
