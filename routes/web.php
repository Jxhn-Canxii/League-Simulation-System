<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\LeaguesController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeasonsController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SimulateController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeadersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {

    Route::prefix('records/')->group(function(){
        Route::get('', [RecordsController::class, 'index'])->name('records.index');
        Route::post('season-champions', [RecordsController::class, 'champions'])->name('records.champions');
        Route::post('recent-games', [RecordsController::class, 'recent'])->name('records.recent');
        Route::post('top-scorer-teams', [RecordsController::class, 'topscorerteams'])->name('records.team.topscorer');
        Route::post('winningest-teams', [RecordsController::class, 'winningestteams'])->name('records.team.winningest');
        Route::post('rivals-per-team', [RecordsController::class, 'get_rivalries'])->name('records.rivalries');
        Route::post('playoff-appearances', [RecordsController::class, 'playoff_appearances'])->name('records.playoff.appearances');
        Route::post('top-scorer-players', [RecordsController::class, 'topscorerplayers'])->name('records.player.topscorer');

    });

    Route::prefix('teams/')->group(function(){
        Route::get('', [TeamsController::class, 'index'])->name('teams.index');
        Route::post('list-teams', [TeamsController::class, 'list'])->name('teams.list');
        Route::post('add-teams', [TeamsController::class, 'add'])->name('teams.add');
        Route::post('update-teams', [TeamsController::class, 'update'])->name('teams.update');
        Route::post('delete-teams', [TeamsController::class, 'delete'])->name('teams.delete');
        Route::post('team-info', [TeamsController::class, 'teaminfo'])->name('teams.info');
        Route::post('team-season-finals', [TeamsController::class, 'teamseasonfinals'])->name('teams.season.finals');
        Route::post('team-season-standings', [TeamsController::class, 'teamseasonstandings'])->name('teams.season.standings');
        Route::post('team-season-history', [TeamsController::class, 'teamseasonhistory'])->name('teams.season.history');
        Route::post('team-last-season-performance', [TeamsController::class, 'teamlastseason'])->name('teams.last.season');
        Route::post('team-recent-matches', [TeamsController::class, 'teammatches'])->name('teams.matches');
        Route::post('team-head2head-records', [TeamsController::class, 'teammatchesh2h'])->name('teams.matches.h2h');
        Route::post('team-rivals', [TeamsController::class, 'teamrivals'])->name('teams.rivals');
        Route::post('team-latest-season', [TeamsController::class, 'teamslatestseason'])->name('teams.latest.season');
        Route::post('team-match-history', [TeamsController::class, 'matchhistory'])->name('match.history');
    });

    Route::prefix('simulate/')->group(function(){
        Route::post('game-playoff', [SimulateController::class, 'simulateplayoff'])->name('game.simulate.playoff');
        Route::post('game-regular', [SimulateController::class, 'simulateregular'])->name('game.simulate.regular');
        Route::post('get-round-schedule-ids', [SimulateController::class, 'getscheduleids'])->name('game.per.round');
        Route::post('game-per-round', [SimulateController::class, 'simulateperround'])->name('game.simulate.round');
    });

    Route::prefix('schedule/')->group(function(){
        Route::get('', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('list-schedules', [ScheduleController::class, 'list'])->name('schedule.list');
        //simulation and scheduling
        Route::post('create-schedule-regular', [ScheduleController::class, 'createseasonandschedule'])->name('create.schedule.regular');
        Route::post('create-schedule-playoff', [ScheduleController::class, 'playoffschedule'])->name('create.schedule.playoff');

    });

    Route::prefix('leaders/')->group(function(){
        Route::get('', [LeadersController::class, 'index'])->name('leaders.index');
        Route::get('single-stats-leaders', [LeadersController::class, 'getSingleStatsLeaders'])->name('single.stats.leaders');
        Route::get('total-stats-leaders', [LeadersController::class, 'getTotalStatsLeaders'])->name('total.stats.leaders');
        Route::get('average-stats-leaders', [LeadersController::class, 'getAverageStatsLeaders'])->name('average.stats.leaders');
        Route::get('update-stats-leaders', [LeadersController::class, 'updateAllTimeTopStats'])->name('update.stats.leaders');
        Route::get('update-season-stats-leaders/{season_id}', [LeadersController::class, 'updateAllTimeTopStatsPerSeason'])->name('update.season.stats.leaders');
    });

    Route::prefix('ratings/')->group(function(){
        Route::post('update-player-status', [RatingsController::class, 'updateactiveplayers'])->name('update.player.status');
    });

    Route::prefix('analytics/')->group(function(){
        Route::get('', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::post('get-all-standings', [AnalyticsController::class, 'get_all_standings'])->name('analytics.standings');
        Route::get('player-stats', [AnalyticsController::class, 'count_players'])->name('analytics.player.count');
        Route::post('current-season-leaders', [AnalyticsController::class, 'getseasonleaders'])->name('players.season.leaders');
        Route::get('draft-statistics', [AnalyticsController::class, 'getDraftPlayerStatistics'])->name('draft.statistics');
        Route::get('alltime-game-records', [AnalyticsController::class, 'getAllStatistics'])->name('alltime.game.records');
    });

    Route::prefix('draft/')->group(function(){
        Route::post('players-list', [DraftController::class, 'rookiedraftees'])->name('draft.list');
        Route::get('draft-order', [DraftController::class, 'draftorder'])->name('draft.orders');
        Route::get('draft-latest-results', [DraftController::class, 'draftresults'])->name('draft.results');
        Route::post('draft-season-results', [DraftController::class, 'draftresultsperseason'])->name('draft.season.results');
        Route::post('draft-players', [DraftController::class, 'draftplayers'])->name('draft.players');

    });

    Route::prefix('seasons/')->group(function(){
        Route::get('', [SeasonsController::class, 'index'])->name('seasons.index');
        Route::get('season-details/{season_id}', [SeasonsController::class, 'details'])->name('seasons.details');
        Route::post('list-season', [SeasonsController::class, 'list'])->name('seasons.list');

        //season info
        Route::post('season-information', [SeasonsController::class, 'seasoninfo'])->name('seasons.info');
        Route::post('seasons-per-league', [SeasonsController::class, 'seasonsperleague'])->name('league.seasons');
        Route::post('seasons-per-league-paginate', [SeasonsController::class, 'seasonsperleaguepaginate'])->name('league.seasons.paginate');
        Route::post('dropdown-season', [SeasonsController::class, 'getseasonsdropdown'])->name('seasons.dropdown');

    });

    Route::prefix('leagues/')->group(function(){
        Route::get('', [LeaguesController::class, 'index'])->name('leagues.index');
        Route::post('list-leagues', [LeaguesController::class, 'list'])->name('leagues.list');
        Route::post('add-league', [LeaguesController::class, 'add'])->name('leagues.add');
        Route::post('update-league', [LeaguesController::class, 'update'])->name('leagues.update');
        Route::post('delete-league', [LeaguesController::class, 'delete'])->name('leagues.delete');
        Route::get('dropdown-league', [LeaguesController::class, 'dropdown'])->name('leagues.dropdown');
    });

    Route::prefix('conferences/')->group(function(){
        Route::post('list-conferences', [ConferenceController::class, 'list'])->name('conferences.list');
        Route::post('add-conference', [ConferenceController::class, 'add'])->name('conferences.add');
        Route::post('delete-conference', [ConferenceController::class, 'delete'])->name('conferences.delete');

        Route::post('conference-standings', [ConferenceController::class, 'seasonstandings'])->name('conferences.standings');
        Route::post('conference-schedules', [ConferenceController::class, 'seasonschedules'])->name('conferences.schedules');
        Route::post('conference-playoffs', [ConferenceController::class, 'seasonsplayoffs'])->name('conferences.playoffs');
        Route::post('league-conference', [ConferenceController::class, 'leagueconference'])->name('conference.season.dropdown');

    });
    Route::prefix('games/')->group(function(){
        Route::post('box-score', [GameController::class, 'getboxscore'])->name('game.boxscore');
    });
    Route::prefix('players/')->group(function(){
        Route::get('', [PlayersController::class, 'index'])->name('players.index');
        Route::get('freeagents', [PlayersController::class, 'freeagents'])->name('freeagents.index');
        Route::post('list-players', [PlayersController::class, 'listplayers'])->name('players.list');
        Route::post('add-player', [PlayersController::class, 'addplayer'])->name('players.add');
        Route::post('add-free-agent', [PlayersController::class, 'addfreeagentplayer'])->name('players.add.free.agent');

        Route::post('free-agents', [PlayersController::class, 'getfreeagents'])->name('players.free.agents');
        Route::post('all-players', [PlayersController::class, 'getallplayers'])->name('players.list.all');
        Route::post('player-season-performance', [PlayersController::class, 'getplayerseasonperformance'])->name('players.season.performance');
        Route::post('player-play-off-performance', [PlayersController::class, 'getplayerplayoffperformance'])->name('players.playoff.performance');
        Route::post('player-main-performance', [PlayersController::class, 'getplayermainperformance'])->name('players.main.performance');
        Route::post('player-transactions', [PlayersController::class, 'getplayertransactions'])->name('players.season.transactions');
        Route::post('player-injury', [PlayersController::class, 'getplayerinjuryhistory'])->name('players.season.injury');

        Route::post('player-game-logs', [PlayersController::class, 'getplayergamelogs'])->name('players.game.logs');
        Route::post('players-playoff-filters', [PlayersController::class, 'getplayerswithfilters'])->name('filter.playoffs.player');

        Route::post('player-best-alltime', [PlayersController::class, 'gettop20playersalltime'])->name('best.players.alltime');
        Route::post('player-best-alltime-by-team', [PlayersController::class, 'gettop10playersbyteam'])->name('best.team.players.alltime');
    });

    Route::prefix('transactions/')->group(function(){
        Route::post('assign-team-free-agents', [TransactionsController::class, 'assignplayertorandomteam'])->name('assign.freeagent.teams');
        Route::post('auto-assign-team-free-agents', [TransactionsController::class, 'assignremainingfreeagents'])->name('auto.assign.freeagent.teams');
        Route::post('waive-player', [TransactionsController::class, 'waiveplayer'])->name('players.waive');
        Route::post('extend-contract-player', [TransactionsController::class, 'extendcontract'])->name('players.contract.extend');
        Route::post('player-transactions', [TransactionsController::class, 'gettransactions'])->name('players.transactions');

    });

    Route::prefix('awards/')->group(function(){
        Route::get('', [AwardsController::class, 'index'])->name('awards.index');
        Route::post('store-player-stats', [AwardsController::class, 'storeplayerseasonstats'])->name('store.player.stats');
        Route::get('override-store-player-stats', [AwardsController::class, 'storeallplayerseasonstats'])->name('store.player.stats.override');
        Route::post('player-awards', [AwardsController::class, 'storeseasonawards'])->name('player.awards');
        Route::get('player-awards-dropdown', [AwardsController::class, 'getawardnamesdropdown'])->name('player.awards.dropdown');
        Route::post('player-awards-filter', [AwardsController::class, 'filterawardsperseason'])->name('player.awards.filter');
        Route::post('player-season-awards', [AwardsController::class, 'getseasonawards'])->name('player.season.awards');
        Route::get('awarding/{season_id}', [AwardsController::class, 'storeseasonawardsauto'])->name('awarding.per.season');
    });

    Route::prefix('users/')->group(function(){
        Route::get('', [UserController::class, 'index'])->name('users.index');
    });

    Route::prefix('profile/')->group(function(){
        Route::get('', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
