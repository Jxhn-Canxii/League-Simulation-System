<template>
    <div class="draft-board">
        <h2 class="text-xl font-semibold text-gray-800">Trade Proposal</h2>

        <!-- Show 'Generate Proposal' button if proposals are empty -->
        <div v-if="proposals.length === 0 && current_season > 1" class="flex justify-center items-center p-4 mt-4 bg-white shadow-md rounded-lg">
            <button 
                @click="generateTradeProposal" 
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Generate Proposal
            </button>
        </div>
        <div v-if="current_season == 1 || proposals.length > 0" class="text-right mb-4 mt-4">
            <button 
                @click="endTrade" 
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                End Trade
            </button>
        </div>
        <!-- Display list of proposals -->
        <div v-if="proposals.length > 0  && current_season > 2" >
            <!-- Show 'End Trade' button if there are proposals -->
            <!-- Tabs for categorizing proposals by role -->
            <div class="flex mb-4 space-x-4">
                <button 
                    v-for="(category, index) in categories" 
                    :key="index" 
                    @click="selectCategory(category)" 
                    :class="['px-4 py-2 rounded', selectedCategory === category ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800']">
                    {{ category }}
                </button>
            </div>

            <!-- Display proposals by selected category -->
            <div v-if="proposalsByCategory[selectedCategory].length > 0">
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(proposal, index) in proposalsByCategory[selectedCategory]" :key="proposal.id" class="border p-4 rounded-lg bg-white shadow-md">
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex-1 p-4 bg-gray-50 rounded-lg shadow-md">
                                <h4 class="text-center font-semibold text-lg">{{ proposal.player_from_name }}</h4>
                                <p class="text-center text-gray-500">{{ proposal.from_team }} to {{ proposal.to_team }}</p>
                                <p class="text-center text-gray-500">{{ proposal.player_from_role }}</p>
                                <div class="flex justify-center items-center mt-2">
                                    <a href="#" @click.prevent="showProfile(proposal.player_from_id)" class="text-blue-500 underline">View Profile</a>
                                </div>
                            </div>

                            <div class="mx-4 flex items-center">
                                <span class="text-gray-600 font-semibold">→</span>
                            </div>

                            <div class="flex-1 p-4 bg-gray-50 rounded-lg shadow-md">
                                <h4 class="text-center font-semibold text-lg">{{ proposal.player_to_name }}</h4>
                                <p class="text-center text-gray-500">{{ proposal.to_team }} to {{ proposal.from_team }}</p>
                                <p class="text-center text-gray-500">{{ proposal.player_to_role }}</p>
                                <div class="flex justify-center items-center mt-2">
                                    <a href="#" @click.prevent="showProfile(proposal.player_to_id)" class="text-blue-500 underline">View Profile</a>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm">{{ new Date(proposal.created_at).toLocaleString() }}</p>
                        <div class="mt-2 flex justify-end space-x-4">
                            <button 
                                @click="approveProposal(proposal.id)" 
                                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                                Approve
                            </button>
                            <button 
                                @click="rejectProposal(proposal.id)" 
                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Show if no proposals available for the selected category -->
            <div v-else>
                <p class="text-center text-gray-500">No trade proposals available for this category.</p>
            </div>
        </div>
    </div>

    <!-- Modal for Player Profile -->
    <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="showPlayerProfileModal = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="p-6 block">
            <!-- Image Section -->
            <PlayerPerformance v-if="selectedPlayer" :key="selectedPlayer" :player_id="selectedPlayer" />
        </div>
    </Modal>
</template>
<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";

const emits = defineEmits(["newSeason"]);
const showPlayerProfileModal = ref(false);
const selectedPlayer = ref(null);
const proposals = ref([]);
const current_season = ref(null);
const selectedCategory = ref("star player"); // Default category
const categories = ref(["star player", "starter", "role player", "bench"]); // Categories for roles
const proposalsByCategory = ref({
    "star player": [],
    "starter": [],
    "role player": [],
    "bench": []
});

onMounted(async () => {
    await fetchTradeCandidates();
});

const selectCategory = (category) => {
    selectedCategory.value = category;
};

const showProfile = (playerId) => {
    selectedPlayer.value = playerId;
    showPlayerProfileModal.value = true;
};

// Fetch trade candidates
const fetchTradeCandidates = async () => {
    try {
        const response = await axios.get(route("trade.list")); // Update with your API endpoint
        proposals.value = response.data.trade_proposals;
        current_season.value = response.data.current_season;
        categorizeProposalsByRole();
    } catch (error) {
        console.error("Error fetching available proposals:", error);
    }
};

// Categorize proposals by role
const categorizeProposalsByRole = () => {
    // Clear current categorization
    proposalsByCategory.value["star player"] = [];
    proposalsByCategory.value["starter"] = [];
    proposalsByCategory.value["role player"] = [];
    proposalsByCategory.value["bench"] = [];

    proposals.value.forEach(proposal => {
        const role = proposal.player_to_role.toLowerCase();
        console.log(proposal.player_to_name);
        if (proposalsByCategory.value[role]) {
            proposalsByCategory.value[role].push(proposal);
        }
    });
};

// Handle pagination (if needed)
const handlePagination = (page_num) => {
    search.value.page_num = page_num;
    fetchTradeCandidates();
};

// End trade function (currently not used here)
const endTrade = async () => {
    try {
        const response = await axios.post(route("trade.end"));
        if (response) {
            await Swal.fire({
                title: 'Success!',
                text: 'Trade successfully completed!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            fetchTradeCandidates();
            emits("newSeason", Math.random());
        }
    } catch (error) {
        console.error("Error ending trade:", error);
        await Swal.fire({
            title: 'Error!',
            text: error.response.data.message,
            icon: 'error',
        });
    }
};

// Approve trade proposal
const approveProposal = async (proposalId) => {
    try {
        const response = await axios.post(route("trade.approve"),{ proposal_id: proposalId });
        if (response) {
            await Swal.fire({
                title: 'Success!',
                text: 'Trade proposal approved!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            fetchTradeCandidates(); // Refresh proposals list
        }
    } catch (error) {
        console.error("Error approving proposal:", error);
        await Swal.fire({
            title: 'Error!',
            text: error.response.data.message,
            icon: 'error',
        });
    }
};

// Reject trade proposal
const rejectProposal = async (proposalId) => {
    try {
        const response = await axios.post(route("trade.reject"),{ proposal_id: proposalId });
        if (response) {
            await Swal.fire({
                title: 'Success!',
                text: 'Trade proposal rejected!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            fetchTradeCandidates(); // Refresh proposals list
        }
    } catch (error) {
        console.error("Error rejecting proposal:", error);
        await Swal.fire({
            title: 'Error!',
            text: error.response.data.message,
            icon: 'error',
        });
    }
};

// Generate new trade proposal
const generateTradeProposal = async () => {
    try {
        // Show the processing Swal
        const swalProcessing = Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we generate the trade proposal.',
            icon: 'info',
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Make the API request
        const response = await axios.get(route("trade.generate")); // Update with your API endpoint

        // Close the processing Swal once the API call finishes
        swalProcessing.close();

        if (response) {
            await Swal.fire({
                title: 'Proposal Generated!',
                text: 'A new trade proposal has been generated.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            fetchTradeCandidates(); // Refresh proposals list
        }
    } catch (error) {
        console.error("Error generating trade proposal:", error);
        await Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || "An error occurred while generating the trade proposal.",
            icon: 'error',
        });
    }
};
</script>
