<script setup lang="ts">
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    chartGridColor,
    chartTickColor,
    chartTooltip,
    prefersReducedMotion,
    resolveCssColor,
    withAlpha,
} from '@/lib/chartColors';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    data: number[];
    /** Explicit colour per bar. Without it the last bar is highlighted. */
    colors?: string[];
    color?: string;
    label?: string;
    heightClass?: string;
}>();

const brand = ref('rgb(29, 78, 216)');
onMounted(() => {
    brand.value = resolveCssColor('--primary');
});

const baseColor = computed(() => props.color ?? brand.value);

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? 'Count',
            data: props.data,
            // The current period (last bar) in full colour, history softened.
            backgroundColor:
                props.colors ??
                props.labels.map((_, index) =>
                    index === props.labels.length - 1
                        ? baseColor.value
                        : withAlpha(baseColor.value, 0.28),
                ),
            hoverBackgroundColor: baseColor.value,
            borderRadius: 10,
            borderSkipped: false,
            maxBarThickness: 34,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: prefersReducedMotion()
        ? (false as const)
        : { duration: 900, easing: 'easeOutBack' as const },
    plugins: {
        legend: { display: false },
        tooltip: chartTooltip,
    },
    scales: {
        y: {
            beginAtZero: true,
            border: { display: false },
            ticks: {
                precision: 0,
                color: chartTickColor,
                font: { size: 11 },
                padding: 8,
            },
            grid: { color: chartGridColor, drawTicks: false },
        },
        x: {
            border: { display: false },
            ticks: { color: chartTickColor, font: { size: 11 } },
            grid: { display: false },
        },
    },
}));
</script>

<template>
    <div :class="heightClass ?? 'h-64'">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
