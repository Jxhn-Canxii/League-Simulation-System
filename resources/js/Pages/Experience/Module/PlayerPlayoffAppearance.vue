<template>
    <div
        class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2"
    >
        <h3 class="text-md font-semibold text-gray-800">
            Player Playoff Filters
        </h3>

        <!-- Input for filtering -->
         <div class="flex gap-2">
            <input
            type="text"
            v-model="search_filters.search"
            @input.prevent="fetchFilteredPlayers()"
            placeholder="Enter player name"
            class="mt-1 mb-2 p-2 border rounded w-full"
        />

        <!-- Dropdown for sorting by -->
        <select
            v-model="search_filters.sort_by"
            @change="fetchFilteredPlayers()"
            class="mt-1 mb-2 p-2 border rounded w-full"
        >
            <option value="playoff_appearances">
                Most Playoff Appearances
            </option>
            <option value="finals_appearances">Most Finals Appearances</option>
            <option value="big_four">Most Big 4 Appearances</option>
            <option value="seasons_played">Seasons Played</option>
            <option value="championships_won">Championships</option>
        </select>
         </div>


        <table class="w-full text-xs">
            <thead>
                <tr
                    class="border-b bg-gray-50 text-left text-nowrap text-xs font-semibold uppercase tracking-wide text-gray-500"
                >
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Player
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Status
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Current Team
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Teams Played
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Playoffs
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Big 4
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Finals
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Championships
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                    >
                        Seasons Played
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="player in players.data"
                    :key="player.id"
                    class="text-gray-700"
                >
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.player_name }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden text-nowrap"
                    >
                        <!-- Display "Retired" if player is not active and retirement age is greater than or equal to their age -->
                        <span
                            v-if="!player.active_status"
                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                            >Free Agent/Retired</span
                        >

                        <!-- Display "Active" if player is active and retirement age is less than their age -->
                        <span
                            v-else
                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                            >Active</span
                        >
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.current_team_name ?? "-" }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-normal break-words">
                            {{ player.teams_played_for_in_playoffs ?? "-" }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.total_playoff_appearances }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.interconference_semi_finals_appearances }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.finals_appearances }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.championships_won }}
                        </p>
                    </td>
                    <td
                        class="border-b border-gray-200 bg-white px-2 py-2 text-ellipsis overflow-hidden"
                    >
                        <p class="text-gray-900 whitespace-nowrap truncate">
                            {{ player.total_seasons_played }}
                        </p>
                    </td>
                </tr>
                <!-- <tr v-if="!players.data.length">
                    <td colspan="6" class="border-b text-center font-bold text-lg border-gray-200 bg-white px-3 py-3">
                        <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                    </td>
                </tr> -->
            </tbody>
        </table>

        <div class="flex w-full overflow-auto mt-2">
            <Paginator
                v-if="players.total"
                :page_number="search_filters.page_num"
                :total_rows="players.total ?? 0"
                :itemsperpage="search_filters.itemsperpage"
                @page_num="handlePagination"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Paginator from "@/Components/Paginator.vue";
import axios from "axios";
import { useForm } from "@inertiajs/vue3";

const players = ref([]);
const search_filters = useForm({
    page_num: 1,
    itemsperpage: 10,
    search: "",
    sort_by: "playoff_appearances",
    sort_order: "desc",
});

const fetchFilteredPlayers = async () => {
    try {
        const response = await axios.post(
            route("filter.playoffs.player"),
            search_filters
        );
        players.value = response.data;
    } catch (error) {
        console.error("Error fetching filtered players:", error);
    }
};

const handlePagination = (page_num) => {
    search_filters.page_num = page_num;
    fetchFilteredPlayers();
};

onMounted(() => {
    fetchFilteredPlayers();
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
