<?php

namespace App\Http\Controllers;

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
        // Define stat categories and their corresponding columns
        $statCategories = [
            'points' => 'pgs.points',
            'rebounds' => 'pgs.rebounds',
            'assists' => 'pgs.assists',
            'steals' => 'pgs.steals',
            'blocks' => 'pgs.blocks'
        ];

        // Get the current season id (you can get this from your business logic or the latest game)
        $currentSeasonId = DB::table('seasons')->latest('id')->value('id');

        // For each stat category, check if a player qualifies for the all-time top 10
        foreach ($statCategories as $category => $column) {
            // Get the current season's top players for the given stat category
            $currentSeasonTopStats = DB::table('player_game_stats as pgs')
                ->select(
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
                ->orderByDesc($column) // Order by stat value in descending order
                ->limit(10) // Get the top 10 players
                ->get();

            // Get the existing top 10 all-time stats for the category
            $allTimeTopStats = DB::table('all_time_top_stats')
                ->where('stat_category', $category)
                ->orderByDesc('stat_value') // Ensure the top 10 are ordered by stat value
                ->limit(10)
                ->get();

            // Check if the current season stats qualify to replace any of the all-time top 10 stats
            foreach ($currentSeasonTopStats as $currentStat) {
                // Check if the current season stat qualifies to be in the all-time top 10
                $isQualified = false;
                if ($allTimeTopStats->count() < 10) {
                    $isQualified = true; // If there's space in the top 10, the current stat qualifies
                } else {
                    // Check if the current season's stat is better than the lowest stat in the all-time top 10
                    $lowestTopStat = $allTimeTopStats->last();
                    if ($currentStat->stat_value > $lowestTopStat->stat_value) {
                        $isQualified = true;
                    }
                }

                // If qualified, insert it into the all-time top stats
                if ($isQualified) {
                    // Remove the lowest entry if the all-time top 10 is already full
                    if ($allTimeTopStats->count() >= 10) {
                        $lowestTopStat = $allTimeTopStats->last();
                        DB::table('all_time_top_stats')
                            ->where('id', $lowestTopStat->id)
                            ->delete(); // Remove the lowest stat
                    }

                    // Insert the new top stat into the all-time table
                    DB::table('all_time_top_stats')->insert([
                        'stat_category' => $category,
                        'player_id' => $currentStat->player_id,
                        'player_name' => $currentStat->player_name,
                        'game_id' => $currentStat->game_id,
                        'team_id' => $currentStat->team_id,
                        'opponent_id' => $currentStat->opponent_id,
                        'season_id' => $currentStat->season_id,
                        'stat_value' => $currentStat->stat_value
                    ]);
                }
            }
        }
    }
}
