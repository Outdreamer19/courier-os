<script setup lang="ts">
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    chartStatusColors,
    chartTooltip,
    prefersReducedMotion,
} from '@/lib/chartColors';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    data: number[];
    colors?: string[];
    /** Show Chart.js' own legend. Off when the page draws its own. */
    legend?: boolean;
    heightClass?: string;
}>();

const palette = computed(
    () =>
        props.colors ??
        props.labels.map(
            (_, index) => chartStatusColors[index % chartStatusColors.length],
        ),
);

const total = computed(() => props.data.reduce((sum, n) => sum + n, 0));

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            backgroundColor: palette.value,
            hoverOffset: 6,
            borderWidth: 0,
            borderRadius: 6,
            spacing: 3,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '74%',
    animation: prefersReducedMotion()
        ? (false as const)
        : {
              animateRotate: true,
              duration: 1200,
              easing: 'easeOutQuart' as const,
          },
    plugins: {
        legend: {
            display: props.legend ?? false,
            position: 'bottom' as const,
            labels: { boxWidth: 10, usePointStyle: true, padding: 14 },
        },
        tooltip: chartTooltip,
    },
}));

defineExpose({ palette, total });
</script>

<template>
    <div :class="['relative', heightClass ?? 'h-60']">
        <Doughnut :data="chartData" :options="chartOptions" />
        <div
            class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"
        >
            <slot name="center" :total="total" />
        </div>
    </div>
</template>
