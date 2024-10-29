<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use Exception;
use Inertia\Inertia;
use App\Models\Seasons;
use App\Models\Teams;
use App\Models\Schedules;
use App\Models\Conference;
use App\Models\Player;
use App\Models\PlayerGameStats;
use Illuminate\Support\Facades\DB;

class RatingsController extends Controller
{
    //
    public function updateActivePlayers(Request $request)
    {
        DB::beginTransaction(); // Start transaction

        try {
            // Validate the request data
            $request->validate([
                'team_id' => 'required|integer|min:0',
                'is_last' => 'required|boolean',
            ]);

            $teamId = $request->team_id;
            $isLast = $request->is_last;

            \Log::info('Received request:', ['team_id' => $teamId, 'is_last' => $isLast]);

            // Fetch all active players, filtered by team_id if provided
            $query = Player::where('is_active', 1);
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
            $players = $query->get();

            $seasonId = $this->getLatestSeasonId();

            // Fetch the team name if team_id is provided
            $teamName = '';
            if ($teamId) {
                $team = Teams::find($teamId);
                if ($team) {
                    $teamName = $team->name;
                }
            }

            $improvedPlayers = [];
            $declinedPlayers = [];
            $reSignedPlayers = []; // Track re-signed players

            // Fetch player statistics for the current season
            $stats = DB::table('player_season_stats')
                ->where('season_id', $seasonId)
                ->where('team_id', $teamId)
                ->get()
                ->sortByDesc(function ($stat) {
                    // Define a composite score based on your performance metrics
                    return $stat->avg_points_per_game * 0.4 +
                        $stat->avg_rebounds_per_game * 0.3 +
                        $stat->avg_assists_per_game * 0.2 +
                        $stat->avg_steals_per_game * 0.1;
                });

            // Rank players and assign roles
            $rankedPlayers = $stats->values();
            // Update player roles based on the new distribution: 3 star players, 2 starters, 4 role players, 3 bench players
            foreach ($rankedPlayers->take(3) as $playerStat) {
                // Top 3 players become star players
                Player::where('id', $playerStat->player_id)->update(['role' => 'star player']);
            }

            foreach ($rankedPlayers->slice(3, 2) as $playerStat) {
                // Next 2 players become starters
                Player::where('id', $playerStat->player_id)->update(['role' => 'starter']);
            }

            foreach ($rankedPlayers->slice(5, 4) as $playerStat) {
                // Next 4 players become role players
                Player::where('id', $playerStat->player_id)->update(['role' => 'role player']);
            }

            foreach ($rankedPlayers->slice(9, 3) as $playerStat) {
                // // Last 3 players become bench players
                // Update the player's role to "bench" and set contract years and team ID
                DB::table('players')
                ->where('id', $playerStat->player_id)
                ->update([
                    'role' => 'bench', // Assign the role
                    'contract_years' => 0,
                    'team_id' => 0,
                ]);

                 // Log the transaction for the waived player
                 DB::table('transactions')->insert([
                    'player_id' => $playerStat->player_id,
                    'season_id' => $seasonId,
                    'details' => 'Waived by ' . ($teamName ?? 'Unknown Team'),
                    'from_team_id' => $teamId,
                    'to_team_id' => 0,
                    'status' => 'waived',
                ]);

                \Log::info('Player waived', [
                    'player_id' => $playerStat->player_id,
                    'team_name' => $teamName ?? 'Unknown Team',
                    'season_id' => $seasonId,
                ]);

            }

            // Fetch updated players
            $players = $query->get();

            foreach ($players as $player) {
                // Check if the player's ratings have already been updated for the current season
                $ratingExists = DB::table('player_ratings')
                    ->where('player_id', $player->id)
                    ->where('season_id', $seasonId)
                    ->exists();

                if ($ratingExists) {
                    // Skip updating this player if already updated
                    continue;
                }

                // Store old ratings and role for comparison
                $oldRatings = [
                    'shooting' => $player->shooting_rating,
                    'defense' => $player->defense_rating,
                    'passing' => $player->passing_rating,
                    'rebounding' => $player->rebounding_rating,
                    'overall' => $player->overall_rating,
                ];
                $oldRole = $player->role;

                // Deduct contract_years by 1 and increment age by 1
                $player->contract_years -= 1;
                $player->age += 1;
                $player->is_rookie = 0; // All players are no longer rookies

                // Check for retirement
                if($player->age >= $player->retirement_age){

                    $player->is_active = 0;
                    $player->contract_years = 0;
                    $player->team_id = 0;

                    DB::table('transactions')->insert([
                        'player_id' => $player->id,
                        'season_id' => $seasonId,
                        'details' => 'has retired from the league.',
                        'from_team_id' => $player->team_id,
                        'to_team_id' => 0,
                        'status' => 'retired',
                    ]);
                }



                // Check if contract_years is 0
                if ($player->contract_years == 0 && $player->team_id ==  $teamId) {
                    // Determine if the player re-signs
                    $reSignChance = match ($player->role) {
                        'star player' => 70,
                        'starter' => 50,
                        'role player' => 30,
                        'bench' => 10,
                        default => 50,
                    };

                    if (mt_rand(1, 100) > $reSignChance) {
                        // Player re-signs, assign contract length based on role
                        $player->contract_years += $this->getContractYearsBasedOnRole($player->role);
                        $reSignedPlayers[] = $player; // Track re-signed player

                        DB::table('transactions')->insert([
                            'player_id' => $player->id,
                            'season_id' => $seasonId,
                            'details' => 'Re-signed with ' . $teamName,
                            'from_team_id' => $player->team_id,
                            'to_team_id' => $player->team_id,
                            'status' => 'resigned',
                        ]);
                    } else {

                        $player->contract_years = 0;
                        $player->team_id = 0;

                        DB::table('transactions')->insert([
                            'player_id' => $player->id,
                            'season_id' => $seasonId,
                            'details' => 'Released by ' . $teamName,
                            'from_team_id' => 0,
                            'to_team_id' => 0,
                            'status' => 'released',
                        ]);
                    }
                }

                // Determine if the player should have an injury_prone_percentage of 0
                if (rand(1, 100) <= 40) {
                    // 40% chance to be injury-prone
                    // Assign a random value between 10 and 100 in increments of 10
                    $player->injury_prone_percentage = rand(0, 100);
                } else {
                    $player->injury_prone_percentage = 0;
                }


                $performanceData = $this->comparePerformanceBetweenSeasons($player->id);

                $latestPerformance = $performanceData['latest_performance'];
                $previousPerformance = $performanceData['previous_performance'];
                $performanceChange = $performanceData['performance_change'];

                // Apply performance change to further adjust the ratings
                foreach (['shooting', 'defense', 'passing', 'rebounding'] as $category) {
                    if ($performanceChange[$category] > 0) {
                        // Increase the rating if performance has improved
                        $player->{$category . '_rating'} = min($player->{$category . '_rating'} + 2, 99);
                    } elseif ($performanceChange[$category] < 0) {
                        // Decrease the rating if performance has declined
                        $player->{$category . '_rating'} = max($player->{$category . '_rating'} - 2, 40);
                    }
                }

                // Recalculate the player's overall rating
                $player->overall_rating = ($player->shooting_rating + $player->defense_rating + $player->passing_rating + $player->rebounding_rating) / 4;

                // Check for improvements or declines
                if ($player->overall_rating > $oldRatings['overall'] || $this->rolePriority[$player->role] < $this->rolePriority[$oldRole]) {
                    // Player improved if the overall rating increased or the role was promoted (higher priority)
                    $improvedPlayers[] = $player;
                } elseif ($player->overall_rating < $oldRatings['overall'] || $this->rolePriority[$player->role] > $this->rolePriority[$oldRole]) {
                    // Player declined if the overall rating decreased or the role was demoted (lower priority)
                    $declinedPlayers[] = $player;
                }

                DB::table('players')->where('id', $player->id)->update([
                    'contract_years' => $player->contract_years,
                    'team_id' => $player->team_id,
                    'is_active' => $player->is_active,
                    'age' => $player->age,
                    'role' => $player->role,
                    'shooting_rating' => $player->shooting_rating,
                    'defense_rating' => $player->defense_rating,
                    'passing_rating' => $player->passing_rating,
                    'rebounding_rating' => $player->rebounding_rating,
                    'overall_rating' => $player->overall_rating,
                    'injury_prone_percentage' => $player->injury_prone_percentage,
                ]);

                // Log the updated ratings
                $this->logPlayerRatings($player, $seasonId);

            }


            // Show alert if this is the last update
            if ($isLast) {
                \Log::info('Processing last update.');

                // Assign non-re-signed players to teams with fewer than 15 players
                $freeAgents = Player::where('team_id', 0)->where('is_active', 1)->get();
                $teamsWithFewMembers = DB::table('teams')
                    ->leftJoin('players', 'teams.id', '=', 'players.team_id')
                    ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
                    ->groupBy('teams.id', 'teams.name')
                    ->havingRaw('COUNT(players.id) < 15')
                    ->get();

                foreach ($freeAgents as $agent) {
                    if ($teamsWithFewMembers->isEmpty()) {
                        break;
                    }

                    // Randomly select a team from the incomplete teams
                    $team = $teamsWithFewMembers->random();
                    $playersNeeded = 15 - $team->player_count;

                    // Update the agent's team and contract years
                    $agent->team_id = $team->id;
                    $agent->contract_years = $this->getContractYearsBasedOnRole($agent->role);
                    $agent->save();

                    // Reduce the number of players needed for that team
                    $team->player_count++;

                    // Remove the team from the list if it no longer needs more players
                    if ($playersNeeded <= 1) {
                        $teamsWithFewMembers = $teamsWithFewMembers->filter(function ($t) use ($team) {
                            return $t->id !== $team->id;
                        });
                    }
                }
                // Update season status
                $season = Seasons::find($seasonId);
                if ($season) {
                    $season->status = 10;
                    $season->save();
                } else {
                    \Log::warning('Season not found for ID:', ['seasonId' => $seasonId]);
                }

                DB::commit(); // Commit transaction

                return response()->json([
                    'error' => false,
                    'message' => 'All player statuses have been updated successfully. Update finished.',
                    'team_name' => $teamName,
                    'improved_players' => $improvedPlayers,
                    'declined_players' => $declinedPlayers,
                    're_signed_players' => $reSignedPlayers, // Include re-signed players in response
                ]);
            }

            DB::commit(); // Commit transaction

            return response()->json([
                'error' => false,
                'message' => 'All player statuses have been updated successfully.',
                'team_name' => $teamName,
                'improved_players' => $improvedPlayers,
                'declined_players' => $declinedPlayers,
                're_signed_players' => $reSignedPlayers, // Include re-signed players in response
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            \Log::error('Failed to update player statuses', ['exception' => $e]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to update player statuses.',
                'error_message' => $e->getMessage(), // Display the exception message
            ], 500);
        }
    }
    private function getContractYearsBasedOnRole($role)
    {
        switch ($role) {
            case 'star player':
                return mt_rand(1, 7);
            case 'starter':
                return mt_rand(1, 5);
            case 'role player':
                return mt_rand(1, 4);
            case 'bench':
            default:
                return mt_rand(1, 3);
        }
    }


    // Define role priority (lower number = higher priority)
    protected $rolePriority = [
        'star player' => 1,
        'starter' => 2,
        'role player' => 3,
        'bench' => 4,
    ];
    protected $roleThresholds = [
        'star player' => 85,  // Star players should maintain at least 85 overall rating
        'starter' => 75,      // Starters should maintain at least 75 overall rating
        'role player' => 60,  // Role players should maintain at least 60 overall rating
        'bench' => 40,        // Bench players should maintain at least 40 overall rating
    ];

    // Function to adjust the player's ratings if they are underperforming for their role
    private function adjustRatingsForRole($player)
    {
        // Define performance thresholds for each role
        $roleThresholds = $this->roleThresholds;

        // Get the performance threshold for the player's current role
        $currentRole = $player->role;
        $expectedPerformance = $roleThresholds[$currentRole] ?? 60; // Default to 60 if the role is not found

        // Adjust ratings if the player's overall rating is below the expected threshold for their role
        if ($player->overall_rating < $expectedPerformance) {
            // The player is underperforming, so reduce their ratings slightly
            $player->shooting_rating = max(40, $player->shooting_rating - mt_rand(1, 3));
            $player->defense_rating = max(40, $player->defense_rating - mt_rand(1, 3));
            $player->passing_rating = max(40, $player->passing_rating - mt_rand(1, 3));
            $player->rebounding_rating = max(40, $player->rebounding_rating - mt_rand(1, 3));

            // Recalculate overall rating based on adjusted individual ratings
            $player->overall_rating = ($player->shooting_rating + $player->defense_rating + $player->passing_rating + $player->rebounding_rating) / 4;
        }
    }
    private function logPlayerRatings($player, $seasonId)
    {
        // Check if the player ratings for the current season already exist
        $existingRecord = DB::table('player_ratings')
            ->where('player_id', $player->id)
            ->where('season_id', $seasonId)
            ->exists();

        // Only update or insert ratings if no record exists for the current season
        if (!$existingRecord) {
            DB::table('player_ratings')->updateOrInsert(
                [
                    'player_id' => $player->id,
                    'season_id' => $seasonId,
                ],
                [
                    'role' => $player->role,
                    'team_id' => $player->team_id,
                    'shooting_rating' => $player->shooting_rating,
                    'defense_rating' => $player->defense_rating,
                    'passing_rating' => $player->passing_rating,
                    'rebounding_rating' => $player->rebounding_rating,
                    'overall_rating' => $player->overall_rating,
                    'updated_at' => now(),
                ]
            );
        } else {
            \Log::info('Player ratings already recorded for season', [
                'player_id' => $player->id,
                'season_id' => $seasonId
            ]);
        }
    }

    private function comparePerformanceBetweenSeasons($playerId)
    {
        // Get the latest season ID and the previous season ID
        $latestSeasonId = $this->getLatestSeasonId();
        $previousSeasonId = $this->getPreviousSeasonId($latestSeasonId);

        // Fetch player's role for the upcoming season from the players table
        $upcomingSeasonRole = Player::where('id', $playerId)
            ->value('role'); // Assume 'role' column exists in players table

        // Fetch player's stats for the latest season from player_season_stats table
        $latestSeasonStats = DB::table('player_season_stats')
            ->where('player_id', $playerId)
            ->where('season_id', $latestSeasonId)
            ->first(); // Get latest season stats

        // Fetch player's stats for the previous season from player_season_stats table
        $previousSeasonStats = DB::table('player_season_stats')
            ->where('player_id', $playerId)
            ->where('season_id', $previousSeasonId)
            ->first(); // Get previous season stats
        // Fetch player's role for the latest season from player_season_stats
        $latestSeasonRole = $latestSeasonStats->role ?? 'role player'; // Default to 'role player' if no stats found

        // Define role-based adjustments
        $roleAdjustments = [
            'star player' => ['shooting' => 1.2, 'defense' => 1.2, 'passing' => 1.2, 'rebounding' => 1.2],
            'starter' => ['shooting' => 1.1, 'defense' => 1.1, 'passing' => 1.1, 'rebounding' => 1.1],
            'role player' => ['shooting' => 1.0, 'defense' => 1.0, 'passing' => 1.0, 'rebounding' => 1.0],
            'bench' => ['shooting' => 0.9, 'defense' => 0.9, 'passing' => 0.9, 'rebounding' => 0.9],
        ];

        // Get role adjustment factors for both roles
        $latestAdjustmentFactors = $roleAdjustments[$latestSeasonRole] ?? $roleAdjustments['role player'];
        $upcomingAdjustmentFactors = $roleAdjustments[$upcomingSeasonRole] ?? $roleAdjustments['role player'];

        // Initialize performance metrics for both seasons
        $latestPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];
        $previousPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];

