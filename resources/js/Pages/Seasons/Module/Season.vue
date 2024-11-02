<template>
    <!-- Overlay Container -->
    <div
        v-if="isHide"
        class="bg-gray-900 bg-opacity-75 flex items-center justify-center z-500"
    >
        <div class="bg-white p-6 rounded-md shadow-lg w-screen min-h-full overflow-hidden">
            <h2 class="text-lg font-semibold text-gray-800 mb-2 text-left">
                {{ season_info?.seasons[0].name ?? "" }} Standings
            </h2>
            <div class="grid grid-cols-3 gap-6">
                <div class="block">
                    <div class="block">
                        <table
                            class="min-w-full divide-y divide-gray-200 text-sm"
                            v-if="
                                season_standings &&
                                season_standings.standings?.length > 0
                            "
                        >
                            <thead class="bg-gray-50 text-xs">
                                <tr>
                                    <th
                                        class="px-1 py-1 text-left text-gray-500 uppercase tracking-wider text-nowrap"
                                    >
                                        Team
                                    </th>
                                    <th
                                        class="px-1 py-1 text-left text-gray-500 uppercase tracking-wider text-nowrap"
                                    >
                                        Wins
                                    </th>
                                    <th
                                        class="px-1 py-1 text-left text-gray-500 uppercase tracking-wider text-nowrap"
                                    >
                                        Loss
                                    </th>
                                    <th
                                        class="px-1 py-1 text-left text-gray-500 uppercase tracking-wider text-nowrap"
                                    >
                                        Rank
                                    </th>
                                    <th
                                        class="px-1 py-1 text-left text-gray-500 uppercase tracking-wider text-nowrap"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-rose-200 divide-y divide-gray-200 text-sm"
                            >
                                <tr
                                    v-if="season_standings?.standings?.length > 0"
                                    v-for="(
                                        team, index
                                    ) in season_standings.standings"
                                    :key="index"
                                    :class="
                                        index <= 7
                                            ? 'bg-orange-300 text-black text-bold'
                                            : 'text-bold'
                                    "
                                >
                                    <td class="px-1 py-1 whitespace-nowrap text-sm">
                                        <button
                                            type="button"
                                            class="uppercase"
                                            :title="
                                                ' Playoff Appearance:' +
                                                team.playoff_appearances
                                            "
                                            @click.prevent="
                                                isTeamModalOpen = team.team_id
                                            "
                                        >
                                            <b
                                                >{{ team.team_name }}
                                                <sup class="text-slate-500">{{
                                                    team.streak_status
                                                }}</sup></b
                                            >
                                        </button>
                                    </td>
                                    <td class="px-1 py-1 whitespace-nowrap text-sm">
                                        {{ team.wins }}
                                    </td>
                                    <td class="px-1 py-1 whitespace-nowrap text-sm">
                                        {{ team.losses }}
                                    </td>
                                    <td class="px-1 py-1 whitespace-nowrap text-sm">
                                        {{ team.overall_rank }}
                                    </td>
                                    <td class="px-1 py-1 whitespace-nowrap text-sm">
                                        <div class="flex space-x-1">
                                            <!-- <span
                                            v-if="team.conference_1_rank > 0"
                                            class="flex items-center justify-center w-5 h-5 bg-red-500 text-black text-xs rounded-full"
                                            title="#1 Conference Rank"
                                        >
                                            {{ team.conference_1_rank }}
                                        </span>
                                        <span
                                            v-if="team.overall_1_rank > 0"
                                            class="flex items-center justify-center w-5 h-5 bg-purple-500 text-black text-xs rounded-full"
                                            title="#1 Overall Rank"
                                        >
                                            {{ team.overall_1_rank }}
                                        </span> -->
                                            <!-- <span
                                                v-if="
                                                    team.conference_championships >
                                                    0
                                                "
                                                class="flex items-center justify-center w-5 h-5 bg-yellow-500 text-black text-xs rounded-full"
                                                title="Conference Championships"
                                            >
                                                {{ team.conference_championships }}
                                            </span>
                                            <span
                                                v-if="team.finals_appearances > 0"
                                                class="flex items-center justify-center w-5 h-5 bg-green-500 text-black text-xs rounded-full"
                                                title="Finals Appearances"
                                            >
                                                {{ team.finals_appearances }}
                                            </span> -->
                                            <span
                                                v-if="team.championships > 0"
                                                class="flex items-center justify-center w-5 h-5 bg-red-500 text-black text-xs rounded-full"
                                                title="Championships"
                                            >
                                                {{ team.championships }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="text-center font-bold text-red-500">
                            No Standings available
                        </div>
                        <small class="text-red-500 font-bold" v-if="currentRound"
                            >Simulating Round #
                            {{ parseFloat(currentRound) }}</small
                        >
                        <div class="flex mt-4">
                            <!-- <TopPlayers
                                v-if="season_info?.seasons"
                                :season_id="season_info.seasons[0].id"
                                :conference_id="activeConferenceTab"
                                :key="topPlayersKey"
                                :round="currentRound ?? 0"
                            /> -->
                        </div>
                    </div>
                    <!-- <div class="block">

                    </div> -->
                </div>

                <div class="block md:col-span-2">
                    <GameResults v-if="activeGameId != 0" :key="activeGameId" :game_id="activeGameId" :showBoxScore="false" />
                    <p v-else class="text-red-500 font-bold">No games available!</p>
                    <!-- <SeasonTimeLine :key="topPlayersKey" :isConference="activeConferenceTab" /> -->
                </div>
            </div>
        </div>
    </div>

    <!-- this div will be at the bottom the top div will create an ilussion of overlay make the standings and top10 players card float at the center-->
    <div v-else>
        <div
            class="w-full mb-2 flex overflow-x-auto border-b-2"
            v-if="season_info.conferences && season_info.seasons"
        >
            <ul class="flex flex-wrap">
                <li
                    v-for="conference in season_info.conferences"
                    :key="conference.id"
                    @click.prevent="fetchConferenceData(conference.id)"
                    :class="
                        activeConferenceTab == conference.id
                            ? 'animate-pulse font-bold text-orange-500'
                            : ''
                    "
                    class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                >
                    <i
                        :class="
                            activeConferenceTab == conference.id
                                ? 'text-orange-500'
                                : 'text-gray-500'
                        "
                        class="fa fa-shield mr-2"
                        :title="conference.name + ' Conference'"
                    ></i>
                    <span
                        hidden
                        class="text-truncate hidden sm:inline md:inline"
                        >{{ conference.name }}
                        {{
                            conference.champions_count > 0
                                ? "( " + conference.champions_count + " )"
                                : ""
                        }}</span
                    >
                    <!-- Warning Badge Notification Counter -->
                </li>
            </ul>
        </div>
        <div
            class="flex justify-end mb-2 space-x-2"
            v-if="season_schedules && !season_schedules.is_simulated"
        >
            <button
                @click="simulatePerRound()"
                :disabled="isHide"
                :class="isHide ? 'opacity-50' : ''"
                class="text-indigo-600 bg-orange-300 shadow rounded-full p-2 font-bold text-md text-nowrap hover:text-indigo-900"
            >
                Simulate Conference
            </button>
            <button
                @click="simulateAll()"
                :disabled="isHide"
                :class="isHide ? 'opacity-50' : ''"
                class="text-indigo-600 bg-orange-400 shadow rounded-full p-2 font-bold text-md text-nowrap hover:text-indigo-900"
            >
                Simulate All Season
            </button>
        </div>
        <div
            class="grid grid-cols-1 md:grid-cols-7 gap-6 p-6"
            v-if="season_info.seasons && season_info.seasons[0].type != 1"
        >
            <!-- Standings UI (Left Side) -->
            <div class="md:col-span-3 sm:col-span-1 overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">
                    {{ season_info?.seasons[0].name ?? "" }} Standings
                </h2>
                <table
                    class="min-w-full divide-y divide-gray-200"
                    v-if="
                        season_standings &&
                        season_standings.standings.length > 0
                    "
                >
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap"
                            >
                                Team
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap"
                            >
                                Wins
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap"
                            >
                                Loss
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap"
                            >
                                Rank
                            </th>
                            <th
                                class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap"
                            >
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-rose-200 divide-y divide-gray-200 text-bold"
                    >
                        <tr
                            v-for="(team, index) in season_standings.standings"
                            :key="index"
                            :class="
                                index <= 7
                                    ? 'bg-orange-300 text-black text-bold'
                                    : 'text-bold'
                            "
                        >
                            <td
                                class="px-2 py-2 whitespace-nowrap uppercase text-sm"
                            >
                                <button
                                    type="button"
                                    class="uppercase"
                                    :title="
                                        'Conference champions:' +
                                        team.conference_1_rank +
                                        ' Playoff Appearance:' +
                                        team.playoff_appearances
                                    "
                                    @click.prevent="
                                        isTeamModalOpen = team.team_id
                                    "
                                >
                                    <b>{{ team.team_name }}</b>
                                </button>
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap text-nowrap text-sm"
                            >
                                {{ team.wins }}
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap text-nowrap text-sm"
                            >
                                {{ team.losses }}
                            </td>
                            <td
                                class="px-2 py-2 whitespace-nowrap text-nowrap text-sm"
                            >
                                {{ team.overall_rank }}
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-sm">
                                <div class="flex space-x-1">
                                    <!-- <span
                                        v-if="team.conference_1_rank > 0"
                                        class="flex items-center justify-center w-6 h-6 bg-red-500 text-black text-sm rounded-full"
                                        title="#1 Conference Rank"
                                    >
                                        {{ team.conference_1_rank }}
                                    </span>
                                    <span
                                        v-if="team.overall_1_rank > 0"
                                        class="flex items-center justify-center w-6 h-6 bg-purple-500 text-black text-sm rounded-full"
                                        title="#1 Overall Rank"
                                    >
                                        {{ team.overall_1_rank }}
                                    </span> -->
                                    <span
                                        v-if="team.conference_championships > 0"
                                        class="flex items-center justify-center w-5 h-5 bg-yellow-500 text-black text-xs rounded-full"
                                        title="Conference Championships"
                                    >
                                        {{ team.conference_championships }}
                                    </span>
                                    <span
                                        v-if="team.finals_appearances > 0"
                                        class="flex items-center justify-center w-5 h-5 bg-green-500 text-black text-xs rounded-full"
                                        title="Finals Appearances"
                                    >
                                        {{ team.finals_appearances }}
                                    </span>
                                    <span
                                        v-if="team.championships > 0"
                                        class="flex items-center justify-center w-5 h-5 bg-red-500 text-black text-xs rounded-full"
                                        title="Championships"
                                    >
                                        {{ team.championships }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="text-center font-bold text-red-500">
                    No Standings available
                </div>
                <!-- Stats List -->
                <ul class="mt-4 uppercase" v-if="season_info.seasons">
                    <li>
                        <i class="fas fa-trophy"></i>Finals Champion:
                        {{ season_info.seasons[0].finals_winner_name }}
                    </li>
                    <li>
                        <i class="fas fa-medal"></i>Finals Runner Up:
                        {{ season_info.seasons[0].finals_loser_name }}
                    </li>
                    <li>
                        <i class="fas fa-trophy"></i> Regular Season Champion:
                        {{ season_info.seasons[0].champion_name }}
                    </li>
                    <li>
                        <i class="fas fa-bomb"></i> Weakest:
                        {{ season_info.seasons[0].weakest_name }}
                    </li>
                    <li>
                        <i class="fas fa-calendar-alt"></i> Season Name:
                        {{ season_info.seasons[0].name }}
                    </li>
                </ul>

                <div class="flex mt-4">
                    <!-- <TopPlayers
                        v-if="season_info?.seasons"
                        :season_id="season_info.seasons[0].id"
                        :conference_id="activeConferenceTab"
                        :key="topPlayersKey"
                        :round="currentRound ?? 0"
                    /> -->
                </div>
            </div>
            <!-- Schedule and Results UI (Right Side) -->
            <div class="md:col-span-4 sm:col-span-1 overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">
                    Schedule and Results ({{
                        season_schedules?.schedules?.length
                    }})
                </h2>
                <div class="flex justify-end mb-2"></div>
                <div
                    v-if="
                        season_schedules &&
                        season_schedules.schedules?.length > 0
                    "
                    class="grid md:grid-cols-2 sm:col-span-1 gap-6"
                >
                    <div
                        v-for="(game, index) in season_schedules.schedules"
                        :key="index"
                        class="bg-white shadow overflow-hidden sm:rounded-lg"
                    >
                        <div class="px-4 py-5 sm:px-6">
                            <h3
                                class="text-xs font-extrabold text-nowrap leading-6 uppercase text-gray-900"
                            >
                                {{ game.home_team_name }}
                                <br />
                                <sup class="text-red-500">vs</sup>
                                <br />
                                {{ game.away_team_name }}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-xs uppercase text-gray-500"
                            >
                                {{ "Round #" + (parseFloat(game.round) + 1) }}
                            </p>
                            <code class="mt-1 max-w-2xl text-xs text-gray-300">
                                #R{{ game.round }}-{{ game.game_id }}
                            </code>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <template
                                    v-if="
                                        game.home_score === 0 &&
                                        game.away_score === 0
                                    "
                                >
                                    <div
                                        v-if="!isHide"
                                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-1 sm:gap-4 sm:px-6"
                                    >
                                        <a
                                            href="#"
                                            class="text-sm text-blue-500 underline font-bold"
                                            @click.prevent="
                                                isGameResultModalOpen =
                                                    game.game_id
                                            "
                                            >View Result</a
                                        >
                                    </div>
                                    <div
                                        v-else
                                        class="bg-white px-4 py-3 text-center sm:grid sm:grid-cols-1 sm:gap-4 sm:px-6"
                                    >
                                        <p
                                            class="text-red-500 animate-pulse text-xs text-nowrap"
                                        >
                                            Getting results
                                        </p>
                                    </div>
                                </template>
                                <template v-else>
                                    <div
                                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                                    >
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Home
                                        </dt>
                                        <dd
                                            class="mt-1 text-sm text-gray-900 sm:col-span-2"
                                        >
                                            {{ game.home_score }}
                                        </dd>
                                    </div>
                                    <div
                                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                                    >
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Away
                                        </dt>
                                        <dd
                                            class="mt-1 text-sm text-gray-900 sm:col-span-2"
                                        >
                                            {{ game.away_score }}
                                        </dd>
                                    </div>
                                    <div
                                        class="bg-gray-200 px-4 py-1 flex justify-end sm:gap-4 sm:px-6"
                                    >
                                        <a
                                            href="#"
                                            class="text-sm text-blue-500 underline font-bold"
                                            @click.prevent="
                                                isGameResultModalOpen =
                                                    game.game_id
                                            "
                                            >View Result</a
                                        >
                                    </div>
                                </template>
                            </dl>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p class="text-gray-500">No schedule available.</p>
                </div>
            </div>
        </div>
    </div>
    <Modal :show="isTeamModalOpen" :maxWidth="'fullscreen'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isTeamModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="flex justify-start mt-5 border-b border-gray-200">
            <button
                :class="[
                    'px-4 py-2',
                    currentTab === 'info'
                        ? 'border-b-2 border-blue-500 text-blue-500'
                        : 'text-gray-500 hover:text-gray-700',
                ]"
                @click="currentTab = 'info'"
            >
                Team Info
            </button>
            <button
                :class="[
                    'px-4 py-2',
                    currentTab === 'history'
                        ? 'border-b-2 border-blue-500 text-blue-500'
                        : 'text-gray-500 hover:text-gray-700',
                ]"
                @click="currentTab = 'history'"
            >
                Team Season History
            </button>
            <button
                :class="[
                    'px-4 py-2',
                    currentTab === 'roster'
                        ? 'border-b-2 border-blue-500 text-blue-500'
                        : 'text-gray-500 hover:text-gray-700',
                ]"
                @click="currentTab = 'roster'"
            >
                Team Roster
            </button>
            <button
                :class="[
                    'px-4 py-2',
                    currentTab === 'legend'
                        ? 'border-b-2 border-blue-500 text-blue-500'
                        : 'text-gray-500 hover:text-gray-700',
                ]"
                @click="currentTab = 'legend'"
            >
                Top 10 Player
            </button>
        </div>
        <div class="mt-4">
            <TeamInfo v-if="currentTab === 'info'" :team_id="isTeamModalOpen" />
            <TeamHistory
                v-if="currentTab === 'history'"
                :team_id="isTeamModalOpen"
            />
            <TeamRoster
                v-if="currentTab === 'roster'"
                :team_id="isTeamModalOpen"
            />
        </div>
    </Modal>
    <Modal :show="isGameResultModalOpen" :maxWidth="'4xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isGameResultModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="mt-4">
            <GameResults :game_id="isGameResultModalOpen" />
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted, watch, computed } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import { roundNameFormatter } from "@/Utility/Formatter";

