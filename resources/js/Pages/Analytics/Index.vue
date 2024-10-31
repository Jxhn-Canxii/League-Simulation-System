<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header>
            Analytics
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="grid grid-cols-1 gap-6">

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import axios from 'axios'; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";

import PlayerPerformance from '../Teams/Module/PlayerPerformance.vue';

const showPlayerProfileModal = ref(false);
const selectedPlayer = ref([]);
const data = ref({
    free_agents: [],
    current_page: 1,
    total_pages: 0,
    total: 0,
});
const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
    itemsperpage: 10,
});
const teams = ref([]);


const fetchAllPlayers = async () => {
    try {
        const response = await axios.post(route("players.list.all"), search.value);
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching free agents:", error);
    }
};
const handlePagination = (page_num) => {
    search.value.page_num = page_num;
    fetchAllPlayers();
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
