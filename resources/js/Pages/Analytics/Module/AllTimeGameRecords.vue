<template>
    <div class="grid md:grid-cols-5 grid-cols-1 gap-6">

        <!-- Score Details Cards -->
        <div
            v-for="(item, index) in scoreData"
            :key="index"
            @click.prevent="isGameResultModalOpen = item.game_id"
            class="flex flex-col items-center border border-gray-300 p-6 bg-white rounded-lg shadow-md relative"
        >
            <div :class="item.scoreColor" class="text-5xl font-bold">
                {{ item.score }}
            </div>
            <div class="text-center mt-3">
                <p class="text-lg font-semibold text-gray-600">{{ item.home_team }} vs {{ item.away_team }}</p>
                <p class="text-sm text-gray-500">Season ID: {{ item.season_id }}</p>
                <p class="text-sm text-gray-500">Game ID: {{ item.game_id }}</p>
            </div>
            <div class="text-center mt-4">
                <p class="text-lg text-nowrap font-bold">{{ item.title }}</p>
            </div>
        </div>

    </div>

    <!-- Modal for Game Results -->
    <Modal :show="isGameResultModalOpen" :maxWidth="'4xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isGameResultModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="mt-4">
            <GameResults :game_id="isGameResultModalOpen" />
        </div>
    </Modal>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported
import Modal from "@/Components/Modal.vue";
import GameResults from "@/Pages/Seasons/Module/GameResults.vue";

const isGameResultModalOpen = ref(false);
const scoreData = ref([]);

const fetchGameRecords = async () => {
    try {
        // Adjust the API call to fetch data based on the SQL query
        const response = await axios.get(route("alltime.game.records"));  // Adjust to correct route
        const records = response.data;

        scoreData.value = [
            {
                score: records.lowest_score_by_team,
                home_team: records.home_team_for_lowest_score,
                away_team: records.away_team_for_lowest_score,
                season_id: records.lowest_score_season_id, // You may need to add this to the response or adjust
                game_id: records.lowest_score_game_id, // You may need to add this to the response or adjust
                title: `Lowest Score(${records.team_with_lowest_score})`,
                scoreColor: "text-red-600",
            },
            {
                score: records.highest_score_by_team,
                home_team: records.home_team_for_highest_score,
                away_team: records.away_team_for_highest_score,
                season_id: records.highest_score_season_id, // You may need to add this to the response or adjust
                game_id: records.highest_score_game_id, // You may need to add this to the response or adjust
                title: `Highest Score(${records.team_with_highest_score})`,
                scoreColor: "text-green-600",
            },
            {
                score: records.highest_combined_score,
                home_team: records.home_team_for_highest_combined_score,
                away_team: records.away_team_for_highest_combined_score,
                season_id: records.highest_combined_score_season_id, // You may need to add this to the response or adjust
                game_id: records.highest_combined_score_game_id, // You may need to add this to the response or adjust
                title: "Highest Combined Score",
                scoreColor: "text-yellow-600",
            },
            {
                score: records.lowest_combined_score,
                home_team: records.home_team_for_lowest_combined_score,
                away_team: records.away_team_for_lowest_combined_score,
                season_id: records.lowest_combined_score_season_id, // You may need to add this to the response or adjust
                game_id: records.lowest_combined_score_game_id, // You may need to add this to the response or adjust
                title: "Lowest Combined Score",
                scoreColor: "text-orange-600",
            },
            {
                score: records.biggest_winning_margin,
                home_team: records.home_team_for_biggest_margin,
                away_team: records.away_team_for_biggest_margin,
                season_id: records.biggest_margin_season_id, // You may need to add this to the response or adjust
                game_id: records.biggest_margin_game_id, // You may need to add this to the response or adjust
                title: "Biggest Winning Margin",
                scoreColor: "text-purple-600",
            },
        ];

    } catch (error) {
        console.error("Error fetching game records:", error);
    }
};

onMounted(() => {
    fetchGameRecords();
});
</script>
