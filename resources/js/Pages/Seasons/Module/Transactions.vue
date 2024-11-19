<template>
    <div class="transactions-board">
        <!-- Title Section -->
        <h3 class="text-lg font-semibold text-gray-800">
            Transactions for Season {{ props.season_id }}
        </h3>
        <hr class="my-4 border-t border-gray-200" />

        <!-- Tabs for Transactions Type (Normal / Notable) -->
        <div class="flex border-b mb-4">
            <button
                class="px-4 py-2 text-sm font-medium"
                :class="{
                    'text-blue-600 border-b-2 border-blue-600': selectedType === 'notable',
                    'text-gray-600': selectedType !== 'notable'
                }"
                @click="changeType('notable')"
            >
                Notable Transactions
            </button>
            <button
                class="px-4 py-2 text-sm font-medium"
                :class="{
                    'text-blue-600 border-b-2 border-blue-600': selectedType === 'normal',
                    'text-gray-600': selectedType !== 'normal'
                }"
                @click="changeType('normal')"
            >
                Normal Transactions
            </button>
        </div>

        <!-- Transactions Table -->
        <div v-if="data.data?.length > 0">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-nowrap">
                    <tr>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Player</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Awards</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">From Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">To Team</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Transaction Type</th>
                        <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loop through transactions -->
                    <tr v-for="transaction in data.data" :key="transaction.id" @click.prevent="showPlayerProfileModal = transaction.player_id" :class="transaction.is_active ? 'bg-green-100' : 'bg-red-100'" class="hover:bg-gray-100 cursor-pointer">
                        <td class="px-2 py-1 whitespace-nowrap border">
                            <span>
                                {{ transaction.player_name }}
                                <sup v-if="transaction.is_finals_mvp">
                                    <i class="fa fa-star fa-sm text-yellow-500"></i>
                                </sup>
                            </span>
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">
                            <span
                                :class="roleClasses(transaction.role)"
                                class="inline-flex items-center capitalize px-2.5 py-0.5 rounded text-xs font-medium"
                            >
                                {{ transaction.role }}
                            </span>
                        </td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ transaction.player_awards ?? '-' }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ transaction.from_team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ transaction.to_team_name }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ transaction.status }}</td>
                        <td class="px-2 py-1 whitespace-nowrap border">{{ transaction.details }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- No transactions message -->
        <div v-else class="text-center py-4 text-gray-500">
            <p>No transactions available for this season.</p>
        </div>
    </div>
    <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="showPlayerProfileModal = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="p-6 block">
            <PlayerPerformance :key="showPlayerProfileModal" :player_id="showPlayerProfileModal" />
        </div>
    </Modal>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Paginator from "@/Components/Paginator.vue";
import { roleClasses } from "@/Utility/Formatter";
import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";
import Modal from "@/Components/Modal.vue";

const showPlayerProfileModal = ref(false);
const props = defineProps({
    season_id: {
        type: [Number, String],
        default: 0,
    },
    team_id: {
        type: [Number, String],
        default: 0,
    },
});

const selectedType = ref("notable"); // Track selected type (normal/notable)
const data = ref({ data: [], total: 0 }); // Store transactions data with pagination info
const search = ref({
    season_id: 0,
    team_id: 0,
    itemsperpage: 10,
    page_num: 1,
    search: '',
    type: 'notable',
});

// Change transaction type (normal or notable)
const changeType = (type) => {
    selectedType.value = type;
    search.value.page_num = 1; // Reset to page 1 when changing the transaction type
    fetchTransactions();
};

// Fetch the transactions data from Laravel API
const fetchTransactions = async () => {
    try {
        search.value.type = selectedType.value;
        search.value.season_id = props.season_id;
        search.value.team_id = props.team_id;
        const response = await axios.post(route("players.transactions"), search.value);
        data.value = response.data; // Store the response data (includes pagination info)
    } catch (error) {
        console.error("Error fetching transactions:", error);
    }
};

// Handle pagination (when page number changes)
const handlePagination = (page) => {
    search.value.page_num = page;
    fetchTransactions(); // Fetch transactions for the selected page
};

// Fetch transactions when the component is mounted
onMounted(() => {
    fetchTransactions(); // Load transactions on initial mount
});
</script>
