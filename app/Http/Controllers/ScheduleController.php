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

class ScheduleController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Schedules/Index', [
            'status' => session('status'),
        ]);
    }
    public function list(Request $request)
    {
        // Fetch schedules with teams' data for the specified league
        $seasons = Seasons::where('league_id', $request->league_id)
            ->paginate(10); // Adjust per your pagination needs

        return response()->json($seasons);
    }
    public function createSeasonAndSchedule(Request $request)
    {
        $request->validate([
            'season_name' => 'required|unique:seasons,name',
            'type' => 'required|in:1,2,3',
            'start' => 'required',
            'league_id' => 'required|exists:leagues,id',
            'match_type' => 'required|in:1,2',
        ]);

        // Check if the match type is valid for double round robin by conference
        if ($request->type != 3 || $request->match_type != 1) {
            return response()->json([
                'message' => 'Invalid match type or season type for double round robin by conference.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Create a new season

            $season = Seasons::create([
                'name' => $request->season_name,
                'type' => $request->type,
                'match_type' => $request->match_type,
                'start_playoffs' => $request->start,
                'league_id' => $request->league_id,
                'is_conference' => 1,
                'status' => 1, // Assuming default status is 'active'
            ]);

            // Create the double round robin schedule by conference
            $this->createDoubleRoundRobinScheduleByConference($season->id, $request->league_id);

            DB::commit();

            return response()->json([
                'message' => 'Created game schedule successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the exception or handle it as needed
            return response()->json([
                'message' => 'Failed to create game schedule.',
                'error' => 'Error creating season and schedule: ' . $e->getMessage(),
                'season_id' => $request->season_name,
            ], 500);
        }
    }
    private function createDoubleRoundRobinScheduleByConference($seasonId, $leagueId)
    {
        DB::beginTransaction(); // Start transaction
        try {
            // Retrieve teams based on league_id
            $teams = Teams::where('league_id', $leagueId)->get();

            // Group teams by conference_id and shuffle each conference's teams
            $teamsByConference = $teams->groupBy('conference_id')->map(function ($conferenceTeams) {
                return $conferenceTeams->shuffle();
            });

            // Generate matches for each conference
            foreach ($teamsByConference as $conferenceId => $conferenceTeams) {
                $roundCounter = 0; // Initialize round counter
                $numTeams = count($conferenceTeams);
                $gameIdCounter = 1; // Initialize game ID counter
                $matches = [];

                // Generate matches for each round 1st leg
                for ($round = 0; $round < ($numTeams - 1); $round++) {
                    for ($i = 0; $i < $numTeams / 2; $i++) {
                        $homeIndex = ($round + $i) % ($numTeams - 1);
                        $awayIndex = ($numTeams - 1 - $i + $round) % ($numTeams - 1);

                        if ($i == 0) {
                            $awayIndex = $numTeams - 1;
                        }

                        $homeTeam = $conferenceTeams[$homeIndex];
                        $awayTeam = $conferenceTeams[$awayIndex];

                        // Ensure both teams are not null (bye team)
                        if ($homeTeam->id != $awayTeam->id) {
                            // First leg match
                            $gameId = $seasonId . '-' . ($roundCounter + 1) . '-' . $conferenceId . '-' . $gameIdCounter;
                            $matches[] = [
                                'season_id' => $seasonId,
                                'game_id' => $gameId,
                                'round' => $roundCounter + 1, // Continue round number
                                'conference_id' => $conferenceId,
                                'home_id' => $homeTeam->id,
                                'away_id' => $awayTeam->id,
                                'home_score' => 0, // Initialize with default score
                                'away_score' => 0, // Initialize with default score
                            ];
                            $gameIdCounter++;
                        }
                    }
                    $roundCounter++; // Increment round number after each round
                }
                // Generate matches for each round 2nd leg
                for ($round = 0; $round < ($numTeams - 1); $round++) {
                    for ($i = 0; $i < $numTeams / 2; $i++) {
                        $homeIndex = ($round + $i) % ($numTeams - 1);
                        $awayIndex = ($numTeams - 1 - $i + $round) % ($numTeams - 1);

                        if ($i == 0) {
                            $awayIndex = $numTeams - 1;
                        }

                        $homeTeam = $conferenceTeams[$homeIndex];
                        $awayTeam = $conferenceTeams[$awayIndex];

                        // Ensure both teams are not null (bye team)
                        if ($homeTeam->id != $awayTeam->id) {
                            // First leg match
                            $gameId = $seasonId . '-' . ($roundCounter + 1) . '-' . $conferenceId . '-' . $gameIdCounter;
                            $matches[] = [
                                'season_id' => $seasonId,
                                'game_id' => $gameId,
                                'round' => $roundCounter + 1, // Continue round number
                                'conference_id' => $conferenceId,
                                'home_id' => $homeTeam->id,
                                'away_id' => $awayTeam->id,
                                'home_score' => 0, // Initialize with default score
                                'away_score' => 0, // Initialize with default score
                            ];
                            $gameIdCounter++;
                        }
                    }
                    $roundCounter++; // Increment round number after each round
                }
                 // Generate matches for each round 3rd leg
                for ($round = 0; $round < ($numTeams - 1); $round++) {
                    for ($i = 0; $i < $numTeams / 2; $i++) {
                        $homeIndex = ($round + $i) % ($numTeams - 1);
                        $awayIndex = ($numTeams - 1 - $i + $round) % ($numTeams - 1);

                        if ($i == 0) {
                            $awayIndex = $numTeams - 1;
                        }

                        $homeTeam = $conferenceTeams[$homeIndex];
                        $awayTeam = $conferenceTeams[$awayIndex];

                        // Ensure both teams are not null (bye team)
                        if ($homeTeam->id != $awayTeam->id) {
                            // First leg match
                            $gameId = $seasonId . '-' . ($roundCounter + 1) . '-' . $conferenceId . '-' . $gameIdCounter;
                            $matches[] = [
                                'season_id' => $seasonId,
                                'game_id' => $gameId,
                                'round' => $roundCounter + 1, // Continue round number
                                'conference_id' => $conferenceId,
                                'home_id' => $homeTeam->id,
                                'away_id' => $awayTeam->id,
                                'home_score' => 0, // Initialize with default score
                                'away_score' => 0, // Initialize with default score
                            ];
                            $gameIdCounter++;
                        }
                    }
                    $roundCounter++; // Increment round number after each round
                }
                // Save matches to the database
                Schedules::insert($matches);

                // // Create player game stats for each game
                // if ($matches) {
                //     foreach ($matches as $match) {
                //         $homeTeamPlayers = Player::where('team_id', $match['home_id'])->get();
                //         $awayTeamPlayers = Player::where('team_id', $match['away_id'])->get();

                //         foreach ($homeTeamPlayers as $homePlayer) {
                //             if ($homePlayer->team_id == $match['home_id']) {
                //                 PlayerGameStats::updateOrCreate(
                //                     [
                //                         'season_id' => $seasonId,
                //                         'game_id' => $match['game_id'],
                //                         'player_id' => $homePlayer->id,
                //                         'team_id' => $match['home_id'],
                //                     ],
                //                     [
                //                         'points' => 0,
                //                         'rebounds' => 0,
                //                         'assists' => 0,
                //                         'steals' => 0,
                //                         'blocks' => 0,
                //                         'turnovers' => 0,
                //                         'fouls' => 0,
                //                     ]
                //                 );
                //             }
                //         }

                //         foreach ($awayTeamPlayers as $awayPlayer) {
                //             if ($awayPlayer->team_id == $match['away_id']) {
                //                 PlayerGameStats::updateOrCreate(
                //                     [
                //                         'season_id' => $seasonId,
                //                         'game_id' => $match['game_id'],
                //                         'player_id' => $awayPlayer->id,
                //                         'team_id' => $match['away_id'],
                //                     ],
                //                     [
                //                         'points' => 0,
                //                         'rebounds' => 0,
                //                         'assists' => 0,
                //                         'steals' => 0,
                //                         'blocks' => 0,
                //                         'turnovers' => 0,
                //                         'fouls' => 0,
                //                     ]
                //                 );
                //             }
                //         }
                //     }
                // }
            }

            DB::commit(); // Commit transaction if all operations succeed
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback all changes on error
            // Log the error for debugging
            \Log::error('Error creating double round-robin schedule: ' . $e->getMessage());
            // Optionally, you can throw the exception again or return a custom error message
            throw $e;
        }
    }

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

                // Apply defensive adjustments based on away team's defensive stats
                $defensiveImpact = ($awayTeamDefensiveStats['blocks'] + $awayTeamDefensiveStats['steals']) / 20; // Scale factor
                $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                $points = max($points, 0); // Ensure no negative points

                // Apply similar adjustments for assists and rebounds if needed
                // Uncomment if you want to adjust these stats
                // $assists -= round($defensiveImpact * $minutes * 0.1);
                // $assists = max($assists, 0); // Ensure no negative assists
                // $rebounds -= round($defensiveImpact * $minutes * 0.1);
                // $rebounds = max($rebounds, 0); // Ensure no negative rebounds

                $steals = round($minutes * (0.05 - $defensiveImpact / 50)); // Adjust steals based on opponent's defense
                $blocks = round($minutes * (0.03 - $defensiveImpact / 50)); // Adjust blocks based on opponent's defense

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

                // Apply defensive adjustments based on home team's defensive stats
                $defensiveImpact = ($homeTeamDefensiveStats['blocks'] + $homeTeamDefensiveStats['steals']) / 20; // Scale factor
                $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                $points = max($points, 0); // Ensure no negative points

                // Apply similar adjustments for assists and rebounds if needed
                // Uncomment if you want to adjust these stats
                // $assists -= round($defensiveImpact * $minutes * 0.1);
                // $assists = max($assists, 0); // Ensure no negative assists
                // $rebounds -= round($defensiveImpact * $minutes * 0.1);
                // $rebounds = max($rebounds, 0); // Ensure no negative rebounds

                $steals = round($minutes * (0.05 - $defensiveImpact / 50)); // Adjust steals based on opponent's defense
                $blocks = round($minutes * (0.03 - $defensiveImpact / 50)); // Adjust blocks based on opponent's defense

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

                    // Apply defensive adjustments based on away team's defensive stats
                    $defensiveImpact = ($awayTeamDefensiveStats['blocks'] + $awayTeamDefensiveStats['steals']) / 20; // Scale factor
                    $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                    $points = max($points, 0); // Ensure no negative points

                    // Apply similar adjustments for assists and rebounds if needed
                    // Uncomment if you want to adjust these stats
                    // $assists -= round($defensiveImpact * $minutes * 0.1);
                    // $assists = max($assists, 0); // Ensure no negative assists
                    // $rebounds -= round($defensiveImpact * $minutes * 0.1);
                    // $rebounds = max($rebounds, 0); // Ensure no negative rebounds

                    $steals = round($minutes * (0.05 - $defensiveImpact / 50)); // Adjust steals based on opponent's defense
                    $blocks = round($minutes * (0.03 - $defensiveImpact / 50)); // Adjust blocks based on opponent's defense

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

                    // Apply defensive adjustments based on home team's defensive stats
                    $defensiveImpact = ($homeTeamDefensiveStats['blocks'] + $homeTeamDefensiveStats['steals']) / 20; // Scale factor
                    $points -= round($defensiveImpact * $minutes * 0.1); // Adjust points based on opponent's defense
                    $points = max($points, 0); // Ensure no negative points

                    // Apply similar adjustments for assists and rebounds if needed
                    // Uncomment if you want to adjust these stats
                    // $assists -= round($defensiveImpact * $minutes * 0.1);
                    // $assists = max($assists, 0); // Ensure no negative assists
                    // $rebounds -= round($defensiveImpact * $minutes * 0.1);
                    // $rebounds = max($rebounds, 0); // Ensure no negative rebounds

                    $steals = round($minutes * (0.05 - $defensiveImpact / 50)); // Adjust steals based on opponent's defense
                    $blocks = round($minutes * (0.03 - $defensiveImpact / 50)); // Adjust blocks based on opponent's defense

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

    private function getBoxScore($game_id)
    {

        // Fetch game details from the schedules table
        $game = \DB::table('schedule_view')
            ->where('game_id', $game_id)
            ->first();

        if (!$game) {
            return response()->json([
                'message' => 'Game not found',
            ], 404);
        }

        $playerStats = \DB::table('player_game_stats')
            ->where('player_game_stats.game_id', $game_id)
            ->leftJoin('players', 'player_game_stats.player_id', '=', 'players.id') // Join with players
            ->leftJoin('teams', 'player_game_stats.team_id', '=', 'teams.id') // Join with teams to get team names
            ->leftJoin('player_ratings', function ($join) {
                $join->on('player_ratings.player_id', '=', 'players.id')
                    ->on('player_ratings.season_id', '=', 'player_game_stats.season_id'); // Assuming season_id should match
            })
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'players.is_rookie as is_rookie',
                'player_game_stats.team_id',
                'teams.name as team_name',
                'player_ratings.role as player_role',
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

        return $boxScore;
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
    public function getScheduleIds(Request $request)
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
    private function updateFinalsWinnerV1($gameData, $winnerId, $homeScore, $awayScore)
    {
        // Find the MVP of the winning team
        $mvpPlayer = PlayerGameStats::join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->where('player_game_stats.team_id', $winnerId)
            ->where('player_game_stats.game_id', $gameData->game_id)
            ->orderBy('player_game_stats.points', 'desc')
            ->first();

        $finalsMVP = $mvpPlayer ? $mvpPlayer->name : '';
        $finalsMVPId = $mvpPlayer ? $mvpPlayer->player_id : '';

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

    //start playoff algo
    public static function playoffschedule(Request $request)
    {
        // Retrieve inputs
        $seasonId = $request->season_id;
        $round = $request->round;
        $start = $request->start;

        // Update season champions and losers if needed
        if (($start == 16 && $round === 'round_of_16')) {
            self::updateSeasonChampionsAndLosers($seasonId);
        }

        // Retrieve the league_id from the seasons table
        $leagueId = DB::table('seasons')
            ->where('id', $seasonId)
            ->value('league_id');

        // Retrieve the number of conferences based on the league_id
        $conferenceCount = DB::table('conferences')
            ->where('league_id', $leagueId)
            ->count();

        // Ensure we only process if there are exactly 2 conferences
        if ($conferenceCount != 2 && $conferenceCount != 4 && $conferenceCount != 8) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid number of conferences.',
                'conference_count' => $conferenceCount,
            ], 400);
        }

        // Initialize an array to collect all schedules
        $allSchedules = [];

        if ($round == 'interconference_semi_finals' || $round == 'finals') {
            $pairings = self::generatePairings16($seasonId, 0, $round);
            $allSchedules = self::createSchedule($pairings, $seasonId, $round, 0);
        } else {
            // Determine the number of top teams to select per conference
            $topTeamsCount = $conferenceCount * 16 / $conferenceCount;

            // Get top teams from each conference
            $conferences = DB::table('conferences')
                ->where('league_id', $leagueId)
                ->pluck('id')
                ->toArray();

            foreach ($conferences as $conferenceId) {
                $conferenceTeams = DB::table('standings_view')
                    ->where('season_id', $seasonId)
                    ->where('conference_id', $conferenceId)
                    ->where('overall_rank', '<=', $topTeamsCount)
                    ->orderBy('overall_rank', 'asc')
                    ->pluck('team_id')
                    ->toArray();

                // Ensure we have enough teams, pad if necessary
                $totalTeams = count($conferenceTeams);
                if ($totalTeams < $topTeamsCount) {
                    $paddingNeeded = $topTeamsCount - $totalTeams;
                    $paddingTeams = DB::table('standings_view')
                        ->where('season_id', $seasonId)
                        ->where('conference_id', $conferenceId)
                        ->whereNotIn('team_id', $conferenceTeams)
                        ->orderBy('overall_rank', 'asc')
                        ->take($paddingNeeded)
                        ->pluck('team_id')
                        ->toArray();
                    $conferenceTeams = array_merge($conferenceTeams, $paddingTeams);
                }

                // Convert the array to the desired format
                $topTeamsByOverallRank = array_values($conferenceTeams);

                $pairings = ($round == 'round_of_16') ? self::pairTeams($topTeamsByOverallRank, 8) : self::generatePairings16($seasonId, $conferenceId, $round);
                // Create the playoff schedule for the specified round for the current conference
                $schedule = self::createSchedule($pairings, $seasonId, $round, $conferenceId);

                // Append the current conference's schedule to the allSchedules array
                $allSchedules = array_merge($allSchedules, $schedule);
            }
        }
        // Insert all playoff schedules into the database in a single batch
        try {
            self::insertSchedule($seasonId, $round, $allSchedules);

            // Update the season's status based on the round
            $status = self::roundStatusFormatter($round);
            DB::table('seasons')
                ->where('id', $seasonId)
                ->update(['status' => $status]);

            // If the schedule was inserted successfully, return a success response
            return response()->json(['success' => true, 'message' => 'Schedule inserted successfully']);
        } catch (Exception $e) {
            // If an exception occurred (due to duplicate schedule), return an error response
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    private static function updateSeasonChampionsAndLosers($seasonId)
    {
        // Retrieve the top and bottom teams from standings view
        $topTeam = DB::table('standings_view')
            ->where('season_id', $seasonId)
            ->orderBy('overall_rank') // Ascending order for the top team
            ->first();

        $bottomTeam = DB::table('standings_view')
            ->where('season_id', $seasonId)
            ->orderBy('overall_rank', 'desc') // Descending order for the bottom team
            ->first();


        // Check if top and bottom teams exist before updating
        if ($topTeam && $bottomTeam) {
            // Update the season's champion and weakest teams
            DB::table('seasons')
                ->where('id', $seasonId)
                ->update([
                    'champion_id' => $topTeam->team_id,
                    'champion_name' => $topTeam->team_name,
                    'weakest_id' => $bottomTeam->team_id,
                    'weakest_name' => $bottomTeam->team_name,
                ]);
        } else {
            // Handle case where top or bottom team is not found
            // You may log an error or handle it according to your application's logic
        }
    }

    private static function roundStatusFormatter($round)
    {

        switch ($round) {
            case 'round_of_32':
                return 3;
                break;
            case 'round_of_16':
                return 4;
                break;
            case 'quarter_finals':
                return 5;
                break;
            case 'semi_finals':
                return 6;
            case 'interconference_semi_finals':
                return 7;
                break;
            case 'finals':
                return 8;
                break;
            default:
                return 8;
                break;
        }
    }
    // Function to generate pairings for playoff matches based on the round
    private static function generatePairings16($seasonId, $conferenceId, $round)
    {
        // Initialize pairings array
        $pairings = [];

        // Generate pairings based on the round
        switch ($round) {
            case 'quarter_finals':
                // Pair the teams for quarter-finals
                $winners = self::getWinnersOfRound('round_of_16', $seasonId, $conferenceId);
                $pairings = self::pairTeams($winners, 4);
                break;
            case 'semi_finals':
                // Pair the winners of quarter-finals for semi-finals
                $winners = self::getWinnersOfRound('quarter_finals', $seasonId, $conferenceId);
                $pairings = self::pairTeams($winners, 2);
                break;
            case 'interconference_semi_finals':
                // Pair the winners of semi-finals for finals
                $winners = self::getWinnersOfRound('semi_finals', $seasonId, $conferenceId);
                $pairings = self::pairTeams($winners, 4);
                break;
            case 'finals':
                // Pair the winners of semi-finals for finals
                $winners = self::getWinnersOfRound('interconference_semi_finals', $seasonId, $conferenceId);
                $pairings = self::pairTeams($winners, 2);
                break;
        }

        return $pairings;
    }
    // Function to get the winners of a specific round
    private static function getWinnersOfRound($round, $seasonId, $conferenceId)
    {
        // Retrieve the winners of the specified round from the database
        $winners = false;
        if ($round != 'semi_finals') {
            $winners = DB::table('schedules')
                ->where('round', $round)
                ->where('conference_id', $conferenceId)
                ->where('season_id', $seasonId)
                ->get();
        } else {
            $winners = DB::table('schedules')
                ->where('round', $round)
                ->where('season_id', $seasonId)
                ->get();
        }


        $winningIds = [];

        foreach ($winners as $game) {
            if ($game->home_score > $game->away_score) {
                $winningIds[] = $game->home_id;
            } elseif ($game->away_score > $game->home_score) {
                $winningIds[] = $game->away_id;
            } else {
                // Handle draws if necessary
            }
        }

        return $winningIds;
    }
    private static function pairTeams($teams, $pairCount)
    {
        // Generate pairings based on the teams array
        $pairings = [];
        for ($i = 0; $i < $pairCount / 2; $i++) {
            $pairings[] = [$teams[$i], $teams[$pairCount - $i - 1]];
        }

        return $pairings;
    }
    // Function to create schedule for a round of playoff matches
    private static function createSchedule($pairings, $seasonId, $round, $conferenceId)
    {
        $schedule = [];
        foreach ($pairings as $game_number => $pair) {
            // Create schedule entries for each pairing in the round
            $game_id = $seasonId . 'R' . $round . '-G' . ($game_number + 1) . 'C' . $conferenceId;
            $schedule[] = [
                'home_id' => $pair[0],
                'conference_id' => ($round == 'finals' || $round == 'interconference_semi_finals') ? 0 : $conferenceId,
                'game_id' => $game_id,
                'away_id' => $pair[1],
                'season_id' => $seasonId,
                'round' => $round,
                // Add more fields as needed, such as date and time
            ];
        }

        return $schedule;
    }

    // Function to insert schedule and player game stats into the database
    private static function insertScheduleV1($season, $round, $schedule)
    {
        // Start a database transaction
        DB::transaction(function () use ($season, $round, $schedule) {
            // Insert schedule entries into the database
            DB::table('schedules')->insert($schedule);

            // Prepare player game stats entries
            $playerGameStats = [];
            foreach ($schedule as $match) {
                // Fetch players for home and away teams
                $homeTeamPlayers = Player::where('team_id', $match['home_id'])
                    ->where('is_active', 1)
                    ->get();

                $awayTeamPlayers = Player::where('team_id', $match['away_id'])
                    ->where('is_active', 1)
                    ->get();


                // Create player game stats entries for home team players
                foreach ($homeTeamPlayers as $player) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'season_id' => $season,
                        'game_id' => $match['game_id'],
                        'team_id' => $match['home_id'],
                        'points' => 0, // Initialize or calculate based on game performance
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                    ];
                }

                // Create player game stats entries for away team players
                foreach ($awayTeamPlayers as $player) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'season_id' => $season,
                        'game_id' => $match['game_id'],
                        'team_id' => $match['away_id'],
                        'points' => 0, // Initialize or calculate based on game performance
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                    ];
                }
            }

            // Insert player game stats entries into the database
            if (!empty($playerGameStats)) {
                DB::table('player_game_stats')->insert($playerGameStats);
            }
        });
    }
    private static function insertSchedule($season, $round, $schedule)
    {
        // Start a database transaction
        DB::transaction(function () use ($season, $round, $schedule) {
            // Filter out any matches with duplicate game_id values that already exist in the database
            $existingGameIds = DB::table('schedules')
                ->whereIn('game_id', array_column($schedule, 'game_id'))
                ->pluck('game_id')
                ->toArray();

            // If any game_ids already exist, throw an exception or return an error response
            if (!empty($existingGameIds)) {
                $existingGameIdsStr = implode(', ', $existingGameIds);
                throw new \Exception("Duplicate game_id(s) found: {$existingGameIdsStr}. No schedules were inserted.");
            }

            // Insert schedule entries into the database
            DB::table('schedules')->insert($schedule);

            // Prepare player game stats entries
            $playerGameStats = [];
            foreach ($schedule as $match) {
                // Fetch players for home and away teams
                $homeTeamPlayers = Player::where('team_id', $match['home_id'])
                    ->where('is_active', 1)
                    ->get();

                $awayTeamPlayers = Player::where('team_id', $match['away_id'])
                    ->where('is_active', 1)
                    ->get();

                // Create player game stats entries for home team players
                foreach ($homeTeamPlayers as $player) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'season_id' => $season,
                        'game_id' => $match['game_id'],
                        'team_id' => $match['home_id'],
                        'points' => 0,
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                    ];
                }

                // Create player game stats entries for away team players
                foreach ($awayTeamPlayers as $player) {
                    $playerGameStats[] = [
                        'player_id' => $player->id,
                        'season_id' => $season,
                        'game_id' => $match['game_id'],
                        'team_id' => $match['away_id'],
                        'points' => 0,
                        'rebounds' => 0,
                        'assists' => 0,
                        'steals' => 0,
                        'blocks' => 0,
                        'turnovers' => 0,
                        'fouls' => 0,
                    ];
                }
            }

            // Insert player game stats entries into the database
            if (!empty($playerGameStats)) {
                DB::table('player_game_stats')->insert($playerGameStats);
            }
        });
    }
}
