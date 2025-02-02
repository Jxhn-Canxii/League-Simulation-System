<template>
    <div class="p-4 bg-gray-900 shadow-md min-h-screen flex justify-center items-center rounded-lg max-w-7xl mx-auto" v-if="!gameDetails">
        <!-- Skeleton Loader -->
        <div class="flex justify-center items-center h-full">
            <!-- Centered Loader -->
            <div class="flex flex-col items-center space-y-6">
                <!-- Placeholder for Home Team Name -->
                <div class="w-32 h-6 bg-gray-700 rounded-md animate-pulse"></div>

                <!-- Placeholder for Home Team Score -->
                <div class="w-24 h-8 bg-gray-700 rounded-md animate-pulse"></div>

                <!-- Placeholder for "VS" Text -->
                <div class="text-white text-xl font-semibold">
                    <span class="animate-pulse">VS</span>
                </div>

                <!-- Placeholder for Away Team Score -->
                <div class="w-24 h-8 bg-gray-700 rounded-md animate-pulse"></div>

                <!-- Placeholder for Away Team Name -->
                <div class="w-32 h-6 bg-gray-700 rounded-md animate-pulse"></div>

                <!-- Placeholder for Round or Game Status -->
                <div class="w-48 h-6 bg-gray-700 rounded-md animate-pulse mt-4"></div>

                <!-- Placeholder for Matchup Record -->
                <div class="w-32 h-6 bg-gray-700 rounded-md animate-pulse mt-4"></div>
            </div>
        </div>
    </div>
    <div class="p-4 bg-gray-900 shadow-md rounded-lg max-w-7xl mx-auto" v-else>
        <!-- Game Summary -->
        <div
            class="flex flex-col lg:flex-row justify-between mb-4 border-b-2 border-gray-700 pb-4"
        >
            <div
                class="flex-1 text-center mb-2 lg:mb-0 team-card rounded relative"
                @click.prevent="
                    isTeamRosterModalOpen = gameDetails?.home_team.team_id
                "
                :style="{
                    backgroundColor: '#' + gameDetails?.home_team.primary_color,
                }"
            >
                <!-- Home team primary color -->
                <h2 class="text-5xl font-bold text-white">
                    {{ gameDetails?.home_team.score }}
                </h2>
                <p
                    class="text-md font-semibold text-white"
                    :style="{
                        backgroundColor:
                            '#' + gameDetails?.home_team.secondary_color,
                    }"
                >
                    {{ gameDetails?.home_team.name }} ({{
                        gameDetails?.home_team.streak
                    }})
                </p>
                <div class="flex justify-center" v-if="!props.showBoxScore">
                    <ul class="flex space-x-2 mt-2">
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-7 h-7 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-white">{{
                                    gameDetails?.home_team.ratings.offense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">OFF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-red-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.home_team.ratings.defense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">DEF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-violet-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.home_team.ratings.passing_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">PASS</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-yellow-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.home_team.ratings.rebounding_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">REB</p>
                        </li>
                    </ul>
                </div>
                <small class="absolute top-0 right-0 font-bold text-gray-200"># {{ gameDetails?.home_team.team_id }}</small>
            </div>

            <div class="flex-1 text-center mb-2 lg:mb-0 text-white">
                <div class="bg-gray-800 p-2 rounded-lg m-1">
                    <p class="text-xs font-semibold text-yellow-500">
                        Liga Dos
                        {{
                            isNaN(gameDetails?.round)
                                ? "Playoffs"
                                : "Regular Season"
                        }}
                    </p>
                    <p class="text-xs font-semibold">
                        Round:
                        {{
                            roundNameFormatter(
                                isNaN(gameDetails?.round)
                                    ? gameDetails?.round
                                    : parseFloat(gameDetails?.round)
                            )
                        }}
                    </p>
                    <p class="text-xs font-semibold">
                        Game ID: {{ gameDetails?.game_id }}
                    </p>
                    <p class="text-xs font-semibold">
                        Matchup Record:
                        {{
                            gameDetails?.head_to_head_record.home_team_wins ?? 0
                        }}
                        -
                        {{
                            gameDetails?.head_to_head_record.away_team_wins ?? 0
                        }}
                    </p>
                    <!-- <div class="timer">
                        <p class="text-xs text-gray-300">{{ formatTime(time) }} seconds</p>
                    </div> -->
                </div>
                <div class="flex flex-col p-2 m-1 bg-slate-800 rounded text-white">
                    <small class="text-xs text-nowrap text-gray-500">{{ seasonLeaders.message }}</small>
                    <small class="text-xs text-nowrap font-bold" :title="seasonLeaders.draft_status">{{ seasonLeaders.player_name }} ({{ seasonLeaders.stat_value }} {{ seasonLeaders.stat_type }})</small>
                    <small class="text-xs text-nowrap text-gray-500">{{ seasonLeaders.team_name }}</small>
                </div>
            </div>

            <div
                class="flex-1 text-center mb-2 lg:mb-0 team-card rounded relative"
                @click.prevent="
                    isTeamRosterModalOpen = gameDetails?.away_team.team_id
                "
                :style="{
                    backgroundColor: '#' + gameDetails?.away_team.primary_color,
                }"
            >
                <!-- Away team primary color -->
                <h2 class="text-5xl font-bold text-white">
                    {{ gameDetails?.away_team.score }}
                </h2>
                <p
                    class="text-md font-semibold text-white"
                    :style="{
                        backgroundColor:
                            '#' + gameDetails?.away_team.secondary_color,
                    }"
                >
                    {{ gameDetails?.away_team.name }} ({{
                        gameDetails?.away_team.streak
                    }})
                </p>
                <div class="flex justify-center" v-if="!props.showBoxScore">
                    <ul class="flex space-x-2 mt-2">
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-7 h-7 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.offense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">OFF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-red-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.defense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">DEF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-violet-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.passing_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">PASS</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-7 h-7 p-2 bg-yellow-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.rebounding_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">REB</p>
                        </li>
                    </ul>
                </div>
                <small class="absolute top-0 right-0 font-bold text-gray-200"># {{ gameDetails?.away_team.team_id }}</small>
            </div>
        </div>

        <!-- Player Statistics Tables -->
        <div class="mb-4 text-white" v-if="props.showBoxScore">
            <h3 class="text-xl font-semibold mb-2">Player Statistics</h3>

            <!-- Home Team Player Stats -->
            <div
                class="mb-2 p-2 rounded"
                :style="{
                    backgroundColor: '#' + gameDetails?.home_team.primary_color,
                }"
            >
            <div class="flex justify-between">
                <h4 class="text-lg font-semibold flex items-center mb-1">
                    {{ gameDetails?.home_team.name }} Player Stats
                </h4>
                <ul class="flex space-x-2">
                    <li class="flex flex-col items-center">
                        <span
                            class="flex-shrink-0 w-10 h-10 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                gameDetails?.home_team.ratings.offense_rating
                            }}</span>
                        </span>
                        <p class="text-xs text-gray-900 font-bold">OFF</p>
                    </li>
                    <li class="flex flex-col items-center">
                        <span
                        class="flex-shrink-0 w-10 h-10 p-2 bg-red-600 rounded-full flex items-center justify-center"
                    >
                        <span class="text-sm font-bold text-white">{{
                                gameDetails?.home_team.ratings.defense_rating
                            }}</span>
                        </span>
                        <p class="text-xs text-gray-900 font-bold">DEF</p>
                    </li>
                    <li class="flex flex-col items-center">
                        <span
                        class="flex-shrink-0 w-10 h-10 p-2 bg-violet-600 rounded-full flex items-center justify-center"
                    >
                        <span class="text-sm font-bold text-white">{{
                                gameDetails?.home_team.ratings.passing_rating
                            }}</span>
                        </span>
                        <p class="text-xs text-gray-900 font-bold">PASS</p>
                    </li>
                    <li class="flex flex-col items-center">
                        <span
                        class="flex-shrink-0 w-10 h-10 p-2 bg-yellow-600 rounded-full flex items-center justify-center"
                    >
                        <span class="text-sm font-bold text-white">{{
                                gameDetails?.home_team.ratings.rebounding_rating
                            }}</span>
                        </span>
                        <p class="text-xs text-gray-900 font-bold">REB</p>
                    </li>
                </ul>
            </div>
                <table
                    class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm"
                >
                    <thead>
                        <tr
                            class="bg-gray-700 text-left"
                            :style="{
                                backgroundColor:
                                    '#' +
                                    gameDetails?.home_team.secondary_color,
                            }"
                        >
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
                        <tr
                            v-for="player in sortedHomePlayers"
                            :key="player.name"
                            @click.prevent="showPlayerProfileModal = player"
                            :class="{
                                'bg-yellow-100 text-black':
                                    top5HomePlayers.includes(player.name),
                            }"
                            class="border-b hover:bg-gray-600"
                        >
                            <td class="py-1 px-3 text-xs">
                                {{ player.name
                                }}<sup>{{ player.is_rookie ? "R" : "V" }}</sup>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                <span :class="roleBadgeClass(player.role)">{{
                                    player.role
                                }}</span>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{
                                    player.minutes > 0 ? player.minutes : "DNP"
                                }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.points }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.rebounds }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.assists }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.steals }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.blocks }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.turnovers : 0 }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.fouls : 0 }}
                            </td>
                        </tr>
                        <tr v-if="sortedHomePlayers.length === 0">
                            <td
                                colspan="10"
                                class="py-1 px-3 text-center text-xs"
                            >
                                No player statistics available.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Away Team Player Stats -->
            <div
                class="mb-2 p-2 rounded"
                :style="{
                    backgroundColor: '#' + gameDetails?.away_team.primary_color,
                }"
            >
                <div class="flex justify-between">
                    <h4 class="text-lg font-semibold flex items-center mb-1">
                        {{ gameDetails?.away_team.name }} Player Stats
                    </h4>
                    <ul class="flex space-x-2">
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-10 h-10 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.offense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">OFF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-10 h-10 p-2 bg-red-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.defense_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">DEF</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-10 h-10 p-2 bg-violet-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.passing_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">PASS</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                            class="flex-shrink-0 w-10 h-10 p-2 bg-yellow-600 rounded-full flex items-center justify-center"
                        >
                            <span class="text-sm font-bold text-white">{{
                                    gameDetails?.away_team.ratings.rebounding_rating
                                }}</span>
                            </span>
                            <p class="text-xs text-gray-900 font-bold">REB</p>
                        </li>
                    </ul>
                </div>

                <table
                    class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm"
                >
                    <thead>
                        <tr
                            class="text-left"
                            :style="{
                                backgroundColor:
                                    '#' +
                                    gameDetails?.away_team.secondary_color,
                            }"
                        >
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
                        <tr
                            v-for="player in sortedAwayPlayers"
                            :key="player.name"
                            @click.prevent="showPlayerProfileModal = player"
                            :class="{
                                'bg-yellow-100 text-black':
                                    top5AwayPlayers.includes(player.name),
                            }"
                            class="border-b hover:bg-gray-600"
                        >
                            <td class="py-1 px-3 text-xs">
                                {{ player.name }}
                                <sup>{{ player.is_rookie ? "R" : "V" }}</sup>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                <span :class="roleBadgeClass(player.role)">{{
                                    player.role
                                }}</span>
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{
                                    player.minutes > 0 ? player.minutes : "DNP"
                                }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.points }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.rebounds }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.assists }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.steals }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.blocks }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.turnovers : 0 }}
                            </td>
                            <td class="py-1 px-3 text-xs">
                                {{ player.minutes > 0 ? player.fouls : 0 }}
                            </td>
                        </tr>
                        <tr v-if="sortedAwayPlayers.length === 0">
                            <td
                                colspan="10"
                                class="py-1 px-3 text-center text-xs"
                            >
                                No player statistics available.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Best Player of the Game -->
        <div class="flex bg-white">
            <!-- Best Player Section: 1/4 Width -->
            <div class="w-1/2 p-2">
                <h3 class="text-lg font-semibold mb-1">Player of the Game</h3>
                <div
                    v-if="bestPlayer"
                    class="bg-white shadow-lg p-4 rounded-lg text-black"
                >
                    <div class="flex flex-col items-center text-white mx-0 p-0 rounded"
                    :style="{
                        backgroundColor:
                            '#' + (gameDetails?.home_team.score > gameDetails?.away_team.score ? gameDetails?.home_team.primary_color : gameDetails?.away_team.primary_color),
                    }"
                    >
                        <p class="text-4xl font-extrabold mb-1">
                            {{ playerFormatter(bestPlayer?.name) }}
                            <sup v-if="bestPlayer?.is_finals_mvp">
                                <i class="fa fa-star fa-sm text-yellow-500"></i>
                            </sup>
                        </p>
                        <span
                            :class="roleClasses(bestPlayer.role)"
                            class="inline-flex items-center capitalize px-2.5 mb-2 py-0.5 rounded text-xs font-medium"
                        >
                            {{ bestPlayer.role }}
                        </span>
                        <div class="flex w-full justify-center px-0 mx-0"
                        :style="{
                            backgroundColor:
                                '#' + (gameDetails?.home_team.score > gameDetails?.away_team.score ? gameDetails?.home_team.secondary_color : gameDetails?.away_team.secondary_color),
                        }"
                        >
                            <p class="text-xl">
                                {{ bestPlayer?.team }}
                            </p>
                        </div>
                    </div>
                    <ul class="grid grid-cols-3 gap-4 p-4">
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.points
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">PTS</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.rebounds
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">REB</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.assists
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">AST</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.steals
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">STL</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-blue-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.blocks
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">BLK</p>
                        </li>
                        <li class="flex flex-col items-center">
                            <span
                                class="flex-shrink-0 w-25 h-25 p-2 bg-red-600 rounded-full flex items-center justify-center"
                            >
                                <span class="text-6xl font-bold text-white">{{
                                    bestPlayer?.turnovers
                                }}</span>
                            </span>
                            <p class="text-xl text-gray-900 font-bold">TO</p>
                        </li>
                    </ul>

                    <!-- Marquee for awards -->
                    <div class="mt-4 block justify-start text-wrap">
                        <p class="text-xs font-bold text-gray-600" v-if="bestPlayer?.awards && bestPlayer?.awards.length > 0">
                            {{ bestPlayer?.awards }}
                        </p>
                        <p class="text-xs font-bold text-gray-600" v-if="bestPlayer?.finals_mvp && bestPlayer?.finals_mvp.length > 0">
                             {{ bestPlayer?.finals_mvp }}
                        </p>
                        <p class="text-xs font-bold text-gray-600" v-if="bestPlayer?.championship_won && bestPlayer?.championship_won.length > 0">
                            {{ bestPlayer?.championship_won }}
                       </p>
                    </div>
                    <div class="flex justify-center">
                        <sup class="float-center font-bold mt-0 text-red-500">
                            {{ bestPlayer.draft_status == 'Undrafted' ? 'S'+bestPlayer.draft_id+' '+bestPlayer.draft_status : bestPlayer.draft_status + (bestPlayer.drafted_team_acro ? ' ('+bestPlayer.drafted_team_acro+ ')' : '')}}
                        </sup>
                    </div>
                </div>
            </div>
            <!-- Stat Leaders Section: 3/4 Width -->
            <div class="w-1/2 p-2 bg-white">
                <h3 class="text-lg font-semibold mb-2">Stat Leaders</h3>
                <div class="min-w-full shadow-lg border-gray-300 p-4">
                    <ul class="space-y-4">
                        <li
                            v-if="statLeaders.points"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i
                                    class="fas fa-basketball-ball text-gray-600"
                                ></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-bold">{{
                                            statLeaders.points.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.points.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.points.points }} pts
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li
                            v-if="statLeaders.assists"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i class="fas fa-hand-point-right text-gray-600" title="Assist"></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.assists.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.assists.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{
                                                statLeaders.assists.assists
                                            }}
                                            ast
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.rebounds"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i class="fas fa-arrow-alt-circle-up text-gray-600" title="Rebounds"></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.rebounds.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.rebounds.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{
                                                statLeaders.rebounds.rebounds
                                            }}
                                            reb
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.steals"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                            <i class="fas fa-user-shield text-gray-600" title="Steals"></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.steals.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.steals.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.steals.steals }} stl
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="statLeaders.blocks"
                            class="flex items-center border-b border-gray-300 pb-2"
                        >
                            <span
                                class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <i class="fas fa-stop-circle text-gray-600" title="Blocks"></i>
                            </span>
                            <div class="ml-3 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-semibold">{{
                                            statLeaders.blocks.player_name
                                        }}</span>
                                        <small class="text-gray-400 block">{{
                                            statLeaders.blocks.team_name
                                        }}</small>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-2xl">
                                            {{ statLeaders.blocks.blocks }} blk
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="p-0 flex-grow mt-2" v-if="injuredPlayers?.length > 0">
                        <small>Injury Report</small>
                        <div class="flex justify-between items-start">
                            <div class="block text-nowrap overflow-x-auto">
                            <marquee class="text-red-600 font-semibold">
                                <!-- Comma separated list of player names and their team names -->
                                {{ formatInjuredPlayers(injuredPlayers) }}
                            </marquee>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <TeamRoster
                v-if="isTeamRosterModalOpen"
                :team_id="isTeamRosterModalOpen"
            />
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
            <PlayerPerformance
                :key="showPlayerProfileModal.player_id"
                :player_id="showPlayerProfileModal.player_id"
            />
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { roundNameFormatter, roleBadgeClass, playerFormatter } from "@/Utility/Formatter";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import TeamRoster from "@/Pages/Teams/Module/TeamRoster.vue";
import PlayerPerformance from "@/Pages/Teams/Module/PlayerPerformance.vue";

