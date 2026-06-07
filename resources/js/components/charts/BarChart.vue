<script setup lang="ts">
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { chartBarColors, chartGridColor } from '@/lib/chartColors';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    data: number[];
    colors?: string[];
    label?: string;
}>();

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? 'Count',
            data: props.data,
            backgroundColor:
                props.colors ??
                props.labels.map(
                    (_, index) => chartBarColors[index % chartBarColors.length],
                ),
            borderRadius: 6,
            borderSkipped: false,
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
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
