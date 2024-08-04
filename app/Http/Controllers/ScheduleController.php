<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Inertia\Inertia;
use App\Models\Seasons;
use App\Models\Teams;
use App\Models\Schedules;
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
            'match_type'  => 'required|in:1,2',
        ]);

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

        // Get teams for the specified league
        $teams = Teams::where('league_id', $request->league_id)->get();
        if ($request->match_type == 1) { /// round robin per conference
            if ($request->type == 1) {
                // Single elimination schedule by conference
                $this->createSingleEliminationSchedule($season->id, $request->league_id);
            } elseif ($request->type == 2) {
                // Round robin schedule by conference
                $this->createRoundRobinScheduleByConference($season->id, $request->league_id);
            } elseif ($request->type == 3) {
                // Double round robin schedule by conference
                $this->createDoubleRoundRobinScheduleByConference($season->id, $request->league_id);
            }
        }
        if ($request->match_type == 2) { // all teams round robin
            if ($request->type == 1) {
                // Single elimination schedule by conference
                return response()->json([
                    'message' => 'Theres no single elimination in all teams match type',
                ], 500);
            } elseif ($request->type == 2) {
                // Round robin schedule by conference
                $this->createRoundRobinScheduleByLeague($season->id, $request->league_id);
            } elseif ($request->type == 3) {
                // Double round robin schedule by conference
                $this->createDoubleRoundRobinScheduleByLeague($season->id, $request->league_id);
            }
        }


        return response()->json([
            'message' => 'Created game schedule successfully',
        ]);
    }
    private function createRoundRobinScheduleByConference($seasonId, $leagueId)
    {
        // Retrieve teams based on league_id
        $teams = Teams::where('league_id', $leagueId)->get();

        // Group teams by conference_id and shuffle each conference's teams
        $teamsByConference = $teams->groupBy('conference_id')->map(function ($conferenceTeams) {
            return $conferenceTeams->shuffle();
        });

        // Generate matches for each conference
        foreach ($teamsByConference as $conferenceId => $conferenceTeams) {
            // Initialize variables
            $numTeams = $conferenceTeams->count();
            $gameIdCounter = 1;
            $matches = [];

            // Check if the number of teams is odd
            $hasOddTeams = $numTeams % 2 !== 0;

            // If odd, add a bye team
            if ($hasOddTeams) {
                $conferenceTeams->push(null); // Add a null team as a placeholder for the bye
                $numTeams++; // Increment the number of teams
            }

            // Generate matches for each round
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
                    if ($homeTeam && $awayTeam) {
                        // First leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => $round + 1,
                            'conference_id' => $conferenceId,
                            'home_id' => $homeTeam->id,
                            'away_id' => $awayTeam->id,
                        ];
                    }
                }
            }

            // Save matches to the database
            Schedules::insert($matches);
        }
    }
    private function createDoubleRoundRobinScheduleByConference($seasonId, $leagueId)
    {
        // Retrieve teams based on league_id
        $teams = Teams::where('league_id', $leagueId)->get();

        // Group teams by conference_id and shuffle each conference's teams
        $teamsByConference = $teams->groupBy('conference_id')->map(function ($conferenceTeams) {
            return $conferenceTeams->shuffle();
        });

        // Generate matches for each conference
        foreach ($teamsByConference as $conferenceId => $conferenceTeams) {
            // Initialize variables
            $numTeams = $conferenceTeams->count();
            $gameIdCounter = 1;
            $matches = [];

            // Check if the number of teams is odd
            $hasOddTeams = $numTeams % 2 !== 0;

            // If odd, add a bye team
            if ($hasOddTeams) {
                $conferenceTeams->push(null); // Add a null team as a placeholder for the bye
                $numTeams++; // Increment the number of teams
            }

            // Generate matches for each round
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
                    if ($homeTeam && $awayTeam) {
                        // First leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => $round + 1,
                            'conference_id' => $conferenceId,
                            'home_id' => $homeTeam->id,
                            'away_id' => $awayTeam->id,
                        ];
                    }
                }
            }

            // Generate reverse matches for double round-robin
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
                    if ($homeTeam && $awayTeam) {
                        // Second leg match (reverse of the first leg)
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => $round + 1 + ($numTeams - 1), // Adjust the round number for second leg
                            'conference_id' => $conferenceId,
                            'home_id' => $awayTeam->id,
                            'away_id' => $homeTeam->id,
                        ];
                    }
                }
            }

            // Save matches to the database
            Schedules::insert($matches);
        }
    }
    private function createRoundRobinScheduleByLeague($seasonId, $leagueId)
    {
        // Retrieve teams based on league_id
        $teams = Teams::where('league_id', $leagueId)->get();

        // Group teams by conference_id
        $teamsByConference = $teams->groupBy('conference_id');

        // Initialize variables
        $gameIdCounter = 1;
        $matches = [];

        // Generate intra-conference matches (double round-robin within each conference)
        foreach ($teamsByConference as $conferenceId => $conferenceTeams) {
            $numTeams = $conferenceTeams->count();
            $hasOddTeams = $numTeams % 2 !== 0;

            // If odd, add a bye team
            if ($hasOddTeams) {
                $conferenceTeams->push(null); // Add a null team as a placeholder for the bye
                $numTeams++; // Increment the number of teams
            }

            // Generate matches for each team playing against every other team twice
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
                    if ($homeTeam && $awayTeam) {
                        // First leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => $round + 1,
                            'conference_id' => $conferenceId,
                            'home_id' => $homeTeam->id,
                            'away_id' => $awayTeam->id,
                        ];

                        // Second leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => ($numTeams - 1) + $round + 1, // Start the second leg round after the first leg rounds
                            'conference_id' => $conferenceId,
                            'home_id' => $awayTeam->id,
                            'away_id' => $homeTeam->id,
                        ];
                    }
                }
            }
        }
        // Shuffle the order of matches
        shuffle($matches);

        // Save matches to the database
        Schedules::insert($matches);
    }
    private function createDoubleRoundRobinScheduleByLeague($seasonId, $leagueId)
    {
        // Retrieve teams based on league_id
        $teams = Teams::where('league_id', $leagueId)->get();

        // Group teams by conference_id
        $teamsByConference = $teams->groupBy('conference_id');

        // Initialize variables
        $gameIdCounter = 1;
        $matches = [];

        // Generate intra-conference matches (double round-robin within each conference)
        foreach ($teamsByConference as $conferenceId => $conferenceTeams) {
            $numTeams = $conferenceTeams->count();
            $hasOddTeams = $numTeams % 2 !== 0;

            // If odd, add a bye team
            if ($hasOddTeams) {
                $conferenceTeams->push(null); // Add a null team as a placeholder for the bye
                $numTeams++; // Increment the number of teams
            }

            // Generate matches for each team playing against every other team twice
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
                    if ($homeTeam && $awayTeam) {
                        // First leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => $round + 1,
                            'conference_id' => $conferenceId,
                            'home_id' => $homeTeam->id,
                            'away_id' => $awayTeam->id,
                        ];

                        // Second leg match
                        $matches[] = [
                            'season_id' => $seasonId,
                            'game_id' => $gameIdCounter++,
                            'round' => ($numTeams - 1) + $round + 1, // Start the second leg round after the first leg rounds
                            'conference_id' => $conferenceId,
                            'home_id' => $awayTeam->id,
                            'away_id' => $homeTeam->id,
                        ];
                    }
                }
            }
        }

        // Generate inter-conference matches (home and away)
        foreach ($teamsByConference as $confId => $confTeams) {
            foreach ($confTeams as $homeTeam) {
                foreach ($teamsByConference as $otherConfId => $otherConfTeams) {
                    if ($otherConfId != $confId) {
                        foreach ($otherConfTeams as $awayTeam) {
                            // Ensure both teams are not null (in case of a bye week)
                            if ($homeTeam && $awayTeam) {
                                // First leg match
                                $matches[] = [
                                    'season_id' => $seasonId,
                                    'game_id' => $gameIdCounter++,
                                    'round' => $gameIdCounter, // Use a different counter for inter-conference rounds
                                    'conference_id' => $homeTeam->conference_id, // Conference of the home team
                                    'home_id' => $homeTeam->id,
                                    'away_id' => $awayTeam->id,
                                ];

                                // Second leg match
                                $matches[] = [
                                    'season_id' => $seasonId,
                                    'game_id' => $gameIdCounter++,
                                    'round' => $gameIdCounter, // Use a different counter for inter-conference rounds
                                    'conference_id' => $homeTeam->conference_id, // Conference of the home team
                                    'home_id' => $awayTeam->id,
                                    'away_id' => $homeTeam->id,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Shuffle the order of matches
        shuffle($matches);

        // Save matches to the database
        Schedules::insert($matches);
    }
    // Helper function to create a match pair
    private function createMatch($seasonId, $gameId, $round, $conferenceId, $homeId, $awayId)
    {
        return [
            'season_id' => $seasonId,
            'game_id' => $gameId,
            'round' => $round,
            'conference_id' => $conferenceId,
            'home_id' => $homeId,
            'away_id' => $awayId,
        ];
    }

    public function simulate(Request $request)
    {
        // Validate the request data if necessary

        // Join the teams table to get home and away team names and standings in a single query
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

        // Fetch historical performance metrics (average wins)
        $homeHistoricalPerformance = DB::table('standings_view')
            ->where('team_id', $gameData->home_team_id)
            ->avg('wins');
        $awayHistoricalPerformance = DB::table('standings_view')
            ->where('team_id', $gameData->away_team_id)
            ->avg('wins');

        // Check if home or away team is the defending champion
        $seasonData = DB::table('seasons')
            ->where('id', $gameData->season_id - 1)
            ->select('finals_winner_id')
            ->first();

        $homeTeamStrength = rand(1, 100); // Home team's strength (random value)
        $awayTeamStrength = rand(1, 100); // Away team's strength (random value)

        if ($seasonData) {
            // Apply defending champion advantage
            if ($gameData->home_team_id == $seasonData->finals_winner_id) {
                $homeTeamStrength += 10; // Defending champions get a strength boost
            }
            if ($gameData->away_team_id == $seasonData->finals_winner_id) {
                $awayTeamStrength += 10; // Defending champions get a strength boost
            }
        }


        // Simulate the game logic here
        $homeAdvantage = 5; // Home advantage (constant value)
        $weatherImpact = rand(-10, 10); // Impact of weather on game (random value)
        $injuryImpact = rand(-10, 10); // Impact of injuries on team performance (random value)

        // Calculate season performance factor
        $homePerformanceFactor = ($homeHistoricalPerformance + $gameData->home_current_performance) / 2;
        $awayPerformanceFactor = ($awayHistoricalPerformance + $gameData->away_current_performance) / 2;

        // Apply factors to calculate scores
        $homeScore = max(round($homeTeamStrength * (1 + $homeAdvantage / 100) * (rand(90, 110) / 100) + $weatherImpact + $injuryImpact + $homePerformanceFactor), 0);
        $awayScore = max(round($awayTeamStrength * (rand(90, 105) / 100) + $weatherImpact + $injuryImpact + $awayPerformanceFactor), 0);

        // Ensure both scores are not zero
        if ($homeScore === 0 && $awayScore === 0) {
            $homeScore = max(round($homeTeamStrength * (rand(85, 105) / 100)), 0) + 5;
            $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100)), 0) + 5;
        }
        if ($homeScore === $awayScore) {
            $homeScore += 5;
            $awayScore += 5;
        }

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
        if ($gameData->round === 'finals') {
            $seasonUpdateData = [
                'finals_winner_id' => $winnerId,
                'finals_loser_id' => $winnerId === $gameData->home_team_id ? $gameData->away_team_id : $gameData->home_team_id,
                'finals_winner_name' => $winnerId === $gameData->home_team_id ? $gameData->home_team_name : $gameData->away_team_name,
                'finals_loser_name' => $winnerId === $gameData->home_team_id ? $gameData->away_team_name : $gameData->home_team_name,
                'finals_winner_score' => $winnerId === $gameData->home_team_id ? $homeScore : $awayScore,
                'finals_loser_score' => $winnerId === $gameData->home_team_id ? $awayScore : $homeScore,
            ];
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

    public function simulateperround(Request $request)
    {
        // Validate the request data
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'round' => 'required|integer',
        ]);

        // Retrieve the season ID and round from the request
        $seasonId = $request->season_id;
        $round = $request->round;

        // Find all schedules for the given season ID and round
        $schedules = Schedules::where('season_id', $seasonId)
            ->where('round', $round)
            ->get();

        // Check if there are any schedules found
        if ($schedules->isEmpty()) {
            return response()->json([
                'error' => 'No schedules found for the given season and round.',
            ], 404);
        }

        // Fetch historical and current performance metrics for all teams involved in one query
        $teamIds = $schedules->pluck('home_id')->merge($schedules->pluck('away_id'))->unique();

        $performanceData = DB::table('standings_view')
            ->whereIn('team_id', $teamIds)
            ->where('season_id', $seasonId)
            ->get()
            ->groupBy('team_id');

        // Loop through each schedule to simulate the game
        foreach ($schedules as $schedule) {
            $homeData = $performanceData->get($schedule->home_id, collect());
            $awayData = $performanceData->get($schedule->away_id, collect());

            // Fetch historical and current performance metrics
            $homeHistoricalPerformance = $homeData->avg('wins');
            $awayHistoricalPerformance = $awayData->avg('wins');
            $homeCurrentPerformance = $homeData->sum('wins'); // Assuming 'wins' column is summed for current performance
            $awayCurrentPerformance = $awayData->sum('wins'); // Assuming 'wins' column is summed for current performance

            // Simulate the game logic here
            // Factors affecting game outcome
            $homeTeamStrength = rand(1, 100); // Home team's strength (random value)
            $awayTeamStrength = rand(1, 100); // Away team's strength (random value)
            $homeAdvantage = 5; // Home advantage (constant value)
            $weatherImpact = rand(-10, 10); // Impact of weather on game (random value)
            $injuryImpact = rand(-10, 10); // Impact of injuries on team performance (random value)

            // Calculate season performance factor
            $homePerformanceFactor = ($homeHistoricalPerformance + $homeCurrentPerformance) / 2;
            $awayPerformanceFactor = ($awayHistoricalPerformance + $awayCurrentPerformance) / 2;

            // Apply factors to calculate scores
            $homeScore = max(round($homeTeamStrength * (1 + $homeAdvantage / 100) * (rand(90, 110) / 100) + $weatherImpact + $injuryImpact + $homePerformanceFactor), 0);
            $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100) + $weatherImpact + $injuryImpact + $awayPerformanceFactor), 0);

            // Ensure both scores are not zero
            if ($homeScore === 0 && $awayScore === 0) {
                $homeScore = max(round($homeTeamStrength * (rand(85, 105) / 100)), 0) + 5;
                $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100)), 0) + 5;
            }

            // Ensure there is no tie
            while ($homeScore === $awayScore) {
                $homeScore += rand(1, 3); // Randomly adjust home score
            }

            // Update the scores
            $schedule->home_score = $homeScore;
            $schedule->away_score = $awayScore;
            $schedule->status = 2; // Marking the game as completed

            // Save the updated scores
            $schedule->save();
        }

        // Check if all games in the round have been simulated
        $allGamesSimulated = Schedules::where('season_id', $seasonId)
            ->where('round', $round)
            ->where('status', '!=', 2) // Checking for games that are not yet simulated
            ->doesntExist();

        if ($allGamesSimulated) {
            // Update the season's status to 2 (indicating all games in the round have been simulated)
            $season = Seasons::find($seasonId);
            if ($season) {
                $season->status = 2;
                $season->save();
            }
        }

        // Return a response indicating success
        return response()->json([
            'message' => 'All games for round ' . $round . ' simulated successfully.',
        ]);
    }
    public function simulateperconference(Request $request)
    {
        // Validate the request data
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'conference_id' => 'required|exists:conferences,id',
        ]);

        // Retrieve the season ID and conference ID from the request
        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;

        // Find all schedules for the given season ID and conference ID
        $schedules = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->get();

        // Check if there are any schedules found
        if ($schedules->isEmpty()) {
            return response()->json([
                'error' => 'No schedules found for the given season and conference.',
            ], 404);
        }

        // Fetch historical and current performance metrics for all teams involved in one query
        $teamIds = $schedules->pluck('home_id')->merge($schedules->pluck('away_id'))->unique();

        $performanceData = DB::table('standings_view')
            ->whereIn('team_id', $teamIds)
            ->where('season_id', $seasonId)
            ->get()
            ->groupBy('team_id');

        // Fetch the defending champion of the previous season
        $previousSeasonId = $seasonId - 1;
        $previousSeasonData = DB::table('seasons')
            ->where('id', $previousSeasonId)
            ->select('finals_winner_id')
            ->first();

        // Fetch conference champions from the previous season
        $previousConferenceChampions = DB::table('standings_view')
            ->where('season_id', $previousSeasonId)
            ->where('conference_rank', 1)
            ->pluck('team_id')
            ->toArray();

        // Fetch national champion from the previous season
        $previousNationalChampion = DB::table('standings_view')
            ->where('season_id', $previousSeasonId)
            ->where('overall_rank', 1)
            ->value('team_id');

        // Loop through each schedule to simulate the game
        foreach ($schedules as $schedule) {
            $homeData = $performanceData->get($schedule->home_id, collect());
            $awayData = $performanceData->get($schedule->away_id, collect());

            // Fetch historical and current performance metrics
            $homeHistoricalPerformance = $homeData->avg('wins');
            $awayHistoricalPerformance = $awayData->avg('wins');
            $homeCurrentPerformance = $homeData->where('season_id', $seasonId)->sum('wins');
            $awayCurrentPerformance = $awayData->where('season_id', $seasonId)->sum('wins');

            // Simulate the game logic here
            // Factors affecting game outcome
            $homeTeamStrength = rand(1, 100); // Home team's strength (random value)
            $awayTeamStrength = rand(1, 100); // Away team's strength (random value)
            $homeAdvantage = 5; // Home advantage (constant value)
            $weatherImpact = rand(-10, 10); // Impact of weather on game (random value)
            $injuryImpact = rand(-10, 10); // Impact of injuries on team performance (random value)

            // Apply previous season's champion advantages
            if ($previousSeasonData) {
                if ($schedule->home_id == $previousSeasonData->finals_winner_id) {
                    $homeTeamStrength += 10; // Finals champions get a strength boost
                }
                if ($schedule->away_id == $previousSeasonData->finals_winner_id) {
                    $awayTeamStrength += 10; // Finals champions get a strength boost
                }
            }
            if ($previousConferenceChampions) {
                if (in_array($schedule->home_id, $previousConferenceChampions)) {
                    $homeTeamStrength += 5; // Conference champions get a strength boost
                }
                if (in_array($schedule->away_id, $previousConferenceChampions)) {
                    $awayTeamStrength += 5; // Conference champions get a strength boost
                }
            }
            if ($previousNationalChampion) {
                if ($schedule->home_id == $previousNationalChampion) {
                    $homeTeamStrength += 10; // National champions get a strength boost
                }
                if ($schedule->away_id == $previousNationalChampion) {
                    $awayTeamStrength += 10; // National champions get a strength boost
                }
            }

            // Calculate season performance factor
            $homePerformanceFactor = ($homeHistoricalPerformance + $homeCurrentPerformance) / 2;
            $awayPerformanceFactor = ($awayHistoricalPerformance + $awayCurrentPerformance) / 2;

            // Apply factors to calculate scores
            $homeScore = max(round($homeTeamStrength * (1 + $homeAdvantage / 100) * (rand(90, 110) / 100) + $weatherImpact + $injuryImpact + $homePerformanceFactor), 0);
            $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100) + $weatherImpact + $injuryImpact + $awayPerformanceFactor), 0);

            // Ensure both scores are not zero
            if ($homeScore === 0 && $awayScore === 0) {
                $homeScore = max(round($homeTeamStrength * (rand(85, 105) / 100)), 0) + 5;
                $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100)), 0) + 5;
            }
            if ($homeScore === $awayScore) {
                $homeScore += 5;
                $awayScore += 5;
            }

            // Update the scores
            $schedule->home_score = $homeScore;
            $schedule->away_score = $awayScore;
            $schedule->status = 2; // Marking the game as completed

            // Save the updated scores
            $schedule->save();
        }

        // Check if all rounds have been simulated
        $allRoundsSimulated = Schedules::where('season_id', $seasonId)
            ->where('status', 1)
            ->doesntExist();

        if ($allRoundsSimulated) {
            // Update the season's status to 2 (indicating all rounds have been simulated)
            $season = Seasons::find($seasonId);
            if ($season) {
                $season->status = 2;
                $season->save();
            }
        }

        // Return a response indicating success
        return response()->json([
            'message' => 'All conference games simulated successfully.',
        ]);
    }
    public function simulateall(Request $request)
    {
        // Validate the request data
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
        ]);

        // Retrieve the season ID from the request
        $seasonId = $request->season_id;

        // Find all schedules for the given season ID
        $schedules = Schedules::where('season_id', $seasonId)->get();

        // Check if there are any schedules found
        if ($schedules->isEmpty()) {
            return response()->json([
                'error' => 'No schedules found for the given season.',
            ], 404);
        }

        // Fetch historical and current performance metrics for all teams involved in one query
        $teamIds = $schedules->pluck('home_id')->merge($schedules->pluck('away_id'))->unique();

        $performanceData = DB::table('standings_view')
            ->whereIn('team_id', $teamIds)
            ->where('season_id', $seasonId)
            ->get()
            ->groupBy('team_id');

        // Loop through each schedule to simulate the game
        foreach ($schedules as $schedule) {
            $homeData = $performanceData->get($schedule->home_id, collect());
            $awayData = $performanceData->get($schedule->away_id, collect());

            // Fetch historical and current performance metrics
            $homeHistoricalPerformance = $homeData->avg('wins');
            $awayHistoricalPerformance = $awayData->avg('wins');
            $homeCurrentPerformance = $homeData->where('season_id', $seasonId)->sum('wins');
            $awayCurrentPerformance = $awayData->where('season_id', $seasonId)->sum('wins');

            // Simulate the game logic here
            // Factors affecting game outcome
            $homeTeamStrength = rand(1, 100); // Home team's strength (random value)
            $awayTeamStrength = rand(1, 100); // Away team's strength (random value)
            $homeAdvantage = 5; // Home advantage (constant value)
            $weatherImpact = rand(-10, 10); // Impact of weather on game (random value)
            $injuryImpact = rand(-10, 10); // Impact of injuries on team performance (random value)

            // Calculate season performance factor
            $homePerformanceFactor = ($homeHistoricalPerformance + $homeCurrentPerformance) / 2;
            $awayPerformanceFactor = ($awayHistoricalPerformance + $awayCurrentPerformance) / 2;

            // Apply factors to calculate scores
            $homeScore = max(round($homeTeamStrength * (1 + $homeAdvantage / 100) * (rand(90, 110) / 100) + $weatherImpact + $injuryImpact + $homePerformanceFactor), 0);
            $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100) + $weatherImpact + $injuryImpact + $awayPerformanceFactor), 0);

            // Ensure both scores are not zero
            if ($homeScore === 0 && $awayScore === 0) {
                $homeScore = max(round($homeTeamStrength * (rand(85, 105) / 100)), 0) + 5;
                $awayScore = max(round($awayTeamStrength * (rand(85, 105) / 100)), 0) + 5;
            }

            // Ensure there is no tie
            while ($homeScore === $awayScore) {
                $homeScore += rand(1, 3); // Randomly adjust home score
            }

            // Update the scores
            $schedule->home_score = $homeScore;
            $schedule->away_score = $awayScore;
            $schedule->status = 2; // Marking the game as completed

            // Save the updated scores
            $schedule->save();
        }

        // Update the season's status to 2 (indicating all games have been simulated)
        $season = Seasons::find($seasonId);
        if ($season) {
            $season->status = 2;
            $season->save();
        }

        // Return a response indicating success
        return response()->json([
            'message' => 'All games simulated successfully',
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

        if($round == 'interconference_semi_finals' || $round == 'finals'){
            $pairings = self::generatePairings16($seasonId, 0, $round);
            $allSchedules = self::createSchedule($pairings, $seasonId, $round,0);
        }
        else{
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

                $pairings = ($round == 'round_of_16') ? self::pairTeams($topTeamsByOverallRank, 8) : self::generatePairings16($seasonId,$conferenceId, $round);
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
        if($round != 'semi_finals') {
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


    // Function to insert schedule into the database
    private static function insertSchedule($season, $round, $schedule)
    {
        // Check if a schedule with the same round and season_id already exists
        DB::table('schedules')->insert($schedule);
    }
}
