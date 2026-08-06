<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
    <thead>
        <tr
            :class="
                cn(
                    'border-b bg-muted/50 text-xs font-semibold tracking-wider text-muted-foreground uppercase',
                    // Mirrors the row accent's 3px gutter so the header labels
                    // stay aligned with the cells beneath them.
                    '[&>th:first-child]:border-l-[3px] [&>th:first-child]:border-l-transparent',
                    props.class,
                )
            "
        >
            <slot />
        </tr>
    </thead>
</template>
