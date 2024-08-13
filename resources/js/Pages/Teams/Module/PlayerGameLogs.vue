<template>
    <div class="team-roster p-3">
        <!-- Loading State -->
        <!-- Main Content -->
        <div>
            <!-- Divider -->
            <hr class="my-4 border-t border-gray-200" />

            <!-- Player Profile and Playoff Performance in One Row -->
            <ProfileHeader :player_id="player_id" />


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
                            <td class="px-2 py-1 whitespace-nowrap border">{{ roundNameFormatter(isNaN(parseFloat(player.round)) ? player.round : parseFloat(player.round)) }}</td>
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
import ProfileHeader from "./ProfileHeader.vue";
const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
    season_id: {
        type: Number,
        required: true,
    },
});

const game_logs = ref({});
const player_id = ref(props.player_id);
const season_id = ref(props.season_id);
const season_logs = ref(props.season_logs);
const playoff_logs = ref(props.playoff_logs);
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
    await fetchPlayerGameLogs();
    isLoading.value = false;
});
const fetchPlayerGameLogs = async () => {
    try {
        search.value.player_id = player_id.value;
        search.value.season_id = season_id.value;
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
