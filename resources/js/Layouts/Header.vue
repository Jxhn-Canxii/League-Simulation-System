<template>
    <header class="flex items-center justify-between border-b-2 border-rose-600 bg-stone-900 px-4 py-2">
        <!-- Mobile menu toggle button -->
        <button @click="$page.props.showingMobileMenu = !$page.props.showingMobileMenu" class="text-gray-500 focus:outline-none lg:hidden mr-2">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <!-- Logo and title -->
        <div class="flex items-center space-x-1">
            <i class="fa fa-basketball text-rose-500"></i>
            <span class="text-sm font-semibold text-red-600">LIGA PILIPINAS</span>
        </div>

        <!-- Navigation Links -->
        <nav class="hidden lg:flex text-sm text-nowrap px-0 mx-0"> <!-- Reduced space and font size for compactness -->
            <nav-link :href="route('analytics.index')" :active="route().current('analytics.index')">
                <template #icon><i class="fa fa-chart-line"></i></template> Analytics
            </nav-link>
            <nav-link :href="route('seasons.index')" :active="route().current('seasons.index')">
                <template #icon><i class="fa fa-calendar"></i></template> Seasons
            </nav-link>
            <nav-link :href="route('records.index')" :active="route().current('records.index')">
                <template #icon><i class="fa fa-book"></i></template> Records
            </nav-link>
            <nav-link :href="route('leaders.index')" :active="route().current('leaders.index')">
                <template #icon><i class="fa fa-ranking-star"></i></template> Leaders
            </nav-link>
            <nav-link :href="route('awards.index')" :active="route().current('awards.index')">
                <template #icon><i class="fa fa-medal"></i></template> Awards
            </nav-link>
            <nav-link :href="route('players.index')" :active="route().current('players.index')">
                <template #icon><i class="fa fa-users"></i></template> Players
            </nav-link>
            <nav-link :href="route('freeagents.index')" :active="route().current('freeagents.index')">
                <template #icon><i class="fa fa-users-slash"></i></template> Free Agents
            </nav-link>
            <nav-link :href="route('teams.index')" :active="route().current('teams.index')">
                <template #icon><i class="fa fa-basketball"></i></template> Teams
            </nav-link>
            <nav-link :href="route('leagues.index')" :active="route().current('leagues.index')">
                <template #icon><i class="fa fa-list"></i></template> Leagues
            </nav-link>
            <nav-link :href="route('users.index')" :active="route().current('users.index')">
                <template #icon><i class="fa fa-users"></i></template> Users
            </nav-link>
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
import { Link } from '@inertiajs/vue3';
</script>
