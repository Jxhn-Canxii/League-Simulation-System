<template>
    <div class="team-roster p-3">
        <!-- Tab Navigation -->
        <div class="flex space-x-4">
            <button
                 class="text-sm font-medium text-gray-600 border-b-2 hover:border-blue-600"
                :class="activeTab === 'profile' ? 'border-blue-600 text-blue-600' : 'border-transparent'"
                @click="setActiveTab('profile')"
            >
                <i class="fas fa-user"></i> Player Profile
            </button>
            <button
                class="text-sm font-medium text-gray-600 border-b-2 hover:border-blue-600"
                :class="activeTab === 'stats' ? 'border-blue-600 text-blue-600' : 'border-transparent'"
                @click="setActiveTab('stats')"
            >
                <i class="fas fa-chart-bar"></i> Season Stats
            </button>
            <button
                 class="text-sm font-medium text-gray-600 border-b-2 hover:border-blue-600"
                :class="activeTab === 'transactions' ? 'border-blue-600 text-blue-600' : 'border-transparent'"
                @click="setActiveTab('transactions')"
            >
                <i class="fas fa-exchange-alt"></i> Player Transactions
            </button>
            <button
                class="text-sm font-medium text-gray-600 border-b-2 hover:border-blue-600"
                :class="activeTab === 'injury' ? 'border-blue-600 text-blue-600' : 'border-transparent'"
                @click="setActiveTab('injury')"
            >
                <i class="fas fa-medkit"></i> Injury History
            </button>
        </div>
        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Tab Content -->
        <div v-if="activeTab === 'profile'">
            <ProfileHeader v-if="playoff_logs" :key="player_id" :player_id="player_id" />
        </div>
        <div v-if="activeTab === 'stats'">
            <h2 class="text-sm font-semibold text-gray-800">
                Regular Season Logs {{ season_logs.player_stats?.length  > 0 ? '('+season_logs.player_stats?.length+')' : '' }}
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
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Ratings
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(player, index) in season_logs.player_stats" v-if="season_logs.player_stats?.length > 0" :key="player.player_id" @click.prevent="isGameLogsModalOpen = player.season_id" class="hover:bg-gray-100">
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.season_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_names }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border"><span :class="roleClasses(player.role)" class="inline-flex items-center capitalize px-2.5 py-0.5 rounded text-xs font-medium">{{ player.role }}</span></td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.games_played }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_points_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_rebounds_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_assists_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_steals_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_blocks_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_turnovers_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_fouls_per_game.toFixed(1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border font-bold">{{ player.overall_rating ? player.overall_rating.toFixed(1) : 'Unrated' }}</td>
                        </tr>
                        <tr class="hover:bg-gray-100" v-else>
                            <td class="px-2 py-1 text-red-500 text-center font-semibold" colspan="12">No data available</td>
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
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Ratings
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(player, index) in playoff_logs.player_stats" v-if="playoff_logs.player_stats?.length > 0" :key="player.player_id" @click.prevent="isGameLogsModalOpen = player.season_id" class="hover:bg-gray-100">
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
                            <td class="px-2 py-1 whitespace-nowrap border font-bold">
                                {{ player.overall_rating ? player.overall_rating.toFixed(1) : 'Unrated' }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-100" v-else>
                            <td class="px-2 py-1 text-red-500 text-center font-semibold" colspan="12">No data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="activeTab === 'transactions'">
            <h2 class="text-sm font-semibold text-gray-800 mt-4">
                Player Transactions
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Season
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Player Name
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Transfer Details
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                               Old Team
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                               New Team
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(transaction, index) in transactions.data" v-if="transactions.data?.length > 0" :key="transaction.id" @click.prevent="isViewModalOpen = transaction.season_id" class="hover:bg-gray-100">
                            <td class="px-2 py-1 text-gray-700">Season {{ transaction.season_id }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.name }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.role }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.details }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.from_team_name ?? 'Free Agent' }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.to_team_name ?? 'Free Agent' }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ transaction.status }}</td>
                        </tr>
                        <tr class="hover:bg-gray-100" v-else>
                            <td class="px-2 py-1 text-red-500 text-center font-semibold" colspan="7">No data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="activeTab === 'injury'">
            <h2 class="text-sm font-semibold text-gray-800 mt-4">
                Injury History
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Season
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Player Name
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Team
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Injury Details
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                # Games Missed
                            </th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(injury, index) in injuries.data" v-if="injuries.data?.length > 0" :key="injury.id" @click.prevent="isViewModalOpen = injury.season_id" class="hover:bg-gray-100">
                            <td class="px-2 py-1 text-gray-700">Season {{ injury.season_id }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.player_name }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.role }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.team_when_injured }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.injury_type }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.recovery_games }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ injury.status }}</td>
                        </tr>
                        <tr class="hover:bg-gray-100" v-else>
                            <td class="px-2 py-1 text-red-500 text-center font-semibold" colspan="7">No data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <Modal :show="isGameLogsModalOpen" :maxWidth="'6xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isGameLogsModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="mt-4 p-3 block">
            <PlayerGameLogs
                :key="props.player_id"
                :player_id="props.player_id"
                :season_id="isGameLogsModalOpen"
            />
        </div>
    </Modal>
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
const isGameLogsModalOpen = ref(false);
const activeTab = ref('profile');
const season_logs = ref([]);
const playoff_logs = ref({});
const transactions = ref([]);
const injuries = ref([]);
const player_id = ref(props.player_id);
// Watch for changes in player_id
// Fetch data on component mount
onMounted(() => {
    fetchPlayerSeasonPerformance();
    fetchPlayerPlayoffPerformance();
    fetchPlayerTransactions();
    fetchPlayerInjuryHistory();
});
const setActiveTab = (tab) => {
    activeTab.value = tab;
}
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
const fetchPlayerTransactions = async () => {
    try {
        const response = await axios.post(route("players.season.transactions"), {
            player_id:  player_id.value,
        });
        transactions.value = response.data;
    } catch (error) {
        console.error("Error fetching player season performance:", error);
    }
};
const fetchPlayerInjuryHistory = async () => {
    try {
        const response = await axios.post(route("players.season.injury"), {
            player_id:  player_id.value,
        });
        injuries.value = response.data;
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
