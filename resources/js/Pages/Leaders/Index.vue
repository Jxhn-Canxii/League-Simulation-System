<template>
    <Head title="Leaders" />

    <AuthenticatedLayout>
        <template #header>
            Stats Leaders
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="flex">
                <button
                    @click="reloadData()"
                    class="px-4 py-2 bg-rose-500 text-white rounded mb-4 text-sm"
                >
                    <i class="fa fa-reload"></i> Reload Data
                </button>
            </div>
            <div class="grid grid-cols-5 gap-6">
                <!-- Points Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Points Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in average.topPoints" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_points_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Assists Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Assists Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in average.topAssists" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_assists_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                 <!-- Rebounds Table -->
                 <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Rebounds Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in average.topRebounds" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_rebounds_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Steals Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Steals Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in average.topSteals" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_steals_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Blocks Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Blocks Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in average.topBlocks" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_blocks_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Points Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Total Points Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in total.topTotalPoints" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name ?? 'Free Agent' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.total_points}}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Assists Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Total Assists Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in total.topTotalAssists" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name ?? 'Free Agent' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.total_assists }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                 <!-- Rebounds Table -->
                 <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Total Rebounds Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in total.topTotalRebounds" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name ?? 'Free Agent' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.total_rebounds }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Steals Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Total Steals Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in total.topTotalSteals" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name ?? 'Free Agent' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.total_steals }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Blocks Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Average Blocks Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in total.topTotalBlocks" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name ?? 'Free Agent' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.total_blocks }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                  <!-- Points Table -->
                  <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Single Points Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in single.topSinglePoints" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }}</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_points_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Assists Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Single Assists Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in single.topSingleAssists" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_assists_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                 <!-- Rebounds Table -->
                 <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Single Rebounds Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in single.topSingleRebounds" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_rebounds_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Steals Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Single Steals Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in single.topSingleSteals" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_steals_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Blocks Table -->
                <div class="bg-white inline-block min-w-full overflow-hidden rounded shadow p-2 mt-4">
                    <h3 class="text-md font-semibold text-gray-800">Single Blocks Leaders</h3>
                    <div class="mt-4 text-xs">
                        <ul>
                            <li v-for="(player, index) in single.topSingleBlocks" :key="player.player_id">
                                <div :class="{'bg-yellow-100': index < 3}" class="flex items-center justify-between py-1 px-4 rounded shadow-sm">
                                    <!-- Left side: Name, Team, and Season -->
                                    <div class="flex flex-col items-start space-y-1">
                                        <div class="flex items-center space-x-3">
                                            <span :class="{'text-md font-bold text-yellow-600': index < 3, 'text-md': index >= 3}" class="text-gray-800">{{ index + 1 }}.</span>
                                            <span :class="{'font-semibold text-yellow-600': index < 3, 'text-gray-800': index >= 3}" class="text-gray-800">{{ player.player_name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span>{{ player.team_name }} (Season {{ player.season_id }})</span>
                                        </div>
                                    </div>
                                    <!-- Right side: Points in a circle -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold text-md p-2">
                                        {{ player.avg_blocks_per_game }}
                                    </div>
                                </div>
                            </li>
                        </ul>
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
import axios from 'axios'; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";

import PlayerPerformance from '../Teams/Module/PlayerPerformance.vue';

const average = ref([]);
const total = ref([]);
const single = ref([]);

// Cache key definitions
const CACHE_KEY_AVERAGE = 'average_stat_leaders';
const CACHE_KEY_TOTAL = 'total_stat_leaders';
const CACHE_KEY_SINGLE = 'single_stat_leaders';

const reloadData = async () => {
    // Clears all data from localStorage
    localStorage.clear();
    await fetchStatLeaders("average.stats.leaders", CACHE_KEY_AVERAGE, average);
    await fetchStatLeaders("total.stats.leaders", CACHE_KEY_TOTAL, total);
    await fetchStatLeaders("single.stats.leaders", CACHE_KEY_SINGLE, single);

    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "All Data has been Cleared",
    });
}
// Function to fetch and cache data
const fetchStatLeaders = async (endpoint, cacheKey, refVariable) => {
    // Check if cached data exists in localStorage
    const cachedData = localStorage.getItem(cacheKey);
    if (cachedData) {
        // Parse and use cached data
        refVariable.value = JSON.parse(cachedData);
        console.log('Loaded from cache:', cacheKey);
    } else {
        // Fetch from API if no cached data
        try {
            const response = await axios.get(route(endpoint));
            refVariable.value = response.data;
            // Cache the data for future use
            localStorage.setItem(cacheKey, JSON.stringify(response.data));
            console.log('Fetched from API and cached:', cacheKey);
        } catch (error) {
            console.error(`Error fetching ${cacheKey}:`, error);
        }
    }
};

// Fetch all stats on mounted
onMounted(() => {
    fetchStatLeaders("average.stats.leaders", CACHE_KEY_AVERAGE, average);
    fetchStatLeaders("total.stats.leaders", CACHE_KEY_TOTAL, total);
    fetchStatLeaders("single.stats.leaders", CACHE_KEY_SINGLE, single);
});
</script>

