<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="grid grid-cols-2 gap-6">
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
                                All-time score
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
                                Team Name
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Conference
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
                        <tr v-for="team in champions.data" v-if="champions.total_pages" :key="team.id" class="text-gray-700">
                            <td class="border-b border-gray-200 bg-white px-3 py-3 text-xs">
                                <p class="text-gray-900 whitespace-no-wrap uppercase">{{ team.name }}</p>
                            </td>
                            <td class="border-b border-gray-200 bg-white text-center px-3 py-3 text-xs">
                                <span class="inline-flex items-center text-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ team.conference_name }}
                                </span>
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
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <div class="bg-white overflow-hidden shadow-sm rounded min-h-full p-3">
                    <h3 class="text-md font-semibold text-gray-800">Last 12 Games</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4" v-if="recent_results">
                            <div v-for="game in recent_results.data" :key="game.id" class="col-span-1">
                                <div class="bg-white shadow-md rounded-md overflow-hidden">
                                    <div class="px-4 py-5 sm:px-6">
                                        <h3 class="text-xs font-bold uppercase text-nowrap leading-6 text-gray-800">
                                            {{ game.home_team_name }} vs {{ game.away_team_name }}
                                        </h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                           {{ roundNameFormatter(game.round) }}
                                        </p>
                                    </div>
                                    <div class="border-t border-gray-200">
                                        <div class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Home
                                            </dt>
                                            <dd :class="[game.status === 'Loss' ? 'font-bold text-red-500' : '', game.away_score < game.home_score ? 'font-bold' : '']" class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                {{ game.home_score }}
                                                <span v-if="game.home_score > game.away_score" class="ml-2 text-yellow-500">
                                                    <i class="fas fa-medal"></i>
                                                </span>
                                            </dd>
                                        </div>
                                        <div class="bg-gray-200 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Away
                                            </dt>
                                            <dd :class="[game.status === 'Loss' ? 'font-bold text-red-500' : '', game.away_score > game.home_score ? 'font-bold' : '']" class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                {{ game.away_score }}
                                                <span v-if="game.home_score < game.away_score" class="ml-2 text-yellow-500">
                                                    <i class="fas fa-medal"></i>
                                                </span>
                                            </dd>
                                        </div>
                                        <!-- Additional details can be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <div class="bg-white overflow-hidden shadow-sm rounded min-h-full p-3">
                    <h3 class="text-md font-semibold text-gray-800">Top 16 Playoff Teams (Most Playoff Games)</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4" v-if="rivals">
                            <div v-for="team in playoffs.data" :key="team.id" class="col-span-1">
                                <div class="bg-white shadow-md rounded-md overflow-hidden">
                                    <div class="block px-4 py-5 sm:px-6">
                                        <h3 class="text-xs font-bold uppercase text-nowrap leading-6 text-gray-800">
                                            {{ team.team_name }}
                                        </h3>
                                        <span class="inline-flex items-center text-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ team.conference_name }}
                                        </span>
                                    </div>
                                    <div class="border-t border-gray-200">
                                        <div class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-xs font-medium text-gray-500 capitalize text-nowrap flex w-1/2">
                                                Appearance.
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 flex justify-end items-center font-bold w-1/2">
                                               {{ team.playoff_appearances }}
                                            </dd>
                                        </div>
                                        <!-- Additional details can be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <div class="bg-white overflow-hidden shadow-sm rounded min-h-full p-3">
                    <h3 class="text-md font-semibold text-gray-800">Top Rivals</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5" v-if="rivals">
                            <div v-for="game in rivals.data" :key="game.id" class="col-span-1">
                                <div class="bg-white shadow-md rounded-md overflow-hidden">
                                    <div class="border-t border-gray-200">
                                        <div class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-xs font-medium text-gray-500 capitalize text-nowrap flex w-1/2">
                                                {{ game.team1 }}
                                            </dt>
                                            <dd :class="game.wins_team1 > game.wins_team2 ? 'font-bold' : ''" class="mt-1 text-sm text-gray-900 sm:col-span-2 flex justify-end items-center font-bold w-1/2">
                                                {{ game.wins_team1 }}
                                                <!-- <span v-if="game.wins_team1 > game.wins_team2" class="ml-2 text-yellow-500">
                                                    <i class="fas fa-medal"></i>
                                                </span> -->
                                            </dd>
                                        </div>
                                        <div class="bg-gray-200 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-xs font-medium text-gray-500 flex capitalize text-nowrap w-1/2">
                                                {{ game.team2 }}
                                            </dt>
                                            <dd :class="game.wins_team2 > game.wins_team1 ? 'font-bold' : ''" class="mt-1 text-sm text-gray-900 sm:col-span-2 flex justify-end items-center font-bold w-1/2">
                                                {{ game.wins_team2 }}
                                                <!-- <span v-if="game.wins_team2 > game.wins_team1" class="ml-2 text-yellow-500">
                                                    <i class="fas fa-medal"></i>
                                                </span> -->
                                            </dd>
                                        </div>
                                        <!-- Additional details can be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6 mt-4">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import { roundNameFormatter,generateRandomKey, moneyFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";

const champions = ref([]);
const scorers = ref([]);
const top_scorers = ref([]);
const recent_results = ref([]);
const rivals = ref([]);
const playoffs = ref([]);
const change_key = ref(localStorage.getItem('chanpions-key'));
const search_champions = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});
const search_topscorers = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});
const search_scorers = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});

const fetchChampions = async (page = 1) => {
    try {
        const response = await axios.post(route("dashboard.champions"),search_champions.value);
        champions.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const handleChampionsPagination = (page_num) => {
    search_champions.value.page_num = page_num;
    fetchChampions();
};
const fetchTopScorers = async (page = 1) => {
    try {
        const response = await axios.post(route("dashboard.team.topscorer"),search_topscorers.value);
        top_scorers.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const handleTopScorerPagination = (page_num) => {
    search_topscorers.value.page_num = page_num;
    fetchTopScorers();
};
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
const fetchMostPlayoffAppearance = async () => {
    try {
        const response = await axios.post(route("dashboard.playoff.appearances"));
        playoffs.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};
const fetchRecentResults = async () => {
    try {
        const response = await axios.post(route("dashboard.recent"));
        recent_results.value = response.data;
} catch (error) {
        console.error("Error fetching recent results:", error);
    }
};
const fetchRivalry = async () => {
    try {
        const response = await axios.post(route("dashboard.rivalries"));
        rivals.value = response.data;
} catch (error) {
        console.error("Error fetching recent results:", error);
    }
};
const setKey = () => {
    localStorage.getItem('champions-key') ? false : localStorage.setItem('champions-key',generateRandomKey());
}
onMounted(()=>{
    fetchChampions();
    fetchTopScorers();
    fetchMostPlayoffAppearance();
    fetchRecentResults();
    fetchRivalry();
    fetchScorers();
    setKey();
});
</script>
