<script setup lang="ts">
import { useCountUp } from '@/composables/useCountUp';

const props = defineProps<{
    title: string;
    percent: number;
    percentLabel: string;
    rows: { label: string; percent: number; color: string }[];
}>();

const { elementRef, value } = useCountUp({ target: props.percent, decimals: 0 });
</script>

<template>
    <div ref="elementRef" class="rounded-2xl border border-marketing-border bg-white p-6 shadow-[0_20px_60px_-25px_rgba(33,13,2,0.35)]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-marketing-ink-muted">
            {{ title }}
        </p>
        <p class="mt-2 flex items-baseline gap-1 text-4xl font-semibold text-marketing-ink">
            {{ Math.round(value) }}<span class="text-2xl">%</span>
        </p>
        <p class="text-xs text-marketing-ink-muted">{{ percentLabel }}</p>

        <ul class="mt-5 space-y-3">
            <li v-for="row in rows" :key="row.label" class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2 text-marketing-ink">
                    <span class="size-2 rounded-full" :style="{ backgroundColor: row.color }" />
                    {{ row.label }}
                </span>
                <span class="font-medium text-marketing-ink-muted">{{ row.percent }}%</span>
            </li>
        </ul>
    </div>
</template>
