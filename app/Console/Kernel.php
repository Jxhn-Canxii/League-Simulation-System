<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\LeadersController;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            // Check if the latest season status is 12
            $latestSeason = DB::table('seasons')
                ->orderByDesc('id') // Assuming 'id' is the season identifier
                ->first();

            // If the latest season's status is 12, run the update
            if ($latestSeason && $latestSeason->status == 12) {
                // Call the updateAllTimeTopStats method from LeadersController
                app(LeadersController::class)->updateAllTimeTopStats();
            }

            \Log::info('Scheduler is running every minute!');
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
