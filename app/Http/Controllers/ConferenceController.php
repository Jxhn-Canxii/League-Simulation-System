<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConferenceController extends Controller
{
    /**
     * Get the list of all conferences with associated league name.
     */
    public function list()
    {
        $conferences = Conference::with('league')->get();
        return response()->json($conferences);
    }

    /**
     * Get the list of all conferences per league_id.
     */
    public function leagueConference(Request $request)
    {
        // Assuming 'league_id' is a parameter sent in the request
        $league_id = $request->league_id;

        // Assuming you have a relationship between leagues and conferences
        $conferences = Conference::where('league_id', $league_id)
            ->get(['id', 'name']);

        return response()->json($conferences);
    }

    public function seasoninfo(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;

        // Retrieve season information and associated conferences
        $seasons = DB::table('seasons')
            ->where('id', $seasonId)
            ->get();

        // Retrieve conferences associated with the league of the provided season_id
        $conferences = DB::table('conferences')
            ->join('seasons', 'conferences.league_id', '=', 'seasons.league_id')
            ->where('seasons.id', $seasonId)
            ->select('conferences.id', 'conferences.name')
            ->distinct()
            ->get();

        return response()->json([
            'seasons' => $seasons,
            'conferences' => $conferences,
        ]);
    }

    public function seasonstandings(Request $request)
    {
        // Retrieve the season_id and conference_id from the request
        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;

        // Fetch the conference name from the conferences table
        $conference = DB::table('conferences')
            ->where('id', $conferenceId)
            ->first();

        // Fetch standings filtered by season_id and conference_id
        $standings = DB::table('standings_view')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->orderByDesc('wins') // Order by wins in descending order
            ->orderBy('conference_rank','asc') // If wins are tied, then order by score_difference in descending order
            ->get();

        // Return the standings along with the conference name
        return response()->json([
            'standings' => $standings,
            'conference_name' => $conference ? $conference->name : 'N/A', // Check if conference exists
        ]);
    }

    // Function to get power rankings
    public function powerrankings(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;

        // Fetch power rankings filtered by season_id
        $powerRankings = DB::table('power_rankings_view') // Replace with your actual view or table name
            ->where('season_id', $seasonId)
            ->orderByDesc('ranking') // Adjust ordering based on your power ranking logic
            ->get();

        return response()->json([
            'power_rankings' => $powerRankings,
        ]);
    }

    // Function to get the results of the previous round in a conference
    public function previousConferenceRoundResults(Request $request)
    {
        // Retrieve the season_id and conference_id from the request
        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;

        // Retrieve the current round from the request
        $currentRound = $request->current_round;

        // Determine the previous round in the specified conference
        $previousRound = DB::table('schedules')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('round', '<', $currentRound) // Adjust comparison as needed
            ->orderByDesc('round')
            ->value('round');

        // Fetch results for the previous round in the specified conference
        $previousRoundResults = DB::table('schedules')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('round', $previousRound)
            ->get();

        return response()->json([
            'previous_round_results' => $previousRoundResults,
        ]);
    }
    public function seasonschedules(Request $request)
    {
        // Retrieve the season_id and conference_id from the request
        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;
        $excludedRounds = config('playoffs');
        // Retrieve schedules excluding certain rounds
        $schedules = DB::table('schedule_view')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->whereNotIn('round', $excludedRounds)
            ->get();

        // Check if all non-final rounds are simulated
        $allRoundsSimulated = DB::table('schedule_view')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->whereNotIn('round', $excludedRounds)
            ->where('status', 1)
            ->doesntExist(); // Use doesntExist() to check if no records match

        // Count distinct rounds
        $distinctRoundsCount = DB::table('schedule_view')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->whereNotIn('round', $excludedRounds)
            ->distinct('round')
            ->count('round');

        // Retrieve distinct rounds
        $rounds = DB::table('schedule_view')
            ->where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->whereNotIn('round', $excludedRounds)
            ->distinct('round')
            ->pluck('round'); // Get a list of distinct rounds

        return response()->json([
            'schedules' => $schedules,
            'is_simulated' => $allRoundsSimulated,
            'distinct_rounds_count' => $distinctRoundsCount,
            'rounds' => $rounds, // Include the list of rounds in the response
        ]);
    }


    public function seasonsplayoffs(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;
        $status = $request->status;
        $start = $request->start;
        $type = $request->type; // 1 is for finished playoffs : 2 for single update
        $playoffs = $this->playoffTree($seasonId, $status, $type, $start);

        return response()->json([
            'playoffs' => $playoffs,
        ]);
    }
    private static function playoffTree($seasonId, $status, $type, $start)
    {
        $status = $status >= 8 ? 8 : $status;
        // Define round indices based on status
        $roundIndices = [];
        if($start == 8){
            if ($type == 2) {
                $roundIndices = [
                    1 => [],
                    2 => [],
                    4 => [],
                    5 => ['quarter_finals'],
                    6 => ['semi_finals'],
                    7 => ['interconference_semi_finals'],
                    8 => ['finals'],
                ];
            }
            if ($type == 1) {
                $roundIndices = [
                    1 => [],
                    2 => [],
                    4 => [],
                    5 => ['quarter_finals'],
                    6 => ['quarter_finals', 'semi_finals'],
                    7 => ['quarter_finals', 'semi_finals','interconference_semi_finals'],
                    8 => ['quarter_finals', 'semi_finals','interconference_semi_finals', 'finals']
                ];
            }
        }
        if($start == 16){
            if ($type == 2) {
                $roundIndices = [
                    1 => [],
                    2 => [],
                    4 => ['round_of_16'],
                    5 => ['quarter_finals'],
                    6 => ['semi_finals'],
                    7 => ['interconference_semi_finals'],
                    8 => ['finals'],
                ];
            }
            if ($type == 1) {
                $roundIndices = [
                    1 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                    ],
                    2 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                    ],
                    3 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                    ],
                    4 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                        'quarter_finals',
                    ],
                    5 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                        'quarter_finals',
                        'semi_finals',
                    ],
                    6 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                        'quarter_finals',
                        'semi_finals',
                        'finals',
                    ],
                    7 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                        'quarter_finals',
                        'semi_finals',
                        'interconference_semi_finals',
                        'finals',
                    ],
                    8 => [
                        'play_ins_elims_round_1',
                        'play_ins_elims_round_2',
                        'play_ins_finals',
                        'round_of_16',
                        'quarter_finals',
                        'semi_finals',
                        'interconference_semi_finals',
                        'finals',
                    ],
                ];
            }
        }


        // Determine the current rounds based on status
        $currentRounds = $roundIndices[$status];

        // Organize the playoff tree structure
        $tree = [];

        foreach ($currentRounds as $round) {
            $tree[$round] = [];

            // Fetch playoff schedule data for the specified season ID and current round
            $playoffSchedule = DB::table('schedules')
                ->select('game_id', 'home_id', 'away_id', 'home_score', 'away_score', 'round', 'id')
                ->where('season_id', $seasonId)
                ->where('round', $round)
                ->orderBy('round', 'asc')
                ->get();

            // Organize the playoff schedule data into the tree structure
            // Fetch unique team IDs for home and away teams
            $teamIds = $playoffSchedule->pluck('home_id')->merge($playoffSchedule->pluck('away_id'))->unique();

            // Fetch standings data for all teams in a single query
            $standingsData = DB::table('standings_view')
                ->whereIn('team_id', $teamIds)
                ->where('season_id', $seasonId)
                ->get()
                ->keyBy('team_id');

            foreach ($playoffSchedule as $game) {
                // Fetch team names from the standings_data if available, otherwise fetch from the teams table
                $homeTeamName = isset($standingsData[$game->home_id]->name) ? $standingsData[$game->home_id]->name : DB::table('teams')->where('id', $game->home_id)->value('name');
                $awayTeamName = isset($standingsData[$game->away_id]->name) ? $standingsData[$game->away_id]->name : DB::table('teams')->where('id', $game->away_id)->value('name');

                $gameNode = [
                    'id' => $game->id,
                    'game_id' => $game->game_id,
                    'home_team' => [
                        'id' => $game->home_id,
                        'name' => $homeTeamName,
                        'home_score' => $game->home_score,
                        'conference' => isset($standingsData[$game->home_id]->conference_name) ? $standingsData[$game->home_id]->conference_name : null,
                        'conference_rank' => isset($standingsData[$game->home_id]->conference_rank) ? $standingsData[$game->home_id]->conference_rank : null,
                        'overall_rank' => isset($standingsData[$game->home_id]->overall_rank) ? $standingsData[$game->home_id]->overall_rank : null
                    ],
                    'away_team' => [
                        'id' => $game->away_id,
                        'name' => $awayTeamName,
                        'away_score' => $game->away_score,
                        'conference' => isset($standingsData[$game->away_id]->conference_name) ? $standingsData[$game->away_id]->conference_name : null,
                        'conference_rank' => isset($standingsData[$game->away_id]->conference_rank) ? $standingsData[$game->away_id]->conference_rank : null,
                        'overall_rank' => isset($standingsData[$game->away_id]->overall_rank) ? $standingsData[$game->away_id]->overall_rank : null
                    ],
                    'winner' => $game->home_score > $game->away_score ? $game->home_id : ($game->home_score < $game->away_score ? $game->away_id : null), // Set winner_id based on score comparison
                    'season_id' => $seasonId // Include season_id
                ];


                // Determine the winner based on home_score and away_score
                if ($game->home_score > $game->away_score) {
                    $gameNode['winner'] = $game->home_id;
                } elseif ($game->home_score < $game->away_score) {
                    $gameNode['winner'] = $game->away_id;
                }

                $tree[$round][] = $gameNode;
            }
        }

        return $tree;
    }

    /**
     * Add a new conference.
     */
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'league_id' => 'required|exists:leagues,id',
        ]);

        $conference = new Conference();
        $conference->name = $request->input('name');
        $conference->league_id = $request->input('league_id');
        $conference->save();

        return response()->json(['message' => 'Conference added successfully']);
    }

    /**
     * Delete a conference.
     */
    public function delete(Conference $conference)
    {
        $conference->delete();
        return response()->json(['message' => 'Conference deleted successfully']);
    }
}
