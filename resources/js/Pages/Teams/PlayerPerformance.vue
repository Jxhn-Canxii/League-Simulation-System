<template>
    <div class="team-roster p-3">
        <h2 class="text-sm font-semibold text-gray-800">
            Player Profile
        </h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Player Details Section -->
        <div class="player-details mb-6" v-if="playoff_performance.player_details">
            <h3 class="text-md font-semibold text-gray-700 mb-2">
                Player Details
            </h3>
            <p><strong>Name:</strong> {{ playoff_performance.player_details.player_name }}</p>
            <p><strong>Team:</strong> {{ playoff_performance.player_details.team_name ?? '-' }}</p>
            <p><strong>Role:</strong> <span :class="roleClasses(playoff_performance.player_details.role)">{{ playoff_performance.player_details.role }}</span></p>
        </div>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Playoff Performance -->
        <div class="playoff-performance mb-6">
            <h3 class="text-md font-semibold text-gray-700 mb-2">
                Playoff Performance
            </h3>
            <div v-if="playoff_performance.playoff_performance">
                <p><strong>Conference Quarter Finals:</strong> {{ playoff_performance.playoff_performance.round_of_16 ?? 0 }}</p>
                <p><strong>Conference Semi Finals:</strong> {{ playoff_performance.playoff_performance.quarter_finals ?? 0  }}</p>
                <p><strong>Conference Finals:</strong> {{ playoff_performance.playoff_performance.semi_finals ?? 0  }}</p>
                <p><strong>Big 4:</strong> {{ playoff_performance.playoff_performance.interconference_semi_finals ?? 0  }}</p>
                <p><strong>The Finals:</strong> {{ playoff_performance.playoff_performance.finals ?? 0  }}</p>
                <p><strong>Finals MVP Count:</strong> {{ playoff_performance.playoff_performance.finals_mvp_count ?? 0  }}</p>
            </div>
            <div v-else>
                <p>No playoff performance data available.</p>
            </div>
        </div>

        <!-- Season Performance Table -->
        <h2 class="text-sm font-semibold text-gray-800">
            Player Season Logs
        </h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

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
                    <tr v-for="(player, index) in season_performance.player_stats" :key="player.player_id" class="hover:bg-gray-100">
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
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
});

const season_performance = ref([]);
const playoff_performance = ref({});

// Watch for changes in player_id
watch(
    () => props.player_id,
    async (newPlayerId) => {
        await fetchPlayerPlayoffPerformance(newPlayerId);
        await fetchPlayerSeasonPerformance(newPlayerId);
    }
);

// Fetch data on component mount
onMounted(() => {
    fetchPlayerPlayoffPerformance(props.player_id);
    fetchPlayerSeasonPerformance(props.player_id);
});

const fetchPlayerPlayoffPerformance = async (player_id) => {
    try {
        const response = await axios.post(route("players.playoff.performance"), {
            player_id: player_id,
        });
        playoff_performance.value = response.data;
    } catch (error) {
        console.error("Error fetching player playoff performance:", error);
    }
};

const fetchPlayerSeasonPerformance = async (player_id) => {
    try {
        const response = await axios.post(route("players.season.performance"), {
            player_id: player_id,
        });
        season_performance.value = response.data;
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
