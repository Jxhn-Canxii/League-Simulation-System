<template>
    <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">All-Time Team Scorers</h3>
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
            <!-- Search Bar -->
            <div class="w-full sm:w-1/2 relative">
                <input
                    type="search"
                    v-model="search_topscorers.search"
                    @input.prevent="fetchTopScorers()"
                    placeholder="Enter player or team name"
                    class="p-2 border rounded w-full text-sm"
                />
            </div>

            <!-- Sort Dropdown -->
            <div class="w-full sm:w-1/4">
                <select
                    v-model="search_topscorers.sort_by"
                    @change="fetchTopScorers()"
                    class="p-2 border rounded w-full text-sm"
                >
                    <option value="total_points">Sort by Points</option>
                    <option value="total_rebounds">Sort by Rebounds</option>
                    <option value="total_assists">Sort by Assists</option>
                    <option value="total_steals">Sort by Steals</option>
                    <option value="total_blocks">Sort by Blocks</option>
                </select>
            </div>
        </div>
        <!-- Table -->
        <div class="overflow-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Team Name
                        </th>
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Conference
                        </th>
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Score
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="team in top_scorers.data"
                        v-if="top_scorers.total_pages"
                        :key="generateRandomKey()"
                        class="text-gray-700"
                    >
                        <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                            <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.name }}</p>
                        </td>
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                            <span class="inline-flex items-center text-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ team.conference }}
                            </span>
                        </td>
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                            <p class="text-gray-900 whitespace-no-wrap uppercase">{{ moneyFormatter(team.total_points) }}</p>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="4" class="border-b text-center font-bold text-lg border-gray-200 bg-white px-3 py-3">
                            <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="flex w-full overflow-auto mt-4">
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
import { ref, onMounted } from "vue";
import Paginator from "@/Components/Paginator.vue";
import { moneyFormatter, generateRandomKey } from "@/Utility/Formatter";

const top_scorers = ref([]);
const search_topscorers = ref({
    page_num: 1,
    itemsperpage: 10,
    sort_by: "total_points", // Default sorting by total points
    search: "",
});

const fetchTopScorers = async () => {
    try {
        const response = await axios.post(route("records.team.topscorer"), search_topscorers.value);
        top_scorers.value = response.data;
    } catch (error) {
        console.error("Error fetching top scorers:", error);
    }
};

const handleTopScorerPagination = (page_num) => {
    search_topscorers.value.page_num = page_num;
    fetchTopScorers();
};

onMounted(() => {
    fetchTopScorers();
});
</script>
