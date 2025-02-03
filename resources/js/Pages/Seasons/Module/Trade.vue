<template>
    <div class="draft-board">
        <h2 class="text-xl font-semibold text-gray-800">Trade Proposal</h2>

        <!-- Show 'Generate Proposal' button if proposals are empty -->
        <div v-if="proposals.length === 0">
            <button 
                @click="generateTradeProposal" 
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Generate Proposal
            </button>
        </div>

        <!-- Display list of proposals -->
        <div v-else>
             <!-- Show 'End Trade' button if there are proposals -->
            <div class="text-right mb-4">
                <button 
                    @click="endTrade" 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    End Trade
                </button>
            </div>
            <ul class="space-y-4">
                <li v-for="(proposal, index) in proposals" :key="proposal.id" class="border p-4 rounded-lg bg-white shadow-md">
                    <div class="flex justify-between">
                        <div class="text-gray-800">
                            <p><strong>Proposal ID:</strong> {{ proposal.id }}</p>
                            <p><strong>From Team:</strong> {{ proposal.from_team }}</p>
                            <p><strong>To Team:</strong> {{ proposal.to_team }}</p>
                        </div>
                        <div class="space-x-4">
                            <p><strong>{{ proposal.player_from_name }} → {{ proposal.player_to_name }}</strong></p>
                            <p class="text-gray-500 text-sm">{{ new Date(proposal.created_at).toLocaleString() }}</p>
                        </div>
                    </div>
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
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

const emits = defineEmits(["newSeason"]);
const proposals = ref([]);
const isHide = ref(true);
const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: "",
    itemsperpage: 10,
});

onMounted(async () => {
    await fetchTradeCandidates();
});

// Fetch trade candidates
const fetchTradeCandidates = async () => {
    try {
        const response = await axios.post(route("trade.list"), search.value); // Update with your API endpoint
        proposals.value = response.data;
    } catch (error) {
        console.error("Error fetching available proposals:", error);
    }
};

// Handle pagination
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
            isHide.value = true;
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
        const response = await axios.post(route("trade.approve", { proposalId }));
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
        const response = await axios.post(route("trade.reject", { proposalId }));
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
        const response = await axios.post(route("trade.generate")); // Update with your API endpoint
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
            text: error.response.data.message,
            icon: 'error',
        });
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
