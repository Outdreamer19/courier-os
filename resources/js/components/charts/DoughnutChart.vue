<script setup lang="ts">
import {
    ArcElement,
    Chart as ChartJS,
    Legend,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { chartStatusColors } from '@/lib/chartColors';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps<{
    labels: string[];
    data: number[];
    colors?: string[];
}>();

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            backgroundColor:
                props.colors ??
                props.labels.map(
                    (_, index) =>
                        chartStatusColors[index % chartStatusColors.length],
                ),
            borderWidth: 2,
            borderColor: '#ffffff',
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                boxWidth: 12,
                padding: 16,
            },
        },
    },
}));
</script>

<template>
    <div class="h-72">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
