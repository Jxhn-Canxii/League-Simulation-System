<template>
    <div class="team-roster p-4">
        <h2 class="text-xl font-semibold text-gray-800" v-if="team_info.teams">
            {{ team_info.teams.team_name ?? "-" }} ({{ team_info.teams.acronym ?? "-" }})
        </h2>

        <span
            v-if="team_info.teams"
            class="inline-flex items-center px-2.5 py-0.5 bg-green-300 text-green-600 rounded text-xs font-medium"
        >
            {{ team_info.teams.conference_name ?? "-" }}
        </span>
        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <!-- Add Player Button -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <select v-model="season_id" @change="seasonBehavior()" class="mt-1 block w-full sm:w-auto border-gray-300 rounded-md shadow-sm sm:text-sm">
                    <option value="0">Latest</option>
                    <option v-for="(season, ss) in seasons" :key="season.season_id" :value="season.season_id">{{ season.name }}</option>
                </select>
            </div>
            <div>
                <!-- <button
                    @click="showAddPlayerModal = true"
                    class="ml-4 px-4 py-2 bg-green-500 text-white rounded text-sm flex items-center"
                >
                    <i class="fa fa-user mr-2"></i> Add Player
                </button> -->
            </div>
        </div>

        <!-- Players Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Name
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Age
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Role
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Points Per Game"
                        >
                            GP
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Points Per Game"
                        >
                            PPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Rebounds Per Game"
                        >
                            RPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Assist Per Game"
                        >
                            APG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Steals Per Game"
                        >
                            SPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Blocks Per Game"
                        >
                            BPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Turnover Per Game"
                        >
                            TOPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                            title="Fouls Per Game"
                        >
                            FPG
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <!-- <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th> -->
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(player, index) in team_roster.players"
                        :key="player.player_id"
                        v-if="team_roster.players?.length > 0"
                        @click.prevent="showPlayerProfile(player)"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.name }}<sup>{{ player.is_rookie ? 'R':'V'}}</sup>
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.age }}
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
                        <td class="px-2 py-1 whitespace-nowrap border">
                            <!-- Display "Retired" if player is not active and retirement age is greater than or equal to their age -->
                            <span v-if="player.age > player.retirement_age && !player.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Retired</span>

                            <!-- Display "Active" if player is active and retirement age is less than their age -->
                            <span v-if="player.age <= player.retirement_age && player.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>

                            <!-- Display "Waived/Free Agent" if player is waived/free agent and retirement age is less than their age -->
                            <span v-if="player.age <= player.retirement_age && !player.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Waived/Free Agent</span>
                        </td>
                        <!-- <td class="px-2 py-1 whitespace-nowrap border">
                            <button
                                @click="waivePlayer(player.player_id)"
                                class="px-2 py-1 bg-red-500 text-white text-xs rounded-l"
                            >
                                Waive
                            </button>
                            <button
                                @click="extendContract(player.player_id)"
                                class="px-2 py-1 bg-blue-500 text-white rounded-r text-xs"
                            >
                                Extend Contract
                            </button>
                        </td> -->
                    </tr>
                    <tr
                        v-else
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border text-center font-bold text-red-500" colspan="13">***No Players Found***</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal for Adding Player -->
        <Modal :show="showAddPlayerModal" :maxWidth="'sm'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showAddPlayerModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="grid grid-cols-1 gap-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800">Add Player</h2>
                <div>
                    <label
                        for="player_name"
                        class="block text-sm font-medium text-gray-700"
                        >Player Name</label
                    >
                    <input
                        v-model="newPlayerName"
                        id="player_name"
                        type="text"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"
                    />
                </div>
                <button
                    @click="addPlayer()"
                    class="px-4 py-2 bg-green-500 text-white rounded"
                >
                    Add Player
                </button>
            </div>
        </Modal>

        <!-- Modal for Extending Contract -->
        <Modal :show="showExtendModal" :maxWidth="'sm'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showExtendModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="grid grid-cols-1 gap-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800">
                    Extend Contract
                </h2>
                <div><strong>Player:</strong> {{ selectedPlayer.name }}</div>
                <div>
                    <label
                        for="additional_years"
                        class="block text-sm font-medium text-gray-700"
                        >Additional Years</label
                    >
                    <input
                        v-model="additionalYears"
                        id="additional_years"
                        type="number"
                        min="1"
                        max="5"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"
                    />
                </div>
                <button
                    @click="confirmExtendContract"
                    class="px-4 py-2 bg-blue-500 text-white rounded"
                >
                    Extend Contract
                </button>
            </div>
        </Modal>

        <!-- Modal for Player Profile -->
        <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showPlayerProfileModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="p-6 block">
                <!-- Image Section -->
                <PlayerPerformance :key="selectedPlayer.player_id" :player_id="selectedPlayer.player_id" />
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import axios from "axios";
import PlayerPerformance from "./PlayerPerformance.vue";

