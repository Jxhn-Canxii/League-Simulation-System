<template>
    <div class="p-4 bg-gray-900 shadow-md rounded-lg max-w-7xl mx-auto">
        <!-- Game Summary -->
        <div
            class="flex flex-col lg:flex-row justify-between mb-4 border-b-2 border-gray-700 pb-4"
        >
            <div
                class="flex-1 text-center mb-2 lg:mb-0 team-card rounded"
                @click.prevent="
                    isTeamRosterModalOpen = gameDetails?.home_team.team_id
                "
                :style="{
                    backgroundColor: '#' + gameDetails?.home_team.primary_color,
                }"
            >
                <!-- Home team primary color -->
                <h2 class="text-5xl font-bold text-white">
                    {{ gameDetails?.home_team.score }}
                </h2>
                <p
                    class="text-md font-semibold text-white"
                    :style="{
                        backgroundColor:
                            '#' + gameDetails?.home_team.secondary_color,
                    }"
                >
                    {{ gameDetails?.home_team.name }} ({{
                        gameDetails?.home_team.streak
                    }})
                </p>
            </div>

            <div class="flex-1 text-center mb-2 lg:mb-0 text-white">
                <div class="bg-gray-800 p-2 rounded-lg">
                    <p class="text-xs font-semibold text-yellow-500">
                        Liga Dos
                        {{
                            isNaN(gameDetails?.round)
                                ? "Playoffs"
                                : "Regular Season"
                        }}
                    </p>
                    <p class="text-xs font-semibold">
                        Round:
                        {{
                            roundNameFormatter(
                                isNaN(gameDetails?.round)
                                    ? gameDetails?.round
                                    : parseFloat(gameDetails?.round)
                            )
                        }}
                    </p>
                    <p class="text-xs font-semibold">
                        Game ID: {{ gameDetails?.game_id }}
                    </p>
                    <p class="text-xs font-semibold">
                        Matchup Record:
                        {{
                            gameDetails?.head_to_head_record.home_team_wins ?? 0
                        }}
                        -
                        {{
                            gameDetails?.head_to_head_record.away_team_wins ?? 0
                        }}
                    </p>
                </div>
            </div>

            <div
                class="flex-1 text-center mb-2 lg:mb-0 team-card rounded"
                @click.prevent="
                    isTeamRosterModalOpen = gameDetails?.away_team.team_id
                "
                :style="{
                    backgroundColor: '#' + gameDetails?.away_team.primary_color,
                }"
            >
                <!-- Away team primary color -->
                <h2 class="text-5xl font-bold text-white">
                    {{ gameDetails?.away_team.score }}
                </h2>
                <p
                    class="text-md font-semibold text-white"
                    :style="{
                        backgroundColor:
                            '#' + gameDetails?.away_team.secondary_color,
                    }"
                >
                    {{ gameDetails?.away_team.name }} ({{
                        gameDetails?.away_team.streak
                    }})
                </p>
            </div>
        </div>

        <!-- Player Statistics Tables -->
        <div class="mb-4 text-white" v-if="props.showBoxScore">
            <h3 class="text-xl font-semibold mb-2">Player Statistics</h3>

            <!-- Home Team Player Stats -->
            <div
                class="mb-2 p-2 rounded"
                :style="{
                    backgroundColor: '#' + gameDetails?.home_team.primary_color,
                }"
            >
                <h4 class="text-lg font-semibold mb-1">
                    {{ gameDetails?.home_team.name }} Player Stats
                </h4>
                <table
                    class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm"
                >
                    <thead>
                        <tr
                            class="bg-gray-700 text-left"
                            :style="{
                                backgroundColor:
                                    '#' +
                                    gameDetails?.home_team.secondary_color,
                            }"
                        >
                            <th class="py-2 px-3 text-xs">Name</th>
                            <th class="py-2 px-3 text-xs">Role</th>
                            <th class="py-2 px-3 text-xs">Mins</th>
                            <th class="py-2 px-3 text-xs">Pts</th>
                            <th class="py-2 px-3 text-xs">Rbd</th>
                            <th class="py-2 px-3 text-xs">Ast</th>
                            <th class="py-2 px-3 text-xs">Stl</th>
                            <th class="py-2 px-3 text-xs">Blk</th>
                            <th class="py-2 px-3 text-xs">TO</th>
                            <th class="py-2 px-3 text-xs">Fls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="player in sortedHomePlayers"
                            :key="player.name"
                            @click.prevent="showPlayerProfileModal = player"
                            :class="{
                                'bg-yellow-100 text-black':
                                    top5HomePlayers.includes(player.name),
                            }"
                            class="border-b hover:bg-gray-600"
                        >
                            <td class="py-1 px-3 text-xs">
                                {{ player.name
                                }}<sup>{{ player.is_rookie ? "R" : "V" }}</sup>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                <span :class="roleBadgeClass(player.role)">{{
                                    player.role
                                }}</span>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{
                                    player.minutes > 0 ? player.minutes : "DNP"
                                }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.points }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.rebounds }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.assists }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.steals }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.blocks }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.turnovers : 0 }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.fouls : 0 }}
                            </td>
                        </tr>
                        <tr v-if="sortedHomePlayers.length === 0">
                            <td
                                colspan="10"
                                class="py-1 px-3 text-center text-xs"
                            >
                                No player statistics available.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Away Team Player Stats -->
            <div
                class="mb-2 p-2 rounded"
                :style="{
                    backgroundColor: '#' + gameDetails?.away_team.primary_color,
                }"
            >
                <h4 class="text-lg font-semibold mb-1">
                    {{ gameDetails?.away_team.name }} Player Stats
                </h4>
                <table
                    class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm"
                >
                    <thead>
                        <tr
                            class="text-left"
                            :style="{
                                backgroundColor:
                                    '#' +
                                    gameDetails?.away_team.secondary_color,
                            }"
                        >
                            <th class="py-2 px-3 text-xs">Name</th>
                            <th class="py-2 px-3 text-xs">Role</th>
                            <th class="py-2 px-3 text-xs">Mins</th>
                            <th class="py-2 px-3 text-xs">Pts</th>
                            <th class="py-2 px-3 text-xs">Rbd</th>
                            <th class="py-2 px-3 text-xs">Ast</th>
                            <th class="py-2 px-3 text-xs">Stl</th>
                            <th class="py-2 px-3 text-xs">Blk</th>
                            <th class="py-2 px-3 text-xs">TO</th>
                            <th class="py-2 px-3 text-xs">Fls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="player in sortedAwayPlayers"
                            :key="player.name"
                            @click.prevent="showPlayerProfileModal = player"
                            :class="{
                                'bg-yellow-100 text-black':
                                    top5AwayPlayers.includes(player.name),
                            }"
                            class="border-b hover:bg-gray-600"
                        >
                            <td class="py-1 px-3 text-xs">
                                {{ player.name }}
                                <sup>{{ player.is_rookie ? "R" : "V" }}</sup>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                <span :class="roleBadgeClass(player.role)">{{
                                    player.role
                                }}</span>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{
                                    player.minutes > 0 ? player.minutes : "DNP"
                                }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.points }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.rebounds }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.assists }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.steals }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.blocks }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.turnovers : 0 }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.fouls : 0 }}
                            </td>
                        </tr>
                        <tr v-if="sortedAwayPlayers.length === 0">
                            <td
                                colspan="10"
                                class="py-1 px-3 text-center text-xs"
                            >
                                No player statistics available.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Best Player of the Game -->
        <div class="flex bg-white">
            <!-- Best Player Section: 1/4 Width -->
            <div class="w-1/2 p-4">
                <h3 class="text-lg font-semibold mb-3">Player of the Game</h3>
                <div
                    v-if="bestPlayer"
                    class="bg-white shadow-lg p-4 rounded-lg text-black mb-4"
                >
                    <div class="flex flex-col items-center mb-4">
                        <p class="text-4xl font-extrabold mb-1">
                            {{ playerFormatter(bestPlayer?.name) }}
                            <sup v-if="bestPlayer?.is_finals_mvp">
                                <i class="fa fa-star fa-sm text-yellow-500"></i>
                            </sup>
                        </p>
                        <p class="text-gray-600 text-xl">
                            {{ bestPlayer?.team }}
                        </p>
                    </div>
                    <ul class="grid grid-cols-3 gap-4 p-4">
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.points
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">PTS</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.rebounds
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">REB</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.assists
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">AST</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.steals
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">STL</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.blocks
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">BLK</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-red-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.turnovers
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">TO</p>
                        </li>
                    </ul>

                    <!-- Marquee for awards -->
                    <div class="mt-4 flex justify-start">
                        <p class="text-sm font-bold text-gray-600" v-if="bestPlayer?.awards && bestPlayer?.awards.length > 0">
                            {{ bestPlayer?.awards }},
                        </p>
                        <p class="text-sm font-bold text-gray-600" v-if="bestPlayer?.finals_mvp && bestPlayer?.finals_mvp.length > 0">
                             {{ bestPlayer?.finals_mvp }},
                        </p>
                        <p class="text-sm font-bold text-gray-600" v-if="bestPlayer?.championship_won && bestPlayer?.championship_won.length > 0">
                            {{ bestPlayer?.championship_won }}
                       </p>
                    </div>
                    <small class="float-right font-bold text-red-500"
                        >{{ bestPlayer.draft_status }}
                        {{
                            bestPlayer.drafted_team_acro
                                ? `(${bestPlayer.drafted_team_acro})`
                                : ""
                        }}
                    </small>
                </div>
            </div>
            <!-- Stat Leaders Section: 3/4 Width -->
            <div class="w-1/2 p-4 bg-white">
                <h3 class="text-lg font-semibold mb-2">Stat Leaders</h3>
                <div class="min-w-full shadow-lg border-gray-300 p-4">
                    <ul class="space-y-4">
                        <li
                            v-if="statLeaders.points"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-bold">{{
                                            statLeaders.points.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.points.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.points.points }} pts
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li
                            v-if="statLeaders.assists"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.assists.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.assists.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{
                                                statLeaders.assists.assists
                                            }}
                                            ast
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.rebounds"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.rebounds.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.rebounds.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{
                                                statLeaders.rebounds.rebounds
                                            }}
                                            reb
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.steals"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.steals.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.steals.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.steals.steals }} stl
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.blocks"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.blocks.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.blocks.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.blocks.blocks }} blk
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <Modal :show="isTeamRosterModalOpen" :maxWidth="'fullscreen'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="isTeamRosterModalOpen = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="mt-4">
                <TeamRoster
                    v-if="isTeamRosterModalOpen"
                    :team_id="isTeamRosterModalOpen"
                />
            </div>
        </Modal>

        <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
            <button
                class="flex float-end bg-gray-100 p-3"
                @click.prevent="showPlayerProfileModal = false"
            >
                <i class="fa fa-times text-black-600"></i>
            </button>
            <div class="p-6 block">
                <PlayerPerformance
                    :key="showPlayerProfileModal.player_id"
                    :player_id="showPlayerProfileModal.player_id"
                />
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { roundNameFormatter, roleBadgeClass } from "@/Utility/Formatter";
import Modal from "@/Components/Modal.vue";

