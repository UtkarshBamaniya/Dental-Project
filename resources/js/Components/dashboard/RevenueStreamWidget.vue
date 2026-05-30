<script setup>
import { computed } from 'vue';

const chartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
        {
            label: 'Revenue',
            data: [5400, 6200, 5800, 8100, 7600, 9200],
            fill: true,
            tension: 0.4,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.18)',
        },
    ],
}));

const chartOptions = computed(() => ({
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
        },
    },
}));
</script>

<template>
    <div class="card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <div class="text-xl font-semibold">Revenue Stream</div>
                <div class="text-sm text-slate-500">First half of the year</div>
            </div>
            <Button icon="pi pi-download" text rounded aria-label="Download report" />
        </div>

        <div class="h-80">
            <Chart type="line" :data="chartData" :options="chartOptions" class="h-full" />
        </div>
    </div>
</template>
