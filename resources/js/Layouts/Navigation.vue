<template>
    <div :class="$page.props.showingMobileMenu ? 'block' : 'hidden'" @click="$page.props.showingMobileMenu = false"
         class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden"></div>

    <div :class="$page.props.showingMobileMenu ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
         class="overflow-y-auto fixed inset-y-0 left-0 z-30 w-64 bg-rose-900 transition duration-300 transform lg:translate-x-0 lg:static lg:inset-0">
        <div class="flex justify-center items-center mt-8">
            <div class="flex items-center">
                <i class="fa fa-2x fa-basketball text-rose-500"></i>
                <span class="mx-2 text-2xl font-semibold text-white">LIGA PILIPINAS</span>
            </div>
        </div>

        <nav class="mt-10" x-data="{ isMultiLevelMenuOpen: false }">
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
            <nav-link :href="route('analytics.index')" :active="route().current('analytics.index')">
                <template #icon>
                    <i class="fa fa-chart-line"></i>
                </template>
                Analytics
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

</template>

<script>
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue'

export default {
    components: {
        NavLink,
        Link,
    },

    setup() {
        let showingTwoLevelMenu = ref(false)

        return {
            showingTwoLevelMenu
        }
    },
}
</script>

<style scoped>

</style>
