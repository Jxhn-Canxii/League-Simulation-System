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
        // Tasks that run every minute
        $schedule->call(function () {
            try {
                // Log and execute storeallplayerseasonstats
                // try {
                //     app(AwardsController::class)->storeallplayerseasonstats();
                //     Log::info('storeallplayerseasonstats executed successfully!');
                //     logger('storeallplayerseasonstats executed successfully at: ' . now());
                // } catch (\Exception $e) {
                //     Log::error('Error in storeallplayerseasonstats: ' . $e->getMessage());
                //     logger('Error in storeallplayerseasonstats: ' . $e->getMessage());
                // }

                // Log and execute updateAllTimeTopStats
                try {
                    app(LeadersController::class)->updateAllTimeTopStats();
                    Log::info('updateAllTimeTopStats executed successfully!');
                    logger('updateAllTimeTopStats executed successfully at: ' . now());
                } catch (\Exception $e) {
                    Log::error('Error in updateAllTimeTopStats: ' . $e->getMessage());
                    logger('Error in updateAllTimeTopStats: ' . $e->getMessage());
                }

                // try {
                //     $storeallseasons = app(AwardsController::class)->processAllSeasonPlayerStats();
                //     if ($storeallseasons) {
                //         Log::info('processAllSeasonPlayerStats executed successfully!');
                //         logger('processAllSeasonPlayerStats executed successfully at: ' . now());
                //     }
                // } catch (\Exception $e) {
                //     Log::error('Error in processAllSeasonPlayerStats: ' . $e->getMessage());
                //     logger('Error in processAllSeasonPlayerStats: ' . $e->getMessage());
                // }
                // // Overall task log
                // Log::info('Every-minute tasks ran successfully!');
                // logger('All tasks completed successfully at: ' . now());

            } catch (\Exception $e) {
                Log::error('Error running every-minute tasks: ' . $e->getMessage());
                logger('Error running tasks: ' . $e->getMessage());
            }
        })->hourly();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
