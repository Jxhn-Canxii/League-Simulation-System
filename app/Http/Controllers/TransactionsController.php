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

        // Fetch teams with fewer than 12 players
        $teamIds = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id')
            ->groupBy('teams.id')
            ->havingRaw('SUM(CASE WHEN players.is_active = 1 THEN 1 ELSE 0 END) < 12')
            ->pluck('teams.id');


        $teamsCount = $teamIds->count();

        if ($teamsCount === 0) {
            return response()->json([
                'message' => 'No teams available with fewer than 12 players.',
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
        // Fetch teams with fewer than 12 players
        $teamsWithFewMembers = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.id', 'teams.name')
            ->havingRaw('COUNT(players.id) < 12')
            ->get();

        // Fetch free agents (players with team_id = 0)
        $freeAgents = Player::where('team_id', 0)
            ->where('contract_years', 0)
            ->where('is_active', 1)
            ->orderByRaw("FIELD(role, 'star player', 'starter', 'role player', 'bench')")
            ->get();

        $remainingFreeAgents = $freeAgents->count();
        $teamsCount = $teamsWithFewMembers->count();
        if ($teamsCount <= 0) {
            // Update the last season's status to 10 if there are no incomplete teams
            DB::table('seasons')
                ->where('id', $this->getLatestSeasonId())
                ->update(['status' => 10]);

            return response()->json([
                'error' => false,
                'message' => 'All teams has signed 12 players',
                'team_count' =>  $teamsCount,
            ], 200);
        }
        if ($remainingFreeAgents === 0) {
            $incompleteTeams = $teamsWithFewMembers->map(function ($team) {
                $playersNeeded = 12 - $team->player_count;
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

        // Randomly assign each free agent to a team with fewer than 12 players
        foreach ($freeAgents as $agent) {
            // Randomly select a team from the incomplete teams
            $team = $teamsWithFewMembers->random();
            $playersNeeded = 12 - $team->player_count;

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
            if ($remainingFreeAgents <= 0) {
                break;
            }
        }

        // Check for incomplete teams after assignment
        $incompleteTeams = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.name')
            ->havingRaw('COUNT(players.id) < 12')
            ->get()
            ->map(function ($team) {
                $playersNeeded = 12 - $team->player_count;
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
    public function assignRemainingFreeAgents()
    {
        // Fetch teams with fewer than 12 players
        $teamsWithFewMembers = DB::table('teams')
            ->leftJoin('players', 'teams.id', '=', 'players.team_id')
            ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
            ->groupBy('teams.id', 'teams.name')
            ->havingRaw('COUNT(players.id) < 12')
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
            // Update the last season's status to 10 if there are no incomplete teams
            DB::table('seasons')
                ->where('id', $this->getLatestSeasonId())
                ->update(['status' => 10]);

            return response()->json([
                'error' => false,
                'message' => 'All teams have signed 12 players.',
                'team_count' => $teamsCount,
            ], 200);
        }

        if ($remainingFreeAgents === 0) {
            $incompleteTeams = $teamsWithFewMembers->map(function ($team) {
                $playersNeeded = 12 - $team->player_count;
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
        } else {
            // Randomly assign each free agent to a team with fewer than 12 players
            foreach ($freeAgents as $agent) {
                if ($remainingFreeAgents <= 0) break;

                // Randomly select a team from the incomplete teams
                $team = $teamsWithFewMembers->random();
                $playersNeeded = 12 - $team->player_count;

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
                ->havingRaw('COUNT(players.id) < 12')
                ->get()
                ->map(function ($team) {
                    $playersNeeded = 12 - $team->player_count;
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
