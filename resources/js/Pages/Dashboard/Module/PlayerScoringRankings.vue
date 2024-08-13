<template>
    <div
    class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
>
<h3 class="text-md font-semibold text-gray-800">All Time Top Scorer</h3>
    <input
        type="text"
        v-model="search_scorers.search"
        @input.prevent="fetchScorers()"
        id="LeagueName"
        placeholder="Enter team name"
        class="mt-1 mb-2 p-2 border rounded w-full"
    />
    <table class="w-full">
        <thead>
            <tr class="border-b bg-gray-50 text-left  text-nowrap text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Rank
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Player
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Current Team
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    All-time Score
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="player in scorers.data" v-if="scorers.total_pages" :key="player.id" class="text-gray-700">
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ player.rank }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ player.player_name }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ player.team_name ?? '-' }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ moneyFormatter(player.total_score) }}</p>
                </td>
            </tr>
            <tr v-else>
                <td colspan="4" class="border-b text-center font-bold text-lg border-gray-200 bg-white px-3 py-3">
                    <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="flex w-full overflow-auto">
        <Paginator
            v-if="scorers.total"
            :page_number="search_scorers.page_num"
            :total_rows="scorers.total ?? 0"
            :itemsperpage="search_scorers.itemsperpage"
            @page_num="handleScorersPagination"
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

const scorers = ref([]);
const search_scorers = ref({
    page_num: 1,
    itemsperpage:10,
    search: '',
});

const fetchScorers = async (page = 1) => {
    try {
        const response = await axios.post(route("dashboard.player.topscorer"),search_scorers.value);
        scorers.value = response.data;
} catch (error) {
        console.error("Error fetching top scorer of all time:", error);
    }
};
const handleScorersPagination = (page_num) => {
    search_scorers.value.page_num = page_num;
    fetchScorers();
};
onMounted(()=>{
    fetchScorers();
});
</script>
