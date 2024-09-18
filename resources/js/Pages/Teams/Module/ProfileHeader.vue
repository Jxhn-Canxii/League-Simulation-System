<template>
 <!-- Player Profile and Playoff Performance in One Row -->
 <div class="flex flex-col md:flex-row gap-6">
    <!-- Player Details Section -->
    <div class="player-details mb-6 flex-1" v-if="main_performance.player_details">
        <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fa fa-user text-blue-500 mr-2"></i>
            Player Details
        </h3>
        <p class="text-nowrap text-sm">
            <strong>Name:</strong> {{ main_performance.player_details.player_name ?? "-" }} ,{{ main_performance.player_details.age ?? "N/A" }}
        </p>
        <p>
            <strong>Team:</strong> {{ main_performance.player_details.team_name ?? "-" }}
        </p>
        <p>
            <strong>Role:</strong>
            <span  :class="roleClasses(main_performance.player_details.role)">
                {{ main_performance.player_details.role }}
            </span>
        </p>
        <p>
            <strong>Overall Ratings:</strong> {{ main_performance.player_details.overall_rating ?? "-" }}
        </p>
        <p class="first-letter:uppercase">
            <strong>Archetype:</strong> {{ main_performance.player_details.type ?? "-" }}
        </p>
        <p>
            <strong>Season Exp:</strong>
            <span class="text-xs" :class="playerExpStatusClass(main_performance.season_count)">
                {{ playerExpStatusText(main_performance.season_count ?? main_performance.season_count) }} ({{ main_performance.season_count ?? 0 }})
            </span>
        </p>
        <p>
            <strong>Playoff Exp:</strong>
            <span class="text-xs" :class="playerExpStatusClass(main_performance.playoff_count)">
                {{ playerExpStatusText(main_performance.playoff_count ?? main_performance.playoff_count) }} ({{ main_performance.playoff_count ?? 0 }})
            </span>
        </p>
        <p>
            <strong>Contract:</strong>
            {{ main_performance.player_details.contract_years + " years left" ?? "Unsigned" }}
        </p>
    </div>

    <!-- Playoff Performance Section -->
    <div class="playoff-performance mb-6 flex-1">
        <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fa fa-trophy text-yellow-500 mr-2"></i>
            Playoff Performance
        </h3>
        <div v-if="main_performance.playoff_performance">
            <p>
                <strong>Conf. Quarter Finals:</strong> {{ main_performance.playoff_performance.round_of_16 ?? 0 }}
            </p>
            <p>
                <strong>Conf. Semi Finals:</strong> {{ main_performance.playoff_performance.quarter_finals ?? 0 }}
            </p>
            <p>
                <strong>Conf. Finals:</strong> {{ main_performance.playoff_performance.semi_finals ?? 0 }}
            </p>
            <p>
                <strong>The Big 4:</strong> {{ main_performance.playoff_performance.interconference_semi_finals ?? 0 }}
            </p>
            <p>
                <strong>The Finals:</strong> {{ main_performance.playoff_performance.finals ?? 0 }}
            </p>
            <p>
                <strong>Finals MVP Count:</strong> {{ main_performance.mvp_count ?? 0 }}
            </p>
        </div>
        <div v-else>
            <p>No playoff performance data available.</p>
        </div>
    </div>

    <!-- Awards Section -->
    <div class="awards mb-6 flex-1">
        <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fa fa-star text-gray-500 mr-2"></i>
            Championships
        </h3>
        <div v-if="main_performance.conference_championships?.length > 0">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">
                Conf. Championships
                {{ main_performance.conference_championships?.length > 0 ? "(" + main_performance.conference_championships?.length + ")" : "" }}
            </h4>
            <div v-for="(season, index) in main_performance.conference_championships" :key="index" class="flex items-center mb-2">
                <i class="fa fa-ribbon text-yellow-500 mr-2"></i>
                <p class="text-xs">{{ season.season_name }} ({{ season.championship_team }})</p>
            </div>
        </div>
        <div v-if="main_performance.championships?.length > 0">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">
                Championships
                {{ main_performance.championships?.length > 0 ? "(" + main_performance.championships?.length + ")" : "" }}
            </h4>
            <div v-for="(season, index) in main_performance.championships" :key="index" class="flex items-center mb-2">
                <i class="fa fa-trophy text-yellow-500 mr-2"></i>
                <p class="text-xs">{{ season.season_name }} ({{ season.championship_team }})</p>
            </div>
        </div>
    </div>
    <div class="awards mb-6 flex-1">
        <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fa fa-star text-gray-500 mr-2"></i>
            Awards
        </h3>
        <div v-if="main_performance.mvp_seasons?.length > 0">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">
                MVP Seasons
                {{ main_performance.mvp_seasons?.length > 0 ? "(" + main_performance.mvp_seasons?.length + ")" : "" }}
            </h4>
            <div v-for="(season, index) in main_performance.mvp_seasons" :key="index" class="flex items-center mb-2">
                <i class="fa fa-medal text-yellow-500 mr-2"></i>
                <p class="text-sm">{{ season }}</p>
            </div>
        </div>
        <div v-if="main_performance.awards?.length > 0">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">
                Awards
                {{ main_performance.awards?.length > 0 ? "(" + main_performance.awards?.length + ")" : "" }}
            </h4>
            <div v-for="(season, index) in main_performance.awards" :title="season.team_name" :key="index" class="flex text-nowrap items-center mb-2">
                <i class="fa fa-medal text-yellow-500 mr-2"></i>
                <p class="text-xs">{{ season.award_name }} (Season {{ season.season }})</p>
            </div>
        </div>
    </div>
    <!-- Career Highs Section -->
    <div class="career-highs mb-6 flex-1">
        <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
            <i class="fa fa-chart-line text-purple-500 mr-2"></i>
            Career Highs
        </h3>
        <div v-if="main_performance.career_highs">
            <p>
                <strong>Points:</strong> {{ main_performance.career_highs.career_high_points ?? "N/A" }}
            </p>
            <p>
                <strong>Rebounds:</strong> {{ main_performance.career_highs.career_high_rebounds ?? "N/A" }}
            </p>
            <p>
                <strong>Assists:</strong> {{ main_performance.career_highs.career_high_assists ?? "N/A" }}
            </p>
            <p>
                <strong>Steals:</strong> {{ main_performance.career_highs.career_high_steals ?? "N/A" }}
            </p>
            <p>
                <strong>Blocks:</strong> {{ main_performance.career_highs.career_high_blocks ?? "N/A" }}
            </p>
            <p>
                <strong>Turnovers:</strong> {{ main_performance.career_highs.career_high_turnovers ?? "N/A" }}
            </p>
            <p>
                <strong>Fouls:</strong> {{ main_performance.career_highs.career_high_fouls ?? "N/A" }}
            </p>
        </div>
        <div v-else>
            <p>No career highs data available.</p>
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { roleClasses, playerExpStatusClass,playerExpStatusText } from "@/Utility/Formatter";
const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
    season_logs: {
        type: Number,
        required: true,
    },
    playoff_logs: {
        type: Number,
        required: true,
    },
});
const main_performance = ref([]);
const season_logs = ref(props.season_logs);
const playoff_logs = ref(props.playoff_logs);
const player_id = ref(props.player_id);

// Watch for changes in player_id
// Fetch data on component mount
onMounted(() => {
    fetchPlayerMainPerformance();
});

const fetchPlayerMainPerformance = async () => {
    try {
        const response = await axios.post(route("players.main.performance"), {
            player_id: player_id.value,
        });
        main_performance.value = response.data;
    } catch (error) {
        console.error("Error fetching player playoff performance:", error);
    }
};
</script>

<style scoped>
.table {
    font-size: 0.75rem; /* Smaller text size */
}

.table th,
.table td {
    padding: 0.5rem; /* Smaller padding */
}
</style>
