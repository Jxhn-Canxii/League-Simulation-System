<?php
namespace App\Http\Controllers;

use App\Models\Seasons;
use App\Models\Schedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeasonsController extends Controller
{
    /**
     * Display a listing of the seasons.
     */
    public function index()
    {
        return Inertia::render('Seasons/Index', [
            'status' => session('status'),
        ]);
    }

    public function list(Request $request)
    {
        // Retrieve seasons based on the provided league_id
        $query = DB::table('seasons');

        // Join the teams table to retrieve conference names
        $query->leftJoin('teams as winner', 'seasons.finals_winner_id', '=', 'winner.id')
              ->leftJoin('teams as loser', 'seasons.finals_loser_id', '=', 'loser.id')
              ->leftJoin('teams as weakest', 'seasons.weakest_id', '=', 'weakest.id')
              ->leftJoin('teams as champion', 'seasons.champion_id', '=', 'champion.id')
              ->leftJoin('conferences as winner_conference', 'winner.conference_id', '=', 'winner_conference.id')
              ->leftJoin('conferences as loser_conference', 'loser.conference_id', '=', 'loser_conference.id')
              ->leftJoin('conferences as weakest_conference', 'weakest.conference_id', '=', 'weakest_conference.id')
              ->leftJoin('conferences as champion_conference', 'champion.conference_id', '=', 'champion_conference.id');

        // Retrieve search query from request
        $searchQuery = $request->search;

        // Apply search filter if search query is provided
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('seasons.name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('winner.name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('loser.name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('weakest.name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('champion.name', 'like', '%' . $searchQuery . '%');
            });
        }

        // Order the seasons by the season_id column in descending order (latest first)
        $query->orderByDesc('id');

        // Get the total count of records after applying search filter
        $totalCount = $query->count();

        // Set the number of records to display per page
        $perPage = 10;

        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $perPage);

        // Get the current page from the request, default to 1 if not provided
        $currentPage = $request->page_num ?? 1;

        // Calculate the offset for pagination
        $offset = ($currentPage - 1) * $perPage;

        // Retrieve seasons data with pagination after applying search filter
        $seasons = $query->offset($offset)
                         ->limit($perPage)
                         ->select('seasons.*', 'winner_conference.name as winner_conference_name', 'loser_conference.name as loser_conference_name', 'weakest_conference.name as weakest_conference_name', 'champion_conference.name as champion_conference_name')
                         ->get();

        $isNewSeason = $this->is_new_season();

        $teamIds = DB::table('teams')->pluck('id')->toArray();

        // If you need the result as a Collection again, you can convert it back
        $teamIdsCollection = collect($teamIds);
        // Create the response array
        $response = [
            'seasons' => $seasons,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'total_count' => $totalCount,
            'is_new_season' => $isNewSeason,
            'team_ids' => $teamIdsCollection, // Include team count in the response
        ];

        // Return the seasons data along with pagination information as a JSON response
        return response()->json($response);
    }
    private function is_new_season() {
        // Get the last season status
        $lastSeasonStatus = DB::table('seasons')
            ->orderBy('id', 'desc')
            ->value('status');

        // Check the status and return the appropriate value
        if ($lastSeasonStatus == 8) {
            return 1;
        } elseif ($lastSeasonStatus == 9) {
            return 2;
        } elseif ($lastSeasonStatus == 10) {
            return 3;
        }
    }

    public function seasonsperleague(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'league_id' => 'required|exists:leagues,id', // Ensure the league_id exists in the leagues table
        ]);

        // Retrieve seasons based on the provided league_id
        $seasons = Seasons::where('league_id', $request->league_id)->get(['id', 'name']);

        return response()->json($seasons);
    }
    public function seasonsperleaguepaginate(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'league_id' => 'required|exists:leagues,id', // Ensure the league_id exists in the leagues table
            'page_num' => 'required|numeric|min:1',
        ]);

        // Retrieve seasons based on the provided league_id
        $seasonsQuery = Seasons::where('league_id', $request->league_id)
                                 ->orderBy('id', 'desc'); // Order by ID in descending order

        // Get the total count of seasons
        $totalSeasons = $seasonsQuery->count();

        // Set the number of seasons per page
        $perPage = 5; // Change this value according to your requirements

        // Calculate the total pages
        $totalPages = ceil($totalSeasons / $perPage);

        // Retrieve the current page number from the request
        $pageNum = $request->page_num;

        // Validate the page number
        $pageNum = max(1, min($pageNum, $totalPages));

        // Calculate the offset based on the current page number
        $offset = ($pageNum - 1) * $perPage;

        // Retrieve seasons for the current page
        $seasons = $seasonsQuery->skip($offset)->take($perPage)->get(['id', 'name','finals_winner_name']);

        // Prepare the page numbers for pagination
        $pageNumbers = range($pageNum, min($totalPages, $pageNum + 2));

        // Return the seasons and page numbers as JSON response
        return response()->json([
            'seasons' => $seasons,
            'page_numbers' => $pageNumbers,
            'total_pages' => $totalPages,
            'page_num' => $pageNum,
        ]);
    }

    public function seasoninfo(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;

        // Retrieve season information and associated conferences
        $seasons = DB::table('seasons')
            ->where('id', $seasonId)
            ->get();

        $conferences = self::allconference($seasonId);

        return response()->json([
            'seasons' => $seasons,
            'conferences' => $conferences,
        ]);
    }
    private function allconference($seasonId)
    {
        $conferences = DB::table('conferences')
            ->join('seasons', 'conferences.league_id', '=', 'seasons.league_id')
            ->where('seasons.id', $seasonId)
            ->select('conferences.id as conference_id', 'conferences.name as conference_name')
            ->distinct()
            ->get();

        $conferenceChampions = [];
        foreach ($conferences as $conference) {
            $champions = self::championsperconference($conference->conference_id);

            $championshipSeasons = [];
            foreach ($champions as $champion) {
                $championshipSeasons[] = [
                    'season' => $champion->season_name,
                    'team_name' => $champion->finals_winner_name
                ];
            }

            $championCount = count($championshipSeasons);

            $conferenceChampions[] = [
                'id' => $conference->conference_id,
                'name' => $conference->conference_name,
                'champions_count' => $championCount,
                'championship_season' => $championshipSeasons
            ];
        }

        return $conferenceChampions;
    }
    private function championsperconference($conference_id)
    {
        $result = DB::table('teams')
                ->join('seasons', 'teams.id', '=', 'seasons.finals_winner_id')
                ->where('teams.conference_id',$conference_id)
                ->select('seasons.finals_winner_name', 'seasons.name as season_name')
                ->get();
        return $result;
    }

    public function seasonstandings(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;

        // Fetch standings filtered by season_id
        $standings = DB::table('standings_view')
            ->where('season_id', $seasonId)
            ->orderByDesc('wins') // Order by wins in descending order
            ->orderByDesc('score_difference') // If wins are tied, then order by score_difference in descending order
            ->get();

        return response()->json([
            'standings' => $standings,
        ]);
    }
    public function seasonsplayoffs(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;
        $status = $request->status;
        $type = $request->type; // 1 is for finished playoffs : 2 for single update
        $playoffs = ($type == 1 ) ? $this->playoffTree($seasonId,$status) : $this->playoffTreeSingle($seasonId,$status);

        return response()->json([
            'playoffs' => $playoffs,
        ]);
    }
    public function getseasonsdropdown ()
    {
        // Fetch all seasons with their id and name, ordered by the latest season_id
        $seasons = DB::table('seasons')
            ->select('id as season_id', 'name')
            ->orderBy('id', 'desc') // Order by season_id in descending order
            ->get();

        // Return the data as JSON response
        return response()->json($seasons);
    }


    public function seasonschedules(Request $request)
    {
        // Retrieve the season_id from the request
        $seasonId = $request->season_id;

        $schedules = DB::table('schedule_view')
            ->where('season_id', $seasonId)
            ->whereNotIn('round', ['round_of_16', 'quarter_finals', 'semi_finals','interconference_semi_finals', 'finals'])
            ->get();

        return response()->json([
            'schedules' => $schedules,
        ]);
    }
    private static function playoffTree($seasonId, $status)
    {
        // Define round indices based on status
        $roundIndices = [
            1 => [],
            2 => [],
            3 => ['round_of_16'],
            4 => ['round_of_16', 'quarter_finals'],
            5 => ['round_of_16', 'quarter_finals', 'semi_finals'],
            6 => ['round_of_16', 'quarter_finals', 'semi_finals', 'finals'],
            7 => ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals'],
            8 => ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals'],
        ];

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
            foreach ($playoffSchedule as $game) {
                $gameNode = [
                    'id' => $game->id,
                    'game_id' => $game->game_id,
                    'home_team' => [
                        'id' => $game->home_id,
                        'name' => DB::table('teams')->where('id', $game->home_id)->value('name'),
                        'score' => $game->home_score,
                    ],
                    'away_team' => [
                        'id' => $game->away_id,
                        'name' => DB::table('teams')->where('id', $game->away_id)->value('name'),
                        'score' => $game->away_score,
                    ],
                    'winner' => null // Initialize winner as null
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
    private static function playoffTreeSingle($seasonId, $status)
    {
        // Define all possible round names

        // Define round indices based on status
        $roundIndices = [
            1 => [],
            3 => ['round_of_16'],
            4 => ['quarter_finals'],
            5 => ['semi_finals'],
            6 => ['interconference_semi_finals'],
            7 => ['finals'],
            8 => ['finals'],
        ];

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
            foreach ($playoffSchedule as $game) {
                $gameNode = [
                    'id' => $game->id,
                    'game_id' => $game->game_id,
                    'home_team' => [
                        'id' => $game->home_id,
                        'name' => DB::table('teams')->where('id', $game->home_id)->value('name'),
                        'score' => $game->home_score,
                    ],
                    'away_team' => [
                        'id' => $game->away_id,
                        'name' => DB::table('teams')->where('id', $game->away_id)->value('name'),
                        'score' => $game->away_score,
                    ],
                    'winner' => null // Initialize winner as null
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
    // Other methods...
}
