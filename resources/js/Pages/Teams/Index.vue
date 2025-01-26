<template>
    <div>
        <Head title="Teams" />

        <AuthenticatedLayout>
            <template #header> Teams </template>
            <div
                class="inline-block min-w-full bg-white overflow-hidden rounded shadow p-2"
            >
                <button
                    @click.prevent="isAddModalOpen = true"
                    v-bind:class="{ 'opacity-25': isAddModalOpen }"
                    v-bind:disabled="isAddModalOpen"
                    class="px-2 py-2 bg-blue-500 font-bold mb-4 text-md float-end text-white rounded shadow"
                >
                    <i class="fa fa-plus"></i> Add Team
                </button>
                <input
                    type="text"
                    v-model="search_teams.search"
                    @input.prevent="fetchTeams()"
                    id="LeagueName"
                    placeholder="Enter team name"
                    class="mt-1 mb-2 p-2 border rounded w-full"
                />
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr
                            class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                        >
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                            >
                                Team Name
                            </th>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                            >
                                League
                            </th>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                            >
                                Conference
                            </th>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="team in teams.teams"
                            v-if="teams.total_pages"
                            :key="team.id"
                            class="text-gray-700"
                        >
                            <td
                                class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                            >
                                <p
                                    class="text-gray-900 whitespace-no-wrap uppercase"
                                >
                                    {{ team.name }} ({{ team.acronym }})
                                </p>
                            </td>
                            <td
                                class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                            >
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ team.league_name }}
                                </p>
                            </td>
                            <td
                                class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                            >
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ team.conference_name }} Conference
                                </p>
                            </td>
                            <td
                                class="border-b border-gray-200 bg-white px-5 py-5 text-sm"
                            >
                                <button
                                    @click.prevent="teamBehavior(team)"
                                    v-bind:class="{
                                        'opacity-25': isTeamModalOpen,
                                    }"
                                    v-bind:disabled="isTeamModalOpen"
                                    class="px-2 py-2 bg-blue-500 font-bold text-md float-center text-white shadow"
                                >
                                    <i class="fa fa-eye"></i> View
                                </button>
                                <button
                                    @click.prevent="
                                        (isEditModalOpen = true), fillForm(team)
                                    "
                                    v-bind:class="{
                                        'opacity-25': isEditModalOpen,
                                    }"
                                    v-bind:disabled="isEditModalOpen"
                                    class="px-2 py-2 bg-yellow-500 font-bold text-md float-center text-white shadow"
                                >
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <button
                                    @click.prevent="fillForm(team), Delete()"
                                    class="px-2 py-2 bg-red-500 font-bold text-md float-center text-white shadow"
                                >
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-else>
                            <td
                                colspan="4"
                                class="border-b text-center font-bold text-lg border-gray-200 bg-white px-5 py-5"
                            >
                                <p class="text-red-500 whitespace-no-wrap">
                                    No Data Found!
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex w-full overflow-auto">
                    <Paginator
                        v-if="teams.total_count"
                        :page_number="search_teams.page_num"
                        :total_rows="teams.total_count ?? 0"
                        :itemsperpage="search_teams.itemsperpage"
                        @page_num="handlePagination"
                    />
                </div>
            </div>
            <Modal :show="isAddModalOpen" :maxWidth="'2xl'">
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="isAddModalOpen = false"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="grid grid-cols-1 gap-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Add Team
                    </h2>
                    <form class="mt-4" @submit.prevent="Add()">
                        <div class="mb-4">
                            <label
                                for="LeagueName"
                                class="block text-sm font-medium text-gray-700"
                                >Name</label
                            >
                            <input
                                type="text"
                                v-model="form.name"
                                id="LeagueName"
                                placeholder="Enter team name"
                                class="mt-1 p-2 border rounded-md w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="LeagueName"
                                class="block text-sm font-medium text-gray-700"
                                >Acronym</label
                            >
                            <input
                                type="text"
                                v-model="form.acronym"
                                id="LeagueName"
                                maxlength="4"
                                placeholder="Enter team Acronym"
                                class="mt-1 p-2 border rounded-md w-full uppercase"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.acronym"
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
                                class="mt-1 p-2 border rounded-md w-full"
                                v-model="form.league_id" @change.prevent="conferenceDropdown(form.league_id)"
                            >
                                <option value="0">Select League</option>
                                <option
                                    :value="league.id"
                                    v-for="league in leagues"
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
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >League</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full"
                                v-model="form.conference_id"
                            >
                                <option value="0">Select conference</option>
                                <option
                                    :value="conference.id"
                                    v-for="conference in conferences"
                                    :key="conference.id"
                                >
                                    {{ conference.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.conference_id"
                            />
                        </div>
                        <div class="flex items-center">
                            <button
                                type="submit"
                                class="bg-blue-500 text-white font-bold py-2 px-4 rounded"
                            >
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
            <Modal :show="isEditModalOpen" :maxWidth="'2xl'">
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="isEditModalOpen = false"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="grid grid-cols-1 gap-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Add Team
                    </h2>
                    <form class="mt-4" @submit.prevent="Update()">
                        <div class="mb-4">
                            <label
                                for="LeagueName"
                                class="block text-sm font-medium text-gray-700"
                                >Name</label
                            >
                            <input
                                type="text"
                                v-model="form.name"
                                id="LeagueName"
                                placeholder="Enter team name"
                                class="mt-1 p-2 border rounded-md w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                for="LeagueName"
                                class="block text-sm font-medium text-gray-700"
                                >Acronym</label
                            >
                            <input
                                type="text"
                                v-model="form.acronym"
                                id="LeagueName"
                                maxlength="4"
                                placeholder="Enter team Acronym"
                                class="mt-1 p-2 border rounded-md w-full uppercase"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.acronym"
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
                                class="mt-1 p-2 border rounded-md w-full"
                                v-model="form.league_id" @change.prevent="conferenceDropdown(form.league_id)"
                            >
                                <option value="0">Select League</option>
                                <option
                                    :value="league.id"
                                    v-for="league in leagues"
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
                        <div class="mb-4">
                            <label
                                for="FloorNo"
                                class="block text-sm font-medium text-gray-700"
                                >League</label
                            >
                            <select
                                name=""
                                id=""
                                class="mt-1 p-2 border rounded-md w-full"
                                v-model="form.conference_id"
                            >
                                <option value="0">Select conference</option>
                                <option
                                    :value="conference.id"
                                    v-for="conference in conferences"
                                    :key="conference.id"
                                >
                                    {{ conference.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.conference_id"
                            />
                        </div>
                        <div class="flex items-center">
                            <button
                                type="submit"
                                class="bg-blue-500 text-white font-bold py-2 px-4 rounded"
                            >
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
            <Modal :show="isTeamModalOpen" :maxWidth="'fullscreen'">
                <button
                    class="flex float-end bg-gray-100 p-3"
                    @click.prevent="isTeamModalOpen = false"
                >
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="flex justify-start mt-5 border-b border-gray-200">
                    <button
                        :class="['px-4 py-2', currentTab === 'info' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                        @click="currentTab = 'info'"
                    >
                        Team Info
                    </button>
                    <button
                        :class="['px-4 py-2', currentTab === 'history' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                        @click="currentTab = 'history'"
                    >
                        Team Season History
                    </button>
                    <button
                        :class="['px-4 py-2', currentTab === 'roster' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                        @click="currentTab = 'roster'"
                    >
                        Team Roster
                    </button>
                    <button
                        :class="['px-4 py-2', currentTab === 'timeline' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                        @click="currentTab = 'timeline'"
                    >
                        Season Timeline
                    </button>
                    <button
                    :class="['px-4 py-2', currentTab === 'legend' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500 hover:text-gray-700']"
                    @click="currentTab = 'legend'"
                >
                    Top 15 Players
                </button>
                </div>
                <div class="mt-4">
                    <TeamInfo :key="currentTab" v-if="currentTab === 'info'" :team_id="teamForm.team_id" />
                    <TeamHistory :key="currentTab"  v-if="currentTab === 'history'" :team_id="teamForm.team_id" />
                    <TeamRoster :key="currentTab" v-if="currentTab === 'roster'" :team_id="teamForm.team_id" />
                    <Top10Player :key="currentTab" v-if="currentTab === 'legend'" :team_id="teamForm.team_id" />
                    <SeasonTimeLine :key="currentTab" v-if="currentTab === 'timeline'" :teamId="teamForm.team_id" />
                </div>
            </Modal>
        </AuthenticatedLayout>
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";
import InputError from "@/Components/InputError.vue";
import { roundNameFormatter } from "@/Utility/Formatter";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

import TeamHistory from "./Module/TeamHistory.vue";
import TeamInfo from "./Module/TeamInfo.vue";
import TeamRoster from "./Module/TeamRoster.vue";
import Top10Player from "./Module/Top10Player.vue";
import SeasonTimeLine from "../Analytics/Module/SeasonTimeLine.vue";

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isTeamModalOpen = ref(false);
const currentTab  = ref('info');
const currentRosterTab = ref('roster');
const leagues = ref(false);
const conferences = ref(false);
const currentPage = ref(1);
const teams = ref([]);

const search_teams = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: "",
    itemsperpage: 10,
});
const form = useForm({
    id: 0,
    name: "",
    acronym: "",
    league_id: 0,
    conference_id: 0,
});

const teamForm = useForm({
    team_id: 0,
    team_name: '',
});
onMounted(() => {
    fetchTeams();
    leagueDropdown();
});
const teamBehavior = (data) => {
    teamForm.team_name = data.name;
    teamForm.team_id = data.id;
    isTeamModalOpen.value = true;
}
const fetchTeams = async () => {
    try {
        const response = await axios.post(route("teams.list"), search_teams.value);
        teams.value = response.data;
    } catch (error) {
        console.error("Error fetching teams:", error);
    }
};
const handlePagination = (page_num) => {
    search_teams.value.page_num = page_num ?? 1;
    fetchTeams();
};
const leagueDropdown = async () => {
    try {
        const response = await axios.get(route("leagues.dropdown")); // Fetch data from the API endpoint
        leagues.value = response.data; // Parse JSON response and assign it to the leagues array
    } catch (error) {
        console.error("Error fetching leagues:", error);
    }
};
const conferenceDropdown = async (league_id) => {
    try {
        const response = await axios.post(route("conference.season.dropdown"),{league_id : league_id}); // Fetch data from the API endpoint
        conferences.value = response.data; // Parse JSON response and assign it to the league conference array
    } catch (error) {
        console.error("Error fetching leagues:", error);
    }
};

const Add = async () => {
    try {
        const response = await axios.post(route("teams.add"), form);
        if (response) {
            Swal.fire({
                title: "Success!",
                text: "Team added successfully.",
                icon: "success",
            });
            // Close the modal and reset form
            form.reset("name");
            // Refresh leagues
            fetchTeams();
        } else {
            Swal.fire({
                title: "Warning!",
                text: response.data.message,
                icon: "warning",
            });
        }
        isAddModalOpen.value = false;
    } catch (error) {
        console.error("Error adding team:", error);
        Swal.fire({
            title: "Warning!",
            text: error.response.data.message,
            icon: "warning",
        });
    }
};

const Update = async () => {
    try {
        const response = await axios.post(route("teams.update"), form);
        if (response) {
            Swal.fire({
                title: "Success!",
                text: "Team info updated successfully.",
                icon: "success",
            });
            // Close the modal
            form.reset("name");
            // Refresh leagues
            fetchTeams();
        } else {
            Swal.fire({
                title: "Warning!",
                text: response.data.message,
                icon: "warning",
            });
        }
        isEditModalOpen.value = false;
    } catch (error) {
        console.error("Error updating team:", error);
         Swal.fire({
            title: "Warning!",
            text: error.response.data.message,
            icon: "warning",
        });
    }
};

const Delete = async () => {
    // Show a confirmation dialog
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to delete this team.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true,
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await axios.post(route("teams.delete"), form);
                if (response) {
                    Swal.fire({
                        title: "Success!",
                        text: "Team removed successfully.",
                        icon: "success",
                    });
                    // Refresh leagues
                    fetchTeams();
                } else {
                    throw new Error("Failed to delete team");
                }
            } catch (error) {
                console.error("Error deleting team:", error);
            }
        }
    });
};

const fillForm = (data) => {
    form.id = data.id;
    form.name = data.name;
    form.acronym = data.acronym;
    form.league_id = data.league_id;
    form.conference_id = data.conference_id;

    conferenceDropdown(data.league_id);
};
</script>
