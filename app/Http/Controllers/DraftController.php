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
    public function draftorder()
    {
        // Get the latest season_id from the standings_view
        $latestSeasonId = DB::table('standings_view')->max('season_id');

        // Fetch standings for the latest season, sorted by overall rank, including team name
        $draftOrder = DB::table('standings_view')
            ->select('team_id', 'team_name', 'wins', 'losses', 'overall_rank')
            ->where('season_id', $latestSeasonId)
            ->orderBy('overall_rank', 'desc')
            ->get();

        // Prepare the draft order for two rounds
        $twoRoundDraftOrder = [];
        $totalTeams = $draftOrder->count();

        foreach ($draftOrder as $index => $team) {
            // First round
            $twoRoundDraftOrder[] = [
                'round' => 1,
                'pick' => $index + 1, // Pick number starts at 1
                'team_id' => $team->team_id,
                'team_name' => $team->team_name,
                'wins' => $team->wins,
                'losses' => $team->losses,
                'overall_rank' => $team->overall_rank,
            ];

            // Second round (reverse order)
            $twoRoundDraftOrder[] = [
                'round' => 2,
                'pick' => $index + 1, // Pick number starts at 1
                'team_id' => $team->team_id,
                'team_name' => $team->team_name,
                'wins' => $team->wins,
                'losses' => $team->losses,
                'overall_rank' => $team->overall_rank,
            ];
        }

        // Sort by round and then by pick number
        usort($twoRoundDraftOrder, function ($a, $b) {
            if ($a['round'] === $b['round']) {
                return $a['pick'] <=> $b['pick'];
            }
            return $a['round'] <=> $b['round'];
        });

        // Return JSON response
        return response()->json([
            'season_id' => $latestSeasonId,
            'draft_order' => $twoRoundDraftOrder,
        ]);
    }


    //  DB::table('seasons')
    //  ->where('id', $this->getLatestSeasonId())
    //  ->update(['status' => 11]);
    public function draftplayers()
    {
        DB::beginTransaction(); // Start transaction

        $draftResults = []; // Initialize drafted players array

        try {
            // Get the latest season_id from the standings_view
            $latestSeasonId = DB::table('standings_view')->max('season_id');
            $currentSeasonId = $latestSeasonId + 1; // Current season id for the draft

            // Fetch standings for the latest season, sorted by overall rank
            $draftOrder = DB::table('standings_view')
                ->select('team_id', 'wins', 'losses', 'overall_rank', 'team_name')
                ->where('season_id', $latestSeasonId)
                ->orderBy('overall_rank', 'desc')
                ->get();


            // Fetch rookie players sorted by overall rating (highest first)
            $availablePlayers = DB::table('players')
                ->where('is_rookie', 1)
                ->where('team_id', 0) // Only include players not yet assigned to a team
                ->where('draft_id', $currentSeasonId) // Only include players that has the same draft_id
                ->orderBy('overall_rating', 'desc')
                ->get();

            if (count($availablePlayers) < 160) {
                return response()->json([
                    'error' => true,
                    'message' => 'Rookies not enough for teams!',
                ], 400);
            }

            // $availablePlayers = collect($availablePlayers);
            $pickNumber = 1; // Track pick number
            // Perform the drafting
            foreach ($draftOrder as $team) {
                if ($availablePlayers) {
                    $selectedPlayer = $availablePlayers->shift(); // Get the highest-rated rookie player

                    // Determine the round and pick number
                    $round = 1;
                    $draftStatus = "S{$currentSeasonId} R{$round} P{$pickNumber}";
                    $contract = $this->determineContractYears($selectedPlayer->role);

                    // Check if the team already has 15 members
                    $teamsWithFewMembers = DB::table('teams')
                        ->leftJoin('players', 'teams.id', '=', 'players.team_id')
                        ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
                        ->where('teams.id', $team->team_id) // Filter by specific team ID
                        ->groupBy('teams.id', 'teams.name')
                        ->havingRaw('COUNT(players.id) < 15')
                        ->get();


                    // Check if there is a spot available
                    $spotAvailable = $teamsWithFewMembers->isNotEmpty();

                    DB::table('players')->where('id', $selectedPlayer->id)->update([
                        'draft_id' => $currentSeasonId,
                        'draft_order' => $pickNumber,
                        'drafted_team_id' => $team->team_id,
                        'is_drafted' => 1,
                        'draft_status' => $draftStatus,
                        'team_id' => $spotAvailable ? $team->team_id : 0,
                        'contract_years' => $spotAvailable ? $contract : 0,
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

                    // Save to the drafts table
                    $draftInsert = DB::table('drafts')->insert([
                        'team_id' => $team->team_id,
                        'player_id' => $selectedPlayer->id,
                        'season_id' => $currentSeasonId,
                        'round' => $round,
                        'pick_number' => $pickNumber,
                        'draft_status' => $draftStatus,
                    ]);

                    // Log draft insert success or failure
                    \Log::info('Draft insert:', [
                        'player_id' => $selectedPlayer->id,
                        'insert_success' => $draftInsert,
                    ]);

                    // Store the draft result
                    $draftResults[] = [
                        'team_id' => $team->team_id,
                        'player_id' => $selectedPlayer->id,
                        'player_name' => $selectedPlayer->name,
                        'overall_rating' => $selectedPlayer->overall_rating,
                        'draft_id' => $currentSeasonId,
                        'draft_order' => $pickNumber,
                        'draft_status' => $draftStatus,
                        'round' => $round,
                        'pick_number' => $pickNumber,
                    ];

                    $pickNumber++; // Increment pick number
                } else {
                    // No more players available to draft
                    \Log::info('No more available players to draft.');

                    return response()->json([
                        'error' => true,
                        'message' => 'No rookie available',
                    ], 400);
                }
            }

            $pickNumberTwo = 1;
            foreach ($draftOrder as $team) {
                if ($availablePlayers) {
                    $selectedPlayer = $availablePlayers->shift(); // Get the highest-rated rookie player

                    // Determine the round and pick number
                    $round = 2;
                    $draftStatus = "S{$currentSeasonId} R{$round} P{$pickNumberTwo}";
                    $contract = $this->determineContractYears($selectedPlayer->role);

                    // Check if the team already has 15 members
                    $teamsWithFewMembers = DB::table('teams')
                        ->leftJoin('players', 'teams.id', '=', 'players.team_id')
                        ->select('teams.id', 'teams.name', DB::raw('COUNT(players.id) as player_count'))
                        ->where('teams.id', $team->team_id) // Filter by specific team ID
                        ->groupBy('teams.id', 'teams.name')
                        ->havingRaw('COUNT(players.id) < 15')
                        ->get();


                    // Check if there is a spot available
                    $spotAvailable = $teamsWithFewMembers->isNotEmpty();

                    // Update player details for drafted player
                    DB::table('players')->where('id', $selectedPlayer->id)->update([
                        'draft_id' => $currentSeasonId,
                        'draft_order' => $pickNumber,
                        'drafted_team_id' => $team->team_id,
                        'is_drafted' => 1,
                        'draft_status' => $draftStatus,
                        'team_id' => $spotAvailable ? $team->team_id : 0,
                        'contract_years' => $spotAvailable ? $contract : 0,
                    ]);

                    // Save to the drafts table
                    $draftInsert = DB::table('drafts')->insert([
                        'team_id' => $team->team_id,
                        'player_id' => $selectedPlayer->id,
                        'season_id' => $currentSeasonId,
                        'round' => $round,
                        'pick_number' => $pickNumberTwo,
                        'draft_status' => $draftStatus,
                    ]);

                    // Log draft insert success or failure
                    \Log::info('Draft insert:', [
                        'player_id' => $selectedPlayer->id,
                        'insert_success' => $draftInsert,
                    ]);

                    // Store the draft result
                    $draftResults[] = [
                        'team_id' => $team->team_id,
                        'player_id' => $selectedPlayer->id,
                        'player_name' => $selectedPlayer->name,
                        'overall_rating' => $selectedPlayer->overall_rating,
                        'draft_id' => $currentSeasonId,
                        'draft_order' => $pickNumberTwo,
                        'draft_status' => $draftStatus,
                        'round' => $round,
                        'pick_number' => $pickNumber,
                    ];

                    $pickNumberTwo++; // Increment pick number

                } else {
                    // No more players available to draft
                    \Log::info('No more available players to draft.');

                    return response()->json([
                        'error' => true,
                        'message' => 'No rookie available',
                    ], 400);
                }
            }

            // After drafting logic but before DB::commit()
            DB::table('players')
                ->where('draft_id', $currentSeasonId)
                ->where('is_drafted', 0)
                ->update([
                    'team_id' => 0,
                    'contract_years' => 0,
                    'draft_status' => 'Undrafted',
                    'is_rookie' => 1,
                ]);

            // Update the season status to 11 after drafting
            $seasonUpdate = DB::table('seasons')
                ->where('id', $latestSeasonId)
                ->update(['status' => 11]);

            // Log season update success or failure
            \Log::info('Season status updated:', [
                'season_id' => $latestSeasonId,
                'update_success' => $seasonUpdate,
            ]);

            DB::commit(); // Commit transaction

            // Return the draft results as a JSON response
            return response()->json([
                'error' => false,
                'season_id' => $currentSeasonId,
                'draft_results' => $draftResults,
                'message' => 'Draft Success!'
            ], 200);
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
    public function rookiedraftees(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = $request->input('itemsperpage', 10); // Number of items per page
        $currentPage = $request->input('page_num', 1); // Current page number
        $search = $request->input('search', ''); // Search term

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

        // Define role priorities
        $rolePriorities = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Build the query with optional search filter
        $query = Player::select('*')
            ->where('contract_years', 0)
            ->where('is_active', 1)
            ->where('is_rookie', 1);

        // Apply search filter if provided
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Add role priority sorting
        $query->orderByRaw(
            "FIELD(role, 'star player', 'starter', 'role player', 'bench')"
        );

        // Get total number of records
        $total = $query->count();

        // Fetch the paginated data
        $freeAgents = $query->offset($offset)
            ->limit($perPage)
            ->get();

        // Calculate total pages
        $totalPages = (int) ceil($total / $perPage);

        return response()->json([
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'rookies' => $freeAgents,
        ]);
    }
    public function draftresults()
    {
        // Get the latest season_id from the standings_view
        $latestSeasonId = $this->getLatestSeasonId();

        // Fetch draft results for the latest season from the drafts table
        $draftResults = DB::table('drafts')
            ->select('team_id', 'player_id', 'season_id', 'round', 'pick_number', 'draft_status')
            ->where('season_id', $latestSeasonId + 1) // Assuming the new season is the next one
            ->get();

        // If you want to include team names and player names, you can join the relevant tables
        $draftResultsWithNames = DB::table('drafts')
            ->join('teams', 'drafts.team_id', '=', 'teams.id')
            ->join('players', 'drafts.player_id', '=', 'players.id')
            ->select(
                'drafts.team_id',
                'teams.name',
                'drafts.player_id',
                'players.name as player_name',
                'drafts.season_id',
                'drafts.round',
                'drafts.pick_number',
                'drafts.draft_status'
            )
            ->where('drafts.season_id', $latestSeasonId + 1)
            ->get();

        // Return the draft results as a JSON response
        return response()->json([
            'season_id' => $latestSeasonId + 1, // Return the new season id
            'draft_results' => $draftResultsWithNames,
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
}
