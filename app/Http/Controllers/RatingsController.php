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



            $roleMapping = [
                1 => 'starter',
                2 => 'role player',
                3 => 'bench',
                4 => 'bench', // No further downgrade
            ];

            // Fetch all active players, filtered by team_id if provided
            $query = Player::where('is_active', 1);
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
            $players = $query->get();

            $seasonId = $this->getLatestSeasonId();
            \Log::info('Latest season ID:', ['seasonId' => $seasonId]);

            $improvedPlayers = [];
            $declinedPlayers = [];

            foreach ($players as $player) {
                // Check if player ratings for the current season already exist
                $ratingExists = DB::table('player_ratings')
                    ->where('player_id', $player->id)
                    ->where('season_id', $seasonId)
                    ->exists();

                if (!$ratingExists) {
                    // Store old ratings and role for comparison
                    $oldRatings = [
                        'shooting' => $player->shooting_rating,
                        'defense' => $player->defense_rating,
                        'passing' => $player->passing_rating,
                        'rebounding' => $player->rebounding_rating,
                        'overall' => $player->overall_rating,
                    ];
                    $oldRole = $player->role;

                    // Deduct contract_years by 1
                    $player->contract_years -= 1;
                    $player->is_rookie = 0;

                    // Check if contract_years is 0 and update team_id to 0
                    if ($player->contract_years <= 0) {
                        $player->contract_years = 0; // Ensure contract_years is exactly 0
                        $player->team_id = 0; // Update team_id to 0
                    }

                    // Increment age by 1
                    $player->age += 1;

                    // Determine if the player should have an injury_prone_percentage of 0
                    if (rand(1, 100) <= 20) {
                        // Assign a random value between 1 and 100
                        $player->injury_prone_percentage = rand(1, 100);
                    } else {
                        $player->injury_prone_percentage = 0;
                    }

                    // Update player ratings based on performance
                    $performance = $this->calculatePerformance($player->id); // Calculate performance from game stats
                    $player->shooting_rating = $this->updateRating($player->shooting_rating, $performance['shooting']);
                    $player->defense_rating = $this->updateRating($player->defense_rating, $performance['defense']);
                    $player->passing_rating = $this->updateRating($player->passing_rating, $performance['passing']);
                    $player->rebounding_rating = $this->updateRating($player->rebounding_rating, $performance['rebounding']);
                    $player->overall_rating = ($player->shooting_rating + $player->defense_rating + $player->passing_rating + $player->rebounding_rating) / 4;

                    $player->role = $this->updateRoleBasedOnPerformance($player);

                    // Check for improvements or declines
                    if ($player->overall_rating > $oldRatings['overall'] || $player->role != $oldRole) {
                        $improvedPlayers[] = $player;
                    } elseif ($player->overall_rating < $oldRatings['overall'] || $player->role != $oldRole) {
                        $declinedPlayers[] = $player;
                    }

                    // Check for retirement
                    if ($player->age >= $player->retirement_age) {
                        $player->is_active = 0;
                        $player->team_id = 0;
                    }

                    // Save the updated player data
                    $player->save();
                }

                // Log the updated ratings
                $this->logPlayerRatings($player, $seasonId);
            }

            // Fetch the team name if team_id is provided
            $teamName = '';
            if ($teamId) {
                $team = Teams::find($teamId);
                if ($team) {
                    $teamName = $team->name;
                }
            }

            // Show alert if this is the last update
            if ($isLast) {
                \Log::info('Processing last update.');

                // Use Eloquent to update the season status
                $season = Seasons::find($seasonId);
                if ($season) {
                    $season->status = 9;
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
                ]);
            }

            DB::commit(); // Commit transaction

            return response()->json([
                'error' => false,
                'message' => 'All player statuses have been updated successfully.',
                'team_name' => $teamName,
                'improved_players' => $improvedPlayers,
                'declined_players' => $declinedPlayers,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            \Log::error('Failed to update player statuses', ['exception' => $e]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to update player statuses.',
            ], 500);
        }
    }

    // Define role priority (lower number = higher priority)
    protected $rolePriority = [
        'star player' => 1,
        'starter' => 2,
        'role player' => 3,
        'bench' => 4,
    ];
    // Function to update role based on performance
    protected function updateRoleBasedOnPerformance(Player $player)
    {
        // Role thresholds based on overall rating
        $roleThresholds = [
            'star player' => 85,
            'starter' => 75,
            'role player' => 60,
            'bench' => 40,
        ];


        // Check current role
        $currentRolePriority = $this->rolePriority[$player->role];

        // Determine new role based on updated overall rating
        $newRole = $player->role; // Default to current role

        if ($player->overall_rating >= $roleThresholds['star player']) {
            $newRole = 'star player';
        } elseif ($player->overall_rating >= $roleThresholds['starter']) {
            $newRole = 'starter';
        } elseif ($player->overall_rating >= $roleThresholds['role player']) {
            $newRole = 'role player';
        } else {
            $newRole = 'bench';
        }

        // Enforce incremental upgrades/downgrades
        $newRolePriority = $this->rolePriority[$newRole];

        if ($newRolePriority > $currentRolePriority) {
            // Upgrade only if the difference in priority is 1
            if ($newRolePriority - $currentRolePriority == 1) {
                return $newRole;
            }
        } elseif ($newRolePriority < $currentRolePriority) {
            // Downgrade only if the difference in priority is 1
            if ($currentRolePriority - $newRolePriority == 1) {
                return $newRole;
            }
        }

        // No change in role if upgrade/downgrade rules are not met
        return $player->role;
    }

    // Function to update player rating based on performance
    protected function updateRating($currentRating, $performance)
    {
        // Define rating adjustment based on performance
        $adjustment = 0;

        if ($performance > 85) {
            $adjustment = 3; // High performance, significant increase
        } elseif ($performance > 75) {
            $adjustment = 2; // Good performance, moderate increase
        } elseif ($performance > 60) {
            $adjustment = 1; // Decent performance, slight increase
        } elseif ($performance < 40) {
            $adjustment = -2; // Poor performance, significant decrease
        } elseif ($performance < 50) {
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

    private function calculatePerformance($playerId)
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
