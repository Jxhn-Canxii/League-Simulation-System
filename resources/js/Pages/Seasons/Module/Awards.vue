<template>
    <div class="team-roster">
        <h2 class="text-xl font-semibold text-gray-800">Season Awards</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Update Button -->
        <div class="mb-4 flex justify-center" v-if="!awards.length">
            <button
                @click="updatePlayerStatus"
                class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Update Player Status
            </button>
        </div>

        <!-- Awards Table -->
        <div class="overflow-x-auto" v-if="awards.length">
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
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios'; // Ensure axios is imported
import Swal from 'sweetalert2';

const awards = ref([]);
const props = defineProps({
    team_ids: Array,
});

const updatePlayerStatus = async () => {
    try {
        const team_ids = props.team_ids;

        for (let i = 0; i < team_ids.length; i++) {
            const team_id = team_ids[i];
            const is_last = i === team_ids.length - 1;

            // Update player status for each team and get the response
            await updatePlayerStatusPerTeam(i, team_id,team_ids.length);
            if (is_last) {
                await showSeasonAwards();
            }
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to update player status for some or all teams. Please try again later.',
        });
    }
};

const updatePlayerStatusPerTeam = async (index, team_id, team_count) => {
    try {
        // const total_teams = team_count + 1;

        // Show the initial Swal with a progress bar
        Swal.fire({
            title: 'Preparing Season Awards',
            html: `<div id="progress-container">
                    <p>Processing team ${team_id}/${team_count}</p>
                    <div class="progress">
                        <div id="progress-bar" class="progress-bar" role="progressbar" style="width: ${((index / total_teams) * 100)}%;" aria-valuenow="${index}" aria-valuemin="0" aria-valuemax="${total_teams}"></div>
                    </div>
                   </div>`,
            showConfirmButton: true,
            allowOutsideClick: false,
            position: 'top',
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Make the request
        const response = await axios.post(route('store.player.stats'), { team_id: team_id });

        // Update progress bar after response
        const progressPercentage = ((index / team_count) * 100);
        document.getElementById('progress-bar').style.width = `${progressPercentage}%`;

        // After completion of all teams, show a success message
        if (index === team_count) {
            Swal.fire({
                title: 'Success!',
                text: 'All player stats updated successfully.',
                icon: 'success',
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to update player stats. Please try again later.',
        });
    }
};


const showSeasonAwards = async () => {
    try {
        const response = await axios.post(route('player.awards'));
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
    // Initialize if needed
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

/* Progress bar styling */
.progress {
    width: 100%;
    background-color: #f3f3f3;
    border-radius: 5px;
    height: 20px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background-color: #4caf50;
    text-align: center;
    color: white;
    line-height: 20px;
    transition: width 0.5s;
}

</style>
