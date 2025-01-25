<template>
    <Head title="Awards" />

    <AuthenticatedLayout>
        <template #header>
            Awards
        </template>

        <div class="inline-block min-w-full bg-white overflow-hidden shadow p-2 rounded">
            <div class="grid grid-cols-1 gap-6">
                <PlayerAwards />
                <MVPList />
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
import PlayerAwards from './Module/PlayerAwards.vue';
import MVPList from './Module/MVPList.vue';
</script>

