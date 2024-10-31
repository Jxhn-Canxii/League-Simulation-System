<template>
    <div
    class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
>
<h3 class="text-md font-semibold text-gray-800">Championship count</h3>
    <input
        type="text"
        v-model="search_champions.search"
        @input.prevent="fetchChampions()"
        id="LeagueName"
        placeholder="Enter team name"
        class="mt-1 mb-2 p-2 border rounded w-full"
    />
    <small class="text-red-400">{{ champions.total }} teams has won a championship.</small>
    <table class="w-full">
        <thead>
            <tr class="border-b bg-gray-50 text-left  text-nowrap text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Team
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    # of Championship
                </th>
                <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                    Last Appearance
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="team in champions.data" v-if="champions.total_pages" :title="'Conference:'+team.conference_name+', Last Appearance:'+team.last_finals_appearance" :key="team.id" class="text-gray-700">
                <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.name }} ({{ team.acronym }})</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.championships }}</p>
                </td>
                <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                    <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.last_finals_appearance }}</p>
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
            v-if="champions.total"
            :page_number="search_champions.page_num"
            :total_rows="champions.total ?? 0"
            :itemsperpage="search_champions.itemsperpage"
            @page_num="handleChampionsPagination"
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

const champions = ref([]);
const search_champions = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});

const fetchChampions = async (page = 1) => {
    try {
        const response = await axios.post(route("records.champions"),search_champions.value);
        champions.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const handleChampionsPagination = (page_num) => {
    search_champions.value.page_num = page_num;
    fetchChampions();
};
onMounted(()=>{
    fetchChampions();
});
</script>
