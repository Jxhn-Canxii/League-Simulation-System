<template>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-lg font-semibold text-gray-800">

        </h2>

        <Pie
            id="my-chart-id"
            aria-label="Test"
            class="h-20 m-20"
            :options="chartOptions"
            :data="chartData"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';

// Register the required components for Chart.js
ChartJS.register(Title, Tooltip, Legend, ArcElement);

// Define chart data and options using refs
const chartData = ref({
    labels: ['Occupied', 'Vacant-Dirty', 'Vacant-Clean', 'Out of Order', 'Out of Service'],
    datasets: [
        {
            label: '2023',
            backgroundColor: ['#f87979', '#ffcccb', '#ffe4e1', '#ffb6c1', '#fa8072', '#e9967a'],
            data: [40, 20, 12, 0, 12]
        }
    ]
});

const chartOptions = ref({
    responsive: true,
});
</script>
