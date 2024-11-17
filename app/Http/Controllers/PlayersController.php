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

class PlayersController extends Controller
{

    public function index()
    {
        return Inertia::render('Players/Index', [
            'status' => session('status'),
        ]);
    }
    public function freeagents()
    {
        return Inertia::render('FreeAgents/Index', [
            'status' => session('status'),
        ]);
    }
    public function listplayers(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'season_id' => 'nullable|integer',
        ]);

        $teamId = $request->team_id;
        $seasonId = $request->season_id;

        // Initialize an array to hold player stats
        $playerStats = [];
        $latestSeasonId = DB::table('player_game_stats')->max('season_id');
        if (is_null($seasonId) || $seasonId == 0) {
            $seasonId = $latestSeasonId;
        }

        // Fetch the season status
        $seasonStatus = DB::table('seasons')->where('id', $seasonId)->value('status');

        // Fetch player stats for the given team_id and season_id
        $playerStatsData = DB::table('player_season_stats')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->get();

        if (count($playerStatsData) > 0) {
            foreach ($playerStatsData as $stats) {
                // Fetch the player
                $player = DB::table('players')
                    ->select('players.*', 'teams.acronym as drafted_team', 'seasons.name as draft_class')
                    ->leftJoin('seasons', 'players.draft_id', '=', 'seasons.id')
                    ->leftJoin('teams', 'players.drafted_team_id', '=', 'teams.id')
                    ->where('players.id', $stats->player_id)->first();

                if ($player) {
                    // Count the number of games played for the player
                    $gamesPlayed = DB::table('player_game_stats')
                        ->where('player_id', $player->id)
                        ->where('team_id', $teamId)
                        ->where('season_id', $seasonId)
                        ->where('minutes', '>', 0) // Only count games where minutes > 0
                        ->count(); // Directly count the rows

                    // If season status is 11 and the player has 0 games played, skip this player
                    if ($seasonStatus == 11 && $gamesPlayed == 0) {
                        continue; // Skip the rest of the logic for this player
                    }

                    $playerStats[] = [
                        'player_id' => $player->id,
                        'name' => $player->name,
                        'age' => $player->age,
                        'role' => $stats->role,
                        'is_active' => $player->is_active,
                        'is_rookie' => $player->is_rookie,
                        'retirement_age' => $player->retirement_age,
                        'drafted_team' => $player->drafted_team,
                        'draft_status' => $player->draft_status,
                        'draft_class' => $player->draft_class,
                        'status' => $player->team_id == $teamId ? ($player->is_active ? 1 : 0) : 2,
                        'average_points_per_game' => (float)$stats->avg_points_per_game,
                        'average_rebounds_per_game' => (float)$stats->avg_rebounds_per_game,
                        'average_assists_per_game' => (float)$stats->avg_assists_per_game,
                        'average_steals_per_game' => (float)$stats->avg_steals_per_game,
                        'average_blocks_per_game' => (float)$stats->avg_blocks_per_game,
                        'average_turnovers_per_game' => (float)$stats->avg_turnovers_per_game,
                        'average_fouls_per_game' => (float)$stats->avg_fouls_per_game,
                        'games_played' => $gamesPlayed,
                    ];
                }
            }
        } else {
            // Fetch players from the players table and set all stats to zero

            $players = DB::table('players')
                ->select('players.*', 'teams.acronym as drafted_team', 'seasons.name as draft_class')
                ->leftJoin('seasons', 'players.draft_id', '=', 'seasons.id')
                ->leftJoin('teams', 'players.drafted_team_id', '=', 'teams.id')
                ->where('team_id', $teamId)
                ->get();

            // Fetch average statistics for players
            $playerGameStats = DB::table('player_game_stats')
                ->select(
                    'player_id',
                    DB::raw('COUNT(CASE WHEN minutes > 0 THEN 1 END) as games_played'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN points ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_points'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN rebounds ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_rebounds'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN assists ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_assists'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN steals ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_steals'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN blocks ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_blocks'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN turnovers ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_turnovers'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN fouls ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_fouls'),
                    DB::raw('SUM(CASE WHEN minutes > 0 THEN minutes ELSE 0 END) / NULLIF(COUNT(CASE WHEN minutes > 0 THEN 1 END), 0) as avg_minutes')
                )
                ->where('season_id', $seasonId) // Filter by the specific season
                ->groupBy('player_id')
                ->get()
                ->keyBy('player_id'); // Key the result by player_id for quick lookup

            foreach ($players as $player) {
                $playerId = $player->id;

                // Default values in case there are no stats
                $stats = [
                    'average_points_per_game' => (float)0,
                    'average_rebounds_per_game' => (float)0,
                    'average_assists_per_game' => (float)0,
                    'average_steals_per_game' => (float)0,
                    'average_blocks_per_game' => (float)0,
                    'average_turnovers_per_game' => (float)0,
                    'average_fouls_per_game' => (float)0,
                    'games_played' => 0,
                ];

                // If there are stats for this player, update values
                if (isset($playerGameStats[$playerId])) {
                    $stats = [
                        'average_points_per_game' => (float) $playerGameStats[$playerId]->avg_points,
                        'average_rebounds_per_game' => (float) $playerGameStats[$playerId]->avg_rebounds,
                        'average_assists_per_game' => (float) $playerGameStats[$playerId]->avg_assists,
                        'average_steals_per_game' => (float) $playerGameStats[$playerId]->avg_steals,
                        'average_blocks_per_game' => (float) $playerGameStats[$playerId]->avg_blocks,
                        'average_turnovers_per_game' => (float) $playerGameStats[$playerId]->avg_turnovers,
                        'average_fouls_per_game' => (float) $playerGameStats[$playerId]->avg_fouls,
                        'games_played' => (int) $playerGameStats[$playerId]->games_played,
                    ];
                }

                // Only include players with games played > 0 if season status is 11
                if ($seasonStatus != 11 || $stats['games_played'] > 0) {
                    $playerStats[] = [
                        'player_id' => $playerId,
                        'name' => $player->name,
                        'age' => $player->age,
                        'role' => $player->role,
                        'is_active' => $player->is_active,
                        'is_rookie' => $player->is_rookie,
                        'retirement_age' => $player->retirement_age,
                        'drafted_team' => $player->drafted_team,
                        'draft_status' => $player->draft_status,
                        'draft_class' => $player->draft_class,
                        'status' => $player->team_id == $teamId ? ($player->is_active ? 1 : 0) : 2,
                        'average_points_per_game' => $stats['average_points_per_game'],
                        'average_rebounds_per_game' => $stats['average_rebounds_per_game'],
                        'average_assists_per_game' => $stats['average_assists_per_game'],
                        'average_steals_per_game' => $stats['average_steals_per_game'],
                        'average_blocks_per_game' => $stats['average_blocks_per_game'],
                        'average_turnovers_per_game' => $stats['average_turnovers_per_game'],
                        'average_fouls_per_game' => $stats['average_fouls_per_game'],
                        'games_played' => $stats['games_played'],
                    ];
                }
            }
        }

        // Define role-based priority
        $rolePriority = [
            'star player' => 1,
            'starter' => 2,
            'role player' => 3,
            'bench' => 4,
        ];

        // Sort players by role and then by points (if available)
        usort($playerStats, function ($a, $b) use ($rolePriority) {
            if ($rolePriority[$a['role']] === $rolePriority[$b['role']]) {
                return $b['average_points_per_game'] <=> $a['average_points_per_game'];
            }
            return $rolePriority[$a['role']] <=> $rolePriority[$b['role']];
        });

        return response()->json([
            'players' => $playerStats,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'stats_count' => count($playerStatsData),
        ]);
    }

    public function getfreeagents(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = $request->input('itemsperpage', 10); // Number of items per page
        $currentPage = $request->input('page_num', 1); // Current page number
        $search = $request->input('search', ''); // Search term

        // Build the query with optional search filter
        $query = Player::select(
                'players.*',
                'teams.acronym as drafted_team',
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(award_name, ' (Season ', season_id, ')') SEPARATOR ', ') FROM season_awards WHERE season_awards.player_id = players.id) as awards"),
                DB::raw("(SELECT  CONCAT('Finals MVP (Season ', seasons.id, ')')  FROM seasons WHERE seasons.finals_mvp_id = players.id LIMIT 1) as finals_mvp"),
                DB::raw("CASE WHEN players.id = (SELECT finals_mvp_id FROM seasons WHERE seasons.finals_mvp_id = players.id) THEN 1 ELSE 0 END as is_finals_mvp"),
                DB::raw("(SELECT GROUP_CONCAT(seasons.name SEPARATOR ', ') FROM seasons WHERE seasons.finals_mvp_id = players.id) as finals_mvp_seasons")
            )
            ->where('players.contract_years', 0)
            ->where('players.is_active', 1)
            ->leftJoin('teams', 'players.drafted_team_id', '=', 'teams.id'); // Join teams on players.drafted_team_id

        // Apply search filter if provided
        if ($search) {
            $query->where('players.name', 'like', "%{$search}%");
        }

        // Add ordering for awards, finals MVP status, and role priority
        $query->orderByRaw("
            LENGTH(awards) DESC,
            is_finals_mvp DESC,
            FIELD(role, 'star player', 'starter', 'role player', 'bench')
        ");

        // Get total number of records
        $total = $query->count();

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

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
            'free_agents' => $freeAgents,
        ]);
    }

    public function getallplayers(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = $request->input('itemsperpage', 10); // Number of items per page
        $currentPage = $request->input('page_num', 1); // Current page number
        $search = $request->input('search', ''); // Search term

        // Calculate the offset for the query
        $offset = ($currentPage - 1) * $perPage;

        // Start building the query with optional search filter and join with teams
        $query = DB::table('players')
            ->select(
                'players.id as player_id',
                'players.country',
                'players.name',
                'players.age',
                'players.role',
                'players.is_active',
                'players.retirement_age',
                'players.contract_years',
                DB::raw("IF(players.team_id = 0, 'none', teams.name) as team_name"),

                // Get the list of awards for the player
                DB::raw("
                    (SELECT GROUP_CONCAT(
                            CONCAT(award_name, ' (Season ', season_awards.season_id, ')')
                            SEPARATOR ', ')
                     FROM season_awards
                     WHERE season_awards.player_id = players.id
                    ) as awards
                "),

                // Get the Finals MVP for the player, if applicable
                DB::raw("
                    COALESCE(
                        (SELECT
                            CONCAT('Finals MVP (Season ', seasons.id, ')')
                         FROM seasons
                         WHERE seasons.finals_mvp_id = players.id
                         LIMIT 1
                        ), '') as finals_mvp
                "),

                // Check if player is finals MVP (this is somewhat redundant with the previous subquery)
                DB::raw("CASE WHEN players.id = (SELECT finals_mvp_id FROM seasons WHERE seasons.finals_mvp_id = players.id) THEN 1 ELSE 0 END as is_finals_mvp")
            )
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id');

        // Apply search filter if provided
        if ($search) {
            $query->where('players.name', 'like', "%{$search}%");
        }

        // Add sorting by is_active status, then by role priority
        $query->orderBy('players.is_active', 'desc') // Active players first
              ->orderByRaw("FIELD(players.role, 'star player', 'starter', 'role player', 'bench')");

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
            'free_agents' => $freeAgents,
        ]);
    }


    // Add a player to a team with random attributes
    public function addplayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        // Check if the team already has 15 players
        $playerCount = Player::where('team_id', $request->team_id)
            ->where('is_active', 1) // Ensure players are active
            ->count();

        if ($playerCount >= 15) {
            return response()->json([
                'error' => true,
                'message' => 'Team already has 15 players. Cannot add more.',
            ], 400);
        }

        // Check if a player with the same name already exists in any team
        $existingPlayer = Player::where('name', $request->name)->first();
        if ($existingPlayer) {
            return response()->json([
                'error' => true,
                'message' => 'A player with this name already exists in another team.',
            ], 400);
        }

        // Generate random attributes
        $age = mt_rand(18, 25);
        $retirementAge = rand($age + 1, 45); // Retirement age should be greater than current age
        $injuryPronePercentage = rand(0, 100); // Random injury-prone percentage between 0 and 100
        $contractYears = rand(1, 5); // Random contract years between 1 and 5

        // Randomize player role
        $roles = ['starter', 'star player', 'role player', 'bench'];
        $role = $roles[array_rand($roles)];

        // Randomize player ratings
        $shootingRating = rand(1, 100);
        $defenseRating = rand(1, 100);
        $passingRating = rand(1, 100);
        $reboundingRating = rand(1, 100);
        $overallRating = ($shootingRating + $defenseRating + $passingRating + $reboundingRating) / 4;

        // Calculate contract expiration date
        $contractExpiresAt = Carbon::now()->addYears($contractYears);

        $player = Player::create([
            'name' => $request->name,
            'team_id' => $request->team_id,
            'age' => $age,
            'retirement_age' => $retirementAge,
            'injury_prone_percentage' => $injuryPronePercentage,
            'contract_years' => $contractYears,
            'contract_expires_at' => $contractExpiresAt,
            'is_active' => true,
            'role' => $role,
            'shooting_rating' => $shootingRating,
            'defense_rating' => $defenseRating,
            'passing_rating' => $passingRating,
            'rebounding_rating' => $reboundingRating,
            'overall_rating' => $overallRating,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Player added successfully',
            'player' => $player,
        ]);
    }
    public function addfreeagentplayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:players,name',
            'address' => 'required|string|max:255',
            'country' => 'required|string',
        ]);

        $latestSeasonId = DB::table('standings_view')->max('season_id');

        // Start at 1 if no records are found, otherwise increment the latest season ID
        $currentSeasonId = $latestSeasonId ? (int) $latestSeasonId + 1 : 1;

        // Check if a player with the same name already exists in any team
        $existingPlayer = Player::where('name', $request->name)->first();
        if ($existingPlayer) {
            return response()->json([
                'error' => true,
                'message' => 'A player with this name already exists in another team.',
            ], 400);
        }

        // Generate random attributes
        $age = mt_rand(18, 25);
        $retirementAge = rand($age + 1, 45); // Retirement age should be greater than current age
        $contractYears = rand(1, 5); // Random contract years between 1 and 5

        // Get random archetype and attributes
        $attributes = $this->getRandomArchetypeAndAttributes();
        $selectedArchetype = $attributes['archetype'];
        $shootingRating = $attributes['shooting_rating'];
        $defenseRating = $attributes['defense_rating'];
        $passingRating = $attributes['passing_rating'];
        $reboundingRating = $attributes['rebounding_rating'];

        // Randomize player role
        $roles = ['star player', 'starter', 'role player', 'bench'];
        $role = $roles[array_rand($roles)];

        // Modify ratings slightly based on role if needed
        switch ($role) {
            case 'star player':
                $shootingRating = min($shootingRating + rand(0, 10), 99);
                $defenseRating = min($defenseRating + rand(0, 10), 99);
                $passingRating = min($passingRating + rand(0, 10), 99);
                $reboundingRating = min($reboundingRating + rand(0, 10), 99);
                break;
                // Add modifications for other roles as necessary
        }

        // Calculate overall rating
        $overallRating = ($shootingRating + $defenseRating + $passingRating + $reboundingRating) / 4;

        // Calculate contract expiration date
        $contractExpiresAt = Carbon::now()->addYears($contractYears);

        $player = Player::create([
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'team_id' => 0,
            'age' => $age,
            'retirement_age' => $retirementAge,
            'injury_prone_percentage' => 0,
            'contract_years' => 0,
            'contract_expires_at' => $contractExpiresAt,
            'is_active' => true,
            'role' => $role,
            'type' => $selectedArchetype, // Save archetype name
            'shooting_rating' => $shootingRating,
            'defense_rating' => $defenseRating,
            'passing_rating' => $passingRating,
            'rebounding_rating' => $reboundingRating,
            'overall_rating' => $overallRating,
            'draft_id' => $currentSeasonId,
            'draft_order' => 0,
            'drafted_team_id' => 0,
            'is_drafted' => 0,
            'draft_status' => 'Rookie',
            'is_rookie' => true,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Player added successfully',
            'player' => $player,
        ]);
    }

    /**
     * Get a random archetype and its attributes.
     *
     * @return array
     */
    private function getRandomArchetypeAndAttributes()
    {
        // Define archetypes and their attribute ranges
        // Define archetypes and their attribute ranges
        $archetypes = [
            'playmaker' => [
                'shooting' => [70, 85],
                'defense' => [65, 80],
                'passing' => [85, 99],
                'rebounding' => [60, 75],
            ],
            'defender' => [
                'shooting' => [60, 75],
                'defense' => [85, 99],
                'passing' => [60, 75],
                'rebounding' => [70, 85],
            ],
            'scorer' => [
                'shooting' => [85, 99],
                'defense' => [60, 75],
                'passing' => [65, 80],
                'rebounding' => [60, 75],
            ],
            'all-rounder' => [
                'shooting' => [75, 90],
                'defense' => [75, 90],
                'passing' => [75, 90],
                'rebounding' => [75, 90],
            ],
            'hustler' => [
                'shooting' => [60, 75],
                'defense' => [70, 85],
                'passing' => [60, 75],
                'rebounding' => [65, 80],
            ],
            'underperformer' => [
                'shooting' => [50, 65],
                'defense' => [50, 65],
                'passing' => [50, 65],
                'rebounding' => [50, 65],
            ],
            'project' => [
                'shooting' => [40, 60],
                'defense' => [40, 60],
                'passing' => [40, 60],
                'rebounding' => [40, 60],
            ],
            'journeyman' => [
                'shooting' => [55, 70],
                'defense' => [55, 70],
                'passing' => [55, 70],
                'rebounding' => [55, 70],
            ],
            'benchwarmer' => [
                'shooting' => [45, 60],
                'defense' => [45, 60],
                'passing' => [45, 60],
                'rebounding' => [45, 60],
            ],
            'shooter' => [
                'shooting' => [80, 95],
                'defense' => [50, 65],
                'passing' => [55, 70],
                'rebounding' => [50, 65],
            ],
            'playoff-clutch' => [
                'shooting' => [75, 90],
                'defense' => [70, 85],
                'passing' => [70, 85],
                'rebounding' => [65, 80],
            ],
            'spot-up-shooter' => [
                'shooting' => [85, 99],
                'defense' => [50, 65],
                'passing' => [50, 65],
                'rebounding' => [50, 65],
            ],
            'energy-guy' => [
                'shooting' => [60, 75],
                'defense' => [65, 80],
                'passing' => [55, 70],
                'rebounding' => [60, 75],
            ],
            'weak-link' => [
                'shooting' => [45, 60],
                'defense' => [45, 60],
                'passing' => [45, 60],
                'rebounding' => [45, 60],
            ],
            'training-camp' => [
                'shooting' => [40, 55],
                'defense' => [40, 55],
                'passing' => [40, 55],
                'rebounding' => [40, 55],
            ],
            'specialist' => [
                'shooting' => [70, 85],
                'defense' => [50, 65],
                'passing' => [50, 65],
                'rebounding' => [50, 65],
            ],
        ];


        // Randomly select an archetype
        $archetypeKeys = array_keys($archetypes);
        $selectedArchetype = $archetypeKeys[array_rand($archetypeKeys)];
        $archetypeAttributes = $archetypes[$selectedArchetype];

        // Generate random ratings based on the selected archetype
        $attributes = [
            'archetype' => $selectedArchetype,
            'shooting_rating' => rand($archetypeAttributes['shooting'][0], $archetypeAttributes['shooting'][1]),
            'defense_rating' => rand($archetypeAttributes['defense'][0], $archetypeAttributes['defense'][1]),
            'passing_rating' => rand($archetypeAttributes['passing'][0], $archetypeAttributes['passing'][1]),
            'rebounding_rating' => rand($archetypeAttributes['rebounding'][0], $archetypeAttributes['rebounding'][1]),
        ];

        return $attributes;
    }

    // Generate a random age ensuring it is either at least 19 or exactly 30
    private function generateRandomAge()
    {
        $possibleAges = [19, 30];
        $randomAge = $possibleAges[array_rand($possibleAges)];

        // Return the age with a 50% chance of being 19 or 30
        return $randomAge;
    }

    public function getplayerplayoffperformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player stats for the given player across specified playoff rounds
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id') // Join with seasons table
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->leftJoin('player_ratings', function ($join) {
                $join->on('player_game_stats.player_id', '=', 'player_ratings.player_id')
                    ->on('player_game_stats.season_id', '=', 'player_ratings.season_id');
            }) // Left join with player_ratings table
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.team_id',
                'teams.name as team_name',
                'teams.conference_id',
                'player_game_stats.season_id',
                'player_ratings.overall_rating',
                'seasons.name as season_name', // Select season name
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played'), // Exclude DNP games
                \DB::raw('COALESCE(player_ratings.role, players.role) as role') // Use COALESCE to handle NULL roles
            )
            ->where('player_game_stats.player_id', $playerId)
            ->whereIn('schedules.round', ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals']) // Filter by playoff rounds
            ->groupBy('players.id', 'players.name', 'players.team_id', 'players.role', 'player_ratings.overall_rating', 'teams.name', 'teams.conference_id', 'player_game_stats.season_id', 'seasons.name', 'player_ratings.role')
            ->orderBy('player_game_stats.season_id', 'desc') // Sort by season_id in descending order
            ->get();

        if ($playerStats->isEmpty()) {
            return response()->json([
                'error' => 'No stats found for the given player.',
                'player_stats' => [],
            ], 404);
        }

        // Initialize an array to hold formatted player stats
        $formattedPlayerStats = [];

        foreach ($playerStats as $stats) {
            // Calculate averages
            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $stats->games_played : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $stats->games_played : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $stats->games_played : 0;
            $averageStealsPerGame = $stats->games_played > 0 ? $stats->total_steals / $stats->games_played : 0;
            $averageBlocksPerGame = $stats->games_played > 0 ? $stats->total_blocks / $stats->games_played : 0;
            $averageTurnoversPerGame = $stats->games_played > 0 ? $stats->total_turnovers / $stats->games_played : 0;
            $averageFoulsPerGame = $stats->games_played > 0 ? $stats->total_fouls / $stats->games_played : 0;

            // Append player with stats and team name
            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'overall_rating' => $stats->overall_rating,
                'team_name' => $stats->team_name,
                'team_id' => $stats->team_id,
                'conference_id' => $stats->conference_id,
                'role' => $stats->role, // Add player role
                'season_id' => $stats->season_id,
                'season_name' => $stats->season_name, // Add season name
                'total_points' => $stats->total_points,
                'total_rebounds' => $stats->total_rebounds,
                'total_assists' => $stats->total_assists,
                'total_steals' => $stats->total_steals,
                'total_blocks' => $stats->total_blocks,
                'total_turnovers' => $stats->total_turnovers,
                'total_fouls' => $stats->total_fouls,
                'games_played' => $stats->games_played,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }
        return response()->json([
            'player_stats' => $formattedPlayerStats,
        ]);
    }
    public function getplayerseasonperformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player stats for the given player excluding the specified playoff rounds
        $playerStats = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id') // Join with seasons table
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->leftJoin('player_ratings', function ($join) {
                $join->on('player_game_stats.player_id', '=', 'player_ratings.player_id')
                    ->on('player_game_stats.season_id', '=', 'player_ratings.season_id');
            }) // Left join with player_ratings table
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'players.team_id',
                'teams.name as team_name',
                'teams.conference_id',
                'player_game_stats.season_id',
                'player_ratings.overall_rating',
                'seasons.name as season_name', // Select season name
                \DB::raw('SUM(player_game_stats.points) as total_points'),
                \DB::raw('SUM(player_game_stats.rebounds) as total_rebounds'),
                \DB::raw('SUM(player_game_stats.assists) as total_assists'),
                \DB::raw('SUM(player_game_stats.steals) as total_steals'),
                \DB::raw('SUM(player_game_stats.blocks) as total_blocks'),
                \DB::raw('SUM(player_game_stats.turnovers) as total_turnovers'),
                \DB::raw('SUM(player_game_stats.fouls) as total_fouls'),
                \DB::raw('COUNT(DISTINCT CASE WHEN player_game_stats.minutes > 0 THEN player_game_stats.game_id END) as games_played'), // Exclude DNP games
                \DB::raw('COALESCE(player_ratings.role, players.role) as role') // Use COALESCE to handle NULL roles
            )
            ->where('player_game_stats.player_id', $playerId)
            ->whereNotIn('schedules.round', ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals']) // Exclude specific playoff rounds
            ->groupBy('players.id', 'players.name', 'players.team_id', 'players.role', 'player_ratings.overall_rating', 'teams.name', 'teams.conference_id', 'player_game_stats.season_id', 'seasons.name', 'player_ratings.role')
            ->orderBy('player_game_stats.season_id', 'desc') // Sort by season_id in descending order
            ->get();

        if ($playerStats->isEmpty()) {
            return response()->json([
                'error' => 'No stats found for the given player.',
                'player_stats' => [],
            ], 404);
        }

        // Initialize an array to hold formatted player stats
        $formattedPlayerStats = [];

        foreach ($playerStats as $stats) {
            // Calculate averages
            $averagePointsPerGame = $stats->games_played > 0 ? $stats->total_points / $stats->games_played : 0;
            $averageReboundsPerGame = $stats->games_played > 0 ? $stats->total_rebounds / $stats->games_played : 0;
            $averageAssistsPerGame = $stats->games_played > 0 ? $stats->total_assists / $stats->games_played : 0;
            $averageStealsPerGame = $stats->games_played > 0 ? $stats->total_steals / $stats->games_played : 0;
            $averageBlocksPerGame = $stats->games_played > 0 ? $stats->total_blocks / $stats->games_played : 0;
            $averageTurnoversPerGame = $stats->games_played > 0 ? $stats->total_turnovers / $stats->games_played : 0;
            $averageFoulsPerGame = $stats->games_played > 0 ? $stats->total_fouls / $stats->games_played : 0;

            // Append player with stats, team name, and role
            $formattedPlayerStats[] = [
                'player_id' => $stats->player_id,
                'player_name' => $stats->player_name,
                'team_name' => $stats->team_name,
                'team_id' => $stats->team_id,
                'conference_id' => $stats->conference_id,
                'season_id' => $stats->season_id,
                'overall_rating' => $stats->overall_rating,
                'season_name' => $stats->season_name, // Add season name
                'role' => $stats->role, // Add player role
                'total_points' => $stats->total_points,
                'total_rebounds' => $stats->total_rebounds,
                'total_assists' => $stats->total_assists,
                'total_steals' => $stats->total_steals,
                'total_blocks' => $stats->total_blocks,
                'total_turnovers' => $stats->total_turnovers,
                'total_fouls' => $stats->total_fouls,
                'games_played' => $stats->games_played,
                'average_points_per_game' => $averagePointsPerGame,
                'average_rebounds_per_game' => $averageReboundsPerGame,
                'average_assists_per_game' => $averageAssistsPerGame,
                'average_steals_per_game' => $averageStealsPerGame,
                'average_blocks_per_game' => $averageBlocksPerGame,
                'average_turnovers_per_game' => $averageTurnoversPerGame,
                'average_fouls_per_game' => $averageFoulsPerGame,
            ];
        }

        return response()->json([
            'player_stats' => $formattedPlayerStats,
        ]);
    }

    public function getplayermainperformance(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $playerId = $request->player_id;

        // Fetch player and team details
        $playerDetails = \DB::table('players')
            ->join('teams', 'players.team_id', '=', 'teams.id', 'left') // Join teams table to get team details
            ->join('teams as drafted_teams', 'players.drafted_team_id', '=', 'drafted_teams.id', 'left') // Join teams table to get team details
            ->join('seasons', 'players.draft_id', '=', 'seasons.id', 'left')
            ->where('players.id', $playerId)
            ->select('players.id as player_id', 'players.name as player_name', 'players.country as country', 'players.address as address', 'players.age as age', 'players.retirement_age as retirement_age', 'teams.name as team_name', 'players.role', 'players.contract_years', 'players.is_rookie', 'players.overall_rating', 'players.type', 'players.draft_status as draft_status', 'seasons.name as draft_class', 'drafted_teams.acronym as drafted_team')
            ->first();

        if (!$playerDetails) {
            return response()->json([
                'error' => 'Player not found.',
            ], 404);
        }

        // Fetch playoff performance
        $playoffPerformance = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->join('seasons', 'player_game_stats.season_id', '=', 'seasons.id')
            ->select(
                \DB::raw('SUM(CASE WHEN schedules.round = "round_of_16" THEN 1 ELSE 0 END) as round_of_16'),
                \DB::raw('SUM(CASE WHEN schedules.round = "quarter_finals" THEN 1 ELSE 0 END) as quarter_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "semi_finals" THEN 1 ELSE 0 END) as semi_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "interconference_semi_finals" THEN 1 ELSE 0 END) as interconference_semi_finals'),
                \DB::raw('SUM(CASE WHEN schedules.round = "finals" THEN 1 ELSE 0 END) as finals')
            )
            ->where('player_game_stats.player_id', $playerId)
            ->first();

        // Set default values if no performance data
        $playoffPerformance = $playoffPerformance ?: (object)[
            'round_of_16' => 0,
            'quarter_finals' => 0,
            'semi_finals' => 0,
            'interconference_semi_finals' => 0,
            'finals' => 0,
        ];

        // Fetch MVP count and seasons
        $awardsData = \DB::table('season_awards')
            ->join('players', 'season_awards.player_id', '=', 'players.id')
            ->join('teams', 'season_awards.team_id', '=', 'teams.id')
            ->leftJoin('seasons', 'season_awards.season_id', '=', 'seasons.id') // Join the seasons table
            ->where('season_awards.player_id', $playerId)
            ->select(
                'season_awards.award_name as award_name',
                'season_awards.season_id as season',
                'seasons.name as season_name', // Select the season name
                'teams.name as team_name'
            )
            ->distinct()
            ->get();


        // Fetch MVP count and seasons
        $mvpData = \DB::table('seasons')
            ->where('seasons.finals_mvp_id', $playerId)
            ->select('seasons.name as season_name')
            ->get();

        $mvpCount = $mvpData->count();

        // Fetch championship count and season names
        $championships = \DB::table('seasons')
            ->join('player_game_stats', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->join('teams', 'player_game_stats.team_id', '=', 'teams.id')
            ->select('seasons.name as season_name', 'seasons.finals_winner_name as championship_team')
            ->where('player_game_stats.player_id', $playerId)
            ->where('schedules.round', 'finals')
            ->whereColumn('seasons.id', 'player_game_stats.season_id')
            ->whereExists(function ($query) use ($playerId) {
                $query->select(\DB::raw(1))
                    ->from('schedules as s')
                    ->join('player_game_stats as pg', 's.game_id', '=', 'pg.game_id')
                    ->where('pg.team_id', '=', \DB::raw('player_game_stats.team_id'))
                    ->where('s.round', 'finals')
                    ->where('pg.player_id', $playerId)
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
            ->groupBy('seasons.name', 'seasons.finals_winner_name')
            ->get();

        $conference_championships = \DB::table('seasons')
            ->join('player_game_stats', 'seasons.id', '=', 'player_game_stats.season_id')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->join('teams as team', 'player_game_stats.team_id', '=', 'team.id') // Join with player’s team
            ->join('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
            ->join('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
            ->select(
                'seasons.name as season_name',
                \DB::raw('CASE
                    WHEN (schedules.home_id = team.id AND schedules.home_score > schedules.away_score) THEN home_team.name
                    WHEN (schedules.away_id = team.id AND schedules.away_score > schedules.home_score) THEN away_team.name
                    ELSE NULL
                END as championship_team')
            )
            ->where('player_game_stats.player_id', $playerId)
            ->where('schedules.round', 'semi_finals')
            ->whereColumn('seasons.id', 'player_game_stats.season_id')
            ->whereExists(function ($query) use ($playerId) {
                $query->select(\DB::raw(1))
                    ->from('schedules as s')
                    ->join('player_game_stats as pg', 's.game_id', '=', 'pg.game_id')
                    ->where('pg.team_id', '=', \DB::raw('player_game_stats.team_id'))
                    ->where('s.round', 'semi_finals')
                    ->where('pg.player_id', $playerId)
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
            ->groupBy('seasons.name', 'team.id', 'home_team.name', 'away_team.name', 'schedules.home_id', 'schedules.away_id', 'schedules.home_score', 'schedules.away_score')
            ->get();

        // Fetch career high stats
        $careerHighs = \DB::table('player_game_stats')
            ->select(
                \DB::raw('MAX(points) as career_high_points'),
                \DB::raw('MAX(rebounds) as career_high_rebounds'),
                \DB::raw('MAX(assists) as career_high_assists'),
                \DB::raw('MAX(steals) as career_high_steals'),
                \DB::raw('MAX(blocks) as career_high_blocks'),
                \DB::raw('MAX(turnovers) as career_high_turnovers'),
                \DB::raw('MAX(fouls) as career_high_fouls')
            )
            ->where('player_id', $playerId)
            ->first();

        // Calculate season count
        $seasonCount = \DB::table('player_game_stats')
            ->where('player_id', $playerId)
            ->distinct('season_id')
            ->count('season_id');

        // Calculate playoff count
        $playoffCount = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id')
            ->where('player_game_stats.player_id', $playerId)
            ->whereIn('schedules.round', ['round_of_16', 'quarter_finals', 'semi_finals', 'interconference_semi_finals', 'finals'])
            ->distinct('schedules.season_id')
            ->count('schedules.round');

        return response()->json([
            'player_details' => $playerDetails,
            'playoff_performance' => $playoffPerformance,
            'mvp_count' => $mvpCount,
            'mvp_seasons' => $mvpData->pluck('season_name'),
            'championships' => $championships,
            'conference_championships' => $conference_championships,
            'career_highs' => $careerHighs,
            'season_count' => $seasonCount,
            'awards' => $awardsData,
            'playoff_count' => $playoffCount,
        ]);
    }

    public function getPlayerGameLogsV1(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'season_id' => 'required|exists:seasons,id',
            'page_num' => 'required|integer|min:1',
            'itemsperpage' => 'required|integer|min:1',
        ]);

        $playerId = $request->player_id;
        $seasonId = $request->season_id;
        $page = $request->page_num;
        $perPage = $request->itemsperpage;

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Fetch player game logs for the given player and season with pagination
        $playerGameLogs = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams as player_team', 'player_game_stats.team_id', '=', 'player_team.id') // Join with player's team
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id') // Join with seasons table
            ->leftJoin('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
            ->leftJoin('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
            ->select(
                'player_game_stats.id as stat_id', // Include player_game_stats.id in the select
                'player_game_stats.game_id',
                'player_team.name as team_name',
                \DB::raw('CASE
                    WHEN player_game_stats.team_id = schedules.home_id THEN home_team.name
                    ELSE away_team.name
                END as opponent_team_name'), // Determine opponent team name
                'schedules.round as round', // Add round info
                'seasons.name as season_name', // Include season name
                \DB::raw('player_game_stats.points as game_points'),
                \DB::raw('player_game_stats.rebounds as game_rebounds'),
                \DB::raw('player_game_stats.assists as game_assists'),
                \DB::raw('player_game_stats.steals as game_steals'),
                \DB::raw('player_game_stats.blocks as game_blocks'),
                \DB::raw('player_game_stats.turnovers as game_turnovers'),
                \DB::raw('player_game_stats.fouls as game_fouls'),
                'player_game_stats.minutes as game_minutes',
                \DB::raw('(CASE
                    WHEN player_game_stats.team_id = schedules.home_id THEN
                        (CASE WHEN schedules.home_score > schedules.away_score THEN "Win" ELSE "Loss" END)
                    ELSE
                        (CASE WHEN schedules.away_score > schedules.home_score THEN "Win" ELSE "Loss" END)
                END) as game_result') // Determine win/loss
            )
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->orderBy('player_game_stats.id', 'desc') // Order by player_game_stats.id in descending order
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Fetch total count of records for pagination info
        $totalRecords = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->count();

        // Prepare pagination metadata
        $totalPages = ceil($totalRecords / $perPage);

        // Prepare response
        return response()->json([
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'game_logs' => $playerGameLogs,
        ]);
    }
    public function getplayergamelogs(Request $request)
    {
        // Validate the request data
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'season_id' => 'required|exists:seasons,id',
            'page_num' => 'required|integer|min:1',
            'itemsperpage' => 'required|integer|min:1',
        ]);

        $playerId = $request->player_id;
        $seasonId = $request->season_id;
        $page = $request->page_num;
        $perPage = $request->itemsperpage;

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Fetch player game logs for the given player and season with pagination
        $playerGameLogs = \DB::table('player_game_stats')
            ->join('players', 'player_game_stats.player_id', '=', 'players.id')
            ->join('teams as player_team', 'player_game_stats.team_id', '=', 'player_team.id') // Join with player's team to get team name
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->join('seasons', 'schedules.season_id', '=', 'seasons.id') // Join with seasons table
            ->leftJoin('teams as home_team', 'schedules.home_id', '=', 'home_team.id') // Join with home team
            ->leftJoin('teams as away_team', 'schedules.away_id', '=', 'away_team.id') // Join with away team
            ->select(
                'player_game_stats.id as stat_id', // Include player_game_stats.id in the select
                'player_game_stats.game_id',
                'player_team.name as team_name', // Player's team name
                \DB::raw('CASE
                WHEN player_game_stats.team_id = schedules.home_id THEN away_team.name
                ELSE home_team.name
            END as opponent_team_name'), // Determine opponent team name
                'schedules.round as round', // Add round info
                'seasons.name as season_name', // Include season name
                \DB::raw('player_game_stats.points as game_points'),
                \DB::raw('player_game_stats.rebounds as game_rebounds'),
                \DB::raw('player_game_stats.assists as game_assists'),
                \DB::raw('player_game_stats.steals as game_steals'),
                \DB::raw('player_game_stats.blocks as game_blocks'),
                \DB::raw('player_game_stats.turnovers as game_turnovers'),
                \DB::raw('player_game_stats.fouls as game_fouls'),
                'player_game_stats.minutes as game_minutes',
                \DB::raw('(CASE
                WHEN player_game_stats.team_id = schedules.home_id THEN
                    (CASE WHEN schedules.home_score > schedules.away_score THEN "Win" ELSE "Loss" END)
                ELSE
                    (CASE WHEN schedules.away_score > schedules.home_score THEN "Win" ELSE "Loss" END)
            END) as game_result') // Determine win/loss
            )
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->orderBy('player_game_stats.id', 'desc') // Order by player_game_stats.id in descending order
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Fetch total count of records for pagination info
        $totalRecords = \DB::table('player_game_stats')
            ->join('schedules', 'player_game_stats.game_id', '=', 'schedules.game_id') // Join with schedules table
            ->where('player_game_stats.player_id', $playerId)
            ->where('player_game_stats.season_id', $seasonId)
            ->count();

        // Prepare pagination metadata
        $totalPages = ceil($totalRecords / $perPage);

        // Prepare response
        return response()->json([
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'game_logs' => $playerGameLogs,
        ]);
    }
    public function getplayerswithfilters(Request $request)
    {
        $sortColumn = $request->input('sort_by');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('itemsperpage', 10);
        $page = $request->input('page_num', 1);
        $offset = ($page - 1) * $perPage;

        // Base query to get filtered players from the view
        $query = DB::table('player_playoff_appearances')
            ->select(
                'player_id',
                'player_name',
                'current_team_name',
                'round_of_32_appearances',
                'round_of_16_appearances',
                'quarter_finals_appearances',
                'semi_finals_appearances',
                'interconference_semi_finals_appearances',
                'finals_appearances',
                'total_playoff_appearances',
                'seasons_played_in_playoffs',
                'total_seasons_played',
                'teams_played_for_in_playoffs',
                'active_status',
                'championships_won'
            );

        // Apply sorting
        switch ($sortColumn) {
            case 'playoff_appearances':
                $query->orderBy('total_playoff_appearances', $sortOrder);
                break;
            case 'big_four':
                $query->orderBy('interconference_semi_finals_appearances', $sortOrder);
                break;
            case 'finals_appearances':
                $query->orderBy('finals_appearances', $sortOrder);
                break;
            case 'seasons_played':
                $query->orderBy('total_seasons_played', $sortOrder);
                break;
            case 'championships_won':
                $query->orderBy('championships_won', $sortOrder);
                break;
            default:
                // Default sorting if invalid sort column
                $query->orderBy('player_name', 'asc');
        }

        // Fetch total number of records
        $total = DB::table('players')
            ->count();

        // Fetch paginated results
        $players = $query->skip($offset)->take($perPage)->get();

        // Return paginated response
        return response()->json([
            'data' => $players,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ]);
    }

    public function gettop20playersalltime()
    {
        $top20Players = DB::table('top_20_players_all_time')->get();
        return response()->json($top20Players);
    }
    public function gettop10playersbyteam(Request $request)
    {
        $top10PlayersByTeam = DB::table('top_10_players_by_team_all_time')->where('team_id', $request->team_id)->get();
        return response()->json($top10PlayersByTeam);
    }
}
