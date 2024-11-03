<template>
<div class="team-info p-4">
    <h2
        class="text-xl font-semibold text-gray-800"
        v-if="team_info"
    >
        {{ team_info.teams.team_name ?? "-" }} (
        {{ team_info.teams.acronym ?? "-" }})
    </h2>
    <!-- Divider -->
    <hr class="my-4 border-t border-gray-200" />

    <div class="mt-4 grid md:grid-cols-5 grid-cols-1 gap-4" v-if="team_info">
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                All-Time Stats
            </h3>
            <p>
                Wins:
                {{ team_info.allTimeStats.all_time_wins ?? 0 }}
            </p>
            <p>
                Losses:
                {{
                    team_info.allTimeStats.all_time_losses ?? 0
                }}
            </p>
            <p>
                Total Games:
                {{
                    parseFloat(
                        team_info.allTimeStats.all_time_wins ??
                            0
                    ) +
                    parseFloat(
                        team_info.allTimeStats
                            .all_time_losses ?? 0
                    )
                }}
            </p>
            <p
                v-if="
                    team_info &&
                    team_info.allTimeStats.all_time_wins !=
                        null &&
                    team_info.allTimeStats.all_time_losses !=
                        null
                "
            >
                Winrate:
                {{
                    (
                        (parseFloat(
                            team_info.allTimeStats
                                .all_time_wins ?? 0
                        ) /
                            (parseFloat(
                                team_info.allTimeStats
                                    .all_time_wins ?? 0
                            ) +
                                parseFloat(
                                    team_info.allTimeStats
                                        .all_time_losses ?? 0
                                ))) *
                        100
                    ).toFixed(2)
                }}%
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Finals Stats
            </h3>
            <p>
                Wins:
                {{ team_info.finalsStats.finals_wins ?? 0 }}
            </p>
            <p>
                Losses:
                {{ team_info.finalsStats.finals_losses ?? 0 }}
            </p>
            <p>
                Appearances:
                {{ team_info.finalsStats.finals_appearances }}
            </p>
        </div>
        <div >
            <h3 class="text-md font-semibold text-gray-800">
                Playoff Stats
            </h3>
            <p>
                Wins:
                {{ team_info.playoffStats.playoff_wins ?? 0 }}
            </p>
            <p>
                Losses:
                {{ team_info.playoffStats.playoff_losses ?? 0 }}
            </p>
            <p>
                Appearances:
                {{ team_info.playoffStats.playoff_appearances }}
            </p>
        </div>
        <div >
            <h3 class="text-md font-semibold text-gray-800">
               Game Streaks
            </h3>
            <p>
                Win Streak:
                {{ team_info.streaks[0].best_winning_streak ?? 0 }}
            </p>
            <p>
                Losing Streak:
                {{ team_info.streaks[0].best_losing_streak ?? 0 }}
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Playoff Round Appearance
            </h3>
            <p>
                Round of 32:
                {{ team_info.roundStats.round_of_32_appearances ?? 0 }}
            </p>
            <p>
                Round of 16:
                {{ team_info.roundStats.round_of_16_appearances ?? 0 }}
            </p>
            <p>
                Quarter Finals:
                {{ team_info.roundStats.quarter_final_appearances ?? 0 }}
            </p>
            <p>
                Semi Finals:
                {{ team_info.roundStats.semi_final_appearances ?? 0 }}
            </p>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-4 gap-4" v-else>
        <p class="text-red-500">No Team Info</p>
    </div>
    <!-- Divider -->
    <hr class="my-4 border-t border-gray-200" />

    <div
        class="mt-4 grid grid-cols-5 gap-4"
        v-if="team_last_season"lastRoundOf32Season
    >
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Last Round of 32 Season
            </h3>
            <p>
                {{
                    team_last_season.lastRoundOf32Season ??
                    "n/a"
                }}
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Last Round of 16 Season
            </h3>
            <p>
                {{
                    team_last_season.lastRoundOf16Season ??
                    "n/a"
                }}
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Last Quarter Final Season
            </h3>
            <p>
                {{
                    team_last_season.lastQuarterFinalSeason ??
                    "n/a"
                }}
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Last Semi Final Season
            </h3>
            <p>
                {{
                    team_last_season.lastSemiFinalSeason ??
                    "n/a"
                }}
            </p>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-800">
                Last Final Season
            </h3>
            <p>
                {{ team_last_season.lastFinalSeason ?? "n/a" }}
            </p>
        </div>
    </div>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />

        <div class="mt-4 grid grid-cols-5 gap-4">
            <div
                v-if="
                    team_season_finals &&
                    team_season_finals.finalsWinSeasons.length > 0
                "
            >
                <h3 class="text-md font-semibold text-gray-800">
                    Champions Seasons
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(
                            season, index
                        ) in team_season_finals.finalsWinSeasons"
                        :key="index"
                        class="inline-block bg-green-500 text-white px-2 py-1 rounded-md text-sm"
                        >{{ season }}</span
                    >
                </div>
            </div>
            <div
                class="mt-4 grid grid-cols-1 gap-4 text-center"
                v-else
            >
                <p class="text-red-500">No Champions Season</p>
            </div>
            <div
                v-if="
                    team_season_finals &&
                    team_season_finals.finalsSeasons.length > 0
                "
            >
                <h3 class="text-md font-semibold text-gray-800">
                    Finals Appearance
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(
                            season, index
                        ) in team_season_finals.finalsSeasons"
                        :key="index"
                        class="inline-block bg-green-500 text-white px-2 py-1 rounded-md text-sm"
                        >{{ season }}</span
                    >
                </div>
            </div>
            <div
                class="mt-4 grid grid-cols-1 gap-4 text-center"
                v-else
            >
                <p class="text-red-500">No Finals Season</p>
            </div>
            <div
                v-if="
                    team_season_standings &&
                    team_season_standings.topStandingsSeasons
                        .length > 0
                "
            >
                <h3 class="text-md font-semibold text-gray-800">
                    Top Seasons
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(
                            season, index
                        ) in team_season_standings.topStandingsSeasons"
                        :key="index"
                        class="inline-block bg-green-500 text-white px-2 py-1 rounded-md text-sm"
                        >{{ season }}</span
                    >
                </div>
            </div>
            <div
                class="mt-4 grid grid-cols-1 gap-4 text-center"
                v-else
            >
                <p class="text-red-500">No Top Season</p>
            </div>
            <div
                v-if="
                    team_season_standings &&
                    team_season_standings.playOffAppearance
                        .length > 0
                "
            >
                <h3 class="text-md font-semibold text-gray-800">
                Play-offs Appearance
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(
                            season, index
                        ) in team_season_standings.playOffAppearance"
                        :key="index"
                        class="inline-block bg-green-500 text-white px-2 py-1 rounded-md text-sm"
                        >{{ season }}</span
                    >
                </div>
            </div>
            <div
                class="mt-4 grid grid-cols-1 gap-4 text-center"
                v-else
            >
                <p class="text-red-500">No Play-offs Appearance</p>
            </div>
            <div
                v-if="
                    team_season_standings &&
                    team_season_standings.bottomStandingsSeasons
                        .length > 0
                "
            >
                <h3 class="text-md font-semibold text-gray-800">
                    Worst Seasons
                </h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="(
                            season, index
                        ) in team_season_standings.bottomStandingsSeasons"
                        :key="index"
                        class="inline-block bg-red-500 text-white px-2 py-1 rounded-md text-sm"
                        >{{ season }}</span
                    >
                </div>
            </div>
            <div
                class="mt-4 grid grid-cols-1 gap-4 text-center"
                v-else
            >
                <p class="text-red-500">No Worst Season</p>
            </div>
        </div>
        <hr class="my-4 border-t border-gray-200" />

    <div class="mt-4" v-if="team_rivals && team_rivals.top_rivals.length > 0">
        <h3 class="text-md font-semibold text-gray-800">
            Rivals
        </h3>
        <div
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5 lg:grid-cols-5"
        >
            <div
                v-for="(team,tt) in team_rivals.top_rivals"
                :key="tt"
                class="col-span-1"
            >
                <div
                    class="bg-white shadow-md rounded-md overflow-hidden"
                >
                    <div class="px-4 py-5 sm:px-6">
                        <h3
                            class="text-xs font-bold text-nowrap uppercase leading-6 text-gray-800"
                        >
                            {{ team.team_name }}
                        </h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <div
                            class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                        >
                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Win
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                            >
                                {{ team.wins }}
                                <span
                                    v-if="
                                        team.wins >
                                        team.losses
                                    "
                                    class="ml-2 text-yellow-500"
                                >
                                    <i class="fas fa-medal"></i>
                                </span>
                            </dd>
                        </div>
                        <div
                            class="bg-gray-200 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                        >
                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Loss
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                            >
                                {{ team.losses }}
                                <span
                                    v-if="
                                        team.losses >
                                        team.wins
                                    "
                                    class="ml-2 text-yellow-500"
                                >
                                    <i class="fas fa-medal"></i>
                                </span>
                            </dd>
                        </div>
                        <!-- Additional details can be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 text-center" v-else>
        <p class="text-red-500">
            No Rivals Information
        </p>
    </div>
    <!-- Divider -->
    <hr class="my-4 border-t border-gray-200" />

    <div class="mt-4" v-if="team_matches && team_matches.lastTenGames.length > 0">
        <h3 class="text-md font-semibold text-gray-800">
            Last Ten Games ({{
                calculateRecord(team_matches.lastTenGames)
            }})
        </h3>
        <div
            class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5"
        >
            <div
                v-for="game in team_matches.lastTenGames"
                :key="game.id"
                class="col-span-1"
            >
                <div
                    class="bg-white shadow-md rounded-md overflow-hidden"
                >
                    <div class="px-4 py-5 sm:px-6">
                        <h3
                            class="text-xs font-bold text-nowrap uppercase leading-6 text-gray-800"
                        >
                            {{ game.home_team_name }} vs
                            {{ game.away_team_name }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Round:
                            {{ roundNameFormatter(game.round) }}
                        </p>
                    </div>
                    <div class="border-t border-gray-200">
                        <div
                            class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                        >
                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Home
                            </dt>
                            <dd
                                :class="[
                                    game.status === 'Loss'
                                        ? 'font-bold text-red-500'
                                        : '',
                                    game.away_score <
                                    game.home_score
                                        ? 'font-bold'
                                        : '',
                                ]"
                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                            >
                                {{ game.home_score }}
                                <span
                                    v-if="
                                        game.home_score >
                                        game.away_score
                                    "
                                    class="ml-2 text-yellow-500"
                                >
                                    <i class="fas fa-medal"></i>
                                </span>
                            </dd>
                        </div>
                        <div
                            class="bg-gray-200 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                        >
                            <dt
                                class="text-sm font-medium text-gray-500"
                            >
                                Away
                            </dt>
                            <dd
                                :class="[
                                    game.status === 'Loss'
                                        ? 'font-bold text-red-500'
                                        : '',
                                    game.away_score >
                                    game.home_score
                                        ? 'font-bold'
                                        : '',
                                ]"
                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                            >
                                {{ game.away_score }}
                                <span
                                    v-if="
                                        game.home_score <
                                        game.away_score
                                    "
                                    class="ml-2 text-yellow-500"
                                >
                                    <i class="fas fa-medal"></i>
                                </span>
                            </dd>
                        </div>
                        <!-- Additional details can be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 text-center" v-else>
        <p class="text-red-500">No Recent Matches Available</p>
    </div>
    <!-- Divider -->
    <hr class="my-4 border-t border-gray-200" />

    <div
        class="mt-4"
        v-if="
            team_head_2_head &&
            team_head_2_head.headToHeadBattles.length > 0
        "
    >
        <h3 class="text-md font-semibold text-gray-800">
            Head-to-Head Battles
        </h3>
        <div class="mt-4 overflow-auto">
            <input
                type="text"
                class="mt-1 mb-2 p-2 border rounded w-full"
                v-model="searchQuery"
                placeholder="Search..."
            />
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <!-- Dynamically generate table headers based on keys -->
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            v-if="
                                team_head_2_head
                                    .headToHeadBattles.length >
                                0
                            "
                            v-for="(key, index) in Object.keys(
                                team_head_2_head
                                    .headToHeadBattles[0]
                            )"
                            :key="index"
                        >
                            {{ key.replaceAll("_", " ") }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(
                            battle, index
                        ) in team_head_2_head
                        .headToHeadBattles"
                        :key="index"
                        class="bg-white shadow"
                    >
                        <!-- Dynamically generate table data cells based on keys -->
                        <td
                            class="px-6 py-4 whitespace-nowrap"
                            v-for="(value, key) in battle"
                            :key="key"
                        >
                            <div
                                class="text-sm text-gray-900 uppercase"
                            >
                                {{ value }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 text-center" v-else>
        <p class="text-red-500">
            No Head to Head Battle Information
        </p>
    </div>
</div>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import { roundNameFormatter } from "@/Utility/Formatter";
import { ref, onMounted, computed, watch  } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    team_id: {
        type: Number,
        required: true
    }
});