        // Aggregate stats for the latest season
        if ($latestSeasonStats) {
            $latestPerformance['shooting'] = $latestSeasonStats->avg_points_per_game;
            $latestPerformance['defense'] = $latestSeasonStats->avg_blocks_per_game + $latestSeasonStats->avg_steals_per_game;
            $latestPerformance['passing'] = $latestSeasonStats->avg_assists_per_game;
            $latestPerformance['rebounding'] = $latestSeasonStats->avg_rebounds_per_game;

            // Apply role-based adjustments
            foreach ($latestPerformance as $key => $value) {
                $latestPerformance[$key] *= $latestAdjustmentFactors[$key] ?? 1;
            }
        }

        // Aggregate stats for the previous season
        if ($previousSeasonStats) {
            $previousPerformance['shooting'] = $previousSeasonStats->avg_points_per_game;
            $previousPerformance['defense'] = $previousSeasonStats->avg_blocks_per_game + $previousSeasonStats->avg_steals_per_game;
            $previousPerformance['passing'] = $previousSeasonStats->avg_assists_per_game;
            $previousPerformance['rebounding'] = $previousSeasonStats->avg_rebounds_per_game;

            // Apply role-based adjustments
            foreach ($previousPerformance as $key => $value) {
                $previousPerformance[$key] *= $latestAdjustmentFactors[$key] ?? 1;
            }
        }

