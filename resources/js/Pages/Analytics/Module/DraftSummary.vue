<template>
    <div class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-10 xl:grid-cols-10 gap-6">

        <!-- Draft Player Statistics Cards -->
        <div
            v-for="(item, index) in data"
            :key="index"
            class="relative flex flex-col items-center border border-gray-300 p-4 bg-white rounded-lg shadow-lg group hover:shadow-xl transition-shadow duration-300"
        >
            <!-- Draft ID -->
            <div class="text-xl absolute left-2 top-0 font-semibold text-black">
                Draft
            </div>
            <div class="text-3xl absolute right-2 top-0 font-semibold text-black">
                {{ item.draft_id }}
            </div>

            <!-- Show Active Players by Default -->
            <div class="text-sm text-gray-400 mt-4 text-nowrap text-left" v-if="item.active_players_with_team > 0">
                <span class="font-bold">Active: </span>{{ item.active_players_with_team }}
            </div>
            <div class="text-sm text-red-400 text-nowrap text-left" v-if="item.active_players_with_team > 0">
                <span class="font-bold">Retired: </span>{{ item.inactive_players }}
            </div>
            <!-- Full Data on Hover -->
            <div
                class="absolute inset-0 bg-black bg-opacity-80 text-white p-6 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                v-if="item.active_players_with_team > 0"
            >
                <span class="font-bold">{{ item.active_percentage }}% </span>Active
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported

const data = ref([]);

// Fetch the draft statistics data from the backend
const fetchGameRecords = async () => {
    try {
        // Adjust the API call to fetch data based on the SQL query
        const response = await axios.get(route("draft.statistics"));  // Adjust to correct route
        data.value = response.data;  // Update the data ref with the response data
    } catch (error) {
        console.error("Error fetching draft statistics:", error);
    }
};

onMounted(() => {
    fetchGameRecords();
});
</script>

<style scoped>
/* Add custom styles for the grid and hover effect */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

/* Ensure the cards have proper spacing and hover effect */
.group {
    position: relative;
}

/* Card Styles */
.group {
    transition: transform 0.3s ease-in-out;
}

.group:hover {
    transform: scale(1.05); /* Add zoom effect */
}

/* Styles for small text */
.text-xs {
    font-size: 0.75rem; /* Smaller text for stats */
}

/* Hover effect for showing full data */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}
</style>
