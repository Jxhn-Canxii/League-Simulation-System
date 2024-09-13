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

            $seasonId = $this->getLatestSeasonId();

            if($isLast){
                DB::table('seasons')
                ->where('id',  $seasonId)
                ->update(['status' => 10]);
            }
            // Fetch all active players for the given team
            $players = Player::where('team_id', $teamId)->where('is_active', 1)->get();

            // Fetch latest season stats for all players in the team
            $playerStats = DB::table('player_season_stats')
                ->where('season_id', $seasonId)
                ->whereIn('player_id', $players->pluck('id'))
                ->select(
                    'player_id',
                    DB::raw('avg_points_per_game + avg_rebounds_per_game + avg_assists_per_game + avg_steals_per_game + avg_blocks_per_game as total_performance')
                )
                ->orderByDesc('total_performance')
                ->get();

            // Rank players based on the total performance
            $rankedPlayers = $playerStats->pluck('player_id')->toArray();

            if (count($rankedPlayers) >= 12) {
                // Assign roles based on rankings
                foreach ($rankedPlayers as $rank => $playerId) {
                    $player = Player::find($playerId);

                    // Role assignment based on rank
                    if ($rank < 3) {
                        $player->role = 'star player';   // Top 3: Star player
                    } elseif ($rank < 6) {
                        $player->role = 'starter';       // Rank 4-6: Starter
                    } elseif ($rank < 9) {
                        $player->role = 'role player';   // Rank 7-9: Role player
                    } else {
                        $player->role = 'bench';         // Rank 10-12: Bench
                    }

                    // Save the updated role
                    $player->save();
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Not enough players to assign roles. Each team needs at least 12 active players.',
                ], 400);
            }

            // Process players for contract years, performance changes, and re-signs
            $improvedPlayers = [];
            $declinedPlayers = [];
            $reSignedPlayers = [];

            foreach ($players as $player) {
                // Skip if player ratings for current season exist in the player_ratings table
                if (DB::table('player_ratings')->where('player_id', $player->id)->where('season_id', $seasonId)->exists()) {
                    continue;
                }

                // Deduct contract years
                $player->contract_years -= 1;
                $player->is_rookie = 0;

                // Handle contract expiration
                if ($player->contract_years == 0) {
                    $reSignChance = match ($player->role) {
                        'star player' => 70,
                        'starter' => 50,
                        'role player' => 30,
                        'bench' => 10,
                        default => 50,
                    };

                    if (mt_rand(1, 100) <= $reSignChance) {
                        // Player re-signs: assign new contract based on role
                        $player->contract_years = $this->getContractYearsBasedOnRole($player->role);
                        $reSignedPlayers[] = $player; // Track re-signed player
                    } else {
                        // Player becomes a free agent
                        $player->contract_years = 0;
                        $player->team_id = 0;
                    }
                }

                // Update player age and check for retirement
                $player->age += 1;

                if ($player->age >= $player->retirement_age) {
                    $player->is_active = 0;
                    $player->team_id = 0;
                }

                // Update player ratings based on performance change
                $performanceData = $this->comparePerformanceBetweenSeasons($player->id);

                $performanceChange = $performanceData['performance_change'];

                // Adjust player ratings based on performance
                foreach (['shooting', 'defense', 'passing', 'rebounding'] as $category) {
                    if ($performanceChange[$category] > 0) {
                        // Increase the rating if performance improved, max 99
                        $player->{$category . '_rating'} = min($player->{$category . '_rating'} + 2, 99);
                    } elseif ($performanceChange[$category] < 0) {
                        // Decrease the rating if performance declined, min 40
                        $player->{$category . '_rating'} = max($player->{$category . '_rating'} - 2, 40);
                    }
                }

                // Recalculate the player's overall rating
                $player->overall_rating = ($player->shooting_rating + $player->defense_rating + $player->passing_rating + $player->rebounding_rating) / 4;

                // Save updated player data in `players` table
                $player->save();

                // Insert or update the player's ratings in the `player_ratings` table
                DB::table('player_ratings')->updateOrInsert(
                    [
                        'player_id' => $player->id,
                        'season_id' => $seasonId,
                    ],
                    [
                        'team_id' => $player->team_id,
                        'role' => $player->role,
                        'shooting_rating' => $player->shooting_rating,
                        'defense_rating' => $player->defense_rating,
                        'passing_rating' => $player->passing_rating,
                        'rebounding_rating' => $player->rebounding_rating,
                        'overall_rating' => $player->overall_rating,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Fetch the team name
            $teamName = '';
            if ($teamId) {
                $team = Teams::find($teamId);
                if ($team) {
                    $teamName = $team->name;
                }
            }
            DB::commit(); // Commit transaction

            return response()->json([
                'error' => false,
                'message' => 'Player statuses, roles, and ratings have been updated successfully.',
                'improved_players' => $improvedPlayers,
                'declined_players' => $declinedPlayers,
                're_signed_players' => $reSignedPlayers,
                'team_name' => $teamName,
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            \Log::error('Failed to update player statuses', ['exception' => $e]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to update player statuses.',
                'error_message' => $e,
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

    // Function to update role based on performance
    private function updateRoleBasedOnPerformance($player)
    {

        $roleThresholds = $this->roleThresholds;
        $currentRolePriority = $this->rolePriority[$player->role];
        $newRole = $player->role;

        // Check if the player is underperforming for their current role
        $this->adjustRatingsForRole($player);

        // Determine new role based on updated overall rating
        if ($player->overall_rating >= $roleThresholds['star player']) {
            $newRole = 'star player';
        } elseif ($player->overall_rating >= $roleThresholds['starter']) {
            $newRole = 'starter';
        } elseif ($player->overall_rating >= $roleThresholds['role player']) {
            $newRole = 'role player';
        } else {
            $newRole = 'bench';
        }

        // Incremental role change (only one step up or down)
        $newRolePriority = $this->rolePriority[$newRole];

        if ($newRolePriority > $currentRolePriority && $newRolePriority - $currentRolePriority == 1) {
            // Allow upgrade only by one role step
            return $newRole;
        } elseif ($newRolePriority < $currentRolePriority && $currentRolePriority - $newRolePriority == 1) {
            // Allow downgrade only by one role step
            return $newRole;
        }

        // If the role shouldn't change, return the current role
        return $player->role;
    }
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

    // Function to update player rating based on performance
    private function updateRating($currentRating, $performance, $role)
    {
        // Define the base performance thresholds for each role
        $rolePerformanceThresholds = $this->roleThresholds;

        // Get the performance threshold for the current role
        $expectedPerformance = $rolePerformanceThresholds[$role] ?? 60; // Default to 60 if role is not found

        // Define rating adjustment based on performance relative to the role's expectations
        $adjustment = 0;

        if ($performance > $expectedPerformance + 10) {
            $adjustment = 3; // High performance, significant increase
        } elseif ($performance > $expectedPerformance) {
            $adjustment = 2; // Good performance, moderate increase
        } elseif ($performance >= $expectedPerformance - 15) {
            $adjustment = 1; // Decent performance, slight increase
        } elseif ($performance < $expectedPerformance - 30) {
            $adjustment = -2; // Poor performance, significant decrease
        } elseif ($performance < $expectedPerformance - 15) {
            $adjustment = -1; // Below average performance, slight decrease
        }

        // Ensure rating remains within bounds of 40-99
        $newRating = max(40, min(99, $currentRating + $adjustment));

        return $newRating;
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



    // Helper function to get the number of seasons played by the player
    private function getSeasonsPlayed($playerId)
    {
        return PlayerGameStats::where('player_id', $playerId)
            ->distinct('season_id')
            ->count();
    }

    private function calculatePerformanceV1($playerId)
    {
        // Fetch game stats for the current season where minutes are greater than 0
        $stats = PlayerGameStats::where('player_id', $playerId)
            ->where('season_id', $this->getLatestSeasonId())
            ->where('minutes', '>', 0)
            ->get();

        // Initialize performance metrics
        $performance = [
            'shooting' => 0,
            'defense' => 0,
            'passing' => 0,
            'rebounding' => 0,
        ];

        // Aggregate stats
        foreach ($stats as $stat) {
            $performance['shooting'] += $stat->points / max($stat->minutes, 1); // Points per minute
            $performance['defense'] += $stat->blocks + $stat->steals; // Combined defensive stats
            $performance['passing'] += $stat->assists / max($stat->minutes, 1); // Assists per minute
            $performance['rebounding'] += $stat->rebounds / max($stat->minutes, 1); // Rebounds per minute
        }

        // Calculate averages
        $totalGames = $stats->count();
        if ($totalGames > 0) {
            $performance['shooting'] /= $totalGames;
            $performance['defense'] /= $totalGames;
            $performance['passing'] /= $totalGames;
            $performance['rebounding'] /= $totalGames;
        }

        // Determine the dynamic maximum values for normalization based on league stats
        $leagueMax = PlayerGameStats::where('season_id', $this->getLatestSeasonId())
            ->selectRaw('
            MAX(points / GREATEST(minutes, 1)) as max_shooting,
            MAX(blocks + steals) as max_defense,
            MAX(assists / GREATEST(minutes, 1)) as max_passing,
            MAX(rebounds / GREATEST(minutes, 1)) as max_rebounding
        ')
            ->first();

        // Use the league max values or fallback to a default max (e.g., 99) if none found
        $maxValues = [
            'shooting' => $leagueMax->max_shooting ?? 99,
            'defense' => $leagueMax->max_defense ?? 99,
            'passing' => $leagueMax->max_passing ?? 99,
            'rebounding' => $leagueMax->max_rebounding ?? 99,
        ];

        // Normalize and rate each metric on a 40-99 scale
        foreach ($performance as $key => $value) {
            $normalizedValue = ($value / $maxValues[$key]) * 59 + 40; // Scale to 40-99 range
            $performance[$key] = min(max($normalizedValue, 40), 99); // Ensure value stays within bounds
        }

        return $performance;
    }
    private function comparePerformanceBetweenSeasonsV1($playerId)
    {
        // Get the latest season ID and the previous season ID
        $latestSeasonId = $this->getLatestSeasonId();
        $previousSeasonId = $this->getPreviousSeasonId($latestSeasonId); // This should return the season before the latest one

        // Fetch stats for the latest season
        $latestStats = PlayerGameStats::where('player_id', $playerId)
            ->where('season_id', $latestSeasonId)
            ->where('minutes', '>', 0)
            ->get();

        // Fetch stats for the previous season
        $previousStats = PlayerGameStats::where('player_id', $playerId)
            ->where('season_id', $previousSeasonId)
            ->where('minutes', '>', 0)
            ->get();

        // Initialize performance metrics for both seasons
        $latestPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];
        $previousPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];

        // Aggregate stats for the latest season
        foreach ($latestStats as $stat) {
            $latestPerformance['shooting'] += $stat->points / max($stat->minutes, 1); // Points per minute
            $latestPerformance['defense'] += $stat->blocks + $stat->steals; // Defensive stats
            $latestPerformance['passing'] += $stat->assists / max($stat->minutes, 1); // Assists per minute
            $latestPerformance['rebounding'] += $stat->rebounds / max($stat->minutes, 1); // Rebounds per minute
        }

        // Aggregate stats for the previous season
        foreach ($previousStats as $stat) {
            $previousPerformance['shooting'] += $stat->points / max($stat->minutes, 1); // Points per minute
            $previousPerformance['defense'] += $stat->blocks + $stat->steals; // Defensive stats
            $previousPerformance['passing'] += $stat->assists / max($stat->minutes, 1); // Assists per minute
            $previousPerformance['rebounding'] += $stat->rebounds / max($stat->minutes, 1); // Rebounds per minute
        }

        // Calculate averages for both seasons
        $latestTotalGames = $latestStats->count();
        $previousTotalGames = $previousStats->count();

        if ($latestTotalGames > 0) {
            foreach ($latestPerformance as $key => $value) {
                $latestPerformance[$key] /= $latestTotalGames; // Average per game stats
            }
        }

        if ($previousTotalGames > 0) {
            foreach ($previousPerformance as $key => $value) {
                $previousPerformance[$key] /= $previousTotalGames; // Average per game stats
            }
        }

        // Compare performance between the two seasons
        $performanceChange = [];
        foreach ($latestPerformance as $key => $latestValue) {
            $previousValue = $previousPerformance[$key] ?? 0; // Default to 0 if no previous stats
            $performanceChange[$key] = $latestValue - $previousValue; // Calculate performance difference
        }

        return [
            'latest_performance' => $latestPerformance,
            'previous_performance' => $previousPerformance,
            'performance_change' => $performanceChange, // How much the player improved or declined
        ];
    }
    private function comparePerformanceBetweenSeasons($playerId)
    {
        // Get the latest season ID and the previous season ID
        $latestSeasonId = $this->getLatestSeasonId();
        $previousSeasonId = $this->getPreviousSeasonId($latestSeasonId);

        // Fetch stats for the latest season
        $latestStats = PlayerGameStats::where('player_id', $playerId)
            ->where('season_id', $latestSeasonId)
            ->where('minutes', '>', 0)
            ->get();

        // Fetch stats for the previous season
        $previousStats = PlayerGameStats::where('player_id', $playerId)
            ->where('season_id', $previousSeasonId)
            ->where('minutes', '>', 0)
            ->get();

        // Initialize performance metrics for both seasons
        $latestPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];
        $previousPerformance = ['shooting' => 0, 'defense' => 0, 'passing' => 0, 'rebounding' => 0];

        // Aggregate stats for the latest season
        foreach ($latestStats as $stat) {
            $pointsPerMinute = $stat->points / max($stat->minutes, 1);
            $assistsPerMinute = $stat->assists / max($stat->minutes, 1);
            $reboundsPerMinute = $stat->rebounds / max($stat->minutes, 1);

            // Add fouls and turnovers impact
            $foulsImpact = $stat->fouls * -0.2; // Arbitrary negative impact
            $turnoversImpact = $stat->turnovers * -0.1; // Arbitrary negative impact

            $latestPerformance['shooting'] += $pointsPerMinute + $foulsImpact; // Points per minute adjusted by fouls
            $latestPerformance['defense'] += ($stat->blocks + $stat->steals) - $turnoversImpact; // Defensive stats adjusted by turnovers
            $latestPerformance['passing'] += $assistsPerMinute - $turnoversImpact; // Assists per minute adjusted by turnovers
            $latestPerformance['rebounding'] += $reboundsPerMinute; // Rebounds are not directly impacted by fouls or turnovers
        }

        // Aggregate stats for the previous season
        foreach ($previousStats as $stat) {
            $pointsPerMinute = $stat->points / max($stat->minutes, 1);
            $assistsPerMinute = $stat->assists / max($stat->minutes, 1);
            $reboundsPerMinute = $stat->rebounds / max($stat->minutes, 1);

            // Add fouls and turnovers impact
            $foulsImpact = $stat->fouls * -0.2; // Arbitrary negative impact
            $turnoversImpact = $stat->turnovers * -0.1; // Arbitrary negative impact

            $previousPerformance['shooting'] += $pointsPerMinute + $foulsImpact; // Points per minute adjusted by fouls
            $previousPerformance['defense'] += ($stat->blocks + $stat->steals) - $turnoversImpact; // Defensive stats adjusted by turnovers
            $previousPerformance['passing'] += $assistsPerMinute - $turnoversImpact; // Assists per minute adjusted by turnovers
            $previousPerformance['rebounding'] += $reboundsPerMinute; // Rebounds are not directly impacted by fouls or turnovers
        }

        // Calculate averages for both seasons
        $latestTotalGames = $latestStats->count();
        $previousTotalGames = $previousStats->count();

        if ($latestTotalGames > 0) {
            foreach ($latestPerformance as $key => $value) {
                $latestPerformance[$key] /= $latestTotalGames; // Average per game stats
            }
        }

        if ($previousTotalGames > 0) {
            foreach ($previousPerformance as $key => $value) {
                $previousPerformance[$key] /= $previousTotalGames; // Average per game stats
            }
        }

        // Compare performance between the two seasons
        $performanceChange = [];
        foreach ($latestPerformance as $key => $latestValue) {
            $previousValue = $previousPerformance[$key] ?? 0; // Default to 0 if no previous stats
            $performanceChange[$key] = $latestValue - $previousValue; // Calculate performance difference
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
