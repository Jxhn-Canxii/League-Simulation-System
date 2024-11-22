<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use Exception;
use Inertia\Inertia;
use App\Models\Seasons;
use App\Models\Teams;
use App\Models\Schedules;
use App\Models\Conference;
use App\Models\Player;
use App\Models\PlayerGameStats;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\PlayersController;
use Illuminate\Support\Facades\DB;

class SimulateController extends Controller
{
    //
    private function maxPoints()
    {
        return [
            'star player' => [
                'points' => rand(1, 100),
                'rebounds' => rand(1, 50),
                'assists' => rand(1, 30),
                'steals' => rand(1, 20),
                'blocks' => rand(1, 20),
            ],
            'starter' => [
                'points' => rand(1, 50),
                'rebounds' => rand(1, 30),
                'assists' => rand(1, 25),
                'steals' => rand(1, 15),
                'blocks' => rand(1, 15),
            ],
            'role player' => [
                'points' => rand(1, 30),
                'rebounds' => rand(1, 20),
                'assists' => rand(1, 20),
                'steals' => rand(1, 15),
                'blocks' => rand(1, 15),
            ],
            'bench' => [
                'points' => rand(1, 20),
                'rebounds' => rand(1, 15),
                'assists' => rand(1, 10),
                'steals' => rand(1, 10),
                'blocks' => rand(1, 100),
            ]
        ];
    }

    public function simulateplayoff(Request $request)
    {
        // Validate the request data
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $storeStats = new AwardsController;
        // Fetch game data
        $gameData = Schedules::join('teams as home', 'schedules.home_id', '=', 'home.id')
            ->join('teams as away', 'schedules.away_id', '=', 'away.id')
            ->join('standings_view as home_standings', function ($join) {
                $join->on('home.id', '=', 'home_standings.team_id')
                    ->whereColumn('home_standings.season_id', 'schedules.season_id');
            })
            ->join('standings_view as away_standings', function ($join) {
                $join->on('away.id', '=', 'away_standings.team_id')
                    ->whereColumn('away_standings.season_id', 'schedules.season_id');
            })
            ->select(
                'schedules.id',
                'schedules.round',
                'schedules.season_id',
                'schedules.game_id',
                'home.id as home_team_id',
                'home.name as home_team_name',
                'away.id as away_team_id',
                'away.name as away_team_name',
                'home_standings.overall_rank as home_overall_rank',
                'away_standings.overall_rank as away_overall_rank',
                'home_standings.conference_name as home_conference_name',
                'away_standings.conference_name as away_conference_name',
                'home_standings.conference_rank as home_conference_rank',
                'away_standings.conference_rank as away_conference_rank',
                'home_standings.wins as home_current_performance',
                'away_standings.wins as away_current_performance',
                'schedules.home_score',
                'schedules.away_score',
                'schedules.status'
            )
            ->findOrFail($request->schedule_id);


        // Fetch current season ID
        $currentSeasonId = $gameData->season_id;

        // Define role-based priority and maximum points
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Define total minutes available for each team
        $totalMinutes = 240;


        // Fetch and prioritize players for home and away teams
        $homeTeamPlayers = Player::where('team_id', $gameData->home_team_id)->get()
            ->sortBy(function ($player) use ($rolePriority) {
                return $rolePriority[$player->role] ?? 5; // Default to a lower priority if role not found
            })->values();

        $awayTeamPlayers = Player::where('team_id', $gameData->away_team_id)->get()
            ->sortBy(function ($player) use ($rolePriority) {
                return $rolePriority[$player->role] ?? 5; // Default to a lower priority if role not found
            })->values();

        // Initialize arrays to hold player game stats and minutes
        $playerGameStats = [];

        // Distribute minutes to players considering injury status
        $homeMinutes = $this->distributeMinutes($homeTeamPlayers, $totalMinutes);
        $awayMinutes = $this->distributeMinutes($awayTeamPlayers, $totalMinutes);

        // Simulate player game stats for home team
        foreach ($homeTeamPlayers as $player) {
            $minutes = $homeMinutes[$player->id] ?? 0;
            // Calculate average defensive stats per game for the away team
            $awayTeamDefensiveStats = [
                'defensive_rating' => Player::where('team_id', $gameData->away_team_id)
                    ->where('is_active', 1) // Optional: If you have an active player flag
                    ->avg('defense_rating') ?? 0,
                'rebounding_rating' => Player::where('team_id', $gameData->away_team_id)
                    ->where('is_active', 1) // Optional: If you have an active player flag
                    ->avg('rebounding_rating') ?? 0,
            ];

            // If minutes is 0, player did not play
            if ($minutes === 0 || $player->is_injured) {
                $playerGameStats[] = [
                    'player_id' => $player->id,
                    'game_id' => $gameData->game_id,
                    'season_id' => $currentSeasonId,
                    'team_id' => $player->team_id,
                    'points' => 0,
                    'rebounds' => 0,
                    'assists' => 0,
                    'steals' => 0,
                    'blocks' => 0,
                    'turnovers' => 0,
                    'fouls' => 0,
                    'minutes' => 0,
                ];
            } else {
                $performanceFactor = rand(80, 120) / 100; // Randomize within 80% to 120%
                $pointsPerMinute = 0.5 + ($player->shooting_rating / 200);
                $points = round($pointsPerMinute * $minutes * $performanceFactor);

                $points = rand(0, $points);

                $assistPerMinute = 0.2 + ($player->passing_rating / 200);
                $assists = round($assistPerMinute * $minutes * $performanceFactor);

                $assists = rand(0, $assists);

                $reboundPerMinute = 0.3 + ($player->rebounding_rating / 200);
                $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);

                $rebounds = rand(0, $rebounds);

                $blocksPerMinute = 0.3 + ($player->blocks_rating / 200);
                $blocks = round($blocksPerMinute * $minutes * $performanceFactor);

                $blocks = rand(0, $blocks);


                $stealsPerMinute = 0.3 + ($player->steals_rating / 200);
                $steals = round($stealsPerMinute * $minutes * $performanceFactor);

                $steals = rand(0, $steals);

                // Apply defensive adjustments based on away team's defensive stats
                $defensiveImpact = ($awayTeamDefensiveStats['defensive_rating'] + $awayTeamDefensiveStats['rebounding_rating']) / 200; // Scale factor
                $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                $points = max($points, 0); // Ensure no negative points

                // Turnovers and fouls
                $turnovers = round(rand(0, 2));
                $fouls = round(rand(0, 4));

                // Update player game stats
                $playerGameStats[] = [
                    'player_id' => $player->id,
                    'game_id' => $gameData->game_id,
                    'season_id' => $currentSeasonId,
                    'team_id' => $player->team_id,
                    'points' => max($points, 0),      // Ensure no negative values
                    'rebounds' => max($rebounds, 0),  // Ensure no negative values
                    'assists' => max($assists, 0),    // Ensure no negative values
                    'steals' => max($steals, 0),      // Ensure no negative values
                    'blocks' => max($blocks, 0),      // Ensure no negative values
                    'turnovers' => max($turnovers, 0), // Ensure no negative values
                    'fouls' => max($fouls, 0),        // Ensure no negative values
                    'minutes' => max($minutes, 0),
                ];
            }
        }

