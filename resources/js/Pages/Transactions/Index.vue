<template>
    <Head title="Players" />

    <AuthenticatedLayout>
        <template #header>
            Players
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="grid grid-cols-1 gap-6">

                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2">
                    <h3 class="text-md font-semibold text-gray-800">Player List</h3>
                    <input
                        type="text"
                        v-model="search.search"
                        @input="fetchAllPlayers()"
                        id="LeagueName"
                        placeholder="Enter Player name"
                        class="mt-1 mb-2 p-2 border rounded w-full"
                    />
                    <div v-if="data.free_agents.length === 0" class="text-center text-gray-500">No player found.</div>
                    <div v-else class="overflow-x-auto mt-4">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 text-nowrap">
                                <tr>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Current Team</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Remaining Contract</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Age</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="player in data.free_agents" :key="player.player_id" class="hover:bg-gray-100">
                                    <td class="px-2 py-1 whitespace-nowrap border">{{ player.name }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name ?? '-' }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap border">{{ player.contract_years ?? 0 }} yrs.</td>
                                    <td class="px-2 py-1 whitespace-nowrap border">{{ player.age }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap border">
                                        <span :class="roleClasses(player.role)" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium">
                                            {{ player.role }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1 whitespace-nowrap border">
                                        <span v-if="player.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Waived/Free Agent</span>
                                    </td>
                                    <td class="px-2 py-1 whitespace-nowrap border">
                                        <button
                                        @click="showPlayerProfile(player)"
                                        class="px-2 py-1 bg-blue-500 text-white rounded-l text-xs"
                                    >
                                        View Profile
                                    </button>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="data.total > 0" class="flex justify-start font-bold p-2 text-xs overflow-auto">
                        <button @click="fetchAllPlayers(data.current_page - 1)" :disabled="data.current_page === 1" class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50">Previous</button>
                        <button v-for="pageNumber in data.total_pages" :key="pageNumber" @click="fetchAllPlayers(pageNumber)" :disabled="data.current_page === pageNumber" class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50">{{ pageNumber }}</button>
                        <button @click="fetchAllPlayers(data.current_page + 1)" :disabled="data.current_page === data.total_pages" class="px-3 py-1 mr-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50">Next</button>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showPlayerProfileModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="p-6 block">
                <!-- Image Section -->
                <div class="ml-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Player Profile
                    </h2>
                    <div class="mt-4">
                        <p><strong>Name:</strong> {{ selectedPlayer.name }}</p>
                        <p><strong>Age:</strong> {{ selectedPlayer.age }}</p>
                        <p><strong>Role:</strong> {{ selectedPlayer.role }}</p>
                    </div>
                </div>
                <PlayerPerformance :key="selectedPlayer.player_id" :player_id="selectedPlayer.player_id" />
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import axios from 'axios'; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import PlayerPerformance from '../Teams/PlayerPerformance.vue';
const showPlayerProfileModal = ref(false);
const selectedPlayer = ref([]);
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
    search: '',
});
const teams = ref([]);


const fetchAllPlayers = async (page = 1) => {
    try {
        search.value.current_page = page;
        const response = await axios.post(route("players.list.all"), search.value);
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching free agents:", error);
    }
};
const showPlayerProfile = (player) => {
    selectedPlayer.value = player;
    showPlayerProfileModal.value = true;
};

const assignTeams = async (player_id) => {
    try {
        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to assign this player to a team?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, assign it!',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            // Proceed with the request if confirmed
            const response = await axios.post(route("assign.freeagent.teams"), { player_id: player_id });

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.data.message, // Assuming the response contains a 'message' field
            });
            fetchAllPlayers();
        } else {
            // Show cancellation message if canceled
            Swal.fire({
                icon: 'info',
                title: 'Cancelled',
                text: 'The player was not assigned to a team.',
            });
        }
    } catch (error) {
        console.error("Error assigning team:", error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
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
    fetchAllPlayers();
});
</script>
