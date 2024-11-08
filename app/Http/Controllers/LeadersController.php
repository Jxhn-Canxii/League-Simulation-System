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
        // Fetch total stats for each player from player_season_stats

        // Get top 10 players by total points
        $topTotalPoints = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.total_points', // total points from player_season_stats
                'players.id as player_id'
            )
            ->orderByDesc('player_season_stats.total_points')
            ->limit(10)
            ->get();

        // Get top 10 players by total rebounds
        $topTotalRebounds = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.total_rebounds', // total rebounds from player_season_stats
                'players.id as player_id'
            )
            ->orderByDesc('player_season_stats.total_rebounds')
            ->limit(10)
            ->get();

        // Get top 10 players by total assists
        $topTotalAssists = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.total_assists', // total assists from player_season_stats
                'players.id as player_id'
            )
            ->orderByDesc('player_season_stats.total_assists')
            ->limit(10)
            ->get();

        // Get top 10 players by total steals
        $topTotalSteals = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.total_steals', // total steals from player_season_stats
                'players.id as player_id'
            )
            ->orderByDesc('player_season_stats.total_steals')
            ->limit(10)
            ->get();

        // Get top 10 players by total blocks
        $topTotalBlocks = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_season_stats.team_id', '=', 'teams.id')
            ->select(
                'players.name as player_name',
                'teams.name as team_name',
                'player_season_stats.total_blocks', // total blocks from player_season_stats
                'players.id as player_id'
            )
            ->orderByDesc('player_season_stats.total_blocks')
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

        // Fetch total stats for each player across all games
        // Highest Points in a Single Game
        $topSinglePoints = DB::table('player_game_stats')
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'player_game_stats.game_id',
                'teams.name as team_name',
                'opponent_teams.name as opponent_name',
                'seasons.id as season_id',
                DB::raw('MAX(player_game_stats.points) as highest_points')
            )
            ->join('players', 'players.id', '=', 'player_game_stats.player_id')
            ->join('teams', 'teams.id', '=', 'player_game_stats.team_id')
            ->join('seasons', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedule_view', 'schedule_view.game_id', '=', 'player_game_stats.game_id')
            ->join('teams as opponent_teams', function ($join) {
                $join->on('opponent_teams.id', '=', 'schedules_view.home_id')
                    ->orOn('opponent_teams.id', '=', 'schedules_view.away_id');
            })
            ->where(function ($query) {
                $query->where('player_game_stats.team_id', '=', 'schedules_view.home_id')
                    ->orWhere('player_game_stats.team_id', '=', 'schedules_view.away_id');
            })
            ->groupBy('player_game_stats.player_id', 'player_game_stats.game_id', 'player_game_stats.team_id', 'players.name', 'teams.name', 'opponent_teams.name', 'seasons.year')
            ->orderByDesc('highest_points')
            ->limit(10)
            ->get();

        // Highest Rebounds in a Single Game
        $topSingleRebounds = DB::table('player_game_stats')
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'player_game_stats.game_id',
                'teams.name as team_name',
                'opponent_teams.name as opponent_name',
                'seasons.id as season_id',
                DB::raw('MAX(player_game_stats.rebounds) as highest_rebounds')
            )
            ->join('players', 'players.id', '=', 'player_game_stats.player_id')
            ->join('teams', 'teams.id', '=', 'player_game_stats.team_id')
            ->join('seasons', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedule_view', 'schedule_view.game_id', '=', 'player_game_stats.game_id')
            ->join('teams as opponent_teams', function ($join) {
                $join->on('opponent_teams.id', '=', 'schedules_view.home_id')
                    ->orOn('opponent_teams.id', '=', 'schedules_view.away_id');
            })
            ->where(function ($query) {
                $query->where('player_game_stats.team_id', '=', 'schedules_view.home_id')
                    ->orWhere('player_game_stats.team_id', '=', 'schedules_view.away_id');
            })
            ->groupBy('player_game_stats.player_id', 'player_game_stats.game_id', 'player_game_stats.team_id', 'players.name', 'teams.name', 'opponent_teams.name', 'seasons.year')
            ->orderByDesc('highest_rebounds')
            ->limit(10)
            ->get();

        // Highest Assists in a Single Game
        $topSingleAssists = DB::table('player_game_stats')
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'player_game_stats.game_id',
                'teams.name as team_name',
                'opponent_teams.name as opponent_name',
                'seasons.id as season_id',
                DB::raw('MAX(player_game_stats.assists) as highest_assists')
            )
            ->join('players', 'players.id', '=', 'player_game_stats.player_id')
            ->join('teams', 'teams.id', '=', 'player_game_stats.team_id')
            ->join('seasons', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedule_view', 'schedule_view.game_id', '=', 'player_game_stats.game_id')
            ->join('teams as opponent_teams', function ($join) {
                $join->on('opponent_teams.id', '=', 'schedules_view.home_id')
                    ->orOn('opponent_teams.id', '=', 'schedules_view.away_id');
            })
            ->where(function ($query) {
                $query->where('player_game_stats.team_id', '=', 'schedules_view.home_id')
                    ->orWhere('player_game_stats.team_id', '=', 'schedules_view.away_id');
            })
            ->groupBy('player_game_stats.player_id', 'player_game_stats.game_id', 'player_game_stats.team_id', 'players.name', 'teams.name', 'opponent_teams.name', 'seasons.year')
            ->orderByDesc('highest_assists')
            ->limit(10)
            ->get();

        // Highest Blocks in a Single Game
        $topSingleBlocks = DB::table('player_game_stats')
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'player_game_stats.game_id',
                'teams.name as team_name',
                'opponent_teams.name as opponent_name',
                'seasons.year as season_year',
                DB::raw('MAX(player_game_stats.blocks) as highest_blocks')
            )
            ->join('players', 'players.id', '=', 'player_game_stats.player_id')
            ->join('teams', 'teams.id', '=', 'player_game_stats.team_id')
            ->join('seasons', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedule_view', 'schedule_view.game_id', '=', 'player_game_stats.game_id')
            ->join('teams as opponent_teams', function ($join) {
                $join->on('opponent_teams.id', '=', 'schedules_view.home_id')
                    ->orOn('opponent_teams.id', '=', 'schedules_view.away_id');
            })
            ->where(function ($query) {
                $query->where('player_game_stats.team_id', '=', 'schedules_view.home_id')
                    ->orWhere('player_game_stats.team_id', '=', 'schedules_view.away_id');
            })
            ->groupBy('player_game_stats.player_id', 'player_game_stats.game_id', 'player_game_stats.team_id', 'players.name', 'teams.name', 'opponent_teams.name', 'seasons.year')
            ->orderByDesc('highest_blocks')
            ->limit(10)
            ->get();

        // Highest Steals in a Single Game
        $topSingleSteals = DB::table('player_game_stats')
            ->select(
                'player_game_stats.player_id',
                'players.name as player_name',
                'player_game_stats.game_id',
                'teams.name as team_name',
                'opponent_teams.name as opponent_name',
                'seasons.year as season_year',
                DB::raw('MAX(player_game_stats.steals) as highest_steals')
            )
            ->join('players', 'players.id', '=', 'player_game_stats.player_id')
            ->join('teams', 'teams.id', '=', 'player_game_stats.team_id')
            ->join('seasons', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedule_view', 'schedule_view.game_id', '=', 'player_game_stats.game_id')
            ->join('teams as opponent_teams', function ($join) {
                $join->on('opponent_teams.id', '=', 'schedules_view.home_id')
                    ->orOn('opponent_teams.id', '=', 'schedules_view.away_id');
            })
            ->where(function ($query) {
                $query->where('player_game_stats.team_id', '=', 'schedules_view.home_id')
                    ->orWhere('player_game_stats.team_id', '=', 'schedules_view.away_id');
            })
            ->groupBy('player_game_stats.player_id', 'player_game_stats.game_id', 'player_game_stats.team_id', 'players.name', 'teams.name', 'opponent_teams.name', 'seasons.year')
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
}
