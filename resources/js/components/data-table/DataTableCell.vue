<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right' | 'center';
        /** Dial the cell back to secondary text. */
        muted?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    { align: 'left', muted: false, class: '' },
);
</script>

<template>
    <td
        :class="
            cn(
                'px-6 py-3 align-middle',
                props.align === 'right' && 'text-right',
                props.align === 'center' && 'text-center',
                props.muted && 'text-muted-foreground',
                props.class,
            )
        "
    >
        <slot />
    </td>
</template>
