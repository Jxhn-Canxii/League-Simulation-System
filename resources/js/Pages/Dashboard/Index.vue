<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="grid grid-cols-2 gap-6">
                    <TeamScoringRankings />
                    <Championships />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <Last12Games />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <TopPlayoffTeams />
            </div>
            <div class="grid grid-cols-1 gap-6 mt-4">
               <Rivals />
            </div>
            <div class="grid grid-cols-2 gap-6 mt-4">
                <PlayerScoringRankings />
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
import TeamScoringRankings from './TeamScoringRankings.vue';
import Championships from './Championships.vue';
import Last12Games from './Last12Games.vue';
import PlayerScoringRankings from './PlayerScoringRankings.vue';
import Rivals from './Rivals.vue';
import TopPlayoffTeams from './TopPlayoffTeams.vue';
</script>
