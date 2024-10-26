<template>
    <div class="p-4 bg-gray-900 text-white shadow-md rounded-lg max-w-7xl mx-auto">
      <!-- Game Summary -->
      <div class="flex flex-col lg:flex-row justify-between mb-4 border-b-2 border-gray-700 pb-4">
        <div class="flex-1 text-center mb-2 lg:mb-0 team-card" @click.prevent="isTeamRosterModalOpen = gameDetails?.home_team.team_id">
          <h2 class="text-5xl font-bold">{{ gameDetails?.home_team.score }}</h2>
          <p class="text-md font-semibold">{{ gameDetails?.home_team.name }}</p>
        </div>
        <div class="flex-1 text-center mb-2 lg:mb-0">
          <div class="bg-gray-800 p-2 rounded-lg">
            <p class="text-xs font-semibold text-yellow-500">Liga Dos {{ isNaN(gameDetails?.round) ? 'Playoffs' : 'Regular Season' }}</p>
            <p class="text-xs font-semibold">Round: {{ roundNameFormatter(isNaN(gameDetails?.round) ? gameDetails?.round : parseFloat(gameDetails?.round)) }}</p>
            <p class="text-xs font-semibold">Game ID: {{ gameDetails?.game_id }}</p>
          </div>
        </div>
        <div class="flex-1 text-center mb-2 lg:mb-0 team-card" @click.prevent="isTeamRosterModalOpen = gameDetails?.away_team.team_id">
          <h2 class="text-5xl font-bold">{{ gameDetails?.away_team.score }}</h2>
          <p class="text-md font-semibold">{{ gameDetails?.away_team.name }}</p>
        </div>
      </div>

      <!-- Player Statistics Tables -->
      <div class="mb-4">
        <h3 class="text-xl font-semibold mb-2">Player Statistics</h3>

        <!-- Home Team Player Stats -->
        <div class="mb-2">
          <h4 class="text-lg font-semibold mb-1">{{ gameDetails?.home_team.name }} Player Stats</h4>
          <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm">
            <thead>
              <tr class="bg-gray-700 text-left">
                <th class="py-2 px-3 text-xs">Name</th>
                <th class="py-2 px-3 text-xs">Role</th>
                <th class="py-2 px-3 text-xs">Mins</th>
                <th class="py-2 px-3 text-xs">Pts</th>
                <th class="py-2 px-3 text-xs">Rbd</th>
                <th class="py-2 px-3 text-xs">Ast</th>
                <th class="py-2 px-3 text-xs">Stl</th>
                <th class="py-2 px-3 text-xs">Blk</th>
                <th class="py-2 px-3 text-xs">TO</th>
                <th class="py-2 px-3 text-xs">Fls</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="player in sortedHomePlayers" :key="player.name" @click.prevent="showPlayerProfileModal = player" :class="{'bg-yellow-100 text-black': top5HomePlayers.includes(player.name)}" class="border-b hover:bg-gray-600">
                <td class="py-1 px-3 text-xs">{{ player.name }}<sup>{{ player.is_rookie ? 'R':'V'}}</sup></td>
                <td class="py-1 px-3 text-xs"><span :class="roleBadgeClass(player.role)">{{ player.role }}</span></td>
                <td class="py-1 px-3 text-xs">{{ player.minutes > 0 ? player.minutes : 'DNP' }}</td>
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
          <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm">
            <thead>
              <tr class="bg-gray-700 text-left">
                <th class="py-2 px-3 text-xs">Name</th>
                <th class="py-2 px-3 text-xs">Role</th>
                <th class="py-2 px-3 text-xs">Mins</th>
                <th class="py-2 px-3 text-xs">Pts</th>
                <th class="py-2 px-3 text-xs">Rbd</th>
                <th class="py-2 px-3 text-xs">Ast</th>
                <th class="py-2 px-3 text-xs">Stl</th>
                <th class="py-2 px-3 text-xs">Blk</th>
                <th class="py-2 px-3 text-xs">TO</th>
                <th class="py-2 px-3 text-xs">Fls</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="player in sortedAwayPlayers" :key="player.name" @click.prevent="showPlayerProfileModal = player" :class="{'bg-yellow-100 text-black': top5AwayPlayers.includes(player.name)}" class="border-b hover:bg-gray-600">
                <td class="py-1 px-3 text-xs">{{ player.name }} <sup>{{ player.is_rookie ? 'R':'V'}}</sup></td>
                <td class="py-1 px-3 text-xs"><span :class="roleBadgeClass(player.role)">{{ player.role }}</span></td>
                <td class="py-1 px-3 text-xs">{{ player.minutes > 0 ? player.minutes : 'DNP' }}</td>
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
      <div v-if="bestPlayer" class="bg-yellow-100 p-2 rounded-lg text-black">
        <h3 class="text-lg font-semibold mb-1">Best Player of the Game</h3>
        <p><strong>Name:</strong> {{ bestPlayer.name }}</p>
        <p><strong>Team:</strong> {{ bestPlayer.team }}</p>
        <p><strong>Points:</strong> {{ bestPlayer.points }}</p>
        <p><strong>Rebounds:</strong> {{ bestPlayer.rebounds }}</p>
        <p><strong>Assists:</strong> {{ bestPlayer.assists }}</p>
        <p><strong>Steals:</strong> {{ bestPlayer.steals }}</p>
        <p><strong>Blocks:</strong> {{ bestPlayer.blocks }}</p>
      </div>

      <Modal :show="isTeamRosterModalOpen" :maxWidth="'fullscreen'">
        <button class="flex float-end bg-gray-100 p-3" @click.prevent="isTeamRosterModalOpen = false">
          <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="mt-4">
          <TeamRoster v-if="isTeamRosterModalOpen" :team_id="isTeamRosterModalOpen" />
        </div>
      </Modal>

      <Modal :show="showPlayerProfileModal" :maxWidth="'6xl'">
        <button class="flex float-end bg-gray-100 p-3" @click.prevent="showPlayerProfileModal = false">
          <i class="fa fa-times text-black-600"></i>
        </button>
        <div class="p-6 block">
          <PlayerPerformance :key="showPlayerProfileModal.player_id" :player_id="showPlayerProfileModal.player_id" />
        </div>
      </Modal>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from 'axios';
  import { roundNameFormatter, roleBadgeClass } from "@/Utility/Formatter";
  import Modal from "@/Components/Modal.vue";

  import TeamRoster from "@/Pages/Teams/Module/TeamRoster.vue";
  import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";

  const props = defineProps({
    game_id: {
      type: String,
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

  onMounted(() => {
    fetchBoxScore();
  });
  </script>

<style scoped>
.team-card {
  background-color: #1a202c; /* Dark background for team cards */
  transition: transform 0.2s;
}

.team-card:hover {
  transform: scale(1.05); /* Scale effect on hover */
}

/* Use darker backgrounds for table headers */
table {
  border-collapse: collapse;
}

th, td {
  border: 1px solid #2d3748; /* Subtle borders */
}

tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.1); /* Light hover effect */
}
</style>

