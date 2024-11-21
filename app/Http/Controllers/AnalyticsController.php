<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use App\Models\Seasons;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Analytics/Index', [
            'status' => session('status'),
        ]);
    }

    public function get_all_standings(Request $request)
    {
        $request->validate([
            'conference_id' => 'required|integer',
            'team_id' => 'required|integer',
        ]);

        // Fetch all records from standings_view and join with seasons and teams tables
        $standings = DB::table('standings_view')
            ->join('seasons', 'standings_view.season_id', '=', 'seasons.id')
            ->join('teams', 'standings_view.team_id', '=', 'teams.id') // Join with teams table to get colors
            ->select(
                'standings_view.team_name',
                'seasons.name AS season',
                'standings_view.wins',
                'teams.primary_color',
                'teams.secondary_color'
            );

        // Check if conference_id is 0, then conditionally add the where clause
        if ($request->conference_id > 0) {
            $standings->where('standings_view.conference_id', $request->conference_id);
        }
        if ($request->team_id > 0) {
            $standings->where('standings_view.team_id', $request->team_id);
        }

        $standings = $standings->get();

        // Structure data for line chart
        $structuredData = [];

        foreach ($standings as $record) {
            $team = $record->team_name;
            $season = $record->season;
            $wins = $record->wins;
            $primaryColor = '#' . ltrim($record->primary_color, '#'); // Add # to primary color
            $secondaryColor = '#' . ltrim($record->secondary_color, '#'); // Add # to secondary color

            // Initialize team entry if it doesn't exist
            if (!isset($structuredData[$team])) {
                $structuredData[$team] = [
                    'color' => $primaryColor,
                    'secondaryColor' => $secondaryColor,
                    'winsData' => []
                ];
            }

            // Add the win data for the specific season
            $structuredData[$team]['winsData'][$season] = $wins;
        }

        // Prepare the final data for the line chart
        $seasons = array_unique(array_reduce($structuredData, function ($carry, $item) {
            return array_merge($carry, array_keys($item['winsData']));
        }, []));

        $finalData = [
            'labels' => array_values($seasons), // Seasons as labels
            'datasets' => []
        ];

        foreach ($structuredData as $team => $data) {
            $dataset = [
                'label' => $team,
                'data' => array_map(function ($season) use ($data) {
                    return $data['winsData'][$season] ?? 0; // Default to 0 if no wins recorded
                }, $seasons),
                'fill' => false,
                'borderColor' => $data['color'], // Use the team's primary color with #
                'backgroundColor' => $data['secondaryColor'], // Use the team's secondary color with #
            ];

            $finalData['datasets'][] = $dataset;
        }

        return response()->json($finalData); // Return JSON response for chart
    }


    public function count_players()
    {
        // Count total players
        $totalPlayers = DB::table('players')->count();

        // Count active players
        $activePlayers = DB::table('players')
            ->where('is_active', 1)
            ->count();

        // Count retired players
        $retiredPlayers = DB::table('players')
            ->where('is_active', 0)
            ->count();

        // Count rookie players
        $rookiePlayers = DB::table('players')
            ->where('is_rookie', 1)
            ->count();

        // Count free agents
        $freeAgents = DB::table('players')
            ->where('is_active', 1)
            ->where('team_id', 0)
            ->count();

        // Count unique teams with players (team_id > 0)
        $totalTeams = DB::table('teams')
            ->distinct('id')
            ->count('team_id');

        // Count active players in teams (team_id > 0)
        $activePlayersInTeams = DB::table('players')
            ->where('is_active', 1)
            ->where('team_id', '>', 0)
            ->count();

        // Define max roster size
        $maxRosterSize = 15;

        // Calculate total available slots
        $totalAvailableSlots = ($totalTeams * $maxRosterSize) - $activePlayersInTeams;

        return response()->json([
            'total_players' => $totalPlayers,
            'active_players' => $activePlayers,
            'retired_players' => $retiredPlayers,
            'rookie_players' => $rookiePlayers,
            'free_agents' => $freeAgents,
            'active_players_with_team' => $activePlayersInTeams,
            'total_available_slots' => $totalAvailableSlots,
        ]);
    }

    public function getSeasonLeadersV1(Request $request)
    {
        // Determine the type of leader to return based on the request
        $leaderType = $request->input('leader_type', 'mvp_leaders'); // Default to 'mvp_leaders'
        $seasonId =  $request->input('season_id', $this->getLatestSeasonId());
        $excludedRounds = ['play_ins_elims_round_1', 'play_ins_elims_round_2', 'play_ins_finals', 'quarter_finals', 'round_of_16', 'round_of_32', 'semi_finals', 'interconference_semi_finals', 'finals'];

        // Fetch player stats for the given season and conference
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->where('player_game_stats.season_id', $seasonId)
            ->whereNotIn('schedules.round', $excludedRounds)
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.draft_status as draft_status',
                'players.team_id',
                'teams.name as team_name',
                'players.is_rookie',
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('SUM(player_game_stats.minutes) as total_minutes'),
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played')
            )
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'players.is_rookie', 'players.draft_status')
            ->get();

        $formattedPlayerStats = [];

        foreach ($playerStats as $stats) {
            $gamesPlayed = $stats->games_played;

            $averagePointsPerGame = $gamesPlayed > 0 ? $stats->total_points / $gamesPlayed : 0;
            $averageReboundsPerGame = $gamesPlayed > 0 ? $stats->total_rebounds / $gamesPlayed : 0;
            $averageAssistsPerGame = $gamesPlayed > 0 ? $stats->total_assists / $gamesPlayed : 0;
            $averageBlocksPerGame = $gamesPlayed > 0 ? $stats->total_blocks / $gamesPlayed : 0;
            $averageStealsPerGame = $gamesPlayed > 0 ? $stats->total_steals / $gamesPlayed : 0;
            $averageTurnoversPerGame = $gamesPlayed > 0 ? $stats->total_turnovers / $gamesPlayed : 0;
            $averageFoulsPerGame = $gamesPlayed > 0 ? $stats->total_fouls / $gamesPlayed : 0;

            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'is_rookie' => $stats->is_rookie, // Check if rookie
                'draft_status' => $stats->draft_status, // Check if rookie
                'games_played' => $gamesPlayed,
                'points_per_game' => number_format($averagePointsPerGame, 2),
                'rebounds_per_game' => number_format($averageReboundsPerGame, 2),
                'assists_per_game' => number_format($averageAssistsPerGame, 2),
                'blocks_per_game' => number_format($averageBlocksPerGame, 2),
                'steals_per_game' => number_format($averageStealsPerGame, 2),
                'turnovers_per_game' => number_format($averageTurnoversPerGame, 2),
                'fouls_per_game' => number_format($averageFoulsPerGame, 2),
            ];
        }

        $limit = 10;
        // Fetch MVP leaders and Rookie leaders from view tables

        $mvpLeaders = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->where('player_game_stats.season_id', $seasonId)
            ->whereNotIn('schedules.round', $excludedRounds)
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.draft_status as draft_status',
                'players.draft_id as draft_id',
                'players.team_id',
                'teams.name as team_name',
                'players.is_rookie',
                \DB::raw('COUNT(CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) AS games_played'),
                \DB::raw('ROUND(SUM(player_game_stats.points) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS points_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.rebounds) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS rebounds_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.assists) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS assists_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.steals) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS steals_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.blocks) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS blocks_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.turnovers) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS turnovers_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.fouls) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS fouls_per_game'),
                \DB::raw('(SUM(player_game_stats.points) +
                (SUM(player_game_stats.rebounds) * 1.2) +
                (SUM(player_game_stats.assists) * 1.5) +
                (SUM(player_game_stats.steals) * 2) +
                (SUM(player_game_stats.blocks) * 2) -
                (SUM(player_game_stats.turnovers) * 1) -
                (SUM(player_game_stats.fouls) * 0.5)) as performance_score')
            )
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'players.is_rookie', 'players.draft_status')
            ->orderByDesc('performance_score')
            ->take($limit)
            ->get();

        $rookieLeaders = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->where('player_game_stats.season_id', $seasonId)
            ->where('players.draft_id', $seasonId) // Assuming 'draft_id' represents the season in which the player was drafted
            ->whereNotIn('schedules.round', $excludedRounds)
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.draft_status as draft_status',
                'players.draft_id as draft_id',
                'players.team_id',
                'teams.name as team_name',
                'players.is_rookie',
                \DB::raw('COUNT(CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) AS games_played'),
                \DB::raw('ROUND(SUM(player_game_stats.points) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS points_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.rebounds) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS rebounds_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.assists) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS assists_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.steals) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS steals_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.blocks) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS blocks_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.turnovers) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS turnovers_per_game'),
                \DB::raw('ROUND(SUM(player_game_stats.fouls) / NULLIF(COUNT(CASE WHEN player_game_stats.minutes > 0 THEN 1 END), 0), 2) AS fouls_per_game'),
                \DB::raw('(SUM(player_game_stats.points) +
            (SUM(player_game_stats.rebounds) * 1.2) +
            (SUM(player_game_stats.assists) * 1.5) +
            (SUM(player_game_stats.steals) * 2) +
            (SUM(player_game_stats.blocks) * 2) -
            (SUM(player_game_stats.turnovers) * 1) -
            (SUM(player_game_stats.fouls) * 0.5)) as performance_score')
            )
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'players.is_rookie', 'players.draft_status')
            ->orderByDesc('performance_score')
            ->take($limit)
            ->get();

        // $mvpLeaders = \DB::table('mvp_leaders')->take($limit)->get();
        // $rookieLeaders = \DB::table('rookie_leaders')->take($limit)->get();

        // Fetch top $limit leaders for each stat
        $topPoints = collect($formattedPlayerStats)->sortByDesc('points_per_game')->take($limit)->values();
        $topRebounds = collect($formattedPlayerStats)->sortByDesc('rebounds_per_game')->take($limit)->values();
        $topAssists = collect($formattedPlayerStats)->sortByDesc('assists_per_game')->take($limit)->values();
        $topBlocks = collect($formattedPlayerStats)->sortByDesc('blocks_per_game')->take($limit)->values();
        $topSteals = collect($formattedPlayerStats)->sortByDesc('steals_per_game')->take($limit)->values();
        $topTurnovers = collect($formattedPlayerStats)->sortByDesc('turnovers_per_game')->take($limit)->values();
        $topFouls = collect($formattedPlayerStats)->sortByDesc('fouls_per_game')->take($limit)->values();

        // Return all leaders under the 'data' key
        $response = match ($leaderType) {
            'top_point_leaders' => $topPoints,
            'top_rebound_leaders' => $topRebounds,
            'top_assist_leaders' => $topAssists,
            'top_block_leaders' => $topBlocks,
            'top_steals_leaders' => $topSteals,
            'top_turnovers_leaders' => $topTurnovers,
            'top_fouls_leaders' => $topFouls,
            'mvp_leaders' => $mvpLeaders,
            'rookie_leaders' => $rookieLeaders,
            default => [],
        };

        return response()->json(['leaders' => $response]);
    }
    public function getSeasonLeaders(Request $request)
    {
        // Determine the leader type and season ID
        $leaderType = $request->input('leader_type', 'mvp_leaders');
        $seasonId = $request->input('season_id', $this->getLatestSeasonId());

        // Ensure season ID is valid
        if (empty($seasonId)) {
            return response()->json(['error' => 'Invalid season ID.'], 400);
        }

        // Fetch player stats
        $playerStats = \DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->where('player_season_stats.season_id', $seasonId)
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.draft_status',
                'players.team_id',
                'teams.name as team_name',
                'players.is_rookie',
                'players.draft_id',
                'player_season_stats.total_games_played as games_played',
                'player_season_stats.avg_points_per_game',
                'player_season_stats.avg_rebounds_per_game',
                'player_season_stats.avg_assists_per_game',
                'player_season_stats.avg_steals_per_game',
                'player_season_stats.avg_blocks_per_game',
                'player_season_stats.avg_turnovers_per_game',
                'player_season_stats.avg_fouls_per_game'
            )
            ->get();

        // Format player stats
        $formattedPlayerStats = $playerStats->map(function ($stats) {
            return [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'is_rookie' => $stats->is_rookie,
                'draft_status' => $stats->draft_status,
                'draft_id' => $stats->draft_id,
                'games_played' => $stats->games_played,
                'points_per_game' => number_format($stats->avg_points_per_game, 2),
                'rebounds_per_game' => number_format($stats->avg_rebounds_per_game, 2),
                'assists_per_game' => number_format($stats->avg_assists_per_game, 2),
                'blocks_per_game' => number_format($stats->avg_blocks_per_game, 2),
                'steals_per_game' => number_format($stats->avg_steals_per_game, 2),
                'turnovers_per_game' => number_format($stats->avg_turnovers_per_game, 2),
                'fouls_per_game' => number_format($stats->avg_fouls_per_game, 2),
            ];
        });

        // Define limit for top leaders
        $limit = 10;

        // Fetch MVP leaders
        // Calculate MVP Leaders
        $mvpLeaders = $playerStats->map(function ($stats) {
            // Calculate performance points
            $performancePoints =
                ($stats->avg_points_per_game * 1) +
                ($stats->avg_rebounds_per_game * 1.2) +
                ($stats->avg_assists_per_game * 1.5) +
                ($stats->avg_steals_per_game * 2) +
                ($stats->avg_blocks_per_game * 2) -
                ($stats->avg_turnovers_per_game * 1) -
                ($stats->avg_fouls_per_game * 0.5);

            // Store the raw performance score for sorting
            $stats->performance_score = (float) $performancePoints;

            return $stats;
        })
            ->sortByDesc('performance_score') // Sort numerically by performance score
            ->take($limit);

        // Calculate Rookie Leaders
        $rookieLeaders = $playerStats
            ->filter(function ($stats) use ($seasonId) {
                // Filter rookies based on draft_id matching the current season ID
                return $stats->draft_id == $seasonId;
            })
            ->map(function ($stats) {
                // Calculate performance points
                $performancePoints =
                    ($stats->avg_points_per_game * 1) +
                    ($stats->avg_rebounds_per_game * 1.2) +
                    ($stats->avg_assists_per_game * 1.5) +
                    ($stats->avg_steals_per_game * 2) +
                    ($stats->avg_blocks_per_game * 2) -
                    ($stats->avg_turnovers_per_game * 1) -
                    ($stats->avg_fouls_per_game * 0.5);

                // Store the raw performance score for sorting
                $stats->performance_score = (float) $performancePoints;

                return $stats;
            })
            ->sortByDesc('performance_score') // Sort numerically by performance score
            ->take($limit);
        // Sort by specific categories
        $topPoints = $formattedPlayerStats->sortByDesc('points_per_game')->take($limit);
        $topRebounds = $formattedPlayerStats->sortByDesc('rebounds_per_game')->take($limit);
        $topAssists = $formattedPlayerStats->sortByDesc('assists_per_game')->take($limit);
        $topBlocks = $formattedPlayerStats->sortByDesc('blocks_per_game')->take($limit);
        $topSteals = $formattedPlayerStats->sortByDesc('steals_per_game')->take($limit);
        $topTurnovers = $formattedPlayerStats->sortByDesc('turnovers_per_game')->take($limit);
        $topFouls = $formattedPlayerStats->sortByDesc('fouls_per_game')->take($limit);

        // Match the leader type
        $response = match ($leaderType) {
            'top_point_leaders' => $topPoints,
            'top_rebound_leaders' => $topRebounds,
            'top_assist_leaders' => $topAssists,
            'top_block_leaders' => $topBlocks,
            'top_steals_leaders' => $topSteals,
            'top_turnovers_leaders' => $topTurnovers,
            'top_fouls_leaders' => $topFouls,
            'mvp_leaders' => $mvpLeaders,
            'rookie_leaders' => $rookieLeaders,
            default => [],
        };

        return response()->json(['leaders' => $response]);
    }

    private function getLatestSeasonId()
    {
        // Fetch the latest season ID based on descending order of IDs
        $latestSeasonId = Seasons::orderBy('id', 'desc')->pluck('id')->first();

        if ($latestSeasonId) {
            return $latestSeasonId;
        } else {
            return 0;
        }

        // Handle the case where no seasons are found
        throw new \Exception('No seasons found.');
    }
}
