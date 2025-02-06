<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TradeController extends Controller
{
    public function getTradeProposals()
    {
        $latestSeasonId = DB::table('player_season_stats')->max('season_id') + 1;
    
        $proposals = DB::table('trade_proposals')
            ->leftJoin('teams as team_from', 'trade_proposals.team_from_id', '=', 'team_from.id')
            ->leftJoin('teams as team_to', 'trade_proposals.team_to_id', '=', 'team_to.id')
            ->leftJoin('players as player_from', 'trade_proposals.player_from_id', '=', 'player_from.id')
            ->leftJoin('players as player_to', 'trade_proposals.player_to_id', '=', 'player_to.id')
            ->select(
                'trade_proposals.id',
                'trade_proposals.status',
                'trade_proposals.created_at',
                'player_from.name as player_from_name',
                'player_from.role as player_from_role',
                'player_from.id as player_from_id',
                'player_to.name as player_to_name',
                'player_to.role as player_to_role',
                'player_to.id as player_to_id',
                'team_from.name as from_team',
                'team_to.name as to_team'
            )
            ->where('trade_proposals.status', 'pending')
            ->where('trade_proposals.season_id', $latestSeasonId)
            ->orderBy('trade_proposals.created_at', 'desc')
            ->get();
    
        return response()->json([
            'trade_proposals' => $proposals,
            'current_season' => $latestSeasonId
        ]);
    }
    public function automatedTradeDecision()
    {
        $latestSeasonId = DB::table('seasons')->max('id') + 1;
        
        // Fetch all trade proposals for the current season
        $proposals = DB::table('trade_proposals')
            ->where('season_id', $latestSeasonId)
            ->where('status', 'pending')
            ->get();
    
        $decisions = [];
    
        foreach ($proposals as $proposal) {
            try {
                // Check if the player has already been involved in another pending trade for the same season
                $duplicateTrade = DB::table('trade_proposals')
                    ->where('season_id', $latestSeasonId)
                    ->where(function($query) use ($proposal) {
                        $query->where('player_from_id', $proposal->player_from_id)
                              ->orWhere('player_to_id', $proposal->player_from_id)
                              ->orWhere('player_from_id', $proposal->player_to_id)
                              ->orWhere('player_to_id', $proposal->player_to_id);
                    })
                    ->where('status', 'pending')
                    ->exists();
    
                if ($duplicateTrade) {
                    // Reject the trade if a player is involved in a duplicate trade
                    DB::table('trade_proposals')
                        ->where('id', $proposal->id)
                        ->update(['status' => 'rejected', 'updated_at' => now()]);
    
                    $decisions[] = [
                        'proposal_id' => $proposal->id,
                        'status' => 'rejected',
                        'reason' => 'Player involved in multiple pending trades.'
                    ];
                    continue; // Skip the rest of the logic for this proposal
                }
    
                $playerFromScore = $this->calculatePerformanceScore($this->getPlayerStats($proposal->player_from_id));
                $playerToScore = $this->calculatePerformanceScore($this->getPlayerStats($proposal->player_to_id));
    
                // Example decision logic:
                $scoreDifference = abs($playerFromScore - $playerToScore);
    
                if (rand(1, 100) <= 30) { //30% chance of trade rejection
                    // Reject the trade if the score difference is too large
                    DB::table('trade_proposals')
                        ->where('id', $proposal->id)
                        ->update(['status' => 'rejected', 'updated_at' => now()]);
    
                    $decisions[] = [
                        'proposal_id' => $proposal->id,
                        'status' => 'rejected',
                        'reason' => 'Trade score imbalance.'
                    ];
                } else {
                    // Approve the trade if the score difference is within acceptable limits
                    DB::table('trade_proposals')
                        ->where('id', $proposal->id)
                        ->update(['status' => 'approved', 'updated_at' => now()]);
    
                    $decisions[] = [
                        'proposal_id' => $proposal->id,
                        'status' => 'approved',
                        'reason' => 'Trade balance accepted.'
                    ];
    
                    // Perform the actual trade (same as approveTrade logic)
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
    
                        $this->logTrade($proposal->team_to_id, $proposal->team_from_id, $proposal->player_to_id, $proposal->player_from_id);
                        $this->logTrade($proposal->team_from_id, $proposal->team_to_id, $proposal->player_from_id, $proposal->player_to_id);
                    });
                }
            } catch (\Exception $e) {
                // Handle any exceptions that occur during the trade decision process
                // Log the error message (optional, depending on your logging system)
                Log::error("Error processing trade proposal ID {$proposal->id}: " . $e->getMessage());
    
                // Add a decision indicating the error to the response
                $decisions[] = [
                    'proposal_id' => $proposal->id,
                    'status' => 'error',
                    'reason' => 'An error occurred while processing the trade.'
                ];
            }
        }
    
        return response()->json([
            'decisions' => $decisions
        ]);
    }
    
    

    public function endTradeWindow(){
        $latestSeasonId = DB::table('seasons')->max('id');

        DB::table('seasons')
        ->where('id',  $latestSeasonId)
        ->update(['status' => config('timeline.player_trade')]);

        return response()->json(['message' => 'Trade window ended!']);
    }
    public function approveTrade(Request $request)
    {
        $proposalId = $request->input('proposal_id');

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
            
            $this->logTrade($proposal->team_to_id, $proposal->team_from_id,$proposal->player_to_id, $proposal->player_from_id);
            $this->logTrade($proposal->team_from_id, $proposal->team_to_id, $proposal->player_from_id, $proposal->player_to_id);
        });

        return response()->json(['message' => 'Trade approved successfully.']);
    }

    public function rejectTrade(Request $request)
    {
        $proposalId = $request->input('proposal_id');

        $proposal = DB::table('trade_proposals')->find($proposalId);
        
        if (!$proposal || $proposal->status !== 'pending') {
            return response()->json(['error' => 'Trade proposal not found or already processed.'], 400);
        }

        DB::table('trade_proposals')
            ->where('id', $proposalId)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return response()->json(['message' => 'Trade rejected successfully.']);
    }
    private function logTrade($teamId, $opponentId, $playerId, $tradePlayerId,$message = 'Trade proposal accepted.')
    {
        $latestSeasonId = DB::table('seasons')->max('id');
    
        // Fetch player details in a single query (avoiding multiple DB calls)
        $player = DB::table('players')->select('name', 'role')->where('id', $playerId)->first();
        $tradePlayer = DB::table('players')->select('name', 'role')->where('id', $tradePlayerId)->first();
        
        // Fetch team names
        $teamFrom = DB::table('teams')->where('id', $teamId)->value('name');
        $teamTo = DB::table('teams')->where('id', $opponentId)->value('name');
    
        if (!$player || !$tradePlayer || !$teamFrom || !$teamTo) {
            return response()->json(['error' => 'Player or team not found'], 404);
        }
    
        DB::transaction(function () use ($latestSeasonId, $teamId, $opponentId, $playerId, $tradePlayerId, $player, $tradePlayer, $message, $teamFrom, $teamTo) {
            // Insert into trade_logs
            DB::table('trade_logs')->insert([
                'season_id' => $latestSeasonId,
                'team_from_id' => $teamId,
                'team_to_id' => $opponentId,
                'player_id' => $playerId,
                'role' => $player->role,
                'player_name' => $player->name,
                'trade_reason' => $message,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            // Insert into transactions table with both players and team names in details
            DB::table('transactions')->insert([
                'player_id' => $playerId,
                'season_id' => $latestSeasonId,
                'details' => "Traded {$player->name} ({$teamFrom}) in exchange for {$tradePlayer->name} ({$teamTo})",
                'from_team_id' => $teamId,
                'to_team_id' => $opponentId,
                'status' => 'trade',
            ]);
        });
    
        return response()->json(['message' => 'Trade logged successfully.']);
    }
    
    
    
    public function generateTradeProposals()
    {
        $latestSeasonId = DB::table('seasons')->max('id') + 1;
        $teams = DB::table('teams')->pluck('id');
        $tradeProposals = [];
        $tradeablePlayers = [];
    
        // Step 1: Collect all tradeable players and calculate their scores
        foreach ($teams as $teamId) {
            $players = $this->findUnderperformingPlayers($teamId);
            foreach ($players as &$player) {
                $player['composite_score'] = $this->calculatePerformanceScore((object) $player);
            }
            $tradeablePlayers = array_merge($tradeablePlayers, $players);
        }
    
        // Add unhappy stars and calculate their scores
        $unhappyStars = $this->findUnhappyStars();
        foreach ($unhappyStars as &$star) {
            $star['composite_score'] = $this->calculatePerformanceScore((object) $star);
        }
        $tradeablePlayers = array_merge($tradeablePlayers, $unhappyStars);
    
        // Step 2: Sort players by performance score (highest first)
        usort($tradeablePlayers, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);
    
        // Step 3: Process multi-team trades
        while (!empty($tradeablePlayers)) {
            $bestPlayer = array_shift($tradeablePlayers); // Get highest-value player
            $tradePartners = [];
            $remainingScore = $bestPlayer['composite_score'];
    
            foreach ($tradeablePlayers as $key => $player) {
                if ($player['team_id'] !== $bestPlayer['team_id']) { // Ensure cross-team trade
                    $tradePartners[] = $player;
                    $remainingScore -= $player['composite_score'];
                    unset($tradeablePlayers[$key]); // Remove traded player
    
                    if (abs($remainingScore) <= 10) { // Allow slight imbalance (10-point threshold)
                        break;
                    }
                }
            }
    
            // Multi-team trade formation
            if (!empty($tradePartners)) {
                foreach ($tradePartners as $tradePlayer) {
                    $tradeProposals[] = [
                        'season_id' => $latestSeasonId,
                        'team_from_id' => $bestPlayer['team_id'],
                        'team_to_id' => $tradePlayer['team_id'],
                        'player_from_id' => $bestPlayer['player_id'],
                        'player_to_id' => $tradePlayer['player_id'],
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }
    
        // Step 4: Store trade proposals in the database
        if (!empty($tradeProposals)) {
            DB::table('trade_proposals')->insert($tradeProposals);
        }
    
        return response()->json([
            'message' => 'Multi-team trade proposals generated successfully.',
            'trades' => $tradeProposals
        ]);
    }
    
    
    
    private function calculatePerformanceScore($player)
    {
        return (float) (
            ($player->avg_points_per_game ?? 0) * 2 +
            ($player->avg_rebounds_per_game ?? 0) * 1.5 +
            ($player->avg_assists_per_game ?? 0) * 1.5 +
            ($player->avg_steals_per_game ?? 0) * 2 +
            ($player->avg_blocks_per_game ?? 0) * 2 -
            ($player->avg_turnovers_per_game ?? 0) * 1.5
        );
    }



    private function findUnderperformingPlayers($teamId)
    {
        $latestSeasonId = DB::table('seasons')->max('id');
        $previousSeasonId = $latestSeasonId - 1; // Assuming seasons are sequential
    
        $latestStats = DB::table('player_season_stats')
            ->join('players', 'player_season_stats.player_id', '=', 'players.id')
            ->where('players.team_id', $teamId)
            ->where('player_season_stats.season_id', $latestSeasonId)
            ->select(
                'players.id as player_id',
                'players.team_id',
                'players.role',
                'players.name as player_name',
                'player_season_stats.total_games',
                'player_season_stats.total_games_played',
                'player_season_stats.avg_minutes_per_game',
                'player_season_stats.avg_points_per_game',
                'player_season_stats.avg_rebounds_per_game',
                'player_season_stats.avg_assists_per_game',
                'player_season_stats.avg_steals_per_game',
                'player_season_stats.avg_blocks_per_game',
                'player_season_stats.avg_turnovers_per_game',
                'player_season_stats.avg_fouls_per_game'
            )
            ->get();
    
        $underperformingPlayers = [];
    
        foreach ($latestStats as $playerStats) {
            $previousStats = DB::table('player_season_stats')
                ->where('player_id', $playerStats->player_id)
                ->where('season_id', $previousSeasonId)
                ->first();
    
            $latestScore = $this->calculatePerformanceScore($playerStats);
            $previousScore = $previousStats ? $this->calculatePerformanceScore($previousStats) : 0;
    
            if ($latestScore < $previousScore) {
                $underperformingPlayers[] = (array) $playerStats;
            }
        }
    
        return $underperformingPlayers;
    }
    
    private function findUnhappyStars()
    {
        $latestSeasonId = DB::table('seasons')->max('id');
    
        $starPlayers = DB::table('players')
            ->join('player_season_stats', 'players.id', '=', 'player_season_stats.player_id')
            ->join('standings_view', 'players.team_id', '=', 'standings_view.team_id')
            ->where('standings_view.season_id', $latestSeasonId)
            ->where('standings_view.overall_rank', '>', 75) // Teams ranked below 75
            ->where('player_season_stats.season_id', $latestSeasonId)
            ->select(
                'players.id as player_id',
                'players.team_id',
                'players.role',
                'players.name as player_name',
                'player_season_stats.total_games',
                'player_season_stats.total_games_played',
                'player_season_stats.avg_minutes_per_game',
                'player_season_stats.avg_points_per_game',
                'player_season_stats.avg_rebounds_per_game',
                'player_season_stats.avg_assists_per_game',
                'player_season_stats.avg_steals_per_game',
                'player_season_stats.avg_blocks_per_game',
                'player_season_stats.avg_turnovers_per_game',
                'player_season_stats.avg_fouls_per_game'
            )
            ->get();
    
        return $starPlayers->map(fn($player) => (array) $player)->toArray();
    }
    
    
    private function getPlayerStats($playerId)
    {
        $latestSeasonId = DB::table('seasons')->max('id');
        
        return DB::table('player_season_stats')
            ->where('player_id', $playerId)
            ->where('season_id', $latestSeasonId)
            ->first();
    }
}
