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
        $mvpLeaders = $playerStats->map(function ($stats) use ($seasonId) {
            // Get the total number of games played by the team
            $totalGames = DB::table('schedules')
                ->where('home_id', $stats->team_id)
                ->orWhere('away_id', $stats->team_id)
                ->where('season_id', $seasonId)
                ->count();

            // Calculate how many games the player has played
            $gamesPlayed = $stats->total_games_played;

            // Calculate the required 70% of total games
            $requiredGames = ceil($totalGames * 0.7); // Round up to the nearest whole number

            // Check if player has played at least 70% of team's games
            if ($gamesPlayed >= $requiredGames) {
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
            } else {
                // If the player doesn't meet the 70% threshold, set a very low performance score to exclude them
                $stats->performance_score = -1;
            }

            return $stats;
        })
            ->sortByDesc('performance_score') // Sort numerically by performance score
            ->take($limit);

        // Calculate Rookie Leaders (same logic as MVP, but for rookies)
        $rookieLeaders = $playerStats
            ->filter(function ($stats) use ($seasonId) {
                // Filter rookies based on draft_id matching the current season ID
                return $stats->draft_id == $seasonId;
            })
            ->map(function ($stats) use ($seasonId) {
                // Get the total number of games played by the team
                $totalGames = DB::table('schedules')
                    ->where('home_id', $stats->team_id)
                    ->orWhere('away_id', $stats->team_id)
                    ->where('season_id', $seasonId)
                    ->count();

                // Calculate how many games the player has played
                $gamesPlayed = $stats->total_games_played;

                // Calculate the required 70% of total games
                $requiredGames = ceil($totalGames * 0.7); // Round up to the nearest whole number

                // Check if player has played at least 70% of team's games
                if ($gamesPlayed >= $requiredGames) {
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
                } else {
                    // If the player doesn't meet the 70% threshold, set a very low performance score to exclude them
                    $stats->performance_score = -1;
                }

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
