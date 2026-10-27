<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Автопродление Премиума и пакетов продвижения (нужен cron: * * * * * php artisan schedule:run)
        $schedule->command('subscriptions:renew')->hourly()->withoutOverlapping();

        // Страховка: подтянуть оплаченные платежи ЮKassa, если вебхук не дошёл
        $schedule->command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping();
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
