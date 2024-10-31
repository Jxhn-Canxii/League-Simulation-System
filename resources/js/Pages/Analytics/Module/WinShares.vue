<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex justify-between">
                <h2 class="text-lg font-semibold text-gray-800">All-Time Win-Loss Record (Stacked Bar Chart)</h2>
            </div>
            <canvas id="allTimeWinLossChart"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const data = ref([]);
const teamsList = ref([]);
let chartInstance = null; // Reference to the chart instance

const sampleData = () => {
    // Sample data for all-time wins and losses for 5 teams
    teamsList.value = ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'];

    data.value = [
        { label: 'Team A', wins: 800, losses: 200 },
        { label: 'Team B', wins: 750, losses: 250 },
        { label: 'Team C', wins: 700, losses: 300 },
        { label: 'Team D', wins: 650, losses: 350 },
        { label: 'Team E', wins: 600, losses: 400 },
    ];
};

const renderChart = async () => {
    // Destroy the existing chart instance if it exists
    if (chartInstance) {
        chartInstance.destroy();
    }

    const datasets = [
        {
            label: 'Wins',
            data: data.value.map(team => team.wins),
            backgroundColor: 'rgba(75, 192, 192, 0.6)', // Example color for wins
            stack: 'combined',
        },
        {
            label: 'Losses',
            data: data.value.map(team => team.losses),
            backgroundColor: 'rgba(255, 99, 132, 0.6)', // Example color for losses
            stack: 'combined',
        },
    ];

    const ctx = document.getElementById('allTimeWinLossChart').getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: teamsList.value,
            datasets: datasets,
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'All-Time Win-Loss Record'
                }
            },
            scales: {
                x: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Teams'
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Games Played'
                    }
                }
            }
        }
    });
};

onMounted(() => {
    sampleData(); // Use sample data for testing
    renderChart(); // Render the chart with the sample data
});
</script>

<style scoped>
/* Add custom styles here */
</style>
