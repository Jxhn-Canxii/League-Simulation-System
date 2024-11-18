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
    public function createseasonandschedule(Request $request)
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
                                'home_id' => $awayTeam->id,
                                'away_id' => $homeTeam->id,
                                'home_score' => 0, // Initialize with default score
                                'away_score' => 0, // Initialize with default score
                            ];
                            $gameIdCounter++;
                        }
                    }
                    $roundCounter++; // Increment round number after each round
                }
                // Generate matches for each round 3rd leg
                // for ($round = 0; $round < ($numTeams - 1); $round++) {
                //     for ($i = 0; $i < $numTeams / 2; $i++) {
                //         $homeIndex = ($round + $i) % ($numTeams - 1);
                //         $awayIndex = ($numTeams - 1 - $i + $round) % ($numTeams - 1);

                //         if ($i == 0) {
                //             $awayIndex = $numTeams - 1;
                //         }

                //         $homeTeam = $conferenceTeams[$homeIndex];
                //         $awayTeam = $conferenceTeams[$awayIndex];

                //         // Ensure both teams are not null (bye team)
                //         if ($homeTeam->id != $awayTeam->id) {
                //             // First leg match
                //             $gameId = $seasonId . '-' . ($roundCounter + 1) . '-' . $conferenceId . '-' . $gameIdCounter;
                //             $matches[] = [
                //                 'season_id' => $seasonId,
                //                 'game_id' => $gameId,
                //                 'round' => $roundCounter + 1, // Continue round number
                //                 'conference_id' => $conferenceId,
                //                 'home_id' => $homeTeam->id,
                //                 'away_id' => $awayTeam->id,
                //                 'home_score' => 0, // Initialize with default score
                //                 'away_score' => 0, // Initialize with default score
                //             ];
                //             $gameIdCounter++;
                //         }
                //     }
                //     $roundCounter++; // Increment round number after each round
                // }
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
    private static function getWinnersOfRoundV1($round, $seasonId, $conferenceId)
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

        // Iterate through the winners to determine the winning teams
        foreach ($winners as $game) {
            if ($game->home_score > $game->away_score) {
                $winningIds[] = $game->home_id;
            } elseif ($game->away_score > $game->home_score) {
                $winningIds[] = $game->away_id;
            } else {
                // Handle draws if necessary
            }
        }

        // If the round is semi-finals, order the winning teams by their overall rank
        if ($round == 'semi_finals') {
            $winningIds = DB::table('standings_view')
                ->whereIn('team_id', $winningIds)  // Filter by the winning team IDs
                ->orderBy('overall_rank')  // Order by overall rank
                ->pluck('team_id')  // Get the ordered list of team IDs
                ->toArray();  // Convert the result to a plain array
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
