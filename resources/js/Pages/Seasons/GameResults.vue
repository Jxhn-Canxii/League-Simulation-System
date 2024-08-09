<template>
    <div class="p-4 bg-white shadow-md rounded-lg max-w-7xl mx-auto">
      <!-- Game Summary -->
      <div class="flex flex-col lg:flex-row justify-between mb-4">
        <div class="flex-1 text-center mb-2 lg:mb-0" @click.prevent="isTeamRosterModalOpen = gameDetails?.home_team.team_id">
          <h2 class="text-3xl font-bold">{{ gameDetails?.home_team.score }}</h2>
          <p class="text-md font-semibold">{{ gameDetails?.home_team.name }}</p>
        </div>
        <div class="flex-1 text-center mb-2 lg:mb-0">
          <div class="bg-gray-200 p-2 rounded-lg">
            <p class="text-xs font-semibold text-yellow-500">Liga Dos {{ isNaN(gameDetails?.round) ? 'Playoffs' : 'Regular Season' }}</p>
            <p class="text-xs font-semibold">Round: {{ roundNameFormatter(isNaN(gameDetails?.round) ? gameDetails?.round : parseFloat(gameDetails?.round) + 1) }}</p>
            <p class="text-xs font-semibold">Game ID: {{ gameDetails?.game_id }}</p>
          </div>
        </div>
        <div class="flex-1 text-center mb-2 lg:mb-0" @click.prevent="isTeamRosterModalOpen = gameDetails?.away_team.team_id">
          <h2 class="text-3xl font-bold">{{ gameDetails?.away_team.score }}</h2>
          <p class="text-md font-semibold">{{ gameDetails?.away_team.name }}</p>
        </div>
      </div>

      <!-- Player Statistics Tables -->
      <div class="mb-4">
        <h3 class="text-xl font-semibold mb-2">Player Statistics</h3>

        <!-- Home Team Player Stats -->
        <div class="mb-2">
          <h4 class="text-lg font-semibold mb-1">{{ gameDetails?.home_team.name }} Player Stats</h4>
          <table class="min-w-full bg-gray-100 rounded-lg overflow-hidden text-sm">
            <thead>
              <tr class="bg-gray-200 text-left">
                <th class="py-2 px-3 text-xs">Name</th>
                <th class="py-2 px-3 text-xs">Role</th>
                <th class="py-2 px-3 text-xs">Minutes Played</th>
                <th class="py-2 px-3 text-xs">Points</th>
                <th class="py-2 px-3 text-xs">Rebounds</th>
                <th class="py-2 px-3 text-xs">Assists</th>
                <th class="py-2 px-3 text-xs">Steals</th>
                <th class="py-2 px-3 text-xs">Blocks</th>
                <th class="py-2 px-3 text-xs">Turnovers</th>
                <th class="py-2 px-3 text-xs">Fouls</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="player in sortedHomePlayers" :key="player.name" @click.prevent="showPlayerProfileModal = player" :class="{'bg-yellow-100': top5HomePlayers.includes(player.name)}" class="border-b">
                <td class="py-1 px-3 text-xs">{{ player.name }}</td>
                <td class="py-1 px-3 text-xs">
                  <span :class="roleBadgeClass(player.role)">{{ player.role }}</span>
                </td>
                <td class="py-1 px-3 text-xs">{{ player.minutes }}</td>
                <td class="py-1 px-3 text-xs">{{ player.points }}</td>
                <td class="py-1 px-3 text-xs">{{ player.rebounds }}</td>
                <td class="py-1 px-3 text-xs">{{ player.assists }}</td>
                <td class="py-1 px-3 text-xs">{{ player.steals }}</td>
                <td class="py-1 px-3 text-xs">{{ player.blocks }}</td>
                <td class="py-1 px-3 text-xs">{{ player.turnovers }}</td>
                <td class="py-1 px-3 text-xs">{{ player.fouls }}</td>
              </tr>
              <tr v-if="sortedHomePlayers.length === 0">
                <td colspan="10" class="py-1 px-3 text-center text-xs">No player statistics available.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Away Team Player Stats -->
        <div>
          <h4 class="text-lg font-semibold mb-1">{{ gameDetails?.away_team.name }} Player Stats</h4>
          <table class="min-w-full bg-gray-100 rounded-lg overflow-hidden text-sm">
            <thead>
              <tr class="bg-gray-200 text-left">
                <th class="py-2 px-3 text-xs">Name</th>
                <th class="py-2 px-3 text-xs">Role</th>
                <th class="py-2 px-3 text-xs">Minutes Played</th>
                <th class="py-2 px-3 text-xs">Points</th>
                <th class="py-2 px-3 text-xs">Rebounds</th>
                <th class="py-2 px-3 text-xs">Assists</th>
                <th class="py-2 px-3 text-xs">Steals</th>
                <th class="py-2 px-3 text-xs">Blocks</th>
                <th class="py-2 px-3 text-xs">Turnovers</th>
                <th class="py-2 px-3 text-xs">Fouls</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="player in sortedAwayPlayers" :key="player.name" @click.prevent="showPlayerProfileModal = player" :class="{'bg-yellow-100': top5AwayPlayers.includes(player.name)}" class="border-b">
                <td class="py-1 px-3 text-xs">{{ player.name }}</td>
                <td class="py-1 px-3 text-xs">
                  <span :class="roleBadgeClass(player.role)">{{ player.role }}</span>
                </td>
                <td class="py-1 px-3 text-xs">{{ player.minutes }}</td>
                <td class="py-1 px-3 text-xs">{{ player.points }}</td>
                <td class="py-1 px-3 text-xs">{{ player.rebounds }}</td>
                <td class="py-1 px-3 text-xs">{{ player.assists }}</td>
                <td class="py-1 px-3 text-xs">{{ player.steals }}</td>
                <td class="py-1 px-3 text-xs">{{ player.blocks }}</td>
                <td class="py-1 px-3 text-xs">{{ player.turnovers }}</td>
                <td class="py-1 px-3 text-xs">{{ player.fouls }}</td>
              </tr>
              <tr v-if="sortedAwayPlayers.length === 0">
                <td colspan="10" class="py-1 px-3 text-center text-xs">No player statistics available.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Best Player of the Game -->
      <div v-if="bestPlayer" class="bg-yellow-100 p-2 rounded-lg">
        <h3 class="text-lg font-semibold mb-1">Best Player of the Game</h3>
        <p><strong>Name:</strong> {{ bestPlayer.name }}</p>
        <p><strong>Team:</strong> {{ bestPlayer.team }}</p>
        <p><strong>Points:</strong> {{ bestPlayer.points }}</p>
        <p><strong>Rebounds:</strong> {{ bestPlayer.rebounds }}</p>
        <p><strong>Assists:</strong> {{ bestPlayer.assists }}</p>
        <p><strong>Steals:</strong> {{ bestPlayer.steals }}</p>
        <p><strong>Blocks:</strong> {{ bestPlayer.blocks }}</p>
      </div>
    </div>
    <Modal :show="isTeamRosterModalOpen" :maxWidth="'fullscreen'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="isTeamRosterModalOpen = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="mt-4">
            <TeamRoster v-if="isTeamRosterModalOpen" :team_id="isTeamRosterModalOpen" />
        </div>
    </Modal>
    <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
        <button
            class="flex float-end bg-gray-100 p-3"
            @click.prevent="showPlayerProfileModal = false"
        >
            <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="p-6 block">
            <!-- Image Section -->
            <div class="ml-6">
                <h2 class="text-lg font-semibold text-gray-800">
                    Player Profile
                </h2>
                <div class="mt-4">
                    <p><strong>Name:</strong> {{ showPlayerProfileModal.name }}</p>
                    <span :class="roleBadgeClass(showPlayerProfileModal.role)">{{ showPlayerProfileModal.role }}</span>
                </div>
            </div>
            <PlayerPerformance :key="showPlayerProfileModal.player_id" :player_id="showPlayerProfileModal.player_id" />
        </div>
    </Modal>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from 'axios';
  import { roundNameFormatter } from "@/Utility/Formatter";
  import Modal from "@/Components/Modal.vue";
  import TeamRoster from '../Teams/TeamRoster.vue';
  import PlayerPerformance from '../Teams/PlayerPerformance.vue';
  const props = defineProps({
    game_id: {
      type: Number,
      required: true
    }
  });
  const showPlayerProfileModal = ref(false);
  const isTeamRosterModalOpen = ref(false);
  const gameDetails = ref(null);
  const playerStats = ref({ home: [], away: [] });
  const bestPlayer = ref(null);

  // Fetch the box score data
  const fetchBoxScore = async () => {
    try {
      const response = await axios.post(route("game.boxscore"), {
        game_id: props.game_id
      });
      const data = response.data.box_score;

      gameDetails.value = data;
      playerStats.value.home = data.player_stats.home;
      playerStats.value.away = data.player_stats.away;
      bestPlayer.value = data.best_player;
    } catch (error) {
      console.error('Error fetching box score:', error);
    }
  };

  // Sort players by points and get top 5 players
  const sortedHomePlayers = computed(() => {
    return playerStats.value.home.slice().sort((a, b) => b.points - a.points);
  });

  const sortedAwayPlayers = computed(() => {
    return playerStats.value.away.slice().sort((a, b) => b.points - a.points);
  });

  const top5HomePlayers = computed(() => {
    return sortedHomePlayers.value.slice(0, 5).map(player => player.name);
  });

  const top5AwayPlayers = computed(() => {
    return sortedAwayPlayers.value.slice(0, 5).map(player => player.name);
  });

  // Function to determine badge class based on role
  const roleBadgeClass = (role) => {
    switch(role) {
      case 'star player':
        return 'bg-red-500 text-white rounded-full px-2 py-1 text-xs';
      case 'starter':
        return 'bg-blue-500 text-white rounded-full px-2 py-1 text-xs';
      case 'role player':
        return 'bg-green-500 text-white rounded-full px-2 py-1 text-xs';
      case 'bench':
        return 'bg-gray-500 text-white rounded-full px-2 py-1 text-xs';
      default:
        return 'bg-gray-300 text-gray-800 rounded-full px-2 py-1 text-xs';
    }
  };

  onMounted(() => {
    fetchBoxScore();
  });
  </script>

  <style scoped>
  /* Add any additional styles you need here */
  </style>
