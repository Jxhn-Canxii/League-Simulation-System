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

    public function get_all_standings()
    {
        // Fetch all records from standings_view and join with seasons table
        $standings = DB::table('standings_view')
            ->join('seasons', 'standings_view.season_id', '=', 'seasons.id')
            ->select('standings_view.team_name', 'seasons.name AS season', 'standings_view.wins')
            ->get();

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
}
