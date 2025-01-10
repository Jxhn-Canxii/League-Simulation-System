<template>
    <div class="flex overflow-auto shadow">
        <div class="p-6 bg-white rounded-lg shadow-md">
            <canvas :id="'playerRatingsChart'+props.playerRatings.player_id+chart_id"></canvas>
            <p class="text-center">{{ props.playerRatings.overall_rating }} Overall</p>
            <p class="text-center first-letter:uppercase font-medium text-gray-500">
                {{ props.playerRatings.type ?? "-" }}
            </p>
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
const chart_id = Math.random();
let chartInstance = null; // Reference to the chart instance

const renderChart = () => {
  const ctx = document.getElementById('playerRatingsChart'+props.playerRatings.player_id+chart_id).getContext('2d');

  // Ratings data coming from the props
  const ratingsData = [
    props.playerRatings.shooting_rating,
    props.playerRatings.defense_rating,
    props.playerRatings.passing_rating,
    props.playerRatings.rebounding_rating,
  ];

  const labels = ['Shooting', 'Defense', 'Passing', 'Rebounding'];

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
          suggestedMax: 100, // Adjust based on your rating scale
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