const props = defineProps({
    team_id: {
        type: Number,
        required: true,
    },
});
const showAddPlayerModal = ref(false);
const showExtendModal = ref(false);
const showPlayerProfileModal = ref(false);
const selectedPlayer = ref(null);
const additionalYears = ref(1);
const newPlayerName = ref("");
const team_roster = ref([]);
const team_info = ref([]);
const seasons = ref([]);
const season_id = ref(0);
watch(
    () => props.team_id,
    async (newId, oldId) => {
        if (newId !== oldId) {
            await fetchTeamInfo(newId);
        }
    }
);

onMounted(() => {
    fetchTeamInfo(props.team_id);
    fetchTeamRoster(props.team_id);
    seasonsDropdown();
});

const fetchTeamInfo = async (id) => {
    try {
        const response = await axios.post(route("teams.info"), {
            team_id: id,
        });
        team_info.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};

const fetchTeamRoster = async (id) => {
    try {
        team_roster.value = [];
        const response = await axios.post(route("players.list"), {
            team_id: id,
            season_id: season_id.value,
        });
        team_roster.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const seasonsDropdown = async () => {
    try {
        const response = await axios.post(route("seasons.dropdown"), {
            season_id: season_id.value,
        });
        seasons.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const seasonBehavior = () => {
    fetchTeamRoster(props.team_id); // Refresh team info
}
const addPlayer = async () => {
    try {
        const response = await axios.post(route("players.add"), {
            name: newPlayerName.value,
            team_id: props.team_id,
        });
        newPlayerName.value = ""; // Clear the input
        // showAddPlayerModal.value = false;
        // Swal.fire({
        //     icon: "success",
        //     title: "Success!",
        //     text: response.data.message, // Assuming the response contains a 'message' field
        // });
        fetchTeamRoster(props.team_id); // Refresh team info
    } catch (error) {
        console.error("Error adding player:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.response.data.message, // Assuming the response contains a 'message' field
        });
    }
};

const waivePlayer = async (playerId) => {
    try {
        await axios.post(route("players.waive"), { id: playerId });
        fetchTeamRoster(props.team_id); // Refresh team info
    } catch (error) {
        console.error("Error waiving player:", error);
    }
};

const extendContract = (playerId) => {
    selectedPlayer.value = team_info.value.players.find(
        (player) => player.id === playerId
    );
    showExtendModal.value = true;
};

const confirmExtendContract = async () => {
    try {
        await axios.post(route("players.contract.extend"), {
            id: selectedPlayer.value.id,
            additional_years: additionalYears.value,
        });
        showExtendModal.value = false;
        fetchTeamRoster(props.team_id); // Refresh team info
    } catch (error) {
        console.error("Error extending contract:", error);
    }
};

const showPlayerProfile = (player) => {
    selectedPlayer.value = player;
    showPlayerProfileModal.value = true;
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
