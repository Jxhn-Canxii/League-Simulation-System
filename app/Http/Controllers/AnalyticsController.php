<?php

namespace App\Http\Controllers;

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
        ]);
        // Fetch all records from standings_view and join with seasons table
        $standings = DB::table('standings_view')
            ->join('seasons', 'standings_view.season_id', '=', 'seasons.id')
            ->select('standings_view.team_name', 'seasons.name AS season', 'standings_view.wins');

        // Check if conference_id is 0, then conditionally add the where clause
        if ($request->conference_id > 0) {
            $standings->where('standings_view.conference_id', $request->conference_id);
        }

        $standings = $standings->get();


        // Structure data for line chart
        $structuredData = [];

        foreach ($standings as $record) {
            $team = $record->team_name;
            $season = $record->season;
            $wins = $record->wins;

            // Initialize team entry if it doesn't exist
            if (!isset($structuredData[$team])) {
                $structuredData[$team] = [];
            }

            // Add the win data for the specific season
            $structuredData[$team][$season] = $wins;
        }

        // Prepare the final data for the line chart
        $seasons = array_unique(array_reduce($structuredData, function ($carry, $item) {
            return array_merge($carry, array_keys($item));
        }, []));

        $finalData = [
            'labels' => array_values($seasons), // Seasons as labels
            'datasets' => []
        ];

        foreach ($structuredData as $team => $winsData) {
            $dataset = [
                'label' => $team,
                'data' => array_map(function ($season) use ($winsData) {
                    return $winsData[$season] ?? 0; // Default to 0 if no wins recorded
                }, $seasons),
                'fill' => false,
                'borderColor' => '#' . substr(md5(rand()), 0, 6), // Random color for each team
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
        $totalTeams = DB::table('players')
            ->where('team_id', '>', 0)
            ->distinct('team_id')
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
            'total_available_slots' => $totalAvailableSlots,
        ]);
    }

    public function getSeasonLeaders(Request $request)
    {
        // Determine the type of leader to return based on the request
        $leaderType = $request->input('leader_type', 'mvp_leaders'); // Default to 'mvp_leaders'
        $seasonId = $this->getLatestSeasonId();
        $excludedRounds = ['quarter_finals', 'round_of_16', 'round_of_32', 'semi_finals', 'interconference_semi_finals', 'finals'];

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
            ->groupBy('players.id', 'players.name', 'players.team_id', 'teams.name', 'players.is_rookie')
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
                'is_rookie' => $stats->is_rookie === 1 ? 'Rookie' : 'Veteran', // Check if rookie
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
        $mvpLeaders = \DB::table('mvp_leaders')->take($limit)->get();
        $rookieLeaders = \DB::table('rookie_leaders')->take($limit)->get();

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
            'top_turnover_leaders' => $topTurnovers,
            'top_foul_leaders' => $topFouls,
            'mvp_leaders' => $mvpLeaders,
            'rookie_leaders' => $rookieLeaders,
            default => [],
        };

        return response()->json(['leaders' => $response ]);

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