import TeamRoster from "@/Pages/Teams/Module/TeamRoster.vue";
import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";

const props = defineProps({
    game_id: {
        type: String,
        required: true,
    },
    showBoxScore: {
        type: Boolean,
        default: true,
    },
});
const showPlayerProfileModal = ref(false);
const isTeamRosterModalOpen = ref(false);
const gameDetails = ref(null);
const playerStats = ref({ home: [], away: [] });
const bestPlayer = ref(null);
const statLeaders = ref([]);

// Fetch the box score data
const fetchBoxScore = async () => {
    try {
        const response = await axios.post(route("game.boxscore"), {
            game_id: props.game_id,
        });
        const data = response.data.box_score;

        gameDetails.value = data;
        playerStats.value.home = data.player_stats.home;
        playerStats.value.away = data.player_stats.away;
        bestPlayer.value = data.best_player;
        statLeaders.value = data.stat_leaders;
    } catch (error) {
        console.error("Error fetching box score:", error);
    }
};

const playerFormatter = (name) => {
    const nameParts = name.split(" "); // Split the name into parts

    // Assume the last part is the surname
    const firstName = nameParts[0];
    const surName = nameParts[nameParts.length - 1];

    // Define a maximum length for the surname
    const maxnameLength = 13; // You can adjust this value as needed

    // Check if the name is too long
    if (name.length > maxnameLength) {
        return `${firstName[0]}. ${surName}`; // Format as "F. Surname"
    } else {
        return name; // Return the name as is if the surname is not too long
    }
};
// Sort players by points and get top 5 players
const sortedHomePlayers = computed(() => {
    return playerStats.value.home.slice().sort((a, b) => b.points - a.points);
});

const sortedAwayPlayers = computed(() => {
    return playerStats.value.away.slice().sort((a, b) => b.points - a.points);
});

const top5HomePlayers = computed(() => {
    return sortedHomePlayers.value.slice(0, 5).map((player) => player.name);
});

const top5AwayPlayers = computed(() => {
    return sortedAwayPlayers.value.slice(0, 5).map((player) => player.name);
});

onMounted(() => {
    fetchBoxScore();
});
</script>

<style scoped>
.team-card {
    background-color: #1a202c; /* Dark background for team cards */
    transition: transform 0.2s;
}

.team-card:hover {
    transform: scale(1.05); /* Scale effect on hover */
}

/* Use darker backgrounds for table headers */
table {
    border-collapse: collapse;
}

th,
td {
    border: 1px solid #2d3748; /* Subtle borders */
}

tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1); /* Light hover effect */
}
</style>
