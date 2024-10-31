<template>
    <div
    class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
>
    <h3 class="text-md font-semibold text-gray-800">All-time scorer</h3>
    <input
        type="text"
        v-model="search_topscorers.search"
        @input.prevent="fetchTopScorers()"
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
                    Score
                </th>
                <!-- <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                   Last Appearance
                </th> -->

            </tr>
        </thead>
        <tbody>
            <tr v-for="team in top_scorers.data" v-if="top_scorers.total_pages" :key="team.id" class="text-gray-700">
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.name }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <span class="inline-flex items-center text-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ team.conference }}
                    </span>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ moneyFormatter(team.total_score) }}</p>
                </td>
                <!-- <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.last_finals_appearance }}</p>
                </td> -->
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
            v-if="top_scorers.total"
            :page_number="search_topscorers.page_num"
            :total_rows="top_scorers.total ?? 0"
            :itemsperpage="search_topscorers.itemsperpage"
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


const top_scorers = ref([]);
const search_topscorers = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});

const fetchTopScorers = async (page = 1) => {
    try {
        const response = await axios.post(route("records.team.topscorer"),search_topscorers.value);
        top_scorers.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const handleTopScorerPagination = (page_num) => {
    search_topscorers.value.page_num = page_num;
    fetchTopScorers();
};
onMounted(()=>{
    fetchTopScorers();
});
</script>
