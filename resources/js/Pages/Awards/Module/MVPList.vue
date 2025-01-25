<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-3 overflow-auto shadow">
        <div v-for="player in data" :key="player.player_id" class="relative max-w-xs bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Player Image (Avatar) -->
            <div class="flex flex-col items-center p-4">
                <div class="relative">
                    <!-- Player Image as Avatar -->
                    <img :src="player.player_image_url || 'default-avatar.png'" alt="Player Image" class="w-24 h-24 rounded-full mb-2 border-4 border-gray-300">
                    
                    <!-- FA Icon (Current Team) on top right corner -->
                    <div class="absolute top-0 right-0 p-2 bg-lime-600 text-white rounded-full text-xs">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold">{{ player.player_name }}</h3>
            </div>

            <!-- Finals MVP Team (comma separated) -->
            <div class="p-4 text-center">
                <p class="text-gray-600">Finals MVP Teams:</p>
                <p class="text-sm text-gray-700">
                    <i class="fa fa-trophy"></i> {{ player.mvp_winning_team_names }}
                </p>
            </div>
            
            <!-- Current Team -->
            <div class="absolute top-2 right-2 bg-lime-600 text-white text-sm px-3 py-1 rounded-full hover:bg-lime-700 transition">
                <p v-if="player.current_team_names" class="cursor-pointer">
                    <i class="fa fa-users"></i> {{ player.current_team_names }}
                </p>
                <p v-else class="cursor-pointer bg-red-600 text-white p-1 rounded-full">
                    <i class="fa fa-user-slash"></i> Free Agent
                </p>
            </div>
            
            <!-- Stats Overlay on Hover -->
            <div class="stats-overlay hidden absolute inset-0 bg-black bg-opacity-50 flex justify-center items-center">
                <div class="text-white text-center px-6 py-4 grid grid-cols-3 gap-4">
                    <div>
                        <p><i class="fa fa-gamepad"></i> Total Games: {{ player.total_games }}</p>
                        <p><i class="fa fa-basketball-ball"></i> Avg Points: {{ player.avg_points_per_game }}</p>
                    </div>
                    <div>
                        <p><i class="fa fa-users"></i> Avg Assists: {{ player.avg_assists_per_game }}</p>
                        <p><i class="fa fa-futbol"></i> Avg Rebounds: {{ player.avg_rebounds_per_game }}</p>
                    </div>
                    <div>
                        <p><i class="fa fa-hand-paper"></i> Avg Steals: {{ player.avg_steals_per_game }}</p>
                        <p><i class="fa fa-shield-alt"></i> Avg Blocks: {{ player.avg_blocks_per_game }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';

const data = ref([]); // Store MVP player data

const fetchMVPLists = async () => {
    try {
        const response = await axios.get(route("awards.mvp.status"));
        data.value = response.data; // Store fetched MVP data
    } catch (error) {
        console.error("Error fetching MVP data:", error);
    }
};

onMounted(() => {
    fetchMVPLists(); // Fetch MVP list data on component mount
});
</script>

<style scoped>
/* Add custom styles for hover effect */
.stats-overlay {
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.relative:hover .stats-overlay {
    opacity: 1;
}
</style>
