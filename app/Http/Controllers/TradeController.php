<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

use Illuminate\Http\Request;
use App\Models\Seasons;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TradeController extends Controller
{
    public function index()
    {
        return Inertia::render('Analytics/Index', [
            'status' => session('status'),
        ]);
    }
    public function getTradeLogs(Request $request)
    {
        $logs = \DB::table('trade_logs')
            ->join('teams as team_from', 'trade_logs.team_from_id', '=', 'team_from.id')
            ->join('teams as team_to', 'trade_logs.team_to_id', '=', 'team_to.id')
            ->select(
                'trade_logs.id',
                'trade_logs.player_name',
                'trade_logs.role',
                'team_from.name as from_team',
                'team_to.name as to_team',
                'trade_logs.trade_reason',
                'trade_logs.created_at'
            )
            ->orderBy('trade_logs.created_at', 'desc')
            ->get();

        return response()->json(['trade_logs' => $logs]);
    }

    private function calculatePlayerScore($playerStats) {
        return $playerStats->avg_points_per_game * 1.0 +
               $playerStats->avg_rebounds_per_game * 1.2 +
               $playerStats->avg_assists_per_game * 1.5 +
               $playerStats->avg_steals_per_game * 2.0 +
               $playerStats->avg_blocks_per_game * 2.0 -
               $playerStats->avg_turnovers_per_game * 1.5;
    }
    public function executeTrade($playerFromTeam1, $playerFromTeam2) {
        \DB::transaction(function () use ($playerFromTeam1, $playerFromTeam2) {
            \DB::table('players')
                ->where('id', $playerFromTeam1->id)
                ->update(['team_id' => $playerFromTeam2->team_id]);

            \DB::table('players')
                ->where('id', $playerFromTeam2->id)
                ->update(['team_id' => $playerFromTeam1->team_id]);
        });

        return [
            'team1_received' => $playerFromTeam2->name,
            'team2_received' => $playerFromTeam1->name,
        ];
    }
    public function autoMultiTeamTrade() {
        $roles = ['star player', 'starter', 'role player', 'bench'];

        foreach ($roles as $role) {
            // Find participants for multi-team trade
            $tradeParticipants = $this->findMultiTeamTradeParticipants($role);

            if ($tradeParticipants) {
                // Generate a random number between 1 and 100
                $tradeChance = rand(1, 100);

                // Only proceed with trade if the chance is between 20 and 30 (inclusive)
                if ($tradeChance >= 20 && $tradeChance <= 30) {
                    // Execute multi-team trade
                    $result = $this->executeMultiTeamTrade($tradeParticipants);

                    \Log::info('Multi-team trade result:', [
                        'role' => $role,
                        'teams' => array_keys($tradeParticipants),
                        'players' => array_column($tradeParticipants, 'name'),
                        'trade_chance' => $tradeChance, // Log the chance for transparency
                        'trade_executed' => true,
                    ]);
                } else {
                    // Log that trade was skipped due to low chance
                    \Log::info('Trade skipped due to low chance:', [
                        'role' => $role,
                        'teams' => array_keys($tradeParticipants),
                        'players' => array_column($tradeParticipants, 'name'),
                        'trade_chance' => $tradeChance, // Log the chance for transparency
                        'trade_executed' => false,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'All multi-team trades completed.']);
    }


    private function executeMultiTeamTrade($tradeParticipants)
    {
        $playerIds = array_keys($tradeParticipants); // Extract player IDs
        $teamIds = array_values(array_column($tradeParticipants, 'team_id'));

        \DB::transaction(function () use ($playerIds, $teamIds) {
            $numTeams = count($teamIds);
            for ($i = 0; $i < $numTeams; $i++) {
                $currentTeam = $teamIds[$i];
                $nextTeam = $teamIds[($i + 1) % $numTeams]; // Circular: last team trades to first

                \DB::table('players')
                    ->where('id', $playerIds[$i])
                    ->update(['team_id' => $nextTeam]);

                // Log the trade
                $this->logTrade($currentTeam, $nextTeam, $playerIds[$i], 'Multi-team trade executed');
            }
        });

        return response()->json(['message' => 'Multi-team trade executed successfully.']);
    }

    private function findMultiTeamTradeParticipants($role) {
        $teams = \DB::table('teams')->pluck('id'); // Fetch all team IDs
        $tradeCandidates = [];

        foreach ($teams as $teamId) {
            $underperformingPlayer = $this->findUnderperformingPlayers($teamId, $role);
            if ($underperformingPlayer) {
                $tradeCandidates[$teamId] = $underperformingPlayer;
            }
        }

        // Return only teams with trade candidates, and ensure at least two teams
        return count($tradeCandidates) >= 2 ? $tradeCandidates : null; // Minimum 2 teams
    }


    private function latestSeasonId() {
        $latestSeasonId = \DB::table('seasons')
        ->orderBy('id', 'desc')
        ->value('id'); // Fetch the ID of the latest season

        return $latestSeasonId;
    }
    private function findUnderperformingPlayers($teamId, $role) {
        $latestSeasonId = $this->latestSeasonId();

        return \DB::table('players')
            ->join('player_season_stats', 'players.id', '=', 'player_season_stats.player_id')
            ->where('players.team_id', $teamId)
            ->where('players.role', $role)
            ->where('player_season_stats.season_id', $latestSeasonId) // Filter for the latest season
            ->select('players.id', 'players.name', 'players.role', 'player_season_stats.*')
            ->orderByRaw('
                (avg_points_per_game * 1.0 + avg_rebounds_per_game * 1.2 + avg_assists_per_game * 1.5 +
                 avg_steals_per_game * 2.0 + avg_blocks_per_game * 2.0 - avg_turnovers_per_game * 1.5)
            ASC') // Sort by lowest performance score
            ->first();
    }
    public function logTrade($teamFromId, $teamToId, $playerId, $reason = null)
    {
        $player = \DB::table('players')->find($playerId);

        \DB::table('trade_logs')->insert([
            'team_from_id' => $teamFromId,
            'team_to_id' => $teamToId,
            'player_id' => $playerId,
            'player_name' => $player->name,
            'role' => $player->role,
            'trade_reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
