<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Teams;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Index');
    }
    public function champions(Request $request)
    {
        // Retrieve pagination parameters from the request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('current_page', 1); // Default page to 1 if not provided

        $offset = ($page - 1) * $perPage;

        // Query to count championships, runner-ups, and last finals appearance of each team
        $teamStatsQuery = DB::table('teams')
            ->select(
                'teams.id',
                'teams.name',
                'teams.acronym',
                'conferences.name as conference_name', // Add conference name to select
                DB::raw('COUNT(DISTINCT CASE WHEN seasons.finals_winner_id = teams.id THEN seasons.id END) AS championships'),
                DB::raw('COUNT(DISTINCT CASE WHEN seasons.finals_loser_id = teams.id THEN seasons.id END) AS runnerups'),
                DB::raw('MAX(CASE WHEN seasons.finals_winner_id = teams.id OR seasons.finals_loser_id = teams.id THEN seasons.name ELSE NULL END) AS last_finals_appearance')
            )
            ->leftJoin('seasons', function ($join) {
                $join->on('teams.id', '=', 'seasons.finals_winner_id')
                    ->orWhere('teams.id', '=', 'seasons.finals_loser_id');
            })
            ->leftJoin('conferences', 'teams.conference_id', '=', 'conferences.id') // Join conferences table
            ->groupBy('teams.id', 'teams.name', 'teams.acronym', 'conferences.name') // Group by team columns and conference name
            ->havingRaw('COALESCE(championships, 0) > 0 OR COALESCE(runnerups, 0) > 0'); // Filter teams with at least one championship or runner-up


        // Count total number of records
        $totalCount = $teamStatsQuery->get()->count();

        // Fetch paginated team statistics
        $teamStats = $teamStatsQuery->orderByDesc('championships')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Cache the response
        $response = [
            'data' => $teamStats,
            'current_page' => $page,
            'total_pages' => ceil($totalCount / $perPage),
            'total' => $totalCount,
        ];

        // Return the response
        return response()->json($response);
    }

    public function recent(Request $request)
    {
        // Query to fetch the recent entries from schedule_view where either home_score or away_score is greater than 0
        $recentSchedule = DB::table('schedule_view')
            ->where('home_score', '>', 0)
            ->orWhere('away_score', '>', 0)
            ->orderBy('id', 'desc') // Order by id in descending order to get the latest entries first
            ->take(12) // Retrieve only the latest 10 entries
            ->get();

        return response()->json([
            'data' => $recentSchedule,
        ]);
    }
    public function get_rivalries()
    {
        $rivalries = DB::table('schedules')
            ->select(
                DB::raw('LEAST(home_team.name, away_team.name) as team1'),
                DB::raw('GREATEST(home_team.name, away_team.name) as team2'),
                DB::raw('SUM(CASE WHEN schedules.home_id = home_team.id AND schedules.home_score > schedules.away_score THEN 1 WHEN schedules.away_id = home_team.id AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END) as wins_team1'),
                DB::raw('SUM(CASE WHEN schedules.home_id = away_team.id AND schedules.home_score > schedules.away_score THEN 1 WHEN schedules.away_id = away_team.id AND schedules.away_score > schedules.home_score THEN 1 ELSE 0 END) as wins_team2'),
                DB::raw('COUNT(*) as total_games')
            )
            ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id')
            ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id')
            ->whereIn('schedules.round', ['round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals', 'finals'])
            ->groupBy('team1', 'team2')
            ->having(DB::raw('COUNT(*)'), '>=', 2)  // Only include teams that have faced each other at least 2 times
            ->orderBy('total_games', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $rivalries,
        ]);
    }
    public function playoff_appearances()
    {
        $teams = DB::table('schedules')
            ->select(
                'teams.name as team_name',
                'conferences.name as conference_name',
                DB::raw('COUNT(DISTINCT CONCAT(schedules.season_id, schedules.round)) as playoff_appearances')
            )
            ->join('teams', function ($join) {
                $join->on('schedules.home_id', '=', 'teams.id')
                    ->orOn('schedules.away_id', '=', 'teams.id');
            })
            ->join('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->whereIn('schedules.round', ['round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals', 'finals'])
            ->groupBy('teams.name', 'conferences.name')
            ->orderBy('playoff_appearances', 'desc')
            ->limit(16)
            ->get();

        return response()->json([
            'data' => $teams,
        ]);
    }

    public function topscorerteams(Request $request)
    {
        // Extracting per_page and current_page from request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('current_page', 1); // Default page to 1 if not provided

        // Calculating the offset to skip records
        $offset = ($page - 1) * $perPage;

        // Query to fetch all entries from schedule_view and sum up scores for each team
        $scoreAlltime = DB::table('schedule_view')
            ->select('teams.name', 'conferences.name as conference', DB::raw('SUM(home_score + away_score) as total_score'))
            ->leftJoin('teams', function ($join) {
                $join->on('schedule_view.home_id', '=', 'teams.id')
                    ->orOn('schedule_view.away_id', '=', 'teams.id');
            })
            ->leftJoin('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->groupBy('teams.name', 'conferences.name')
            ->orderBy('total_score', 'desc') // Sort by total score in descending order
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Count total records
        $totalCount = DB::table('teams')->count();

        // Calculate total pages
        $totalPages = ceil($totalCount / $perPage);

        // Create the response array
        $response = [
            'data' => $scoreAlltime,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $totalCount,
        ];

        return response()->json($response);
    }

    public function topscorerplayers(Request $request)
    {
        // Extracting per_page and current_page from request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('current_page', 1); // Default page to 1 if not provided

        // Calculating the offset to skip records
        $offset = ($page - 1) * $perPage;

        // Query to fetch all entries from player_game_stats and sum up scores for each team
        $scoreAlltime = DB::table('player_game_stats')
            ->select('players.name as player_name', 'teams.name as team_name', DB::raw('SUM(player_game_stats.points) as total_score'))
            ->leftJoin('players', 'player_game_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->groupBy('players.name', 'teams.name')
            ->orderBy('total_score', 'desc') // Sort by total score in descending order
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Count total records (total number of player_game_stats entries)
        $totalCount = DB::table('player_game_stats')->count();

        // Calculate total pages
        $totalPages = ceil($totalCount / $perPage);

        // Create the response array
        $response = [
            'data' => $scoreAlltime,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $totalCount,
        ];

        return response()->json($response);
    }
}
