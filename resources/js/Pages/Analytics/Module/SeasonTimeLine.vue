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

const teamsList = ref([]);
const selectedTeam = ref(null);
let seasonChartInstance = null; // Reference to the season chart instance

// Sample data for all-time wins and losses for 5 teams
const allTimeData = [
    { label: 'Team A', wins: 800, losses: 200 },
    { label: 'Team B', wins: 750, losses: 250 },
    { label: 'Team C', wins: 700, losses: 300 },
    { label: 'Team D', wins: 650, losses: 350 },
    { label: 'Team E', wins: 600, losses: 400 },
];

// Sample data for season progression for each team
const seasonData = {
    'Team A': [30, 35, 40, 42, 50, 55, 60, 70, 75, 80],
    'Team B': [25, 30, 35, 40, 45, 50, 55, 60, 65, 70],
    'Team C': [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
    'Team D': [15, 20, 25, 30, 35, 40, 45, 50, 55, 60],
    'Team E': [10, 15, 20, 25, 30, 35, 40, 45, 50, 55],
};

const sampleData = () => {
    teamsList.value = allTimeData;
    selectedTeam.value = teamsList.value[0].label; // Set default selected team
};

const renderSeasonProgressionChart = () => {
    const ctx = document.getElementById('seasonProgressionChart').getContext('2d');

    // Prepare data for all teams
    const datasets = teamsList.value.map(team => ({
        label: team.label,
        data: seasonData[team.label],
        borderColor: getRandomColor(),
        fill: false,
        tension: 0.1,
    }));

    // Resetting any existing chart instance
    if (seasonChartInstance) {
        seasonChartInstance.destroy();
    }

    seasonChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array.from({ length: 10 }, (_, i) => `Season ${i + 1}`),
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

const getRandomColor = () => {
    const r = Math.floor(Math.random() * 256);
    const g = Math.floor(Math.random() * 256);
    const b = Math.floor(Math.random() * 256);
    return `rgba(${r}, ${g}, ${b}, 1)`; // Solid color for lines
};

const updateSeasonChart = () => {
    renderSeasonProgressionChart(); // This will now display all teams
};

onMounted(() => {
    sampleData(); // Use sample data for teams
    renderSeasonProgressionChart(); // Render the season progression chart for all teams
});
</script>

<style scoped>
/* Add custom styles here */
</style>
