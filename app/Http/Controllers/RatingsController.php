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
    public function updateactiveplayers(Request $request)
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

            // Get the last (highest) team ID in the teams table
            $lastTeamId = DB::table('teams')->max('id');

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
                    // Weigh per-game stats (efficiency and performance per minute)
                    $perGameScore = $stat->avg_points_per_game * 0.3 +
                        $stat->avg_rebounds_per_game * 0.2 +
                        $stat->avg_assists_per_game * 0.2 +
                        $stat->avg_steals_per_game * 0.1 +
                        $stat->avg_blocks_per_game * 0.1 -
                        $stat->avg_turnovers_per_game * 0.1 -
                        $stat->avg_fouls_per_game * 0.1;

                    // Weigh total stats (overall contribution across the season)
                    $totalScore = $stat->total_points * 0.2 +
                        $stat->total_rebounds * 0.2 +
                        $stat->total_assists * 0.2 +
                        $stat->total_steals * 0.15 +
                        $stat->total_blocks * 0.15 -
                        $stat->total_turnovers * 0.1 -
                        $stat->total_fouls * 0.1;


                    $efficiencyFactor = 1 + ($stat->avg_minutes_per_game / 30);  // Assuming 30 minutes is the average threshold

                    // Adjust for role: Apply a modifier based on player role
                    $roleModifier = 1;
                    if ($stat->role === 'star player') {
                        $roleModifier = 1.2;  // Star players get a boost
                    } else if ($stat->role === 'starter') {
                        $roleModifier = 1.1;  // Starters get a smaller boost
                    } else if ($stat->role === 'role player') {
                        $roleModifier = 1.05;  // Role players get a small bonus
                    } else if ($stat->role === 'bench') {
                        $roleModifier = 0.9;  // Bench players are slightly penalized in ranking
                    }

                    // Normalize score based on games played (to account for incomplete seasons)
                    $gamesPlayedModifier = max(1, log($stat->total_games_played + 1) * 0.1);  // log to adjust scale

                    // Return a combined score
                    return ($perGameScore + $totalScore) * $gamesPlayedModifier * $roleModifier * $efficiencyFactor;
                });

            // Rank players and assign roles
            $rankedPlayers = $stats->values();
            // Assign the top 3 players as "star players"
            foreach ($rankedPlayers->take(3) as $playerStat) {
                Player::where('id', $playerStat->player_id)->update(['role' => 'star player']);
            }

            // Assign the next 2 players as "starters"
            foreach ($rankedPlayers->slice(3, 2) as $playerStat) {
                Player::where('id', $playerStat->player_id)->update(['role' => 'starter']);
            }

            // Assign the next 5 players as "role players"
            foreach ($rankedPlayers->slice(5, 5) as $playerStat) {
                Player::where('id', $playerStat->player_id)->update(['role' => 'role player']);
            }

            // Assign the next 2 players as "bench players"
            foreach ($rankedPlayers->slice(10, 2) as $playerStat) {
                Player::where('id', $playerStat->player_id)->update(['role' => 'bench']);
            }

            // Waive the last 3 players (remove them from the team)
            foreach ($rankedPlayers->slice(12, 3) as $playerStat) {
                Player::where('id', $playerStat->player_id)->update(['role' => 'bench']);
                // Optionally log the waived player transaction if you want to track this

                // DB::table('transactions')->insert([
                //     'player_id' => $playerStat->player_id,
                //     'season_id' => $seasonId,
                //     'details' => 'Waived by ' . ($teamName ?? 'Unknown Team'),
                //     'from_team_id' => $teamId,
                //     'to_team_id' => 0,
                //     'status' => 'waived',
                // ]);

                // DB::table('players')->where('id', $playerStat->player_id)->update([
                //     'contract_years' => 0,
                //     'team_id' => 0,
                // ]);

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
                if ($player->age >= $player->retirement_age) {

                    DB::table('transactions')->insert([
                        'player_id' => $player->id,
                        'season_id' => $seasonId,
                        'details' => 'has retired from the league.[Last team: ' . $teamName . ']',
                        'from_team_id' => $player->team_id,
                        'to_team_id' => 0,
                        'status' => 'retired',
                    ]);

                    $player->is_active = 0;
                    $player->contract_years = 0;
                    $player->team_id = 0;
                }



                // Check if contract_years is 0
                if ($player->contract_years == 0) {
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
                            'details' => 'Re-signed with ' . $teamName.' For contract extension of '. $player->contract_years .' years',
                            'from_team_id' => $player->team_id,
                            'to_team_id' => $player->team_id,
                            'status' => 'resigned',
                        ]);
                    } else {

                        DB::table('transactions')->insert([
                            'player_id' => $player->id,
                            'season_id' => $seasonId,
                            'details' => 'Released by ' . $teamName,
                            'from_team_id' => $player->team_id,
                            'to_team_id' => 0,
                            'status' => 'released',
                        ]);

                        $player->contract_years = 0;
                        $player->team_id = 0;
                    }
                }

                // Determine if the player should have an injury_prone_percentage of 0
                // if (rand(1, 100) <= 30) {
                //     // 40% chance to be injury-prone
                //     // Assign a random value between 10 and 100 in increments of 10
                //     $player->injury_prone_percentage = rand(50, 100);
                // } else {
                //     $player->injury_prone_percentage = 0;
                // }
                // Check if the player was injured during the season
                $injury = DB::table('injured_players_view')
                    ->where('player_id', $player->id)
                    ->where('season_id', $seasonId)
                    ->where('status', 'Injured') // Check if the injury is still ongoing
                    ->first();

                if ($injury) {
                    // Apply a penalty to ratings based on the injury
                    $penaltyFactor = 0.8;  // Example: Reduce by 20%
                    $player->shooting_rating *= $penaltyFactor;
                    $player->defense_rating *= $penaltyFactor;
                    $player->passing_rating *= $penaltyFactor;
                    $player->rebounding_rating *= $penaltyFactor;
                    $player->overall_rating *= $penaltyFactor;

                    \Log::info('Injury penalty applied', ['player_id' => $player->id]);
                }

                // Check if the player has recovered from injury for over 30 games, may be waived
                if ($injury && $injury->injury_recovery_games > 30) {
                    $waiveChance = rand(1, 100); // Random chance for waiving the player
                    if ($waiveChance <= 50) { // 50% chance to waive
                        DB::table('transactions')->insert([
                            'player_id' => $player->id,
                            'season_id' => $seasonId,
                            'details' => 'Waived due to extended injury recovery period',
                            'from_team_id' => $teamId,
                            'to_team_id' => 0,
                            'status' => 'waived',
                        ]);

                        $player->is_active = 0; // Mark player as waived
                        $player->contract_years = 0; // Remove contract
                        \Log::info('Player waived due to injury recovery period', ['player_id' => $player->id]);
                    }
                }

                $performanceData = $this->comparePerformanceBetweenSeasons($player->id);

                // $latestPerformance = $performanceData['latest_performance'];
                // $previousPerformance = $performanceData['previous_performance'];
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
                    'is_rookie' => $player->is_rookie,
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
            if ($teamId == $lastTeamId) {
                \Log::info('Processing last update.');

                ///lastly update all active players to non rookie
                //free agent veteran players
                DB::table('players')
                    ->where('team_id', 0)
                    ->where('is_active', 1)
                    ->update([
                        'is_rookie' => 0,
                        'age' => DB::raw('age + 1'), // Increment age by 1
                        'contract_years' => DB::raw("CASE WHEN age + 1 >= retirement_age THEN 0 ELSE contract_years END"), // Set contract_years to 0 if age reaches retirement_age
                        'team_id' => 0,
                        'is_active' => DB::raw("CASE WHEN age + 1 >= retirement_age THEN 0 ELSE is_active END"), // Set is_active to 0 if age reaches retirement_age
                    ]);

                ///active players with teams
                // DB::table('players')
                //     ->where('team_id', '>', 0)
                //     ->where('is_active', 1)
                //     ->update([
                //         'is_rookie' => 0,
                //         'age' => DB::raw('age + 1'), // Increment age by 1
                //         'contract_years' => DB::raw("CASE WHEN age + 1 >= retirement_age THEN 0 ELSE contract_years END"), // Set contract_years to 0 if age reaches retirement_age
                //         'team_id' => DB::raw("CASE WHEN age + 1 >= retirement_age THEN 0 ELSE team_id END"), // Set team_id to 0 if age reaches retirement_age
                //         'is_active' => DB::raw("CASE WHEN age + 1 >= retirement_age THEN 0 ELSE is_active END"), // Set is_active to 0 if age reaches retirement_age
                //     ]);

                ///lastly update the age of non active players
                DB::table('players')
                    ->where('is_active', 0)
                    ->update([
                        'team_id' => 0,
                        'contract_years' => 0,
                        'age' => DB::raw('age + 1'), // Increment age by 1
                    ]);


                // Update season status
                $season = Seasons::find($seasonId);
                if ($season) {
                    $season->status = config('timeline.player_update');
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