        // Compare performance between the two seasons
        $performanceChange = [];
        foreach ($latestPerformance as $key => $latestValue) {
            $previousValue = $previousPerformance[$key] ?? 0; // Default to 0 if no previous stats
            $performanceChange[$key] = $latestValue - $previousValue; // Calculate performance difference
        }

        // Apply drastic adjustments if the role for the upcoming season is different from the latest season's role
        if ($upcomingSeasonRole !== $latestSeasonRole) {
            foreach ($performanceChange as $key => $value) {
                $performanceChange[$key] *= 2; // Apply a drastic adjustment factor (e.g., 2x)
            }
        }

        return [
            'latest_performance' => $latestPerformance,
            'previous_performance' => $previousPerformance,
            'performance_change' => $performanceChange, // How much the player improved or declined
        ];
    }


    // Function to retrieve the previous season ID based on the latest season ID
    private function getPreviousSeasonId($latestSeasonId)
    {
        // Fetch the latest season from the database
        $latestSeason = Seasons::find($latestSeasonId);

        // Assuming that seasons have a 'year' or an incremental 'id'
        // We will fetch the previous season by looking for the one that is closest to but less than the latest season
        $previousSeason = Seasons::where('id', '<', $latestSeasonId)
            ->orderBy('id', 'desc') // Get the season immediately before the latest one
            ->first();

        // If a previous season exists, return its ID, otherwise return null
        return $previousSeason ? $previousSeason->id : null;
    }

    // Example method to get the current season ID
    private function getLatestSeasonId()
    {
        // Fetch the latest season ID based on descending order of IDs
        $latestSeasonId = Seasons::orderBy('id', 'desc')->pluck('id')->first();

        if ($latestSeasonId) {
            return $latestSeasonId;
        }

        // Handle the case where no seasons are found
        throw new \Exception('No seasons found.');
    }
}