        // Simulate player game stats for away team
        foreach ($awayTeamPlayers as $player) {
            $minutes = $awayMinutes[$player->id] ?? 0;

            // Calculate average defensive stats per game for the home team
            $homeTeamDefensiveStats = [
                'defensive_rating' => Player::where('team_id', $gameData->home_team_id)
                    ->where('is_active', 1) // Optional: If you have an active player flag
                    ->avg('defense_rating') ?? 0,
                'rebounding_rating' => Player::where('team_id', $gameData->home_team_id)
                    ->where('is_active', 1) // Optional: If you have an active player flag
                    ->avg('rebounding_rating') ?? 0,
            ];

            // If minutes is 0, player did not play
            if ($minutes === 0 || $player->is_injured) {
                $playerGameStats[] = [
                    'player_id' => $player->id,
                    'game_id' => $gameData->game_id,
                    'season_id' => $currentSeasonId,
                    'team_id' => $player->team_id,
                    'points' => 0,
                    'rebounds' => 0,
                    'assists' => 0,
                    'steals' => 0,
                    'blocks' => 0,
                    'turnovers' => 0,
                    'fouls' => 0,
                    'minutes' => 0,
                ];
            } else {
                $performanceFactor = rand(80, 120) / 100; // Randomize within 80% to 120%
                $pointsPerMinute = 0.5 + ($player->shooting_rating / 200);
                $points = round($pointsPerMinute * $minutes * $performanceFactor);

                $points = rand(0, $points);

                $assistPerMinute = 0.2 + ($player->passing_rating / 200);
                $assists = round($assistPerMinute * $minutes * $performanceFactor);

                $assists = rand(0, $assists);

                $reboundPerMinute = 0.3 + ($player->rebounding_rating / 200);
                $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);

                $rebounds = rand(0, $rebounds);

                $blocksPerMinute = 0.3 + ($player->blocks_rating / 200);
                $blocks = round($blocksPerMinute * $minutes * $performanceFactor);

                $blocks = rand(0, $blocks);


                $stealsPerMinute = 0.3 + ($player->steals_rating / 200);
                $steals = round($stealsPerMinute * $minutes * $performanceFactor);

                $steals = rand(0, $steals);

                // Apply defensive adjustments based on home team's defensive stats
                $defensiveImpact = ($homeTeamDefensiveStats['defensive_rating'] + $homeTeamDefensiveStats['rebounding_rating']) / 200; // Scale factor
                $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                $points = max($points, 0); // Ensure no negative points

                // Turnovers and fouls
                $turnovers = round(rand(0, 2));
                $fouls = round(rand(0, 4));

                // Update player game stats
                $playerGameStats[] = [
                    'player_id' => $player->id,
                    'game_id' => $gameData->game_id,
                    'season_id' => $currentSeasonId,
                    'team_id' => $player->team_id,
                    'points' => max($points, 0),      // Ensure no negative values
                    'rebounds' => max($rebounds, 0),  // Ensure no negative values
                    'assists' => max($assists, 0),    // Ensure no negative values
                    'steals' => max($steals, 0),      // Ensure no negative values
                    'blocks' => max($blocks, 0),      // Ensure no negative values
                    'turnovers' => max($turnovers, 0), // Ensure no negative values
                    'fouls' => max($fouls, 0),        // Ensure no negative values
                    'minutes' => max($minutes, 0),
                ];
            }
        }
        // Convert to arrays
        $homeTeamPlayers = $homeTeamPlayers->toArray();
        $awayTeamPlayers = $awayTeamPlayers->toArray();

        // Calculate total points for each team
        $totalHomePoints = array_sum(array_map(function ($stat) use ($gameData) {
            return $stat['team_id'] === $gameData->home_team_id ? $stat['points'] : 0;
        }, $playerGameStats));

        $totalAwayPoints = array_sum(array_map(function ($stat) use ($gameData) {
            return $stat['team_id'] === $gameData->away_team_id ? $stat['points'] : 0;
        }, $playerGameStats));


        // Assuming $homeTeamPlayers and $awayTeamPlayers are arrays of player stats with player ids
        // Retrieve passing ratings for home and away team players from the player table
        $homePassingTotal = 0;
        $homePassingAverage = 0;
        $awayPassingTotal = 0;
        $awayPassingAverage = 0;

        // Sum up passing ratings for home team players
        foreach ($homeTeamPlayers as $player) {
            $passingRating = $player['passing_rating'] ?? 0;  // Default to 0 if passing_rating is missing
            $homePassingTotal += $passingRating;
        }

        // Sum up passing ratings for away team players
        foreach ($awayTeamPlayers as $player) {
            $passingRating = $player['passing_rating'] ?? 0;  // Default to 0 if passing_rating is missing
            $awayPassingTotal += $passingRating;
        }

        // Calculate passing averages
        $homePassingAverage = count($homeTeamPlayers) > 0 ? $homePassingTotal / count($homeTeamPlayers) : 0;
        $awayPassingAverage = count($awayTeamPlayers) > 0 ? $awayPassingTotal / count($awayTeamPlayers) : 0;

        // Define maximum assists based on total points and completion rate
        $maxHomeAssists = round(($totalHomePoints / 2) * ($homePassingAverage / 100));
        $maxAwayAssists = round(($totalAwayPoints / 2) * ($awayPassingAverage / 100));

        // Track assists assigned to each team
        $homeAssistsAssigned = 0;
        $awayAssistsAssigned = 0;

        // Check if passing_rating exists in player stats before sorting
        foreach ($playerGameStats as &$stats) {
            // Ensure passing_rating exists, default to 0 if not
            if (!isset($stats['passing_rating'])) {
                $stats['passing_rating'] = 0;  // Default passing rating to 0 if it's missing
            }
        }

        // Sort players by passing rating in descending order
        usort($playerGameStats, function ($a, $b) {
            return $b['passing_rating'] <=> $a['passing_rating'];
        });

        // Function to distribute assists
        function distributeAssistsPlayoffs(&$playerGameStats, $teamId, $maxAssists, &$assistsAssigned)
        {
            $playmakerIndex = 0; // Track number of players assigned assists in this iteration

            // Calculate the assist range (half to 3/4 of max assists)
            $assistRange = rand(floor($maxAssists / 2), floor($maxAssists * 3 / 4));

            // Distribute assists among the top 5 to 7 playmakers
            $remainingAssists = $assistRange; // Remaining assists to distribute among top 5 to 7 playmakers
            $playmakers = [];

            foreach ($playerGameStats as &$stats) {
                if ($stats['team_id'] === $teamId && $stats['minutes'] > 0) { // Check if player has more than 0 minutes
                    // Collect the top playmakers (5-7 based on passing rating)
                    if ($playmakerIndex < 7) {
                        $playmakers[] = &$stats; // Add the player to the playmaker list
                    }
                    $playmakerIndex++;
                }
            }

            // Sort the players by passing rating in descending order
            usort($playmakers, function ($a, $b) {
                return $b['passing_rating'] <=> $a['passing_rating'];
            });

            // Randomly distribute the assistRange among the top 5 to 7 players
            $assistCount = count($playmakers);
            if ($assistCount > 0) {
                foreach ($playmakers as &$playmaker) {
                    // Randomly assign assists to each playmaker in the range of 0 to remaining assists
                    $maxForThisPlayer = min($remainingAssists, rand(0, floor($remainingAssists / 2)));
                    $playmaker['assists'] = $maxForThisPlayer;  // Assign assists

                    // Deduct from remaining assists
                    $remainingAssists -= $maxForThisPlayer;

                    // If there are no more assists to distribute, break early
                    if ($remainingAssists <= 0) {
                        break;
                    }
                }
            }

            // Any remaining assists to be distributed among the rest of the players
            $remainingAssistsToDistribute = $maxAssists - $assistRange - $remainingAssists;
            foreach ($playerGameStats as &$stats) {
                if ($stats['team_id'] === $teamId && !in_array($stats, $playmakers) && $stats['minutes'] > 0) { // Ensure player has minutes > 0
                    // Assign remaining assists to players who are not in the top playmaker group and have played minutes
                    $stats['assists'] = rand(0, floor($remainingAssistsToDistribute / 2));
                }
            }

            // Update the assists assigned counter
            $assistsAssigned = $maxAssists - $remainingAssists;
        }

        // Distribute assists for the home team
        distributeAssistsPlayoffs($playerGameStats, $gameData->home_team_id, $maxHomeAssists, $homeAssistsAssigned);

        // Distribute assists for the away team
        distributeAssistsPlayoffs($playerGameStats, $gameData->away_team_id, $maxAwayAssists, $awayAssistsAssigned);

        // Clear reference
        // unset($stats);

        // Update or insert player game stats
        foreach ($playerGameStats as $stats) {

            // Assuming you have a Player model
            Player::where('id', $stats['player_id'])->update(['fatigue' => 0]);

            PlayerGameStats::updateOrCreate(
                [
                    'player_id' => $stats['player_id'],
                    'game_id' => $stats['game_id'],
                    'season_id' => $stats['season_id'],
                    'team_id' => $stats['team_id'],
                ],
                $stats
            );

            $storeStats->storeplayerseasonstats($stats['team_id'], $stats['player_id']);
        }

        // Calculate scores based on player stats
        $homeScore = PlayerGameStats::where('team_id', $gameData->home_team_id)
            ->where('game_id', $gameData->game_id)
            ->sum('points');

        $awayScore = PlayerGameStats::where('team_id', $gameData->away_team_id)
            ->where('game_id', $gameData->game_id)
            ->sum('points');

        // Update the scores
        $gameData->home_score = $homeScore;
        $gameData->away_score = $awayScore;
        $gameData->status = 2; // Marking the game as completed

        // Save the updated scores
        $gameData->save();

        // Determine the winner
        $winnerId = $homeScore > $awayScore ? $gameData->home_team_id : ($homeScore < $awayScore ? $gameData->away_team_id : null);

        // Prepare an array to hold the update data for the seasons table if it's finals
        $seasonUpdateData = [];
        if ($gameData->round === 'semi_finals') {
            $this->updateConferenceChampions($gameData, $winnerId);
        }
        if ($gameData->round === 'finals') {
            // Find the MVP of the winning team
            $this->updateFinalsWinner($gameData, $winnerId, $homeScore, $awayScore);
        }

        // Update the seasons table if there are updates
        if (!empty($seasonUpdateData)) {
            DB::table('seasons')
                ->where('id', $gameData->season_id)
                ->update($seasonUpdateData);
        }

        $this->updateAllTeamStreaks();
        $this->updateHeadToHeadResults($gameData->id);
        // Prepare the schedule response data
        $schedule = [
            'id' => $gameData->id,
            'game_id' => $gameData->game_id,
            'home_team' => [
                'id' => $gameData->home_team_id,
                'name' => $gameData->home_team_name,
                'home_score' => $gameData->home_score,
                'conference' => $gameData->home_conference_name,
                'conference_rank' => $gameData->home_conference_rank,
                'overall_rank' => $gameData->home_overall_rank,
            ],
            'away_team' => [
                'id' => $gameData->away_team_id,
                'name' => $gameData->away_team_name,
                'away_score' => $gameData->away_score,
                'conference' => $gameData->away_conference_name,
                'conference_rank' => $gameData->away_conference_rank,
                'overall_rank' => $gameData->away_overall_rank,
            ],
            'winner' => $winnerId,
            'round' => $gameData->round,
        ];

        // Return the simulation result
        return response()->json([
            'message' => 'Game simulated successfully',
            'schedule' => $schedule
        ]);
    }
    public function simulateregular(Request $request)
    {
        // Validate the request data
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $storeStats = new AwardsController;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Fetch game data
            $gameData = Schedules::join('teams as home', 'schedules.home_id', '=', 'home.id')
                ->join('teams as away', 'schedules.away_id', '=', 'away.id')
                ->join('standings_view as home_standings', function ($join) {
                    $join->on('home.id', '=', 'home_standings.team_id')
                        ->whereColumn('home_standings.season_id', 'schedules.season_id');
                })
                ->join('standings_view as away_standings', function ($join) {
                    $join->on('away.id', '=', 'away_standings.team_id')
                        ->whereColumn('away_standings.season_id', 'schedules.season_id');
                })
                ->select(
                    'schedules.id',
                    'schedules.round',
                    'schedules.season_id',
                    'schedules.game_id',
                    'home.id as home_team_id',
                    'home.name as home_team_name',
                    'away.id as away_team_id',
                    'away.name as away_team_name',
                    'home_standings.overall_rank as home_overall_rank',
                    'away_standings.overall_rank as away_overall_rank',
                    'home_standings.conference_name as home_conference_name',
                    'away_standings.conference_name as away_conference_name',
                    'home_standings.conference_rank as home_conference_rank',
                    'away_standings.conference_rank as away_conference_rank',
                    'home_standings.wins as home_current_performance',
                    'away_standings.wins as away_current_performance',
                    'schedules.home_score',
                    'schedules.away_score',
                    'schedules.status'
                )
                ->findOrFail($request->schedule_id);

            // Check if the game status is already completed
            if ($gameData->status == 2) {
                return response()->json([
                    'message' => 'Game has already been simulated.',
                ], 400);
            }

            // Fetch current season ID
            $currentSeasonId = $gameData->season_id;

            // Define role-based priority and maximum points
            $rolePriority = [
                'star player' => 1,
                'starter' => 2,
                'role player' => 3,
                'bench' => 4,
            ];

            // Define total minutes available for each team
            $totalMinutes = 240;

            // Fetch and prioritize players for home and away teams
            $homeTeamPlayers = Player::where('team_id', $gameData->home_team_id)->get()
                ->sortBy(function ($player) use ($rolePriority) {
                    return $rolePriority[$player->role] ?? 5;
                })->values();

            $awayTeamPlayers = Player::where('team_id', $gameData->away_team_id)->get()
                ->sortBy(function ($player) use ($rolePriority) {
                    return $rolePriority[$player->role] ?? 5;
                })->values();

            // Initialize arrays to hold player game stats and minutes
            $playerGameStats = [];

            // Distribute minutes to players considering injury status
            $homeMinutes = $this->distributeMinutes($homeTeamPlayers, $totalMinutes);
            $awayMinutes = $this->distributeMinutes($awayTeamPlayers, $totalMinutes);
            // Simulate player game stats for home team
            foreach ($homeTeamPlayers as $player) {
                $minutes = $homeMinutes[$player->id] ?? 0;

                // Calculate average defensive stats per game for the away team
                $awayTeamDefensiveStats = [
                    'defensive_rating' => Player::where('team_id', $gameData->away_team_id)
                        ->where('is_active', 1) // Optional: If you have an active player flag
                        ->avg('defense_rating') ?? 0,
                    'rebounding_rating' => Player::where('team_id', $gameData->away_team_id)
                        ->where('is_active', 1) // Optional: If you have an active player flag
                        ->avg('rebounding_rating') ?? 0,
                ];
                // If minutes is 0, player did not play
                if ($minutes === 0 || $player->is_injured) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'game_id' => $gameData->game_id,
                        'season_id' => $currentSeasonId,
                        'team_id' => $player->team_id,
                        'points' => 0,
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                        'minutes' => 0,
                    ];
                } else {
                    $performanceFactor = rand(100, 120) / 100; // Randomize within 80% to 120%
                    $pointsPerMinute = 0.5 + ($player->shooting_rating / 200);
                    $points = round($pointsPerMinute * $minutes * $performanceFactor);

                    $points = rand(0, $points);

                    $reboundPerMinute = 0.3 + ($player->rebounding_rating / 300);
                    $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);

                    $rebounds = rand(0, $rebounds);

                    $blocksPerMinute = 0.3 + ($player->blocks_rating / 200);
                    $blocks = round($blocksPerMinute * $minutes * $performanceFactor);

                    $blocks = rand(0, $blocks);


                    $stealsPerMinute = 0.3 + ($player->steals_rating / 200);
                    $steals = round($stealsPerMinute * $minutes * $performanceFactor);

                    $steals = rand(0, $steals);

                    // Apply defensive adjustments based on away team's defensive stats
                    $defensiveImpact = ($awayTeamDefensiveStats['defensive_rating'] + $awayTeamDefensiveStats['rebounding_rating']) / 200; // Scale factor
                    $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                    $points = max($points, 0); // Ensure no negative points

                    // Turnovers and fouls
                    $turnovers = round(rand(0, 2));
                    $fouls = round(rand(0, 4));

                    // Update player game stats
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'game_id' => $gameData->game_id,
                        'season_id' => $currentSeasonId,
                        'team_id' => $player->team_id,
                        'points' => max($points, 0),      // Ensure no negative values
                        'assists' => 0,  // Ensure no negative values
                        'rebounds' => max($rebounds, 0),  // Ensure no negative values
                        'steals' => max($steals, 0),      // Ensure no negative values
                        'blocks' => max($blocks, 0),      // Ensure no negative values
                        'turnovers' => max($turnovers, 0), // Ensure no negative values
                        'fouls' => max($fouls, 0),        // Ensure no negative values
                        'minutes' => max($minutes, 0),
                    ];
                }
            }

            // Simulate player game stats for away team
            foreach ($awayTeamPlayers as $player) {
                $minutes = $awayMinutes[$player->id] ?? 0;;

                // Calculate average defensive stats per game for the home team
                $homeTeamDefensiveStats = [
                    'defensive_rating' => Player::where('team_id', $gameData->home_team_id)
                        ->where('is_active', 1) // Optional: If you have an active player flag
                        ->avg('defense_rating') ?? 0,
                    'rebounding_rating' => Player::where('team_id', $gameData->home_team_id)
                        ->where('is_active', 1) // Optional: If you have an active player flag
                        ->avg('rebounding_rating') ?? 0,
                ];
                // If minutes is 0, player did not play
                if ($minutes === 0 ||  $player->is_injured) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'game_id' => $gameData->game_id,
                        'season_id' => $currentSeasonId,
                        'team_id' => $player->team_id,
                        'points' => 0,
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                        'minutes' => 0,
                    ];
                } else {
                    $performanceFactor = rand(100, 120) / 100; // Randomize within 80% to 120%
                    $pointsPerMinute = 0.5 + ($player->shooting_rating / 200);
                    $points = round($pointsPerMinute * $minutes * $performanceFactor);

                    $points = rand(0, $points);

                    $reboundPerMinute = 0.3 + ($player->rebounding_rating / 300);
                    $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);

                    $rebounds = rand(0, $rebounds);

                    $blocksPerMinute = 0.3 + ($player->blocks_rating / 200);
                    $blocks = round($blocksPerMinute * $minutes * $performanceFactor);

                    $blocks = rand(0, $blocks);


                    $stealsPerMinute = 0.3 + ($player->steals_rating / 200);
                    $steals = round($stealsPerMinute * $minutes * $performanceFactor);

                    $steals = rand(0, $steals);

                    // Apply defensive adjustments based on home team's defensive stats
                    $defensiveImpact = ($homeTeamDefensiveStats['defensive_rating'] + $homeTeamDefensiveStats['rebounding_rating']) / 200; // Scale factor
                    $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                    $points = max($points, 0); // Ensure no negative points

                    // Turnovers and fouls
                    $turnovers = round(rand(0, 2));
                    $fouls = round(rand(0, 4));

                    // Update player game stats
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'game_id' => $gameData->game_id,
                        'season_id' => $currentSeasonId,
                        'team_id' => $player->team_id,
                        'points' => max($points, 0),      // Ensure no negative values
                        'assists' => 0,  // Ensure no negative values
                        'rebounds' => max($rebounds, 0),  // Ensure no negative values
                        'steals' => max($steals, 0),      // Ensure no negative values
                        'blocks' => max($blocks, 0),      // Ensure no negative values
                        'turnovers' => max($turnovers, 0), // Ensure no negative values
                        'fouls' => max($fouls, 0),        // Ensure no negative values
                        'minutes' => max($minutes, 0),
                    ];
                }
            }

            // Convert to arrays
            $homeTeamPlayers = $homeTeamPlayers->toArray();
            $awayTeamPlayers = $awayTeamPlayers->toArray();

            // Calculate total points for each team
            $totalHomePoints = array_sum(array_map(function ($stat) use ($gameData) {
                return $stat['team_id'] === $gameData->home_team_id ? $stat['points'] : 0;
            }, $playerGameStats));

            $totalAwayPoints = array_sum(array_map(function ($stat) use ($gameData) {
                return $stat['team_id'] === $gameData->away_team_id ? $stat['points'] : 0;
            }, $playerGameStats));


            // Assuming $homeTeamPlayers and $awayTeamPlayers are arrays of player stats with player ids
            // Retrieve passing ratings for home and away team players from the player table
            $homePassingTotal = 0;
            $homePassingAverage = 0;
            $awayPassingTotal = 0;
            $awayPassingAverage = 0;

            // Sum up passing ratings for home team players
            foreach ($homeTeamPlayers as $player) {
                $passingRating = $player['passing_rating'] ?? 0;  // Default to 0 if passing_rating is missing
                $homePassingTotal += $passingRating;
            }

            // Sum up passing ratings for away team players
            foreach ($awayTeamPlayers as $player) {
                $passingRating = $player['passing_rating'] ?? 0;  // Default to 0 if passing_rating is missing
                $awayPassingTotal += $passingRating;
            }

            // Calculate passing averages
            $homePassingAverage = count($homeTeamPlayers) > 0 ? $homePassingTotal / count($homeTeamPlayers) : 0;
            $awayPassingAverage = count($awayTeamPlayers) > 0 ? $awayPassingTotal / count($awayTeamPlayers) : 0;

            // Define maximum assists based on total points and completion rate
            $maxHomeAssists = round(($totalHomePoints / 2) * ($homePassingAverage / 100));
            $maxAwayAssists = round(($totalAwayPoints / 2) * ($awayPassingAverage / 100));

            // Track assists assigned to each team
            $homeAssistsAssigned = 0;
            $awayAssistsAssigned = 0;

            // Check if passing_rating exists in player stats before sorting
            foreach ($playerGameStats as &$stats) {
                // Ensure passing_rating exists, default to 0 if not
                if (!isset($stats['passing_rating'])) {
                    $stats['passing_rating'] = 0;  // Default passing rating to 0 if it's missing
                }
            }

            // Sort players by passing rating in descending order
            usort($playerGameStats, function ($a, $b) {
                return $b['passing_rating'] <=> $a['passing_rating'];
            });

            // Function to distribute assists
            function distributeAssists(&$playerGameStats, $teamId, $maxAssists, &$assistsAssigned)
            {
                $playmakerIndex = 0; // Track number of players assigned assists in this iteration

                // Calculate the assist range (half to 3/4 of max assists)
                $assistRange = rand(floor($maxAssists / 2), floor($maxAssists * 3 / 4));

                // Distribute assists among the top 5 to 7 playmakers
                $remainingAssists = $assistRange; // Remaining assists to distribute among top 5 to 7 playmakers
                $playmakers = [];

                foreach ($playerGameStats as &$stats) {
                    if ($stats['team_id'] === $teamId && $stats['minutes'] > 0) { // Check if player has more than 0 minutes
                        // Collect the top playmakers (5-7 based on passing rating)
                        if ($playmakerIndex < 7) {
                            $playmakers[] = &$stats; // Add the player to the playmaker list
                        }
                        $playmakerIndex++;
                    }
                }

                // Sort the players by passing rating in descending order
                usort($playmakers, function ($a, $b) {
                    return $b['passing_rating'] <=> $a['passing_rating'];
                });

                // Randomly distribute the assistRange among the top 5 to 7 players
                $assistCount = count($playmakers);
                if ($assistCount > 0) {
                    foreach ($playmakers as &$playmaker) {
                        // Randomly assign assists to each playmaker in the range of 0 to remaining assists
                        $maxForThisPlayer = min($remainingAssists, rand(0, floor($remainingAssists / 2)));
                        $playmaker['assists'] = $maxForThisPlayer;  // Assign assists

                        // Deduct from remaining assists
                        $remainingAssists -= $maxForThisPlayer;

                        // If there are no more assists to distribute, break early
                        if ($remainingAssists <= 0) {
                            break;
                        }
                    }
                }

                // Any remaining assists to be distributed among the rest of the players
                $remainingAssistsToDistribute = $maxAssists - $assistRange - $remainingAssists;
                foreach ($playerGameStats as &$stats) {
                    if ($stats['team_id'] === $teamId && !in_array($stats, $playmakers) && $stats['minutes'] > 0) { // Ensure player has minutes > 0
                        // Assign remaining assists to players who are not in the top playmaker group and have played minutes
                        $stats['assists'] = rand(0, floor($remainingAssistsToDistribute / 2));
                    }
                }

                // Update the assists assigned counter
                $assistsAssigned = $maxAssists - $remainingAssists;
            }

            // Distribute assists for the home team
            distributeAssists($playerGameStats, $gameData->home_team_id, $maxHomeAssists, $homeAssistsAssigned);

            // Distribute assists for the away team
            distributeAssists($playerGameStats, $gameData->away_team_id, $maxAwayAssists, $awayAssistsAssigned);

            // Clear reference
            // unset($stats);

            // Update or insert player game stats
            foreach ($playerGameStats as $stats) {
                // Log the player game stats

                try {

                    // Assuming you have a Player model
                    Player::where('id', $stats['player_id'])->update(['fatigue' => 0]);



                    PlayerGameStats::updateOrCreate(
                        [
                            'player_id' => $stats['player_id'],
                            'game_id' => $stats['game_id'],
                            'season_id' => $stats['season_id'],
                            'team_id' => $stats['team_id'],
                        ],
                        $stats
                    );

                    $storeStats->storeplayerseasonstats($stats['team_id'], $stats['player_id']);
                } catch (\Exception $e) {
                    \Log::error('Error saving player game stats:', [
                        'stats' => $stats,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Calculate scores based on player stats
            $homeScore = PlayerGameStats::where('team_id', $gameData->home_team_id)
                ->where('game_id', $gameData->game_id)
                ->sum('points');

            $awayScore = PlayerGameStats::where('team_id', $gameData->away_team_id)
                ->where('game_id', $gameData->game_id)
                ->sum('points');

            // Update the scores
            while ($homeScore === $awayScore) {
                // Simulate an additional 6 minutes of play
                $additionalMinutes = 6;

                $homeMinutes = $this->distributeMinutes($homeTeamPlayers, $additionalMinutes);
                $awayMinutes = $this->distributeMinutes($awayTeamPlayers, $additionalMinutes);

                foreach ($homeTeamPlayers as $player) {
                    if (isset($homeMinutes[$player['id']])) {
                        // Simulate overtime performance
                        $overtimeMinutes = $additionalMinutes;
                        $points = round(($player['shooting_rating'] / 100) * rand(0, 10 * ($overtimeMinutes / 6)));
                        $assists = round(($player['passing_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                        $rebounds = round(($player['rebounding_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                        $steals = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));
                        $blocks = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));

                        // Retrieve player game stats for the home team
                        $playerGameStats = PlayerGameStats::where([
                            'player_id' => $player['id'],
                            'game_id' => $gameData->game_id,
                            'team_id' => $gameData->home_id,
                            'season_id' => $currentSeasonId,
                        ])->first();

                        // Update player game stats for overtime if exists
                        if ($playerGameStats) {
                            $playerGameStats->update([
                                'points' => DB::raw('points + ' . max(0, $points)),       // Ensure non-negative points
                                'assists' => DB::raw('assists + ' . max(0, $assists)),    // Ensure non-negative assists
                                'rebounds' => DB::raw('rebounds + ' . max(0, $rebounds)), // Ensure non-negative rebounds
                                'steals' => DB::raw('steals + ' . max(0, $steals)),       // Ensure non-negative steals
                                'blocks' => DB::raw('blocks + ' . max(0, $blocks)),       // Ensure non-negative blocks
                                'updated_at' => now(),
                            ]);

                            $storeStats->storeplayerseasonstats($gameData->home_id, $player['id']);
                        }


                        $homeScore += $points;
                    }
                }

                foreach ($awayTeamPlayers as $player) {
                    if (isset($awayMinutes[$player['id']])) {
                        // Simulate overtime performance
                        $overtimeMinutes = $additionalMinutes;
                        $points = round(($player['shooting_rating'] / 100) * rand(0, 10 * ($overtimeMinutes / 6)));
                        $assists = round(($player['passing_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                        $rebounds = round(($player['rebounding_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                        $steals = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));
                        $blocks = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));

                        // Retrieve player game stats for the away team
                        $playerGameStats = PlayerGameStats::where([
                            'player_id' => $player['id'],
                            'game_id' => $gameData->game_id,
                            'team_id' => $gameData->away_id,
                            'season_id' => $currentSeasonId,
                        ])->first();

                        // Update player game stats for overtime if exists
                        if ($playerGameStats) {
                            $playerGameStats->update([
                                'points' => DB::raw('points + ' . max(0, $points)),       // Ensure non-negative points
                                'assists' => DB::raw('assists + ' . max(0, $assists)),    // Ensure non-negative assists
                                'rebounds' => DB::raw('rebounds + ' . max(0, $rebounds)), // Ensure non-negative rebounds
                                'steals' => DB::raw('steals + ' . max(0, $steals)),       // Ensure non-negative steals
                                'blocks' => DB::raw('blocks + ' . max(0, $blocks)),       // Ensure non-negative blocks
                                'updated_at' => now(),
                            ]);

                            $storeStats->storeplayerseasonstats($gameData->away_id, $player['id']);
                        }


                        $awayScore += $points;
                    }
                }
            }

            $gameData->home_score = $homeScore;
            $gameData->away_score = $awayScore;
            $gameData->status = 2;

            // Save the updated scores
            $gameData->save();


            // Check if all rounds have been simulated for the season
            $allRoundsSimulatedForSeason = Schedules::where('season_id', $currentSeasonId)
                ->where('status', 1)
                ->doesntExist();

            $this->updateAllTeamStreaks();
            $this->updateHeadToHeadResults($gameData->id);
            if ($allRoundsSimulatedForSeason) {
                // Update the season's status to 2


                $season = Seasons::find($currentSeasonId);
                if ($season) {
                    $season->status = 2;
                    $season->save();
                }
            }

            // Commit the transaction
            DB::commit();

            // $gameResult = $this->getBoxScore($gameData->game_id);

            // Return the simulation result
            return response()->json([
                'message' => 'Game simulated successfully',
                'game_id' => $gameData->game_id,
                // 'data' => $gameResult,
                // 'playerGameStats' => $playerGameStats,
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction if anything fails
            DB::rollBack();

            // Return error response
            return response()->json([
                'message' => 'An error occurred during simulation: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function simulateperround(Request $request)
    {
        // Validate the request data
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'conference_id' => 'required|exists:conferences,id',
            'round' => 'required|integer|min:0', // Validate round number
        ]);

        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;
        $round = $request->round;

        // Check if the round has already been simulated
        $alreadySimulated = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('round', $round)
            ->where('status', 2) // Status 2 indicates completed simulation
            ->exists();

        if ($alreadySimulated) {
            return response()->json([
                'error' => 'This round ' . ($round + 1) . ' has already been simulated.',
            ], 400);
        }

        // Retrieve schedules for the given season, conference, and round
        $schedules = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('round', $round)
            ->where('status', 1) // Only simulate games that haven't been completed
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'error' => 'No schedules found for the given season, conference, and round.',
            ], 404);
        }

        // Define role-based priority and maximum points
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        $roleMaxStats = $this->maxPoints();
        $totalMinutes = 240;

        DB::beginTransaction();

        try {
            foreach ($schedules as $schedule) {
                $homePlayers = Player::where('team_id', $schedule->home_id)->get()->toArray();
                $awayPlayers = Player::where('team_id', $schedule->away_id)->get()->toArray();

                $homePlayers = collect($homePlayers)->sortBy(function ($player) use ($rolePriority) {
                    return $rolePriority[$player['role']] ?? 5;
                })->values();

                $awayPlayers = collect($awayPlayers)->sortBy(function ($player) use ($rolePriority) {
                    return $rolePriority[$player['role']] ?? 5;
                })->values();

                $homeMinutes = $this->distributeMinutes($homePlayers, $totalMinutes);
                $awayMinutes = $this->distributeMinutes($awayPlayers, $totalMinutes);

                $homeScore = 0;
                $awayScore = 0;

                // Simulate home team stats
                foreach ($homePlayers as $player) {
                    $minutes = $homeMinutes[$player['id']] ?? 0;

                    // Track and update fatigue
                    $fatigueIncrease = round($minutes * 0.5);
                    $player['fatigue'] += $fatigueIncrease;
                    $player['fatigue'] = min(100, $player['fatigue']);

                    // Adjust performance factor based on fatigue
                    $fatigueFactor = 1 - ($player['fatigue'] / 100);
                    $performanceFactor = rand(80, 120) / 100 * $fatigueFactor;

                    // Check for injuries
                    $injuryRisk = rand(0, 100);
                    $injuryChance = ($player['fatigue'] * 0.5) + ($player['injury_history'] * 10);
                    if ($injuryRisk < $injuryChance) {
                        $player['is_injured'] = true;
                        $player['injury_type'] = ['minor', 'moderate', 'severe'][rand(0, 2)];
                    }

                    // Apply injury impact on performance
                    if ($player['is_injured']) {
                        switch ($player['injury_type']) {
                            case 'minor':
                                $performanceFactor *= 0.75;
                                break;
                            case 'moderate':
                                $performanceFactor *= 0.5;
                                break;
                            case 'severe':
                                $performanceFactor *= 0.2;
                                break;
                        }
                    }

                    $playerGameStats = PlayerGameStats::where([
                        ['player_id', $player['id']],
                        ['game_id', $schedule->game_id],
                        ['team_id', $schedule->home_id],
                        ['season_id', $seasonId]
                    ])->first();

                    if (($playerGameStats && $minutes === 0) || $player['is_injured']) {
                        $playerGameStats->update([
                            'points' => 0,
                            'assists' => 0,
                            'rebounds' => 0,
                            'steals' => 0,
                            'blocks' => 0,
                            'minutes' => 0,
                            'updated_at' => now(),
                        ]);
                    } elseif ($playerGameStats) {
                        $performanceFactor = rand(80, 120) / 100;
                        $pointsPerMinute = 0.5 + ($player['shooting_rating'] / 200);
                        $points = round($pointsPerMinute * $minutes * $performanceFactor);
                        $points = rand(0, $points);

                        $assistPerMinute = 0.1 + ($player['passing_rating'] / 200);
                        $assists = round($assistPerMinute * $minutes * $performanceFactor);
                        $assists = rand(0, $assists);

                        $reboundPerMinute = 0.1 + ($player['rebounding_rating'] / 200);
                        $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);
                        $rebounds = rand(0, $rebounds);

                        $awayTeamDefensiveStats = [
                            'defensive_rating' => Player::where('team_id', $schedule->away_id)
                                ->where('is_active', 1) // Optional: If you have an active player flag
                                ->avg('defensive_rating') ?? 0,
                            'rebounding_rating' => Player::where('team_id', $schedule->away_id)
                                ->where('is_active', 1) // Optional: If you have an active player flag
                                ->avg('rebounding_rating') ?? 0,
                        ];

                        $defensiveImpact = ($awayTeamDefensiveStats['defensive_rating'] + $awayTeamDefensiveStats['rebounding_rating']) / 20; // Adjust scaling factor as needed
                        $points -= round($defensiveImpact * $minutes * 0.1);
                        $points = max($points, 0);

                        $steals = round($minutes * (0.05 - $defensiveImpact / 100));
                        $blocks = round($minutes * (0.03 - $defensiveImpact / 100));

                        $turnovers = round(rand(0, 2));
                        $fouls = round(rand(0, 4));

                        $playerGameStats->update([
                            'points' => max(0, $points),       // Ensure non-negative points
                            'assists' => max(0, $assists),     // Ensure non-negative assists
                            'rebounds' => max(0, $rebounds),   // Ensure non-negative rebounds
                            'steals' => max(0, $steals),       // Ensure non-negative steals
                            'blocks' => max(0, $blocks),       // Ensure non-negative blocks
                            'turnovers' => max(0, $turnovers), // Ensure non-negative turnovers
                            'fouls' => max(0, $fouls),         // Ensure non-negative fouls
                            'minutes' => max(0, $minutes),     // Ensure non-negative minutes
                            'updated_at' => now(),
                        ]);


                        $homeScore += $points;
                    }
                }

                // Similar update logic for away team players
                foreach ($awayPlayers as $player) {
                    $minutes = $awayMinutes[$player['id']] ?? 0;

                    // Track and update fatigue
                    $fatigueIncrease = round($minutes * 0.5);
                    $player['fatigue'] += $fatigueIncrease;
                    $player['fatigue'] = min(100, $player['fatigue']);

                    // Adjust performance factor based on fatigue
                    $fatigueFactor = 1 - ($player['fatigue'] / 100);
                    $performanceFactor = rand(80, 120) / 100 * $fatigueFactor;

                    // Check for injuries
                    $injuryRisk = rand(0, 100);
                    $injuryChance = ($player['fatigue'] * 0.5) + ($player['injury_history'] * 10);
                    if ($injuryRisk < $injuryChance) {
                        $player['is_injured'] = true;
                        $player['injury_type'] = ['minor', 'moderate', 'severe'][rand(0, 2)];
                    }

                    // Apply injury impact on performance
                    if ($player['is_injured']) {
                        switch ($player['injury_type']) {
                            case 'minor':
                                $performanceFactor *= 0.75;
                                break;
                            case 'moderate':
                                $performanceFactor *= 0.5;
                                break;
                            case 'severe':
                                $performanceFactor *= 0.2;
                                break;
                        }
                    }

                    $playerGameStats = PlayerGameStats::where([
                        ['player_id', $player['id']],
                        ['game_id', $schedule->game_id],
                        ['team_id', $schedule->away_id],
                        ['season_id', $seasonId]
                    ])->first();

                    if (($playerGameStats && $minutes === 0) || $player['is_injured']) {
                        $playerGameStats->update([
                            'points' => 0,
                            'assists' => 0,
                            'rebounds' => 0,
                            'steals' => 0,
                            'blocks' => 0,
                            'minutes' => 0,
                            'updated_at' => now(),
                        ]);
                    } elseif ($playerGameStats) {
                        // Similar logic for performance stats
                        $performanceFactor = rand(80, 120) / 100;
                        $pointsPerMinute = 0.5 + ($player['shooting_rating'] / 200);
                        $points = round($pointsPerMinute * $minutes * $performanceFactor);
                        $points = rand(0, $points);

                        $assistPerMinute = 0.1 + ($player['passing_rating'] / 200);
                        $assists = round($assistPerMinute * $minutes * $performanceFactor);
                        $assists = rand(0, $assists);

                        $reboundPerMinute = 0.1 + ($player['rebounding_rating'] / 200);
                        $rebounds = round($reboundPerMinute * $minutes * $performanceFactor);
                        $rebounds = rand(0, $rebounds);

                        $totalGames = Schedules::where('season_id', $seasonId)
                            ->where('status', 2)
                            ->where('home_id', $schedule->home_id)
                            ->count();

                        $homeTeamDefensiveStats = [
                            'defensive_rating' => Player::where('team_id', $schedule->home_id)
                                ->where('is_active', 1) // Optional: If you have an active player flag
                                ->avg('defensive_rating') ?? 0,
                            'rebounding_rating' => Player::where('team_id', $schedule->home_id)
                                ->where('is_active', 1) // Optional: If you have an active player flag
                                ->avg('rebounding_rating') ?? 0,
                        ];

                        $defensiveImpact = ($homeTeamDefensiveStats['defensive_rating'] + $homeTeamDefensiveStats['rebounding_rating']) / 20; // Adjust scaling factor as needed
                        $points -= round($defensiveImpact * $minutes * 0.1);
                        $points = max($points, 0);

                        $steals = round($minutes * (0.05 - $defensiveImpact / 100));
                        $blocks = round($minutes * (0.03 - $defensiveImpact / 100));

                        $turnovers = round(rand(0, 2));
                        $fouls = round(rand(0, 4));

                        $playerGameStats->update([
                            'points' => max(0, $points),       // Ensure non-negative points
                            'assists' => max(0, $assists),     // Ensure non-negative assists
                            'rebounds' => max(0, $rebounds),   // Ensure non-negative rebounds
                            'steals' => max(0, $steals),       // Ensure non-negative steals
                            'blocks' => max(0, $blocks),       // Ensure non-negative blocks
                            'turnovers' => max(0, $turnovers), // Ensure non-negative turnovers
                            'fouls' => max(0, $fouls),         // Ensure non-negative fouls
                            'minutes' => max(0, $minutes),     // Ensure non-negative minutes
                            'updated_at' => now(),
                        ]);


                        $awayScore += $points;
                    }
                }

                //overtime
                while ($homeScore === $awayScore) {
                    // Simulate an additional 6 minutes of play
                    $additionalMinutes = 6;

                    $homeMinutes = $this->distributeMinutes($homePlayers, $additionalMinutes);
                    $awayMinutes = $this->distributeMinutes($awayPlayers, $additionalMinutes);

                    foreach ($homePlayers as $player) {
                        if (isset($homeMinutes[$player['id']])) {
                            // Simulate overtime performance
                            $overtimeMinutes = $additionalMinutes;
                            $points = round(($player['shooting_rating'] / 100) * rand(0, 10 * ($overtimeMinutes / 6)));
                            $assists = round(($player['passing_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                            $rebounds = round(($player['rebounding_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                            $steals = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));
                            $blocks = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));

                            // Retrieve player game stats for the home team
                            $playerGameStats = PlayerGameStats::where([
                                'player_id' => $player['id'],
                                'game_id' => $schedule->game_id,
                                'team_id' => $schedule->home_id,
                                'season_id' => $seasonId
                            ])->first();

                            // Update player game stats for overtime if exists
                            if ($playerGameStats) {
                                $playerGameStats->update([
                                    'points' => DB::raw('points + ' . max(0, $points)),       // Ensure non-negative points
                                    'assists' => DB::raw('assists + ' . max(0, $assists)),    // Ensure non-negative assists
                                    'rebounds' => DB::raw('rebounds + ' . max(0, $rebounds)), // Ensure non-negative rebounds
                                    'steals' => DB::raw('steals + ' . max(0, $steals)),       // Ensure non-negative steals
                                    'blocks' => DB::raw('blocks + ' . max(0, $blocks)),       // Ensure non-negative blocks
                                    'updated_at' => now(),
                                ]);
                            }


                            $homeScore += $points;
                        }
                    }

                    foreach ($awayPlayers as $player) {
                        if (isset($awayMinutes[$player['id']])) {
                            // Simulate overtime performance
                            $overtimeMinutes = $additionalMinutes;
                            $points = round(($player['shooting_rating'] / 100) * rand(0, 10 * ($overtimeMinutes / 6)));
                            $assists = round(($player['passing_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                            $rebounds = round(($player['rebounding_rating'] / 100) * rand(0, 3 * ($overtimeMinutes / 6)));
                            $steals = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));
                            $blocks = round(($player['defense_rating'] / 100) * rand(0, 2 * ($overtimeMinutes / 6)));

                            // Retrieve player game stats for the away team
                            $playerGameStats = PlayerGameStats::where([
                                'player_id' => $player['id'],
                                'game_id' => $schedule->game_id,
                                'team_id' => $schedule->away_id,
                                'season_id' => $seasonId
                            ])->first();

                            // Update player game stats for overtime if exists
                            if ($playerGameStats) {
                                $playerGameStats->update([
                                    'points' => DB::raw('points + ' . max(0, $points)),       // Ensure non-negative points
                                    'assists' => DB::raw('assists + ' . max(0, $assists)),    // Ensure non-negative assists
                                    'rebounds' => DB::raw('rebounds + ' . max(0, $rebounds)), // Ensure non-negative rebounds
                                    'steals' => DB::raw('steals + ' . max(0, $steals)),       // Ensure non-negative steals
                                    'blocks' => DB::raw('blocks + ' . max(0, $blocks)),       // Ensure non-negative blocks
                                    'updated_at' => now(),
                                ]);
                            }


                            $awayScore += $points;
                        }
                    }
                }

                // Update the schedule with the simulated game scores
                $schedule->update([
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => 2, // Mark as simulated
                    'updated_at' => now(),
                ]);
            }
            // Check if all rounds have been simulated for the season
            $allRoundsSimulatedForSeason = Schedules::where('season_id', $seasonId)
                ->where('status', 1)
                ->doesntExist();

            if ($allRoundsSimulatedForSeason) {
                // Update the season's status to 2
                $season = Seasons::find($seasonId);
                if ($season) {
                    $season->status = 2; // Example status for completed season
                    $season->save();
                }
            }
            DB::commit();

            return response()->json([
                'message' => 'Round ' . ($round + 1) . ' has been successfully simulated.',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Simulation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getscheduleids(Request $request)
    {
        // Validate the request data
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'conference_id' => 'required|exists:conferences,id',
        ]);

        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;

        // Retrieve schedule IDs for the given season and conference
        $scheduleIds = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('status', 1)
            ->orderBy('id')
            ->pluck('id')
            ->toArray(); // Get the IDs as an array

        return response()->json([
            'schedule_ids' => $scheduleIds,
        ]);
    }

    private function distributeMinutesV1($playersArray, $totalMinutes)
    {
        // Define role-based priorities and their minute allocation limits
        $rolePriority = [
            'star player' => 1,   // Highest priority
            'starter' => 2,       // Second highest priority
            'role player' => 3,   // Lower priority
            'bench' => 4,         // Lowest priority
        ];

        // Convert Eloquent collection to array
        // $playersArray = $players->toArray();

        // Sort players based on their role priority (higher priority first)
        $sortedPlayers = collect($playersArray)->sortBy(function ($player) use ($rolePriority) {
            return $rolePriority[$player['role']] ?? 5; // Default to lowest priority if role not found
        })->values();

        $minutes = [];
        $assignedMinutes = 0;

        // Allocate minutes based on priority roles
        foreach ($sortedPlayers as $player) {
            if (rand(1, 100) >= $player['injury_prone_percentage']) {
                // Player is injured and should get zero minutes
                $minutes[$player['id']] = 0;
            } else {
                // Define initial minute ranges based on role priority
                switch ($rolePriority[$player['role']] ?? 5) {
                    case 1: // Star player
                        $assignedMinutesForRole = rand(30, 35); // Star players get the most minutes
                        break;
                    case 2: // Starter
                        $assignedMinutesForRole = rand(25, 30); // Starters get slightly fewer minutes
                        break;
                    case 3: // Role player
                        $assignedMinutesForRole = rand(15, 20); // Role players get fewer minutes
                        break;
                    case 4: // Bench
                        $assignedMinutesForRole = rand(5, 10);  // Bench players get the least minutes
                        break;
                    default:
                        $assignedMinutesForRole = 0;
                        break;
                }

                $minutes[$player['id']] = $assignedMinutesForRole;
                $assignedMinutes += $assignedMinutesForRole;
            }
        }

        // Calculate remaining minutes to reach the target
        $remainingMinutes = $totalMinutes - $assignedMinutes;

        // Get players who were not assigned any minutes (injured or otherwise)
        $availablePlayers = array_filter($sortedPlayers->toArray(), function ($player) use ($minutes) {
            return !isset($minutes[$player['id']]) || $minutes[$player['id']] === 0;
        });

        // Count the number of available players
        $numAvailablePlayers = count($availablePlayers);

        // Distribute remaining minutes based on role priority (higher priority roles get more of the remaining minutes)
        if ($numAvailablePlayers > 0) {
            // First, calculate how much "weight" each player should get based on role priority
            $totalWeight = array_sum(array_map(function ($player) use ($rolePriority) {
                return 1 / $rolePriority[$player['role']] ?? 5;
            }, $availablePlayers));

            // Now, distribute the remaining minutes according to weight
            foreach ($availablePlayers as $player) {
                $playerWeight = 1 / ($rolePriority[$player['role']] ?? 5);  // Lower priority roles get more weight
                $allocatedMinutes = ($playerWeight / $totalWeight) * $remainingMinutes;  // Proportional allocation
                $minutes[$player['id']] += $allocatedMinutes;
            }
        }

        // Ensure total minutes match the target (adjust if necessary)
        $totalAssignedMinutes = array_sum($minutes);

        if ($totalAssignedMinutes !== $totalMinutes) {
            // If there is a discrepancy, adjust the last few players' minutes (either add or subtract)
            $difference = $totalMinutes - $totalAssignedMinutes;
            foreach ($minutes as $id => &$minute) {
                // Add or subtract the difference proportionally
                $minute += ($difference / count($minutes)); // Simple proportional adjustment
            }
        }

        // Track and update fatigue
        $fatigueIncrease = round($minutes[$player->id] * 0.5);
        $player->fatigue += $fatigueIncrease;
        $player->fatigue = min(100,  $player->fatigue);

        // Adjust performance factor based on fatigue
        $fatigueFactor = 1 - ($player->fatigue / 100);
        $performanceFactor = rand(80, 120) / 100 * $fatigueFactor;

        // Check for injuries
        $injuryRisk = rand(0, 100);
        $injuryChance = ($player->fatigue * 0.5) + ($player->injury_history * 10);
        if ($injuryRisk < $injuryChance) {
            $player->is_injured = true;
            $player->injury_type = ['minor', 'moderate', 'severe'][rand(0, 2)];

            // Increment injury history
            $player->injury_history += 1;
        }

        // Apply injury impact on performance
        if ($player->is_injured) {
            switch ($player->injury_type) {
                case 'minor':
                    $performanceFactor *= 0.75;
                    break;
                case 'moderate':
                    $performanceFactor *= 0.5;
                    break;
                case 'severe':
                    $performanceFactor *= 0.2;
                    break;
            }
        }

        $player->save();

        return $minutes;
    }

    private function distributeMinutes($playersArray, $totalMinutes)
    {
        // Define role-based priorities and their minute allocation limits
        $rolePriority = [
            'star player' => 1,   // Highest priority
            'starter' => 2,       // Second highest priority
            'role player' => 3,   // Lower priority
            'bench' => 4,         // Lowest priority
        ];

        // Convert Eloquent collection to array
        // $playersArray = $players->toArray();

        // Sort players based on their role priority (higher priority first)
        $sortedPlayers = collect($playersArray)->sortBy(function ($player) use ($rolePriority) {
            return $rolePriority[$player['role']] ?? 5; // Default to lowest priority if role not found
        })->values();

        $minutes = [];
        $assignedMinutes = 0;

        // Allocate minutes based on priority roles
        foreach ($sortedPlayers as $player) {
            if (rand(1, 100) >= $player['injury_prone_percentage']) {
                // Player is injured and should get zero minutes
                $minutes[$player['id']] = 0;
            } else {
                // Define initial minute ranges based on role priority
                switch ($rolePriority[$player['role']] ?? 5) {
                    case 1: // Star player
                        $assignedMinutesForRole = rand(30, 35); // Star players get the most minutes
                        break;
                    case 2: // Starter
                        $assignedMinutesForRole = rand(25, 30); // Starters get slightly fewer minutes
                        break;
                    case 3: // Role player
                        $assignedMinutesForRole = rand(15, 20); // Role players get fewer minutes
                        break;
                    case 4: // Bench
                        $assignedMinutesForRole = rand(5, 10);  // Bench players get the least minutes
                        break;
                    default:
                        $assignedMinutesForRole = 0;
                        break;
                }

                $minutes[$player['id']] = $assignedMinutesForRole;
                $assignedMinutes += $assignedMinutesForRole;
            }

            // Track and update fatigue for each player
            $this->fatigueRate($player,$minutes[$player['id']]);
        }

        // Calculate remaining minutes to reach the target
        $remainingMinutes = $totalMinutes - $assignedMinutes;

        // Get players who were not assigned any minutes (injured or otherwise)
        $availablePlayers = array_filter($sortedPlayers->toArray(), function ($player) use ($minutes) {
            return !isset($minutes[$player['id']]) || $minutes[$player['id']] === 0;
        });

        // Count the number of available players
        $numAvailablePlayers = count($availablePlayers);

        // Distribute remaining minutes based on role priority (higher priority roles get more of the remaining minutes)
        if ($numAvailablePlayers > 0) {
            // First, calculate how much "weight" each player should get based on role priority
            $totalWeight = array_sum(array_map(function ($player) use ($rolePriority) {
                return 1 / $rolePriority[$player['role']] ?? 5;
            }, $availablePlayers));

            // Now, distribute the remaining minutes according to weight
            foreach ($availablePlayers as $player) {
                $playerWeight = 1 / ($rolePriority[$player['role']] ?? 5);  // Lower priority roles get more weight
                $allocatedMinutes = ($playerWeight / $totalWeight) * $remainingMinutes;  // Proportional allocation
                $minutes[$player['id']] += $allocatedMinutes;
            }
        }

        // Ensure total minutes match the target (adjust if necessary)
        $totalAssignedMinutes = array_sum($minutes);

        if ($totalAssignedMinutes !== $totalMinutes) {
            // If there is a discrepancy, adjust the last few players' minutes (either add or subtract)
            $difference = $totalMinutes - $totalAssignedMinutes;
            foreach ($minutes as $id => &$minute) {
                // Add or subtract the difference proportionally
                $minute += ($difference / count($minutes)); // Simple proportional adjustment
            }
        }

        return $minutes;
    }
    private function fatigueRate($player, $minutes)
    {
        try {
            // Fetch the most recent season id
            $seasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id') ?? 1;

            // Calculate fatigue increase based on minutes played
            $fatigueIncrease = round($minutes * 0.5);
            $player->fatigue += $fatigueIncrease;
            $player->fatigue = min(100, $player->fatigue); // Ensure fatigue does not exceed 100%

            // Adjust performance factor based on fatigue
            $fatigueFactor = 1 - ($player->fatigue / 100);
            $performanceFactor = rand(80, 120) / 100 * $fatigueFactor;

            // Check for injuries if the player is not already injured
            if (!$player->is_injured) {
                // Cast injury_prone_percentage to a float for accurate comparison
                $injuryPercentage = (float) $player->injury_prone_percentage;

                // Generate a random number between 0 and $injuryPercentage
                $injuryRisk = rand(0, 100) / 100 * $injuryPercentage;

                // Calculate injuryChance based on fatigue and injury history
                $injuryChance = ($player->fatigue * 0.5) + ($player->injury_history * 10);

                // Check if injury risk is less than the injury chance
                if ($injuryRisk < $injuryChance) {
                    // Fetch all injury types from the config
                    $injuryTypes = config('injuries');

                    // Ensure the injuryTypes config is not empty
                    if (is_array($injuryTypes) && count($injuryTypes) > 0) {
                        // Randomly select an injury type from the config
                        $injuryTypeName = array_rand($injuryTypes);

                        // Mark the player as injured and set the injury details
                        $player->is_injured = true;
                        $player->injury_type = $injuryTypeName; // Save the injury type name
                        $player->injury_history += 1; // Increment injury history

                        // Set recovery games based on injury type from the config
                        $player->injury_recovery_games = $injuryTypes[$injuryTypeName]['recovery_games'];

                        // Insert the injury record into the database using DB::table()
                        DB::table('injury_histories')->insert([
                            'player_id' => $player->id,
                            'team_id' => $player->team_id,
                            'season_id' => $seasonId,
                            'injury_type' => $injuryTypeName,
                            'recovery_games' => $injuryTypes[$injuryTypeName]['recovery_games'],
                            'performance_impact' => $injuryTypes[$injuryTypeName]['performance_impact'],
                            'injury_date' => now(), // Use Carbon's now() for consistent timestamp
                            'recovery_date' => null, // Recovery date will be null until the player recovers
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        // Log error or handle case where injury types are not defined
                        Log::error("Injury types are not configured correctly.");
                    }
                }
            }


            // Handle injury recovery logic based on the number of games
            if ($player->is_injured) {
                // Decrement recovery games as each game is played
                $player->injury_recovery_games -= 1; // Decrease recovery games
            }

            // Check if the player has played enough games to recover
            if ($player->injury_recovery_games <= 0) {
                // Player is healed
                $player->is_injured = false; // Mark player as recovered
                $player->injury_type = 'none'; // Clear injury type
                $player->injury_recovery_games = 0; // Reset the recovery game counter

                // Update the injury record to set the recovery date in the injury history table
                $lastInjury = DB::table('injury_histories')
                    ->where('player_id', $player->id)
                    ->whereNull('recovery_date') // Only update the most recent injury without recovery date
                    ->latest()
                    ->first();

                if ($lastInjury) {
                    DB::table('injury_histories')
                        ->where('id', $lastInjury->id)
                        ->update([
                            'recovery_date' => now(), // Set the recovery date
                            'updated_at' => now(), // Update the timestamp
                        ]);
                }
            }

            // Apply injury impact on performance
            if ($player->is_injured) {
                $injuryType = config('injuries')[$player->injury_type];

                if ($injuryType) {
                    $performanceFactor *= $injuryType['performance_impact'];
                }
            }

            // Save player after applying updates
            $player->save();

            return response()->json([
                'message' => 'Fatigue and injury update successful',
                'player' => $player,
            ], 200); // Successful response
        } catch (\Exception $e) {
            // Log the error message for debugging
            \Log::error('Error updating fatigue and injury for player ' . $player->id . ': ' . $e->getMessage());

            // Return a structured error response
            return response()->json([
                'error' => 'Error updating fatigue and injury data: ' . $e->getMessage()
            ], 500); // Internal server error
        }
    }

    // Method to handle semi-finals logic
    private function updateConferenceChampions($gameData, $winnerId)
    {
        // Determine the conference based on home or away conference name
        $conferenceName = $gameData->home_conference_name;

        // Define the columns to update
        $columnsToUpdate = [];

        // Determine the winner's team name based on the winner ID
        $winnerName = null;
        if ($gameData->home_team_id === $winnerId) {
            $winnerName = $gameData->home_team_name;
        } elseif ($gameData->away_team_id === $winnerId) {
            $winnerName = $gameData->away_team_name;
        }

        // Check the conference and set the champion ID and name columns
        switch ($conferenceName) {
            case 'East':
                $columnsToUpdate = [
                    'east_champion_id' => $winnerId,
                    'east_champion_name' => $winnerName,
                ];
                break;
            case 'West':
                $columnsToUpdate = [
                    'west_champion_id' => $winnerId,
                    'west_champion_name' => $winnerName,
                ];
                break;
            case 'North':
                $columnsToUpdate = [
                    'north_champion_id' => $winnerId,
                    'north_champion_name' => $winnerName,
                ];
                break;
            case 'South':
                $columnsToUpdate = [
                    'south_champion_id' => $winnerId,
                    'south_champion_name' => $winnerName,
                ];
                break;
        }

        // Update the seasons table with the determined columns
        DB::table('seasons')
            ->where('id', $gameData->season_id)
            ->update($columnsToUpdate);
    }

    // Method to handle finals logic
    private function updateFinalsWinner($gameData, $winnerId, $homeScore, $awayScore)
    {
        // Find the best statistical player (MVP) from the winning team
        $mvpPlayer = PlayerGameStats::join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->where('player_game_stats.team_id', $winnerId)
            ->where('player_game_stats.game_id', $gameData->game_id)
            // Calculate a weighted performance metric for the MVP
            ->select(
                'player_game_stats.*',
                'players.name as mvp_name', // Include the player's name
                DB::raw('(
        player_game_stats.points * 1.0 +
        player_game_stats.rebounds * 1.2 +
        player_game_stats.assists * 1.5 +
        player_game_stats.steals * 2.0 +
        player_game_stats.blocks * 2.0 -
        player_game_stats.turnovers * 1.5
    ) as mvp_score')
            )
            ->orderByDesc('mvp_score') // Order by the calculated performance score
            ->first();

        // If an MVP player is found, set the player's name and id
        $finalsMVP = $mvpPlayer ? $mvpPlayer->mvp_name : ''; // Use the player's name from the 'mvp_name' alias
        $finalsMVPId = $mvpPlayer ? $mvpPlayer->player_id : '';


        // Update the season's finals information
        DB::table('seasons')
            ->where('id', $gameData->season_id)
            ->update([
                'finals_winner_id' => $winnerId,
                'finals_loser_id' => $winnerId === $gameData->home_team_id ? $gameData->away_team_id : $gameData->home_team_id,
                'finals_winner_name' => $winnerId === $gameData->home_team_id ? $gameData->home_team_name : $gameData->away_team_name,
                'finals_loser_name' => $winnerId === $gameData->home_team_id ? $gameData->away_team_name : $gameData->home_team_name,
                'finals_winner_score' => $winnerId === $gameData->home_team_id ? $homeScore : $awayScore,
                'finals_loser_score' => $winnerId === $gameData->home_team_id ? $awayScore : $homeScore,
                'finals_mvp' => $finalsMVP,
                'finals_mvp_id' => $finalsMVPId,
            ]);
    }


    private function updateAllTeamStreaks()
    {
        // Fetch all games from the earliest to the latest
        $games = \DB::table('schedule_view')
            ->where('status', 2)
            ->orderBy('id', 'asc') // Order by id to process games chronologically
            ->get();

        if ($games->isEmpty()) {
            return; // No games to process
        }

        // Initialize an array to store streak information for each team
        $teamStreaks = [];

        // Iterate over each game to calculate streaks
        foreach ($games as $game) {
            // Home team processing
            $this->processGameStreak($teamStreaks, $game->home_id, $game->home_score, $game->away_score, $game->id);

            // Away team processing
            $this->processGameStreak($teamStreaks, $game->away_id, $game->away_score, $game->home_score, $game->id);
        }

        // Update the streak table for each team
        foreach ($teamStreaks as $teamId => $streak) {
            // Fetch the existing streak record for the team
            $streakRecord = \DB::table('streak')->where('team_id', $teamId)->first();

            if ($streakRecord) {
                // Update the best streak if the current one is greater
                if ($streak['best_winning_streak'] > $streakRecord->best_winning_streak) {
                    \DB::table('streak')->where('team_id', $teamId)->update([
                        'best_winning_streak' => $streak['best_winning_streak'],
                        'best_winning_streak_start_id' => $streak['best_winning_streak_start_id'],
                        'best_winning_streak_end_id' => $streak['best_winning_streak_end_id'],
                    ]);
                }
                if ($streak['best_losing_streak'] > $streakRecord->best_losing_streak) {
                    \DB::table('streak')->where('team_id', $teamId)->update([
                        'best_losing_streak' => $streak['best_losing_streak'],
                        'best_losing_streak_start_id' => $streak['best_losing_streak_start_id'],
                        'best_losing_streak_end_id' => $streak['best_losing_streak_end_id'],
                    ]);
                }
            } else {
                // Insert a new record if none exists for the team
                \DB::table('streak')->insert([
                    'team_id' => $teamId,
                    'best_winning_streak' => $streak['best_winning_streak'],
                    'best_losing_streak' => $streak['best_losing_streak'],
                    'best_winning_streak_start_id' => $streak['best_winning_streak_start_id'],
                    'best_winning_streak_end_id' => $streak['best_winning_streak_end_id'],
                    'best_losing_streak_start_id' => $streak['best_losing_streak_start_id'],
                    'best_losing_streak_end_id' => $streak['best_losing_streak_end_id'],
                ]);
            }
        }
    }

    // Modify processGameStreak to track start and end game IDs
    private function processGameStreak(&$teamStreaks, $teamId, $teamScore, $opponentScore, $gameId)
    {
        // Initialize streaks for the team if not already set
        if (!isset($teamStreaks[$teamId])) {
            $teamStreaks[$teamId] = [
                'current_streak' => 0,
                'is_winning_streak' => null,
                'best_winning_streak' => 0,
                'best_losing_streak' => 0,
                'best_winning_streak_start_id' => 0,
                'best_winning_streak_end_id' => 0,
                'best_losing_streak_start_id' => 0,
                'best_losing_streak_end_id' => 0,
            ];
        }

        $streak = &$teamStreaks[$teamId]; // Reference to the team's streak data

        // Determine if the game is a win or loss
        $isWin = $teamScore > $opponentScore;

        if ($isWin) {
            if ($streak['is_winning_streak'] === false) {
                // Streak direction changed from losing to winning
                $streak['current_streak'] = 1;
                $streak['best_winning_streak_start_id'] = $gameId; // Start of new winning streak
                $streak['best_losing_streak_start_id'] = 0; // Reset losing streak
                $streak['best_losing_streak_end_id'] = 0; // Reset losing streak
            } else {
                // Continue winning streak
                $streak['current_streak']++;
            }
            $streak['is_winning_streak'] = true;
            $streak['best_winning_streak'] = max($streak['best_winning_streak'], $streak['current_streak']);
            $streak['best_winning_streak_end_id'] = $gameId; // Update end of winning streak
        } else {
            if ($streak['is_winning_streak'] === true) {
                // Streak direction changed from winning to losing
                $streak['current_streak'] = 1;
                $streak['best_losing_streak_start_id'] = $gameId; // Start of new losing streak
                $streak['best_winning_streak_end_id'] = 0; // Reset winning streak
            } else {
                // Continue losing streak
                $streak['current_streak']++;
            }
            $streak['is_winning_streak'] = false;
            $streak['best_losing_streak'] = max($streak['best_losing_streak'], $streak['current_streak']);
            $streak['best_losing_streak_end_id'] = $gameId; // Update end of losing streak
        }
    }

    private function updateHeadToHeadResults($gameId)
    {
        // Fetch the game details from the schedules table
        $game = DB::table('schedules')
            ->where('id', $gameId)
            ->where('status', 2) // Ensure the game is completed
            ->first();

        if (!$game) {
            return response()->json([
                'error' => 'Game not found or not completed for game_id: ' . $gameId
            ], 404); // Game not found or not completed
        }

        // Determine the outcome of the game
        $teamWins = $game->home_score > $game->away_score ? 1 : 0;
        $opponentWins = $game->away_score > $game->home_score ? 1 : 0;
        $draws = $game->home_score == $game->away_score ? 1 : 0;

        // Update for the team's perspective (home vs away)
        $this->updateHeadToHeadMatchup($game->home_id, $game->away_id, $teamWins, $opponentWins, $draws);

        // Update for the opponent's perspective (away vs home)
        $this->updateHeadToHeadMatchup($game->away_id, $game->home_id, $opponentWins, $teamWins, $draws);

        return response()->json([
            'message' => 'Successfully updated head-to-head matchups for game_id: ' . $gameId
        ], 200); // Success
    }

    private function updateHeadToHeadMatchup($teamId, $opponentId, $teamWins, $opponentWins, $draws)
    {
        try {
            // Check if this matchup already exists in the head_to_head table
            $matchup = DB::table('head_to_head')
                ->where('team_id', $teamId)
                ->where('opponent_id', $opponentId)
                ->first();

            if ($matchup) {
                // If matchup exists, update the match count and win/loss records
                DB::table('head_to_head')
                    ->where('team_id', $teamId)
                    ->where('opponent_id', $opponentId)
                    ->update([
                        'wins' => $matchup->wins + $teamWins,
                        'losses' => $matchup->losses + $opponentWins,
                        'draws' => $matchup->draws + $draws,
                        'updated_at' => now(),
                    ]);
            } else {
                // If matchup does not exist, insert a new record
                DB::table('head_to_head')
                    ->insert([
                        'team_id' => $teamId,
                        'opponent_id' => $opponentId,
                        'wins' => $teamWins,
                        'losses' => $opponentWins,
                        'draws' => $draws,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Return true if successful
            return true;
        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error updating head-to-head matchup for team_id ' . $teamId . ' vs opponent_id ' . $opponentId . ': ' . $e->getMessage());

            // Return a structured error response
            return response()->json([
                'error' => 'Error updating head-to-head matchup: ' . $e->getMessage()
            ], 500); // Internal server error
        }
    }
}
