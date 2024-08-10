<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\LeaguesController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeasonsController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TransactionsController;
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
    Route::prefix('dashboard/')->group(function(){
        Route::get('', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('champions', [DashboardController::class, 'champions'])->name('dashboard.champions');
        Route::post('recent', [DashboardController::class, 'recent'])->name('dashboard.recent');
        Route::post('topscorerteams', [DashboardController::class, 'topscorerteams'])->name('dashboard.team.topscorer');
        Route::post('rivals', [DashboardController::class, 'get_rivalries'])->name('dashboard.rivalries');
        Route::post('playoff_appearances', [DashboardController::class, 'playoff_appearances'])->name('dashboard.playoff.appearances');
        Route::post('topscorerplayers', [DashboardController::class, 'topscorerplayers'])->name('dashboard.player.topscorer');

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
        Route::post('create', [ScheduleController::class, 'createSeasonAndSchedule'])->name('schedule.create');
        Route::post('game', [ScheduleController::class, 'simulate'])->name('game.simulate');
        Route::post('game_per_round', [ScheduleController::class, 'simulateperround'])->name('game.simulate.round');
        Route::post('game_per_conference', [ScheduleController::class, 'simulateperconference'])->name('game.simulate.conference');
        Route::post('all_game', [ScheduleController::class, 'simulateall'])->name('game.simulate.all');
        Route::post('seasonplayoffschedule', [ScheduleController::class, 'playoffschedule'])->name('season.playoff.schedule');

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
        Route::post('list', [PlayersController::class, 'listPlayers'])->name('players.list');
        Route::post('add', [PlayersController::class, 'addPlayer'])->name('players.add');
        Route::post('add-freeagent', [PlayersController::class, 'addFreeAgentPlayer'])->name('players.add.free.agent');
        Route::post('waive', [PlayersController::class, 'waivePlayer'])->name('players.waive');
        Route::post('extend-contract', [PlayersController::class, 'extendContract'])->name('players.contract.extend');
        Route::post('/box-score', [PlayersController::class, 'getBoxScore'])->name('game.boxscore');
        Route::post('/top-10-players', [PlayersController::class, 'getBestPlayersInConference'])->name('top.players.conference.season');
        Route::post('/free-agents', [PlayersController::class, 'getFreeAgents'])->name('players.free.agents');
        Route::post('/all-players', [PlayersController::class, 'getAllPlayers'])->name('players.list.all');
        Route::post('/player-season-performance', [PlayersController::class, 'getPlayerSeasonPerformance'])->name('players.season.performance');
        Route::post('/player-play-off-performance', [PlayersController::class, 'getPlayerPlayoffPerformance'])->name('players.playoff.performance');
    });
    Route::prefix('transactions/')->group(function(){
        Route::get('', [TransactionsController::class, 'index'])->name('transactions.index');
        Route::post('/assign-team-free-agents', [TransactionsController::class, 'assignPlayerToRandomTeam'])->name('assign.freeagent.teams');
        Route::post('/auto-assign-team-free-agents', [TransactionsController::class, 'assignRemainingFreeAgents'])->name('auto.assign.freeagent.teams');

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
