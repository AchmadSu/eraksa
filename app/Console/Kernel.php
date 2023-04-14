<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('study_programs:truncate')->everyFiveMinutes();
        $schedule->command('placements:truncate')->everyFiveMinutes();
        $schedule->command('category_assets:truncate')->everyFiveMinutes();
        $schedule->command('users:truncate')->everyFiveMinutes();
        $schedule->command('assets:truncate')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
