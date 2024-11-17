<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Schedules;
use App\Models\Seasons;
use App\Models\Teams;
use App\Models\PlayerGameStats;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeadersController extends Controller
{


    public function index()
    {
        return Inertia::render('Leaders/Index', [
            'status' => session('status'),
        ]);
    }
    public function getAverageStatsLeaders()
    {

        // Fetch total stats for each player across all games
        // Get top 10 players by average points per game, with season and team information
        $topPoints = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.avg_points_per_game',
                'player_season_stats.season_id'
            )
            ->orderByDesc('avg_points_per_game')
            ->limit(10)
            ->get();

        // Get top 10 players by average rebounds per game, with season and team information
        $topRebounds = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.avg_rebounds_per_game',
                'player_season_stats.season_id'
            )
            ->orderByDesc('avg_rebounds_per_game')
            ->limit(10)
            ->get();

        // Get top 10 players by average assists per game, with season and team information
        $topAssists = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.avg_assists_per_game',
                'player_season_stats.season_id'
            )
            ->orderByDesc('avg_assists_per_game')
            ->limit(10)
            ->get();

        // Get top 10 players by average steals per game, with season and team information
        $topSteals = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.avg_steals_per_game',
                'player_season_stats.season_id'
            )
            ->orderByDesc('avg_steals_per_game')
            ->limit(10)
            ->get();

        // Get top 10 players by average blocks per game, with season and team information
        $topBlocks = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.avg_blocks_per_game',
                'player_season_stats.season_id'
            )
            ->orderByDesc('avg_blocks_per_game')
            ->limit(10)
            ->get();


        // Return data as a JSON response
        return response()->json([
            'topPoints' => $topPoints,
            'topRebounds' => $topRebounds,
            'topAssists' => $topAssists,
            'topSteals' => $topSteals,
            'topBlocks' => $topBlocks,
        ]);
    }
    public function getTotalStatsLeaders()
    {
        // Get top 10 players by total combined points across all seasons
        $topTotalPoints = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw('SUM(player_season_stats.total_points) as total_points'), // Sum total points across all seasons
                'players.id as player_id'
            )
            ->groupBy('player_season_stats.player_id', 'players.name', 'teams.name', 'players.id')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        // Get top 10 players by total combined rebounds across all seasons
        $topTotalRebounds = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw('SUM(player_season_stats.total_rebounds) as total_rebounds'), // Sum total rebounds across all seasons
                'players.id as player_id'
            )
            ->groupBy('player_season_stats.player_id', 'players.name', 'teams.name', 'players.id')
            ->orderByDesc('total_rebounds')
            ->limit(10)
            ->get();

        // Get top 10 players by total combined assists across all seasons
        $topTotalAssists = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw('SUM(player_season_stats.total_assists) as total_assists'), // Sum total assists across all seasons
                'players.id as player_id'
            )
            ->groupBy('player_season_stats.player_id', 'players.name', 'teams.name', 'players.id')
            ->orderByDesc('total_assists')
            ->limit(10)
            ->get();

        // Get top 10 players by total combined steals across all seasons
        $topTotalSteals = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw('SUM(player_season_stats.total_steals) as total_steals'), // Sum total steals across all seasons
                'players.id as player_id'
            )
            ->groupBy('player_season_stats.player_id', 'players.name', 'teams.name', 'players.id')
            ->orderByDesc('total_steals')
            ->limit(10)
            ->get();

        // Get top 10 players by total combined blocks across all seasons
        $topTotalBlocks = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                DB::raw('SUM(player_season_stats.total_blocks) as total_blocks'), // Sum total blocks across all seasons
                'players.id as player_id'
            )
            ->groupBy('player_season_stats.player_id', 'players.name', 'teams.name', 'players.id')
            ->orderByDesc('total_blocks')
            ->limit(10)
            ->get();

        // Return data as a JSON response
        return response()->json([
            'topTotalPoints' => $topTotalPoints,
            'topTotalRebounds' => $topTotalRebounds,
            'topTotalAssists' => $topTotalAssists,
            'topTotalSteals' => $topTotalSteals,
            'topTotalBlocks' => $topTotalBlocks,
        ]);
    }

    public function getSingleStatsLeaders()
    {
        // Fetch top players for each stat category from all_time_top_stats
        // Highest Points in a Single Game
        $topSinglePoints = DB::table('all_time_top_stats')
            ->select(
                'player_id',
                'player_name',
                'game_id',
                'team1.name as team_name', // Player's team name
                'team2.name as opponent_team_name', // Opponent team name
                DB::raw('MAX(stat_value) as highest_points'),
                'season_id'
            )
            ->join('teams as team1', 'team1.id', '=', 'all_time_top_stats.team_id') // Join for player team_name
            ->join('teams as team2', 'team2.id', '=', 'all_time_top_stats.opponent_id') // Join for opponent team_name using opponent_id
            ->where('stat_category', 'points')
            ->groupBy('player_id', 'player_name', 'game_id', 'team1.name', 'team2.name', 'season_id')
            ->orderByDesc('highest_points')
            ->limit(10)
            ->get();

        // Highest Rebounds in a Single Game
        $topSingleRebounds = DB::table('all_time_top_stats')
            ->select(
                'player_id',
                'player_name',
                'game_id',
                'team1.name as team_name',
                'team2.name as opponent_team_name',
                DB::raw('MAX(stat_value) as highest_rebounds'),
                'season_id'
            )
            ->join('teams as team1', 'team1.id', '=', 'all_time_top_stats.team_id')
            ->join('teams as team2', 'team2.id', '=', 'all_time_top_stats.opponent_id')
            ->where('stat_category', 'rebounds')
            ->groupBy('player_id', 'player_name', 'game_id', 'team1.name', 'team2.name', 'season_id')
            ->orderByDesc('highest_rebounds')
            ->limit(10)
            ->get();

        // Highest Assists in a Single Game
        $topSingleAssists = DB::table('all_time_top_stats')
            ->select(
                'player_id',
                'player_name',
                'game_id',
                'team1.name as team_name',
                'team2.name as opponent_team_name',
                DB::raw('MAX(stat_value) as highest_assists'),
                'season_id'
            )
            ->join('teams as team1', 'team1.id', '=', 'all_time_top_stats.team_id')
            ->join('teams as team2', 'team2.id', '=', 'all_time_top_stats.opponent_id')
            ->where('stat_category', 'assists')
            ->groupBy('player_id', 'player_name', 'game_id', 'team1.name', 'team2.name', 'season_id')
            ->orderByDesc('highest_assists')
            ->limit(10)
            ->get();

        // Highest Blocks in a Single Game
        $topSingleBlocks = DB::table('all_time_top_stats')
            ->select(
                'player_id',
                'player_name',
                'game_id',
                'team1.name as team_name',
                'team2.name as opponent_team_name',
                DB::raw('MAX(stat_value) as highest_blocks'),
                'season_id'
            )
            ->join('teams as team1', 'team1.id', '=', 'all_time_top_stats.team_id')
            ->join('teams as team2', 'team2.id', '=', 'all_time_top_stats.opponent_id')
            ->where('stat_category', 'blocks')
            ->groupBy('player_id', 'player_name', 'game_id', 'team1.name', 'team2.name', 'season_id')
            ->orderByDesc('highest_blocks')
            ->limit(10)
            ->get();

        // Highest Steals in a Single Game
        $topSingleSteals = DB::table('all_time_top_stats')
            ->select(
                'player_id',
                'player_name',
                'game_id',
                'team1.name as team_name',
                'team2.name as opponent_team_name',
                DB::raw('MAX(stat_value) as highest_steals'),
                'season_id'
            )
            ->join('teams as team1', 'team1.id', '=', 'all_time_top_stats.team_id')
            ->join('teams as team2', 'team2.id', '=', 'all_time_top_stats.opponent_id')
            ->where('stat_category', 'steals')
            ->groupBy('player_id', 'player_name', 'game_id', 'team1.name', 'team2.name', 'season_id')
            ->orderByDesc('highest_steals')
            ->limit(10)
            ->get();

        // Return data as a JSON response
        return response()->json([
            'topSinglePoints' => $topSinglePoints,
            'topSingleRebounds' => $topSingleRebounds,
            'topSingleAssists' => $topSingleAssists,
            'topSingleBlocks' => $topSingleBlocks,
            'topSingleSteals' => $topSingleSteals
        ]);
    }
    public function updateAllTimeTopStats()
    {
        // Get the current season id (you can get this from your business logic or the latest game)
        $currentSeasonId = DB::table('seasons')->latest('id')->value('id');
        // Define the stat categories and corresponding columns in the player_game_stats table
        $statCategories = [
            'points' => 'pgs.points',
            'rebounds' => 'pgs.rebounds',
            'assists' => 'pgs.assists',
            'steals' => 'pgs.steals',
            'blocks' => 'pgs.blocks',
        ];

        // Iterate through each stat category
        foreach ($statCategories as $category => $column) {
            // Fetch the top 10 players for the current stat category
            $topStats = DB::table('player_game_stats as pgs')
                ->select(
                    DB::raw("'$category' AS stat_category"),
                    'pgs.player_id',
                    'players.name as player_name',
                    'pgs.game_id',
                    'pgs.team_id',
                    DB::raw("CASE WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id ELSE schedule_view.home_id END AS opponent_id"),
                    'pgs.season_id',
                    DB::raw("$column as stat_value")
                )
                ->join('players', 'pgs.player_id', '=', 'players.id')
                ->join('schedule_view', 'pgs.game_id', '=', 'schedule_view.game_id')
                ->where('pgs.season_id', $currentSeasonId) // Filter by current season
                ->orderByDesc($column) // Sort the stats by highest value
                ->limit(10) // Only top 10 players
                ->get();

            // Fetch the existing all-time top 10 stats for the given stat category
            $existingTopStats = DB::table('all_time_top_stats')
                ->where('stat_category', $category)
                ->orderByDesc('stat_value') // Order by stat_value descending
                ->limit(10) // Get top 10 all-time stats
                ->get();

            // Compare the current top stats with the all-time stats
            foreach ($topStats as $stat) {
                // Check if this stat qualifies to enter the all-time top 10
                $lowestStat = $existingTopStats->last(); // Get the lowest stat in the top 10

                // If there's room in the top 10 or this stat is greater than the lowest stat
                if ($existingTopStats->count() < 10 || $stat->stat_value > $lowestStat->stat_value) {
                    // If the table already has 10 stats, remove the lowest
                    if ($existingTopStats->count() == 10) {
                        DB::table('all_time_top_stats')
                            ->where('stat_category', $category)
                            ->where('stat_value', $lowestStat->stat_value)
                            ->delete();
                    }

                    // Insert the current top stat into the all-time top stats table
                    DB::table('all_time_top_stats')->insert([
                        'stat_category' => $stat->stat_category,
                        'player_id' => $stat->player_id,
                        'player_name' => $stat->player_name,
                        'game_id' => $stat->game_id,
                        'team_id' => $stat->team_id,
                        'opponent_id' => $stat->opponent_id,
                        'season_id' => $stat->season_id,
                        'stat_value' => $stat->stat_value,
                    ]);
                }
            }
        }
    }

    public function updateAllTimeTopStatsPerSeason(Request $request)
    {
        // Get the current season id (you can get this from your business logic or the latest game)
        $currentSeasonId = $request->season_id;
        // Define the stat categories and corresponding columns in the player_game_stats table
        $statCategories = [
            'points' => 'pgs.points',
            'rebounds' => 'pgs.rebounds',
            'assists' => 'pgs.assists',
            'steals' => 'pgs.steals',
            'blocks' => 'pgs.blocks',
        ];

        // Iterate through each stat category
        foreach ($statCategories as $category => $column) {
            // Fetch the top 10 players for the current stat category
            $topStats = DB::table('player_game_stats as pgs')
                ->select(
                    DB::raw("'$category' AS stat_category"),
                    'pgs.player_id',
                    'players.name as player_name',
                    'pgs.game_id',
                    'pgs.team_id',
                    DB::raw("CASE WHEN pgs.team_id = schedule_view.home_id THEN schedule_view.away_id ELSE schedule_view.home_id END AS opponent_id"),
                    'pgs.season_id',
                    DB::raw("$column as stat_value")
                )
                ->join('players', 'pgs.player_id', '=', 'players.id')
                ->join('schedule_view', 'pgs.game_id', '=', 'schedule_view.game_id')
                ->where('pgs.season_id', $currentSeasonId) // Filter by current season
                ->orderByDesc($column) // Sort the stats by highest value
                ->limit(10) // Only top 10 players
                ->get();

            // Fetch the existing all-time top 10 stats for the given stat category
            $existingTopStats = DB::table('all_time_top_stats')
                ->where('stat_category', $category)
                ->orderByDesc('stat_value') // Order by stat_value descending
                ->limit(10) // Get top 10 all-time stats
                ->get();

            // Compare the current top stats with the all-time stats
            foreach ($topStats as $stat) {
                // Check if this stat qualifies to enter the all-time top 10
                $lowestStat = $existingTopStats->last(); // Get the lowest stat in the top 10

                // If there's room in the top 10 or this stat is greater than the lowest stat
                if ($existingTopStats->count() < 10 || $stat->stat_value > $lowestStat->stat_value) {
                    // If the table already has 10 stats, remove the lowest
                    if ($existingTopStats->count() == 10) {
                        DB::table('all_time_top_stats')
                            ->where('stat_category', $category)
                            ->where('stat_value', $lowestStat->stat_value)
                            ->delete();
                    }

                    // Insert the current top stat into the all-time top stats table
                    DB::table('all_time_top_stats')->insert([
                        'stat_category' => $stat->stat_category,
                        'player_id' => $stat->player_id,
                        'player_name' => $stat->player_name,
                        'game_id' => $stat->game_id,
                        'team_id' => $stat->team_id,
                        'opponent_id' => $stat->opponent_id,
                        'season_id' => $stat->season_id,
                        'stat_value' => $stat->stat_value,
                    ]);
                }
            }
        }
    }
}
