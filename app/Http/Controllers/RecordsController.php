<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Teams;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecordsController extends Controller
{
    public function index()
    {
        return Inertia::render('Records/Index', [
            'status' => session('status'),
        ]);
    }
    public function champions(Request $request)
    {
        // Retrieve pagination parameters from the request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('page_num', 1); // Default page to 1 if not provided

        $offset = ($page - 1) * $perPage;

        // Query to count championships, runner-ups, and last finals appearance of each team
        $teamStatsQuery = DB::table('teams')
            ->select(
                'teams.id',
                'teams.name',
                'teams.acronym',
                'teams.primary_color',
                'teams.secondary_color',
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
            ->groupBy('teams.id', 'teams.name', 'teams.acronym','teams.primary_color','teams.secondary_color', 'conferences.name') // Group by team columns and conference name
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
            ->whereIn('schedules.round', config('playoffs'))
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
            ->whereIn('schedules.round', config('playoffs'))
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
        // Extracting per_page, current_page, and sort_by from request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('page_num', 1); // Default page to 1 if not provided
        $sortBy = $request->input('sort_by', 'total_points'); // Default sort by total points if not provided

        // Valid sort fields to prevent SQL injection
        $validSortFields = [
            'total_points',
            'total_rebounds',
            'total_assists',
            'total_steals',
            'total_blocks',
            'total_turnovers',
            'total_fouls',
        ];

        // Ensure sort_by is valid
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'total_points';
        }

        // Calculating the offset to skip records
        $offset = ($page - 1) * $perPage;

        // Query to fetch teams and aggregate player stats per team
        $scoreAlltime = DB::table('player_season_stats')
            ->select(
                'teams.name',
                'teams.primary_color',
                'teams.secondary_color',
                'conferences.name as conference',
                DB::raw('SUM(player_season_stats.total_points) as total_points'),
                DB::raw('SUM(player_season_stats.total_rebounds) as total_rebounds'),
                DB::raw('SUM(player_season_stats.total_assists) as total_assists'),
                DB::raw('SUM(player_season_stats.total_steals) as total_steals'),
                DB::raw('SUM(player_season_stats.total_blocks) as total_blocks'),
                DB::raw('SUM(player_season_stats.total_turnovers) as total_turnovers'),
                DB::raw('SUM(player_season_stats.total_fouls) as total_fouls')
            )
            ->leftJoin('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->leftJoin('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->groupBy('teams.id', 'teams.name', 'teams.primary_color', 'teams.secondary_color', 'conferences.name')
            ->orderBy($sortBy, 'desc') // Sort dynamically based on the requested stat
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

    public function statsleaders(Request $request)
    {
        // Extract parameters from request with default values
        $perPage = max(1, (int) $request->input('itemsperpage', 10)); // Default items per page to 10
        $page = max(1, (int) $request->input('page_num', 1)); // Default page number to 1
        $sortBy = $request->input('sort_by', 'total_points'); // Default sort column to 'total_points'

        // Ensure valid sort column
        $validSortColumns = [
            'total_points',
            'total_rebounds',
            'total_assists',
            'total_steals',
            'total_blocks',
            'total_turnovers',
            'total_fouls'
        ];
        if (!in_array($sortBy, $validSortColumns)) {
            return response()->json([
                'error' => 'Invalid sort_by parameter. Valid options are: ' . implode(', ', $validSortColumns)
            ], 400);
        }

        // Calculate offset for pagination
        $offset = ($page - 1) * $perPage;

        // Query to fetch statistics from player_season_stats
        $statsAlltime = DB::table('player_season_stats')
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw("SUM(player_season_stats.$sortBy) as total_stat") // Aggregate the chosen stat for each player
            )
            ->leftJoin('players', 'player_season_stats.player_id', '=', 'players.id') // Join players
            ->leftJoin('teams', 'teams.id', '=', 'players.team_id') // Join players
            ->groupBy('players.id', 'players.name','teams.name')                                  // Group by player_id and player_name
            ->orderBy('total_stat', 'desc')                                          // Order by total_stat in descending order
            ->skip($offset)                                                          // Offset for pagination
            ->take($perPage)                                                         // Limit results per page
            ->get()
            ->map(function ($item, $index) use ($offset) {
                // Add rank to each item based on the pagination offset
                $item->rank = $offset + $index + 1;
                return $item;
            });

    

        // Count total unique players with stats
        $totalCount = DB::table('player_season_stats')
            ->select('player_id')
            ->distinct()
            ->count();

        // Calculate total pages
        $totalPages = ceil($totalCount / $perPage);

        // Create the response array
        $response = [
            'data' => $statsAlltime,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $totalCount,
            'sort_by' => $sortBy,
        ];

        return response()->json($response);
    }

    public function winningestteams(Request $request)
    {
        // Extracting per_page and current_page from request
        $perPage = $request->input('per_page', 10); // Default per page to 10 if not provided
        $page = $request->input('page_num', 1); // Default page to 1 if not provided

        // Calculating the offset to skip records
        $offset = ($page - 1) * $perPage;

        // Query to fetch team statistics
        $teamsStats = DB::table('standings_view')
            ->select('teams.id as team_id', 'teams.name','teams.primary_color','teams.secondary_color', 'conferences.name as conference',
                     DB::raw('SUM(wins) as total_wins'),
                     DB::raw('SUM(losses) as total_losses'),
                     DB::raw('IFNULL((SUM(wins) / NULLIF(SUM(wins) + SUM(losses), 0)) * 100, 0) as win_rate'))
            ->leftJoin('teams', 'standings_view.team_id', '=', 'teams.id')
            ->leftJoin('conferences', 'teams.conference_id', '=', 'conferences.id')
            ->groupBy('teams.id', 'teams.name', 'conferences.name','teams.primary_color','teams.secondary_color')
            ->orderBy('total_wins', 'desc') // Sort by total wins in descending order
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Count total records (number of distinct teams in standings_view)
        $totalCount = DB::table('standings_view')
            ->distinct('team_id')
            ->count('team_id');

        // Calculate total pages
        $totalPages = ceil($totalCount / $perPage);

        // Query to get best and worst seasons for each team
        // $teamSeasons = DB::table('standings_view')
        //     ->select('standings_view.team_id', 'standings_view.season_id',
        //              'seasons.name as season_name',
        //              DB::raw('SUM(wins) as total_wins'),
        //              DB::raw('SUM(losses) as total_losses'),
        //              DB::raw('IFNULL((SUM(wins) / NULLIF(SUM(wins) + SUM(losses), 0)) * 100, 0) as win_rate'))
        //     ->leftJoin('seasons', 'standings_view.season_id', '=', 'seasons.id') // Join with seasons table
        //     ->where('seasons.status',8)
        //     ->groupBy('standings_view.team_id', 'standings_view.season_id', 'seasons.name')
        //     ->orderBy('standings_view.team_id', 'asc'); // For grouping by team_id

        // // Determine best and worst seasons per team
        // $bestSeasons = $teamSeasons->clone()->orderBy('win_rate', 'desc')->get()->groupBy('team_id')->map(function ($seasons) {
        //     return $seasons->first(); // Best winning season (highest percentage) for each team
        // });

        // $worstSeasons = $teamSeasons->clone()->orderBy('win_rate', 'asc')->get()->groupBy('team_id')->map(function ($seasons) {
        //     return $seasons->first(); // Worst winning season (lowest percentage) for each team
        // });

        // Combine team stats with best and worst seasons
        // $teamsWithSeasons = $teamsStats->map(function ($team) use ($) {
        //     $bestSeason = $bestSeasons->get($team->team_id);
        //     $worstSeason = $worstSeasons->get($team->team_id);

        //     return [
        //         'team_name' => $team->name,
        //         'conference' => $team->conference,
        //         'total_wins' => $team->total_wins,
        //         'total_losses' => $team->total_losses,
        //         'win_rate' => $team->win_rate,
        //         // 'best_season' => $bestSeason ? $bestSeason->season_name : 'N/A',
        //         // 'best_win_loss' => $bestSeason ? $bestSeason->total_wins . "-" . $bestSeason->total_losses : 'N/A',
        //         // 'worst_season' => $worstSeason ? $worstSeason->season_name : 'N/A',
        //         // 'worst_win_loss' => $worstSeason ? $worstSeason->total_wins . "-" . $worstSeason->total_losses : 'N/A',
        //     ];
        // });

        // Create the response array
        $response = [
            'data' => $teamsStats,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $totalCount,
        ];

        return response()->json($response);
    }
    public function updatePlayerPlayoffAppearances(Request $request)
    {
        $seasonId = $request->season_id;
    
        // Retrieve player playoff statistics for the given season
        $playerData = DB::table('players AS p')
            ->leftJoin('player_game_stats AS pg', 'p.id', '=', 'pg.player_id')
            ->leftJoin('schedules AS s', 'pg.game_id', '=', 's.game_id')
            ->leftJoin('teams AS t', 'pg.team_id', '=', 't.id')
            ->leftJoin('teams AS t2', 'p.team_id', '=', 't2.id')
            ->leftJoin(DB::raw('(SELECT DISTINCT player_id, season_id FROM player_game_stats) AS all_s'), 'all_s.player_id', '=', 'p.id')
            ->where('all_s.season_id', $seasonId)  // Filter by season_id
            ->whereIn('s.round', [
                'play_ins_elims_round_1', 'play_ins_elims_round_2', 'play_ins_finals',
                'round_of_32', 'round_of_16', 'quarter_finals', 'semi_finals',
                'interconference_semi_finals', 'finals'
            ])
            ->where('s.season_id', $seasonId) // Ensure we're filtering by the correct season in the schedules table
            ->select([
                'p.id AS player_id',
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "play_ins_elims_round_1" THEN s.game_id END) AS play_ins_elims_round_1_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "play_ins_elims_round_2" THEN s.game_id END) AS play_ins_elims_round_2_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "play_ins_finals" THEN s.game_id END) AS play_ins_finals_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "round_of_32" THEN s.game_id END) AS round_of_32_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "round_of_16" THEN s.game_id END) AS round_of_16_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "quarter_finals" THEN s.game_id END) AS quarter_finals_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "semi_finals" THEN s.game_id END) AS semi_finals_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "interconference_semi_finals" THEN s.game_id END) AS interconference_semi_finals_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "finals" THEN s.game_id END) AS finals_appearances'),
                DB::raw('COUNT(DISTINCT s.game_id) AS total_playoff_appearances'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round IN ("play_ins_elims_round_1", "play_ins_elims_round_2", "play_ins_finals", "round_of_32", "round_of_16", "quarter_finals", "semi_finals", "interconference_semi_finals", "finals") THEN s.season_id END) AS seasons_played_in_playoffs'),
                DB::raw('COUNT(DISTINCT all_s.season_id) AS total_seasons_played'),
                DB::raw('COUNT(DISTINCT CASE WHEN s.round = "finals" AND ((pg.team_id = s.home_id AND s.home_score > s.away_score) OR (pg.team_id = s.away_id AND s.away_score > s.home_score)) THEN s.game_id END) AS championships_won')
            ])
            ->groupBy('p.id', 'all_s.season_id') // Group by both player and season to avoid over-counting
            ->get();
    
        // Insert or update the data for each player in the player_playoff_appearances table
        foreach ($playerData as $data) {
            DB::table('player_playoff_appearances')->updateOrInsert(
                [
                    'player_id' => $data->player_id,
                ],
                [
                    'play_ins_elims_round_1_appearances' => DB::raw("IFNULL(play_ins_elims_round_1_appearances, 0) + {$data->play_ins_elims_round_1_appearances}"),
                    'play_ins_elims_round_2_appearances' => DB::raw("IFNULL(play_ins_elims_round_2_appearances, 0) + {$data->play_ins_elims_round_2_appearances}"),
                    'play_ins_finals_appearances' => DB::raw("IFNULL(play_ins_finals_appearances, 0) + {$data->play_ins_finals_appearances}"),
                    'round_of_32_appearances' => DB::raw("IFNULL(round_of_32_appearances, 0) + {$data->round_of_32_appearances}"),
                    'round_of_16_appearances' => DB::raw("IFNULL(round_of_16_appearances, 0) + {$data->round_of_16_appearances}"),
                    'quarter_finals_appearances' => DB::raw("IFNULL(quarter_finals_appearances, 0) + {$data->quarter_finals_appearances}"),
                    'semi_finals_appearances' => DB::raw("IFNULL(semi_finals_appearances, 0) + {$data->semi_finals_appearances}"),
                    'interconference_semi_finals_appearances' => DB::raw("IFNULL(interconference_semi_finals_appearances, 0) + {$data->interconference_semi_finals_appearances}"),
                    'finals_appearances' => DB::raw("IFNULL(finals_appearances, 0) + {$data->finals_appearances}"),
                    'total_playoff_appearances' => DB::raw("IFNULL(total_playoff_appearances, 0) + {$data->total_playoff_appearances}"),
                    'seasons_played_in_playoffs' => DB::raw("IFNULL(seasons_played_in_playoffs, 0) + {$data->seasons_played_in_playoffs}"),
                    'total_seasons_played' => DB::raw("IFNULL(total_seasons_played, 0) + {$data->total_seasons_played}"),
                    'championships_won' => DB::raw("IFNULL(championships_won, 0) + {$data->championships_won}")
                ]
            );
        }

        return response()->json(['message' => 'Success update in season '. $seasonId]);
    }
    
}
