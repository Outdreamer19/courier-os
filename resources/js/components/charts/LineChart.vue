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
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { chartGridColor } from '@/lib/chartColors';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    data: number[];
    color?: string;
    label?: string;
}>();

const lineColor = computed(() => props.color ?? '#06B6D4');

const withAlpha = (hex: string, alpha: number): string => {
    const value = hex.replace('#', '');
    const r = parseInt(value.substring(0, 2), 16);
    const g = parseInt(value.substring(2, 4), 16);
    const b = parseInt(value.substring(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? 'Count',
            data: props.data,
            borderColor: lineColor.value,
            backgroundColor: withAlpha(lineColor.value, 0.16),
            pointBackgroundColor: lineColor.value,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2.5,
            tension: 0.35,
            fill: true,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: Boolean(props.label),
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
            grid: {
                color: chartGridColor,
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
}));
</script>

<template>
    <div class="h-64">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
