<template>
    <div class="team-roster">
        <h2 class="text-xl font-semibold text-gray-800">Player Signings</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <!-- Players Table -->
        <div class="overflow-hidden">
            <div
                class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
            >
                <h3 class="text-md font-semibold text-gray-800">
                    Free Agents List
                </h3>
                <input
                    v-if="false"
                    type="text"
                    v-model="search.search"
                    @input="fetchFreeAgent()"
                    id="LeagueName"
                    placeholder="Enter Player name"
                    class="mt-1 mb-2 p-2 border rounded w-full"
                />
                <div class="flex justify-between">

                    <div>
                        <button
                            @click="assignTeamsAuto()"
                            class="px-4 py-2 bg-rose-500 text-white rounded mb-4 text-sm"
                        >
                        <i class="fa fa-users"></i> Distribute Free Agents
                        </button>
                    </div>
                    <div>
                        <button
                            @click="showAddPlayerModal = true"
                            class="px-4 py-2 bg-green-500 text-white rounded mb-4 mr-2 text-sm"
                        >
                            <i class="fa fa-user"></i> Add Rookie Player
                        </button>
                        <button
                            @click.prevent="addMultiplePlayers(50)"
                            class="px-4 py-2 bg-green-700 text-white rounded mb-4 text-sm"
                        >
                            <i class="fa fa-user"></i> Add Rookie Player From Api
                        </button>
                    </div>

                </div>
                <div
                    v-if="data.free_agents.length === 0"
                    class="text-center text-gray-500"
                >
                    No free agents found.
                </div>
                <div v-else class="overflow-x-auto mt-4">
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
                                    title="Games Played"
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
                                    title="Assists Per Game"
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
                                    title="Turnovers Per Game"
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
                                <th
                                    class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="player in data.free_agents"
                                :key="player.player_id"
                                class="hover:bg-gray-100"
                            >
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{ player.name }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{ player.age }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    <span
                                        :class="roleClasses(player.role)"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium"
                                    >
                                        {{ player.role }}
                                    </span>
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{ player.games_played || 0 }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_points_per_game || 0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_rebounds_per_game ||
                                                0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_assists_per_game || 0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_steals_per_game || 0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_blocks_per_game || 0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_turnovers_per_game ||
                                                0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    {{
                                        parseFloat(
                                            player.average_fouls_per_game || 0
                                        ).toFixed(1)
                                    }}
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    <span
                                        v-if="player.is_active"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                        >Active</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                                        >Waived</span
                                    >
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap border">
                                    <button
                                        @click="assignTeams(player.player_id)"
                                        class="px-2 py-1 bg-blue-500 text-white rounded-l text-xs"
                                    >
                                        Assign New Team
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div
                    v-if="data.total > 0"
                    class="flex justify-start font-bold p-2 text-xs overflow-auto"
                >
                    <button
                        @click="fetchFreeAgent(data.current_page - 1)"
                        :disabled="data.current_page === 1"
                        class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        v-for="pageNumber in data.total_pages"
                        :key="pageNumber"
                        @click="fetchFreeAgent(pageNumber)"
                        :disabled="data.current_page === pageNumber"
                        class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50"
                    >
                        {{ pageNumber }}
                    </button>
                    <button
                        @click="fetchFreeAgent(data.current_page + 1)"
                        :disabled="data.current_page === data.total_pages"
                        class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
        <Modal :show="showAddPlayerModal" :maxWidth="'sm'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showAddPlayerModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="grid grid-cols-1 gap-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800">
                    Add Player(Free Agent)
                </h2>
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
                    @click="addPlayerV1()"
                    class="px-4 py-2 bg-green-500 text-white rounded"
                >
                    Add Player
                </button>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";

const showAddPlayerModal = ref(false);
const newPlayerName = ref("");
const data = ref({
    free_agents: [],
    current_page: 1,
    total_pages: 0,
    total: 0,
});
const search = ref({
    current_page: 1,
    total_pages: 0,
    total: 0,
    search: "",
});
const teams = ref([]);
const emits = defineEmits(["newSeason"]);
const addPlayerV1 = async () => {
    try {
        const response = await axios.post(route("players.add.free.agent"), {
            name: newPlayerName.value,
        });
        newPlayerName.value = ""; // Clear the input
        showAddPlayerModal.value = false; // Close the modal
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: response.data.message, // Assuming the response contains a 'message' field
        });
        fetchFreeAgent(); // Refresh free agent list
    } catch (error) {
        console.error("Error adding player:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.response.data.message, // Assuming the response contains a 'message' field
        });
    }
};
const fetchRandomFullName = async () => {
    try {
        const response = await axios.get('https://randomuser.me/api/?inc=name&gender=male'); // API URL for random male user
        const { first, last } = response.data.results[0].name; // Extract first and last name

        // Function to check if a name contains only English alphabet letters
        const isEnglishReadable = (name) => /^[A-Za-z]+$/.test(name);

        if (isEnglishReadable(first) && isEnglishReadable(last)) {
            return `${first} ${last}`; // Return full name if valid
        } else {
            return null; // Return null if the name is not valid
        }
    } catch (error) {
        console.error("Error fetching random player name:", error);
        return null; // Return null on error
    }
};

