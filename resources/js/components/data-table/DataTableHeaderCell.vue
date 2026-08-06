<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        /** Column share, e.g. "20%". Required by the fixed table layout. */
        width?: string;
        align?: 'left' | 'right' | 'center';
        class?: HTMLAttributes['class'];
    }>(),
    { align: 'left', width: undefined, class: '' },
);
</script>

<template>
    <th
        :class="
            cn(
                'px-6 py-3 font-semibold',
                props.align === 'right' && 'text-right',
                props.align === 'center' && 'text-center',
                props.class,
            )
        "
        :style="props.width ? { width: props.width } : undefined"
    >
        <slot />
    </th>
</template>
