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
    (99 - props.playerRatings.injury_prone_percentage),
  ];

  const labels = ['Shooting', 'Defense', 'Passing', 'Rebounding','Health'];

  // Define an array of colors for each label
  const labelColors = [
    'rgba(255, 99, 132, 0.2)', // Shooting (red)
    'rgba(54, 162, 235, 0.2)', // Defense (blue)
    'rgba(255, 159, 64, 0.2)', // Passing (orange)
    'rgba(75, 192, 192, 0.2)', // Rebounding (green)
    'rgba(153, 102, 255, 0.2)', // Health (purple)
  ];

  // Border colors for each label (matching the point colors)
  const borderColors = [
    'rgba(255, 99, 132, 1)', // Shooting
    'rgba(54, 162, 235, 1)', // Defense
    'rgba(255, 159, 64, 1)', // Passing
    'rgba(75, 192, 192, 1)', // Rebounding
    'rgba(153, 102, 255, 1)', // Health
  ];

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
        backgroundColor: labelColors, // Use the label colors for points
        borderColor: borderColors,    // Use the border colors for each point
        borderWidth: 1,
        pointBackgroundColor: borderColors, // Point color matches the border
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
