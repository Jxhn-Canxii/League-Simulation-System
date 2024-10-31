<template>
    <header class="flex items-center justify-between border-b-4 border-rose-600 bg-stone-900 px-6 py-1">
        <div class="flex items-center">
            <button @click="$page.props.showingMobileMenu = !$page.props.showingMobileMenu" class="text-gray-500 focus:outline-none lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="flex justify-center items-center">
            <div class="flex items-center">
                <i class="fa fa-2x fa-basketball animate-spin duration-75 hover:stroke-purple-400 text-rose-500"></i>
                <span class="mx-2 text-2xl font-semibold text-red-600">LIGA PILIPINAS</span>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <nav class="flex flex-row space-x-1"> <!-- Flex row to align links horizontally, with space between items -->
                <nav-link :href="route('analytics.index')" :active="route().current('analytics.index')">
                    <template #icon>
                        <i class="fa fa-chart-line"></i>
                    </template>
                    Analytics
                </nav-link>
                <nav-link :href="route('seasons.index')" :active="route().current('seasons.index')">
                    <template #icon>
                        <i class="fa fa-calendar"></i>
                    </template>
                    Seasons
                </nav-link>
                <nav-link :href="route('records.index')" :active="route().current('records.index')">
                    <template #icon>
                        <i class="fa fa-book"></i>
                    </template>
                    Records
                </nav-link>
                <nav-link :href="route('awards.index')" :active="route().current('awards.index')">
                    <template #icon>
                        <i class="fa fa-medal"></i>
                    </template>
                    Awards
                </nav-link>
                <nav-link :href="route('players.index')" :active="route().current('players.index')">
                    <template #icon>
                        <i class="fa fa-users"></i>
                    </template>
                    Players
                </nav-link>
                <nav-link :href="route('teams.index')" :active="route().current('teams.index')">
                    <template #icon>
                        <i class="fa fa-basketball"></i>
                    </template>
                    Teams
                </nav-link>
                <nav-link :href="route('leagues.index')" :active="route().current('leagues.index')">
                    <template #icon>
                        <i class="fa fa-list"></i>
                    </template>
                    Leagues
                </nav-link>
                <nav-link :href="route('users.index')" :active="route().current('users.index')">
                    <template #icon>
                        <i class="fa fa-users"></i>
                    </template>
                    Users
                </nav-link>
            </nav>
        </div>
        <div class="flex items-center">
            <dropdown-link class="w-full text-left" :href="route('logout')" method="post" as="button">
                <i class="fa fa-power-off mt-4 fa-xl text-red-500"></i>
            </dropdown-link>
        </div>
    </header>
</template>

<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
</script>
