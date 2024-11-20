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

class GameController extends Controller
{

    public function getboxscore(Request $request)
    {
        // Validate the request
        $request->validate([
            'game_id' => 'required|string',
        ]);

        $game_id = $request->game_id; // Fetch game details from the schedule_view table and join with teams table
        $game = \DB::table('schedule_view')
            ->join('teams as away_team', 'schedule_view.away_id', '=', 'away_team.id') // Join for away team
            ->join('teams as home_team', 'schedule_view.home_id', '=', 'home_team.id') // Join for home team
            ->where('schedule_view.game_id', $game_id)
            ->select(
                'schedule_view.*', // Select all columns from schedule_view
                'away_team.primary_color as away_primary_color',
                'away_team.secondary_color as away_secondary_color',
                'home_team.primary_color as home_primary_color',
                'home_team.secondary_color as home_secondary_color'
            )
            ->first();

        if (!$game) {
            return response()->json([
                'message' => 'Game not found',
            ], 404);
        }
        $playerStats = \DB::table('player_game_stats')
            ->where('player_game_stats.game_id', $game_id)
            ->leftJoin('players as p', 'player_game_stats.player_id', '=', 'p.id') // Alias for players table
            ->leftJoin('teams as drafted_team', 'drafted_team.id', '=', 'p.drafted_team_id') // Alias for drafted teams
            ->leftJoin('teams as t', 'player_game_stats.team_id', '=', 't.id') // Alias for teams table
            ->leftJoin('player_season_stats as pss', function ($join) {
                $join->on('pss.player_id', '=', 'p.id')
                    ->on('pss.season_id', '=', 'player_game_stats.season_id'); // Join player_season_stats on player_id and season_id
            })
            ->leftJoin('season_awards as sa', 'sa.player_id', '=', 'p.id')
            ->select(
                'player_game_stats.player_id',
                'p.name as player_name',
                'p.is_rookie',
                'p.draft_status',
                'p.draft_id',
                'drafted_team.acronym as drafted_team_acro',
                'player_game_stats.team_id',
                'player_game_stats.game_id',
                'player_game_stats.season_id as game_season_id',
                'pss.season_id as stats_season_id',
                't.name as team_name',
                'pss.role as player_role',
                'player_game_stats.points',
                'player_game_stats.assists',
                'player_game_stats.rebounds',
                'player_game_stats.steals',
                'player_game_stats.blocks',
                'player_game_stats.turnovers',
                'player_game_stats.fouls',
                'player_game_stats.minutes',

                // Get the list of awards for the player in the current season
                // DB::raw("COALESCE(
                //     GROUP_CONCAT(
                //         CONCAT(
                //             sa.award_name,
                //             ' (Season ', sa.season_id, ') by ',
                //             t.name
                //         ) SEPARATOR ', '),
                //     null) as awards"),

                // Determine if the player is the Finals MVP for this season
                DB::raw("CASE WHEN p.id = (SELECT finals_mvp_id FROM seasons WHERE seasons.finals_mvp_id = p.id LIMIT 1) THEN 1 ELSE 0 END as is_finals_mvp"),

                // Return the string for Finals MVP
                // DB::raw("COALESCE(
                //     (SELECT CONCAT('Finals MVP (Season ', s.id, ') by ',
                //         CASE
                //             WHEN pgs.team_id = s.finals_winner_id AND s.finals_winner_score > s.finals_loser_score THEN th.name
                //             WHEN pgs.team_id = s.finals_loser_id AND s.finals_loser_score > s.finals_winner_score THEN ta.name
                //             ELSE 'No Winner'
                //         END)
                //     FROM seasons s
                //     LEFT JOIN teams th ON th.id = s.finals_winner_id  -- Join for winner team name
                //     LEFT JOIN teams ta ON ta.id = s.finals_loser_id  -- Join for loser team name
                //     JOIN player_game_stats pgs ON pgs.season_id = s.id AND pgs.player_id = p.id  -- Join to get the player’s game stats
                //     WHERE s.finals_mvp_id = p.id
                //     LIMIT 1), '') as finals_mvp"),

                // DB::raw("COALESCE(
                //     (SELECT CONCAT('Championship Won (Season ', s.id, ') by ',
                //                    CASE
                //                        WHEN pgs.team_id = s.finals_winner_id AND s.finals_winner_score > s.finals_loser_score THEN th.name
                //                        WHEN pgs.team_id = s.finals_loser_id AND s.finals_loser_score > s.finals_winner_score THEN ta.name
                //                    END)
                //      FROM seasons s
                //      JOIN player_game_stats pgs ON pgs.season_id = s.id AND pgs.player_id = p.id
                //      LEFT JOIN teams th ON th.id = s.finals_winner_id  -- Join for home team name
                //      LEFT JOIN teams ta ON ta.id = s.finals_loser_id  -- Join for away team name
                //      WHERE (
                //          (pgs.team_id = s.finals_winner_id AND s.finals_winner_score > s.finals_loser_score)  -- Home team wins
                //          OR
                //          (pgs.team_id = s.finals_loser_id AND s.finals_loser_score > s.finals_winner_score)  -- Away team wins
                //      )
                //      LIMIT 1), '') as championship_won"),
            )
            ->groupBy(
                'player_game_stats.player_id',
                'p.id',
                'p.name',
                'p.is_rookie',
                'p.draft_status',
                'p.draft_id',
                'drafted_team.acronym',
                'player_game_stats.team_id',
                'player_game_stats.game_id',
                'player_game_stats.season_id',
                'pss.season_id',
                't.name',
                'pss.role',
                'player_game_stats.points',
                'player_game_stats.assists',
                'player_game_stats.rebounds',
                'player_game_stats.steals',
                'player_game_stats.blocks',
                'player_game_stats.turnovers',
                'player_game_stats.fouls',
                'player_game_stats.minutes'
            )
            ->get()
            ->keyBy('player_id');



        // Fetch all players that might be relevant to the game (ignoring team_id here)
        $players = \DB::table('players')
            ->whereIn('id', $playerStats->pluck('player_id')->toArray())
            ->get()
            ->keyBy('id');

        // Calculate stat leaders
        $statLeaders = [
            'points' => $playerStats->sortByDesc('points')->first(),
            'assists' => $playerStats->sortByDesc('assists')->first(),
            'rebounds' => $playerStats->sortByDesc('rebounds')->first(),
            'steals' => $playerStats->sortByDesc('steals')->first(),
            'blocks' => $playerStats->sortByDesc('blocks')->first(),
        ];

        // Determine the winning team
        $winningTeamId = $game->home_score > $game->away_score ? $game->home_id : $game->away_id;

        // Filter player stats for the winning team
        $winningTeamPlayersStats = $playerStats->filter(function ($stat) use ($winningTeamId) {
            return $stat->team_id == $winningTeamId;
        });

        // Determine the best player of the winning team
        // $bestWinningTeamPlayer = $winningTeamPlayersStats->sort(function ($a, $b) {
        //     $aStats = $a->points + $a->assists + $a->rebounds + $a->steals + $a->blocks;
        //     $bStats = $b->points + $b->assists + $b->rebounds + $b->steals + $b->blocks;
        //     return $bStats <=> $aStats;
        // })->first();

        $bestWinningTeamPlayer = $winningTeamPlayersStats->sort(function ($a, $b) {
            $aStats = $a->points * 1.0 + $a->rebounds * 1.2 + $a->assists * 1.5 + $a->steals * 2.0 + $a->blocks * 2.0 - $a->turnovers * 1.5;
            $bStats = $b->points * 1.0 + $b->rebounds * 1.2 + $b->assists * 1.5 + $b->steals * 2.0 + $b->blocks * 2.0 - $b->turnovers * 1.5;
            return $bStats <=> $aStats;
        })->first();


        // Fetch player details for the best player of the winning team if exists
        $bestWinningTeamPlayerDetails = $bestWinningTeamPlayer ? [
            'game_id' => $bestWinningTeamPlayer->game_id,
            'name' => $bestWinningTeamPlayer->player_name,
            'team' => $bestWinningTeamPlayer->team_name,
            'points' => $bestWinningTeamPlayer->points,
            'assists' => $bestWinningTeamPlayer->assists,
            'rebounds' => $bestWinningTeamPlayer->rebounds,
            'steals' => $bestWinningTeamPlayer->steals,
            'blocks' => $bestWinningTeamPlayer->blocks,
            'turnovers' => $bestWinningTeamPlayer->turnovers,
            'fouls' => $bestWinningTeamPlayer->fouls,
            'role' => $bestWinningTeamPlayer->player_role,
            'minutes' => $bestWinningTeamPlayer->minutes,
            'draft_id' => $bestWinningTeamPlayer->draft_id,
            'draft_status' => $bestWinningTeamPlayer->draft_status,
            'drafted_team_acro' => $bestWinningTeamPlayer->drafted_team_acro,
            'awards' => $bestWinningTeamPlayer->awards ?? null,
            'finals_mvp' => $bestWinningTeamPlayer->finals_mvp ?? null,
            'championship_won' => $bestWinningTeamPlayer->championship_won ?? null,
            'is_finals_mvp' => $bestWinningTeamPlayer->is_finals_mvp,
        ] : null;

        $homeTeamStreak = $this->getTeamStreak($game->home_id, $game->id);
        $awayTeamStreak = $this->getTeamStreak($game->away_id, $game->id);

        // Query to get head-to-head record
        $headToHeadRecord = $this->getHeadToHeadRecord($game->home_id, $game->away_id);

        // Split player stats into home and away teams, using the game team IDs
        $homeTeamPlayers = $players->filter(function ($player) use ($game, $playerStats) {
            $playerStat = $playerStats->get($player->id);
            return $playerStat && $playerStat->team_id == $game->home_id;
        });

        $awayTeamPlayers = $players->filter(function ($player) use ($game, $playerStats) {
            $playerStat = $playerStats->get($player->id);
            return $playerStat && $playerStat->team_id == $game->away_id;
        });

        // Convert home team player stats to an array, including those with no recorded stats
        $homeTeamPlayersArray = $homeTeamPlayers->map(function ($player) use ($playerStats) {
            $stats = $playerStats->get($player->id);

            return [
                'player_id' => $player->id,
                'team_id' => $player->team_id,
                'name' => $player->name,
                'role' => $player->role,
                'is_rookie' => $player->is_rookie,
                'points' => $stats ? $stats->points : 0,
                'assists' => $stats ? $stats->assists : 0,
                'rebounds' => $stats ? $stats->rebounds : 0,
                'steals' => $stats ? $stats->steals : 0,
                'blocks' => $stats ? $stats->blocks : 0,
                'turnovers' => $stats ? $stats->turnovers : 0,
                'fouls' => $stats ? $stats->fouls : 0,
                'minutes' => $stats ? $stats->minutes : 'DNP',
            ];
        })->values()->toArray();

        // Convert away team player stats to an array, including those with no recorded stats
        $awayTeamPlayersArray = $awayTeamPlayers->map(function ($player) use ($playerStats) {
            $stats = $playerStats->get($player->id);

            return [
                'player_id' => $player->id,
                'team_id' => $player->team_id,
                'name' => $player->name,
                'role' => $player->role,
                'is_rookie' => $player->is_rookie,
                'points' => $stats ? $stats->points : 0,
                'assists' => $stats ? $stats->assists : 0,
                'rebounds' => $stats ? $stats->rebounds : 0,
                'steals' => $stats ? $stats->steals : 0,
                'blocks' => $stats ? $stats->blocks : 0,
                'turnovers' => $stats ? $stats->turnovers : 0,
                'fouls' => $stats ? $stats->fouls : 0,
                'minutes' => $stats ? $stats->minutes : 'DNP',
            ];
        })->values()->toArray();

        $homeTeamRatings = $this->getTeamRatingsPerSeason($game->season_id, $game->home_id);
        $awayTeamRatings = $this->getTeamRatingsPerSeason($game->season_id, $game->away_id);
        // Format data for box score
        $boxScore = [
            'game_id' => $game->game_id,
            'round' => $game->round,
            'home_team' => [
                'team_id' => $game->home_id, // Use the correct field from your query
                'name' => $game->home_team_name,
                'score' => $game->home_score,
                'primary_color' => $game->home_primary_color, // Add primary color
                'secondary_color' => $game->home_secondary_color, // Add secondary color
                'streak' => $homeTeamStreak,
                'ratings' => $homeTeamRatings,
            ],
            'away_team' => [
                'team_id' => $game->away_id, // Use the correct field from your query
                'name' => $game->away_team_name,
                'score' => $game->away_score,
                'primary_color' => $game->away_primary_color, // Add primary color
                'secondary_color' => $game->away_secondary_color, // Add secondary color
                'streak' => $awayTeamStreak,
                'ratings' => $awayTeamRatings,
            ],
            'player_stats' => [
                'home' => $homeTeamPlayersArray,
                'away' => $awayTeamPlayersArray,
            ],
            'head_to_head_record' => $headToHeadRecord,
            'stat_leaders' => $statLeaders,
            'best_player' => $bestWinningTeamPlayerDetails,
            'total_players_played' => $playerStats->count(),
        ];

        return response()->json([
            'box_score' => $boxScore,
        ]);
    }

