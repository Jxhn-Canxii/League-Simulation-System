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
    public function updateActivePlayers()
    {
        DB::beginTransaction(); // Start transaction

        try {
            // Define role priority and possible new roles
            $rolePriority = [
                'star player' => 1,
                'starter' => 2,
                'role player' => 3,
                'bench' => 4,
            ];

            $roleMapping = [
                1 => 'starter',
                2 => 'role player',
                3 => 'bench',
                4 => 'bench' // No further downgrade
            ];

            // Fetch all active players
            $players = Player::where('is_active', 1)->get();

            $seasonId = $this->getLatestSeasonId();

            foreach ($players as $player) {
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

                // Check for retirement
                if ($player->age >= $player->retirement_age) {
                    $player->is_active = 0;
                    $player->team_id = 0;
                } else {
                    // Adjust role if player is near retirement
                    if ($player->age >= ($player->retirement_age - 3)) { // Near retirement
                        $currentPriority = $rolePriority[$player->role];
                        if ($currentPriority < 4) {
                            $player->role = $roleMapping[$currentPriority];
                        }
                    }
                }

                // Determine if the player should have an injury_prone_percentage of 0
                if (rand(1, 100) <= 20) {
                    // Assign a random value between 1 and 100
                    $player->injury_prone_percentage = rand(1, 100);
                } else {
                    $player->injury_prone_percentage = 0;
                }

                // Save the updated player data
                $player->save();

                // Update player ratings based on performance
                $performance = $this->calculatePerformance($player->id); // Calculate performance from game stats
                $player->shooting_rating = $this->updateRating($player->shooting_rating, $performance['shooting']);
                $player->defense_rating = $this->updateRating($player->defense_rating, $performance['defense']);
                $player->passing_rating = $this->updateRating($player->passing_rating, $performance['passing']);
                $player->rebounding_rating = $this->updateRating($player->rebounding_rating, $performance['rebounding']);
                $player->overall_rating = ($player->shooting_rating + $player->defense_rating + $player->passing_rating + $player->rebounding_rating) / 4;

                // Insert updated ratings into player_ratings table
                $this->logPlayerRatings($player, $seasonId);
            }

            // Update the last season's status to 9
            DB::table('seasons')
                ->where('id', $seasonId)
                ->update(['status' => 9]);

            DB::commit(); // Commit transaction

            // Return a response indicating all player statuses have been updated
            return response()->json([
                'error' => false,
                'message' => 'All player statuses have been updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            // Log the error
            \Log::error('Failed to update player statuses', ['exception' => $e]);

            // Return an error response
            return response()->json([
                'error' => true,
                'message' => 'Failed to update player statuses.',
            ], 500);
        }
    }

    private function logPlayerRatings($player, $seasonId)
    {
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
    }
    // Function to update ratings based on performance


    // Function to update player role based on performance
    private function updateRoleBasedOnPerformance($player)
    {
        $seasonPlayedCount = $this->getSeasonsPlayed($player->id);

        // If the player is a rookie or has not played any season, do not change their role
        if ($player->is_rookie || $seasonPlayedCount == 0) {
            return $player->role;
        }

        // Determine the role based on overall rating
        if ($player->overall_rating >= 85) {
            return 'star player';
        } elseif ($player->overall_rating >= 75) {
            return 'starter';
        } elseif ($player->overall_rating >= 60) {
            return 'role player';
        } elseif ($player->overall_rating >= 40) {
            return 'bench';
        } else {
            return 'bench'; // Default to bench if the rating is below 40
        }
    }

    // Helper function to get the number of seasons played by the player
    private function getSeasonsPlayed($playerId)
    {
        return PlayerGameStats::where('player_id', $playerId)
            ->distinct('season_id')
            ->count();
    }

    // Function to calculate player performance based on game stats
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

        // Define maximum values for normalization
        $maxValues = [
            'shooting' => 99, // Adjust based on your expectations
            'defense' => 99, // Adjust based on your expectations
            'passing' => 99, // Adjust based on your expectations
            'rebounding' => 99, // Adjust based on your expectations
        ];

        // Normalize and rate each metric on a 40-99 scale
        foreach ($performance as $key => $value) {
            $normalizedValue = ($value / $maxValues[$key]) * 59 + 40; // Scale to 40-99 range
            $performance[$key] = min(max($normalizedValue, 40), 99); // Ensure value stays within bounds
        }

        return $performance;
    }


    private function updateRating($currentRating, $performanceMetric)
    {
        // Ensure performanceMetric is between 0 and 100
        $performanceMetric = min(max($performanceMetric, 0), 100);

        // Adjust the rating based on the performance
        $newRating = $currentRating + ($performanceMetric - $currentRating) / 10;

        // Ensure the rating stays within the 40-99 bounds
        return min(max($newRating, 40), 99);
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
