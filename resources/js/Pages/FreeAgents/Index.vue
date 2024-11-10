<template>
    <Head title="Free Agents" />

    <AuthenticatedLayout>
        <template #header>
            Free Agents
        </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3">
            <div class="grid grid-cols-1 gap-6">
                <FreeAgents :showControls="false" />
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import FreeAgents from '../Seasons/Module/FreeAgents.vue';
</script>
