<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
    <tr
        :class="
            cn(
                'group transition-colors duration-150 hover:bg-muted/60',
                // The accent is a left border on the first cell that only
                // changes colour on hover — always present, so nothing shifts.
                '[&>td:first-child]:border-l-[3px] [&>td:first-child]:border-l-transparent [&>td:first-child]:transition-colors [&>td:first-child]:duration-150',
                'hover:[&>td:first-child]:border-l-brand-ink',
                props.class,
            )
        "
    >
        <slot />
    </tr>
</template>
