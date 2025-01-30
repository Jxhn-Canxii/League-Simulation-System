<template>
    <!-- Top 10 players module -->
    <div
        class=" inline-block min-w-full overflow-hidden rounded p-4 bg-white"
    >
        <h3 class="text-md font-semibold text-gray-800 mb-6">
            Top 20 Players All-time
        </h3>
        <div class="overflow-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr
                        class="border-b bg-gray-50 text-left text-nowrap text-xs font-semibold uppercase tracking-wide text-gray-500"
                    >
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Rank
                        </th>
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
                        <!-- <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Teams Played
                        </th> -->
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            MVP
                        </th>
                        <!-- <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Championships
                        </th> -->
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Awards
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Points
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Assist
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Rebound
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Blocks
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Steals
                        </th>
                        <!-- <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Total Turnovers
                        </th> -->
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 py-2 px-2 text-left font-semibold uppercase text-gray-600"
                        >
                            Statistical Points
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(player,ii) in players"
                        v-if="players.length > 0"
                        :key="player.player_id"
                        class="text-gray-700"
                    >
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ ii + 1 }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.player_name }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden text-nowrap"
                        >
                            <!-- Display "Retired" if player is not active and retirement age is greater than or equal to their age -->
                            <span
                                v-if="!player.is_active"
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                                >Retired</span
                            >

                            <!-- Display "Active" if player is active and retirement age is less than their age -->
                            <span
                                v-else
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                >Active</span
                            >
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate" v-if="player.is_active">
                                {{ player.current_team_name ?? "Free Agent" }}
                            </p>
                            <p class="text-gray-900 whitespace-nowrap truncate" v-else>
                                -
                            </p>
                        </td>
                        <!-- <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-normal break-words">
                                {{ player.teams_played ?? "-" }}
                            </p>
                        </td> -->
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.finals_mvp_count }}
                            </p>
                        </td>
                        <!-- <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.championships_won }}
                            </p>
                        </td> -->
                        <td
                            class="border-b border-gray-200  text-wrap px-2 py-2"
                        >
                            <p class="text-gray-900">
                                {{ player.all_awards }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_points }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_assists }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_rebounds }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_blocks }}
                            </p>
                        </td>
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_steals }}
                            </p>
                        </td>
                        <!-- <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.total_turnovers }}
                            </p>
                        </td> -->
                        <td
                            class="border-b border-gray-200  px-2 py-2 text-ellipsis overflow-hidden"
                        >
                            <p class="text-gray-900 whitespace-nowrap truncate">
                                {{ player.base_statistical_points }}
                            </p>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="12" class="border-b text-center font-bold text-lg border-gray-200  px-3 py-3">
                            <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
import { ref, onMounted, onUnmounted } from "vue";
import Paginator from "@/Components/Paginator.vue";
import axios from "axios";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    team_id: Number,
});
const players = ref([]);
const fetchTopPlayers = async () => {
    try {
        const response = await axios.get(
            route("best.players.alltime")
        );
        players.value = response.data;
        console.log('loaded top 20 module');
    } catch (error) {
        console.error("Error fetching filtered players:", error);
    }
};

const handlePagination = (page_num) => {
    search_filters.page_num = page_num;
    fetchTopPlayers();
};

onMounted(() => {
    fetchTopPlayers();
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
