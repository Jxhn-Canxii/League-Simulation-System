<template>
    <div class="draft-board">
        <h2 class="text-xl font-semibold text-gray-800">Draft Board</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <!-- Available Players Section -->
        <button
            @click.prevent="draftPlayer()"
            class="px-2 py-2 bg-blue-500 rounded font-bold text-md float-end text-white shadow"
        >
            <i class="fa fa-users"></i> Draft
        </button>
        <h3 class="text-lg font-semibold text-gray-800">Draft Results</h3>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8" v-if="draftOrder.length > 0">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Round #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Pick #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Team
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="player in draftOrder"
                        :key="player.id"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.round }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.pick }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.team_name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto mb-8"  v-if="draftResults.length > 0">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Round #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Pick #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Name
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Team
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="player in draftResults"
                        :key="player.id"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.round }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.pick_number }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.player_name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Available Players Section -->
        <h3 class="text-lg font-semibold text-gray-800">Available Players</h3>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8">
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
                            Overall Rating
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="player in availablePlayers.rookies"
                        :key="player.id"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.overall_rating }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex w-full overflow-auto">
            <Paginator
                v-if="availablePlayers.total"
                :page_number="search.page_num"
                :total_rows="availablePlayers.total ?? 0"
                :itemsperpage="search.itemsperpage"
                @page_num="handlePagination"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

import Paginator from "@/Components/Paginator.vue";

const emits = defineEmits(["newSeason"]);
const teams = ref([]);
const availablePlayers = ref([]);
const draftOrder = ref([]);
const draftResults = ref([]);

const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: "",
    itemsperpage: 10,
});

onMounted(async () => {
    await fetchDraftOrder();
    await fetchAvailablePlayers();
});

const fetchAvailablePlayers = async () => {
    try {
        const response = await axios.post(route("draft.list"), search.value); // Update with your API endpoint
        availablePlayers.value = response.data;
    } catch (error) {
        console.error("Error fetching available players:", error);
    }
};
const handlePagination = (page_num) => {
    search.value.page_num = page_num;
    fetchAvailablePlayers();
};
const fetchDraftOrder = async () => {
    try {
        const response = await axios.get(route("draft.orders")); // Update with your API endpoint
        draftOrder.value = response.data.draft_order;
    } catch (error) {
        console.error("Error fetching teams:", error);
    }
};
const fetchDraftResults = async () => {
    try {
        draftOrder.value = [];
        const response = await axios.get(route("draft.results")); // Update with your API endpoint
        draftResults.value = response.data.draft_results;
    } catch (error) {
        console.error("Error fetching draft history:", error);
    }
};

const selectPlayer = (player) => {
    // Logic to handle player selection
    console.log("Selected player:", player);
    // Call API to draft the player
};

const draftPlayer = async () => {
    try {
        const response = await axios.post(route("draft.players")); // Update with your API endpoint

        if(response){
              // Show success alert
            await Swal.fire({
                title: 'Success!',
                text: 'Player drafted successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            });

            await fetchDraftResults();
            await fetchAvailablePlayers();

        }
        emits("newSeason", is_new_season);
    } catch (error) {
        console.error("Error fetching draft history:", error);

        // Show error alert
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to draft player. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
};

</script>

<style scoped>
.draft-board {
    padding: 16px;
}
.team-card {
    background: #f9fafb; /* Tailwind gray-50 */
}
</style>
