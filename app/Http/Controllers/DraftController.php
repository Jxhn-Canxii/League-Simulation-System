<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seasons;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DraftController extends Controller
{
    public function index()
    {
        return Inertia::render('Draft/Index', [
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
    public function draft_order()
    {
        // Get the latest season_id from the standings_view
        $latestSeasonId = DB::table('standings_view')
            ->max('season_id');

        // Fetch standings for the latest season, sorted by overall rank
        $draftOrder = DB::table('standings_view')
            ->select('team_id', 'wins', 'losses', 'overall_rank')
            ->where('season_id', $latestSeasonId)
            ->orderBy('overall_rank', 'asc')
            ->get();

        // Prepare the draft order for two rounds
        $twoRoundDraftOrder = [];

        foreach ($draftOrder as $index => $team) {
            // First round
            $twoRoundDraftOrder[] = [
                'round' => 1,
                'team_id' => $team->team_id,
                'wins' => $team->wins,
                'losses' => $team->losses,
                'overall_rank' => $team->overall_rank,
            ];
            // Second round (reverse order)
            $twoRoundDraftOrder[] = [
                'round' => 2,
                'team_id' => $draftOrder[count($draftOrder) - 1 - $index]->team_id,
                'wins' => $draftOrder[count($draftOrder) - 1 - $index]->wins,
                'losses' => $draftOrder[count($draftOrder) - 1 - $index]->losses,
                'overall_rank' => $draftOrder[count($draftOrder) - 1 - $index]->overall_rank,
            ];
        }

        // Return JSON response
        return response()->json([
            'season_id' => $latestSeasonId,
            'draft_order' => $twoRoundDraftOrder
        ]);
    }
    //  DB::table('seasons')
    //  ->where('id', $this->getLatestSeasonId())
    //  ->update(['status' => 11]);
    public function draft()
    {
        DB::beginTransaction(); // Start transaction

        try {
            // Get the latest season_id from the standings_view
            $latestSeasonId = DB::table('standings_view')->max('season_id');

            // Fetch standings for the latest season, sorted by overall rank
            $draftOrder = DB::table('standings_view')
                ->select('team_id', 'wins', 'losses', 'overall_rank', 'team_name')
                ->where('season_id', $latestSeasonId)
                ->orderBy('overall_rank', 'asc')
                ->get();

            // Fetch rookie players sorted by overall rating (highest first)
            $availablePlayers = DB::table('players')
                ->where('is_rookie', 1)
                ->whereNull('team_id') // Only include players not yet assigned to a team
                ->orderBy('overall_rating', 'desc')
                ->get();

            // Initialize the drafted players array
            $draftResults = [];
            $totalTeams = $draftOrder->count();
            $pickNumber = 1; // Track pick number

            // Perform the drafting
            foreach ($draftOrder as $team) {
                $currentSeasonId = $latestSeasonId + 1;

                if ($availablePlayers->isNotEmpty()) {
                    $selectedPlayer = $availablePlayers->shift(); // Get the highest-rated rookie player

                    // Determine the round and pick number
                    $round = ceil($pickNumber / $totalTeams);
                    $draftStatus = "Season {$currentSeasonId} R [{$round}] P [{$pickNumber}]";

                    // Update player details for drafted player
                    DB::table('players')->where('id', $selectedPlayer->id)->update([
                        'drafted_team_id' => $team->team_id,
                        'is_drafted' => 1,
                        'draft_status' => $draftStatus,
                        'team_id' => $team->team_id // Optional: if you want to update the team_id field as well
                    ]);

                    // Log the transaction
                    DB::table('transactions')->insert([
                        'player_id' => $selectedPlayer->id,
                        'season_id' => $currentSeasonId,
                        'details' => "Drafted by {$team->team_name} in round {$round}, pick {$pickNumber}",
                        'from_team_id' => 0, // No previous team for drafted players
                        'to_team_id' => $team->team_id,
                        'status' => 'draft',
                    ]);

                    $draftResults[] = [
                        'team_id' => $team->team_id,
                        'player_id' => $selectedPlayer->id,
                        'player_name' => $selectedPlayer->name,
                        'overall_rating' => $selectedPlayer->overall_rating,
                        'draft_id' => $currentSeasonId,
                        'draft_order' => $pickNumber,
                        'draft_status' => $draftStatus, // Store draft status
                        'round' => $round, // Store the round
                        'pick_number' => $pickNumber, // Store the pick number
                    ];

                    $pickNumber++; // Increment pick number
                } else {
                    // If no players are available, mark undrafted status
                    // Assuming you need to still mark the player if selected previously
                    if (isset($selectedPlayer)) {
                        DB::table('players')->where('id', $selectedPlayer->id)->update([
                            'draft_id' => $currentSeasonId,
                            'is_drafted' => 0,
                            'draft_status' => 'undrafted',
                        ]);
                    }
                }
            }

            // Commit transaction
            DB::commit();

            // Return the draft results as a JSON response
            return response()->json([
                'season_id' => $latestSeasonId,
                'draft_results' => $draftResults,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            \Log::error('Drafting failed', ['exception' => $e]);

            return response()->json([
                'error' => true,
                'message' => 'Drafting failed.',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }


    public function draft_history()
    {
        // Get the latest season_id from the standings_view
        $latestSeasonId = DB::table('standings_view')->max('season_id');

        // Fetch standings for the latest season, sorted by overall rank
        $draftOrder = DB::table('standings_view')
            ->select('team_id', 'wins', 'losses', 'overall_rank')
            ->where('season_id', $latestSeasonId)
            ->orderBy('overall_rank', 'asc')
            ->get();

        // Fetch rookie players sorted by overall rating (highest first)
        $availablePlayers = DB::table('players')
            ->where('is_rookie', 1)
            ->whereNull('team_id') // Only include players not yet assigned to a team
            ->orderBy('overall_rating', 'desc')
            ->get();

        // Initialize the drafted players array
        $draftResults = [];
        $totalTeams = $draftOrder->count();
        $pickNumber = 1; // Track pick number

        // Perform the drafting
        foreach ($draftOrder as $team) {
            $currentSeasonId = $latestSeasonId + 1;

            if ($availablePlayers->isNotEmpty()) {
                $selectedPlayer = $availablePlayers->shift(); // Get the highest-rated rookie player

                // Determine the round and pick number
                $round = ceil($pickNumber / $totalTeams);
                $draftStatus = `Season .$currentSeasonId. R #' . $round . ' P #' . $pickNumber . '`;


                $draftResults[] = [
                    'team_id' => $team->team_id,
                    'player_id' => $selectedPlayer->id,
                    'player_name' => $selectedPlayer->name,
                    'overall_rating' => $selectedPlayer->overall_rating,
                    'draft_id' =>  $currentSeasonId,
                    'draft_order' => $pickNumber,
                    'draft_status' => $draftStatus, // Store draft status
                    'round' => $round, // Store the round
                    'pick_number' => $pickNumber, // Store the pick number
                ];

                $pickNumber++; // Increment pick number
            } else {
                // If no players are available, mark undrafted status
                DB::table('players')->where('id', $selectedPlayer->id)->update([
                    'draft_id' =>  $currentSeasonId,
                    'is_drafted' => 0,
                    'draft_status' => 'undrafted',
                ]);
            }
        }

        // Return the draft results as a JSON response
        return response()->json([
            'season_id' => $latestSeasonId,
            'draft_results' => $draftResults,
        ]);
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
