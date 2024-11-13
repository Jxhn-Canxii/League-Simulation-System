<template>
    <header class="flex items-center justify-between border-b-2 border-rose-600 bg-stone-900 px-4 py-2">
        <!-- Mobile menu toggle button -->
        <button @click.prevent="$page.props.showingMobileMenu = !$page.props.showingMobileMenu" class="text-gray-500 focus:outline-none lg:hidden mr-2">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <!-- Logo and title -->
        <div class="flex items-center space-x-1">
            <i class="fa fa-basketball text-rose-500"></i>
            <span class="text-sm font-semibold text-red-600">LIGA PILIPINAS</span>
        </div>

        <!-- Navigation Links with Dropdown Grouping -->
        <nav class="hidden lg:flex text-sm text-nowrap px-0 mx-0">
            <!-- History Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('history')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>History</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showHistoryDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('records.index')" :active="route().current('records.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Records</nav-link>
                    <nav-link :href="route('leaders.index')" :active="route().current('leaders.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Leaders</nav-link>
                    <nav-link :href="route('awards.index')" :active="route().current('awards.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Awards</nav-link>
                </div>
            </div>

            <!-- Players & Free Agents Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('players')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>Players & Free Agents</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showPlayersDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('players.index')" :active="route().current('players.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Players</nav-link>
                    <nav-link :href="route('freeagents.index')" :active="route().current('freeagents.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Free Agents</nav-link>
                </div>
            </div>

            <!-- Teams & Leagues Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('teams')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>Teams & Leagues</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showTeamsDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('teams.index')" :active="route().current('teams.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Teams</nav-link>
                    <nav-link :href="route('leagues.index')" :active="route().current('leagues.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Leagues</nav-link>
                </div>
            </div>

            <!-- Analytics Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('analytics')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>Analytics</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showAnalyticsDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('analytics.index')" :active="route().current('analytics.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Analytics</nav-link>
                </div>
            </div>

            <!-- Seasons Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('seasons')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>Seasons</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showSeasonsDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('seasons.index')" :active="route().current('seasons.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Seasons</nav-link>
                </div>
            </div>

            <!-- Users Dropdown -->
            <div class="relative">
                <button @click.prevent="toggleDropdown('users')" class="text-xs text-gray-300 flex items-center space-x-1 px-2 py-1 rounded-md hover:bg-rose-600 hover:text-white">
                    <span>Users</span>
                    <i class="fa fa-caret-down text-sm"></i>
                </button>
                <div v-if="showUsersDropdown" class="absolute bg-stone-900 border-2 border-rose-600 text-gray-300 rounded-md mt-1 p-2 space-y-1">
                    <nav-link :href="route('users.index')" :active="route().current('users.index')" class="text-xs px-2 py-1 hover:bg-rose-600 hover:text-white">Users</nav-link>
                </div>
            </div>
        </nav>

        <!-- Logout button -->
        <dropdown-link class="ml-4 text-left" :href="route('logout')" method="post" as="button">
            <i class="fa fa-power-off text-red-500"></i>
        </dropdown-link>
    </header>
</template>

<script setup>
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { ref } from 'vue';

// Define dropdown state
const showHistoryDropdown = ref(false);
const showPlayersDropdown = ref(false);
const showTeamsDropdown = ref(false);
const showAnalyticsDropdown = ref(false);
const showSeasonsDropdown = ref(false);
const showUsersDropdown = ref(false);

// Function to close all dropdowns
const closeAllDropdowns = () => {
    showHistoryDropdown.value = false;
    showPlayersDropdown.value = false;
    showTeamsDropdown.value = false;
    showAnalyticsDropdown.value = false;
    showSeasonsDropdown.value = false;
    showUsersDropdown.value = false;
};

// Function to toggle a specific dropdown
const toggleDropdown = (dropdown) => {
    closeAllDropdowns(); // Close all dropdowns first
    switch (dropdown) {
        case 'history':
            showHistoryDropdown.value = !showHistoryDropdown.value;
            break;
        case 'players':
            showPlayersDropdown.value = !showPlayersDropdown.value;
            break;
        case 'teams':
            showTeamsDropdown.value = !showTeamsDropdown.value;
            break;
        case 'analytics':
            showAnalyticsDropdown.value = !showAnalyticsDropdown.value;
            break;
        case 'seasons':
            showSeasonsDropdown.value = !showSeasonsDropdown.value;
            break;
        case 'users':
            showUsersDropdown.value = !showUsersDropdown.value;
            break;
    }
};
</script>
