<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seasons;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionsController extends Controller
{

    public function gettransactions(Request $request)
    {
        $seasonId = $request->season_id;
        $teamId = $request->team_id;
        $type = $request->type; // 'normal' or 'notable'
        $perPage = $request->get('itemsperpage', 10); // Default items per page is 10
        $page = $request->get('page_num', 1); // Default to page 1

        // Build the initial query with necessary joins
        $query = DB::table('transactions as t')
            ->leftJoin('season_awards as sa', function ($join) {
                $join->on('sa.player_id', '=', 't.player_id');
            })
            ->leftJoin('seasons as s', 's.id', '=', 't.season_id')
            ->leftJoin('players as p', 'p.id', '=', 't.player_id')
            ->leftJoin('teams as from_team', 'from_team.id', '=', 't.from_team_id')
            ->leftJoin('teams as to_team', 'to_team.id', '=', 't.to_team_id')
            ->leftJoin('teams as award_team', 'award_team.id', '=', 'sa.team_id') // Join teams for the awards
            ->leftJoin('player_season_stats as ps', function ($join) {
                $join->on('ps.player_id', '=', 't.player_id')
                    ->on('ps.season_id', '=', 't.season_id');
            })
            ->select(
                't.id',
                't.player_id',
                't.season_id',
                't.details',
                't.from_team_id',
                't.to_team_id',
                't.status',
                'p.name as player_name',
                'p.is_active as is_active',
                'from_team.name as from_team_name',
                'to_team.name as to_team_name',
                'p.role', // Fetch player's role
                DB::raw("CASE
                WHEN sa.player_id IS NOT NULL  /* Player has an award */
                    OR s.finals_mvp_id = t.player_id  /* Player is Finals MVP */
                    OR p.role = 'star player'  /* Player is a star player */
                THEN 'notable'
                ELSE 'normal'
                END AS transaction_type"),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(sa.award_name, ' (Season: ', sa.season_id, ', Team: ', IFNULL(award_team.name, 'N/A'), ')') ORDER BY sa.season_id ASC) AS player_awards"),
                DB::raw("(SELECT CONCAT('Finals MVP (Season ', s.id, ', Team: ', t.name, ')')
                FROM seasons AS s
                LEFT JOIN teams AS t ON t.id = s.finals_winner_id
                WHERE s.finals_mvp_id = p.id
                LIMIT 1) AS finals_mvp"),
                DB::raw("(SELECT CONCAT('Finals Winner (Season ', s.id, ', Team: ', winner_team.name, ')')
                FROM seasons AS s
                LEFT JOIN teams AS winner_team ON winner_team.id = s.finals_winner_id
                WHERE s.finals_winner_id = p.id
                LIMIT 1) AS player_finals_winner"),
                DB::raw("CASE
                    WHEN s.finals_mvp_id = p.id THEN 1
                    ELSE 0
                END as is_finals_mvp"),
                // Fetch player career championships (all seasons they were champions)
                DB::raw("MAX(CASE WHEN t.status = 'retired' THEN 1 ELSE 0 END) AS is_retired"),  // Check if the player has any 'retired' status
                's.finals_winner_name'  // Add finals_winner_name from the seasons table
            )
            ->whereNotIn('t.status', ['draft', 'released']); // Filter out 'draft' and 'released' transactions

        // Apply filters for 'normal' or 'notable' transaction type based on the CASE logic
        if ($type) {
            $query->whereRaw("
                (sa.player_id IS NOT NULL
                OR s.finals_mvp_id = t.player_id
                OR p.role = 'star player') = ?
            ", [$type === 'notable' ? 1 : 0]);
        }

        // Apply season_id filter if provided (if you want transactions from a specific season)
        if ($seasonId) {
            $query->where('t.season_id', $seasonId);
        }

        // Apply team_id filter if provided (filter by from_team_id or to_team_id)
        if ($teamId) {
            $query->where(function ($subQuery) use ($teamId) {
                $subQuery->where('t.from_team_id', $teamId)
                    ->orWhere('t.to_team_id', $teamId);
            });
        }

        // Add GROUP BY clause to ensure proper grouping for each transaction and player
        $query->groupBy(
            't.id',
            't.player_id',
            't.season_id',
            'sa.player_id',
            't.details',
            't.from_team_id',
            't.to_team_id',
            't.status',
            'p.name',
            'p.is_active',
            'from_team.name',
            'to_team.name',
            'p.role',
            's.id',
            'p.id',
            's.finals_mvp_id',
            'award_team.name',
            's.champion_id',       // Added champion_id in GROUP BY
            's.finals_winner_id',   // Added finals_winner_id in GROUP BY
            's.finals_winner_name'  // Added finals_winner_name in GROUP BY
        );

        // Fetch all transactions without pagination
        $transactions = $query->get();


        foreach ($transactions as $transaction) {
            $playerId = $transaction->player_id;
            $championships = \DB::table('seasons')
                ->join('player_game_stats', 'seasons.id', '=', 'player_game_stats.season_id')
                ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
                ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
                ->select('seasons.id as season_id', 'teams.name as championship_team', 'seasons.name as championship_season')
                ->where('player_game_stats.player_id',  $playerId)
                ->where('schedules.round', 'finals')
                ->whereColumn('seasons.id', 'player_game_stats.season_id')
                ->whereExists(function ($query) use ($playerId) {
                    $query->select(\DB::raw(1))
                        ->from('schedules as s')
                        ->join('player_game_stats as pg', 's.game_id', '=', 'pg.game_id')
                        ->where('pg.team_id', '=', \DB::raw('player_game_stats.team_id'))
                        ->where('s.round', 'finals')
                        ->where('pg.player_id',  $playerId)
                        ->whereColumn('pg.season_id', 'player_game_stats.season_id')
                        ->where(function ($q) {
                            $q->where(function ($q) {
                                $q->whereColumn('s.home_id', 'player_game_stats.team_id')
                                    ->whereColumn('s.home_score', '>', 's.away_score');
                            })
                                ->orWhere(function ($q) {
                                    $q->whereColumn('s.away_id', 'player_game_stats.team_id')
                                        ->whereColumn('s.away_score', '>', 's.home_score');
                                });
                        });
                })
                ->groupBy('seasons.id', 'teams.name', 'seasons.name') // Group by season_id, championship team and season name
                ->get();

            // Convert the championships to a comma-separated string
            $championshipsFormatted = $championships->map(function ($championship) {
                return "{$championship->championship_season} Champion ({$championship->championship_team})";
            })->implode(', ');
            // Assign the championship data to the transaction, if available
            $transaction->player_career_championships = $championshipsFormatted;

            // If the player is retired, set their status as 'retired'
            if ($transaction->is_retired) {
                $transaction->status = 'retired';
            }

            // Remove the temporary 'is_retired' field from the response
            unset($transaction->is_retired);
        }


        // Return the data with total_items set to 0 (no pagination)
        return response()->json([
            'data' => $transactions,  // The actual data for all transactions
            'current_page' => 1,      // Page 1 (since we're not paginating)
            'total_items' => 0,       // Set total_items to 0 as requested
            'total_pages' => 1,       // One page since no pagination
            'per_page' => count($transactions),  // The number of items fetched
        ]);
    }

    // Waive a player (make them inactive)
    public function waiveplayer(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:players,id',
        ]);

        $player = Player::findOrFail($request->id);
        $player->update(['is_active' => false, 'team_id' => null]);

        return response()->json([
            'message' => 'Player waived successfully',
            'player' => $player,
        ]);
    }

    // Extend player's contract
    public function extendcontract(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:players,id',
            'additional_years' => 'required|integer|min:1|max:5',
        ]);

        $player = Player::findOrFail($request->id);

        $newContractEnd = $player->contract_expires_at
            ? $player->contract_expires_at->addYears($request->additional_years)
            : now()->addYears($request->additional_years);

        $player->update([
            'contract_years' => min($player->contract_years + $request->additional_years, 5),
            'contract_expires_at' => $newContractEnd,
        ]);

        return response()->json([
            'message' => 'Contract extended successfully',
            'player' => $player,
        ]);
    }


    public function assignplayertorandomteam(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        // Fetch teams with fewer than 15 players
        $teamIds = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id')
            ->groupBy('teams.id')
            ->havingRaw('SUM(CASE WHEN players.is_active = 1 THEN 1 ELSE 0 END) < 15')
            ->pluck('teams.id');


        $teamsCount = $teamIds->count();

        if ($teamsCount === 0) {
            return response()->json([
                'message' => 'No teams available with fewer than 15 players.',
            ], 400);
        }

        // Select a random team
        $teamId = $teamIds->random();

        // Fetch the player
        $player = Player::find($request->player_id);

        if (!$player) {
            return response()->json([
                'message' => 'Player not found.',
            ], 404);
        }

        // Set contract years based on the player's role
        $contractYears = 1; // Default value
        switch ($player->role) {
            case 'star player':
                $contractYears = 5;
                break;
            case 'starter':
                $contractYears = 3;
                break;
            case 'role player':
                $contractYears = 2;
                break;
            case 'bench':
                $contractYears = 1;
                break;
        }

        // Update the player's team and contract years
        $player->update([
            'team_id' => $teamId,
            'contract_years' => $contractYears,
        ]);

        return response()->json([
            'message' => 'Player successfully assigned to a team. Remaining Teams that needed players: ' . $teamsCount,
            'team_id' => $teamId,
            'team_count' =>  $teamsCount,
        ]);
    }
    public function assignremainingfreeagents()
    {

        $seasonId = $this->getLatestSeasonId();
        $currentseasonId = $seasonId + 1;
        // Fetch teams with fewer than 15 players
        $teamsWithFewMembers = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.id', 'teams.name')
            ->havingRaw('COUNT(players.id) < 15')
            ->get();

        // Fetch free agents (players with team_id = 0)
        // $freeAgents = Player::where('team_id', 0)
        //     ->where('is_active', 1)
        //     ->orderBy("overall_rating", "desc")
        //     ->get();

        $freeAgents = $this->getFreeAgentsByCompositeScore($seasonId);
        $remainingFreeAgents = $freeAgents->count();
        $teamsCount = $teamsWithFewMembers->count();

        if ($teamsCount === 0) {
            // Update the last season's status to 15 if there are no incomplete teams
            // Update player roles based on the last season's stats
            $update = ($seasonId == 0) ? $this->updateTeamRolesBasedOnStatsByRating() : $this->updateTeamRolesBasedOnStats();
            // $update = true;
            if ($update) {
                // After drafting logic but before DB::commit()

                if ($seasonId == 0) {
                    DB::table('players')
                        ->where('draft_id', 1)
                        ->where('is_drafted', 0)
                        ->update([
                            'draft_id' => 1,
                            'team_id' => 0,
                            'contract_years' => 0,
                            'draft_status' => 'Undrafted',
                            'is_rookie' => 1,
                        ]);
                } else {
                    DB::table('seasons')
                        ->where('id',  $seasonId)
                        ->update(['status' => 15]);
                }

                return response()->json([
                    'error' => true,
                    'message' => 'All teams have signed 15 players, and roles have been updated based on last season\'s stats.',
                    'team_count' => $teamsCount,
                ], 401);
            } else {
                return response()->json([
                    'message' => 'Role assigning error!',
                    'update' => $update,
                ], 400);
            }
        } else {
            if ($remainingFreeAgents === 0) {
                $incompleteTeams = $teamsWithFewMembers->map(function ($team) {
                    $playersNeeded = 15 - $team->player_count;
                    return [
                        'team_name' => $team->name,
                        'players_needed' => $playersNeeded,
                    ];
                })->filter(function ($team) {
                    return $team['players_needed'] > 0;
                });

                return response()->json([
                    'message' => 'No free agents available.',
                    'incomplete_teams' => $incompleteTeams,
                ], 400);
            }

            // Randomly assign each free agent to a team with fewer than 15 players
            foreach ($freeAgents as $agent) {
                if ($remainingFreeAgents <= 0) break;

                // Get the player's last team from player_season_stats
                $lastSeasonStats = DB::table('player_season_stats')
                    ->where('player_id', $agent->id)
                    ->orderBy('season_id', 'desc') // Assuming season_id indicates the order of seasons
                    ->first(['team_id']);

                $fromTeamId = $lastSeasonStats ? $lastSeasonStats->team_id : 0; // Default to 0 if no stats

                // Randomly select a team from the incomplete teams
                $team = $teamsWithFewMembers->random();
                $playersNeeded = 15 - $team->player_count;

                // Determine contract years based on the agent's role
                $contractYears = $this->determineContractYears($agent->role);

                // Update the player's team and contract years using DB
                DB::table('players')
                    ->where('id', $agent->id)
                    ->update([
                        'team_id' => $team->id,
                        'contract_years' => $contractYears,
                    ]);

                // Log the transaction for transfer
                $fromTeamName = $fromTeamId ? DB::table('teams')->where('id', $fromTeamId)->value('name') : 'Free Agent';
                $toTeamName = $team->name;

                DB::table('transactions')->insert([
                    'player_id' => $agent->id,
                    'season_id' => $currentseasonId,
                    'details' => 'Transferred from ' . $fromTeamName . ' to ' . $toTeamName,
                    'from_team_id' => $fromTeamId,
                    'to_team_id' => $team->id,
                    'status' => 'transfer',
                ]);

                // Log the transaction for signing
                if ($contractYears > 0) {
                    DB::table('transactions')->insert([
                        'player_id' => $agent->id,
                        'season_id' => $currentseasonId,
                        'details' => 'Signed with ' . $toTeamName . ' for contract of ' . $contractYears . ' years',
                        'from_team_id' => $fromTeamId,
                        'to_team_id' => $team->id,
                        'status' => 'signed',
                    ]);
                }

                // Special draft logic if seasonId is 0
                if ($seasonId == 0) {
                    DB::table('players')
                        ->where('id', $agent->id)
                        ->update([
                            'draft_id' => 1,
                            'draft_order' => 0,
                            'drafted_team_id' => $team->id,
                            'is_drafted' => 1,
                            'draft_status' => 'Special Draft',
                        ]);
                }

                // Reduce the number of players needed for that team
                $team->player_count++;

                // Remove the team from the list if it no longer needs more players
                if ($playersNeeded <= 1) {
                    $teamsWithFewMembers = $teamsWithFewMembers->filter(function ($t) use ($team) {
                        return $t->id !== $team->id;
                    });
                }

                $remainingFreeAgents--;
            }


            // Check for incomplete teams after assignment
            $incompleteTeams = DB::table('teams')
                ->leftJoin('players', 'teams.id', '=', 'players.team_id')
                ->select('teams.name', DB::raw('COUNT(players.id) as player_count'))
                ->groupBy('teams.name')
                ->havingRaw('COUNT(players.id) < 15')
                ->get()
                ->map(function ($team) {
                    $playersNeeded = 15 - $team->player_count;
                    return [
                        'team_name' => $team->name,
                        'players_needed' => $playersNeeded,
                    ];
                })
                ->filter(function ($team) {
                    return $team['players_needed'] > 0;
                });

            return response()->json([
                'message' => 'Free agents have been assigned to teams.',
                'remaining_free_agents' => $remainingFreeAgents,
                'incomplete_teams' => $incompleteTeams,
            ]);
        }
    }

    private function updateTeamRolesBasedOnStatsByRating()
    {
        $seasonId = $this->getLatestSeasonId();
        $teams = DB::table('teams')->pluck('id');

        foreach ($teams as $teamId) {
            DB::beginTransaction();

            try {
                // Fetch all players on the team, ordered by overall rating (veterans and rookies combined)
                $players = DB::table('players')
                    ->where('team_id', $teamId)
                    ->orderByDesc('overall_rating')
                    ->get();

                // Initialize arrays for assigning roles
                $starPlayers = [];
                $starters = [];
                $rolePlayers = [];
                $benchPlayers = [];

                // Assign roles based on overall rating
                foreach ($players as $index => $player) {
                    if (count($starPlayers) < 3) {
                        $starPlayers[] = $player->id;
                    } elseif (count($starters) < 2) {
                        $starters[] = $player->id;
                    } elseif (count($rolePlayers) < 5) {
                        $rolePlayers[] = $player->id;
                    } else {
                        $benchPlayers[] = $player->id;
                    }
                }

                // Update each player's role in the database
                DB::table('players')->whereIn('id', $starPlayers)->update(['role' => 'star player']);
                DB::table('players')->whereIn('id', $starters)->update(['role' => 'starter']);
                DB::table('players')->whereIn('id', $rolePlayers)->update(['role' => 'role player']);
                DB::table('players')->whereIn('id', $benchPlayers)->update(['role' => 'bench']);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                // Log the error message and stack trace for debugging
                \Log::error('Error assigning role for team ' . $teamId . ': ' . $e->getMessage());

                return false; // Return false if an error occurs during the update
            }
        }

        return true; // Return true if all updates succeed
    }

    private function updateTeamRolesBasedOnStats()
    {
        $seasonId = $this->getLatestSeasonId();
        $teams = DB::table('teams')->pluck('id');

        foreach ($teams as $teamId) {
            DB::beginTransaction();

            try {
                // Fetch player stats for the previous season
                $stats = DB::table('player_season_stats')
                    ->join('players', 'player_season_stats.player_id', '=', 'players.id')
                    ->where('player_season_stats.season_id', $seasonId)
                    ->where('players.team_id', $teamId)
                    ->get();

                // Fetch rookies or players with no stats
                $playersWithoutStats = DB::table('players')
                    ->where('team_id', $teamId)
                    ->whereNotIn('id', $stats->pluck('player_id'))
                    ->get();

                // Merge all players
                $allPlayersStats = $stats->merge($playersWithoutStats->map(function ($player) {
                    return (object)[
                        'player_id' => $player->id,
                        'role' => 'bench',  // Default role
                        'avg_points_per_game' => 0,
                        'avg_rebounds_per_game' => 0,
                        'avg_assists_per_game' => 0,
                        'avg_steals_per_game' => 0,
                        'avg_blocks_per_game' => 0,
                        'avg_turnovers_per_game' => 0,
                        'avg_fouls_per_game' => 0,
                        'total_points' => 0,
                        'total_rebounds' => 0,
                        'total_assists' => 0,
                        'total_steals' => 0,
                        'total_blocks' => 0,
                        'total_turnovers' => 0,
                        'total_fouls' => 0,
                        'total_games_played' => 0,
                        'overall_rating' => $player->overall_rating ?? 50, // Default low rating if missing
                        'injury_prone_percentage' => $player->injury_prone_percentage ?? 50,
                        'is_rookie' => $player->is_rookie ?? 0, // Identify rookies
                    ];
                }));

                // Sort players based on the composite score
                $rankedPlayers = $allPlayersStats->sortByDesc(function ($stat) {
                    // Composite score for non-rookies
                    $perGameScore = $stat->avg_points_per_game * 0.3 +
                        $stat->avg_rebounds_per_game * 0.2 +
                        $stat->avg_assists_per_game * 0.2 +
                        $stat->avg_steals_per_game * 0.1 +
                        $stat->avg_blocks_per_game * 0.1 -
                        $stat->avg_turnovers_per_game * 0.1 -
                        $stat->avg_fouls_per_game * 0.1;

                    $totalScore = $stat->total_points * 0.2 +
                        $stat->total_rebounds * 0.2 +
                        $stat->total_assists * 0.2 +
                        $stat->total_steals * 0.15 +
                        $stat->total_blocks * 0.15 -
                        $stat->total_turnovers * 0.1 -
                        $stat->total_fouls * 0.1;

                    $injuryFactor = 1 - ($stat->injury_prone_percentage / 100);

                    // Handle rookies
                    if ($stat->is_rookie) {
                        return $stat->overall_rating * 1.5; // Weight rookie's rating higher
                    }

                    return ($perGameScore + $totalScore) * $injuryFactor;
                });

                // Assign roles based on thresholds
                foreach ($rankedPlayers as $playerStat) {
                    $score = $playerStat->is_rookie
                        ? $playerStat->overall_rating // Use rating for rookies
                        : $playerStat->composite_score; // Use composite score for veterans

                    $role = 'bench'; // Default role
                    if ($score >= 85) {
                        $role = 'star player';
                    } elseif ($score >= 70) {
                        $role = 'starter';
                    } elseif ($score >= 55) {
                        $role = 'role player';
                    }

                    Player::where('id', $playerStat->player_id)->update(['role' => $role]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error assigning role for team ' . $teamId . ': ' . $e->getMessage());
                return $e->getMessage();
            }
        }

        return true;
    }

    private function determineContractYears($role)
    {
        switch ($role) {
            case 'star player':
                return rand(3, 7);
            case 'starter':
                return rand(1, 5);
            case 'role player':
                return rand(1, 3);
            case 'bench':
                return rand(1, 2);
            default:
                return 1;
        }
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
    private function getFreeAgentsByCompositeScore($currentSeasonId)
    {
        $freeAgents = DB::table('players')
            ->leftJoin('player_season_stats', function ($join) use ($currentSeasonId) {
                $join->on('players.id', '=', 'player_season_stats.player_id')
                    ->where('player_season_stats.season_id', '=', $currentSeasonId);
            })
            // Join with seasons to check for finals MVP
            ->leftJoin('seasons', function ($join) use ($currentSeasonId) {
                $join->on('seasons.id', '=', DB::raw("{$currentSeasonId}"))
                    ->whereRaw('seasons.finals_mvp_id = players.id');
            })
            ->selectRaw("
                players.*,
                -- Calculate composite score (just once)
                COALESCE((
                    (
                        player_season_stats.avg_points_per_game * 0.3 +
                        player_season_stats.avg_rebounds_per_game * 0.2 +
                        player_season_stats.avg_assists_per_game * 0.2 +
                        player_season_stats.avg_steals_per_game * 0.1 +
                        player_season_stats.avg_blocks_per_game * 0.1 -
                        player_season_stats.avg_turnovers_per_game * 0.1 -
                        player_season_stats.avg_fouls_per_game * 0.1
                    ) +
                    (
                        player_season_stats.total_points * 0.2 +
                        player_season_stats.total_rebounds * 0.2 +
                        player_season_stats.total_assists * 0.2 +
                        player_season_stats.total_steals * 0.15 +
                        player_season_stats.total_blocks * 0.15 -
                        player_season_stats.total_turnovers * 0.1 -
                        player_season_stats.total_fouls * 0.1
                    )
                ) * (1 - (COALESCE(players.injury_prone_percentage, 50) / 100)), players.overall_rating * 1.5) as composite_score,

                -- Add finals MVP bonus
                CASE WHEN seasons.finals_mvp_id = players.id THEN 50 ELSE 0 END as finals_mvp_bonus,

                -- Compute total score by summing composite score and finals MVP bonus
                COALESCE((
                    (
                        player_season_stats.avg_points_per_game * 0.3 +
                        player_season_stats.avg_rebounds_per_game * 0.2 +
                        player_season_stats.avg_assists_per_game * 0.2 +
                        player_season_stats.avg_steals_per_game * 0.1 +
                        player_season_stats.avg_blocks_per_game * 0.1 -
                        player_season_stats.avg_turnovers_per_game * 0.1 -
                        player_season_stats.avg_fouls_per_game * 0.1
                    ) +
                    (
                        player_season_stats.total_points * 0.2 +
                        player_season_stats.total_rebounds * 0.2 +
                        player_season_stats.total_assists * 0.2 +
                        player_season_stats.total_steals * 0.15 +
                        player_season_stats.total_blocks * 0.15 -
                        player_season_stats.total_turnovers * 0.1 -
                        player_season_stats.total_fouls * 0.1
                    )
                ) * (1 - (COALESCE(players.injury_prone_percentage, 50) / 100)), players.overall_rating * 1.5)
                +
                CASE WHEN seasons.finals_mvp_id = players.id THEN 50 ELSE 0 END
                AS total_score
            ")
            ->where('players.team_id', 0)  // Ensure the player is a free agent
            ->where('players.is_active', 1)  // Ensure the player is active
            ->where('players.is_injured', 0)      // Ensure player is not injured
            ->orderByDesc('total_score')  // Order by total score (composite score + MVP bonus)
            ->get();

        return $freeAgents;
    }

}