import TeamHistory from "@/Pages/Teams/Module/TeamHistory.vue";
import TeamInfo from "@/Pages/Teams/Module/TeamInfo.vue";
import TeamRoster from "@/Pages/Teams/Module/TeamRoster.vue";
import GameResults from "@/Pages/Seasons/Module/GameResults.vue";
import SeasonTimeLine from "@/Pages/Analytics/Module/SeasonTimeLine.vue";

const season_info = ref(false);
const season_conference = ref(false);
const season_standings = ref(false);
const season_schedules = ref(false);
const isTeamModalOpen = ref(false);
const isGameResultModalOpen = ref(false);
const isHide = ref(false);
const currentTab = ref("info");
const currentRound = ref(0);
const topPlayersKey = ref(0); // Key for TopPlayers component
const activeConferenceTab = ref(false);
const activeGameId = ref(0);
const props = defineProps({
    season_id: {
        type: [Number,String],
        required: true,
    },
});
const form = useForm({
    seasons_id: 0,
});
const fetchConferenceData = async (id) => {
    try {
        console.log("loaded");
        await fetchConferenceList(id);
        await fetchConferenceStandings(id);
        await fetchConferenceSchedules(id);
    } catch (error) {
        console.error("Error fetching season information:", error);
    }
};
const fetchConferenceList = async (id) => {
    try {
        activeConferenceTab.value = id;
        const response = await axios.post(route("conference.season.dropdown"), {
            season_id: props.season_id,
            conference_id: id,
        });
        season_conference.value = response.data;
    } catch (error) {
        console.error("Error fetching season information:", error);
    }
};
const fetchSeasonInfo = async () => {
    try {
        const response = await axios.post(route("seasons.info"), {
            season_id: props.season_id,
        });
        season_info.value = response.data;
        fetchConferenceData(season_info.value.conferences[0].id);
    } catch (error) {
        console.error("Error fetching season information:", error);
    }
};
const fetchConferenceStandings = async (id) => {
    try {
        const response = await axios.post(route("conferences.standings"), {
            season_id: props.season_id,
            conference_id: id,
        });
        season_standings.value = response.data;
    } catch (error) {
        console.error("Error fetching season standings:", error);
    }
};

