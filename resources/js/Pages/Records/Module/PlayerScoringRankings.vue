<template>
    <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">All-Time Top Scorer</h3>

        <!-- Search and Sorting Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
            <!-- Search Bar -->
            <div class="w-full sm:w-1/2 relative">
                <input
                    type="search"
                    v-model="search_leaders.search"
                    @input.prevent="fetchLeaders()"
                    placeholder="Enter player or team name"
                    class="p-2 border rounded w-full text-sm"
                />
                <button
                    v-if="search_leaders.search"
                    @click="clearSearch()"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                    ✖
                </button>
            </div>

            <!-- Sort Dropdown -->
            <div class="w-full sm:w-1/4">
                <select
                    v-model="search_leaders.sort_by"
                    @change="fetchLeaders()"
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
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr lass="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Rank</th>
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Player</th>
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Team</th>
                        <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(player, index) in leaders.data"
                        v-if="leaders.total_pages"
                        :key="player.id"
                        class="text-gray-700"
                    >
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-sm">{{ player.rank }}</td>
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-sm">{{ player.player_name }}</td>
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-sm">{{ player.team_name ?? '-' }}</td>
                        <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-sm">
                            {{ moneyFormatter(player.total_stat) }}
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="4" class="text-center py-4 text-lg font-semibold text-red-500">
                            No Data Found!
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginator -->
        <div class="flex justify-center mt-4">
            <Paginator
                v-if="leaders.total"
                :page_number="search_leaders.page_num"
                :total_rows="leaders.total ?? 0"
                :itemsperpage="search_leaders.itemsperpage"
                @page_num="handleleadersPagination"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Paginator from "@/Components/Paginator.vue";
import { moneyFormatter } from "@/Utility/Formatter";

const leaders = ref([]);
const search_leaders = ref({
    page_num: 1,
    itemsperpage: 10,
    search: '',
    sort_by: 'total_points', // Default sort option
});

const fetchLeaders = async () => {
    try {
        const response = await axios.post(route("records.player.stats.leaders"), search_leaders.value);
        leaders.value = response.data;
    } catch (error) {
        console.error("Error fetching top leaders:", error);
    }
};

const handleleadersPagination = (page_num) => {
    search_leaders.value.page_num = page_num;
    fetchLeaders();
};

const clearSearch = () => {
    search_leaders.value.search = '';
    fetchLeaders();
};

onMounted(() => {
    fetchLeaders();
});
</script>
