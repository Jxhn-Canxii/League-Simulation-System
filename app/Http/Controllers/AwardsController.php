<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AwardsController extends Controller
{
    public function index()
    {
        return Inertia::render('Awards/Index', [
            'status' => session('status'),
        ]);
    }
    /**
     * Store aggregated stats of a player's performance for a season in the player_season_stats table.
     * If 'is_last' is true, update the latest season's status to 9.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function storePlayerSeasonStats(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        // Get the team_id and is_last from the request
        $teamId = $request->input('team_id');

        // Get the latest season ID
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');

        // Get all players from the team
        $players = DB::table('players')
            ->where('team_id', $teamId)
            ->get();

        foreach ($players as $player) {

            // Get the aggregated stats for the player in the specified season
            $playerStats = DB::table('player_game_stats')
                ->where('player_id', $player->id)
                ->where('season_id', $latestSeasonId)
                ->select(
                    'player_id',
                    DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as total_games_played'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_points_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_rebounds_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_assists_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_steals_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_blocks_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_turnovers_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_fouls_per_game'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN minutes ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_minutes_per_game')
                )
                ->groupBy('player_id')
                ->first();


            if ($playerStats) {
                // Get the player's role for the specified season
                $playerRating = DB::table('player_ratings')
                    ->where('player_id', $player->id)
                    ->where('season_id', $latestSeasonId)
                    ->first();

                // Insert or update the player's season stats into the player_season_stats table
                DB::table('player_season_stats')->updateOrInsert(
                    [
                        'player_id' => $player->id,
                        'season_id' => $latestSeasonId,
                    ],
                    [
                        'team_id' => $teamId,
                        'role' => $playerRating->role ?? $player->role,  // Role from player_ratings, default to 'unknown'
                        'avg_minutes_per_game' => $playerStats->avg_minutes_per_game,
                        'avg_points_per_game' => $playerStats->avg_points_per_game,
                        'avg_rebounds_per_game' => $playerStats->avg_rebounds_per_game,
                        'avg_assists_per_game' => $playerStats->avg_assists_per_game,
                        'avg_steals_per_game' => $playerStats->avg_steals_per_game,
                        'avg_blocks_per_game' => $playerStats->avg_blocks_per_game,
                        'avg_turnovers_per_game' => $playerStats->avg_turnovers_per_game,
                        'avg_fouls_per_game' => $playerStats->avg_fouls_per_game,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return response()->json(['message' => 'Player season stats stored successfully.']);
    }
    public function getSeasonAwards(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
        ]);
        // Fetch awards along with player, team, and season names for the latest season
        $awards = DB::table('season_awards')
            ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
            ->where('season_awards.season_id', $request->season_id)
            ->select(
                'season_awards.*',
                'players.name as player_name',
                'teams.name as team_name',
                'seasons.name as season_name' // Select the season name
            )
            ->get();


        return response()->json([
            'message' => 'Season awards stored successfully.',
            'awards' => $awards
        ]);
    }
    public function getAwardNamesDropdown()
    {
        // Fetch distinct award names from the season_awards table
        $awardNames = DB::table('season_awards')
            ->select('award_name')
            ->distinct()
            ->get();

        // Pass the award names to the view
        return response()->json([
            'awardNames' => $awardNames
        ]);
    }

    public function filterAwardsPerSeason(Request $request)
    {
        // Assume season_id is passed in the request
        $seasonId = $request->input('season_id');
        $awardsName = $request->input('awards_name');
        // Fetch awards along with player and team names for the updated season
        if ($seasonId > 0) {
            $awards = DB::table('season_awards')
                ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
                ->leftJoin('teams', 'season_awards.team_id', '=', 'teams.id')
                ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
                ->where('season_awards.season_id', $seasonId)
                ->select(
                    'season_awards.id',
                    'season_awards.player_id',
                    'players.name as player_name',
                    'teams.name as team_name',
                    'season_awards.award_name',
                    'season_awards.award_description',
                    'season_awards.season_id',
                    'season_awards.team_id',
                    'season_awards.created_at',
                    'seasons.name as season_name' // Select the season name
                )
                ->orderBy('season_awards.id', 'desc')  // Order by id in descending order
                ->get();
        } else {
            $awards = DB::table('season_awards')
                ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
                ->leftJoin('teams', 'season_awards.team_id', '=', 'teams.id')
                ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
                ->where('season_awards.award_name', $awardsName)
                ->select(
                    'season_awards.id',
                    'season_awards.player_id',
                    'players.name as player_name',
                    'teams.name as team_name',
                    'season_awards.award_name',
                    'season_awards.award_description',
                    'season_awards.season_id',
                    'season_awards.team_id',
                    'season_awards.created_at',
                    'seasons.name as season_name' // Select the season name
                )
                ->orderBy('season_awards.id', 'desc')  // Order by id in descending order
                ->get();
        }

        return response()->json([
            'message' => 'Team IDs in season awards updated successfully for season ' . $seasonId,
            'awards' => $awards
        ]);
    }


    public function storeSeasonAwardsV1()
    {
        // Get the latest season ID
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');

        // Clear existing awards for the latest season
        DB::table('season_awards')->where('season_id', $latestSeasonId)->delete();

        // Get player stats from player_season_stats for the latest season
        $playerStats = DB::table('player_season_stats')
            ->where('season_id', $latestSeasonId)
            ->get();

        // Determine the top performers based on different metrics
        $topScorer = $playerStats->sortByDesc('avg_points_per_game')->first();
        $topRebounder = $playerStats->sortByDesc('avg_rebounds_per_game')->first();
        $topPlaymaker = $playerStats->sortByDesc('avg_assists_per_game')->first();
        $topStealer = $playerStats->sortByDesc('avg_steals_per_game')->first();
        $topBlocker = $playerStats->sortByDesc('avg_blocks_per_game')->first();
        $bestDefender = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->first();
        $bestOverall = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_points_per_game + $stats->avg_rebounds_per_game + $stats->avg_assists_per_game +
                $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->first();

        // Top 5 Offensive Players
        $topOffensivePlayers = $playerStats->sortByDesc('avg_points_per_game')->take(5);

        // Top 5 Defensive Players
        $topDefensivePlayers = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->take(5);

        // Assuming 'player_season_stats' includes data from the previous season for comparison
        $previousSeasonId = DB::table('seasons')->where('id', '<', $latestSeasonId)->orderBy('id', 'desc')->value('id');
        $previousSeasonStats = DB::table('player_season_stats')->where('season_id', $previousSeasonId)->pluck('avg_points_per_game', 'player_id');

        $mostImprovedPlayer = $playerStats->sortByDesc(function ($stats) use ($previousSeasonStats) {
            $previousPoints = $previousSeasonStats[$stats->player_id] ?? 0;
            return ($stats->avg_points_per_game - $previousPoints);
        })
            ->first();

        // Fetch the Rookie of the Season based on a flag or indicator
        $rookieOfTheSeason = DB::table('players')
            ->join('player_season_stats', 'players.id', '=', 'player_season_stats.player_id')
            ->where('player_season_stats.season_id', $latestSeasonId)
            ->where('players.is_rookie', true) // Assuming you have a `is_rookie` field
            ->orderBy('player_season_stats.avg_points_per_game', 'desc')
            ->first();

        // Insert awards into season_awards table if not already present
        $this->insertAward($topScorer, 'Top Scorer', 'Player with the highest average points per game', $latestSeasonId);
        $this->insertAward($topRebounder, 'Top Rebounder', 'Player with the highest average rebounds per game', $latestSeasonId);
        $this->insertAward($topPlaymaker, 'Top Playmaker', 'Player with the highest average assists per game', $latestSeasonId);
        $this->insertAward($topStealer, 'Top Stealer', 'Player with the highest average steals per game', $latestSeasonId);
        $this->insertAward($topBlocker, 'Top Blocker', 'Player with the highest average blocks per game', $latestSeasonId);
        $this->insertAward($bestDefender, 'Best Defensive Player', 'Player with the highest combined average steals and blocks per game', $latestSeasonId);
        $this->insertAward($bestOverall, 'Best Overall Player', 'Player with the highest combined average metrics (points, rebounds, assists, steals, blocks)', $latestSeasonId);
        $this->insertAward($mostImprovedPlayer, 'Most Improved Player', 'Player with the highest increase in average points per game from the previous season', $latestSeasonId);

        // Insert Top 5 Offensive Players awards
        $counter = 1;
        foreach ($topOffensivePlayers as $player) {
            if ($counter > 5) break;
            $this->insertAward($player, 'Top ' . $counter . ' Offensive Player', 'Player ranked ' . $counter . ' in average points per game', $latestSeasonId);
            $counter++;
        }

        // Insert Top 5 Defensive Players awards
        $counter = 1;
        foreach ($topDefensivePlayers as $player) {
            if ($counter > 5) break;
            $this->insertAward($player, 'Top ' . $counter . ' Defensive Player', 'Player ranked ' . $counter . ' in combined average steals and blocks per game', $latestSeasonId);
            $counter++;
        }

        // Insert Rookie of the Season award
        if ($rookieOfTheSeason) {
            $this->insertAward($rookieOfTheSeason, 'Rookie of the Season', 'Best rookie player of the season', $latestSeasonId);
        }
        DB::table('seasons')
            ->where('id', $latestSeasonId)
            ->update(['status' => 9]);

        // Fetch awards along with player, team names, and team_id for the latest season
        $awards = DB::table('season_awards')
            ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->where('season_awards.season_id', $latestSeasonId)
            ->select(
                'season_awards.*',
                'players.name as player_name',
                'teams.name as team_name',
                'teams.id as team_id' // Include team_id in the select clause
            )
            ->get();

        return response()->json([
            'message' => 'Season awards stored successfully.',
            'awards' => $awards
        ]);
    }
    public function storeSeasonAwards()
    {
        // Get the latest season ID
        $latestSeasonId = DB::table('seasons')->orderBy('id', 'desc')->value('id');

        // Clear existing awards for the latest season
        DB::table('season_awards')->where('season_id', $latestSeasonId)->delete();

        // Get player stats from player_season_stats for the latest season
        $playerStats = DB::table('player_season_stats')
            ->where('season_id', $latestSeasonId)
            ->get();

        // Determine the top performers based on different metrics
        $topScorer = $playerStats->sortByDesc('avg_points_per_game')->first();
        $topRebounder = $playerStats->sortByDesc('avg_rebounds_per_game')->first();
        $topPlaymaker = $playerStats->sortByDesc('avg_assists_per_game')->first();
        $topStealer = $playerStats->sortByDesc('avg_steals_per_game')->first();
        $topBlocker = $playerStats->sortByDesc('avg_blocks_per_game')->first();
        $bestDefender = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->first();
        $bestOverall = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_points_per_game + $stats->avg_rebounds_per_game + $stats->avg_assists_per_game +
                $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->first();

        // Top 5 Offensive Players
        $topOffensivePlayers = $playerStats->sortByDesc('avg_points_per_game')->take(5);

        // Top 5 Defensive Players
        $topDefensivePlayers = $playerStats->sortByDesc(function ($stats) {
            return $stats->avg_steals_per_game + $stats->avg_blocks_per_game;
        })->take(5);

        // Assuming 'player_season_stats' includes data from the previous season for comparison
        $previousSeasonId = DB::table('seasons')->where('id', '<', $latestSeasonId)->orderBy('id', 'desc')->value('id');
        $previousSeasonStats = DB::table('player_season_stats')->where('season_id', $previousSeasonId)->pluck('avg_points_per_game', 'player_id');

        // Exclude rookies from the Most Improved Player award
        $nonRookies = DB::table('players')->where('is_rookie', false)->pluck('id');

        $mostImprovedPlayer = $playerStats->filter(function ($stats) use ($nonRookies) {
            return $nonRookies->contains($stats->player_id);
        })
            ->sortByDesc(function ($stats) use ($previousSeasonStats) {
                $previousPoints = $previousSeasonStats[$stats->player_id] ?? 0;
                return ($stats->avg_points_per_game - $previousPoints);
            })
            ->first();

        // Fetch the Rookie of the Season based on a flag or indicator
        $rookieOfTheSeason = DB::table('players')
            ->join('player_season_stats', 'players.id', '=', 'player_season_stats.player_id')
            ->where('player_season_stats.season_id', $latestSeasonId)
            ->where('players.is_rookie', true) // Assuming you have a `is_rookie` field
            ->orderBy('player_season_stats.avg_points_per_game', 'desc')
            ->first();

        // Insert awards into season_awards table if not already present
        $this->insertAward($topScorer, 'Top Scorer', 'Player with the highest average points per game', $latestSeasonId);
        $this->insertAward($topRebounder, 'Top Rebounder', 'Player with the highest average rebounds per game', $latestSeasonId);
        $this->insertAward($topPlaymaker, 'Top Playmaker', 'Player with the highest average assists per game', $latestSeasonId);
        $this->insertAward($topStealer, 'Top Stealer', 'Player with the highest average steals per game', $latestSeasonId);
        $this->insertAward($topBlocker, 'Top Blocker', 'Player with the highest average blocks per game', $latestSeasonId);
        $this->insertAward($bestDefender, 'Best Defensive Player', 'Player with the highest combined average steals and blocks per game', $latestSeasonId);
        $this->insertAward($bestOverall, 'Best Overall Player', 'Player with the highest combined average metrics (points, rebounds, assists, steals, blocks)', $latestSeasonId);
        $this->insertAward($mostImprovedPlayer, 'Most Improved Player', 'Player with the highest increase in average points per game from the previous season', $latestSeasonId);

        // Insert Top 5 Offensive Players awards
        $counter = 1;
        foreach ($topOffensivePlayers as $player) {
            if ($counter > 5) break;
            $this->insertAward($player, 'Top ' . $counter . ' Offensive Player', 'Player ranked ' . $counter . ' in average points per game', $latestSeasonId);
            $counter++;
        }

        // Insert Top 5 Defensive Players awards
        $counter = 1;
        foreach ($topDefensivePlayers as $player) {
            if ($counter > 5) break;
            $this->insertAward($player, 'Top ' . $counter . ' Defensive Player', 'Player ranked ' . $counter . ' in combined average steals and blocks per game', $latestSeasonId);
            $counter++;
        }

        // Insert Rookie of the Season award
        if ($rookieOfTheSeason) {
            $this->insertAward($rookieOfTheSeason, 'Rookie of the Season', 'Best rookie player of the season', $latestSeasonId);
        }
        DB::table('seasons')
            ->where('id', $latestSeasonId)
            ->update(['status' => 9]);

        // Fetch awards along with player, team names, and team_id for the latest season
        $awards = DB::table('season_awards')
            ->leftJoin('players', 'season_awards.player_id', '=', 'players.id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->where('season_awards.season_id', $latestSeasonId)
            ->select(
                'season_awards.*',
                'players.name as player_name',
                'teams.name as team_name',
                'teams.id as team_id' // Include team_id in the select clause
            )
            ->get();

        return response()->json([
            'message' => 'Season awards stored successfully.',
            'awards' => $awards
        ]);
    }

    private function insertAward($playerStats, $awardName, $awardDescription, $seasonId)
    {
        if ($playerStats) {
            DB::table('season_awards')->updateOrInsert(
                [
                    'player_id' => $playerStats->player_id,
                    'team_id' => $playerStats->team_id,
                    'season_id' => $seasonId,
                    'award_name' => $awardName,
                ],
                [
                    'award_description' => $awardDescription,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function getBestPlayersInConference(Request $request)
    {
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'conference_id' => 'required|exists:conferences,id',
            'round' => 'required|min:0',
        ]);

        $seasonId = $request->season_id;
        $conferenceId = $request->conference_id;
        $round = $request->round;
        $excludedRounds = ['quarter_finals', 'round_of_16', 'round_of_32', 'semi_finals', 'interconference_semi_finals', 'finals'];

        // Fetch player stats for the given season and conference
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->where('player_game_stats.season_id', $seasonId)
            ->where('players.conference_id', $conferenceId)
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
        $allRoundsSimulated = Schedules::where('season_id', $seasonId)
            ->where('conference_id', $conferenceId)
            ->where('status', 1)
            ->doesntExist();

        foreach ($playerStats as $stats) {
            $gamesPlayed = $allRoundsSimulated ? $stats->games_played : (($round + 1) * 2);

            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $gamesPlayed : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $gamesPlayed : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $gamesPlayed : 0;

            // Composite score formula for MVP
            $compositeScore = ($stats->total_points * 1.5) +
                ($stats->total_rebounds * 1.2) +
                ($stats->total_assists * 1.2) +
                ($stats->total_steals * 1.5) +
                ($stats->total_blocks * 1.5) -
                ($stats->total_turnovers * 1.0) -
                ($stats->total_fouls * 0.5);

            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'composite_score' => $compositeScore,
                'is_rookie' => $stats->is_rookie === 1, // Check if rookie
                'average_points' => $averagePointsPerGame,
                'average_rebounds' => $averageReboundsPerGame,
                'average_assists' => $averageAssistsPerGame,
            ];
        }

        // Fetch top 5 leaders for each stat
        $topPoints = collect($formattedPlayerStats)->sortByDesc('average_points')->take(5)->values();
        $topRebounds = collect($formattedPlayerStats)->sortByDesc('average_rebounds')->take(5)->values();
        $topAssists = collect($formattedPlayerStats)->sortByDesc('average_assists')->take(5)->values();

        // MVP and Rookie of the Season Leaders
        $mvpLeaders = collect($formattedPlayerStats)->sortByDesc('composite_score')->take(5)->values();
        $rookieLeaders = collect($formattedPlayerStats)->where('is_rookie', true)->sortByDesc('composite_score')->take(5)->values();

        return response()->json([
            'top_point_leaders' => $topPoints,
            'top_rebound_leaders' => $topRebounds,
            'top_assist_leaders' => $topAssists,
            'mvp_leaders' => $mvpLeaders,
            'rookie_leaders' => $rookieLeaders,
        ]);
    }
}
