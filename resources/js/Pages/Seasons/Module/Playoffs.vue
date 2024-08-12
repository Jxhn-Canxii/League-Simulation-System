<template>
    <div
        class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6 border-b-2 border-dashed"
        v-if="season_info.seasons && season_info.seasons[0].status > 1"
    >
        <div class="md:col-span-4 overflow-y-auto">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Playoffs</h2>
            <div
                class="flex justify-center"
                v-if="season_info.seasons && season_info.seasons[0].status == 2"
            >
                <button
                    v-if="!isHide"
                    @click="createPlayOffSchedule('start')"
                    class="text-white bg-red-500 bg-gradient-to-br p-3 shadow rounded-full font-bold text-md text-nowrap hover:text-indigo-900"
                >
                    Start Play-offs
                </button>
                <div class="flex justify-center" v-else>
                    <p class="text-red-500 animate-pulse">
                        Preparing Playoff Schedules
                    </p>
                </div>
            </div>

            <div
                class="flex justify-center text-red-500 pt-4"
                v-if="season_info.seasons && season_info.seasons[0].status == 2"
            >
                <small>Please click to start play-offs simulation!</small>
            </div>
            <!-- Display playoff tree -->
            <div class="grid grid-cols-1 gap-6" v-if="season_playoffs.playoffs">
                <div
                    v-for="(
                        roundMatches, roundName
                    ) in season_playoffs.playoffs"
                    :key="roundName"
                    class="block"
                >
                    <h3
                        v-if="season_playoffs.playoffs[roundName].length > 0"
                        class="text-lg font-semibold text-orange-400 mt-4"
                    >
                        {{ roundNameFormatter(roundName) }}
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                    >
                        <div
                            v-for="(match, mm) in season_playoffs.playoffs[
                                roundName
                            ]"
                            :key="match.game_id"
                            class="col-span-1"
                        >
                            <div
                                class="shadow-md rounded-md overflow-hidden"
                            >
                                <div
                                :class="getConferenceClass(match.home_team.conference,match.away_team.conference)"
                                class="px-4 py-5 sm:px-6">
                                    <h3
                                        class="text-xs font-extrabold text-nowrap uppercase leading-6 text-gray-800"
                                    >
                                        #{{ match.home_team.overall_rank }}
                                        <a href="#" class="text-blue-500" @click.prevent="isTeamModalOpen = match.home_team.id"><u>{{ match.home_team.name || "TBD" }}</u></a>
                                        <sup class="text-red-500 font-bold">vs</sup>
                                        #{{ match.away_team.overall_rank }}
                                        <a href="#" class="text-blue-500" @click.prevent="isTeamModalOpen = match.away_team.id"><u>{{ match.away_team.name || "TBD" }}</u></a>
                                    </h3>
                                    <p
                                        class="mt-1 text-xs uppercase text-gray-500"
                                    >
                                        {{ roundNameFormatter(roundName) }}
                                    </p>
                                </div>
                                <div class="border-gray-200 flex justify-between">
                                    <div class="bg-white px-4 text-nowrap text-gray-600 text-xs py-3">
                                        {{ match.home_team.conference }} #{{ match.home_team.conference_rank }} vs {{ match.away_team.conference }} #{{ match.away_team.conference_rank }}
                                    </div>
                                    <div class="bg-white px-4 text-nowrap text-red-600 text-xs py-3 flex items-center">
                                        <button class="button" @click.prevent="compareTeams(match.home_team.id, match.away_team.id)">
                                            Compare
                                            <i class="fa fa-exchange-alt ml-1"></i> <!-- Font Awesome icon -->
                                        </button>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200">
                                    <dl
                                        v-if="
                                            (match.home_team.score === 0 &&
                                            match.away_team.score === 0) ||  (match.home_team.score ==  match.away_team.score)
                                        "
                                    >
                                        <div
                                            v-if="!isHide || activeIndex != mm"
                                            class="bg-white px-4 py-3 flex justify-between sm:gap-4 sm:px-6"
                                        >
                                            <button
                                                @click="
                                                    simulateGame(
                                                        match.id,
                                                        2,
                                                        mm,
                                                        roundName
                                                    )
                                                "
                                                class="text-nowrap text-indigo-600 font-bold text-sm hover:text-indigo-900"
                                            >
                                                Simulate Game {{ match.home_team.score ==  match.away_team.score && (match.home_team.score != 0 &&
                                                    match.away_team.score != 0) ? '(Overtime)' : '' }}
                                            </button>
                                            <a href="#" class="text-sm text-green-500 underline font-bold" @click.prevent="isGameResultModalOpen = match.game_id">View Result</a>
                                        </div>
                                        <div
                                            v-else
                                            class="bg-white px-4 py-3 text-center text-nowrap sm:grid sm:grid-cols-1 sm:gap-4 sm:px-6"
                                        >
                                            <p
                                                class="text-red-500 animate-pulse text-xs"
                                            >
                                                Getting results
                                            </p>
                                        </div>
                                    </dl>
                                    <dl v-else>
                                        <div
                                            class="bg-gray-100 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                                        >
                                            <dt
                                                class="text-sm font-medium text-gray-500"
                                            >
                                                Home
                                            </dt>
                                            <dd
                                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                                                :class="
                                                    match.away_team.score <
                                                    match.home_team.score
                                                        ? 'font-bold'
                                                        : ''
                                                "
                                            >
                                                {{ match.home_team.score }}
                                                <!-- Medal icon for winner -->
                                                <span
                                                    v-if="
                                                        match.home_team.score >
                                                        match.away_team.score
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
                                                class="mt-1 text-sm text-gray-900 sm:col-span-2"
                                                :class="
                                                    match.away_team.score >
                                                    match.home_team.score
                                                        ? 'font-bold'
                                                        : ''
                                                "
                                            >
                                                {{ match.away_team.score }}
                                                <!-- Medal icon for winner -->
                                                <span
                                                    v-if="
                                                        match.away_team.score >
                                                        match.home_team.score
                                                    "
                                                    class="ml-2 text-yellow-500"
                                                >
                                                    <i class="fas fa-medal"></i>
                                                </span>
                                            </dd>
                                        </div>
                                        <div
                                         class="bg-white px-4 py-3 text-center text-nowrap sm:grid sm:grid-cols-1 sm:gap-4 sm:px-6"
                                        >
                                            <a href="#" class="text-sm text-green-500 underline font-bold" @click.prevent="isGameResultModalOpen = match.game_id">View Result</a>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex justify-end"
                        v-if="
                            !isHide &&
                            season_playoffs.playoffs[roundName].length > 0
                        "
                    >
                        <button
                            v-if="
                                season_info.seasons[0].status ==
                                    roundGridFormatter(roundName,season_info.seasons[0].start_playoffs) &&
                                season_playoffs.playoffs[roundName].length >
                                    0 &&
                                roundName != 'finals'
                            "
                            @click="createPlayOffSchedule(roundName)"
                            class="text-indigo-600 font-bold text-md flex bg-orange-200 shadow p-1 rounded-full hover:text-indigo-900 mt-4"
                        >
                            End
                            {{ roundNameFormatter(roundName) }}
                        </button>

                    </div>
                    <!-- <div class="flex justify-end" v-else>
                        <button
                            disabled
                            class="text-indigo-600 animate-pulse font-bold text-md flex bg-orange-200 shadow p-1 rounded-full hover:text-indigo-900 mt-4"
                        >
                            Preparing...
                        </button>
                    </div> -->
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
                :class="['px-4 py-2', currentTab === 'history' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                @click="currentTab = 'history'"
            >
                Team Season History
            </button>
            <button
                :class="['px-4 py-2', currentTab === 'info' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                @click="currentTab = 'info'"
            >
                Team Info
            </button>
        </div>
        <div class="mt-4">
            <TeamInfo v-if="currentTab === 'info'" :team_id="isTeamModalOpen" />
            <TeamHistory v-if="currentTab === 'history'" :team_id="isTeamModalOpen" />
        </div>
    </Modal>
    <Modal :show="isTeamComparisonModalOpen" :maxWidth="'6xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isTeamComparisonModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>

        <div class="mt-4">
            <TeamComparison :home_id="comparison.home_id" :away_id="comparison.away_id" :season_id="comparison.season_id" />
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
import { ref, onMounted, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import axios from "axios";
import {
    roundNameFormatter,
    roundGridFormatter,
    roundStatusFormatter,
} from "@/Utility/Formatter.js";

import TeamHistory from "../Teams/Module/TeamHistory.vue";
import TeamInfo from "../Teams/Module/TeamInfo.vue";
import TeamComparison from "../Teams/Module/TeamComparison.vue";
import GameResults from "./GameResults.vue";

const isAddModalOpen = ref(false);
const isTeamModalOpen = ref(false);
const isTeamComparisonModalOpen  = ref(false);
const isGameResultModalOpen = ref(false);
const currentTab = ref("history");
const change_key = ref(localStorage.getItem("season-key"));
const isHide = ref(false);
const activeIndex = ref(0);
const season_info = ref(false);
const season_playoffs = ref(false);

const form = useForm({
    seasons_id: 0,
});
const comparison = useForm({
    season_id: 0,
    home_id: 0,
    away_id: 0,
});
const props = defineProps({
    season_id: {
        type: Number,
        required: true
    }
});
const compareTeams = (home_id,away_id) => {
    comparison.season_id = props.season_id;
    comparison.home_id = home_id;
    comparison.away_id = away_id;

    isTeamComparisonModalOpen.value = true;
}
const createPlayOffSchedule = async (round) => {
    try {
        let start_playoffs = season_info.value.seasons[0].start_playoffs;
        round = roundStatusFormatter(round,start_playoffs);
        const response = await axios.post(route("season.playoff.schedule"), {
            season_id: form.seasons_id, // Assuming the parameter name should be schedule_id
            round: round,
            start: start_playoffs,
        });
        isHide.value = true;
        await fetchSeasonInfo(form.seasons_id);
        await fetchSeasonPlayoffs(2);
        isHide.value = false;
        isAddModalOpen.value = false;
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: response.data.message, // Assuming the response contains a 'message' field
        });
    } catch (error) {
        console.error("Error simulating the game:", error);
        // Show error message using Swal2 if needed
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Failed to simulate the game. Please try again later.",
        });
    }
};
///functions that can trigger a change value of change_key
const fetchSeasonInfo = async (id) => {
    try {
        form.seasons_id = id;
        const response = await axios.post(route("seasons.info"), {
            season_id: form.seasons_id,
        });
        season_info.value = response.data;
        fetchSeasonPlayoffs(1);
    } catch (error) {
        console.error("Error fetching season information:", error);
    }
};
const fetchSeasonPlayoffs = async (type) => {
    try {
        let status = season_info.value.seasons[0].status;
        let start_playoffs = season_info.value.seasons[0].start_playoffs;
        const response = await axios.post(route("conferences.playoffs"), {
            season_id: form.seasons_id,
            type: type,
            status: status,
            start: start_playoffs,
        });

        if (type === 2) {
            // Log the type of season_playoffs.value.playoffs before pushing
            console.log(
                "Type of season_playoffs.value.playoffs:",
                typeof season_playoffs.value.playoffs
            );

            // Ensure season_playoffs.value.playoffs is an object before updating
            if (
                typeof season_playoffs.value.playoffs !== "object" ||
                season_playoffs.value.playoffs === null
            ) {
                season_playoffs.value.playoffs = {}; // Initialize as an empty object if it's not already an object
            }
            // Update season_playoffs.value.playoffs with response.data.playoffs
            season_playoffs.value.playoffs = {
                ...season_playoffs.value.playoffs,
                ...response.data.playoffs,
            };
        } else {
            // Simply update season_playoffs.value with response data if type is not 2
            season_playoffs.value = response.data;
        }
    } catch (error) {
        console.error("Error fetching season playoffs:", error);
    }
};
const simulateGame = async (id,type,index,round) => {
    try {
        isHide.value = true;
        activeIndex.value = index;
        const response = await axios.post(route("game.simulate"), {
            schedule_id: id, // Assuming the parameter name should be schedule_id
        });

        season_playoffs.value.playoffs[round][index] = response.data.schedule;
        // Show success message using Swal2
        // Swal.fire({
        //     icon: "success",
        //     title: "Success!",
        //     text: response.data.message, // Assuming the response contains a 'message' field
        // });
        isHide.value = false;
    } catch (error) {
        console.error("Error simulating the game:", error);
        // Show error message using Swal2 if needed
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Failed to simulate the game. Please try again later.",
        });
    }
};
const getConferenceClass = (home_conference, away_conference) => {
    // Define Tailwind classes for each conference
    const conferenceClasses = {
        'North': 'bg-blue-100',
        'South': 'bg-green-100',
        'East': 'bg-yellow-100',
        'West': 'bg-red-100',
    };

    // Check if the home and away conferences are different
    if (home_conference !== away_conference) {
        return 'bg-yellow-100'; // Color when conferences do not match
    }

    // Return the Tailwind class for the home conference
    return conferenceClasses[home_conference] || 'bg-gray-100'; // Default color if conference is not found
};

watch(() => props.season_id, async (n, o) => {
    if (n !== o) {
        await fetchSeasonInfo(o);
    }
});
onMounted(() => {
    fetchSeasonInfo(props.season_id)
});
</script>