const addPlayer = async (name) => {
    try {
        const response = await axios.post(route("players.add.free.agent"), {
            name: name,
        });
        return response.data.message; // Return success message for logging
    } catch (error) {
        console.error("Error adding player:", error.response.data.message);
        throw new Error(error.response.data.message); // Throw error to be caught in Promise.all
    }
};

const addMultiplePlayers = async (count) => {
    try {
        const promises = [];

        for (let i = 0; i < count; i++) {
            const randomFullName = await fetchRandomFullName(); // Fetch random full name
            if(randomFullName != null){
                promises.push(addPlayer(randomFullName)); // Add the promise to the array
            }

        }

        // Wait for all promises to resolve
        const results = await Promise.all(promises);

        // Notify success
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: `Successfully added ${count} players.`,
        });

        // Optionally log results
        results.forEach((message, index) => {
            console.log(`Player ${index + 1}: ${message}`);
        });

        fetchFreeAgent(); // Refresh free agent list
    } catch (error) {
        console.error("Error adding multiple players:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.message, // Show the first error message encountered
        });
    }
};

const fetchFreeAgent = async (page = 1) => {
    try {
        search.value.current_page = page;
        const response = await axios.post(
            route("players.free.agents"),
            search.value
        );
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching free agents:", error);
    }
};

const assignTeams = async (player_id) => {
    try {
        // Show confirmation dialog
        const result = await Swal.fire({
            title: "Are you sure?",
            text: "Do you want to assign this player to a team?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, assign it!",
            cancelButtonText: "No, cancel",
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            // Proceed with the request if confirmed
            const response = await axios.post(route("assign.freeagent.teams"), {
                player_id: player_id,
            });

            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message, // Assuming the response contains a 'message' field
            });
            const is_new_season = response.data.team_count == 0;
            if (is_new_season) {
                emits("newSeason", is_new_season);
            }
            fetchFreeAgent();
        } else {
            // Show cancellation message if canceled
            Swal.fire({
                icon: "info",
                title: "Cancelled",
                text: "The player was not assigned to a team.",
            });
        }
    } catch (error) {
        console.error("Error assigning team:", error);
        Swal.fire({
            icon: "warning",
            title: "Warning!",
            text: error.response.data.message,
        });
    }
};
const assignTeamsAuto = async () => {
    try {
        // Show confirmation dialog
        const result = await Swal.fire({
            title: "Are you sure?",
            text: "Do you want to assign this player to a team?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, assign it!",
            cancelButtonText: "No, cancel",
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            // Proceed with the request if confirmed
            const response = await axios.post(
                route("auto.assign.freeagent.teams"),
                { player_id: 0 }
            );

            const data = response.data;
            let message = "";

            if (data.message === "No free agents available.") {
                message = `<p>${data.message}</p>`;
                if (data.incomplete_teams.length > 0) {
                    message += "<ul>";
                    data.incomplete_teams.forEach((team) => {
                        message += `<li><strong>${team.team_name}</strong>: ${team.players_needed} player(s) needed</li>`;
                    });
                    message += "</ul>";
                }
            } else {
                message = `<p>${data.message}</p>`;
                if (data.remaining_free_agents > 0) {
                    message += `<p>Remaining free agents: ${data.remaining_free_agents}</p>`;
                }
                if (data.incomplete_teams.length > 0) {
                    message += "<ul>";
                    data.incomplete_teams.forEach((team) => {
                        message += `<li><strong>${team.team_name}</strong>: ${team.players_needed} player(s) still needed</li>`;
                    });
                    message += "</ul>";
                }
            }

            Swal.fire({
                icon: "success",
                title: "Success!",
                html: message,
            });
            const is_new_season = response.data.team_count == 0;
            if (is_new_season) {
                emits("newSeason", is_new_season);
            }
            fetchFreeAgent();
        } else {
            // Show cancellation message if canceled
            Swal.fire({
                icon: "info",
                title: "Cancelled",
                text: "The player was not assigned to a team.",
            });
        }
    } catch (error) {
        console.error("Error assigning team:", error);
        Swal.fire({
            icon: "warning",
            title: "Warning!",
            text: error.response.data.message,
        });
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

onMounted(() => {
    fetchFreeAgent();
});
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
