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

Route::get('/', function () {
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
        Route::post('champions', [RecordsController::class, 'champions'])->name('records.champions');
        Route::post('recent', [RecordsController::class, 'recent'])->name('records.recent');
        Route::post('topscorerteams', [RecordsController::class, 'topscorerteams'])->name('records.team.topscorer');
        Route::post('winningestteams', [RecordsController::class, 'winningestteams'])->name('records.team.winningest');
        Route::post('rivals', [RecordsController::class, 'get_rivalries'])->name('records.rivalries');
        Route::post('playoff_appearances', [RecordsController::class, 'playoff_appearances'])->name('records.playoff.appearances');
        Route::post('topscorerplayers', [RecordsController::class, 'topscorerplayers'])->name('records.player.topscorer');

    });

    Route::prefix('teams/')->group(function(){
        Route::get('', [TeamsController::class, 'index'])->name('teams.index');
        Route::post('list', [TeamsController::class, 'list'])->name('teams.list');
        Route::post('add', [TeamsController::class, 'add'])->name('teams.add');
        Route::post('update', [TeamsController::class, 'update'])->name('teams.update');
        Route::post('delete', [TeamsController::class, 'delete'])->name('teams.delete');
        Route::post('info', [TeamsController::class, 'teaminfo'])->name('teams.info');
        Route::post('season_finals', [TeamsController::class, 'teamseasonfinals'])->name('teams.season.finals');
        Route::post('season_standings', [TeamsController::class, 'teamseasonstandings'])->name('teams.season.standings');
        Route::post('season_history', [TeamsController::class, 'teamseasonhistory'])->name('teams.season.history');
        Route::post('last_season', [TeamsController::class, 'teamlastseason'])->name('teams.last.season');
        Route::post('matches', [TeamsController::class, 'teammatches'])->name('teams.matches');
        Route::post('head2head', [TeamsController::class, 'teammatchesh2h'])->name('teams.matches.h2h');
        Route::post('rivals', [TeamsController::class, 'teamrivals'])->name('teams.rivals');
        Route::post('latest_season', [TeamsController::class, 'teamslatestseason'])->name('teams.latest.season');
        Route::post('match_history', [TeamsController::class, 'matchhistory'])->name('match.history');
    });
    Route::prefix('schedule/')->group(function(){
        Route::get('', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('list', [ScheduleController::class, 'list'])->name('schedule.list');

        //simulation and scheduling
        Route::post('create', [ScheduleController::class, 'createseasonandschedule'])->name('schedule.create');
        Route::post('game-playoff', [ScheduleController::class, 'simulateplayoff'])->name('game.simulate.playoff');
        Route::post('game-regular', [ScheduleController::class, 'simulateregular'])->name('game.simulate.regular');
        Route::post('get-round-schedule-ids', [ScheduleController::class, 'getscheduleids'])->name('game.per.round');
        Route::post('game_per_round', [ScheduleController::class, 'simulateperround'])->name('game.simulate.round');
        Route::post('game_per_conference', [ScheduleController::class, 'simulateperconference'])->name('game.simulate.conference');
        Route::post('all_game', [ScheduleController::class, 'simulateall'])->name('game.simulate.all');
        Route::post('seasonplayoffschedule', [ScheduleController::class, 'playoffschedule'])->name('season.playoff.schedule');

    });
    Route::prefix('ratings/')->group(function(){
        Route::post('update-player-status', [RatingsController::class, 'updateactiveplayers'])->name('update.player.status');

    });
    Route::prefix('analytics/')->group(function(){
        Route::get('', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::post('get-all-standings', [AnalyticsController::class, 'get_all_standings'])->name('analytics.standings');
        Route::get('player-stats', [AnalyticsController::class, 'count_players'])->name('analytics.player.count');
    });
    Route::prefix('draft/')->group(function(){
        Route::post('players-list', [DraftController::class, 'rookiedraftees'])->name('draft.list');
        Route::get('draft-order', [DraftController::class, 'draftorder'])->name('draft.orders');
        Route::get('draft-results', [DraftController::class, 'draftresults'])->name('draft.results');
        Route::post('draft-players', [DraftController::class, 'draftplayers'])->name('draft.players');

    });
    Route::prefix('seasons/')->group(function(){
        Route::get('', [SeasonsController::class, 'index'])->name('seasons.index');
        Route::post('list', [SeasonsController::class, 'list'])->name('seasons.list');

        //season info
        Route::post('seasoninfo', [SeasonsController::class, 'seasoninfo'])->name('seasons.info');
        Route::post('seasonsperleague', [SeasonsController::class, 'seasonsperleague'])->name('league.seasons');
        Route::post('seasonsperleaguepaginate', [SeasonsController::class, 'seasonsperleaguepaginate'])->name('league.seasons.paginate');
        Route::post('seasonsdropdown', [SeasonsController::class, 'getseasonsdropdown'])->name('seasons.dropdown');

    });
    Route::prefix('leagues/')->group(function(){
        Route::get('', [LeaguesController::class, 'index'])->name('leagues.index');
        Route::post('list', [LeaguesController::class, 'list'])->name('leagues.list');
        Route::post('add', [LeaguesController::class, 'add'])->name('leagues.add');
        Route::post('update', [LeaguesController::class, 'update'])->name('leagues.update');
        Route::post('delete', [LeaguesController::class, 'delete'])->name('leagues.delete');
        Route::get('dropdown', [LeaguesController::class, 'dropdown'])->name('leagues.dropdown');
    });
    Route::prefix('conferences/')->group(function(){
        Route::post('list', [ConferenceController::class, 'list'])->name('conferences.list');
        Route::post('add', [ConferenceController::class, 'add'])->name('conferences.add');
        Route::post('delete', [ConferenceController::class, 'delete'])->name('conferences.delete');

        Route::post('conferencestandings', [ConferenceController::class, 'seasonstandings'])->name('conferences.standings');
        Route::post('conferenceschedules', [ConferenceController::class, 'seasonschedules'])->name('conferences.schedules');
        Route::post('conferenceplayoffs', [ConferenceController::class, 'seasonsplayoffs'])->name('conferences.playoffs');
        Route::post('leagueconference', [ConferenceController::class, 'leagueconference'])->name('conference.season.dropdown');

    });

    Route::prefix('players/')->group(function(){
        Route::get('', [PlayersController::class, 'index'])->name('players.index');
        Route::post('list', [PlayersController::class, 'listplayers'])->name('players.list');
        Route::post('add', [PlayersController::class, 'addplayer'])->name('players.add');
        Route::post('add-free-agent', [PlayersController::class, 'addfreeagentplayer'])->name('players.add.free.agent');
        Route::post('waive', [PlayersController::class, 'waiveplayer'])->name('players.waive');
        Route::post('extend-contract', [PlayersController::class, 'extendcontract'])->name('players.contract.extend');
        Route::post('/box-score', [PlayersController::class, 'getboxscore'])->name('game.boxscore');
        Route::post('/top-10-players', [AwardsController::class, 'getbestplayersinconference'])->name('top.players.conference.season');
        Route::post('/free-agents', [PlayersController::class, 'getfreeagents'])->name('players.free.agents');
        Route::post('/all-players', [PlayersController::class, 'getallplayers'])->name('players.list.all');
        Route::post('/player-season-performance', [PlayersController::class, 'getplayerseasonperformance'])->name('players.season.performance');
        Route::post('/player-play-off-performance', [PlayersController::class, 'getplayerplayoffperformance'])->name('players.playoff.performance');
        Route::post('/player-main-performance', [PlayersController::class, 'getplayermainperformance'])->name('players.main.performance');
        Route::post('/player-game-logs', [PlayersController::class, 'getplayergamelogs'])->name('players.game.logs');
        Route::post('/players-playoff-filters', [PlayersController::class, 'getplayerswithfilters'])->name('filter.playoffs.player');
        Route::post('/player-best-alltime', [PlayersController::class, 'gettop20playersalltime'])->name('best.players.alltime');
        Route::post('/player-best-alltime-by-team', [PlayersController::class, 'gettop10playersbyteam'])->name('best.team.players.alltime');
    });
    Route::prefix('transactions/')->group(function(){
        Route::post('/assign-team-free-agents', [TransactionsController::class, 'assignplayertorandomteam'])->name('assign.freeagent.teams');
        Route::post('/auto-assign-team-free-agents', [TransactionsController::class, 'assignremainingfreeagents'])->name('auto.assign.freeagent.teams');

    });
    Route::prefix('awards/')->group(function(){
        Route::get('', [AwardsController::class, 'index'])->name('awards.index');
        Route::post('/store-player-stats', [AwardsController::class, 'storeplayerseasonstats'])->name('store.player.stats');
        Route::post('/player-awards', [AwardsController::class, 'storeseasonawards'])->name('player.awards');
        Route::get('/player-awards-dropdown', [AwardsController::class, 'getawardnamesdropdown'])->name('player.awards.dropdown');
        Route::post('/player-awards-filter', [AwardsController::class, 'filterawardsperseason'])->name('player.awards.filter');
        Route::post('/player-season-awards', [AwardsController::class, 'getseasonawards'])->name('player.season.awards');
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
