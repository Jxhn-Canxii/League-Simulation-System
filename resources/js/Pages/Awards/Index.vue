<template>
    <Head title="Awards" />

    <AuthenticatedLayout>
        <template #header>
            Awards
        </template>

        <div class="inline-block min-w-full bg-white overflow-hidden shadow p-2 rounded">
            <div class="flex mb-4">
                <!-- Tabs navigation -->
                <button
                    :class="['rounded-l', activeTab === 'mvpList' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700']"
                    @click="activeTab = 'mvpList'">
                    MVP List
                </button>
                <button
                    :class="['rounded-r', activeTab === 'playerAwards' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700']"
                    @click="activeTab = 'playerAwards'">
                    Player Awards
                </button>
            </div>

            <!-- Tab Content -->
            <div v-if="activeTab === 'mvpList'">
                <MVPList />
            </div>
            <div v-if="activeTab === 'playerAwards'">
                <PlayerAwards />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from "vue";
import PlayerAwards from './Module/PlayerAwards.vue';
import MVPList from './Module/MVPList.vue';

const activeTab = ref('mvpList');  // Default to MVP List tab
</script>

<style scoped>
button {
    padding: 10px 20px;
    font-weight: bold;
    border-radius: 4px;
}

button:hover {
    cursor: pointer;
}
</style>
