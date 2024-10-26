<template>
    <div class="team-roster">
        <h2 class="text-xl font-semibold text-gray-800">Top 15 Players</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <!-- Players Table -->
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">GP</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Points Per Game">PPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rebounds Per Game">RPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Assist Per Game">APG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Steals Per Game">SPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Blocks Per Game">BPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Turnover Per Game">TOPG</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Fouls Per Game">FPG</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="player in top_players.best_players" :key="player.player_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.games_played }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_points_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_rebounds_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_assists_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_steals_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_blocks_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_turnovers_per_game.toFixed(1) }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ player.average_fouls_per_game.toFixed(1) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MVP Candidates Section -->
        <h2 class="text-lg font-semibold text-gray-800">Top 5 MVP Candidates</h2>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="MVP Score">MVP Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="mvpCandidate in top_players.mvp_leaders" :key="mvpCandidate.player_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">{{ mvpCandidate.player_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ mvpCandidate.team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ mvpCandidate.mvp_score.toFixed(1) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Rookie Candidates Section -->
        <h2 class="text-lg font-semibold text-gray-800">Top 5 Rookie Candidates</h2>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider" title="Rookie Score">Rookie Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="rookieCandidate in top_players.rookie_leaders" :key="rookieCandidate.player_id" class="hover:bg-gray-100">
                        <td class="px-2 py-1 whitespace-nowrap border">{{ rookieCandidate.player_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ rookieCandidate.team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ rookieCandidate.rookie_score.toFixed(1) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
const props = defineProps({
    season_id: {
        type: Number,
        required: true,
    },
    conference_id: {
        type: Number,
        required: true,
    },
    key: {
        type: Number,
        required: true,
    },
    round: {
        type: Number,
        required: true,
        default: 0,
    },
});
const top_players = ref({
    best_players: [],
    mvp_candidates: [],
    rookie_candidates: []
});

watch(
    () => [props.key, props.conference_id],
    async ([newKey, newConferenceId], [oldKey, oldConferenceId]) => {
        if (newKey !== oldKey || newConferenceId !== oldConferenceId) {
            await fetchTopPlayers(props.season_id, props.conference_id, props.round);
        }
    }
);

onMounted(() => {
    fetchTopPlayers(props.season_id, props.conference_id, props.round);
});

const fetchTopPlayers = async (season_id, conference_id, round) => {
    try {
        const response = await axios.post(route("top.players.conference.season"), {
            season_id,
            conference_id,
            round,
        });
        top_players.value = response.data;
    } catch (error) {
        console.error("Error fetching top players:", error);
    }
};
</script>

<style scoped>
.table {
    font-size: 0.75rem;
}

.table th,
.table td {
    padding: 0.5rem;
}
</style>
