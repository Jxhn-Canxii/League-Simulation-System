<template>
    <div class="grid gap-6 mb-8 md:grid-cols-1 xl:grid-cols-3 overflow-auto p-2">
        <div v-for="player in data" :key="player.player_id" class="relative max-w-xs bg-white rounded-lg shadow-lg overflow-hidden" @click="toggleView(player.player_id)">
            
            <!-- Player Image (Avatar) -->
            <div class="flex flex-col items-center p-4">
                <div class="relative">
                    <!-- Player Image as Avatar -->
                    <img :src="'/image/profile.png'" alt="Player Image" class="w-24 h-24 rounded-full mb-2 border-4 border-gray-300">
                </div>
                <h3 class="text-xl font-semibold">{{ player.player_name }}</h3>
            </div>
            
            <div class="flex justify-center items-center">
                <div class="text-white text-sm px-3 py-1 inline-flex justify-center text-center rounded-full transition">
                    <p v-if="player.current_team_names" class="cursor-pointer bg-lime-500 px-4 py-1 rounded-full">
                        <i class="fa fa-users"></i> {{ player.current_team_names }}
                    </p>
                    <p v-else class="cursor-pointer bg-red-600 text-white rounded-full px-4 py-1">
                        <i class="fa fa-user-slash"></i> Free Agent
                    </p>
                </div>
            </div> 
            <div class="flex justify-center items-center">
                <div class="text-white text-sm px-3 py-1 inline-flex justify-center text-center rounded-full transition">
                    <p class="cursor-pointer bg-gray-600 text-white rounded-full px-2 py-1">
                        {{ player.player_role }}
                    </p>
                </div>
            </div>
         
            <!-- Finals MVP Team (comma separated) -->
            <div class="p-4 text-center">
                <p class="text-gray-600">Finals MVP Teams:</p>
                <p class="text-sm text-gray-700">
                    <i class="fa fa-trophy"></i> {{ player.mvp_winning_team_names }}
                </p>
            </div>
            
            <!-- Overlay for Stats or Awards or None -->
            <div v-show="player.viewMode !== 'none'" class="absolute inset-0 bg-black bg-opacity-50 flex justify-center items-center opacity-100 transition-opacity duration-300 ease-in-out">
                <div class="text-white text-center px-6 py-4">
                    <!-- Stats View -->
                    <ul v-if="player.viewMode === 'stats'" class="list-none">
                        <li class="flex items-center mb-4">
                            <i class="fa fa-gamepad mr-2"></i>
                            <span>Total Games: {{ player.total_games }}</span>
                        </li>
                        <li class="flex items-center mb-4">
                            <i class="fa fa-basketball-ball mr-2"></i>
                            <span>Avg Points: {{ player.avg_points_per_game }}</span>
                        </li>
                        <li class="flex items-center mb-4">
                            <i class="fa fa-users mr-2"></i>
                            <span>Avg Assists: {{ player.avg_assists_per_game }}</span>
                        </li>
                        <li class="flex items-center mb-4">
                            <i class="fa fa-futbol mr-2"></i>
                            <span>Avg Rebounds: {{ player.avg_rebounds_per_game }}</span>
                        </li>
                        <li class="flex items-center mb-4">
                            <i class="fa fa-hand-paper mr-2"></i>
                            <span>Avg Steals: {{ player.avg_steals_per_game }}</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fa fa-shield-alt mr-2"></i>
                            <span>Avg Blocks: {{ player.avg_blocks_per_game }}</span>
                        </li>
                        <!-- <li v-for="award in player.awards_won" :key="award" class="flex items-center mb-4">
                            <i class="fa fa-trophy mr-2"></i>
                            <span>{{ award }}</span>
                        </li> -->
                    </ul>
                    
                    <!-- Awards View -->
                    <ul v-if="player.viewMode === 'awards'" class="list-none">
                        <li class="flex items-center mb-4">
                            <i class="fa fa-trophy mr-2"></i>
                            <span>{{ player.awards_won }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const data = ref([]); // Store MVP player data

// Fetch MVP data and add 'viewMode' and 'awards_won' property to each player object
const fetchMVPLists = async () => {
    try {
        const response = await axios.get(route("awards.mvp.status"));
        const players = response.data.map(player => ({
            ...player,
            showStats: false,
            viewMode: 'none',  // Track view mode (none, stats, awards)
            awards_won: player.awards_won || [] // Assuming awards_won is part of the response
        }));
        data.value = players;
    } catch (error) {
        console.error("Error fetching MVP data:", error);
    }
};

// Toggle the view mode between 'none', 'stats', and 'awards'
const toggleView = (playerId) => {
    const player = data.value.find(p => p.player_id === playerId);
    if (player) {
        if (player.viewMode === 'none') {
            player.viewMode = 'stats';  // First click: show stats
        } else if (player.viewMode === 'stats') {
            player.viewMode = 'awards';  // Second click: show awards
        } else if (player.viewMode === 'awards') {
            player.viewMode = 'none';  // Third click: hide overlay
        }
    }
};

onMounted(() => {
    fetchMVPLists(); // Fetch MVP list data on component mount
});
</script>

<style scoped>
/* Ensures the overlay covers the entire card */
.relative {
    position: relative;
}

.viewMode-overlay {
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

/* The list items inside the stats overlay */
.viewMode-overlay ul {
    padding: 0;
}

.viewMode-overlay li {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.viewMode-overlay li i {
    margin-right: 10px; /* Space between icon and text */
}

.viewMode-overlay li span {
    display: inline-block;
    text-align: left;
}
</style>
