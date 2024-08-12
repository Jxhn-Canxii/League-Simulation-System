<template>
    <div class="team-info p-4">
        <h2 class="text-xl font-semibold text-gray-800">Team Comparison</h2>
        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <!-- Check if data for both home and away teams are available -->
        <div class="block">
            <div class="grid grid-cols-2 gap-4">
                <!-- Home Team Stats -->
                <div class="border p-4 team-stats" v-if="!homeLoading">
                    <!-- Content shown when home team data is loaded -->
                    <h3 class="text-lg font-semibold" v-if="home.teams && home.latestSeason">
                        {{ home.teams.team_name }} ({{
                            home.latestSeason[0].wins
                        }}
                        - {{ home.latestSeason[0].losses }})
                    </h3>
                    <span
                        v-if="home.teams"
                        class="bg-blue-500 text-white shadow px-2 py-1 mb-3 inline-block rounded-full text-xs font-semibold"
                    >
                        {{ home.teams.conference_name }} Conference   <sup>{{ home.latestSeason[0].streak_status }}</sup>
                    </span>
                    <!-- Display all-time stats -->
                    <div class="stat" v-if="home.allTimeStats">
                        <p>All-Time Wins:</p>
                        <p>{{ home.allTimeStats.all_time_wins }}</p>
                    </div>
                    <div class="stat" v-if="home.allTimeStats">
                        <p>All-Time Losses:</p>
                        <p>{{ home.allTimeStats.all_time_losses }}</p>
                    </div>
                    <div class="stat" v-if="home.allTimeStats">
                        <p>Win Rate:</p>
                        <p>{{ calculateWinRate(home.allTimeStats) }}%</p>
                    </div>
                    <!-- Display round stats -->
                    <div class="stat" v-if="home.roundStats">
                        <p>Semi-Final Appearances:</p>
                        <p>{{ home.roundStats.semi_final_appearances }}</p>
                    </div>
                    <div class="stat" v-if="home.roundStats">
                        <p>Quarter-Final Appearances:</p>
                        <p>{{ home.roundStats.quarter_final_appearances }}</p>
                    </div>
                    <!-- Display playoff stats -->
                    <div class="stat" v-if="home.playoffStats">
                        <p>Playoff Wins:</p>
                        <p>{{ home.playoffStats.playoff_wins }}</p>
                    </div>
                    <div class="stat" v-if="home.playoffStats">
                        <p>Playoff Losses:</p>
                        <p>{{ home.playoffStats.playoff_losses }}</p>
                    </div>
                    <div class="stat" v-if="home.playoffStats">
                        <p>Playoff Appearances:</p>
                        <p>{{ home.playoffStats.playoff_appearances }}</p>
                    </div>
                    <!-- Display finals stats -->
                    <div class="stat" v-if="home.finalsStats">
                        <p>Finals Appearances:</p>
                        <p>{{ home.finalsStats.finals_appearances }}</p>
                    </div>
                    <div class="stat flex justify-between" v-if="home.finalsStats">
                        <p>
                            Championships ({{
                                home.finalsStats.finals_wins ?? 0
                            }}):
                        </p>
                        <div v-if="home.finalsStats">
                            <template
                                v-if="home.finalsStats.finals_wins"
                                v-for="i in parseInt(
                                    home.finalsStats.finals_wins
                                )"
                                :key="'win1-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                            <template
                                v-if="home.finalsStats.finals_losses"
                                v-for="i in parseInt(
                                    home.finalsStats.finals_losses
                                )"
                                :key="'loss1-' + i"
                            >
                                <i class="fa fa-trophy text-gray-500"></i>
                            </template>
                        </div>
                    </div>
                    <!-- Display season stats -->
                    <div class="stat flex justify-between" v-if="home.seasonStats">
                        <p>
                            Overall Championships ({{
                                home.seasonStats.overallRank1Count
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="home.seasonStats.overallRank1Count"
                                v-for="i in parseInt(
                                    home.seasonStats.overallRank1Count
                                )"
                                :key="'win1-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                        </div>
                    </div>
                    <div class="stat flex justify-between" v-if="home.seasonStats">
                        <p>
                            Conf. Championships ({{
                                home.seasonStats.conferenceRank1Count
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="home.seasonStats.conferenceRank1Count"
                                v-for="i in parseInt(
                                    home.seasonStats.conferenceRank1Count
                                )"
                                :key="'win1-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                        </div>
                    </div>
                    <div class="stat flex justify-between" v-if="home.seasonStats">
                        <p>
                            Worst Rankings ({{
                                home.seasonStats.lastOverallRankCount
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="home.seasonStats.lastOverallRankCount"
                                v-for="i in parseInt(
                                    home.seasonStats.lastOverallRankCount
                                )"
                                :key="'win1-' + i"
                            >
                                <i class="fa fa-trophy text-red-500"></i>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="border p-4 team-stats text-center" v-else>
                    <!-- Loading state for home team -->
                    <p class="text-gray-500 font-semibold">Loading Home Team Info...</p>
                </div>
                <!-- End of Home Team Stats -->

                <!-- Away Team Stats -->
                <div class="border p-4 team-stats" v-if="!awayLoading">
                    <!-- Content shown when away team data is loaded -->
                    <h3 class="text-lg font-semibold" v-if="away.teams && away.latestSeason">
                        {{ away.teams.team_name }} ({{
                            away.latestSeason[0].wins
                        }}
                        - {{ away.latestSeason[0].losses }})
                    </h3>
                    <span
                        v-if="away.teams"
                        class="bg-red-500 text-white shadow px-2 py-1 mb-3 inline-block rounded-full text-xs font-semibold"
                    >
                        {{ away.teams.conference_name }} Conference <sup>{{ away.latestSeason[0].streak_status }}</sup>
                    </span>
                    <!-- Display all-time stats -->
                    <div class="stat" v-if="away.allTimeStats">
                        <p>All-Time Wins:</p>
                        <p>{{ away.allTimeStats.all_time_wins }}</p>
                    </div>
                    <div class="stat" v-if="away.allTimeStats">
                        <p>All-Time Losses:</p>
                        <p>{{ away.allTimeStats.all_time_losses }}</p>
                    </div>
                    <div class="stat" v-if="away.allTimeStats">
                        <p>Win Rate:</p>
                        <p>{{ calculateWinRate(away.allTimeStats) }}%</p>
                    </div>
                    <!-- Display round stats -->
                    <div class="stat" v-if="away.roundStats">
                        <p>Semi-Final Appearances:</p>
                        <p>{{ away.roundStats.semi_final_appearances }}</p>
                    </div>
                    <div class="stat" v-if="away.roundStats">
                        <p>Quarter-Final Appearances:</p>
                        <p>{{ away.roundStats.quarter_final_appearances }}</p>
                    </div>
                    <!-- Display playoff stats -->
                    <div class="stat" v-if="away.playoffStats">
                        <p>Playoff Wins:</p>
                        <p>{{ away.playoffStats.playoff_wins }}</p>
                    </div>
                    <div class="stat" v-if="away.playoffStats">
                        <p>Playoff Losses:</p>
                        <p>{{ away.playoffStats.playoff_losses }}</p>
                    </div>
                    <div class="stat" v-if="away.playoffStats">
                        <p>Playoff Appearances:</p>
                        <p>{{ away.playoffStats.playoff_appearances }}</p>
                    </div>
                    <!-- Display finals stats -->
                    <div class="stat" v-if="away.finalsStats">
                        <p>Finals Appearances:</p>
                        <p>{{ away.finalsStats.finals_appearances }}</p>
                    </div>
                    <div class="stat flex justify-between" v-if="away.finalsStats">
                        <p>
                            Championships ({{
                                away.finalsStats.finals_wins ?? 0
                            }}):
                        </p>
                        <div v-if="away.finalsStats">
                            <template
                                v-if="away.finalsStats.finals_wins"
                                v-for="i in parseInt(
                                    away.finalsStats.finals_wins
                                )"
                                :key="'win2-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                            <template
                                v-if="away.finalsStats.finals_losses"
                                v-for="i in parseInt(
                                    away.finalsStats.finals_losses
                                )"
                                :key="'loss2-' + i"
                            >
                                <i class="fa fa-trophy text-gray-500"></i>
                            </template>
                        </div>
                    </div>
                    <!-- Display season stats -->
                    <div class="stat flex justify-between" v-if="away.seasonStats">
                        <p>
                            Overall Championships ({{
                                away.seasonStats.overallRank1Count
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="away.seasonStats.overallRank1Count"
                                v-for="i in parseInt(
                                    away.seasonStats.overallRank1Count
                                )"
                                :key="'win2-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                        </div>
                    </div>
                    <div class="stat flex justify-between" v-if="away.seasonStats">
                        <p>
                            Conf. Championships ({{
                                away.seasonStats.conferenceRank1Count
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="away.seasonStats.conferenceRank1Count"
                                v-for="i in parseInt(
                                    away.seasonStats.conferenceRank1Count
                                )"
                                :key="'win2-' + i"
                            >
                                <i class="fa fa-trophy text-yellow-500"></i>
                            </template>
                        </div>
                    </div>
                    <div class="stat flex justify-between" v-if="away.seasonStats">
                        <p>
                            Worst Rankings ({{
                                away.seasonStats.lastOverallRankCount
                            }}):
                        </p>
                        <div>
                            <template
                                v-if="away.seasonStats.lastOverallRankCount"
                                v-for="i in parseInt(
                                    away.seasonStats.lastOverallRankCount
                                )"
                                :key="'win2-' + i"
                            >
                                <i class="fa fa-trophy text-red-500"></i>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="border p-4 team-stats text-center" v-else>
                    <!-- Loading state for away team -->
                    <p class="text-gray-500 font-semibold">Loading Away Team Info...</p>
                </div>
                <!-- End of Away Team Stats -->
            </div>
            <!-- Display latest matches section -->
            <div class="grid grid-cols-1 gap-4 mt-3" v-if="!matchesLoading">
                <h2 class="text-lg font-semibold text-gray-800">
                    Latest Matches
                </h2>
                <table class="min-w-full divide-y divide-gray-200 p-2">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Season
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Round
                            </th>
                            <th
                                class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Home Team
                            </th>
                            <th
                                class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Score
                            </th>
                            <th
                                class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Away Team
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Iterate over matches data -->
                        <tr
                            v-for="(match, index) in matches"
                            :key="match.id"
                            class="hover:bg-gray-200"
                        >
                            <td class="px-2 py-2 whitespace-nowrap border">
                                {{ match.season_name }}
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap border">
                                {{ roundNameFormatter(match.round) }}
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap border text-right"
                            >
                                <span
                                    :class="{
                                        'font-semibold':
                                            match.home_score > match.away_score,
                                    }"
                                >
                                    {{ match.home_team_name }}
                                </span>
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap border text-right"
                            >
                                <span
                                    :class="{
                                        'font-semibold':
                                            match.home_score > match.away_score,
                                    }"
                                >
                                    {{ match.home_score }} -
                                    {{ match.away_score }}
                                </span>
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap border text-right"
                            >
                                <span
                                    :class="{
                                        'font-semibold':
                                            match.away_score > match.home_score,
                                    }"
                                >
                                    {{ match.away_team_name }}
                                </span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap border">
                                <span
                                    v-if="match.status === 2"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Completed
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"
                                >
                                    In Progress
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="border p-4 team-stats text-center mt-4" v-else>
                <!-- Loading state for matches -->
                <p class="text-gray-500 font-semibold">Loading Matches...</p>
            </div>
        </div>
        <!-- End of Team Comparison Section -->
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { roundNameFormatter } from "@/Utility/Formatter";
import StatButton from "./StatButton.vue";

// Reactive variables for data and loading states
const home = ref([]);
const away = ref([]);
const matches = ref([]);
const homeLoading = ref(true); // Loading state for home team
const awayLoading = ref(true); // Loading state for away team
const matchesLoading = ref(true); // Loading state for matches

// Props passed to the component
const props = defineProps({
    home_id: {
        type: Number,
        required: true,
    },
    away_id: {
        type: Number,
        required: true,
    },
    season_id: {
        type: Number,
        required: true,
    },
});

// Fetch data on component mount
onMounted(() => {
    fetchDataForTeam(props.home_id, props.away_id, props.season_id);
});

// Function to fetch all necessary data
const fetchDataForTeam = async (home_id, away_id, season_id) => {
    try {
        await Promise.all([
            fetchHomeTeamInfo(home_id, season_id),
            fetchAwayTeamInfo(away_id, season_id),
            fetchGameMatchHistory(home_id, away_id, season_id),
        ]);
        // Set loading states to false once all data is loaded
        homeLoading.value = false;
        awayLoading.value = false;
        matchesLoading.value = false;
    } catch (error) {
        console.error("Error fetching data for team:", error);
    }
};

// Function to fetch home team data
const fetchHomeTeamInfo = async (team_id, season_id) => {
    try {
        const response = await axios.post(route("teams.latest.season"), {
            team_id: team_id,
            season_id: season_id,
        });
        home.value = response.data.data;
    } catch (error) {
        console.error("Error fetching home team info:", error);
    }
};

// Function to fetch away team data
const fetchAwayTeamInfo = async (team_id, season_id) => {
    try {
        const response = await axios.post(route("teams.latest.season"), {
            team_id: team_id,
            season_id: season_id,
        });
        away.value = response.data.data;
    } catch (error) {
        console.error("Error fetching away team info:", error);
    }
};

// Function to fetch match history between home and away teams
const fetchGameMatchHistory = async (home_id, away_id, season_id) => {
    try {
        const response = await axios.post(route("match.history"), {
            home_id: home_id,
            away_id: away_id,
            season_id: season_id,
        });
        matches.value = response.data.matches;
    } catch (error) {
        console.error("Error fetching match history:", error);
    }
};

// Function to calculate win rate based on all time stats
const calculateWinRate = (stats) => {
    const totalGames =
        parseFloat(stats.all_time_wins) + parseFloat(stats.all_time_losses);
    return totalGames === 0
        ? 0
        : ((parseFloat(stats.all_time_wins) / totalGames) * 100).toFixed(2);
};
</script>

<style scoped>
.team-stats .stat {
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
}

.fa {
    font-size: 1.2rem;
    margin-left: 5px;
}
</style>
