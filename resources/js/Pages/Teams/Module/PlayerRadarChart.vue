<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-1 overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <div class="flex justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Player Ratings</h2>
            </div>
            <canvas id="playerRatingsChart"></canvas>
        </div>
    </div>
</template>
<script setup>
import { onMounted, ref, defineProps } from 'vue';
import Chart from 'chart.js/auto';

// Define props
const props = defineProps({
  playerRatings: {
    type: Object,
    required: true
  }
});

let chartInstance = null; // Reference to the chart instance

const renderChart = () => {
  const ctx = document.getElementById('playerRatingsChart').getContext('2d');

  // Ratings data coming from the props
  const ratingsData = [
    props.playerRatings.shooting_rating,
    props.playerRatings.defense_rating,
    props.playerRatings.passing_rating,
    props.playerRatings.rebounding_rating,
    props.playerRatings.overall_rating
  ];

  const labels = ['Shooting', 'Defense', 'Passing', 'Rebounding', 'Overall'];

  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Player Ratings',
        data: ratingsData,
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        pointBackgroundColor: 'rgba(75, 192, 192, 1)',
      }],
    },
    options: {
      responsive: true,
      scales: {
        r: {
          angleLines: {
            display: true,
          },
          suggestedMin: 0,
          suggestedMax: 10, // Adjust based on your rating scale
        },
      },
      plugins: {
        title: {
          display: true,
          text: 'Player Ratings',
        },
        legend: {
          position: 'top',
        },
      },
    },
  });
};

onMounted(() => {
  renderChart();
});
</script>
