<template>
    <div class="team-roster p-3">
        <h2 class="text-sm font-semibold text-gray-800">
            Player Profile
        </h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Player Profile and Playoff Performance in One Row -->

        <ProfileHeader v-if="playoff_logs" :key="player_id" :player_id="player_id" />


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
                            Role
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
                            <span
                                :class="roleClasses(player.role)"
                                class="inline-flex items-center capitalize px-2.5 py-0.5 rounded text-xs font-medium"
                            >
                                {{ player.role }}
                            </span>
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
                            Role
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
                            <span
                                :class="roleClasses(player.role)"
                                class="inline-flex items-center capitalize px-2.5 py-0.5 rounded text-xs font-medium"
                            >
                                {{ player.role }}
                            </span>
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
                <PlayerGameLogs v-if="player_id" :key="player_id" :season_id="isViewModalOpen" :player_id="player_id"/>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import PlayerGameLogs from "./PlayerGameLogs.vue";
import Modal from "@/Components/Modal.vue";
import ProfileHeader from "./ProfileHeader.vue";
const props = defineProps({
    player_id: {
        type: Number,
        required: true,
    },
});
const isViewModalOpen = ref(false);
const season_logs = ref([]);
const playoff_logs = ref({});
const player_id = ref(props.player_id);
// Watch for changes in player_id
// Fetch data on component mount
onMounted(() => {
    fetchPlayerSeasonPerformance();
    fetchPlayerPlayoffPerformance();
});

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
