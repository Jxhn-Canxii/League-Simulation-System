<template>
    <div class="draft-board">
        <h2 class="text-xl font-semibold text-gray-800">Draft Board</h2>

        <!-- Divider -->
        <hr class="my-4 border-t border-gray-200" />
        <div class="mb-4">
            <TopStatistics :key="key" />
        </div>
        <!-- Available Players Section -->
        <div class="flex justify-end space-x-2">
            <button
                @click.prevent="addMultiplePlayers(200)"
                class="px-2 py-2 bg-green-500 rounded font-bold text-md float-end text-white shadow"
            >
                <i class="fa fa-user"></i> Add Rookie Player From Api
            </button>
            <button
                v-if="!isHide"
                @click.prevent="draftPlayer()"
                class="px-2 py-2 bg-blue-500 rounded font-bold text-md float-end text-white shadow"
            >
                <i class="fa fa-users"></i> Draft
            </button>
        </div>

        <h3 class="text-lg font-semibold text-gray-800">Draft Results</h3>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8" v-if="draftOrder.length > 0">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Round #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Pick #
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Team
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="player in draftOrder"
                        :key="player.id"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.round }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.pick }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.team_name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="overflow-x-auto mb-8" v-if="draftResults.length > 0">
            <!-- Tabs for Rounds -->
            <div class="flex border-b mb-4">
                <button
                    class="px-4 py-2 text-sm font-medium"
                    :class="selectedRound === 1 ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
                    @click="selectedRound = 1"
                >
                    Round 1
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium"
                    :class="selectedRound === 2 ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
                    @click="selectedRound = 2"
                >
                    Round 2
                </button>
            </div>

            <!-- Round 1 Table -->
            <div v-if="selectedRound === 1">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Round #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Pick #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="player in draftResults.filter(player => player.round === 1)" :key="player.id" class="hover:bg-gray-100">
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.round }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.pick_number }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Round 2 Table -->
            <div v-if="selectedRound === 2">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-nowrap">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Round #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Pick #</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="player in draftResults.filter(player => player.round === 2)" :key="player.id" class="hover:bg-gray-100">
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.round }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.pick_number }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.player_name }}</td>
                            <td class="px-2 py-1 whitespace-nowrap border">{{ player.name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Available Players Section -->
        <h3 class="text-lg font-semibold text-gray-800">Available Players</h3>
        <hr class="my-4 border-t border-gray-200" />
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Name
                        </th>
                        <th
                            class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Overall Rating
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="player in availablePlayers.rookies"
                        :key="player.id"
                        class="hover:bg-gray-100"
                    >
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.name }}
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            {{ player.overall_rating }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex w-full overflow-auto">
            <Paginator
                v-if="availablePlayers.total"
                :page_number="search.page_num"
                :total_rows="availablePlayers.total ?? 0"
                :itemsperpage="search.itemsperpage"
                @page_num="handlePagination"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

import Paginator from "@/Components/Paginator.vue";
import TopStatistics from "@/Pages/Analytics/Module/TopStatistics.vue";

const emits = defineEmits(["newSeason"]);
const teams = ref([]);
const availablePlayers = ref([]);
const draftOrder = ref([]);
const draftResults = ref([]);
const selectedRound = ref(1);
const isHide = ref(true);
const key = ref(0);
const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: "",
    itemsperpage: 10,
});

onMounted(async () => {
    await fetchDraftOrder();
    await fetchAvailablePlayers();
});

const fetchAvailablePlayers = async () => {
    try {
        const response = await axios.post(route("draft.list"), search.value); // Update with your API endpoint
        availablePlayers.value = response.data;

    } catch (error) {
        console.error("Error fetching available players:", error);
    }
};
const handlePagination = (page_num) => {
    search.value.page_num = page_num;
    fetchAvailablePlayers();
};
const fetchDraftOrder = async () => {
    try {
        const response = await axios.get(route("draft.orders")); // Update with your API endpoint
        draftOrder.value = response.data.draft_order;
        isHide.value = false;
    } catch (error) {
        console.error("Error fetching teams:", error);
    }
};
const fetchDraftResults = async () => {
    try {
        draftOrder.value = [];
        const response = await axios.get(route("draft.results")); // Update with your API endpoint
        draftResults.value = response.data.draft_results;
    } catch (error) {
        console.error("Error fetching draft history:", error);
    }
};

const selectPlayer = (player) => {
    // Logic to handle player selection
    console.log("Selected player:", player);
    // Call API to draft the player
};

