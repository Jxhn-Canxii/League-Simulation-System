<template>
    <div class="team-roster p-3">
        <h2 class="text-sm font-semibold text-gray-800">
            Player Profile
        </h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Player Profile and Playoff Performance in One Row -->
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Player Details Section -->
            <div class="player-details mb-6 flex-1" v-if="main_performance.player_details">
                <h3 class="text-md font-semibold text-gray-700 mb-2 flex items-center">
                    <i class="fa fa-user text-blue-500 mr-2"></i>
                    Player Details
                </h3>
                <p>
                    <strong>Name:</strong> {{ main_performance.player_details.player_name ?? "-" }}
                </p>
                <p>
                    <strong>Team:</strong> {{ main_performance.player_details.team_name ?? "-" }}
                </p>
                <p>
                    <strong>Role:</strong>
                    <span :class="roleClasses(main_performance.player_details.role)">
                        {{ main_performance.player_details.role }}
                    </span>
                </p>
                <p>
                    <strong>Experience:</strong>
                    <span :class="playerExpStatusClass(main_performance.player_details.is_rookie)">
                        {{ playerExpStatusText(main_performance.player_details.is_rookie) }}
                    </span>
                </p>
                <p>
                    <strong>Contract Status:</strong>
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
                        <strong>Conference Quarter Finals:</strong> {{ main_performance.playoff_performance.round_of_16 ?? 0 }}
                    </p>
                    <p>
                        <strong>Conference Semi Finals:</strong> {{ main_performance.playoff_performance.quarter_finals ?? 0 }}
                    </p>
                    <p>
                        <strong>Conference Finals:</strong> {{ main_performance.playoff_performance.semi_finals ?? 0 }}
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
                <div v-if="main_performance.conference_championships?.length > 0">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                        Conference Championships
                        {{ main_performance.conference_championships?.length > 0 ? "(" + main_performance.conference_championships?.length + ")" : "" }}
                    </h4>
                    <div v-for="(season, index) in main_performance.conference_championships" :key="index" class="flex items-center mb-2">
                        <i class="fa fa-ribbon text-yellow-500 mr-2"></i>
                        <p class="text-sm">{{ season.season_name }} ({{ season.championship_team }})</p>
                    </div>
                </div>
                <div v-if="main_performance.championships?.length > 0">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                        Championships
                        {{ main_performance.championships?.length > 0 ? "(" + main_performance.championships?.length + ")" : "" }}
                    </h4>
                    <div v-for="(season, index) in main_performance.championships" :key="index" class="flex items-center mb-2">
                        <i class="fa fa-trophy text-yellow-500 mr-2"></i>
                        <p class="text-sm">{{ season.season_name }} ({{ season.championship_team }})</p>
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


        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Season Performance Table -->
        <h2 class="text-sm font-semibold text-gray-800">
            Regular Season Logs {{season_logs.player_stats?.length  > 0 ? '('+season_logs.player_stats?.length+')' : '' }}
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            Season
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            Team
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            GP
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Points Per Game">
                            PPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rebounds Per Game">
                            RPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Assist Per Game">
                            APG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Steals Per Game">
                            SPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Blocks Per Game">
                            BPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Turnover Per Game">
                            TOPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Fouls Per Game">
                            FPG
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(player, index) in season_logs.player_stats" :key="player.player_id" @click.prevent="isViewModalOpen = player.season_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.season_name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.team_name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.games_played }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_points_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_rebounds_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_assists_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_steals_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_blocks_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_turnovers_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_fouls_per_game.toFixed(1) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h2 class="text-sm font-semibold text-gray-800 mt-4">
            Playoffs Logs {{playoff_logs.player_stats?.length  > 0 ? '('+playoff_logs.player_stats?.length+')' : '' }}
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            Season
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            Team
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                            GP
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Points Per Game">
                            PPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rebounds Per Game">
                            RPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Assist Per Game">
                            APG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Steals Per Game">
                            SPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Blocks Per Game">
                            BPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Turnover Per Game">
                            TOPG
                        </th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Fouls Per Game">
                            FPG
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(player, index) in playoff_logs.player_stats" :key="player.player_id" @click.prevent="isViewModalOpen = player.season_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.season_name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.team_name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.games_played }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_points_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_rebounds_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_assists_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_steals_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_blocks_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_turnovers_per_game.toFixed(1) }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.average_fouls_per_game.toFixed(1) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Modal :show="isViewModalOpen" :maxWidth="'6xl'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="isViewModalOpen = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="mt-4">
                <PlayerGameLogs v-if="isViewModalOpen" :season_id="isViewModalOpen" :player_id="player_id" :profile_data="main_performance" />
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import PlayerGameLogs from "./PlayerGameLogs.vue";
import Modal from "@/Components/Modal.vue";
const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
});
const isViewModalOpen = ref(false);
const season_logs = ref([]);
const playoff_logs = ref({});
const main_performance = ref({});
const player_id = ref(props.player_id);
// Watch for changes in player_id
// Fetch data on component mount
onMounted(() => {
    fetchPlayerMainPerformance();
    fetchPlayerSeasonPerformance();
    fetchPlayerPlayoffPerformance();
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
const fetchPlayerPlayoffPerformance = async () => {
    try {
        const response = await axios.post(route("players.playoff.performance"), {
            player_id:  player_id.value,
        });
        playoff_logs.value = response.data;
    } catch (error) {
        console.error("Error fetching player season performance:", error);
    }
};

const fetchPlayerSeasonPerformance = async () => {
    try {
        const response = await axios.post(route("players.season.performance"), {
            player_id:  player_id.value,
        });
        season_logs.value = response.data;
    } catch (error) {
        console.error("Error fetching player season performance:", error);
    }
};

// Helper functions
const roleClasses = (role) => {
    switch (role) {
        case "starter":
            return "bg-blue-100 text-blue-800";
        case "star player":
            return "bg-yellow-100 text-yellow-800";
        case "role player":
            return "bg-green-100 text-green-800";
        case "bench":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-200 text-gray-800"; // Default case
    }
};

const playerStatusClass = (isActive) => {
    return isActive ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800";
};

const playerStatusText = (isActive) => {
    return isActive ? "Active" : "Waived";
};
const playerExpStatusClass = (isRookie) => {
    return !isRookie ? "bg-gray-100 text-gray-800" : "bg-red-100 text-red-800";
};

const playerExpStatusText = (isRookie) => {
    return !isRookie ? "Veteran" : "Rookie";
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