const fetchConferenceSchedules = async (id) => {
    try {
        const response = await axios.post(route("conferences.schedules"), {
            season_id: props.season_id,
            conference_id: id,
        });
        season_schedules.value = response.data;
    } catch (error) {
        console.error("Error fetching season standings:", error);
    }
};

const simulatePerRound = async () => {
    const rounds = season_schedules.value.rounds;
    const lastRoundIndex = rounds.length - 1; // Get the index of the last round

    for (const [index, round] of rounds.entries()) {
        // Check if it's the last round
        const isLastRound = index === lastRoundIndex;

        // Pass an additional parameter if it's the last round
        await simulateRoundGames(round, isLastRound);
    }

};
const simulateAll = async () => {
    const rounds = season_schedules.value.rounds;
    const lastRoundIndex = rounds.length - 1; // Get the index of the last round

    for (let mode = 1; mode <= 4; mode++) {
        for (const [index, round] of rounds.entries()) {
            // Check if it's the last round
            const isLastRound = index === lastRoundIndex;

            // Pass an additional parameter if it's the last round
            await simulateAllRoundGames(round, isLastRound,mode);
        }
    }
};
const simulateAllRoundGames = async (round, isLast,conference_id) => {
    try {
        isHide.value = true;
        currentRound.value = round;
        const response = await axios.post(route("game.per.round"), {
            season_id: props.season_id, // Assuming the parameter name should be schedule_id
            round: round,
            conference_id: conference_id,
        });
        // await localStorage.setItem('season-key',generateRandomKey());
        const gameIds = response.data.schedule_ids; // Assuming the response contains 'game_ids'
        // Loop through each game ID
        for (const gameId of gameIds) {
            // Perform an action with each game ID
            console.log(`Processing Game ID: ${gameId}`);
            await simulateGame(gameId);
            // You can also add more logic here, like fetching game details or updating the state
        }

        topPlayersKey.value = round;
        if (isLast) {
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message, // Assuming the response contains a 'message' field
            });

            await fetchConferenceSchedules(activeConferenceTab.value);
            isHide.value = false;
            currentRound.value = false;
        }
    } catch (error) {
        console.error("Error simulating the game:", error);
        // Show error message using Swal2 if needed
        Swal.fire({
            icon: "warning",
            title: "Warning!",
            text: error.response.data.error,
        });
    }
};
const simulateRoundGames = async (round, isLast) => {
    try {
        isHide.value = true;
        currentRound.value = round;
        const response = await axios.post(route("game.per.round"), {
            season_id: props.season_id, // Assuming the parameter name should be schedule_id
            round: round,
            conference_id: activeConferenceTab.value,
        });
        // await localStorage.setItem('season-key',generateRandomKey());
        const gameIds = response.data.schedule_ids; // Assuming the response contains 'game_ids'
        // Loop through each game ID
        for (const gameId of gameIds) {
            // Perform an action with each game ID
            console.log(`Processing Game ID: ${gameId}`);
            await simulateGame(gameId);
            // You can also add more logic here, like fetching game details or updating the state
        }

        topPlayersKey.value = round;
        if (isLast) {
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message, // Assuming the response contains a 'message' field
            });

            await fetchConferenceSchedules(activeConferenceTab.value);
            isHide.value = false;
            currentRound.value = false;
        }
    } catch (error) {
        console.error("Error simulating the game:", error);
        // Show error message using Swal2 if needed
        Swal.fire({
            icon: "warning",
            title: "Warning!",
            text: error.response.data.error,
        });
    }
};
const simulateGame = async (schedule_id) => {
    try {
        isHide.value = true;

        const response = await axios.post(route("game.simulate.regular"), {
            schedule_id: schedule_id, // Assuming the parameter name should be schedule_id
        });
        // await localStorage.setItem('season-key',generateRandomKey());
        // isHide.value = false;
        topPlayersKey.value++; // Trigger update of TopPlayers component
        await fetchConferenceStandings(activeConferenceTab.value);
        activeGameId.value = response.data.game_id ?? 0;
        // Show success message using Swal2
        // Swal.fire({
        //     icon: "success",
        //     title: "Success!",
        //     text: response.data.message, // Assuming the response contains a 'message' field
        // });
    } catch (error) {
        console.error("Error simulating per conference:", error);
        // Show error message using Swal2 if needed
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.response.data.message,
        });
    }
};
//team modal
watch(
    () => props.season_id,
    async (n, o) => {
        if (n !== o) {
            await fetchSeasonInfo(o);
        }
    }
);
onMounted(() => {
    fetchSeasonInfo();
});
</script>
