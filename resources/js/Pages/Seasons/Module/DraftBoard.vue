<template>
    <div class="draft-board">
        <!-- Available Players Section -->
        <h3 class="text-lg font-semibold text-gray-800">Season {{ props.season_id }} Draft Results</h3>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8" v-if="draftResults.length > 0">
            <!-- Tabs for Rounds -->
            <div class="flex border-b mb-4">
                <button
                    class="px-4 py-2 text-sm font-medium"
                    :class="selectedRound === 1 ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
                    @click="selectedRound = 1"
                >
                    Round 1
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium"
                    :class="selectedRound === 2 ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
                    @click="selectedRound = 2"
                >
                    Round 2
                </button>
            </div>

            <!-- Round 1 Table -->
            <div v-if="selectedRound === 1">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Round #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Pick #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Draft #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Rank #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="player in draftResults.filter(player => player.round === 1)" :key="player.player_id" class="hover:bg-gray-100" @click.prevent="showPlayerProfileModal = player.player_id">
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.round }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.pick_number }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border"   :class="{'bg-green-100': player.pick_number >= player.rank, 'bg-red-100': player.pick_number < player.rank}">{{ player.pick_number }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.rank }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Round 2 Table -->
            <div v-if="selectedRound === 2">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Round #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Pick #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Draft #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Rank #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="player in draftResults.filter(player => player.round === 2)" :key="player.player_id" class="hover:bg-gray-100" @click.prevent="showPlayerProfileModal = player.player_id">
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.round }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.pick_number }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border"   :class="{'bg-green-100': player.pick_number + 80 >= player.rank, 'bg-red-100': player.pick_number + 80 < player.rank}">{{ player.pick_number + 80 }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.rank }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
                        </tr>
                    </tbody>
                </table>
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
            <PlayerPerformance :key="showPlayerProfileModal" :player_id="showPlayerProfileModal" />
        </div>
    </Modal>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import axios from "axios";

import Paginator from "@/Components/Paginator.vue";
import TopStatistics from "@/Pages/Analytics/Module/TopStatistics.vue";
import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";

const emits = defineEmits(["newSeason"]);
const showPlayerProfileModal = ref(false);
const draftResults = ref([]);
const selectedRound = ref(1);
const isHide = ref(true);
const key = ref(0);
const props = defineProps({
    season_id: {
        type: [Number,String],
        required: true,
    },
});
onMounted(async () => {
    await fetchDraftResults();
});

const fetchDraftResults = async () => {
    try {
        draftResults.value = [];
        const response = await axios.post(route("draft.season.results"),{season_id: props.season_id}); // Update with your API endpoint
        draftResults.value = response.data.draft_results;
    } catch (error) {
        console.error("Error fetching draft history:", error);
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
