<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Teams;

class TeamsController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return Inertia::render('Teams/Index', [
            'status' => session('status'),
        ]);
    }
    public function list(Request $request)
    {
        // Retrieve search query from request
        $searchQuery = $request->search;

        // Query builder for teams with join on leagues and conferences tables
        $query = Teams::query()
            ->join('leagues', 'teams.league_id', '=', 'leagues.id')
            ->join('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->select('teams.*', 'leagues.name as league_name', 'conferences.name as conference_name');

        // Apply search filter if search query is provided
        if ($searchQuery) {
            $query->where('teams.name', 'like', '%' . $searchQuery . '%')
                ->orWhere('leagues.name', 'like', '%' . $searchQuery . '%')
                ->orWhere('conferences.name', 'like', '%' . $searchQuery . '%');
        }

        // Get total count of records before pagination
        $totalCount = $query->count();

        // Set the number of records to display per page
        $perPage = 10;

        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $perPage);

        // Get the current page from the request, default to 1 if not provided
        $currentPage = $request->page_num ?? 1;

        // Calculate the offset for pagination
        $offset = ($currentPage - 1) * $perPage;

        // Retrieve teams data with pagination
        $teams = $query->offset($offset)
            ->limit($perPage)
            ->get();

        return response()->json([
            'teams' => $teams,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'total_count' => $totalCount,
            'search' => $searchQuery,
        ]);
    }

    public function teamslatestseason(Request $request)
    {
        $team_id = $request->team_id;
        $season_id = $request->season_id;

        $team_info = $this->currentseasonstatistics($team_id, $season_id);

        return response()->json([
            'data' => $team_info,
        ]);
    }
    public function matchhistory(Request $request)
    {
        $home_id = $request->home_id;
        $away_id = $request->away_id;
        $season_id = $request->season_id;

        $matches = self::getLast10MatchResults($season_id, $home_id, $away_id);

        return response()->json([
            'matches' => $matches,
        ]);
    }
    public function currentseasonstatistics($teamId, $seasonId)
    {
        $teamInfo = self::getTeamInfo($teamId);
        $allTimeStats = self::getAllTimeStats($teamId);
        $finalsStats = self::getFinalsStats($teamId);
        $roundStats = self::getRoundStats($teamId);
        $playoffStats = self::getPlayoffStats($teamId);
        $seasonStats = self::getSeasonHistoryCount($teamId, $seasonId);
        $latestSeason = self::getLatestSeason($teamId, $seasonId);

        return [
            'teams' => $teamInfo[0],
            'allTimeStats' => $allTimeStats,
            'finalsStats' => $finalsStats,
            'roundStats' => $roundStats,
            'playoffStats' => $playoffStats,
            'seasonStats' => $seasonStats,
            'latestSeason' => $latestSeason,
        ];
    }
    private function getLatestSeason($teamId, $seasonId)
    {
        return DB::table('standings_view')
            ->select('*') // Select the necessary fields from season_view
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->get(); // Get the latest season record
    }

    public function getLast10MatchResults($season_id, $homeId, $awayId)
    {
        // Query to retrieve last 10 match results
        $matchResults = DB::table('schedule_view')
            ->where(function ($query) use ($homeId, $awayId, $season_id) {
                $query->where('season_id', '<=', $season_id) // Filter by season_id less than or equal to the specified value
                    ->where('home_id', $homeId)
                    ->where('away_id', $awayId)
                    ->whereRaw('round NOT REGEXP "^[0-9]+$"'); // Exclude rounds that are not numbers
            })
            ->orWhere(function ($query) use ($homeId, $awayId, $season_id) {
                $query->where('season_id', '<=', $season_id) // Filter by season_id less than or equal to the specified value
                    ->where('home_id', $awayId)
                    ->where('away_id', $homeId)
                    ->whereRaw('round NOT REGEXP "^[0-9]+$"'); // Exclude rounds that are not numbers
            })
            ->orderByDesc('created_at') // Order by created_at in descending order to get the latest results first
            ->take(10) // Limit the results to 10
            ->get(); // Retrieve the results as a collection

        return $matchResults;
    }




    public function teaminfo(Request $request)
    {
        $teamId = $request->team_id;

        $teamInfo = self::getTeamInfo($teamId);
        $allTimeStats = self::getAllTimeStats($teamId);
        $finalsStats = self::getFinalsStats($teamId);
        $roundStats = self::getRoundStats($teamId);
        $playoffStats = self::getPlayoffStats($teamId);
        $gameStreaks = self::getTeamStreaks($teamId);

        return response()->json([
            'teams' => $teamInfo[0],
            'allTimeStats' => $allTimeStats,
            'finalsStats' => $finalsStats,
            'roundStats' => $roundStats,
            'playoffStats' => $playoffStats,
            'streaks' => $gameStreaks,
        ]);
    }
    public function teamseasonfinals(Request $request)
    {
        $teamId = $request->team_id;
        $finalsSeasons = self::getFinalsSeasons($teamId);
        $finalsWinSeasons = self::getFinalsWinSeasons($teamId);

        return response()->json([
            'finalsSeasons' => $finalsSeasons,
            'finalsWinSeasons' => $finalsWinSeasons,
        ]);
    }
    public function teamseasonstandings(Request $request)
    {
        $teamId = $request->team_id;
        $topStandingsSeasons = self::getTopStandingsSeasons($teamId);
        $bottomStandingsSeasons = self::getBottomStandingsSeasons($teamId);
        $playOffAppearance = self::getPlayoffAppearance($teamId);

        return response()->json([
            'topStandingsSeasons' => $topStandingsSeasons,
            'bottomStandingsSeasons' => $bottomStandingsSeasons,
            'playOffAppearance' =>  $playOffAppearance,
        ]);
    }
    public function teamseasonhistory(Request $request)
    {
        $teamId = $request->team_id;
        $page = $request->page_num ?? 1;
        $itemsPerPage = $request->itemsperpage ?? 10;

        $teamSeasonHistory = $this->getSeasonHistory($teamId, $page, $itemsPerPage);

        return response()->json($teamSeasonHistory);
    }

    private function getSeasonHistory($teamId, $page, $itemsPerPage)
    {
        // Calculate the offset for pagination
        $offset = ($page - 1) * $itemsPerPage;

        // Fetch data from database with pagination
        $seasonHistory = DB::table('standings_view')
            ->select(
                'standings_view.team_id',
                'standings_view.team_name',
                'standings_view.team_acronym',
                'standings_view.conference_id',
                'standings_view.conference_name',
                'standings_view.wins',
                'standings_view.losses',
                'standings_view.total_home_score',
                'standings_view.total_away_score',
                'standings_view.home_ppg',
                'standings_view.away_ppg',
                'standings_view.score_difference',
                'standings_view.season_id',
                'standings_view.overall_rank',
                'standings_view.conference_rank',
                'seasons.name as season_name',
                DB::raw('CASE WHEN standings_view.overall_rank <= CASE WHEN seasons.start_playoffs = 16 THEN 16 ELSE 32 END THEN TRUE ELSE FALSE END AS isPlayoffQualified'),
                DB::raw('MAX(schedules.id) as last_round_played') // Adjusted to get the last round
            )
            ->join('seasons', 'seasons.id', '=', 'standings_view.season_id')
            ->leftJoin('schedules', function ($join) use ($teamId) {
                $join->on('schedules.season_id', '=', 'standings_view.season_id')
                    ->where(function ($query) use ($teamId) {
                        $query->where('schedules.home_id', '=', $teamId)
                            ->orWhere('schedules.away_id', '=', $teamId);
                    });
            })
            ->where('standings_view.team_id', $teamId)
            ->groupBy(
                'standings_view.team_id',
                'standings_view.team_name',
                'standings_view.team_acronym',
                'standings_view.conference_id',
                'standings_view.conference_name',
                'standings_view.wins',
                'standings_view.losses',
                'standings_view.total_home_score',
                'standings_view.total_away_score',
                'standings_view.home_ppg',
                'standings_view.away_ppg',
                'standings_view.score_difference',
                'standings_view.season_id',
                'standings_view.overall_rank',
                'standings_view.conference_rank',
                'seasons.name'
            )
            ->orderBy('standings_view.season_id', 'desc')
            ->offset($offset)
            ->limit($itemsPerPage)
            ->get();



        // Process the collection and append round information
        foreach ($seasonHistory as $season) {
            $roundInfo = $this->getLastRoundPlayed($season->last_round_played, $teamId);
            $season->round_info = $roundInfo;
        }

        // Get the total number of records
        $totalItems = DB::table('standings_view')
            ->where('team_id', $teamId)
            ->count();

        // Calculate the total number of pages
        $totalPages = ceil($totalItems / $itemsPerPage);

        return [
            'history' => $seasonHistory,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
    }

    public function teamlastseason(Request $request)
    {
        $teamId = $request->team_id;

        $lastPlayInsRound1Season = self::getLastSeasonOfRound($teamId, 'play_ins_elims_round_1');
        $lastPlayInsRound2Season = self::getLastSeasonOfRound($teamId, 'play_ins_elims_round_2');
        $lastPlayInsFinalsSeason = self::getLastSeasonOfRound($teamId, 'play_ins_finals');
        $lastQuarterFinalSeason = self::getLastSeasonOfRound($teamId, 'quarter_finals');
        $lastSemiFinalSeason = self::getLastSeasonOfRound($teamId, 'semi_finals');
        $lastFinalSeason = self::getLastSeasonOfRound($teamId, 'finals');
        $lastRoundOf16Season = self::getLastSeasonOfRound($teamId, 'round_of_16');
        $lastRoundOf32Season = self::getLastSeasonOfRound($teamId, 'round_of_32');

        return response()->json([
            'lastPlayInsRound1Season' =>  $lastPlayInsRound1Season,
            'lastPlayInsRound2Season' =>  $lastPlayInsRound2Season,
            'lastPlayInsFinalsSeason' =>  $lastPlayInsFinalsSeason,
            'lastRoundOf32Season' =>  $lastRoundOf32Season,
            'lastRoundOf16Season' =>  $lastRoundOf16Season,
            'lastQuarterFinalSeason' => $lastQuarterFinalSeason,
            'lastSemiFinalSeason' => $lastSemiFinalSeason,
            'lastFinalSeason' => $lastFinalSeason,
        ]);
    }
    public function teammatches(Request $request)
    {
        $teamId = $request->team_id;

        $lastTenGames = self::getLastTenGames($teamId);

        return response()->json([
            'lastTenGames' => $lastTenGames,
        ]);
    }
    public function teammatchesh2h(Request $request)
    {
        $teamId = $request->team_id;

        $headToHeadBattles = self::headToHead($teamId);

        return response()->json([
            'headToHeadBattles' => $headToHeadBattles,
        ]);
    }
    public function teamrivals(Request $request)
    {
        $teamId = $request->team_id;

        $topRivals = $this->getTopRivals($teamId);
        $winLossRecords = $this->getWinLossRecords($teamId, $topRivals);

        return  response()->json([
            'top_rivals' => $winLossRecords,
        ]);
    }
    private function getTeamInfo($teamId)
    {
        return DB::table('teams')
            ->join('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->select('teams.name as team_name', 'teams.acronym', 'teams.id', 'conferences.name as conference_name')
            ->where('teams.id', $teamId)
            ->get();
    }

    private function getTeamStreaks($teamId)
    {
        return DB::table('streak')
            ->where('team_id', $teamId)
            ->get();
    }


    private function getAllTimeStats($teamId)
    {
        return DB::table('standings_view')
            ->where('team_id', $teamId)
            ->selectRaw('SUM(wins) AS all_time_wins, SUM(losses) AS all_time_losses')
            ->first();
    }
    private function getSeasonHistoryv1($teamId)
    {
        // Fetch data from database
        $seasonHistory = DB::table('standings_view')
            ->select(
                'standings_view.*',
                'seasons.name as season_name',
                DB::raw('CASE WHEN standings_view.overall_rank <= 16 THEN TRUE ELSE FALSE END AS isPlayoffQualified'),
                DB::raw('MAX(schedules.id) as last_round_played_id'),
            )
            ->join('seasons', 'seasons.id', '=', 'standings_view.season_id')
            ->leftJoin('schedules', function ($join) use ($teamId) {
                $join->on('schedules.season_id', '=', 'standings_view.season_id')
                    ->where(function ($query) use ($teamId) {
                        $query->where('schedules.home_id', '=', $teamId)
                            ->orWhere('schedules.away_id', '=', $teamId);
                    });
            })
            ->where('standings_view.team_id', $teamId)
            ->groupBy('standings_view.season_id', 'seasons.id', 'seasons.name', 'standings_view.overall_rank', 'isPlayoffQualified')
            ->orderBy('standings_view.season_id', 'desc')
            ->get();

        // Process the collection and append round information
        foreach ($seasonHistory as $season) {
            $roundInfo = $this->getLastRoundPlayed($season->last_round_played_id, $teamId);
            $season->round_info = $roundInfo;
        }

        return $seasonHistory;
    }
    private function getSeasonHistoryCount($teamId, $seasonId)
    {
        // Fetch data from database
        $seasonHistory = DB::table('standings_view')
            ->select(
                'standings_view.*',
                'seasons.name as season_name',
                'conference_championships as conference_championship',
                DB::raw('CASE WHEN standings_view.overall_rank <= CASE WHEN seasons.start_playoffs = 16 THEN 16 ELSE 32 END THEN TRUE ELSE FALSE END AS isPlayoffQualified'),
                DB::raw('MAX(schedules.id) as last_round_played_id'),
            )
            ->join('seasons', 'seasons.id', '=', 'standings_view.season_id')
            ->leftJoin('schedules', function ($join) use ($teamId) {
                $join->on('schedules.season_id', '=', 'standings_view.season_id')
                    ->where(function ($query) use ($teamId) {
                        $query->where('schedules.home_id', '=', $teamId)
                            ->orWhere('schedules.away_id', '=', $teamId);
                    });
            })
            ->where('standings_view.team_id', $teamId)
            ->groupBy('standings_view.season_id', 'seasons.id', 'seasons.name', 'standings_view.overall_rank', 'isPlayoffQualified')
            ->orderBy('standings_view.season_id', 'desc')
            ->get();

        // Process the collection and append round information
        foreach ($seasonHistory as $season) {
            $roundInfo = $this->getLastRoundPlayed($season->last_round_played_id, $teamId);
            $season->round_info = $roundInfo;
        }

        // Count various conditions
        $playoffQualifiedCount = $seasonHistory->filter(function ($season) {
            return $season->isPlayoffQualified == 1;
        })->count();

        $overallRank1Count = $seasonHistory->filter(function ($season) {
            return $season->overall_rank == 1;
        })->count();

        $conferenceRank1Count = $seasonHistory->filter(function ($season) {
            return $season->conference_rank == 1;
        })->count();


        $seasonCount = DB::table('standings_view')
            ->where('season_id', $seasonId)
            ->count();


        // Count the standings_view where season_id = $seasonId to get the last team number
        $lastOverallRankCount = $seasonHistory->filter(function ($season) use ($teamId, $seasonCount) {
            // Compare overall_rank with the count of standings for the team in the season
            return $season->team_id == $teamId && $season->overall_rank == $seasonCount;
        })->count();

        return [
            // 'seasonHistory' => $seasonHistory,
            'playoffQualifiedCount' => $playoffQualifiedCount,
            'overallRank1Count' => $overallRank1Count,
            'conferenceRank1Count' => $conferenceRank1Count,
            'lastOverallRankCount' => $lastOverallRankCount,
            'conferenceChampions' => $season->conference_championship,
        ];
    }

    private function getLastRoundPlayedv1($lastRoundPlayedId, $teamId)
    {
        // Retrieve the round and result information based on last_round_played_id
        $roundInfo = DB::table('schedules')
            ->select('round', 'home_score', 'away_score', 'home_id', 'away_id')
            ->where('id', $lastRoundPlayedId)
            ->first();

        if (!$roundInfo) {
            return null; // Handle case where no round info found
        }

        // Check if the round is finals
        if ($roundInfo->round == 'finals') {
            // Determine winner based on team's perspective
            if ($roundInfo->home_id == $teamId) {
                // If the team is home and home_score > away_score, they win
                $teamWon = ($roundInfo->home_score > $roundInfo->away_score);
            } elseif ($roundInfo->away_id == $teamId) {
                // If the team is away and away_score > home_score, they win
                $teamWon = ($roundInfo->away_score > $roundInfo->home_score);
            } else {
                // If neither home_id nor away_id matches teamId, consider as not won
                $teamWon = false;
            }

            return [
                'round' => $roundInfo->round,
                'won' => $teamWon,
                'score' => ($teamWon ? ($roundInfo->home_id == $teamId ? $roundInfo->home_score : $roundInfo->away_score) : null),
                'opponent_id' => ($teamWon ? ($roundInfo->home_id == $teamId ? $roundInfo->away_id : $roundInfo->home_id) : null)
            ];
        } else {
            return [
                'round' => $roundInfo->round,
                'won' => null, // Not applicable for non-finals rounds
                'score' => null, // Score not applicable for non-finals rounds
                'opponent_id' => null // Opponent id not applicable for non-finals rounds
            ];
        }
    }
    private function getLastRoundPlayed($lastRoundPlayedId, $teamId)
    {
        // Retrieve the round and result information based on last_round_played_id
        $roundInfo = DB::table('schedules')
            ->select('round', 'home_score', 'away_score', 'home_id', 'away_id')
            ->where('id', $lastRoundPlayedId)
            ->first();

        if (!$roundInfo) {
            return null; // Handle case where no round info found
        }

        // Check if the round is finals
        if ($roundInfo->round) {
            // Determine winner based on team's perspective
            if ($roundInfo->home_id == $teamId) {
                // If the team is home and home_score > away_score, they win
                $teamWon = ($roundInfo->home_score > $roundInfo->away_score);
                $teamScore = $roundInfo->home_score;
                $opponentScore = $roundInfo->away_score;
                $opponentId = $roundInfo->away_id;
            } elseif ($roundInfo->away_id == $teamId) {
                // If the team is away and away_score > home_score, they win
                $teamWon = ($roundInfo->away_score > $roundInfo->home_score);
                $teamScore = $roundInfo->away_score;
                $opponentScore = $roundInfo->home_score;
                $opponentId = $roundInfo->home_id;
            } else {
                // If neither home_id nor away_id matches teamId, consider as not won
                $teamWon = false;
                $teamScore = null;
                $opponentScore = null;
                $opponentId = null;
            }

            // Fetch opponent's name
            $opponentName = null;
            if ($opponentId) {
                $opponentInfo = DB::table('teams')
                    ->select('name')
                    ->where('id', $opponentId)
                    ->first();
                if ($opponentInfo) {
                    $opponentName = $opponentInfo->name;
                }
            }

            return [
                'round' => $roundInfo->round,
                'won' => $teamWon,
                'score' => $teamScore,
                'opponent_id' => $opponentId,
                'opponent_name' => $opponentName,
                'opponent_score' => $opponentScore
            ];
        } else {
            return [
                'round' => $roundInfo->round,
                'won' => null, // Not applicable for non-finals rounds
                'score' => null, // Score not applicable for non-finals rounds
                'opponent_id' => null, // Opponent id not applicable for non-finals rounds
                'opponent_name' => null, // Opponent name not applicable for non-finals rounds
                'opponent_score' => null // Opponent score not applicable for non-finals rounds
            ];
        }
    }

    private function getFinalsStats($teamId)
    {
        return DB::table('schedules')
            ->where(function ($query) use ($teamId) {
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->where('round', 'finals')
            ->selectRaw('SUM(CASE WHEN round = "finals" AND (away_id = ? AND away_score > home_score OR home_id = ? AND home_score > away_score) THEN 1 ELSE 0 END) AS finals_wins', [$teamId, $teamId])
            ->selectRaw('SUM(CASE WHEN round = "finals" AND (away_id = ? AND away_score < home_score OR home_id = ? AND home_score < away_score) THEN 1 ELSE 0 END) AS finals_losses', [$teamId, $teamId])
            ->selectRaw('COUNT(CASE WHEN round = "finals" THEN 1 END) AS finals_appearances')
            ->first();
    }

    private function getRoundStats($teamId)
    {
        return DB::table('schedules')
            ->where(function ($query) use ($teamId) {
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->selectRaw('
                COUNT(CASE WHEN round = "semi_finals" THEN 1 END) AS semi_final_appearances,
                COUNT(CASE WHEN round = "quarter_finals" THEN 1 END) AS quarter_final_appearances,
                COUNT(CASE WHEN round = "round_of_16" THEN 1 END) AS round_of_16_appearances,
                COUNT(CASE WHEN round = "round_of_32" THEN 1 END) AS round_of_32_appearances,
                COUNT(CASE WHEN round IN ("play_ins_elims_round_1", "play_ins_elims_round_2","play_ins_finals") THEN 1 END) AS play_in_appearances
            ')
            ->first();
    }

    private function getPlayoffStats($teamId)
    {
        return DB::table('schedules')
            ->where(function ($query) use ($teamId) {
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->whereIn('round', [
                'play_ins_elims_round_1',
                'play_ins_elims_round_2',
                'play_ins_finals',
                'round_of_16',
                'quarter_finals',
                'semi_finals',
                'interconference_semi_finals',
                'finals'
            ])
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id')
            ->selectRaw('SUM(CASE WHEN (round = "finals" OR round = "semi_finals" OR round = "quarter_finals" OR round = "round_of_16" OR round = "round_of_32") AND (away_id = ? AND away_score > home_score OR home_id = ? AND home_score > away_score) THEN 1 ELSE 0 END) AS playoff_wins', [$teamId, $teamId])
            ->selectRaw('SUM(CASE WHEN (round = "finals" OR round = "semi_finals" OR round = "quarter_finals" OR round = "round_of_16" OR round = "round_of_32") AND (away_id = ? AND away_score < home_score OR home_id = ? AND home_score < away_score) THEN 1 ELSE 0 END) AS playoff_losses', [$teamId, $teamId])
            ->selectRaw('COUNT(CASE WHEN (round = "round_of_16" AND seasons.start_playoffs = 16) OR (round = "round_of_32" AND seasons.start_playoffs = 32) THEN 1 END) AS playoff_appearances')
            // Add condition for play-in rounds (teams ranked 7th-10th)
            ->selectRaw('COUNT(CASE WHEN round IN ("play_ins_elims_round_1", "play_ins_elims_round_2") THEN 1 END) AS play_in_appearances')
            ->first();
    }


    private function getLastTenGames($teamId)
    {
        return DB::table('schedules')
            ->select('schedules.*', 'home_teams.name as home_team_name', 'away_teams.name as away_team_name')
            ->join('teams as home_teams', 'schedules.home_id', '=', 'home_teams.id')
            ->join('teams as away_teams', 'schedules.away_id', '=', 'away_teams.id')
            ->where(function ($query) use ($teamId) {
                $query->where('schedules.away_id', $teamId)
                    ->orWhere('schedules.home_id', $teamId);
            })
            ->where(function ($query) {
                $query->where('schedules.away_score', '>', 0)
                    ->orWhere('schedules.home_score', '>', 0);
            })
            ->orderByDesc('schedules.id')
            ->limit(10)
            ->get()
            ->map(function ($game) use ($teamId) {
                $status = '';

                if ($game->home_id == $teamId) {
                    $status = $game->home_score > $game->away_score ? 'Win' : 'Loss';
                } elseif ($game->away_id == $teamId) {
                    $status = $game->away_score > $game->home_score ? 'Win' : 'Loss';
                }

                return (object) array_merge((array) $game, ['status' => $status]);
            });
    }

    private function getLastSeasonOfRound($teamId, $round)
    {
        $lastSeasonId = DB::table('schedules')
            ->where(function ($query) use ($teamId) {
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->where('round', $round)
            ->orderByDesc('id')
            ->value('season_id');

        return DB::table('seasons')
            ->where('id', $lastSeasonId)
            ->value('name');
    }
    private function getTopStandingsSeasons($teamId)
    {
        return DB::table('seasons')
            ->where('champion_id', $teamId) // Filter for champion_id == teamId
            ->orderByDesc('id') // Order by season ID in descending order
            ->pluck('name'); // Retrieve the season names
    }
    private function getBottomStandingsSeasons($teamId)
    {
        return DB::table('seasons')
            ->where('weakest_id', $teamId) // Filter for champion_id == teamId
            ->orderByDesc('id') // Order by season ID in descending order
            ->pluck('name'); // Retrieve the season names
    }
    private function getPlayoffAppearance($teamId)
    {
        return DB::table('schedules')
            ->where(function ($query) use ($teamId) {
                // Check if the team is involved in any game (either as home or away)
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id')
            ->where(function ($query) {
                // Check for playoff rounds based on the season's start playoffs
                $query->where(function ($subQuery) {
                        // Playoff round of 16 if start playoffs is 16
                        $subQuery->where('seasons.start_playoffs', '=', 16)
                            ->where('schedules.round', '=', 'round_of_16');
                    })
                    ->orWhere(function ($subQuery) {
                        // Playoff round of 32 if start playoffs is 32
                        $subQuery->where('seasons.start_playoffs', '=', 32)
                            ->where('schedules.round', '=', 'round_of_32');
                    })
                    // Add condition to check for the play-in rounds
                    ->orWhere(function ($subQuery) {
                        // Check if the team is part of play-in rounds 1 or 2
                        $subQuery->whereIn('schedules.round', [
                            'play_ins_elims_round_1', 'play_ins_elims_round_2'
                        ]);
                    });
            })
            ->orderByDesc('seasons.id')  // Ensure to get the most recent season first
            ->distinct()  // Ensure that we only get unique season names
            ->pluck('seasons.name');  // Return the unique season names where the team participated in the playoffs
    }

    private function getFinalsSeasons($teamId)
    {
        return DB::table('schedules')
            ->where('round', 'finals')
            ->where(function ($query) use ($teamId) {
                $query->where('away_id', $teamId)
                    ->orWhere('home_id', $teamId);
            })
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id')
            ->orderByDesc('seasons.id') // Order by season ID in descending order
            ->pluck('seasons.name');
    }
    private function getFinalsWinSeasons($teamId)
    {
        return DB::table('seasons')
            ->where('finals_winner_id', $teamId)
            ->where('status', 7)
            ->pluck('seasons.name');
    }

    private function headToHead($teamId)
    {
        return DB::table('schedules')
            ->select(
                DB::raw('CASE WHEN schedules.away_id = ' . $teamId . ' THEN home_team.name ELSE away_team.name END as opponent_name'),
                DB::raw('(SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END)) as total_games'),
                DB::raw('SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score > schedules.away_score THEN 1 ELSE 0 END) as home_wins'),
                DB::raw('SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score < schedules.away_score THEN 1 ELSE 0 END) as home_losses'),
                DB::raw('SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END) as away_wins'),
                DB::raw('SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score < schedules.home_score THEN 1 ELSE 0 END) as away_losses'),
                DB::raw('ROUND(((SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score > schedules.away_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END)) / (SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END))) * 100, 2) as home_win_percentage'),
                DB::raw('ROUND(((SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score < schedules.away_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score < schedules.home_score THEN 1 ELSE 0 END)) / (SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END))) * 100, 2) as home_loss_percentage'),
                DB::raw('ROUND(((SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score > schedules.away_score THEN 1 ELSE 0 END)) / (SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END))) * 100, 2) as away_win_percentage'),
                DB::raw('ROUND(((SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score < schedules.home_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score < schedules.away_score THEN 1 ELSE 0 END)) / (SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END))) * 100, 2) as away_loss_percentage'),
                DB::raw('ROUND((((SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score > schedules.away_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END)) + (SUM(CASE WHEN schedules.home_id = ' . $teamId . ' AND schedules.home_score < schedules.away_score THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' AND schedules.away_score < schedules.home_score THEN 1 ELSE 0 END))) / (SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END))) * 100, 2) as overall_win_percentage')
            )
            ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id')
            ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id')
            ->where('schedules.home_id', $teamId)
            ->orWhere('schedules.away_id', $teamId)
            ->groupBy('opponent_name')
            ->get();
    }

    private function getTopRivals1($teamId)
    {
        return DB::table('schedules')
            ->select(
                DB::raw('CASE WHEN schedules.away_id = ' . $teamId . ' THEN home_team.name ELSE away_team.name END as opponent_name'),
                DB::raw('(SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END)) as total_games')
            )
            ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id')
            ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id')
            ->where('schedules.home_id', $teamId)
            ->orWhere('schedules.away_id', $teamId)
            ->groupBy('opponent_name')
            ->orderBy('total_games', 'desc') // Order by total games played against each opponent in descending order
            ->limit(4) // Limit to top 3 rivals
            ->pluck('opponent_name');
    }
    private function getTopRivals($teamId)
    {
        return DB::table('schedules')
            ->select(
                DB::raw('CASE WHEN schedules.away_id = ' . $teamId . ' THEN home_team.name ELSE away_team.name END as opponent_name'),
                DB::raw('(SUM(CASE WHEN schedules.home_id = ' . $teamId . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN schedules.away_id = ' . $teamId . ' THEN 1 ELSE 0 END)) as total_games')
            )
            ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id')
            ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id')
            ->where(function ($query) use ($teamId) {
                $query->where('schedules.home_id', $teamId)
                    ->orWhere('schedules.away_id', $teamId);
            })
            ->whereIn('schedules.round', config('playoffs'))
            ->groupBy('opponent_name')
            ->orderBy('total_games', 'desc')
            ->limit(5)
            ->pluck('opponent_name');
    }

    private function getWinLossRecords($teamId, $rivals)
    {
        $records = [];
        foreach ($rivals as $rival) {
            $wins = DB::table('schedules')
                ->select(
                    'teams_home.name as home_team_name',
                    'teams_away.name as away_team_name',
                    'schedules.home_id',
                    'schedules.away_id',
                    'schedules.home_score',
                    'schedules.away_score',
                )
                ->leftJoin('teams as teams_home', 'schedules.home_id', '=', 'teams_home.id')
                ->leftJoin('teams as teams_away', 'schedules.away_id', '=', 'teams_away.id')
                // ->where('round', 'finals')
                ->where(function ($query) use ($teamId, $rival) {
                    $query->where(function ($query) use ($teamId, $rival) {
                        $query->where('away_id', $teamId)
                            ->where('teams_home.name', $rival);
                    })
                        ->orWhere(function ($query) use ($teamId, $rival) {
                            $query->where('home_id', $teamId)
                                ->where('teams_away.name', $rival);
                        });
                })
                ->where(function ($query) use ($teamId) {
                    $query->where('home_id', $teamId)
                        ->orWhere('away_id', $teamId);
                })
                ->get();

            $winsCount = 0;
            foreach ($wins as $win) {
                $winner = $win->home_score > $win->away_score ? $win->home_id : $win->away_id;
                if ($winner == $teamId) {
                    $winsCount++;
                }
            }

            $losses = count($wins) - $winsCount;

            $records[] = [
                'team_name' => $rival,
                'wins' => $winsCount,
                'losses' => $losses,
                'home_id' => $winsCount > $losses ? $teamId : null,
                'away_id' => $winsCount < $losses ? $teamId : null
            ];
        }

        return $records;
    }


    // Store a newly created resource in storage.
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'acronym' => 'required',
            'league_id' => 'required|exists:leagues,id', // Assuming there's a leagues table
            'conference_id' => 'required|exists:conferences,id', // Assuming there's a conferences table
        ]);

        // Generate random color hex codes for primary and secondary colors
        $primaryColor = $this->generateRandomColor();
        $secondaryColor = $this->generateRandomColor($primaryColor); // Ensure it’s different from primary

        // Merge the colors with the request data
        $data = $request->all();
        $data['primary_color'] = $primaryColor;
        $data['secondary_color'] = $secondaryColor;

        // Create the new team with the randomized colors
        Teams::create($data);

        return redirect()->route('teams.index');
    }

    /**
     * Generate a random hex color.
     * If a different color is needed, a second parameter is passed to ensure it's not the same.
     */
    private function generateRandomColor($excludeColor = null)
    {
        do {
            // Generate a random hex color (e.g., #FF5733)
            $color = sprintf('%06X', mt_rand(0, 0xFFFFFF));
        } while ($color === $excludeColor); // Ensure the generated color is not the same as the excluded one

        return $color;
    }

    // Update the specified resource in storage.
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required',
            'acronym' => 'required|max:3',
            'league_id' => 'required|exists:leagues,id',
            'conference_id' => 'required|exists:conferences,id' // Assuming there's a conferences table
        ]);

        $team = Teams::findOrFail($request->id);
        $team->update($request->all());

        return redirect()->route('teams.index');
    }

    // Remove the specified resource from storage.
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $team = Teams::findOrFail($request->id);
        $team->delete();

        return redirect()->route('teams.index');
    }
}
