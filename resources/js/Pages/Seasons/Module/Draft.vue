<template>
    <div class="draft-board">
      <h2 class="text-xl font-semibold text-gray-800">Draft Board</h2>

      <!-- Divider -->
      <hr class="my-4 border-t border-gray-200" />

      <!-- Teams Section -->
      <div class="grid grid-cols-3 gap-4 mb-8">
        <div v-for="team in teams" :key="team.id" class="team-card border p-4 rounded-lg">
          <h3 class="font-bold text-lg">{{ team.name }}</h3>
          <p class="text-gray-600">Drafted Players:</p>
          <ul>
            <li v-for="player in team.draftedPlayers" :key="player.id" class="text-gray-800">
              {{ player.name }}
            </li>
          </ul>
          <button @click="draftPlayer(team.id)" class="mt-2 bg-blue-500 text-white px-2 py-1 rounded">
            Draft Player
          </button>
        </div>
      </div>

      <!-- Available Players Section -->
      <h3 class="text-lg font-semibold text-gray-800">Available Players</h3>
      <hr class="my-4 border-t border-gray-200" />
      <div class="overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
          <thead class="bg-gray-50 text-nowrap">
            <tr>
              <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Team</th>
              <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Position</th>
              <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider">Overall Rating</th>
              <th class="px-2 py-1 text-left font-medium text-gray-500 uppercase tracking-wider"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="player in availablePlayers" :key="player.id" class="hover:bg-gray-100">
              <td class="px-2 py-1 whitespace-nowrap border">{{ player.name }}</td>
              <td class="px-2 py-1 whitespace-nowrap border">{{ player.team_name }}</td>
              <td class="px-2 py-1 whitespace-nowrap border">{{ player.position }}</td>
              <td class="px-2 py-1 whitespace-nowrap border">{{ player.overall_rating }}</td>
              <td class="px-2 py-1 whitespace-nowrap border">
                <button @click="selectPlayer(player)" class="bg-green-500 text-white px-2 py-1 rounded">
                  Select
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Draft History Section -->
      <h3 class="text-lg font-semibold text-gray-800">Draft History</h3>
      <hr class="my-4 border-t border-gray-200" />
      <ul>
        <li v-for="history in draftHistory" :key="history.id" class="text-gray-800">
          {{ history.team_name }} drafted {{ history.player_name }} (Round {{ history.round }}, Pick {{ history.pick_number }})
        </li>
      </ul>
    </div>
  </template>

  <script setup>
  import { ref, onMounted } from "vue";
  import axios from "axios";

  const teams = ref([]);
  const availablePlayers = ref([]);
  const draftHistory = ref([]);

  const search = ref({
    page_num: 1,
    total_pages: 0,
    total: 0,
    search: "",
    itemsperpage: 10,
});

  onMounted(async () => {
    await fetchTeams();
    await fetchAvailablePlayers();
    await fetchDraftHistory();
  });

  const fetchTeams = async () => {
    try {
      const response = await axios.get(route("draft.orders")); // Update with your API endpoint
      teams.value = response.data;
    } catch (error) {
      console.error("Error fetching teams:", error);
    }
  };

  const fetchAvailablePlayers = async () => {
    try {
      const response = await axios.post(route("players.free.agents"),search.value); // Update with your API endpoint
      availablePlayers.value = response.data;
    } catch (error) {
      console.error("Error fetching available players:", error);
    }
  };

  const fetchDraftHistory = async () => {
    try {
      const response = await axios.get(route("draft.history")); // Update with your API endpoint
      draftHistory.value = response.data;
    } catch (error) {
      console.error("Error fetching draft history:", error);
    }
  };

  const selectPlayer = (player) => {
    // Logic to handle player selection
    console.log("Selected player:", player);
    // Call API to draft the player
  };

  const draftPlayer = (teamId) => {
    try {
      const response = await axios.get(route("draft.players")); // Update with your API endpoint
      draftHistory.value = response.data;
    } catch (error) {
      console.error("Error fetching draft history:", error);
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
