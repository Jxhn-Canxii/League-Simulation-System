<template>
    <div
    class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
>
    <h3 class="text-md font-semibold text-gray-800">Team Rankings All-time</h3>
    <input
        type="text"
        v-model="search_topteams.search"
        @input.prevent="fetchTopTeams()"
        id="LeagueName"
        placeholder="Enter team name"
        class="mt-1 mb-2 p-2 border rounded w-full"
    />
    <table class="w-full">
        <thead>
            <tr class="border-b bg-gray-50 text-left  text-nowrap text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Team Name
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                   Conference
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Wins
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Loss
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Win Rate
                </th>
                <!-- <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Best Record
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Worst Record
                </th> -->
                <!-- <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                   Last Appearance
                </th> -->

            </tr>
        </thead>
        <tbody>
            <tr v-for="team in top_teams.data" v-if="top_teams.total_pages" :key="team.id" class="text-gray-700">
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.name }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <span class="inline-flex items-center text-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ team.conference }}
                    </span>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.total_wins ?? 0 }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.total_losses ?? 0 }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ moneyFormatter(team.win_rate ?? 0) }} %</p>
                </td>
                <!-- <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.best_season ?? '-' }} ({{ team.best_win_loss ?? '-' }})</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.worst_season ?? '-' }} ({{ team.worst_win_loss ?? '-' }})</p>
                </td> -->
                <!-- <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.last_finals_appearance }}</p>
                </td> -->
            </tr>
            <tr v-else>
                <td colspan="7" class="border-b text-center font-bold text-lg border-gray-200 bg-white px-3 py-3">
                    <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="flex w-full overflow-auto">
        <Paginator
            v-if="top_teams.total"
            :page_number="search_topteams.page_num"
            :total_rows="top_teams.total ?? 0"
            :itemsperpage="search_topteams.itemsperpage"
            @page_num="handleTopScorerPagination"
        />
    </div>
    </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import { roundNameFormatter,generateRandomKey, moneyFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";


const top_teams = ref([]);
const search_topteams = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});

const fetchTopTeams = async (page = 1) => {
    try {
        const response = await axios.post(route("records.team.winningest"),search_topteams.value);
        top_teams.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const handleTopScorerPagination = (page_num) => {
    search_topteams.value.page_num = page_num;
    fetchTopTeams();
};
onMounted(()=>{
    fetchTopTeams();
});
</script>