const team_info = ref(false);
const team_season_standings = ref(false);
const team_season_finals = ref(false);
const team_last_season = ref(false);
const team_matches = ref(false);
const team_rivals = ref(false);
const team_head_2_head = ref(false);
const searchQuery = ref("");
const currentPage = ref(1);
const pageSize = 10;

watch(() => props.team_id, async (n, o) => {
    if (n !== o) {
        await fetchDataForTeam(n);
    }
});
onMounted(() => {
    fetchDataForTeam(props.team_id);
});
const fetchDataForTeam = async (id) => {
    try {
        await fetchTeamInfo(id);
        await fetchTeamLastSeason(id);
        await fetchTeamMatchesHead2Head(id);
        await fetchTeamMatches(id);
        await fetchTeamRivals(id);
        await fetchTeamSeasonStandings(id);
        await fetchTeamSeasonFinals(id);
    } catch (error) {
        console.error("Error fetching data for team:", error);
    }
};
const fetchTeamInfo = async (id) => {
    try {

        const response = await axios.post(route("teams.info"), {
            team_id: id,
        });
        team_info.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const fetchTeamRivals = async (id) => {
    try {
        const response = await axios.post(route("teams.rivals"), {
            team_id: id,
        });
        team_rivals.value = response.data;
    } catch (error) {
        console.error("Error fetching team rivals:", error);
    }
};
const fetchTeamSeasonStandings = async (id) => {
    try {
        team_season_standings.value = false;
        const response = await axios.post(route("teams.season.standings"), {
            team_id: id,
        });
        team_season_standings.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const fetchTeamSeasonFinals = async (id) => {
    try {
        team_season_finals.value = false;
        const response = await axios.post(route("teams.season.finals"), {
            team_id: id,
        });
        team_season_finals.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const fetchTeamLastSeason = async (id) => {
    try {
        team_last_season.value = false;
        const response = await axios.post(route("teams.last.season"), {
            team_id: id,
        });
        team_last_season.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const fetchTeamMatches = async (id) => {
    try {
        team_matches.value = false;
        const response = await axios.post(route("teams.matches"), {
            team_id: id,
        });
        team_matches.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const fetchTeamMatchesHead2Head = async (id) => {
    try {
        team_head_2_head.value = false;
        const response = await axios.post(route("teams.matches.h2h"), {
            team_id: id,
        });
        team_head_2_head.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const calculateRecord = (lastTenGames) => {
    let wins = 0;
    let losses = 0;

    lastTenGames.forEach((game) => {
        if (game.status === "Win") {
            wins++;
        } else if (game.status === "Loss") {
            losses++;
        }
    });

    return `${wins}-${losses}`;
};
const filteredBattles = computed(() => {
    let team = team_head_2_head.value;
    return team.headToHeadBattles.filter((battle) => {
        return Object.values(battle).some((value) =>
            value
                .toString()
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase())
        );
    });
});
const totalPages = computed(() => {
    return Math.ceil(filteredBattles.value.length / pageSize);
});
const paginatedBattles = computed(() => {
    const startIndex = (currentPage.value - 1) * pageSize;
    const endIndex = startIndex + pageSize;
    return filteredBattles.value.slice(startIndex, endIndex);
});




</script>
