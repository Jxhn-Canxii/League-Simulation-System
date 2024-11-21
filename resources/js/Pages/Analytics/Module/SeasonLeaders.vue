<template>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800">Season Leader</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Dropdown for selecting leaders -->
        <div class="mb-4">
            <label for="leader-select" class="block text-sm font-medium text-gray-700">Select Leader Type:</label>
            <select
                id="leader-select"
                v-model="selectedLeaderType"
                @change.prevent="fetchTopPlayers()"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:border-blue-300"
            >
                <option value="mvp_leaders">MVP Leaders</option>
                <option value="rookie_leaders">Rookie Leaders</option>
                <option value="top_point_leaders">Top Point Leaders</option>
                <option value="top_rebound_leaders">Top Rebound Leaders</option>
                <option value="top_assist_leaders">Top Assist Leaders</option>
                <option value="top_block_leaders">Top Block Leaders</option>
                <option value="top_steals_leaders">Top Steals Leaders</option>
                <option value="top_turnovers_leaders">Top Turnover Leaders</option>
                <option value="top_fouls_leaders">Top Fouls Leaders</option>
            </select>
        </div>

        <!-- Loading Screen -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <i class="fas fa-spinner fa-spin text-blue-600 text-3xl"></i>
        </div>

        <!-- Players Cards -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
            <div v-for="player in data.leaders" :key="player.player_id" class="bg-white rounded-lg shadow-lg p-4 flex">
                <!-- Profile Icon -->
                <i class="fas fa-user-circle text-blue-600 text-4xl mr-4"></i>
                <div class="flex-1">
                    <h3 class="text-md text-nowrap font-semibold text-gray-800">{{ player.player_name }}</h3>
                    <p class="text-sm text-gray-500">{{ player.draft_id == props.season_id ? 'Rookie' : 'Veteran' }} - {{ player.team_name }}</p>
                    <p class="text-xs text-gray-400">{{ player.draft_status }}</p>
                    <div class="mt-2">
                        <!-- Highlighted Stat -->
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_point_leaders'">
                            {{ player.points_per_game }} <span class="text-sm">PPG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_rebound_leaders'">
                            {{ player.rebounds_per_game }} <span class="text-sm">RPG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_assist_leaders'">
                            {{ player.assists_per_game }} <span class="text-sm">APG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_steals_leaders'">
                            {{ player.steals_per_game }} <span class="text-sm">SPG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_block_leaders'">
                            {{ player.blocks_per_game }} <span class="text-sm">BPG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_turnovers_leaders'">
                            {{ player.turnovers_per_game }} <span class="text-sm">TOPG</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600" v-if="selectedLeaderType === 'top_fouls_leaders'">
                            {{ player.fouls_per_game }} <span class="text-sm">FPG</span>
                        </div>

                        <!-- Other Stats -->
                        <div class="text-lg text-gray-600" v-if="selectedLeaderType !== 'top_point_leaders' && selectedLeaderType !== 'top_rebound_leaders' && selectedLeaderType !== 'top_assist_leaders' && selectedLeaderType !== 'top_steals_leaders' && selectedLeaderType !== 'top_block_leaders' && selectedLeaderType !== 'top_turnovers_leaders' && selectedLeaderType !== 'top_fouls_leaders'">
                            GP: {{ player.games_played }}<br>
                            PPG: {{ player.avg_points_per_game }}<br>
                            RPG: {{ player.avg_rebounds_per_game }}<br>
                            APG: {{ player.avg_assists_per_game }}<br>
                            SPG: {{ player.avg_steals_per_game }}<br>
                            BPG: {{ player.avg_blocks_per_game }}<br>
                            TOPG: {{ player.avg_turnovers_per_game }}<br>
                            FPG: {{ player.avg_fouls_per_game }}
                        </div>
                        <small class="text-gray-400" title="Performance Score">{{ moneyFormatter(player.performance_score) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { moneyFormatter } from "@/Utility/Formatter";

const data = ref({ leaders: [] });
const selectedLeaderType = ref("mvp_leaders");
const loading = ref(false); // Loading state
const props = defineProps({
    season_id: {
        type: [Number,String],
        required: true,
    },
});
onMounted(() => {
    fetchTopPlayers();
});

// Function to fetch top players data
const fetchTopPlayers = async () => {
    loading.value = true; // Set loading to true
    try {
        const response = await axios.post(route("players.season.leaders"), { leader_type: selectedLeaderType.value, season_id: props.season_id });
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching top players:", error);
    } finally {
        loading.value = false; // Set loading to false after data fetching
    }
};
</script>

<style scoped>
.table {
    font-size: 0.75rem;
}

.table th,
.table td {
    padding: 0.5rem;
}

/* Additional styles for card layout */
.flex {
    display: flex;
}
</style>
