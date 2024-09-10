<template>
    <div class="team-roster">
        <h2 class="text-xl font-semibold text-gray-800">Season Awards</h2>
        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <!-- Awards Table -->
        <div class="overflow-x-auto" v-if="awards">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Award Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Award Description</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="award in awards" :key="award.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ award.award_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ award.player_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ award.team_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ award.award_description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto flex justify-center" v-else>
            <p class="font-bold text-red-500">No awards given please wait for the end season.</p>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios'; // Ensure axios is imported
import Swal from 'sweetalert2';

const awards = ref([]);
const props = defineProps({
    season_id: Array,
});

const showSeasonAwards = async () => {
    try {
        const response = await axios.post(route('player.season.awards'),{season_id: props.season_id});
        awards.value = response.data.awards;
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to fetch season awards. Please try again later.',
        });
    }
};

onMounted(() => {
    showSeasonAwards();
});
</script>
<style scoped>
.table {
    font-size: 0.75rem; /* Smaller text size */
}

.table th,
.table td {
    padding: 0.5rem; /* Smaller padding */
}
</style>
