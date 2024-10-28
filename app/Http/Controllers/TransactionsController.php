<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seasons;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionsController extends Controller
{
    public function index()
    {
        return Inertia::render('Transactions/Index', [
            'status' => session('status'),
        ]);
    }

    public function assignPlayerToRandomTeam(Request $request)
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
    public function assignRemainingFreeAgentsV1()
    {
        // Fetch teams with fewer than 15 players
        $teamsWithFewMembers = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.id', 'teams.name')
            ->havingRaw('COUNT(players.id) < 15')
            ->get();

        // Fetch free agents (players with team_id = 0)
        $freeAgents = Player::where('team_id', 0)
            ->where('contract_years', 0)
            ->where('is_active', 1)
            ->orderByRaw("FIELD(role, 'star player', 'starter', 'role player', 'bench')")
            ->get();

        $remainingFreeAgents = $freeAgents->count();
        $teamsCount = $teamsWithFewMembers->count();

        if ($teamsCount === 0) {
            // Update the last season's status to 12 if there are no incomplete teams
            DB::table('seasons')
                ->where('id', $this->getLatestSeasonId())
                ->update(['status' => 12]);

            // Update player roles based on the last season's stats
            $update = $this->updateTeamRolesBasedOnStats();
            if ($update) {
                return response()->json([
                    'error' => true,
                    'message' => 'All teams have signed 15 players, and roles have been updated based on last season\'s stats.',
                    'team_count' => $teamsCount,
                ], 401);
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

                // Randomly select a team from the incomplete teams
                $team = $teamsWithFewMembers->random();
                $playersNeeded = 15 - $team->player_count;

                // Update the agent's team and contract years
                $agent->team_id = $team->id;
                $agent->contract_years = $this->determineContractYears($agent->role);
                $agent->save();

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
    public function assignRemainingFreeAgents()
    {
        // Fetch teams with fewer than 15 players
        $teamsWithFewMembers = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.id', 'teams.name')
            ->havingRaw('COUNT(players.id) < 15')
            ->get();

        // Fetch free agents (players with team_id = 0)
        $freeAgents = Player::where('team_id', 0)
            ->where('contract_years', 0)
            ->where('is_active', 1)
            ->orderByRaw("FIELD(role, 'star player', 'starter', 'role player', 'bench')")
            ->get();

        $remainingFreeAgents = $freeAgents->count();
        $teamsCount = $teamsWithFewMembers->count();

        if ($teamsCount === 0) {
            // Update the last season's status to 12 if there are no incomplete teams
            DB::table('seasons')
                ->where('id', $this->getLatestSeasonId())
                ->update(['status' => 12]);

            // Update player roles based on the last season's stats
            $update = $this->updateTeamRolesBasedOnStats();
            if ($update) {
                return response()->json([
                    'error' => true,
                    'message' => 'All teams have signed 15 players, and roles have been updated based on last season\'s stats.',
                    'team_count' => $teamsCount,
                ], 401);
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

                // Update the agent's team and contract years
                $agent->team_id = $team->id;
                $agent->contract_years = $this->determineContractYears($agent->role);
                $agent->save();

                // Log the transaction
                DB::table('transactions')->insert([
                    'player_id' => $agent->id,
                    'details' => 'Signed to ' . $team->name,
                    'from_team_id' => $fromTeamId,
                    'to_team_id' => $team->id,
                    'status' => 'signed',
                ]);

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

    private function updateTeamRolesBasedOnStatsV1()
    {
        // Fetch player stats for the last season
        $seasonId = $this->getLatestSeasonId();
        $teams = DB::table('teams')->pluck('id');

        foreach ($teams as $teamId) {
            // Fetch players for each team based on last season's stats (excluding rookies)
            $playerStats = DB::table('player_season_stats')
                ->join('players', 'player_season_stats.player_id', '=', 'players.id')
                ->where('player_season_stats.season_id', $seasonId)
                ->where('player_season_stats.team_id', $teamId) // Specify the table for team_id
                ->where('players.is_rookie', 0) // Exclude rookies
                ->orderByDesc(DB::raw('(avg_points_per_game + avg_rebounds_per_game + avg_assists_per_game + avg_steals_per_game + avg_blocks_per_game)'))
                ->get();

            // Assign roles to non-rookies first
            foreach ($playerStats as $index => $playerStat) {
                $role = '';

                if ($index < 3) {
                    $role = 'star player';
                } elseif ($index < 5) {
                    $role = 'starter';
                } elseif ($index < 10) {
                    $role = 'role player';
                } else {
                    $role = 'bench';
                }

                // Update the player's role in the database
                DB::table('players')
                    ->where('id', $playerStat->player_id)
                    ->update(['role' => $role]);
            }

            // Fetch rookies and order them by overall_rating
            $rookies = DB::table('players')
                ->where('team_id', $teamId)
                ->where('is_rookie', 1)
                ->orderByDesc('overall_rating') // Sort rookies by their overall_rating
                ->get();

            // Assign roles to rookies based on remaining spots
            foreach ($rookies as $rookie) {
                $role = '';

                // Get the current role count for the team
                $roleCount = DB::table('players')
                    ->where('team_id', $teamId)
                    ->whereNotNull('role') // Check if the role is already assigned
                    ->count();

                // Assign remaining roles to rookies
                if ($roleCount < 3) {
                    $role = 'star player';
                } elseif ($roleCount < 5) {
                    $role = 'starter';
                } elseif ($roleCount < 9) {
                    $role = 'role player';
                } else {
                    $role = 'bench';
                }

                // Update rookie's role in the database
                DB::table('players')
                    ->where('id', $rookie->id)
                    ->update(['role' => $role]);

                // Increment the role count for the team
                $roleCount++;
            }
        }

        return true;
    }
    private function updateTeamRolesBasedOnStats()
    {
        // Fetch player stats for the last season
        $seasonId = $this->getLatestSeasonId();
        $teams = DB::table('teams')->pluck('id');

        foreach ($teams as $teamId) {
            // Fetch all players for each team (including rookies and non-rookies), ranked by overall_rating
            $players = DB::table('players')
                ->where('players.team_id', $teamId)
                ->where('players.is_active', 1)
                ->orderByDesc('players.overall_rating') // Sort by overall_rating
                ->get();

            // Assign roles to players based on overall_rating
            foreach ($players as $index => $player) {
                $role = '';

                // Assign roles based on the index in the sorted list
                if ($index < 3) {
                    $role = 'star player'; // Top 3 players
                } elseif ($index < 5) {
                    $role = 'starter'; // Next 2 players
                } elseif ($index < 10) {
                    $role = 'role player'; // Next 5 players
                } else {
                    $role = 'bench'; // Remaining players
                }

                // Update the player's role in the database
                DB::table('players')
                    ->where('id', $player->id)
                    ->update(['role' => $role]);
            }
        }

        // Update injury_prone_percentage for 10% to 30% of players around the league
        $totalPlayers = DB::table('players')->where('is_active', 1)->count();
        $playersToUpdate = rand(ceil($totalPlayers * 0.1), ceil($totalPlayers * 0.3));

        // Select random players to update their injury_prone_percentage
        $randomPlayers = DB::table('players')
            ->where('is_active', 1)
            ->inRandomOrder()
            ->limit($playersToUpdate)
            ->get();

        foreach ($randomPlayers as $player) {
            $injuryPronePercentage = rand(0, 100); // Random value between 0% and 100%

            // Update injury_prone_percentage for the player
            DB::table('players')
                ->where('id', $player->id)
                ->update(['injury_prone_percentage' => $injuryPronePercentage]);
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
        }

        // Handle the case where no seasons are found
        throw new \Exception('No seasons found.');
    }
}
