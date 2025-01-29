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
            <div class="text-3xl block font-semibold mt-7 text-black">
                <div class="flex items-center text-sm text-green-700 mb-2">
                    <i class="fas fa-users me-2"></i>
                    <span>{{ item.active_players_with_team }}</span>
                </div>
                <!-- Total Active Players -->
                <div class="flex items-center text-sm text-blue-700 mb-2">
                    <i class="fas fa-user-check me-2"></i>
                    <span>{{ item.active_players }}</span>
                </div>
                <!-- Total Retired Players -->
                <div class="flex items-center text-sm text-red-700 mb-2">
                    <i class="fas fa-user-times me-2"></i>
                    <span>{{ item.total_players - item.active_players }}</span>
                </div>
            </div>
           
            <!-- Full Data on Hover -->
            <div
                class="absolute inset-0 bg-black bg-opacity-80 space-y-3 text-white p-4 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                v-if="item.active_players_with_team > 0"
            >
                <div class="text-sm font-bold">Percentage</div>
                <div class="flex items-center text-sm" title="Active Players with Team">
                    <i class="fas fa-users me-2"></i>
                    <span>{{ item.active_percentage_with_team }}%</span>
                </div>
                <div class="flex items-center text-sm">
                    <i class="fas fa-user-check me-2"></i>
                    <span>{{ item.active_percentage }}%</span>
                </div>
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
