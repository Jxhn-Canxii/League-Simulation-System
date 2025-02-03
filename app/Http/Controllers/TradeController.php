<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TradeController extends Controller
{
    public function getTradeProposals()
    {
        $proposals = DB::table('trade_proposals')
            ->join('teams as team_from', 'trade_proposals.team_from_id', '=', 'team_from.id')
            ->join('teams as team_to', 'trade_proposals.team_to_id', '=', 'team_to.id')
            ->join('players as player_from', 'trade_proposals.player_from_id', '=', 'player_from.id')
            ->join('players as player_to', 'trade_proposals.player_to_id', '=', 'player_to.id')
            ->select(
                'trade_proposals.id',
                'player_from.name as player_from_name',
                'player_to.name as player_to_name',
                'team_from.name as from_team',
                'team_to.name as to_team',
                'trade_proposals.status',
                'trade_proposals.created_at'
            )
            ->where('trade_proposals.status', 'pending')
            ->orderBy('trade_proposals.created_at', 'desc')
            ->get();

        return response()->json(['trade_proposals' => $proposals]);
    }

    public function insertTradeProposal(Request $request)
    {
        $validated = $request->validate([
            'team_from_id' => 'required|exists:teams,id',
            'team_to_id' => 'required|exists:teams,id',
            'player_from_id' => 'required|exists:players,id',
            'player_to_id' => 'required|exists:players,id'
        ]);

        DB::table('trade_proposals')->insert([
            'team_from_id' => $validated['team_from_id'],
            'team_to_id' => $validated['team_to_id'],
            'player_from_id' => $validated['player_from_id'],
            'player_to_id' => $validated['player_to_id'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Trade proposal inserted successfully.']);
    }

    public function approveTrade($proposalId)
    {
        $proposal = DB::table('trade_proposals')->find($proposalId);
        
        if (!$proposal || $proposal->status !== 'pending') {
            return response()->json(['error' => 'Trade proposal not found or already processed.'], 400);
        }

        DB::transaction(function () use ($proposal) {
            DB::table('players')
                ->where('id', $proposal->player_from_id)
                ->update(['team_id' => $proposal->team_to_id]);
            
            DB::table('players')
                ->where('id', $proposal->player_to_id)
                ->update(['team_id' => $proposal->team_from_id]);
            
            DB::table('trade_proposals')
                ->where('id', $proposal->id)
                ->update(['status' => 'approved', 'updated_at' => now()]);
            
            $this->logTrade($proposal->team_from_id, $proposal->team_to_id, $proposal->player_from_id, 'Trade approved');
            $this->logTrade($proposal->team_to_id, $proposal->team_from_id, $proposal->player_to_id, 'Trade approved');
        });

        return response()->json(['message' => 'Trade approved successfully.']);
    }

    public function rejectTrade($proposalId)
    {
        $proposal = DB::table('trade_proposals')->find($proposalId);
        
        if (!$proposal || $proposal->status !== 'pending') {
            return response()->json(['error' => 'Trade proposal not found or already processed.'], 400);
        }

        DB::table('trade_proposals')
            ->where('id', $proposalId)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return response()->json(['message' => 'Trade rejected successfully.']);
    }

    public function generateTradeProposals()
    {
        $teams = DB::table('teams')->pluck('id');
        $tradeProposals = [];

        foreach ($teams as $teamId) {
            $underperformingPlayer = $this->findUnderperformingPlayers($teamId);
            if ($underperformingPlayer) {
                $potentialTradePartner = $this->findTradePartner($underperformingPlayer);
                if ($potentialTradePartner) {
                    $tradeProposals[] = [
                        'team_from_id' => $teamId,
                        'team_to_id' => $potentialTradePartner->team_id,
                        'player_from_id' => $underperformingPlayer->id,
                        'player_to_id' => $potentialTradePartner->id,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }

        if (!empty($tradeProposals)) {
            DB::table('trade_proposals')->insert($tradeProposals);
        }

        return response()->json(['message' => 'Trade proposals generated successfully.']);
    }

    private function calculatePerformanceScore($stats)
    {
        // Example of performance score calculation. Modify the weight based on your preference.
        return (
            $stats->avg_points_per_game * 0.4 +
            $stats->avg_rebounds_per_game * 0.2 +
            $stats->avg_assists_per_game * 0.2 +
            $stats->avg_steals_per_game * 0.1 +
            $stats->avg_blocks_per_game * 0.1 -
            $stats->avg_turnovers_per_game * 0.1 -
            $stats->avg_fouls_per_game * 0.1
        );
    }
    
    private function findUnderperformingPlayers($teamId)
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id');
        $previousSeasonId = $latestSeasonId - 1; // Assuming seasons are sequential
    
        // Get the latest season stats for players on the team
        $latestStats = DB::table('player_season_stats')
            ->where('team_id', $teamId)
            ->where('season_id', $latestSeasonId)
            ->get();
    
        $underperformingPlayers = [];
    
        foreach ($latestStats as $playerStats) {
            // Get the previous season stats for comparison
            $previousStats = DB::table('player_season_stats')
                ->where('player_id', $playerStats->player_id)
                ->where('season_id', $previousSeasonId)
                ->first();
    
            // Calculate performance scores
            $latestScore = $this->calculatePerformanceScore($playerStats);
            $previousScore = $previousStats ? $this->calculatePerformanceScore($previousStats) : 0;
    
            // Compare performance (adjust the threshold based on your criteria)
            if ($latestScore < $previousScore) {
                $underperformingPlayers[] = $playerStats;
            }
        }
    
        return $underperformingPlayers;
    }
    
    private function findTradePartner($player)
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id');
        $previousSeasonId = $latestSeasonId - 1; // Assuming seasons are sequential
    
        // Get the player's latest and previous season stats
        $latestStats = DB::table('player_season_stats')
            ->where('player_id', $player->id)
            ->where('season_id', $latestSeasonId)
            ->first();
    
        $previousStats = DB::table('player_season_stats')
            ->where('player_id', $player->id)
            ->where('season_id', $previousSeasonId)
            ->first();
    
        // Calculate performance scores
        $latestScore = $this->calculatePerformanceScore($latestStats);
        $previousScore = $previousStats ? $this->calculatePerformanceScore($previousStats) : 0;
    
        // Find trade partner with a similar role but better performance
        return DB::table('player_season_stats')
            ->where('role', $player->role)
            ->where('season_id', $latestSeasonId)
            ->where('team_id', '<>', $player->team_id)
            ->orderByRaw('(' . $this->calculatePerformanceScore('player_season_stats') . ') DESC')
            ->first();
    }
    
}
