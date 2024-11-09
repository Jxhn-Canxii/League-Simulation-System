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

            // Calculate the total number of games played by the away team
            $totalGames = Schedules::where('season_id', $currentSeasonId)
                ->where('status', 2) // Assuming 2 represents completed games
                ->where(function ($query) use ($player) {
                    $query->where('away_id', $player->team_id)
                        ->orWhere('home_id', $player->team_id);
                })
                ->count();

            // Calculate average defensive stats per game for the away team
            $awayTeamDefensiveStats = [
                'blocks' => $gameData->away_team_id ? PlayerGameStats::where('team_id', $gameData->away_team_id)
                    ->where('season_id', $currentSeasonId)
                    ->selectRaw('SUM(blocks) / ?', [$totalGames])
                    ->value('SUM(blocks)') : 0,
                'steals' => $gameData->away_team_id ? PlayerGameStats::where('team_id', $gameData->away_team_id)
                    ->where('season_id', $currentSeasonId)
                    ->selectRaw('SUM(steals) / ?', [$totalGames])
                    ->value('SUM(steals)') : 0,
            ];

            // If minutes is 0, player did not play
            if ($minutes === 0) {
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
                $defensiveImpact = ($awayTeamDefensiveStats['blocks'] + $awayTeamDefensiveStats['steals']) / 20; // Scale factor
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

            // Calculate the total number of games played by the home team
            $totalGames = Schedules::where('season_id', $currentSeasonId)
                ->where('status', 2) // Assuming 2 represents completed games
                ->where(function ($query) use ($player) {
                    $query->where('away_id', $player->team_id)
                        ->orWhere('home_id', $player->team_id);
                })
                ->count();

            // Calculate average defensive stats per game for the home team
            $homeTeamDefensiveStats = [
                'blocks' => $gameData->home_team_id ? PlayerGameStats::where('team_id', $gameData->home_team_id)
                    ->where('season_id', $currentSeasonId)
                    ->selectRaw('SUM(blocks) / ?', [$totalGames])
                    ->value('SUM(blocks)') : 0,
                'steals' => $gameData->home_team_id ? PlayerGameStats::where('team_id', $gameData->home_team_id)
                    ->where('season_id', $currentSeasonId)
                    ->selectRaw('SUM(steals) / ?', [$totalGames])
                    ->value('SUM(steals)') : 0,
            ];

            // If minutes is 0, player did not play
            if ($minutes === 0) {
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
                $defensiveImpact = ($homeTeamDefensiveStats['blocks'] + $homeTeamDefensiveStats['steals']) / 20; // Scale factor
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
        function distributeAssistsPlayoffs(&$playerGameStats, $teamId, $maxAssists, &$assistsAssigned) {
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
            PlayerGameStats::updateOrCreate(
                [
                    'player_id' => $stats['player_id'],
                    'game_id' => $stats['game_id'],
                    'season_id' => $stats['season_id'],
                    'team_id' => $stats['team_id'],
                ],
                $stats
            );
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
        // Prepare the schedule response data
        $schedule = [
            'id' => $gameData->id,
            'game_id' => $gameData->game_id,
            'home_team' => [
                'id' => $gameData->home_team_id,
                'name' => $gameData->home_team_name,
                'score' => $gameData->home_score,
                'conference' => $gameData->home_conference_name,
                'conference_rank' => $gameData->home_conference_rank,
                'overall_rank' => $gameData->home_overall_rank,
            ],
            'away_team' => [
                'id' => $gameData->away_team_id,
                'name' => $gameData->away_team_name,
                'score' => $gameData->away_score,
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

                // Calculate the total number of games played by the away team
                $totalGames = Schedules::where('season_id', $currentSeasonId)
                    ->where('status', 2)
                    ->where(function ($query) use ($gameData) {
                        $query->where('away_id', $gameData->home_team_id)
                            ->orWhere('home_id', $gameData->home_team_id);
                    })
                    ->count();

                // Calculate average defensive stats per game for the away team

                $awayTeamDefensiveStats = [
                    'blocks' => $totalGames > 0 ? PlayerGameStats::where('team_id', $gameData->away_team_id)
                        ->where('season_id', $currentSeasonId)
                        ->sum('blocks') / $totalGames : 0,
                    'steals' => $totalGames > 0 ? PlayerGameStats::where('team_id', $gameData->away_team_id)
                        ->where('season_id', $currentSeasonId)
                        ->sum('steals') / $totalGames : 0,
                ];
                // If minutes is 0, player did not play
                if ($minutes === 0) {
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
                    $defensiveImpact = ($awayTeamDefensiveStats['blocks'] + $awayTeamDefensiveStats['steals']) / 20; // Scale factor
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
                $minutes = $awayMinutes[$player->id] ?? 0;

                // Calculate the total number of games played by the home team
                $totalGames = Schedules::where('season_id', $currentSeasonId)
                    ->where('status', 2)
                    ->where(function ($query) use ($gameData) {
                        $query->where('away_id', $gameData->home_team_id)
                            ->orWhere('home_id', $gameData->home_team_id);
                    })
                    ->count();

                // Calculate average defensive stats per game for the home team
                $homeTeamDefensiveStats = [
                    'blocks' => $totalGames > 0 ? PlayerGameStats::where('team_id', $gameData->home_team_id)
                        ->where('season_id', $currentSeasonId)
                        ->sum('blocks') / $totalGames : 0,
                    'steals' => $totalGames > 0 ? PlayerGameStats::where('team_id', $gameData->home_team_id)
                        ->where('season_id', $currentSeasonId)
                        ->sum('steals') / $totalGames : 0,
                ];

                // If minutes is 0, player did not play
                if ($minutes === 0) {
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
                    $defensiveImpact = ($homeTeamDefensiveStats['blocks'] + $homeTeamDefensiveStats['steals']) / 20; // Scale factor
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
            function distributeAssists(&$playerGameStats, $teamId, $maxAssists, &$assistsAssigned) {
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
                \Log::info('Saving player game stats:', $stats);

                try {
                    \Log::info('Saving player game stats:', $stats);

                    PlayerGameStats::updateOrCreate(
                        [
                            'player_id' => $stats['player_id'],
                            'game_id' => $stats['game_id'],
                            'season_id' => $stats['season_id'],
                            'team_id' => $stats['team_id'],
                        ],
                        $stats
                    );
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

                    $playerGameStats = PlayerGameStats::where([
                        ['player_id', $player['id']],
                        ['game_id', $schedule->game_id],
                        ['team_id', $schedule->home_id],
                        ['season_id', $seasonId]
                    ])->first();

                    if ($playerGameStats && $minutes === 0) {
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

                        $totalGames = Schedules::where('season_id', $seasonId)
                            ->where('status', 2)
                            ->where('away_id', $schedule->away_id)
                            ->count();

                        $awayTeamDefensiveStats = [
                            'blocks' => PlayerGameStats::where('team_id', $schedule->away_id)
                                ->where('season_id', $seasonId)
                                ->selectRaw('SUM(blocks) / ?', [$totalGames])
                                ->value('SUM(blocks)') ?? 0,
                            'steals' => PlayerGameStats::where('team_id', $schedule->away_id)
                                ->where('season_id', $seasonId)
                                ->selectRaw('SUM(steals) / ?', [$totalGames])
                                ->value('SUM(steals)') ?? 0,
                        ];

                        $defensiveImpact = ($awayTeamDefensiveStats['blocks'] + $awayTeamDefensiveStats['steals']) / 40;
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

                    $playerGameStats = PlayerGameStats::where([
                        ['player_id', $player['id']],
                        ['game_id', $schedule->game_id],
                        ['team_id', $schedule->away_id],
                        ['season_id', $seasonId]
                    ])->first();

                    if ($playerGameStats && $minutes === 0) {
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
                            'blocks' => PlayerGameStats::where('team_id', $schedule->home_id)
                                ->where('season_id', $seasonId)
                                ->selectRaw('SUM(blocks) / ?', [$totalGames])
                                ->value('SUM(blocks)') ?? 0,
                            'steals' => PlayerGameStats::where('team_id', $schedule->home_id)
                                ->where('season_id', $seasonId)
                                ->selectRaw('SUM(steals) / ?', [$totalGames])
                                ->value('SUM(steals)') ?? 0,
                        ];

                        $defensiveImpact = ($homeTeamDefensiveStats['blocks'] + $homeTeamDefensiveStats['steals']) / 40;
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

    private function distributeMinutes($players, $totalMinutes)
    {
        // Define role-based priorities and their minute allocation limits
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Convert Eloquent collection to array
        $playersArray = $players->toArray();

        // Sort players based on their role priority
        $sortedPlayers = collect($playersArray)->sortBy(function ($player) use ($rolePriority) {
            return $rolePriority[$player['role']] ?? 5; // Default to lowest priority if role not found
        })->values();

        $minutes = [];
        $assignedMinutes = 0;

        // Allocate minutes based on priority roles
        foreach ($sortedPlayers as $player) {
            if (rand(1, 100) <= $player['injury_prone_percentage']) {
                // Player is injured
                $minutes[$player['id']] = 0;
            } else {
                // Define initial minute ranges based on role priority
                switch ($rolePriority[$player['role']] ?? 5) {
                    case 1: // Star player
                        $assignedMinutesForRole = rand(30, 35);
                        break;
                    case 2: // Starter
                        $assignedMinutesForRole = rand(25, 30);
                        break;
                    case 3: // Role player
                        $assignedMinutesForRole = rand(15, 20);
                        break;
                    case 4: // Bench
                        $assignedMinutesForRole = rand(5, 10);
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
        $availablePlayers = array_filter($sortedPlayers->toArray(), function ($player) use ($minutes) {
            return !isset($minutes[$player['id']]) || $minutes[$player['id']] === 0;
        });
        $numAvailablePlayers = count($availablePlayers);

        // Distribute remaining minutes proportionally
        if ($numAvailablePlayers > 0) {
            $minutesPerPlayer = $remainingMinutes / $numAvailablePlayers;

            foreach ($availablePlayers as $player) {
                $minutes[$player['id']] = $minutesPerPlayer;
            }
        }

        return $minutes;
    }

    // Helper function to create a match pair

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
}
