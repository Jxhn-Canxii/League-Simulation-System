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
                <option value="top_turnovers_leaders">Top Turnover Leaders</option>
                <option value="top_fouls_leaders">Top Fouls Leaders</option>
            </select>
        </div>

        <!-- Players Table -->
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Exp</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">GP</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Points Per Game">PPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rebounds Per Game">RPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Assist Per Game">APG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Steals Per Game">SPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Blocks Per Game">BPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Turnover Per Game">TOPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Fouls Per Game">FPG</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="player in data.leaders" :key="player.player_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.is_rookie }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.games_played }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.points_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.rebounds_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.assists_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.steals_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.blocks_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.turnovers_per_game }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.fouls_per_game }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const data = ref({});
const selectedLeaderType = ref("mvp_leaders");
const displayedPlayers = ref([]);

onMounted(() => {
    fetchTopPlayers();
});

// Function to fetch top players data
const fetchTopPlayers = async () => {
    try {
        const response = await axios.post(route("players.season.leaders"),{leader_type: selectedLeaderType.value});
        data.value = response.data;
        updateDisplayedPlayers(); // Initialize with default value
    } catch (error) {
        console.error("Error fetching top players:", error);
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
</style>
