<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Schedules;
use App\Models\Seasons;
use App\Models\Teams;
use App\Models\PlayerGameStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlayersController extends Controller
{
    public function listPlayersV1(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'season_id' => 'nullable|integer', // Validate season_id as nullable integer
        ]);

        $teamId = $request->team_id;
        $seasonId = $request->season_id;

        // Determine the season_id to use
        if (is_null($seasonId) || $seasonId == 0) {
            // Get the latest season_id if null or 0
            $seasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');
        }

        // Get distinct player_ids from player_game_stats for the given team_id and season_id
        $playerIds = DB::table('player_game_stats')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->distinct()
            ->pluck('player_id');

        // Fetch players for the specified player_ids
        $players = DB::table('players')
            ->whereIn('id', $playerIds)
            ->get();

        // Initialize an array to hold player stats
        $playerStats = [];

        foreach ($players as $player) {
            // Fetch player game stats
            $stats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('team_id', $teamId)
                ->where('season_id', $seasonId)
                ->get();

            // Calculate totals
            $totalPoints = $stats->sum('points');
            $totalRebounds = $stats->sum('rebounds');
            $totalAssists = $stats->sum('assists');
            $totalSteals = $stats->sum('steals');
            $totalBlocks = $stats->sum('blocks');
            $totalTurnovers = $stats->sum('turnovers');
            $totalFouls = $stats->sum('fouls');
            $gamesPlayed = $stats->count();

            // Calculate averages
            $averagePointsPerGame = $gamesPlayed > 0 ? $totalPoints / $gamesPlayed : 0;
            $averageReboundsPerGame = $gamesPlayed > 0 ? $totalRebounds / $gamesPlayed : 0;
            $averageAssistsPerGame = $gamesPlayed > 0 ? $totalAssists / $gamesPlayed : 0;
            $averageStealsPerGame = $gamesPlayed > 0 ? $totalSteals / $gamesPlayed : 0;
            $averageBlocksPerGame = $gamesPlayed > 0 ? $totalBlocks / $gamesPlayed : 0;
            $averageTurnoversPerGame = $gamesPlayed > 0 ? $totalTurnovers / $gamesPlayed : 0;
            $averageFoulsPerGame = $gamesPlayed > 0 ? $totalFouls / $gamesPlayed : 0;

            // Determine player status
            $status = $player->team_id == $teamId ? 1 : 2;

            // Update status to 'free agent' if the player is inactive and status is 'transfer'
            if ($player->is_active == 0 && $status == 2) {
                $status = 0;
            }

            // Append player with stats and details in one row
            $playerStats[] = [
                'player_id' => $player->id,
                'name' => $player->name,
                'age' => $player->age,
                'role' => $player->role,
                'is_active' => $player->is_active,
                'is_rookie' => $player->is_rookie,
                'status' => $status,
                'total_points' => $totalPoints,
                'total_rebounds' => $totalRebounds,
                'total_assists' => $totalAssists,
                'total_steals' => $totalSteals,
                'total_blocks' => $totalBlocks,
                'total_turnovers' => $totalTurnovers,
                'total_fouls' => $totalFouls,
                'games_played' => $gamesPlayed,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }

        // Define role-based priority
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Sort players by role and then by points
        usort($playerStats, function ($a, $b) use ($rolePriority) {
            if ($rolePriority[$a['role']] === $rolePriority[$b['role']]) {
                return $b['total_points'] <=> $a['total_points'];
            }
            return $rolePriority[$a['role']] <=> $rolePriority[$b['role']];
        });

        return response()->json([
            'players' => $playerStats,
            'season_id' => $seasonId,
            'team_id' => $teamId,
        ]);
    }
    public function listPlayers(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'season_id' => 'nullable|integer', // Validate season_id as nullable integer
        ]);

        $teamId = $request->team_id;
        $seasonId = $request->season_id;

        // Determine the season_id to use
        if (is_null($seasonId) || $seasonId == 0) {
            // Get the latest season_id if null or 0
            $seasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');
        }

        // Get distinct player_ids from player_game_stats for the given team_id and season_id
        $playerIds = DB::table('player_game_stats')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->distinct()
            ->pluck('player_id');

        // Fetch players for the specified player_ids
        $players = DB::table('players')
            ->whereIn('id', $playerIds)
            ->get();

        // Initialize an array to hold player stats
        $playerStats = [];

        foreach ($players as $player) {
            // Fetch player game stats
            $stats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('team_id', $teamId)
                ->where('season_id', $seasonId)
                ->get();

            // Filter games where player minutes are greater than 0
            $filteredStats = $stats->filter(function ($stat) {
                return $stat->minutes > 0; // Assuming 'minutes' is the column indicating minutes played
            });

            // Calculate totals
            $totalPoints = $filteredStats->sum('points');
            $totalRebounds = $filteredStats->sum('rebounds');
            $totalAssists = $filteredStats->sum('assists');
            $totalSteals = $filteredStats->sum('steals');
            $totalBlocks = $filteredStats->sum('blocks');
            $totalTurnovers = $filteredStats->sum('turnovers');
            $totalFouls = $filteredStats->sum('fouls');
            $gamesPlayed = $filteredStats->count();

            // Calculate averages
            $averagePointsPerGame = $gamesPlayed > 0 ? $totalPoints / $gamesPlayed : 0;
            $averageReboundsPerGame = $gamesPlayed > 0 ? $totalRebounds / $gamesPlayed : 0;
            $averageAssistsPerGame = $gamesPlayed > 0 ? $totalAssists / $gamesPlayed : 0;
            $averageStealsPerGame = $gamesPlayed > 0 ? $totalSteals / $gamesPlayed : 0;
            $averageBlocksPerGame = $gamesPlayed > 0 ? $totalBlocks / $gamesPlayed : 0;
            $averageTurnoversPerGame = $gamesPlayed > 0 ? $totalTurnovers / $gamesPlayed : 0;
            $averageFoulsPerGame = $gamesPlayed > 0 ? $totalFouls / $gamesPlayed : 0;

            // Determine player status
            $status = $player->team_id == $teamId ? 1 : 2;

            // Update status to 'free agent' if the player is inactive and status is 'transfer'
            if ($player->is_active == 0 && $status == 2) {
                $status = 0;
            }

            // Append player with stats and details in one row
            $playerStats[] = [
                'player_id' => $player->id,
                'name' => $player->name,
                'age' => $player->age,
                'role' => $player->role,
                'is_active' => $player->is_active,
                'is_rookie' => $player->is_rookie,
                'retirement_age' => $player->retirement_age,
                'status' => $status,
                'total_points' => $totalPoints,
                'total_rebounds' => $totalRebounds,
                'total_assists' => $totalAssists,
                'total_steals' => $totalSteals,
                'total_blocks' => $totalBlocks,
                'total_turnovers' => $totalTurnovers,
                'total_fouls' => $totalFouls,
                'games_played' => $gamesPlayed,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }

        // Define role-based priority
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Sort players by role and then by points
        usort($playerStats, function ($a, $b) use ($rolePriority) {
            if ($rolePriority[$a['role']] === $rolePriority[$b['role']]) {
                return $b['total_points'] <=> $a['total_points'];
            }
            return $rolePriority[$a['role']] <=> $rolePriority[$b['role']];
        });

        return response()->json([
            'players' => $playerStats,
            'season_id' => $seasonId,
            'team_id' => $teamId,
        ]);
    }

    public function getFreeAgents(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = $request->input('itemsperpage', 10); // Number of items per page
        $currentPage = $request->input('page_num', 1); // Current page number
        $search = $request->input('search', ''); // Search term

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

        // Define role priorities
        $rolePriorities = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Build the query with optional search filter
        $query = Player::select('id as player_id', 'name', 'age', 'role', 'is_active', 'contract_years', 'team_id')
            ->where('contract_years', 0)
            ->where('is_active', 1);

        // Apply search filter if provided
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Add role priority sorting
        $query->orderByRaw(
            "FIELD(role, 'star player', 'starter', 'role player', 'bench')"
        );

        // Get total number of records
        $total = $query->count();

        // Fetch the paginated data
        $freeAgents = $query->offset($offset)
            ->limit($perPage)
            ->get();

        // Calculate total pages
        $totalPages = (int) ceil($total / $perPage);

        return response()->json([
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'free_agents' => $freeAgents,
        ]);
    }
    public function getAllPlayers(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = $request->input('itemsperpage', 10); // Number of items per page
        $currentPage = $request->input('page_num', 1); // Current page number
        $search = $request->input('search', ''); // Search term

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

        // Build the query with optional search filter and join with teams
        $query = DB::table('players')
            ->select('players.id as player_id', 'players.name', 'players.age', 'players.role', 'players.is_active','players.retirement_age', 'players.contract_years', DB::raw("IF(players.team_id = 0, 'none', teams.name) as team_name"))
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id');

        // Apply search filter if provided
        if ($search) {
            $query->where('players.name', 'like', "%{$search}%");
        }

        // Add sorting by is_active status, then by role priority
        $query->orderBy('players.is_active', 'desc') // Active players first
            ->orderByRaw("FIELD(players.role, 'star player', 'starter', 'role player', 'bench')");

        // Get total number of records
        $total = $query->count();

        // Fetch the paginated data
        $freeAgents = $query->offset($offset)
            ->limit($perPage)
            ->get();

        // Calculate total pages
        $totalPages = (int) ceil($total / $perPage);

        return response()->json([
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'free_agents' => $freeAgents,
        ]);
    }




    // Add a player to a team with random attributes
    public function addPlayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        // Check if the team already has 12 players
        $playerCount = Player::where('team_id', $request->team_id)
            ->where('is_active', 1) // Ensure players are active
            ->count();

        if ($playerCount >= 12) {
            return response()->json([
                'error' => true,
                'message' => 'Team already has 12 players. Cannot add more.',
            ], 400);
        }

        // Check if a player with the same name already exists in any team
        $existingPlayer = Player::where('name', $request->name)->first();
        if ($existingPlayer) {
            return response()->json([
                'error' => true,
                'message' => 'A player with this name already exists in another team.',
            ], 400);
        }

        // Generate random attributes
        $age = mt_rand(18, 25);
        $retirementAge = rand($age + 1, 45); // Retirement age should be greater than current age
        $injuryPronePercentage = rand(0, 100); // Random injury-prone percentage between 0 and 100
        $contractYears = rand(1, 5); // Random contract years between 1 and 5

        // Randomize player role
        $roles = ['starter', 'star player', 'role player', 'bench'];
        $role = $roles[array_rand($roles)];

        // Calculate contract expiration date
        $contractExpiresAt = Carbon::now()->addYears($contractYears);

        $player = Player::create([
            'name' => $request->name,
            'team_id' => $request->team_id,
            'age' => $age,
            'retirement_age' => $retirementAge,
            'injury_prone_percentage' => $injuryPronePercentage,
            'contract_years' => $contractYears,
            'contract_expires_at' => $contractExpiresAt,
            'is_active' => true,
            'role' => $role,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Player added successfully',
            'player' => $player,
        ]);
    }
    public function addFreeAgentPlayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:players,name',
        ]);

        // Check if the team already has 12 players
        $playerCount = Player::where('team_id', $request->team_id)
            ->where('is_active', 1) // Ensure players are active
            ->count();

        if ($playerCount >= 12) {
            return response()->json([
                'error' => true,
                'message' => 'Team already has 12 players. Cannot add more.',
            ], 400);
        }

        // Check if a player with the same name already exists in any team
        $existingPlayer = Player::where('name', $request->name)->first();
        if ($existingPlayer) {
            return response()->json([
                'error' => true,
                'message' => 'A player with this name already exists in another team.',
            ], 400);
        }

        // Generate random attributes
        $age = mt_rand(18, 25);
        $retirementAge = rand($age + 1, 45); // Retirement age should be greater than current age
        $injuryPronePercentage = rand(0, 100); // Random injury-prone percentage between 0 and 100
        $contractYears = rand(1, 5); // Random contract years between 1 and 5

        // Randomize player role
        $roles = ['starter', 'star player', 'role player', 'bench'];
        $role = $roles[array_rand($roles)];

        // Calculate contract expiration date
        $contractExpiresAt = Carbon::now();

        $player = Player::create([
            'name' => $request->name,
            'team_id' => 0,
            'age' => $age,
            'retirement_age' => $retirementAge,
            'injury_prone_percentage' => $injuryPronePercentage,
            'contract_years' => 0,
            'contract_expires_at' => $contractExpiresAt,
            'is_active' => true,
            'role' => $role,
            'is_rookie' => true,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Player added successfully',
            'player' => $player,
        ]);
    }



    // Generate a random age ensuring it is either at least 19 or exactly 30
    private function generateRandomAge()
    {
        $possibleAges = [19, 30];
        $randomAge = $possibleAges[array_rand($possibleAges)];

        // Return the age with a 50% chance of being 19 or 30
        return $randomAge;
    }

    // Waive a player (make them inactive)
    public function waivePlayer(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:players,id',
        ]);

        $player = Player::findOrFail($request->id);
        $player->update(['is_active' => false, 'team_id' => null]);

        return response()->json([
            'message' => 'Player waived successfully',
            'player' => $player,
        ]);
    }

    // Extend player's contract
    public function extendContract(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:players,id',
            'additional_years' => 'required|integer|min:1|max:5',
        ]);

        $player = Player::findOrFail($request->id);

        $newContractEnd = $player->contract_expires_at
            ? $player->contract_expires_at->addYears($request->additional_years)
            : now()->addYears($request->additional_years);

        $player->update([
            'contract_years' => min($player->contract_years + $request->additional_years, 5),
            'contract_expires_at' => $newContractEnd,
        ]);

        return response()->json([
            'message' => 'Contract extended successfully',
            'player' => $player,
        ]);
    }
    public function getBoxScore(Request $request)
    {
        // Validate the request
        $request->validate([
            'game_id' => 'required|string',
        ]);

        $game_id = $request->game_id;

        // Fetch game details from the schedules table
        $game = \DB::table('schedule_view')
            ->where('game_id', $game_id)
            ->first();

        if (!$game) {
            return response()->json([
                'message' => 'Game not found',
            ], 404);
        }

        // Fetch player stats with roles and minutes from the player_game_stats table
        $playerStats = \DB::table('player_game_stats')
            ->where('game_id', $game_id)
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id') // Join with teams to get team names
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'players.is_rookie as is_rookie',
                'player_game_stats.team_id',
                'teams.name as team_name',
                'players.role as player_role',
                'player_game_stats.points',
                'player_game_stats.assists',
                'player_game_stats.rebounds',
                'player_game_stats.steals',
                'player_game_stats.blocks',
                'player_game_stats.turnovers',
                'player_game_stats.fouls',
                'player_game_stats.minutes'
            )
            ->get()
            ->keyBy('player_id');

        // Fetch all players that might be relevant to the game (ignoring team_id here)
        $players = \DB::table('players')
            ->whereIn('id', $playerStats->pluck('player_id')->toArray())
            ->get()
            ->keyBy('id');

        // Determine the best player based on overall stats
        $bestPlayer = $playerStats->sort(function ($a, $b) {
            $aStats = $a->points + $a->assists + $a->rebounds + $a->steals + $a->blocks;
            $bStats = $b->points + $b->assists + $b->rebounds + $b->steals + $b->blocks;
            return $bStats <=> $aStats;
        })->first();

        // Determine the winning team
        $winningTeamId = $game->home_score > $game->away_score ? $game->home_id : $game->away_id;

        // Filter player stats for the winning team
        $winningTeamPlayersStats = $playerStats->filter(function ($stat) use ($winningTeamId) {
            return $stat->team_id == $winningTeamId;
        });

        // Determine the best player of the winning team
        $bestWinningTeamPlayer = $winningTeamPlayersStats->sort(function ($a, $b) {
            $aStats = $a->points + $a->assists + $a->rebounds + $a->steals + $a->blocks;
            $bStats = $b->points + $b->assists + $b->rebounds + $b->steals + $b->blocks;
            return $bStats <=> $aStats;
        })->first();

        // Fetch player details for the best player of the winning team if exists
        $bestWinningTeamPlayerDetails = $bestWinningTeamPlayer ? [
            'name' => $bestWinningTeamPlayer->player_name,
            'team' => $bestWinningTeamPlayer->team_name,
            'points' => $bestWinningTeamPlayer->points,
            'assists' => $bestWinningTeamPlayer->assists,
            'rebounds' => $bestWinningTeamPlayer->rebounds,
            'steals' => $bestWinningTeamPlayer->steals,
            'blocks' => $bestWinningTeamPlayer->blocks,
            'turnovers' => $bestWinningTeamPlayer->turnovers,
            'fouls' => $bestWinningTeamPlayer->fouls,
            'role' => $bestWinningTeamPlayer->player_role,
            'minutes' => $bestWinningTeamPlayer->minutes,
        ] : null;

        // Split player stats into home and away teams, using the game team IDs
        $homeTeamPlayers = $players->filter(function ($player) use ($game, $playerStats) {
            $playerStat = $playerStats->get($player->id);
            return $playerStat && $playerStat->team_id == $game->home_id;
        });

        $awayTeamPlayers = $players->filter(function ($player) use ($game, $playerStats) {
            $playerStat = $playerStats->get($player->id);
            return $playerStat && $playerStat->team_id == $game->away_id;
        });

        // Convert home team player stats to an array, including those with no recorded stats
        $homeTeamPlayersArray = $homeTeamPlayers->map(function ($player) use ($playerStats) {
            $stats = $playerStats->get($player->id);

            return [
                'player_id' => $player->id,
                'name' => $player->name,
                'role' => $player->role,
                'is_rookie' => $player->is_rookie,
                'points' => $stats ? $stats->points : 0,
                'assists' => $stats ? $stats->assists : 0,
                'rebounds' => $stats ? $stats->rebounds : 0,
                'steals' => $stats ? $stats->steals : 0,
                'blocks' => $stats ? $stats->blocks : 0,
                'turnovers' => $stats ? $stats->turnovers : 0,
                'fouls' => $stats ? $stats->fouls : 0,
                'minutes' => $stats ? $stats->minutes : 'DNP',
            ];
        })->values()->toArray();

        // Convert away team player stats to an array, including those with no recorded stats
        $awayTeamPlayersArray = $awayTeamPlayers->map(function ($player) use ($playerStats) {
            $stats = $playerStats->get($player->id);

            return [
                'player_id' => $player->id,
                'name' => $player->name,
                'role' => $player->role,
                'is_rookie' => $player->is_rookie,
                'points' => $stats ? $stats->points : 0,
                'assists' => $stats ? $stats->assists : 0,
                'rebounds' => $stats ? $stats->rebounds : 0,
                'steals' => $stats ? $stats->steals : 0,
                'blocks' => $stats ? $stats->blocks : 0,
                'turnovers' => $stats ? $stats->turnovers : 0,
                'fouls' => $stats ? $stats->fouls : 0,
                'minutes' => $stats ? $stats->minutes : 'DNP',
            ];
        })->values()->toArray();

        // Format data for box score
        $boxScore = [
            'game_id' => $game->game_id,
            'round' => $game->round,
            'home_team' => [
                'team_id' => $game->home_id,
                'name' => $game->home_team_name,
                'score' => $game->home_score,
            ],
            'away_team' => [
                'team_id' => $game->away_id,
                'name' => $game->away_team_name,
                'score' => $game->away_score,
            ],
            'player_stats' => [
                'home' => $homeTeamPlayersArray,
                'away' => $awayTeamPlayersArray,
            ],
            'best_player' => $bestWinningTeamPlayerDetails,
            'total_players_played' => $playerStats->count(),
        ];

        return response()->json([
            'box_score' => $boxScore,
        ]);
    }
    public function getBestPlayersInConference(Request $request)
    {
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'conference_id' => 'required|exists:conferences,id',
            'round' => 'required|min:0',
        ]);

        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;
        $round = $request->round;

        $excludedRounds = ['quarter_finals', 'round_of_16', 'round_of_32', 'semi_finals', 'interconference_semi_finals', 'finals'];

        // Fetch player stats for the given season and conference
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')  // Join with the schedule table
            ->where('teams.conference_id', $conferenceId)
            ->where('player_game_stats.season_id', $seasonId)
            ->whereNotIn('schedules.round', $excludedRounds)  // Exclude specified rounds using the schedule table
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.team_id',
                'teams.name as team_name',
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('SUM(player_game_stats.minutes) as total_minutes'), // Added to help determine DNP status
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played')
            )
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name')
            ->get();

        // Initialize an array to hold formatted player stats
        $formattedPlayerStats = [];

        $allRoundsSimulated = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('status', 1)
            ->doesntExist();

        foreach ($playerStats as $stats) {
            // Calculate averages
            $gamesPlayed = $allRoundsSimulated ? $stats->games_played : (float) ($round + 1) * 2;

            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $gamesPlayed : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $gamesPlayed : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $gamesPlayed : 0;
            $averageStealsPerGame = $stats->games_played > 0 ? $stats->total_steals / $gamesPlayed : 0;
            $averageBlocksPerGame = $stats->games_played > 0 ? $stats->total_blocks / $gamesPlayed : 0;
            $averageTurnoversPerGame = $stats->games_played > 0 ? $stats->total_turnovers / $gamesPlayed : 0;
            $averageFoulsPerGame = $stats->games_played > 0 ? $stats->total_fouls / $gamesPlayed : 0;

            // Calculate composite score
            $compositeScore = ($stats->total_points * 1.5) +
                ($stats->total_rebounds * 1.2) +
                ($stats->total_assists * 1.2) +
                ($stats->total_steals * 1.5) +
                ($stats->total_blocks * 1.5) -
                ($stats->total_turnovers * 1.0) -
                ($stats->total_fouls * 0.5);

            // Append player with stats and team name
            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'team_id' => $stats->team_id,
                'total_points' => $stats->total_points,
                'total_rebounds' => $stats->total_rebounds,
                'total_assists' => $stats->total_assists,
                'total_steals' => $stats->total_steals,
                'total_blocks' => $stats->total_blocks,
                'total_turnovers' => $stats->total_turnovers,
                'total_fouls' => $stats->total_fouls,
                'games_played' => $stats->games_played,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
                'composite_score' => $compositeScore,
            ];
        }

        // Sort players by composite score in descending order and get the top 15
        $topPlayers = collect($formattedPlayerStats)
            ->sortByDesc('composite_score')
            ->take(15)
            ->values()
            ->toArray();

        return response()->json([
            'best_players' => $topPlayers,
        ]);
    }

    public function getPlayerPlayoffPerformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player stats for the given player across specified playoff rounds
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id') // Join with seasons table
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.team_id',
                'teams.name as team_name',
                'teams.conference_id',
                'player_game_stats.season_id',
                'seasons.name as season_name', // Select season name
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played') // Exclude DNP games
            )
            ->where('player_game_stats.player_id', $playerId)
            ->whereIn('schedules.round', ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals']) // Filter by playoff rounds
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'teams.conference_id', 'player_game_stats.season_id', 'seasons.name')
            ->orderBy('player_game_stats.season_id', 'desc') // Sort by season_id in descending order
            ->get();

        if ($playerStats->isEmpty()) {
            return response()->json([
                'error' => 'No stats found for the given player.',
            ], 404);
        }

        // Initialize an array to hold formatted player stats
        $formattedPlayerStats = [];

        foreach ($playerStats as $stats) {
            // Calculate averages
            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $stats->games_played : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $stats->games_played : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $stats->games_played : 0;
            $averageStealsPerGame = $stats->games_played > 0 ? $stats->total_steals / $stats->games_played : 0;
            $averageBlocksPerGame = $stats->games_played > 0 ? $stats->total_blocks / $stats->games_played : 0;
            $averageTurnoversPerGame = $stats->games_played > 0 ? $stats->total_turnovers / $stats->games_played : 0;
            $averageFoulsPerGame = $stats->games_played > 0 ? $stats->total_fouls / $stats->games_played : 0;

            // Append player with stats and team name
            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'team_id' => $stats->team_id,
                'conference_id' => $stats->conference_id,
                'season_id' => $stats->season_id,
                'season_name' => $stats->season_name, // Add season name
                'total_points' => $stats->total_points,
                'total_rebounds' => $stats->total_rebounds,
                'total_assists' => $stats->total_assists,
                'total_steals' => $stats->total_steals,
                'total_blocks' => $stats->total_blocks,
                'total_turnovers' => $stats->total_turnovers,
                'total_fouls' => $stats->total_fouls,
                'games_played' => $stats->games_played,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }

        return response()->json([
            'player_stats' => $formattedPlayerStats,
        ]);
    }
    public function getPlayerSeasonPerformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player stats for the given player excluding the specified playoff rounds
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id') // Join with seasons table
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.team_id',
                'teams.name as team_name',
                'teams.conference_id',
                'player_game_stats.season_id',
                'seasons.name as season_name', // Select season name
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played') // Exclude DNP games
            )
            ->where('player_game_stats.player_id', $playerId)
            ->whereNotIn('schedules.round', ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals']) // Exclude specific playoff rounds
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'teams.conference_id', 'player_game_stats.season_id', 'seasons.name')
            ->orderBy('player_game_stats.season_id', 'desc') // Sort by season_id in descending order
            ->get();

        if ($playerStats->isEmpty()) {
            return response()->json([
                'error' => 'No stats found for the given player.',
            ], 404);
        }

        // Initialize an array to hold formatted player stats
        $formattedPlayerStats = [];

        foreach ($playerStats as $stats) {
            // Calculate averages
            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $stats->games_played : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $stats->games_played : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $stats->games_played : 0;
            $averageStealsPerGame = $stats->games_played > 0 ? $stats->total_steals / $stats->games_played : 0;
            $averageBlocksPerGame = $stats->games_played > 0 ? $stats->total_blocks / $stats->games_played : 0;
            $averageTurnoversPerGame = $stats->games_played > 0 ? $stats->total_turnovers / $stats->games_played : 0;
            $averageFoulsPerGame = $stats->games_played > 0 ? $stats->total_fouls / $stats->games_played : 0;

            // Append player with stats and team name
            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'team_id' => $stats->team_id,
                'conference_id' => $stats->conference_id,
                'season_id' => $stats->season_id,
                'season_name' => $stats->season_name, // Add season name
                'total_points' => $stats->total_points,
                'total_rebounds' => $stats->total_rebounds,
                'total_assists' => $stats->total_assists,
                'total_steals' => $stats->total_steals,
                'total_blocks' => $stats->total_blocks,
                'total_turnovers' => $stats->total_turnovers,
                'total_fouls' => $stats->total_fouls,
                'games_played' => $stats->games_played,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }

        return response()->json([
            'player_stats' => $formattedPlayerStats,
        ]);
    }

    public function getPlayerMainPerformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player and team details
        $playerDetails = \DB::table('players')
            ->join('teams', 'players.team_id', '=', 'teams.id', 'left') // Join teams table to get team details
            ->where('players.id', $playerId)
            ->select('players.id as player_id', 'players.name as player_name', 'teams.name as team_name', 'players.role', 'players.contract_years', 'players.is_rookie')
            ->first();

        if (!$playerDetails) {
            return response()->json([
                'error' => 'Player not found.',
            ], 404);
        }

        // Fetch playoff performance
        $playoffPerformance = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id')
            ->select(
                \DB::raw('SUM(CASE WHEN schedules.round = "round_of_16" THEN 1 ELSE 0 END) as round_of_16'),
                \DB::raw('SUM(CASE WHEN schedules.round = "quarter_finals" THEN 1 ELSE 0 END) as quarter_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "semi_finals" THEN 1 ELSE 0 END) as semi_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "interconference_semi_finals" THEN 1 ELSE 0 END) as interconference_semi_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "finals" THEN 1 ELSE 0 END) as finals')
            )
            ->where('player_game_stats.player_id', $playerId)
            ->first();

        // Set default values if no performance data
        $playoffPerformance = $playoffPerformance ?: (object)[
            'round_of_16' => 0,
            'quarter_finals' => 0,
            'semi_finals' => 0,
            'interconference_semi_finals' => 0,
            'finals' => 0,
        ];

        // Fetch MVP count and seasons
        $mvpData = \DB::table('seasons')
            ->where('seasons.finals_mvp_id', $playerId)
            ->select('seasons.name as season_name')
            ->get();

        $mvpCount = $mvpData->count();

        // Fetch championship count and season names
        $championships = \DB::table('seasons')
            ->join('player_game_stats', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->select('seasons.name as season_name', 'seasons.finals_winner_name as championship_team')
            ->where('player_game_stats.player_id', $playerId)
            ->where('schedules.round', 'finals')
            ->whereColumn('seasons.id', 'player_game_stats.season_id')
            ->whereExists(function ($query) use ($playerId) {
                $query->select(\DB::raw(1))
                    ->from('schedules as s')
                    ->join('player_game_stats as pg', 's.game_id', '=', 'pg.game_id')
                    ->where('pg.team_id', '=', \DB::raw('player_game_stats.team_id'))
                    ->where('s.round', 'finals')
                    ->where('pg.player_id', $playerId)
                    ->whereColumn('pg.season_id', 'player_game_stats.season_id')
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereColumn('s.home_id', 'player_game_stats.team_id')
                                ->whereColumn('s.home_score', '>', 's.away_score');
                        })
                        ->orWhere(function ($q) {
                            $q->whereColumn('s.away_id', 'player_game_stats.team_id')
                                ->whereColumn('s.away_score', '>', 's.home_score');
                        });
                    });
            })
            ->groupBy('seasons.name', 'seasons.finals_winner_name')
            ->get();

            $conference_championships = \DB::table('seasons')
    ->join('player_game_stats', 'seasons.id', '=', 'player_game_stats.season_id')
    ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
    ->join('teams as team', 'player_game_stats.team_id', '=', 'team.id') // Join with player’s team
    ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
    ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
    ->select(
        'seasons.name as season_name',
        \DB::raw('CASE
            WHEN (schedules.home_id = team.id AND schedules.home_score > schedules.away_score) THEN home_team.name
            WHEN (schedules.away_id = team.id AND schedules.away_score > schedules.home_score) THEN away_team.name
            ELSE NULL
        END as championship_team')
    )
    ->where('player_game_stats.player_id', $playerId)
    ->where('schedules.round', 'semi_finals')
    ->whereColumn('seasons.id', 'player_game_stats.season_id')
    ->whereExists(function ($query) use ($playerId) {
        $query->select(\DB::raw(1))
            ->from('schedules as s')
            ->join('player_game_stats as pg', 's.game_id', '=', 'pg.game_id')
            ->where('pg.team_id', '=', \DB::raw('player_game_stats.team_id'))
            ->where('s.round', 'semi_finals')
            ->where('pg.player_id', $playerId)
            ->whereColumn('pg.season_id', 'player_game_stats.season_id')
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->whereColumn('s.home_id', 'player_game_stats.team_id')
                        ->whereColumn('s.home_score', '>', 's.away_score');
                })
                ->orWhere(function ($q) {
                    $q->whereColumn('s.away_id', 'player_game_stats.team_id')
                        ->whereColumn('s.away_score', '>', 's.home_score');
                });
            });
    })
    ->groupBy('seasons.name', 'team.id', 'home_team.name', 'away_team.name', 'schedules.home_id', 'schedules.away_id', 'schedules.home_score', 'schedules.away_score')
    ->get();


        // Fetch career high stats
        $careerHighs = \DB::table('player_game_stats')
            ->select(
                \DB::raw('MAX(points) as career_high_points'),
                \DB::raw('MAX(rebounds) as career_high_rebounds'),
                \DB::raw('MAX(assists) as career_high_assists'),
                \DB::raw('MAX(steals) as career_high_steals'),
                \DB::raw('MAX(blocks) as career_high_blocks'),
                \DB::raw('MAX(turnovers) as career_high_turnovers'),
                \DB::raw('MAX(fouls) as career_high_fouls')
            )
            ->where('player_id', $playerId)
            ->first();

        return response()->json([
            'player_details' => $playerDetails,
            'playoff_performance' => $playoffPerformance,
            'mvp_count' => $mvpCount,
            'mvp_seasons' => $mvpData->pluck('season_name'),
            'championships' => $championships,
            'conference_championships' => $conference_championships,
            'career_highs' => $careerHighs,
        ]);
    }

    public function getPlayerGameLogsV1(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'season_id' => 'required|exists:seasons,id',
            'page_num' => 'required|integer|min:1',
            'itemsperpage' => 'required|integer|min:1',
        ]);

        $playerId = $request->player_id;
        $seasonId = $request->season_id;
        $page = $request->page_num;
        $perPage = $request->itemsperpage;

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Fetch player game logs for the given player and season with pagination
        $playerGameLogs = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams as player_team', 'player_game_stats.team_id', '=', 'player_team.id') // Join with player's team
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id') // Join with seasons table
            ->leftJoin('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
            ->leftJoin('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
            ->select(
                'player_game_stats.id as stat_id', // Include player_game_stats.id in the select
                'player_game_stats.game_id',
                'player_team.name as team_name',
                \DB::raw('CASE
                    WHEN player_game_stats.team_id = schedules.home_id THEN home_team.name
                    ELSE away_team.name
                END as opponent_team_name'), // Determine opponent team name
                'schedules.round as round', // Add round info
                'seasons.name as season_name', // Include season name
                \DB::raw('player_game_stats.points as game_points'),
                \DB::raw('player_game_stats.rebounds as game_rebounds'),
                \DB::raw('player_game_stats.assists as game_assists'),
                \DB::raw('player_game_stats.steals as game_steals'),
                \DB::raw('player_game_stats.blocks as game_blocks'),
                \DB::raw('player_game_stats.turnovers as game_turnovers'),
                \DB::raw('player_game_stats.fouls as game_fouls'),
                'player_game_stats.minutes as game_minutes',
                \DB::raw('(CASE
                    WHEN player_game_stats.team_id = schedules.home_id THEN
                        (CASE WHEN schedules.home_score > schedules.away_score THEN "Win" ELSE "Loss" END)
                    ELSE
                        (CASE WHEN schedules.away_score > schedules.home_score THEN "Win" ELSE "Loss" END)
                END) as game_result') // Determine win/loss
            )
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->orderBy('player_game_stats.id', 'desc') // Order by player_game_stats.id in descending order
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Fetch total count of records for pagination info
        $totalRecords = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->count();

        // Prepare pagination metadata
        $totalPages = ceil($totalRecords / $perPage);

        // Prepare response
        return response()->json([
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'game_logs' => $playerGameLogs,
        ]);
    }
    public function getPlayerGameLogs(Request $request)
{
    // Validate the request data
    $request->validate([
        'player_id' => 'required|exists:players,id',
        'season_id' => 'required|exists:seasons,id',
        'page_num' => 'required|integer|min:1',
        'itemsperpage' => 'required|integer|min:1',
    ]);

    $playerId = $request->player_id;
    $seasonId = $request->season_id;
    $page = $request->page_num;
    $perPage = $request->itemsperpage;

    // Calculate offset
    $offset = ($page - 1) * $perPage;

    // Fetch player game logs for the given player and season with pagination
    $playerGameLogs = \DB::table('player_game_stats')
        ->join('players', 'player_game_stats.player_id', '=', 'players.id')
        ->join('teams as player_team', 'player_game_stats.team_id', '=', 'player_team.id') // Join with player's team to get team name
        ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
        ->join('seasons', 'schedules.season_id', '=', 'seasons.id') // Join with seasons table
        ->leftJoin('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
        ->leftJoin('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
        ->select(
            'player_game_stats.id as stat_id', // Include player_game_stats.id in the select
            'player_game_stats.game_id',
            'player_team.name as team_name', // Player's team name
            \DB::raw('CASE
                WHEN player_game_stats.team_id = schedules.home_id THEN away_team.name
                ELSE home_team.name
            END as opponent_team_name'), // Determine opponent team name
            'schedules.round as round', // Add round info
            'seasons.name as season_name', // Include season name
            \DB::raw('player_game_stats.points as game_points'),
            \DB::raw('player_game_stats.rebounds as game_rebounds'),
            \DB::raw('player_game_stats.assists as game_assists'),
            \DB::raw('player_game_stats.steals as game_steals'),
            \DB::raw('player_game_stats.blocks as game_blocks'),
            \DB::raw('player_game_stats.turnovers as game_turnovers'),
            \DB::raw('player_game_stats.fouls as game_fouls'),
            'player_game_stats.minutes as game_minutes',
            \DB::raw('(CASE
                WHEN player_game_stats.team_id = schedules.home_id THEN
                    (CASE WHEN schedules.home_score > schedules.away_score THEN "Win" ELSE "Loss" END)
                ELSE
                    (CASE WHEN schedules.away_score > schedules.home_score THEN "Win" ELSE "Loss" END)
            END) as game_result') // Determine win/loss
        )
        ->where('player_game_stats.player_id', $playerId)
        ->where('player_game_stats.season_id', $seasonId)
        ->orderBy('player_game_stats.id', 'desc') // Order by player_game_stats.id in descending order
        ->offset($offset)
        ->limit($perPage)
        ->get();

    // Fetch total count of records for pagination info
    $totalRecords = \DB::table('player_game_stats')
        ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
        ->where('player_game_stats.player_id', $playerId)
        ->where('player_game_stats.season_id', $seasonId)
        ->count();

    // Prepare pagination metadata
    $totalPages = ceil($totalRecords / $perPage);

    // Prepare response
    return response()->json([
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords,
        'game_logs' => $playerGameLogs,
    ]);
}



}
