<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); // 300 seconds = 5 minutes

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
                'player_from.role as player_from_role',
                'player_to.role as player_to_role',
                'player_from.id as player_from_id',
                'player_to.id as player_to_id',
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
    private function logTrade($teamId, $opponentId, $playerId, $message)
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id');

        DB::table('trade_logs')->insert([
            'season_id' => $latestSeasonId,
            'team_from_id' => $teamId,
            'team_to_id' => $opponentId,
            'player_id' => $playerId,
            'role' => DB::table('players')->find($playerId)->role,
            'player_name' => DB::table('players')->find($playerId)->name,
            'trade_reason' => $message,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    private function findTradePartner($player)
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id');

        // Step 1: Find teams that need this player's role
        $needyTeams = DB::table('teams')
            ->where('id', '<>', $player->team_id) // Exclude current team
            ->pluck('id');

        $potentialTrades = [];

        foreach ($needyTeams as $teamId) {
            // Step 2: Find players on the other team who could be traded
            $tradeCandidates = DB::table('players')
                ->join('player_season_stats', 'players.id', '=', 'player_season_stats.player_id')
                ->where('players.team_id', $teamId)
                ->where('player_season_stats.season_id', $latestSeasonId)
                ->select('players.*', 'player_season_stats.*')
                ->get();

            foreach ($tradeCandidates as $candidate) {
                // Step 3: Check if the trade is balanced (score-wise)
                $playerScore = $this->calculatePerformanceScore($player);
                $candidateScore = $this->calculatePerformanceScore($candidate);

                if ($candidateScore >= $playerScore * 0.8) { // Allow slightly unbalanced trades
                    $potentialTrades[] = [
                        'team_from_id' => $player->team_id,
                        'team_to_id' => $candidate->team_id,
                        'player_from_id' => $player->player_id,
                        'player_to_id' => $candidate->player_id,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }

        return $potentialTrades;
    }
    public function generateMultiTeamTrade()
    {
        $teams = DB::table('teams')->pluck('id');
        $tradeProposals = [];

        foreach ($teams as $teamId) {
            $underperformingPlayers = $this->findUnderperformingPlayers($teamId);

            foreach ($underperformingPlayers as $underperformingPlayer) {
                $potentialTradePartners = $this->findTradePartner($underperformingPlayer);

                if (count($potentialTradePartners) >= 2) { // Ensure multi-team trade
                    $tradeProposals[] = [
                        'team_from_id' => $teamId,
                        'team_to_id' => $potentialTradePartners[0]['team_to_id'],
                        'player_from_id' => $underperformingPlayer->player_id,
                        'player_to_id' => $potentialTradePartners[0]['player_to_id'],
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    // Include a third team
                    $tradeProposals[] = [
                        'team_from_id' => $potentialTradePartners[1]['team_to_id'],
                        'team_to_id' => $teamId,
                        'player_from_id' => $potentialTradePartners[1]['player_to_id'],
                        'player_to_id' => $underperformingPlayer->player_id,
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

        return response()->json(['message' => 'Multi-team trade proposals generated successfully.']);
    }
    private function findUnderperformingPlayers($teamId)
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id');
        $previousSeasonId = $latestSeasonId - 1; // Assuming seasons are sequential

        // Get the latest season stats for players on the team, joining with players table
        $latestStats = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->where('players.team_id', $teamId) // Filter by team_id from players table
            ->where('player_season_stats.season_id', $latestSeasonId)
            ->select('player_season_stats.*', 'players.team_id', 'players.role', 'players.name as player_name') // Select relevant fields
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
}
