<script setup lang="ts">
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    LinearScale,
    LineElement,
    Legend,
    PointElement,
    Tooltip,
} from 'chart.js';
import type { ScriptableContext } from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import { Line } from 'vue-chartjs';
import {
    chartGridColor,
    chartTickColor,
    chartTooltip,
    prefersReducedMotion,
    resolveCssColor,
    withAlpha,
} from '@/lib/chartColors';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
);

const props = defineProps<{
    labels: string[];
    data: number[];
    /** Any CSS colour. Defaults to the courier's brand colour. */
    color?: string;
    label?: string;
    heightClass?: string;
}>();

// Resolved after mount so the tenant theme has been applied to <html>.
const brand = ref('rgb(29, 78, 216)');
onMounted(() => {
    brand.value = resolveCssColor('--primary');
});

const lineColor = computed(() => props.color ?? brand.value);

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? 'Count',
            data: props.data,
            borderColor: lineColor.value,
            backgroundColor: (context: ScriptableContext<'line'>) => {
                const { ctx, chartArea } = context.chart;

                if (!chartArea) {
                    return withAlpha(lineColor.value, 0.12);
                }

                const gradient = ctx.createLinearGradient(
                    0,
                    chartArea.top,
                    0,
                    chartArea.bottom,
                );
                gradient.addColorStop(0, withAlpha(lineColor.value, 0.28));
                gradient.addColorStop(1, withAlpha(lineColor.value, 0));

                return gradient;
            },
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#ffffff',
            pointHoverBorderColor: lineColor.value,
            pointHoverBorderWidth: 3,
            borderWidth: 3,
            tension: 0.42,
            fill: true,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    animation: prefersReducedMotion()
        ? (false as const)
        : { duration: 1100, easing: 'easeOutQuart' as const },
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
            ticks: { color: chartTickColor, font: { size: 11 }, padding: 8 },
            grid: { display: false },
        },
    },
}));
</script>

<template>
    <div :class="heightClass ?? 'h-72'">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
