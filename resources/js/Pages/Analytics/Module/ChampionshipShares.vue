<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Championships Won by Team</h2>
            </div>
            <canvas id="championshipChart"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const teamsList = ref([]);
let chartInstance = null; // Reference to the chart instance

// Sample data for championships won by each team
const championshipData = [
    { label: 'Team A', championships: 5 },
    { label: 'Team B', championships: 3 },
    { label: 'Team C', championships: 2 },
    { label: 'Team D', championships: 4 },
    { label: 'Team E', championships: 1 },
];

const renderChart = () => {
    const ctx = document.getElementById('championshipChart').getContext('2d');

    const labels = championshipData.map(team => team.label);
    const data = championshipData.map(team => team.championships);
    const backgroundColors = data.map(() => getRandomColor());

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Championships Won',
                data: data,
                backgroundColor: backgroundColors,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Championships Won by Team',
                },
                legend: {
                    position: 'top',
                },
            },
        },
    });
};

const getRandomColor = () => {
    const r = Math.floor(Math.random() * 256);
    const g = Math.floor(Math.random() * 256);
    const b = Math.floor(Math.random() * 256);
    return `rgba(${r}, ${g}, ${b}, 0.6)`; // Semi-transparent color
};

onMounted(() => {
    renderChart(); // Render the chart with sample data
});
</script>

<style scoped>
/* Add custom styles here */
</style>
