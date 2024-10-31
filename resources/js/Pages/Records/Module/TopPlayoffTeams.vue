<template>
    <div class="bg-white overflow-hidden shadow-sm rounded min-h-full p-3">
        <h3 class="text-md font-semibold text-gray-800">Top 16 Playoff Teams (Most Playoff Games)</h3>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4" v-if="playoffs">
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
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import { roundNameFormatter,generateRandomKey, moneyFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";

const playoffs = ref([]);

const fetchMostPlayoffAppearance = async () => {
    try {
        const response = await axios.post(route("records.playoff.appearances"));
        playoffs.value = response.data;
} catch (error) {
        console.error("Error fetching champions:", error);
    }
};

onMounted(()=>{
    fetchMostPlayoffAppearance();
});
</script>
