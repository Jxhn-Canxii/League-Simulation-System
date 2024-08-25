<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="inline-block min-w-full bg-white overflow-hidden shadow p-2 rounded">
            <div class="grid grid-cols-2 gap-6">
                <Championships />
                <PlayerScoringRankings />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <Last12Games />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <TopPlayoffTeams />
                <Rivals />
            </div>
            <div class="grid grid-cols-2 gap-6 mt-4">
                <TeamWinningRankings />
                <TeamScoringRankings />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <PlayerPlayoffAppearance />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import { roundNameFormatter,generateRandomKey, moneyFormatter } from "@/Utility/Formatter";
import Paginator from "@/Components/Paginator.vue";
import TeamScoringRankings from './Module/TeamScoringRankings.vue';
import Championships from './Module/Championships.vue';
import Last12Games from './Module/Last12Games.vue';
import PlayerScoringRankings from './Module/PlayerScoringRankings.vue';
import Rivals from './Module/Rivals.vue';
import TopPlayoffTeams from './Module/TopPlayoffTeams.vue';
import TeamWinningRankings from './Module/TeamWinningRankings.vue';
import PlayerPlayoffAppearance from './Module/PlayerPlayoffAppearance.vue';
</script>
