<template>
    <div class="team-info p-4">
        <!-- <h2 class="text-xl font-semibold text-gray-800" v-if="team_history.history">
            {{ team_history.history[0].team_name ?? "-" }} ({{ team_history.history[0].team_acronym ?? "-" }})
        </h2> -->
        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <div class="gap-4 max-h-100">
            <table class="min-w-full divide-y divide-gray-200 p-2">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season</th>
                        <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Wins</th>
                        <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Losses</th>
                        <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Conference Rank</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conference Champions</th>
                        <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">National Rank</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">National Champions</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Round Played</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">National Finals</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(season, index) in team_history.history" :key="season.season" @click.enter.prevent="showTooltip = season.round_info" class="hover:bg-gray-200">
                        <td class="px-2 py-3 whitespace-nowrap border">{{ season.season_name }}</td>
                        <td class="px-2 py-3 whitespace-nowrap border text-right">{{ season.wins }}</td>
                        <td class="px-2 py-3 whitespace-nowrap border text-right">{{ season.losses }}</td>
                        <td class="px-2 py-3 whitespace-nowrap border text-right">{{ season.conference_rank }}</td>
                        <td class="px-2 py-3 whitespace-nowrap border">
                            <span v-if="season.conference_rank == 1" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-600">
                                Conference Champions
                            </span>
                            <span v-else-if="season.conference_rank == 2" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                Conference Runner Up
                            </span>
                            <span v-else-if="season.conference_rank == 3" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-900">
                                Conference Third Place
                            </span>
                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                No Awards
                            </span>
                        </td>
                        <td class="px-2 py-3 whitespace-nowrap border text-right">{{ season.overall_rank }}</td>
                        <td class="px-2 py-3 whitespace-nowrap border">
                            <span v-if="season.overall_rank == 1" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-600">
                                National Champions
                            </span>
                            <span v-else-if="season.overall_rank == 2" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                National Runner Up
                            </span>
                            <span v-else-if="season.overall_rank == 3" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-900">
                                National Third Place
                            </span>
                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                No Awards
                            </span>
                        </td>
                        <td class="px-2 py-3 whitespace-nowrap border">
                            <span v-if="isNumberChecker(season.round_info.round)" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Conference Qualifier
                            </span>
                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                {{ roundNameFormatter(season.round_info.round) }}
                            </span>
                        </td>
                        <td class="px-2 py-3 whitespace-nowrap border">
                            <span v-if="season.round_info.won && season.round_info.round == 'finals'" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                National Finals Champion
                            </span>
                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                No Awards
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination Controls -->
        <div class="flex w-full overflow-auto">
            <Paginator
              v-if="team_history.total_items"
              :page_number="search.page_num"
              :total_rows="team_history.total_items ?? 0"
              :itemsperpage="search.itemsperpage"
              @page_num="handlePagination"
            />
          </div>
        <Modal :show="showTooltip" :maxWidth="'sm'">
            <button class="flex float-end bg-gray-100 p-3" @click.prevent="showTooltip = false">
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="grid grid-cols-1 gap-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800">
                    Last Game Result
                </h2>
                <div><strong>Round:</strong> {{ roundNameFormatter(showTooltip.round) }}</div>
                <div><strong>Result:</strong> {{ showTooltip.won ? 'Won' : 'Lost' }}</div>
                <div><strong>Score:</strong> {{ showTooltip.score }} - {{ showTooltip.opponent_score }}</div>
                <div><strong>Opponent:</strong> {{ showTooltip.opponent_name }}</div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import { roundNameFormatter } from "@/Utility/Formatter";
import { ref, onMounted, computed, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    team_id: {
        type: Number,
        required: true
    }
});
const showTooltip = ref(false);
const team_history = ref([]);

const search = ref({
    page_num: 1,
    search: "",
    itemsperpage: 10,
});
;
onMounted(() => {
    fetchTeamHistory();
});
const fetchTeamHistory = async () => {
    try {
        search.value.team_id = props.team_id;
        const response = await axios.post(route("teams.season.history"),search.value);
        team_history.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const handlePagination = (page_num) => {
    search.value.page_num = page_num ?? 1;
    fetchTeamHistory();
}
const isNumberChecker = (round) => {
    return !isNaN(round) && !isNaN(parseFloat(round));
};
</script>