    private function getTeamRatingsPerSeason($seasonId, $teamId)
    {
        // Get team ratings for the given season using the player_ratings table
        $teamRatings = \DB::table('player_game_stats')
            ->join('players as p', 'player_game_stats.player_id', '=', 'p.id')
            ->join('player_ratings as pr', 'p.id', '=', 'pr.player_id')  // Join with player_ratings table
            ->join('teams as t', 'player_game_stats.team_id', '=', 't.id')
            ->select(
                't.id as team_id',
                't.name as team_name',
                'player_game_stats.season_id',
                \DB::raw('ROUND(AVG(pr.defense_rating)) as defense_rating'),
                \DB::raw('ROUND(AVG(pr.shooting_rating)) as offense_rating'), // Assuming offense is shooting_rating
                \DB::raw('ROUND(AVG(pr.passing_rating)) as passing_rating'),
                \DB::raw('ROUND(AVG(pr.rebounding_rating)) as rebounding_rating')
            )
            ->where('player_game_stats.season_id', $seasonId)  // Filter by season
            ->where('player_game_stats.team_id', $teamId)  // Filter by team_id (optional)
            ->groupBy('t.id', 't.name', 'player_game_stats.season_id')
            ->get();

        if ($teamRatings->isEmpty()) {
            return response()->json(['message' => 'No team ratings found for this season'], 404);
        }

        return $teamRatings[0];
    }