const draftPlayer = async () => {
    try {
        const response = await axios.post(route("draft.players")); // Update with your API endpoint

        if(response){
              // Show success alert
            await Swal.fire({
                title: 'Success!',
                text: 'Player drafted successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            });

            await fetchDraftResults();
            await fetchAvailablePlayers();

            isHide.value = true;
            emits("newSeason", Math.random());
        }
    } catch (error) {
        console.error("Error fetching draft history:", error);

        // Show error alert
        await Swal.fire({
            title: 'Error!',
            text: error.response.data.message,
            icon: 'error',
        });
    }
};
const fetchRandomFullName1 = async () => {
    try {
        // https://randomuser.me/api/?inc=name,gender,location,nat&gender=male
        const response = await axios.get(' https://randomuser.me/api/?inc=name,gender,location,nat&gender=male'); // API URL for random male user
        const { first, last } = response.data.results[0].name; // Extract first and last name
        const { city, state, country} = response.data.results[0].location; // Extract first and last name
        const nationality = response.data.results[0].nat; // Extract first and last name
        const address = `${city}, ${state}, ${country}`; // Extract first and last name
        const name = `${first} ${last}`;
        const country_formatted = `${country} ,${nationality}`;
        const data = {
            name: name,
            country: country_formatted,
            address: address,
        };
        // Function to check if a name contains only English alphabet letters
        const isEnglishReadable = (name) => /^[A-Za-z]+$/.test(name);

        if (isEnglishReadable(first) && isEnglishReadable(last)) {
            return data; // Return full name if valid
        } else {
            return null; // Return null if the name is not valid
        }
    } catch (error) {
        console.error("Error fetching random player name:", error);
        return null; // Return null on error
    }
};
const fetchRandomFullName2 = async () => {
    try {
        // Fetch a random user from the Random Data API and specify the gender as male
        const response = await axios.get('https://random-data-api.com/api/v2/users?size=1&gender=male');

        // Extract the first name, last name, and country information
        const { first_name, last_name, address } = response.data[0];
        const country = address.country;

        // Format the full name and country information
        const fullName = `${first_name} ${last_name}`;
        const countryFormatted = `${country}`;

        const data = {
            name: fullName,
            country: countryFormatted,
            address: country,
        };

        // Function to check if both first and last names contain only English alphabet letters
        const isEnglishReadable = (name) => /^[A-Za-z]+$/.test(name);

        if (isEnglishReadable(first_name) && isEnglishReadable(last_name)) {
            return data; // Return full name if valid
        } else {
            return null; // Return null if the name is not valid
        }
    } catch (error) {
        console.error("Error fetching random player name:", error);
        return null; // Return null on error
    }
};
const addMultiplePlayers = async (count) => {
    try {
        const promises = [];

        for (let i = 0; i < count; i++) {
            // Randomly choose between fetchRandomFullName1 or fetchRandomFullName2
            const fetchRandomFullName = Math.random() < 0.5 ? fetchRandomFullName1 : fetchRandomFullName2; // 50% chance for each

            const randomFullName = await fetchRandomFullName1(); // Fetch random full name
            if (randomFullName != null) {
                promises.push(addPlayer(randomFullName)); // Add the promise to the array
                key.value = i;
            }
        }


        // Wait for all promises to resolve
        const results = await Promise.all(promises);
        fetchAvailablePlayers(); // Refresh free agent list
        // Notify success
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: `Successfully added ${count} players.`,
        });

        // Optionally log results
        results.forEach((message, index) => {
            console.log(`Player ${index + 1}: ${message}`);
        });

    } catch (error) {
        console.error("Error adding multiple players:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.message, // Show the first error message encountered
        });
    }
};

const addPlayer = async (info) => {
    try {
        const response = await axios.post(route("players.add.free.agent"), {
            name: info.name,
            address: info.address,
            country: info.country,
        });

        // return response.data.message; // Return success message for logging
        Swal.fire({
            icon: "success",
            title: info.name + ' has added to Draft Pool!',
            text: response.data.message, // Assuming the response contains a 'message' field
        });

    } catch (error) {
        console.error("Error adding player:", error.response.data.message);
        throw new Error(error.response.data.message); // Throw error to be caught in Promise.all
    }
};
</script>

<style scoped>
.draft-board {
    padding: 16px;
}
.team-card {
    background: #f9fafb; /* Tailwind gray-50 */
}
</style>
