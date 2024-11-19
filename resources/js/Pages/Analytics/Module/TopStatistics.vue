<template>
    <div class="grid md:grid-cols-6 grid-cols-1 gap-6">
        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                <i class="fa fa-users"></i> <!-- Total Players -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Players</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.total_players ?? 0) }}</p>
            </div>
        </div>

        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                <i class="fa fa-star"></i> <!-- Total Rookies -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Rookies</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.rookie_players ?? 0) }}</p>
            </div>
        </div>

        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-red-500 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-500">
                <i class="fa fa-user-slash"></i> <!-- Total Retired -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Retired</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.retired_players ?? 0) }}</p>
            </div>
        </div>
        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                <i class="fa fa-chess"></i> <!-- Total Free Agents -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Active Players</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.active_players_with_team ?? 0) }}</p>
            </div>
        </div>
        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-red-100 rounded-full dark:text-red-100 dark:bg-green-500">
                <i class="fa fa-check"></i> <!-- Total Retired -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Available Slots</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.total_available_slots ?? 0) }}</p>
            </div>
        </div>
        <div class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                <i class="fa fa-user-circle"></i> <!-- Total Free Agents -->
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Free Agents</p>
                <p class="text-lg font-semibold text-black">{{ moneyFormatter(data.free_agents ?? 0) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";
import { moneyFormatter } from "@/Utility/Formatter";

const data = ref([]);
const fetchPlayerCount = async (page = 1) => {
    try {
        const response = await axios.get(route("analytics.player.count"));
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching player count:", error);
    }
};
onMounted(() => {
    fetchPlayerCount();
});
</script>
