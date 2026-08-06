<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

/**
 * The shared admin/customer table shell.
 *
 * Fixed layout is deliberate: with `auto` the browser hands every spare pixel
 * to whichever column has the longest content, which strands a dead gap in the
 * middle of the row. Declaring column widths on the header cells keeps the
 * spacing even and stops columns from jumping between pages of results.
 */
const props = withDefaults(
    defineProps<{
        /** Below this the shell scrolls horizontally instead of squashing. */
        minWidth?: string;
        class?: HTMLAttributes['class'];
    }>(),
    { minWidth: '760px', class: '' },
);
</script>

<template>
    <div class="overflow-x-auto">
        <table
            :class="
                cn(
                    'w-full table-fixed border-collapse text-left text-sm',
                    props.class,
                )
            "
            :style="{ minWidth: props.minWidth }"
        >
            <slot />
        </table>
    </div>
</template>
