<template>
    <div>
        <Head title="Leagues" />

        <AuthenticatedLayout>
            <template #header> Leagues </template>
            <div class="inline-block min-w-full bg-white overflow-hidden shadow p-2 rounded">
                <button @click.prevent="isAddModalOpen = true"
                    v-bind:class="{ 'opacity-25': isAddModalOpen }"
                    v-bind:disabled="isAddModalOpen"
                    class="px-2 py-2 bg-blue-500 rounded font-bold mb-4 text-md float-end text-white shadow">
                    <i class="fa fa-plus"></i> Add League
                </button>
                <input type="text" v-model="search_liga.search" @input.prevent="fetchLeagues()"
                    id="LeagueName" placeholder="Enter league name"
                    class="mt-1 mb-2 p-2 border rounded-md w-full" />
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                League Name
                            </th>
                            <th class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="league in liga.leagues" v-if="liga.total_pages" :key="league.id"
                            class="text-gray-700">
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ league.name }}</p>
                                <ul>
                                    <li v-for="conference in league.conferences" :key="conference.id">
                                        {{ conference.name }}
                                    </li>
                                </ul>
                            </td>
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <button @click.prevent="(isEditModalOpen = true), fillForm(league)"
                                    v-bind:class="{ 'opacity-25': isEditModalOpen }"
                                    v-bind:disabled="isEditModalOpen"
                                    class="px-2 py-2 bg-yellow-500 font-bold text-md float-center text-white shadow">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <button @click.prevent="fillForm(league), Delete()"
                                    class="px-2 py-2 bg-red-500 font-bold text-md float-center text-white shadow">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="2"
                                class="border-b text-center font-bold text-lg border-gray-200 bg-white px-5 py-5">
                                <p class="text-red-500 whitespace-no-wrap">No Data Found!</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex w-full overflow-auto">
                    <Paginator
                        v-if="liga.total_count"
                        :page_number="search_liga.page_num"
                        :total_rows="liga.total_count ?? 0"
                        :itemsperpage="search_liga.itemsperpage"
                        @page_num="handlePagination"
                    />
                </div>
            </div>
            <Modal :show="isAddModalOpen" :maxWidth="'2xl'">
                <button class="flex float-end bg-gray-100 p-3" @click.prevent="isAddModalOpen = false">
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="grid grid-cols-1 gap-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-800">Add League</h2>
                    <form class="mt-4" @submit.prevent="Add()">
                        <div class="mb-4">
                            <label for="LeagueName" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" v-model="form.name" id="LeagueName"
                                placeholder="Enter league name"
                                class="mt-1 p-2 border rounded-md w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="flex items-center">
                            <button type="submit"
                                class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Submit</button>
                        </div>
                    </form>
                </div>
            </Modal>
            <Modal :show="isEditModalOpen" :maxWidth="'2xl'">
                <button class="flex float-end bg-gray-100 p-3" @click.prevent="isEditModalOpen = false">
                    <i class="fa fa-times text-black-600"></i>
                </button>
                <div class="grid grid-cols-1 gap-6 p-6">
                    <h2 class="text-lg font-semibold text-gray-800">Edit League Information</h2>
                    <form class="mt-4" @submit.prevent="Update()">
                        <div class="mb-4">
                            <label for="LeagueName" class="block text-sm font-medium text-gray-700">League Name</label>
                            <input type="text" v-model="form.name" id="LeagueName" placeholder="Enter league name" class="mt-1 p-2 border rounded-md w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="mb-4">
                            <label for="ConferenceName" class="block text-sm font-medium text-gray-700">Conference</label>
                            <div class="flex mb-4">
                                <input type="text" v-model="form.conference" id="ConferenceName" placeholder="Enter conference name" class="flex-grow p-2 border rounded-md mr-2" />
                                <button type="button" :disabled="form.conference.length == 0" :class="form.conference.length == 0 ? 'opacity-50' : ''" class="bg-blue-500 text-white font-bold py-2 px-4 rounded" @click.prevent="addConference(form.id,form.conference)">Add</button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.conference" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Conferences</label>
                            <ul class="mt-1 p-2 border rounded-md w-full flex flex-wrap">
                                <li v-for="(conference, index) in conferences" :key="index" class="bg-blue-500 text-white font-semibold py-1 px-2 rounded-md mr-2 mb-2">{{ conference.name }} Conference</li>
                            </ul>
                        </div>
                        <div class="flex items-center">
                            <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Submit</button>
                        </div>
                    </form>
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
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const leagues = ref([]);
const liga = ref([]);
const conferences = ref([]);
const search_liga = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: '',
});
const form = useForm({
    id: 0,
    name: "",
    conference: "",
});

const fetchLeagues = async (page = 1) => {
    try {
        const response = await axios.post(route("leagues.list"), search_liga.value);
        liga.value = response.data;

    } catch (error) {
        console.error("Error fetching leagues:", error);
    }
};
const handlePagination = (page_num) => {
    search_liga.value.page_num = page_num;
    fetchLeagues();
};
const fetchConference = async (league_id) => {
    try {
        const response = await axios.post(route("conference.season.dropdown"), { league_id: league_id });
        conferences.value = response.data;

    } catch (error) {
        console.error("Error fetching leagues:", error);
    }
};

onMounted(fetchLeagues);

const Add = async () => {
    try {
        const response = await axios.post(route("leagues.add"),form);
        if (response) {
            Swal.fire({
                title: "Success!",
                text: "League added successfully.",
                icon: "success",
            });
            // Close the modal and reset form
            form.reset("name");
            isAddModalOpen.value = false;
            // Refresh leagues
            fetchLeagues();
        } else {
            throw new Error("Failed to add league");
        }
    } catch (error) {
        console.error("Error adding league:", error);
    }
};
const addConference = async (league_id,conference_name) => {
    try {
        const response = await axios.post(route("conferences.add"),{
            name: conference_name,
            league_id: league_id
        });
        if (response) {
            Swal.fire({
                title: "Success!",
                text: "Conference added successfully.",
                icon: "success",
            });
            // Close the modal and reset form
            form.reset("conference");
            fetchConference(league_id);
        } else {
            throw new Error("Failed to add league");
        }
    } catch (error) {
        console.error("Error adding league:", error);
    }
};
const Update = async () => {
    try {
        const response = await axios.post(route("leagues.update"),form);
        if (response) {
            Swal.fire({
                title: "Success!",
                text: "League info updated successfully.",
                icon: "success",
            });
            // Close the modal
            form.reset("name");
            isEditModalOpen.value = false;
            // Refresh leagues
            fetchLeagues();
        } else {
            throw new Error("Failed to update league");
        }
    } catch (error) {
        console.error("Error updating league:", error);
    }
};

const Delete = async () => {
    // Show a confirmation dialog
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to delete this league.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true,
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await axios.post(route("leagues.delete"),form);
                if (response) {
                    Swal.fire({
                        title: "Success!",
                        text: "League removed successfully.",
                        icon: "success",
                    });
                    // Refresh leagues
                    fetchLeagues();
                } else {
                    throw new Error("Failed to delete league");
                }
            } catch (error) {
                console.error("Error deleting league:", error);
            }
        }
    });
};

const fillForm = (data) => {
    form.id = data.id;
    form.name = data.name;

    fetchConference(data.id);
};
</script>
