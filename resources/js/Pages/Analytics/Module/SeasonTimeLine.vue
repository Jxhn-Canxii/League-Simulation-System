<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-800">Team Season Progression</h2>

            <!-- Dropdown for filtering by team name -->
            <label for="teamFilter" class="block text-sm font-medium text-gray-700"  v-if="props.teamId == 0">Filter by Team:</label>
            <select id="teamFilter" v-model="selectedTeam" v-if="props.teamId == 0" @change="renderSeasonProgressionChart" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">All Teams</option>
                <option v-for="team in uniqueTeams" :key="team" :value="team">{{ team }}</option>
            </select>

            <canvas id="seasonProgressionChart" class="mt-4"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

const props = defineProps({
    isConference: {
        type: Number,
        default: 0,
    },
    teamId: {
        type: Number,
        default: 0,
    },
});

let seasonChartInstance = null; // Reference to the season chart instance
const standings = ref([]); // Standings data fetched from API
const selectedTeam = ref(''); // Selected team for filtering

// Computed property to get unique team names for the dropdown
const uniqueTeams = computed(() => {
    const teams = standings.value.datasets?.map(dataset => dataset.label) || [];
    return [...new Set(teams)]; // Return unique team names
});

const showChart = async () => {
    await fetchAllStandings(); // Fetch standings
    renderSeasonProgressionChart(); // Render the chart
};

const fetchAllStandings = async () => {
    try {
        const response = await axios.post(route("analytics.standings", { conference_id: props.isConference,team_id: props.teamId }));
        standings.value = response.data; // Store fetched standings data
    } catch (error) {
        console.error("Error fetching standings:", error);
    }
};

const renderSeasonProgressionChart = () => {
    const ctx = document.getElementById('seasonProgressionChart').getContext('2d');

    // Filter datasets based on selected team
    const datasets = selectedTeam.value
        ? standings.value.datasets.filter(dataset => dataset.label === selectedTeam.value)
        : standings.value.datasets;

    // Resetting any existing chart instance
    if (seasonChartInstance) {
        seasonChartInstance.destroy();
    }

    seasonChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: standings.value.labels, // Labels for the X-axis
            datasets: datasets, // Use filtered datasets
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
