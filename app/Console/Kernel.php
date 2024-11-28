<?php

namespace App\Console;

use App\Http\Controllers\AwardsController;
use App\Http\Controllers\LeadersController;
use App\Http\Controllers\TradeController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                // Check if the latest season status is 12
                app(AwardsController::class)->storeallplayerseasonstats();
                app(LeadersController::class)->updateAllTimeTopStats();
                // app(TradeController::class)->autoMultiTeamTrade();

                Log::info('Scheduler ran successfully!');
            } catch (\Exception $e) {
                Log::error('Error running scheduler: ' . $e->getMessage());
            }
        })->everyMinute(); // You can adjust the frequency to check (every minute in this example)
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

