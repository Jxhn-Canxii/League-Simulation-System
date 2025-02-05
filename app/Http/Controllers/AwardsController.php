<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class AwardsController extends Controller
{
    public function index()
    {
        return Inertia::render('Awards/Index', [
            'status' => session('status'),
        ]);
    }
    /**
     * Store aggregated stats of a player's performance for a season in the player_season_stats table.
     * If 'is_last' is true, update the latest season's status to 9.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function storeplayerseasonstatsV1(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        // Get the team_id from the request
        $teamId = $request->input('team_id');

        // Get the latest season ID or set it to 12 if it doesn’t exist
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id') ?? 1;

        // Get all players from the team
        $players = DB::table('players')
            ->where('team_id', $teamId)
            ->get();

        foreach ($players as $player) {
            // Check if the player has stats in player_game_stats for the latest season
            $hasStats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->exists();

            if ($hasStats) {
                // Calculate stats if stats are found for the player
                $playerStats = DB::table('player_game_stats')
                    ->where('player_id', $player->id)
                    ->where('season_id', $latestSeasonId)
                    ->select(
                        'player_id',
                        DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as total_games_played'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) as total_points'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) as total_rebounds'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) as total_assists'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) as total_steals'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) as total_blocks'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) as total_turnovers'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) as total_fouls'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN minutes ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_minutes_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_points_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_rebounds_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_assists_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_steals_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_blocks_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_turnovers_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_fouls_per_game')
                    )
                    ->groupBy('player_id')
                    ->first();
            } else {
                // Set all stats to 0 if no stats are found
                $playerStats = (object) [
                    'player_id' => $player->id,
                    'total_games_played' => 0,
                    'total_points' => 0,
                    'total_rebounds' => 0,
                    'total_assists' => 0,
                    'total_steals' => 0,
                    'total_blocks' => 0,
                    'total_turnovers' => 0,
                    'total_fouls' => 0,
                    'avg_minutes_per_game' => 0,
                    'avg_points_per_game' => 0,
                    'avg_rebounds_per_game' => 0,
                    'avg_assists_per_game' => 0,
                    'avg_steals_per_game' => 0,
                    'avg_blocks_per_game' => 0,
                    'avg_turnovers_per_game' => 0,
                    'avg_fouls_per_game' => 0,
                ];
            }

            // Get the player's role for the specified season
            $playerRating = DB::table('player_ratings')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->first();

            // Insert or update the player's season stats into the player_season_stats table
            DB::table('player_season_stats')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'season_id' => $latestSeasonId,
                ],
                [
                    'team_id' => $teamId,
                    'role' => $playerRating->role ?? $player->role,  // Role from player_ratings or default
                    'avg_minutes_per_game' => $playerStats->avg_minutes_per_game,
                    'avg_points_per_game' => $playerStats->avg_points_per_game,
                    'avg_rebounds_per_game' => $playerStats->avg_rebounds_per_game,
                    'avg_assists_per_game' => $playerStats->avg_assists_per_game,
                    'avg_steals_per_game' => $playerStats->avg_steals_per_game,
                    'avg_blocks_per_game' => $playerStats->avg_blocks_per_game,
                    'avg_turnovers_per_game' => $playerStats->avg_turnovers_per_game,
                    'avg_fouls_per_game' => $playerStats->avg_fouls_per_game,

                    // Total stats
                    'total_games_played' => $playerStats->total_games_played,  // Add total_games_played here
                    'total_points' => $playerStats->total_points,
                    'total_rebounds' => $playerStats->total_rebounds,
                    'total_assists' => $playerStats->total_assists,
                    'total_steals' => $playerStats->total_steals,
                    'total_blocks' => $playerStats->total_blocks,
                    'total_turnovers' => $playerStats->total_turnovers,
                    'total_fouls' => $playerStats->total_fouls,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Player season stats stored successfully.']);
    }
    public function storeallplayerseasonstats()
    {
        // Validate the incoming request


        // Get the latest season ID or set it to 12 if it doesn’t exist
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id') ?? 1;
        // Get all players from the team
        $players = DB::table('players')
            ->where('team_id', '>', 0)  // Ensure team_id is greater than 0
            ->where('is_active', true)  // Ensure the player is active
            ->get();


        foreach ($players as $player) {
            // Check if the player has stats in player_game_stats for the latest season
            $hasStats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->exists();
            // Query to count the total games played for a team in a given season
            $gamesPlayedCount = $this->totalRegularSeasonGames($latestSeasonId,$player->team_id);

            if ($hasStats) {
                // Calculate stats if stats are found for the player
                $playerStats = DB::table('player_game_stats')
                    ->where('player_id', $player->id)
                    ->where('season_id', $latestSeasonId)
                    ->select(
                        'player_id',
                        'team_id',
                        DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as total_games_played'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) as total_points'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) as total_rebounds'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) as total_assists'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) as total_steals'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) as total_blocks'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) as total_turnovers'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) as total_fouls'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN minutes ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_minutes_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_points_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_rebounds_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_assists_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_steals_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_blocks_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_turnovers_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_fouls_per_game')
                    )
                    ->groupBy('player_id', 'player_game_stats.team_id')
                    ->first();
            } else {
                // Set all stats to 0 if no stats are found
                $playerStats = (object) [
                    'player_id' => $player->id,
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => 0,
                    'total_points' => 0,
                    'total_rebounds' => 0,
                    'total_assists' => 0,
                    'total_steals' => 0,
                    'total_blocks' => 0,
                    'total_turnovers' => 0,
                    'total_fouls' => 0,
                    'avg_minutes_per_game' => 0,
                    'avg_points_per_game' => 0,
                    'avg_rebounds_per_game' => 0,
                    'avg_assists_per_game' => 0,
                    'avg_steals_per_game' => 0,
                    'avg_blocks_per_game' => 0,
                    'avg_turnovers_per_game' => 0,
                    'avg_fouls_per_game' => 0,
                ];
            }

            // Get the player's role for the specified season
            $playerRating = DB::table('player_ratings')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->first();

            // Insert or update the player's season stats into the player_season_stats table
            DB::table('player_season_stats')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'season_id' => $latestSeasonId,
                ],
                [
                    'team_id' =>  $player->team_id,
                    'role' => $playerRating->role ?? $player->role,  // Role from player_ratings or default
                    'avg_minutes_per_game' => $playerStats->avg_minutes_per_game,
                    'avg_points_per_game' => $playerStats->avg_points_per_game,
                    'avg_rebounds_per_game' => $playerStats->avg_rebounds_per_game,
                    'avg_assists_per_game' => $playerStats->avg_assists_per_game,
                    'avg_steals_per_game' => $playerStats->avg_steals_per_game,
                    'avg_blocks_per_game' => $playerStats->avg_blocks_per_game,
                    'avg_turnovers_per_game' => $playerStats->avg_turnovers_per_game,
                    'avg_fouls_per_game' => $playerStats->avg_fouls_per_game,

                    // Total stats
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => $playerStats->total_games_played,  // Add total_games_played here
                    'total_points' => $playerStats->total_points,
                    'total_rebounds' => $playerStats->total_rebounds,
                    'total_assists' => $playerStats->total_assists,
                    'total_steals' => $playerStats->total_steals,
                    'total_blocks' => $playerStats->total_blocks,
                    'total_turnovers' => $playerStats->total_turnovers,
                    'total_fouls' => $playerStats->total_fouls,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Player season stats stored successfully.']);
    }
    public function processAllSeasonPlayerStats()
    {
        // Get all season IDs
        $seasonIds = DB::table('seasons')->pluck('id');

        foreach ($seasonIds as $seasonId) {
            // Call your function for each season
            $update = $this->storeallplayerseasonstatsV1($seasonId);
            if ($update) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function storeallplayerseasonstatsV1($latestSeasonId)
    {
        // Validate the incoming request


        // Get the latest season ID or set it to 12 if it doesn’t exist
        // $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id') ?? 1;
        // Get all players from the team
        $players = DB::table('players')
            ->where('team_id', '>', 0)  // Ensure team_id is greater than 0
            ->where('is_active', true)  // Ensure the player is active
            ->get();


        foreach ($players as $player) {
            // Check if the player has stats in player_game_stats for the latest season
            $hasStats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->exists();
            // Query to count the total games played for a team in a given season
            $gamesPlayedCount = $this->totalRegularSeasonGames($latestSeasonId,$player->team_id);


            if ($hasStats) {
                // Calculate stats if stats are found for the player
                $playerStats = DB::table('player_game_stats')
                    ->where('player_id', $player->id)
                    ->where('season_id', $latestSeasonId)
                    ->select(
                        'player_id',
                        'team_id',
                        DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as total_games_played'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) as total_points'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) as total_rebounds'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) as total_assists'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) as total_steals'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) as total_blocks'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) as total_turnovers'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) as total_fouls'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN minutes ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_minutes_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_points_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_rebounds_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_assists_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_steals_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_blocks_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_turnovers_per_game'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_fouls_per_game')
                    )
                    ->groupBy('player_id', 'player_game_stats.team_id')
                    ->first();
            } else {
                // Set all stats to 0 if no stats are found
                $playerStats = (object) [
                    'player_id' => $player->id,
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => 0,
                    'total_points' => 0,
                    'total_rebounds' => 0,
                    'total_assists' => 0,
                    'total_steals' => 0,
                    'total_blocks' => 0,
                    'total_turnovers' => 0,
                    'total_fouls' => 0,
                    'avg_minutes_per_game' => 0,
                    'avg_points_per_game' => 0,
                    'avg_rebounds_per_game' => 0,
                    'avg_assists_per_game' => 0,
                    'avg_steals_per_game' => 0,
                    'avg_blocks_per_game' => 0,
                    'avg_turnovers_per_game' => 0,
                    'avg_fouls_per_game' => 0,
                ];
            }

            // Get the player's role for the specified season
            $playerRating = DB::table('player_ratings')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->first();

            // Insert or update the player's season stats into the player_season_stats table
            DB::table('player_season_stats')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'season_id' => $latestSeasonId,
                ],
                [
                    'team_id' =>  $player->team_id,
                    'role' => $playerRating->role ?? $player->role,  // Role from player_ratings or default
                    'avg_minutes_per_game' => $playerStats->avg_minutes_per_game,
                    'avg_points_per_game' => $playerStats->avg_points_per_game,
                    'avg_rebounds_per_game' => $playerStats->avg_rebounds_per_game,
                    'avg_assists_per_game' => $playerStats->avg_assists_per_game,
                    'avg_steals_per_game' => $playerStats->avg_steals_per_game,
                    'avg_blocks_per_game' => $playerStats->avg_blocks_per_game,
                    'avg_turnovers_per_game' => $playerStats->avg_turnovers_per_game,
                    'avg_fouls_per_game' => $playerStats->avg_fouls_per_game,

                    // Total stats
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => $playerStats->total_games_played,  // Add total_games_played here
                    'total_points' => $playerStats->total_points,
                    'total_rebounds' => $playerStats->total_rebounds,
                    'total_assists' => $playerStats->total_assists,
                    'total_steals' => $playerStats->total_steals,
                    'total_blocks' => $playerStats->total_blocks,
                    'total_turnovers' => $playerStats->total_turnovers,
                    'total_fouls' => $playerStats->total_fouls,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Player season stats stored successfully.']);
    }
    public function storeplayerseasonstats($teamId, $playerId)
    {
        try {
            // Get the latest season ID or default to 1 if none exists
            $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id') ?? 1;

            // Fetch the specified player from the team
            $player = DB::table('players')
                ->where('team_id', $teamId)
                ->where('id', $playerId)
                ->where('is_active', true)
                ->first();

            if (!$player) {
                return response()->json(['error' => 'Player not found or inactive'], 404);
            }

            // Query to count the total games played for a team in a given season
            $gamesPlayedCount = $this->totalRegularSeasonGames($latestSeasonId,$teamId);

            // Check if the player has stats in the latest season
            $hasStats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->exists();

            if ($hasStats) {
                // Calculate the player's aggregated stats for the latest season
                $playerStats = DB::table('player_game_stats')
                    ->where('player_id', $player->id)
                    ->where('season_id', $latestSeasonId)
                    ->select(
                        'player_id',
                        'team_id',
                        DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as total_games_played'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) as total_points'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) as total_rebounds'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) as total_assists'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) as total_steals'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) as total_blocks'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) as total_turnovers'),
                        DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) as total_fouls'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN minutes ELSE NULL END) as avg_minutes_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN points ELSE NULL END) as avg_points_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN rebounds ELSE NULL END) as avg_rebounds_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN assists ELSE NULL END) as avg_assists_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN steals ELSE NULL END) as avg_steals_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN blocks ELSE NULL END) as avg_blocks_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN turnovers ELSE NULL END) as avg_turnovers_per_game'),
                        DB::raw('AVG(CASE WHEN minutes > 0 THEN fouls ELSE NULL END) as avg_fouls_per_game')
                    )
                    ->groupBy('player_id', 'team_id')
                    ->first();
            } else {
                // Set default stats if no game stats exist
                $playerStats = (object) [
                    'player_id' => $player->id,
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => 0,
                    'total_points' => 0,
                    'total_rebounds' => 0,
                    'total_assists' => 0,
                    'total_steals' => 0,
                    'total_blocks' => 0,
                    'total_turnovers' => 0,
                    'total_fouls' => 0,
                    'avg_minutes_per_game' => 0,
                    'avg_points_per_game' => 0,
                    'avg_rebounds_per_game' => 0,
                    'avg_assists_per_game' => 0,
                    'avg_steals_per_game' => 0,
                    'avg_blocks_per_game' => 0,
                    'avg_turnovers_per_game' => 0,
                    'avg_fouls_per_game' => 0,
                ];
            }

            // Fetch the player's role for the specified season
            $playerRating = DB::table('player_ratings')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->first();

            // Insert or update the player's season stats in the player_season_stats table
            DB::table('player_season_stats')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'season_id' => $latestSeasonId,
                ],
                [
                    'team_id' => $player->team_id,
                    'role' => $playerRating->role ?? $player->role, // Role from player_ratings or default
                    'avg_minutes_per_game' => $playerStats->avg_minutes_per_game,
                    'avg_points_per_game' => $playerStats->avg_points_per_game,
                    'avg_rebounds_per_game' => $playerStats->avg_rebounds_per_game,
                    'avg_assists_per_game' => $playerStats->avg_assists_per_game,
                    'avg_steals_per_game' => $playerStats->avg_steals_per_game,
                    'avg_blocks_per_game' => $playerStats->avg_blocks_per_game,
                    'avg_turnovers_per_game' => $playerStats->avg_turnovers_per_game,
                    'avg_fouls_per_game' => $playerStats->avg_fouls_per_game,
                    'total_games' => $gamesPlayedCount,
                    'total_games_played' => $playerStats->total_games_played,
                    'total_points' => $playerStats->total_points,
                    'total_rebounds' => $playerStats->total_rebounds,
                    'total_assists' => $playerStats->total_assists,
                    'total_steals' => $playerStats->total_steals,
                    'total_blocks' => $playerStats->total_blocks,
                    'total_turnovers' => $playerStats->total_turnovers,
                    'total_fouls' => $playerStats->total_fouls,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return response()->json(['message' => 'Player season stats stored successfully.']);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            \Log::error('Error in storeplayerseasonstats: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }


    public function getseasonawards(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
        ]);
        // Fetch awards along with player, team, and season names for the latest season
        $awards = DB::table('season_awards')
            ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
            ->leftJoin('teams', 'season_awards.team_id', '=', 'teams.id')
            ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
            ->where('season_awards.season_id', $request->season_id)
            ->select(
                'season_awards.*',
                'players.name as player_name',
                'teams.name as team_name',
                'seasons.name as season_name' // Select the season name
            )
            ->get();


        return response()->json([
            'message' => 'Season awards stored successfully.',
            'awards' => $awards
        ]);
    }
    public function getawardnamesdropdown()
    {
        // Fetch distinct award names from the season_awards table
        $awardNames = DB::table('season_awards')
            ->select('award_name')
            ->distinct()
            ->get();

        // Pass the award names to the view
        return response()->json([
            'awardNames' => $awardNames
        ]);
    }

    public function filterawardsperseason(Request $request)
    {
        // Assume season_id is passed in the request
        $seasonId = $request->input('season_id');
        $awardsName = $request->input('awards_name');
        // Fetch awards along with player and team names for the updated season
        if ($seasonId > 0) {
            $awards = DB::table('season_awards')
                ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
                ->leftJoin('teams', 'season_awards.team_id', '=', 'teams.id')
                ->leftJoin('teams as drafted_team', 'players.drafted_team_id', '=', 'drafted_team.id') // Fixed alias usage
                ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
                ->where('season_awards.season_id', $seasonId)
                ->select(
                    'season_awards.id',
                    'season_awards.player_id',
                    'players.name as player_name',
                    'players.draft_status as draft_status',
                    'drafted_team.acronym as drafted_team', // Fetch drafted team acronym
                    'teams.name as team_name',
                    'season_awards.award_name',
                    'season_awards.award_description',
                    'season_awards.season_id',
                    'season_awards.team_id',
                    'season_awards.created_at',
                    'seasons.name as season_name' // Select the season name
                )
                ->orderBy('season_awards.id', 'desc')  // Order by id in descending order
                ->get();
        } else {
            $awards = DB::table('season_awards')
                ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
                ->leftJoin('teams', 'season_awards.team_id', '=', 'teams.id')
                ->leftJoin('teams as drafted_team', 'players.drafted_team_id', '=', 'drafted_team.id') // Fixed alias usage
                ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
                ->where('season_awards.award_name', $awardsName)
                ->select(
                    'season_awards.id',
                    'season_awards.player_id',
                    'players.name as player_name',
                    'players.draft_status as draft_status',
                    'drafted_team.acronym as drafted_team', // Fetch drafted team acronym
                    'teams.name as team_name',
                    'season_awards.award_name',
                    'season_awards.award_description',
                    'season_awards.season_id',
                    'season_awards.team_id',
                    'season_awards.created_at',
                    'seasons.name as season_name' // Select the season name
                )
                ->orderBy('season_awards.id', 'desc')  // Order by id in descending order
                ->get();
        }


        return response()->json([
            'message' => 'Team IDs in season awards updated successfully for season ' . $seasonId,
            'awards' => $awards
        ]);
    }

    public function storeseasonawards()
    {
        // Get the latest season ID
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');

        // Clear existing awards for the latest season
        DB::table('season_awards')->where('season_id', $latestSeasonId)->delete();

        // Get player stats from player_season_stats for the latest season
        $playerStats = DB::table('player_season_stats')
            ->where('season_id', $latestSeasonId)
            ->get();

        // Filter eligible players (must have played at least 75% of the total games)
        $eligiblePlayerStats = $playerStats->filter(function ($stats) {
            return (int)$stats->total_games_played >= 0.75 * (int)$stats->total_games;
        });


        // Determine the top performers based on different metrics
        $topScorer = $eligiblePlayerStats->sortByDesc('avg_points_per_game')->first();
        $topRebounder = $eligiblePlayerStats->sortByDesc('avg_rebounds_per_game')->first();
        $topPlaymaker = $eligiblePlayerStats->sortByDesc('avg_assists_per_game')->first();
        $topStealer = $eligiblePlayerStats->sortByDesc('avg_steals_per_game')->first();
        $topBlocker = $eligiblePlayerStats->sortByDesc('avg_blocks_per_game')->first();
        $bestDefender = $eligiblePlayerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->first();

        // Top 5 Offensive Players (Top 5 players based on avg points per game)
        $topOffensivePlayers = $eligiblePlayerStats->sortByDesc('avg_points_per_game')->take(5);

        // Top 5 Defensive Players (Top 5 players based on combined avg steals and blocks per game)
        $topDefensivePlayers = $eligiblePlayerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->take(5);

        // Get previous season's stats for comparison
        $previousSeasonId = DB::table('seasons')->where('id', '<', $latestSeasonId)->orderBy('id', 'desc')->value('id');
        $previousSeasonStats = DB::table('player_season_stats')->where('season_id', $previousSeasonId)->pluck('avg_points_per_game', 'player_id');

        // Exclude rookies from the Most Improved Player award
        $nonRookies = DB::table('players')->where('is_rookie', false)->pluck('id');

        $mostImprovedPlayer = $eligiblePlayerStats->filter(function ($stats) use ($nonRookies) {
            return $nonRookies->contains($stats->player_id);
        })
            ->sortByDesc(function ($stats) use ($previousSeasonStats) {
                $previousPoints = $previousSeasonStats[$stats->player_id] ?? 0;
                return ($stats->avg_points_per_game - $previousPoints);
            })
            ->first();

        // Calculate MVP by sorting the players based on the weighted stats and returning the top player
        $mvp = $eligiblePlayerStats->sort(function ($a, $b) {
            $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;

            $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;

            return $bStats <=> $aStats;
        })->first();

        // Filter out rookies and determine the Rookie of the Year award
        $rookies = $eligiblePlayerStats->filter(function ($stats) {
            return DB::table('players')
                ->where('id', $stats->player_id)
                ->where('draft_id', $stats->season_id)
                ->exists();
        });

        // Filter rookies who have played at least 75% of games for Rookie of the Year
        $rookieOfTheYear = $rookies->filter(function ($stats) {
            return $stats->total_games_played >= 0.75 * $stats->total_games;
        })->sort(function ($a, $b) {
            $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;

            $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;

            return $bStats <=> $aStats;
        })->first();

        // Determine the 6th Man of the Year award
        $rolePlayers = $eligiblePlayerStats->filter(function ($stats) {
            return $stats->role !== 'star player' && $stats->role !== 'starter';
        });

        $sixthManOfTheYear = $rolePlayers->sort(function ($a, $b) {
            $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;
            $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;
            return $bStats <=> $aStats;
        })->first();

        // Insert awards into season_awards table if not already present
        $this->insertAward($topScorer, 'Top Scorer', 'Player with the highest average points per game', $latestSeasonId);
        $this->insertAward($topRebounder, 'Top Rebounder', 'Player with the highest average rebounds per game', $latestSeasonId);
        $this->insertAward($topPlaymaker, 'Top Playmaker', 'Player with the highest average assists per game', $latestSeasonId);
        $this->insertAward($topStealer, 'Top Stealer', 'Player with the highest average steals per game', $latestSeasonId);
        $this->insertAward($topBlocker, 'Top Blocker', 'Player with the highest average blocks per game', $latestSeasonId);
        $this->insertAward($bestDefender, 'Best Defensive Player', 'Player with the highest combined average steals and blocks per game', $latestSeasonId);
        $this->insertAward($mvp, 'Best Overall Player', 'Player with the best overall performance score', $latestSeasonId);
        $this->insertAward($mostImprovedPlayer, 'Most Improved Player', 'Player with the highest increase in average points per game from the previous season', $latestSeasonId);

        // Insert the Rookie of the Season award
        if ($rookieOfTheYear) {
            $this->insertAward($rookieOfTheYear, 'Rookie of the Season', 'Best rookie player of the season', $latestSeasonId);
        }

        // Insert the Sixth Man award
        if ($sixthManOfTheYear) {
            $this->insertAward($sixthManOfTheYear, 'Sixth Man of the Year', 'Best player coming off the bench', $latestSeasonId);
        }

        // Insert Top 5 Offensive Players awards
        $counter = 1;
        foreach ($topOffensivePlayers as $player) {
            $this->insertAward($player, 'Top ' . $counter . ' Offensive Player', 'Player ranked ' . $counter . ' in average points per game', $latestSeasonId);
            $counter++;
        }

        // Insert Top 5 Defensive Players awards
        $counter = 1;
        foreach ($topDefensivePlayers as $player) {
            $this->insertAward($player, 'Top ' . $counter . ' Defensive Player', 'Player ranked ' . $counter . ' in combined average steals and blocks per game', $latestSeasonId);
            $counter++;
        }

        // Update season status
        DB::table('seasons')->where('id', $latestSeasonId)->update(['status' => config('timeline.awards')]);

        // Fetch awards along with player, team names, and team_id for the latest season
        $awards = DB::table('season_awards')
            ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->where('season_awards.season_id', $latestSeasonId)
            ->select(
                'season_awards.*',
                'players.name as player_name',
                'teams.name as team_name',
                'teams.id as team_id'
            )
            ->get();

        return response()->json([
            'message' => 'Season awards stored successfully.',
            'awards' => $awards
        ]);
    }

    public function storeseasonawardsauto(Request $request)
    {
        try {
            // Get the latest season ID
            $latestSeasonId = $request->season_id;

            // Clear existing awards for the latest season
            DB::table('season_awards')->where('season_id', $latestSeasonId)->delete();

            // Get player stats from player_season_stats for the latest season
            $playerStats = DB::table('player_season_stats')
                ->where('season_id', $latestSeasonId)
                ->get();

            // Get total number of games played in the season
            $eligiblePlayerStats = $playerStats->filter(function ($stats) use ($latestSeasonId) {
                // Check if the player has played at least 75% of the total games
                $totalGamesInSeason = $stats->total_games;
                return $stats->total_games_played >= 0.75 * $totalGamesInSeason;
            });

            // Determine the top performers based on different metrics
            $topScorer = $eligiblePlayerStats->sortByDesc('avg_points_per_game')->first();
            $topRebounder = $eligiblePlayerStats->sortByDesc('avg_rebounds_per_game')->first();
            $topPlaymaker = $eligiblePlayerStats->sortByDesc('avg_assists_per_game')->first();
            $topStealer = $eligiblePlayerStats->sortByDesc('avg_steals_per_game')->first();
            $topBlocker = $eligiblePlayerStats->sortByDesc('avg_blocks_per_game')->first();
            $bestDefender = $eligiblePlayerStats->sortByDesc(function ($stats) {
                return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
            })->first();

            // Top 5 Offensive Players
            $topOffensivePlayers = $eligiblePlayerStats->sortByDesc('avg_points_per_game')->take(5);

            // Top 5 Defensive Players
            $topDefensivePlayers = $eligiblePlayerStats->sortByDesc(function ($stats) {
                return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
            })->take(5);

            // Get previous season's stats for comparison
            $previousSeasonId = DB::table('seasons')->where('id', '<', $latestSeasonId)->orderBy('id', 'desc')->value('id');
            $previousSeasonStats = DB::table('player_season_stats')->where('season_id', $previousSeasonId)->pluck('avg_points_per_game', 'player_id');

            // Exclude rookies from the Most Improved Player award
            $nonRookies = DB::table('players')->where('is_rookie', false)->pluck('id');

            $mostImprovedPlayer = $eligiblePlayerStats->filter(function ($stats) use ($nonRookies) {
                return $nonRookies->contains($stats->player_id);
            })
                ->sortByDesc(function ($stats) use ($previousSeasonStats) {
                    $previousPoints = $previousSeasonStats[$stats->player_id] ?? 0;
                    return ($stats->avg_points_per_game - $previousPoints);
                })
                ->first();

            // Calculate MVP by sorting the players based on the weighted stats and returning the top player
            $mvp = $eligiblePlayerStats->filter(function ($stats) {
                return $stats->total_games_played >= 0.75 * $stats->total_games; // Ensure MVP has played 75% of games
            })->sort(function ($a, $b) {
                $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                    $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                    $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;

                $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                    $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                    $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;

                return $bStats <=> $aStats;
            })->first();

            // Filter out rookies and determine the Rookie of the Year award
            $rookies = $eligiblePlayerStats->filter(function ($stats) {
                // Check if the player is a rookie by comparing the draft_id and season_id
                return DB::table('players')
                    ->where('id', $stats->player_id)          // Match the player_id
                    ->where('draft_id', $stats->season_id)    // Check if draft_id matches season_id
                    ->exists();  // Return true if a record is found (i.e., player is a rookie)
            });

            // Filter rookies who have played at least 75% of games for Rookie of the Year
            $rookieOfTheYear = $rookies->filter(function ($stats) {
                return $stats->total_games_played >= 0.75 * $stats->total_games;
            })->sort(function ($a, $b) {
                $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                    $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                    $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;

                $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                    $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                    $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;

                return $bStats <=> $aStats;
            })->first();

            // Determine the 6th Man of the Year award
            $rolePlayers = $eligiblePlayerStats->filter(function ($stats) {
                return $stats->role !== 'star player' && $stats->role !== 'starter';
            });

            $sixthManOfTheYear = $rolePlayers->sort(function ($a, $b) {
                $aStats = $a->avg_points_per_game * 1.0 + $a->avg_rebounds_per_game * 1.2 +
                    $a->avg_assists_per_game * 1.5 + $a->avg_steals_per_game * 2.0 +
                    $a->avg_blocks_per_game * 2.0 - $a->avg_turnovers_per_game * 1.5;
                $bStats = $b->avg_points_per_game * 1.0 + $b->avg_rebounds_per_game * 1.2 +
                    $b->avg_assists_per_game * 1.5 + $b->avg_steals_per_game * 2.0 +
                    $b->avg_blocks_per_game * 2.0 - $b->avg_turnovers_per_game * 1.5;
                return $bStats <=> $aStats;
            })->first();

            // Insert awards into season_awards table if not already present
            $this->insertAward($topScorer, 'Top Scorer', 'Player with the highest average points per game', $latestSeasonId);
            $this->insertAward($topRebounder, 'Top Rebounder', 'Player with the highest average rebounds per game', $latestSeasonId);
            $this->insertAward($topPlaymaker, 'Top Playmaker', 'Player with the highest average assists per game', $latestSeasonId);
            $this->insertAward($topStealer, 'Top Stealer', 'Player with the highest average steals per game', $latestSeasonId);
            $this->insertAward($topBlocker, 'Top Blocker', 'Player with the highest average blocks per game', $latestSeasonId);
            $this->insertAward($bestDefender, 'Best Defensive Player', 'Player with the highest combined average steals and blocks per game', $latestSeasonId);
            $this->insertAward($mvp, 'Best Overall Player', 'Player with the best overall performance score', $latestSeasonId);
            $this->insertAward($mostImprovedPlayer, 'Most Improved Player', 'Player with the highest increase in average points per game from the previous season', $latestSeasonId);

            // Insert the Rookie of the Season award
            if ($rookieOfTheYear) {
                $this->insertAward($rookieOfTheYear, 'Rookie of the Season', 'Best rookie player of the season', $latestSeasonId);
            }

            // Insert the 6th Man of the Year award
            if ($sixthManOfTheYear) {
                $this->insertAward($sixthManOfTheYear, '6th Man of the Year', 'Best player coming off the bench', $latestSeasonId);
            }

            // Insert Top 5 Offensive Players awards
            $counter = 1;
            foreach ($topOffensivePlayers as $player) {
                if ($counter > 5) break;
                $this->insertAward($player, 'Top ' . $counter . ' Offensive Player', 'Player ranked ' . $counter . ' in average points per game', $latestSeasonId);
                $counter++;
            }

            // Insert Top 5 Defensive Players awards
            $counter = 1;
            foreach ($topDefensivePlayers as $player) {
                if ($counter > 5) break;
                $this->insertAward($player, 'Top ' . $counter . ' Defensive Player', 'Player ranked ' . $counter . ' in combined average steals and blocks per game', $latestSeasonId);
                $counter++;
            }

            // Update season status
            DB::table('seasons')->where('id', $latestSeasonId)->update(['status' => 12]);

            // Fetch awards along with player, team names, and team_id for the latest season
            $awards = DB::table('season_awards')
                ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
                ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
                ->where('season_awards.season_id', $latestSeasonId)
                ->select(
                    'season_awards.*',
                    'players.name as player_name',
                    'teams.name as team_name',
                    'teams.id as team_id' // Include team_id in the select clause
                )
                ->get();

            return response()->json([
                'message' => 'Season awards stored successfully.',
                'awards' => $awards,
                'season_id' =>  $latestSeasonId,
            ]);
        } catch (\Exception $e) {
            // Log the error message if an exception occurs anywhere in the method
            \Log::error('Error in storing season awards', [
                'season_id' => $request->season_id,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Return an error response
            return response()->json([
                'message' => 'An error occurred while storing the season awards.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    private function insertaward($playerStats, $awardName, $awardDescription, $seasonId)
    {
        if ($playerStats) {
            DB::table('season_awards')->updateOrInsert(
                [
                    'player_id' => $playerStats->player_id,
                    'team_id' => $playerStats->team_id,
                    'season_id' => $seasonId,
                    'award_name' => $awardName,
                ],
                [
                    'award_description' => $awardDescription,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
    public function getFinalsMVPList()
    {
        // Fetch data from the view table directly
        $mvpList = DB::table('finals_mvp_with_stats')  // Assuming this is the name of the view
                    ->select(
                        'player_id',
                        'player_name',
                        'player_role',
                        'current_team_names',
                        'mvp_winning_team_names',
                        'awards_won',
                        'is_active',
                        'total_games',
                        'total_games_played',
                        'avg_minutes_per_game',
                        'avg_points_per_game',
                        'avg_rebounds_per_game',
                        'avg_assists_per_game',
                        'avg_steals_per_game',
                        'avg_blocks_per_game',
                        'avg_turnovers_per_game',
                        'avg_fouls_per_game',
                        'total_points',
                        'total_rebounds',
                        'total_assists',
                        'total_steals',
                        'total_blocks',
                        'total_turnovers',
                        'total_fouls',
                        'stats_created_at',
                        'stats_updated_at'
                    )
                    ->where('player_id','!=',null)
                    ->orderByDesc('stats_created_at')  // Ensure it's ordered by most recent stats
                    ->get();
    
        // Return the data as a JSON response
        return response()->json($mvpList);
    }
    
    private function totalRegularSeasonGames($seasonId, $teamId)
    {
        $gamesPlayedCount = DB::table('schedules')
            ->where('season_id', $seasonId)
            ->where(function ($query) use ($teamId) {
                $query->where('home_id', $teamId)
                    ->orWhere('away_id', $teamId);
            })
            ->count();

        return $gamesPlayedCount;
    }

}