const props = defineProps({
    game_id: {
        type: String,
        required: true,
    },
    showBoxScore: {
        type: Boolean,
        default: true,
    },
});
const showPlayerProfileModal = ref(false);
const isTeamRosterModalOpen = ref(false);
const gameDetails = ref(false);
const playerStats = ref({ home: [], away: [] });
const bestPlayer = ref(null);
const statLeaders = ref([]);
const injuredPlayers =ref([]);
const seasonLeaders = ref([]);
// Fetch the box score data
const time = ref(0); // Timer in seconds
const interval = ref(null); // Stores interval ID
const gameFinished = ref(false); // Flag for game completion

// Start the timer
const startTimer = () => {
  if (interval.value) return; // Prevent multiple intervals running

  interval.value = setInterval(() => {
    time.value++;
  }, 1000);
}

// Stop the timer
const stopTimer = () => {
  if (interval.value) {
    clearInterval(interval.value);
    interval.value = null; // Reset interval
  }
}

// Format time to mm:ss
const  formatTime  = (seconds) => {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
}

const fetchBoxScore = async () => {
    try {
        gameFinished.value = false; // Reset the game status
        startTimer(); // Start the timer
        gameDetails.value = false;
        const response = await axios.post(route("game.boxscore"), {
            game_id: props.game_id,
        });
        const data = response.data.box_score;

        gameDetails.value = data;
        playerStats.value.home = data.player_stats.home;
        playerStats.value.away = data.player_stats.away;
        bestPlayer.value = data.best_player;
        statLeaders.value = data.stat_leaders;
        injuredPlayers.value = data.injury;
        seasonLeaders.value = data.league_leaders;
        gameFinished.value = true;
        stopTimer(); // Stop the timer when the game finishes
    } catch (error) {
        console.error("Error fetching box score:", error);
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
    return sortedHomePlayers.value.slice(0, 5).map((player) => player.name);
});

