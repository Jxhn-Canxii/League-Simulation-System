<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Championships by Team</h2>
            </div>
            <canvas id="championsChart"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const teamsList = ref([]);
let chartInstance = null; // Reference to the chart instance


const champions = ref([]);
const search_champions = ref({
    page_num: 1,
    total_pages: 0,
    per_page: 80,
    total: 0,
    search: '',
});

const showChart = async () => {
    await fetchChampions();
    await renderChart();
}

const fetchChampions = async (page = 1) => {
    try {
        const response = await axios.post(route("records.champions"),search_champions.value);
        champions.value = response.data.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const renderChart = async () => {
    const ctx = document.getElementById('championsChart').getContext('2d');

    const labels =  champions.value.map(team => team.name);
    const data =  champions.value.map(team => team.championships);
    const backgroundColors = champions.value.map(team => `#${team.primary_color}`);

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
    showChart();
});
</script>

<style scoped>
/* Add custom styles here */
</style>
