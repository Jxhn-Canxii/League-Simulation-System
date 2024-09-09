<template>
    <div>
        <Head title="Seasons" />

        <AuthenticatedLayout>
            <template #header> Seasons </template>
            <div
                class="inline-block min-w-full bg-white overflow-auto shadow rounded p-2"
            >
                <div
                    class="flex overflow-hidden justify-end gap-5 p-2"
                    v-if="seasons.is_new_season == 1"
                >
                    <button
                        @click.prevent="updatePlayerStatus()"
                        class="px-2 py-2 bg-blue-500 rounded font-bold text-md float-end text-white shadow"
                    >
                        <i class="fa fa-users"></i> Update Player Status
                    </button>
                </div>
                <div
                    class="flex overflow-hidden justify-end gap-5 p-2"
                    v-if="seasons.is_new_season == 2 || seasons.is_new_season == 4"
                >
                    <button
                        @click.prevent="isPlayerSigningModalOpen = true"
                        v-bind:class="{
                            'opacity-25': isPlayerSigningModalOpen,
                        }"
                        v-bind:disabled="isPlayerSigningModalOpen"
                        class="px-2 py-2 bg-red-500 rounded font-bold text-md float-end text-white shadow"
                    >
                        <i class="fa fa-users"></i> Player Signings
                    </button>
                </div>
                <div
                    class="flex overflow-hidden justify-end gap-5 p-2"
                    v-if="seasons.is_new_season == 3 || seasons.is_new_season == 4"
                >
                    <button
                        @click.prevent="isAddModalOpen = true"
                        v-bind:class="{
                            'opacity-25': isAddModalOpen,
                        }"
                        v-bind:disabled="isAddModalOpen"
                        class="px-2 py-2 bg-blue-500 rounded font-bold text-md float-end text-white shadow"
                    >
                        <i class="fa fa-calendar-plus"></i> New Season
                    </button>
                </div>
                <div class="flex overflow-hidden gap-5 p-2">
                    <input
                        type="text"
                        v-model="search_seasons.search"
                        @input.prevent="fetchSeasons(1)"
                        id="LeagueName"
                        placeholder="Enter season name"
                        class="mt-1 p-2 text-md shadow border rounded-md w-full"
                    />
                </div>
                <table class="w-full whitespace-no-wrap overflow-x-auto border border-gray-200">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Season
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Finals MVP
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Finals Champion
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Finals Runner Up
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Regular Champion
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                West
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                East
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                North
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                South
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Worst
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-1 py-1 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(season, index) in seasons.seasons"
                            v-if="seasons.total_pages"
                            :key="season.id"
                            :class="[
                                season.finals_winner_id === season.champion_id
                                    ? 'bg-stone-700 text-yellow-400 font-extrabold'
                                    : '',
                                season.winner_conference_name === season.loser_conference_name
                                    ? 'bg-slate-600 text-yellow-500 font-extrabold'
                                    : '',
                                index > 0 && seasons.seasons[index - 1].champion_name === season.champion_name
                                    ? 'bg-green-200'
                                    : '',
                                'border border-gray-200',
                            ]"
                        >
                            <td class="border px-1 py-1 text-xs text-nowrap">
                                <p class="whitespace-no-wrap uppercase">{{ season.name }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <p>{{ season.finals_mvp ?? "TBD" }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <p class="font-extrabold text-yellow-500">{{ season.finals_winner_name ?? "TBD" }} ({{ season.finals_winner_score > season.finals_loser_score ? season.finals_winner_score : season.finals_loser_score }})</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <p>{{ season.finals_loser_name ?? "TBD" }} ({{ season.finals_winner_score < season.finals_loser_score ? season.finals_winner_score : season.finals_loser_score }})</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <p>{{ season.type == 1 ? "n/a" : season.champion_name }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap bg-red-100">
                                <p :class="season.finals_winner_id == season.west_champion_id ? 'font-bold text-red-500' : ''">{{ season.west_champion_name ?? "TBD" }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap bg-blue-100">
                                <p :class="season.finals_winner_id == season.east_champion_id ? 'font-bold text-blue-500' : ''">{{ season.east_champion_name ?? "TBD" }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap bg-green-100">
                                <p :class="season.finals_winner_id == season.north_champion_id ? 'font-bold text-green-500' : ''">{{ season.north_champion_name ?? "TBD" }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap bg-yellow-100">
                                <p :class="season.finals_winner_id == season.south_champion_id ? 'font-bold text-yellow-500' : ''">{{ season.south_champion_name ?? "TBD" }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <p>{{ season.type == 1 ? "n/a" : season.weakest_name }}</p>
                            </td>
                            <td class="border border-gray-200 px-1 py-1 text-xs text-nowrap">
                                <button
                                    @click.prevent="(isViewModalOpen = true), (season_id = season.id)"
                                    v-bind:class="{ 'opacity-25': isViewModalOpen }"
                                    v-bind:disabled="isViewModalOpen"
                                    class="px-1 py-1 bg-blue-500 mb-2 rounded font-bold text-xs text-white shadow"
                                >
                                    <i class="fa fa-list"></i> Season
                                </button>
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="11" class="border-b text-center font-bold text-sm border-gray-200 bg-white px-2 py-1">
                                <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex w-full overflow-auto">
                    <Paginator
                        v-if="seasons.total_count"
                        :page_number="search_seasons.page_num"
                        :total_rows="seasons.total_count ?? 0"
                        :itemsperpage="search_seasons.itemsperpage"
                        @page_num="handlePagination"
                    />
                </div>
            </div>
            <Modal :show="isViewModalOpen" :maxWidth="'fullscreen'">
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="(isViewModalOpen = false), fetchSeasons()"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-2"
                >
                    <div class="w-full mb-2 flex overflow-x-auto border-b-2">
                        <ul class="flex flex-wrap">
                            <li
                                @click="changeTab('Regular')"
                                :class="{
                                    'text-blue-500 border-b-2 border-blue-500':
                                        currentTab === 'Regular',
                                }"
                                class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                            >
                                <i
                                    class="fa fa-trophy mr-2 text-gray-500 group-hover:text-blue-500"
                                    title="Regular Season"
                                ></i>
                                <span
                                    hidden
                                    class="text-truncate hidden sm:inline md:inline"
                                    >Regular Season</span
                                >
                                <!-- Warning Badge Notification Counter -->
                                <span
                                    hidden
                                    class="bg-red-500 text-white rounded-full h-4 w-4 text-center m-1 text-xs"
                                    >6</span
                                >
                            </li>
                            <li
                                @click="changeTab('Playoffs')"
                                :class="{
                                    'text-blue-500 border-b-2 border-blue-500':
                                        currentTab === 'Playoffs',
                                }"
                                class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                            >
                                <i
                                    class="fa fa-diagram-project mr-2 text-gray-500 group-hover:text-blue-500"
                                    title="Playoffs"
                                ></i>
                                <span
                                    class="text-truncate hidden sm:inline md:inline"
                                    >Playoffs</span
                                >
                                <!-- Warning Badge Notification Counter -->
                                <span
                                    hidden
                                    class="bg-red-500 text-white rounded-full h-4 w-4 text-center m-1 text-xs"
                                    >6</span
                                >
                            </li>
                        </ul>
                    </div>
                    <!-- Modify the existing content based on the currentTab -->
                    <div
                        v-if="currentTab === 'Regular' && season_id != 0"
                        class="min-w-screen overflow-x-auto"
                    >
                        <Seasons :season_id="season_id" />
                    </div>
                    <div
                        v-if="currentTab === 'Playoffs' && season_id != 0"
                        class="min-w-screen overflow-x-auto"
                    >
                        <Playoffs :season_id="season_id" />
                    </div>
                </div>
            </Modal>
            <Modal :show="isAddModalOpen" :maxWidth="'2xl'">
                <div
                    v-if="isProcessing"
                    class="fixed inset-0 bg-black top-50 left-50 text-white text-center text-sm bg-opacity-50 z-40"
                >
                    Preparing Schedule...
                </div>
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="isAddModalOpen = false"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="relative grid grid-cols-1 gap-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        New Seasons
                    </h2>
                    <form class="mt-4" @submit.prevent="create()">
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >Name</label
                            >
                            <input
                                type="text"
                                id="FloorNo"
                                v-model="form.season_name"
                                minlength="1"
                                placeholder="Input Season Name"
                                name="FloorNo"
                                class="mt-1 p-2 border rounded-md w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.season_name"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >Type</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full bg-gray-200"
                                v-model="form.type"
                                disabled
                            >
                                <option value="0">Select Type</option>
                                <option value="1" disabled>
                                    Single Elimination
                                </option>
                                <option value="2">Single Round Robin</option>
                                <option value="3">Double Round Robin</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.type"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >Start Playoffs</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full bg-gray-200"
                                v-model="form.start"
                                disabled
                            >
                                <option value="0">Select Start</option>
                                <option value="8" disabled>
                                    on Quarter Finals
                                </option>
                                <option value="16">on Round of 16</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.type"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >Match Type</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full bg-gray-200"
                                v-model="form.match_type"
                                disabled
                            >
                                <option value="0">Select Match Type</option>
                                <option value="1">by Conference</option>
                                <option value="2" disabled>All Teams</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.type"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >League</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full bg-gray-200"
                                v-model="form.league_id"
                                disabled
                            >
                                <option value="0">Select League</option>
                                <option
                                    :value="league.id"
                                    v-for="league in leagues_dropdown"
                                    :key="league.id"
                                >
                                    {{ league.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.league_id"
                            />
                        </div>
                        <!-- Add more form fields as needed -->

                        <div class="flex items-center">
                            <button
                                type="submit"
                                :disabled="isProcessing"
                                :class="isProcessing ? 'opacity-50' : ''"
                                class="bg-blue-500 text-white font-bold py-2 px-4 rounded"
                            >
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
            <Modal :show="isPlayerSigningModalOpen" :maxWidth="'6xl'">
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="isPlayerSigningModalOpen = false"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="mt-4 p-3 block">
                    <FreeAgents @newSeason="handleNewSeason" />
                </div>
            </Modal>
        </AuthenticatedLayout>
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import { roundNameFormatter, roundGridFormatter } from "@/Utility/Formatter.js";
import Paginator from "@/Components/Paginator.vue";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

import Seasons from "@/Pages/Seasons/Module/Season.vue";
import Playoffs from "@/Pages/Seasons/Module/Playoffs.vue";
import FreeAgents from "@/Pages/Seasons/Module/FreeAgents.vue";

const isAddModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isPlayerSigningModalOpen = ref(false);
const seasons = ref([]);
const leagues_dropdown = ref([]);
const season_id = ref(0);
const isProcessing = ref(false);
const currentTab = ref("Regular"); // Set the default tab
const search_seasons = ref({
    current_page: 1,
    total_pages: 0,
    total: 0,
    search: "",
    change_key: "",
    itemsperpage: 10,
});
const form = useForm({
    type: 3,
    start: 16,
    league_id: 1,
    seasons_id: 0,
    conference_id: 0,
    match_type: 1,
    errors: [],
});
const fetchSeasons = async (page = 1) => {
    try {
        search_seasons.value.current_page = page;
        const response = await axios.post(
            route("seasons.list"),
            search_seasons.value
        );
        seasons.value = response.data;
    } catch (error) {
        console.error("Error fetching seasons:", error);
    }
};
const handlePagination = (page_num) => {
    search_seasons.value.page_num = page_num ?? 1;
    fetchSeasons();
};
const handleNewSeason = (newSeason) => {
    if (newSeason) {
        isPlayerSigningModalOpen.value = false;
    }
    fetchSeasons();
};
const leagueDropdown = async () => {
    try {
        const response = await axios.get(route("leagues.dropdown"));
        if (!response) {
            throw new Error("Failed to fetch leagues");
        }
        leagues_dropdown.value = await response.data; // Parse JSON response and assign it to the leagues array
    } catch (error) {
        console.error("Error fetching leagues:", error);
    }
};
const create = async () => {
    if (form.league_id == 0) {
        Swal.fire({
            title: "Warning!",
            text: "Please assign league!",
            icon: "warning",
        });
        return false;
    } else {
        try {
            isProcessing.value = true;
            const response = await axios.post(route("schedule.create"), form);
            isAddModalOpen.value = false;
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message, // Assuming the response contains a 'message' field
            });
            form.reset("name", "type", "league_id");
            isProcessing.value = false;
            fetchSeasons();
            seasonsDropdown();
        } catch (error) {
            console.error("Error creating schedule:", error);
            // Show error message using Swal2 if needed
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: error.response.data.message,
            });
        }
    }
};
const updatePlayerStatus = async () => {
    try {
        const team_ids = seasons.value.team_ids;
        console.log(team_ids);

        for (let i = 0; i < team_ids.length; i++) {
            const team_id = team_ids[i];
            const is_last = i === team_ids.length - 1;

            // Update player status for each team and get the response
            await updatePlayerStatusPerTeam(i,team_id, is_last);
        }
        await fetchSeasons(); // Refresh seasons after each team update
    } catch (error) {
        console.error(error);

        // Show error message using Swal2 if there's an error in the loop
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Failed to update player status for some or all teams. Please try again later.",
        });
    }
};

const updatePlayerStatusPerTeam = async (index, team_id, is_last) => {
    try {
        // isProcessing.value = true;

        form.is_last = is_last;
        form.team_id = team_id;

        // Call the update function
        const response = await axios.post(route("update.player.status"), form);

        // Extract improved, declined, and re-signed players from the response
        const improvedPlayers = response.data.improved_players || [];
        const declinedPlayers = response.data.declined_players || [];
        const reSignedPlayers = response.data.re_signed_players || [];
        const teamName = response.data.team_name || 'none';

        // Build the HTML message for Swal
        let htmlMessage = `
            <p style="font-size: 12px;">Player status for team #${index + 1} ${teamName} has been updated.</p>
            <div style="display: flex; flex-direction: column; align-items: center;">

                <!-- Improved Players Table -->
                <table style="width:90%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px;">
                    <thead>
                        <tr><th colspan="3" style="text-align: center; padding: 4px;">Improved Players</th></tr>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 4px;">Player</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Role</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Contract Years</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${improvedPlayers.map(player => `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.name}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.role}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.contract_years} years</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <!-- Declined Players Table -->
                <table style="width:90%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px;">
                    <thead>
                        <tr><th colspan="3" style="text-align: center; padding: 4px;">Declined Players</th></tr>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 4px;">Player</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Role</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Contract Years</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${declinedPlayers.map(player => `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.name}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.role}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.contract_years} years</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <!-- Re-Signed Players Table -->
                 <table style="width:90%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr><th colspan="3" style="text-align: center; padding: 4px;">Re-Signed Players</th></tr>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 4px;">Player</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Role</th>
                            <th style="border: 1px solid #ddd; padding: 4px;">Contract Years</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${reSignedPlayers.map(player => `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.name}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.role}</td>
                                <td style="border: 1px solid #ddd; padding: 4px;">${player.contract_years} years</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Show success alert with the table-like message
        Swal.fire({
            title: `Team ${teamName} Player Update`,
            html: htmlMessage,
            showConfirmButton: true,
            position: 'top', // Position the alert at the top of the screen
        });

        // Close the processing status alert

        // isAddModalOpen.value = false;
        // isProcessing.value = false;
        // Return the response to the main function
        return response;
    } catch (error) {
        console.error(error);

        // Close the processing status alert if there's an error
        // Swal.close();

        // Show error message using Swal2
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: `Failed to update player status for team ID ${team_id}. Please try again later.`,
        });

        // isProcessing.value = false;
    }
};
const seasonsDropdown = async () => {
    try {
        const response = await axios.post(route("seasons.dropdown"), {
            season_id: 0,
        });
        localStorage.setItem('seasons',JSON.stringify(response.data));
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
const changeTab = (tab) => {
    currentTab.value = tab;
};
onMounted(() => {
    fetchSeasons();
    leagueDropdown();
});
</script>
