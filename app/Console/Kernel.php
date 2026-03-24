<?php

namespace App\Console;

use App\Jobs\PingServer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new PingServer)->everyMinute();
        $schedule->command('assets:generate-service-tasks')->dailyAt('01:00');
        $schedule->command('assets:send-service-reminders')->everyFifteenMinutes();
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
