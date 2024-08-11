<template>
    <div class="team-roster p-3">
        <!-- Loading State -->
        <!-- Main Content -->
        <div>
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
                    <div v-if="main_performance.championships?.length > 0">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">
                            Championships
                            {{ main_performance.championships?.length > 0 ? "(" + main_performance.championships?.length + ")" : "" }}
                        </h4>
                        <div v-for="(season, index) in main_performance.championships" :key="index" class="flex items-center mb-2">
                            <i class="fa fa-trophy text-green-500 mr-2"></i>
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
                Regular Season Logs
                {{
                    game_logs.total > 0
                        ? "(" + game_logs.total + ")"
                        : ""
                }}
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Season</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Opponent</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Round</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Minutes</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Points Made">Points</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rebounds Made">Rebounds</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Assist Made">Assist</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Steals Made">Steals</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Blocks Made">Blocks</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Turnover Made">Turnovers</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Fouls Made">Fouls</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(player, index) in game_logs.game_logs"
                            :key="index"
                            :class="{
                                'bg-green-200': player.game_result === 'Win' && player.game_minutes > 0,
                                'bg-red-200': player.game_result === 'Loss' && player.game_minutes > 0,
                                'bg-slate-200': player.game_minutes == 0,
                            }"
                            class="hover:bg-gray-100"
                        >
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.season_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.opponent_team_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ roundNameFormatter(isNaN(parseFloat(player.round)) ? player.round : parseFloat(player.round) + 1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_minutes == 0 ? 'DNP' : player.game_minutes }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_points.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_rebounds.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_assists.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_steals.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_blocks.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_turnovers.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.game_fouls.toFixed(1) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class="flex w-full overflow-auto">
                    <Paginator
                        v-if="game_logs.total_records"
                        :page_number="search.page_num"
                        :total_rows="game_logs.total_records ?? 0"
                        :itemsperpage="search.itemsperpage"
                        @page_num="handlePagination"
                    />
                </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { roundNameFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";
const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
    season_id: {
        type: Number,
        required: true,
    },
    profile_data: {
        type: Object,
        required: true,
    },
});

const game_logs = ref({});
const main_performance = ref({});
const isLoading = ref(true);
const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    itemsperpage: 10,
    search: "",
});
// Watch for changes in player_id
// Fetch data on component mount
onMounted(async () => {
    isLoading.value = true;
    await fetchPlayerMainPerformance();
    await fetchPlayerGameLogs();
    isLoading.value = false;
});

const fetchPlayerMainPerformance = async () => {
    try {
        main_performance.value = props.profile_data;
    } catch (error) {
        console.error("Error fetching player playoff performance:", error);
    }
};

const fetchPlayerGameLogs = async () => {
    try {
        search.value.player_id = props.player_id;
        search.value.season_id = props.season_id;
        const response = await axios.post(route("players.game.logs"),search.value);
        game_logs.value = response.data;
    } catch (error) {
        console.error("Error fetching player game logs:", error);
    }
};
const handlePagination = (page_num) => {
    search.value.page_num = page_num;
    fetchPlayerGameLogs();
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
