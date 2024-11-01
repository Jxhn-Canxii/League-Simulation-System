<template>
    <div
        class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
    >
        <h3 class="text-md font-semibold text-gray-800">
            Player Awards Filters
        </h3>

        <!-- Input for filtering -->
        <div class="flex gap-2">
            <select
                v-model="search_filters.season_id"
                @change.prevent="search_filters.awards_name = 0,fetchFilteredPlayers()"
                class="mt-1 mb-2 p-2 border rounded w-full"
            >
                <option value="0">Select Season</option>
                <option
                    v-for="(season, ss) in seasons"
                    :key="season.season_id"
                    :value="season.season_id"
                >
                    {{ season.name }}
                </option>
            </select>
            <!-- Dropdown for sorting by -->
            <select
                v-model="search_filters.awards_name"
                @change.prevent="search_filters.season_id = 0,fetchFilteredPlayers()"
                class="mt-1 mb-2 p-2 border rounded w-full"
            >
                <option value="0">Select Award Name</option>
                <option v-if="awardsName.awardNames?.length > 0" v-for="(a,aa) in awardsName.awardNames" :key="aa" :value="a.award_name">
                    {{ a.award_name }}
                </option>
            </select>
        </div>

        <div class="overflow-x-auto" v-if="awards">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Season Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Award Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Player Name
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Draft
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Team Name
                        </th>
                        <!-- <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Award Description
                        </th> -->
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="award in awards" :key="award.id">
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                        >
                            {{ award.season_name }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                        >
                            {{ award.award_name }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ award.player_name }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ award.draft_status }} {{ award.drafted_team ? `(${award.drafted_team})` : '' }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ award.team_name }}
                        </td>
                        <!-- <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ award.award_description }}
                        </td> -->
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto flex justify-center" v-else>
            <p class="font-bold text-red-500">Please Choose Season.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Paginator from "@/Components/Paginator.vue";
import axios from "axios";
import { useForm } from "@inertiajs/vue3";

const awards = ref([]);
const awardsName = ref([]);
const seasons = ref([]);
const search_filters = useForm({
    page_num: 1,
    itemsperpage: 10,
    search: "",
    season_id: 0,
    awards_name: 0,
});

const fetchFilteredPlayers = async () => {
    try {
        awards.value = [];
        const response = await axios.post(
            route("player.awards.filter"),
            search_filters
        );
        awards.value = response.data.awards;
    } catch (error) {
        console.error("Error fetching filtered players:", error);
    }
};
const seasonsDropdown = async () => {
    try {
        seasons.value = JSON.parse(localStorage.getItem("seasons"));
    } catch (error) {
        console.error("Error fetching seasons dropdown:", error);
    }
};
const awardsDropdown = async () => {
    try {
        const response = await axios.get(route("player.awards.dropdown"));
        awardsName.value = response.data;
    } catch (error) {
        console.error("Error fetching awards dropdown:", error);
    }
};
// const handlePagination = (page_num) => {
//     search_filters.page_num = page_num;
//     fetchFilteredPlayers();
// };

onMounted(() => {
    fetchFilteredPlayers();
    seasonsDropdown();
    awardsDropdown();
});
</script>

<style scoped>
/* Additional custom styles */
table {
    border-collapse: collapse;
}
th,
td {
    border: 1px solid #ddd;
    padding: 0.5rem;
}
th {
    background-color: #f4f4f4;
}
</style>
