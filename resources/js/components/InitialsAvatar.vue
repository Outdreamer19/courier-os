<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A compact identity mark for table rows and lists: the person's first and
 * last initial inside a tinted circle. The ring is deliberately a stronger,
 * brighter tone than the fill so the avatar reads as a crisp object rather
 * than a soft blob — the fill carries the hue, the border carries the edge.
 */
const props = withDefaults(
    defineProps<{
        name?: string | null;
        /** Tailwind sizing classes for the circle. */
        class?: string;
    }>(),
    { name: '', class: '' },
);

/**
 * Palette entries pair a low-opacity fill with a saturated border and legible
 * text tone. Colours are picked deterministically from the name so the same
 * customer always keeps the same swatch across pages and reloads.
 */
const PALETTE = [
    'bg-sky-500/15 border-sky-500 text-sky-700 dark:bg-sky-400/15 dark:border-sky-400 dark:text-sky-200',
    'bg-violet-500/15 border-violet-500 text-violet-700 dark:bg-violet-400/15 dark:border-violet-400 dark:text-violet-200',
    'bg-emerald-500/15 border-emerald-500 text-emerald-700 dark:bg-emerald-400/15 dark:border-emerald-400 dark:text-emerald-200',
    'bg-amber-500/15 border-amber-500 text-amber-700 dark:bg-amber-400/15 dark:border-amber-400 dark:text-amber-200',
    'bg-rose-500/15 border-rose-500 text-rose-700 dark:bg-rose-400/15 dark:border-rose-400 dark:text-rose-200',
    'bg-teal-500/15 border-teal-500 text-teal-700 dark:bg-teal-400/15 dark:border-teal-400 dark:text-teal-200',
    'bg-indigo-500/15 border-indigo-500 text-indigo-700 dark:bg-indigo-400/15 dark:border-indigo-400 dark:text-indigo-200',
    'bg-fuchsia-500/15 border-fuchsia-500 text-fuchsia-700 dark:bg-fuchsia-400/15 dark:border-fuchsia-400 dark:text-fuchsia-200',
] as const;

const initials = computed(() => {
    const parts = (props.name ?? '').trim().split(/\s+/).filter(Boolean);

    if (!parts.length) {
        return '?';
    }

    // First + last initial, so "Marcia Anne Brown" reads as MB, not MA.
    const first = parts[0]!.charAt(0);
    const last = parts.length > 1 ? parts[parts.length - 1]!.charAt(0) : '';

    return (first + last).toUpperCase();
});

const paletteClass = computed(() => {
    const source = (props.name ?? '').trim() || '?';
    let hash = 0;

    for (let i = 0; i < source.length; i += 1) {
        hash = (hash * 31 + source.charCodeAt(i)) >>> 0;
    }

    return PALETTE[hash % PALETTE.length];
});
</script>

<template>
    <span
        aria-hidden="true"
        :class="
            cn(
                'inline-flex size-9 shrink-0 items-center justify-center rounded-full border text-xs font-semibold tracking-wide select-none',
                paletteClass,
                props.class,
            )
        "
    >
        {{ initials }}
    </span>
</template>
