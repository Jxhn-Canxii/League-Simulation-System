<template>
    <div>
        <Head :title="'Seasons '+ season_id" />

        <AuthenticatedLayout>
            <template #header> Seasons {{ season_id }}</template>
            <div
                class="flex min-w-screen min-h-full bg-white overflow-auto shadow rounded p-2"
            >
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg min-w-screen min-h-full p-2"
                >
                    <div class="w-screen mb-2 flex overflow-x-auto border-b-2">
                        <ul class="flex flex-wrap">
                            <li
                            class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                        >
                        <a :href="route('seasons.index')" class="px-2 py-2 bg-blue-500 rounded font-bold text-md text-white shadow">
                            <i class="fa fa-list"></i>
                            Season List
                        </a>
                        </li>
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
                            <li
                                @click="changeTab('Awards')"
                                :class="{
                                    'text-yellow-500 border-b-2 border-yellow-500':
                                        currentTab === 'Awards',
                                }"
                                class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                            >
                                <i
                                    class="fa fa-medal mr-2 text-gray-500 group-hover:text-yellow-500"
                                    title="Awards"
                                ></i>
                                <span
                                    class="text-truncate hidden sm:inline md:inline"
                                    >Awards</span
                                >
                                <!-- Warning Badge Notification Counter -->
                                <span
                                    hidden
                                    class="bg-red-500 text-white rounded-full h-4 w-4 text-center m-1 text-xs"
                                    >6</span
                                >
                            </li>
                            <li
                                @click="changeTab('Transactions')"
                                :class="{
                                    'text-yellow-500 border-b-2 border-yellow-500':
                                        currentTab === 'Transactions',
                                }"
                                class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                            >
                                <i
                                    class="fa fa-list mr-2 text-gray-500 group-hover:text-yellow-500"
                                    title="Transactions"
                                ></i>
                                <span
                                    class="text-truncate hidden sm:inline md:inline"
                                    >Transactions</span
                                >
                                <!-- Warning Badge Notification Counter -->
                                <span
                                    hidden
                                    class="bg-red-500 text-white rounded-full h-4 w-4 text-center m-1 text-xs"
                                    >6</span
                                >
                            </li>
                            <li
                                class="whitespace-nowrap group flex items-center px-3 py-2 cursor-pointer relative flex-shrink-0 max-w-xs"
                            >
                                <select
                                    v-model="season_id"
                                    class="mt-1 mb-2 p-2 border rounded w-full"
                                >
                                    <option value="0">Select Season</option>
                                    <option
                                        v-for="(season, ss) in seasons"
                                        :key="season.season_id"
                                        :value="season.season_id"
                                    >
                                        {{ season.name }}
                                    </option>
                                </select>
                            </li>
                        </ul>
                    </div>
                    <!-- Modify the existing content based on the currentTab -->
                    <div
                        v-if="currentTab === 'Regular' && season_id != 0"
                        class="min-w-full overflow-x-auto"
                    >
                        <Seasons :season_id="season_id" />
                    </div>
                    <div
                        v-if="currentTab === 'Playoffs' && season_id != 0"
                        class="min-w-full overflow-x-auto"
                    >
                        <Playoffs :season_id="season_id" />
                    </div>
                    <div
                        v-if="currentTab === 'Awards' && season_id != 0"
                        class="min-w-full overflow-x-auto"
                    >
                        <SeasonAwards :season_id="season_id" />
                    </div>
                    <div
                        v-if="currentTab === 'Transactions' && season_id != 0"
                        class="min-w-full overflow-x-auto"
                    >

                    </div>
                </div>
            </div>
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
import SeasonAwards from "./Module/SeasonAwards.vue";

const props = defineProps({
    season_id: {
        type: Number,
        default: 0,
        required: true,
    },
});
const season_id = ref(0);
const seasons = ref(0);
const currentTab = ref("Regular"); // Set the default tab
const changeTab = (tab) => {
    currentTab.value = tab;
};
const loadSeason = () => {
    season_id.value = props.season_id;
    seasonsDropdown();
};
const seasonsDropdown = async () => {
    try {
        const response = await axios.post(route("seasons.dropdown"), {
            season_id: 0,
        });
        seasons.value = response.data;
    } catch (error) {
        console.error("Error fetching team info:", error);
    }
};
onMounted(() => {
    loadSeason();
});
</script>
