<template>
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
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import { roundNameFormatter,generateRandomKey, moneyFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";


const recent_results = ref([]);


const fetchRecentResults = async () => {
    try {
        const response = await axios.post(route("dashboard.recent"));
        recent_results.value = response.data;
} catch (error) {
        console.error("Error fetching recent results:", error);
    }
};
onMounted(()=>{
    fetchRecentResults();
});
</script>
