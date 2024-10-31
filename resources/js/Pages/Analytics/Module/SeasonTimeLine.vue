<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-800">Team Season Progression</h2>
            <canvas id="seasonProgressionChart"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios'; // Ensure axios is imported

let seasonChartInstance = null; // Reference to the season chart instance
const standings = ref([]); // Standings data fetched from API

const showChart = async () => {
    await fetchAllStandings(); // Fetch standings
    await renderSeasonProgressionChart(); // Render the chart
};

const fetchAllStandings = async () => {
    try {
        const response = await axios.get(route("analytics.standings")); // Adjust this route as necessary
        standings.value = response.data; // Store fetched standings data
    } catch (error) {
        console.error("Error fetching standings:", error);
    }
};

const renderSeasonProgressionChart = async () => {
    const ctx = document.getElementById('seasonProgressionChart').getContext('2d');

    // Prepare datasets for each team using the provided data structure
    const datasets = standings.value.datasets;

    // Resetting any existing chart instance
    if (seasonChartInstance) {
        seasonChartInstance.destroy();
    }

    seasonChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: standings.value.labels, // Generate labels based on data length
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Season Progression of All Teams',
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Seasons',
                    },
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Wins',
                    },
                },
            },
        },
    });
};

onMounted(() => {
    showChart(); // Call to fetch data and render the chart
});
</script>

<style scoped>
/* Add custom styles here */
</style>