const top5AwayPlayers = computed(() => {
    return sortedAwayPlayers.value.slice(0, 5).map((player) => player.name);
});

const formatInjuredPlayers = (players) => {
  const injuryMessages = [
    (player) => `${player.player_name} (${player.role}) from ${player.team_when_injured} has suffered a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `${player.team_when_injured}'s ${player.player_name} (${player.role}) is sidelined with a ${player.injury_type.replace('_', ' ')} for the next ${player.recovery_games} games.`,
    (player) => `${player.player_name} (${player.role}) from ${player.team_when_injured} is dealing with a ${player.injury_type.replace('_', ' ')} and will be out for ${player.recovery_games} games.`,
    (player) => `${player.player_name} (${player.role}) from ${player.team_when_injured} is out due to ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `Injury alert: ${player.player_name} (${player.role}) of the ${player.team_when_injured} has a ${player.injury_type.replace('_', ' ')} and will be unavailable for the next ${player.recovery_games} games.`,
    (player) => `${player.player_name} (${player.role}) of ${player.team_when_injured} will be out for ${player.recovery_games} games after suffering a ${player.injury_type.replace('_', ' ')}.`,
    (player) => `${player.role} ${player.player_name} from ${player.team_when_injured} is out due to a ${player.injury_type.replace('_', ' ')} and will be sidelined for ${player.recovery_games} games.`,
    (player) => `The ${player.team_when_injured}'s ${player.role} ${player.player_name} has suffered a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    // More dynamic and varied messages
    (player) => `Breaking news: ${player.player_name} (${player.role}) from ${player.team_when_injured} is out for ${player.recovery_games} games due to a ${player.injury_type.replace('_', ' ')}.`,
    (player) => `${player.player_name} (${player.role}), a key player for ${player.team_when_injured}, has been injured with a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `Injury update: ${player.player_name} from ${player.team_when_injured} has been sidelined with a ${player.injury_type.replace('_', ' ')} and will be unavailable for the next ${player.recovery_games} games.`,
    (player) => `Sad news for ${player.team_when_injured}: ${player.player_name} (${player.role}) is out with a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `${player.player_name} (${player.role}) from ${player.team_when_injured} will be taking a break due to a ${player.injury_type.replace('_', ' ')} for the next ${player.recovery_games} games.`,
    (player) => `${player.player_name} of ${player.team_when_injured} will be out due to ${player.injury_type.replace('_', ' ')}. He is expected to miss ${player.recovery_games} games.`,
    (player) => `${player.team_when_injured} has announced that ${player.player_name} (${player.role}) will be sidelined with a ${player.injury_type.replace('_', ' ')} for ${player.recovery_games} games.`,
    (player) => `Injury update: ${player.player_name} (${player.role}) from ${player.team_when_injured} is expected to miss ${player.recovery_games} games with a ${player.injury_type.replace('_', ' ')}.`,
    (player) => `${player.player_name} of ${player.team_when_injured} has a ${player.injury_type.replace('_', ' ')} and will miss the next ${player.recovery_games} games due to recovery.`,
    (player) => `${player.player_name} (${player.role}) from ${player.team_when_injured} will miss ${player.recovery_games} games after suffering a ${player.injury_type.replace('_', ' ')}. Get well soon!`,
    (player) => `${player.player_name} from ${player.team_when_injured} is recovering from a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `Devastating news for ${player.team_when_injured}: ${player.player_name} (${player.role}) has suffered a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `${player.team_when_injured} will have to make adjustments as ${player.player_name} (${player.role}) is out with a ${player.injury_type.replace('_', ' ')} and will miss ${player.recovery_games} games.`,
    (player) => `A tough blow for ${player.team_when_injured}: ${player.player_name} (${player.role}) is injured and will miss ${player.recovery_games} games due to a ${player.injury_type.replace('_', ' ')}.`,
    (player) => `${player.player_name} from ${player.team_when_injured} will miss ${player.recovery_games} games after sustaining a ${player.injury_type.replace('_', ' ')}. Get well soon, ${player.player_name}!`
  ];

  return players
    .map((player) => {
      // Randomly pick one of the injury messages
      const randomMessage = injuryMessages[Math.floor(Math.random() * injuryMessages.length)];
      return randomMessage(player);
    })
    .join(', ');
};

const roleClasses = (role) => {
    switch (role) {
        case "starter":
            return "bg-blue-100 text-blue-800";
        case "star player":
            return "bg-yellow-100 text-yellow-800";
        case "role player":
            return "bg-green-100 text-green-800";
        case "bench":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-200 text-gray-800"; // Default case
    }
};
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

th,
td {
    border: 1px solid #2d3748; /* Subtle borders */
}

tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1); /* Light hover effect */
}
</style>