    /**
     * Function to get team streak
     */
    private function getTeamStreak($teamId, $game_id)
    {
        // Query to calculate the team's current winning or losing streak
        $streak = \DB::table('schedule_view')
            ->where(function ($query) use ($teamId) {
                $query->where('home_id', $teamId)
                    ->orWhere('away_id', $teamId);
            })
            ->where('status', 2)
            ->where('id', '<=', $game_id) // Get records with id less than or equal to game_id
            ->orderBy('id', 'desc') // Assuming game_id is the chronological identifier
            ->get();

        // Logic to determine streak type (winning or losing)
        $currentStreak = 0;
        $isWinningStreak = null;

        foreach ($streak as $game) {
            // Get the scores for the team and the opponent
            $teamScore = $game->home_id == $teamId ? $game->home_score : $game->away_score;
            $opponentScore = $game->home_id == $teamId ? $game->away_score : $game->home_score;

            // Determine win or loss
            if ($teamScore > $opponentScore) {
                // If it's a win
                if ($isWinningStreak === false) {
                    break; // Break if streak direction changes
                }
                $isWinningStreak = true; // Set streak type to winning
                $currentStreak++; // Increment the winning streak
            } else {
                // If it's a loss
                if ($isWinningStreak === true) {
                    break; // Break if streak direction changes
                }
                $isWinningStreak = false; // Set streak type to losing
                $currentStreak++; // Increment the losing streak
            }
        }

        // Determine the current streak output
        $streakResult = $currentStreak > 0 ? ($isWinningStreak ? 'W' . $currentStreak : 'L' . $currentStreak) : 'N0';

        return $streakResult;
    }

    /**
     * Function to get head-to-head record
     */
    private function getHeadToHeadRecord($homeTeamId, $awayTeamId)
    {
        $headToHead = \DB::table('schedule_view')
            ->where(function ($query) use ($homeTeamId, $awayTeamId) {
                $query->where('home_id', $homeTeamId)
                    ->where('away_id', $awayTeamId);
            })
            ->orWhere(function ($query) use ($homeTeamId, $awayTeamId) {
                $query->where('home_id', $awayTeamId)
                    ->where('away_id', $homeTeamId);
            })
            ->get();

        $homeWins = 0;
        $awayWins = 0;

        foreach ($headToHead as $game) {
            if ($game->home_id == $homeTeamId && $game->home_score > $game->away_score) {
                $homeWins++;
            } elseif ($game->away_id == $homeTeamId && $game->away_score > $game->home_score) {
                $homeWins++;
            } else {
                $awayWins++;
            }
        }

        return [
            'home_team_wins' => $homeWins,
            'away_team_wins' => $awayWins,
        ];
    }
}
