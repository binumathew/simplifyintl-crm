<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CronStaffCommission::class,
        \App\Console\Commands\AutoPlanDisable::class,
        // \App\Console\Commands\CardExpiry::class,
        \App\Console\Commands\CallHistory::class,
        \App\Console\Commands\SimHistory::class,
        // \App\Console\Commands\AutoRecharge::class,
        \App\Console\Commands\AutoPlanSubscription::class,
        \App\Console\Commands\AdvPaidSubscription::class,
        \App\Console\Commands\AddonUsage::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('staff:commission')
                ->monthlyOn(1, '2:00');
        // $schedule->command('account:autoRecharge')                             
        //          ->dailyAt('04:30');
        $schedule->command('plan:subscription')                
                ->dailyAt('04:45'); //->dailyAt('04:45'); //->cron('* * * * *');
        $schedule->command('subscription:notification')
                ->dailyAt('10:00'); 
        $schedule->command('call:history')
                ->hourly();
        $schedule->command('sim:history')
                ->dailyAt('04:30');
        // $schedule->command('card:expiry')
        //          ->dailyAt('12.30'); 
        // $schedule->command('reserve:expiry')
        //          ->everyMinute();

                 //->cron('* * * * *');//
        $schedule->command('autoplan:disable')                 
               ->dailyAt('05:45');

        $schedule->command('advpaid:subscription')                 
               ->dailyAt('05:50');
        
        $schedule->command('addon:usage')                 
               ->dailyAt('10:00');
        $schedule->command('addon:usage')                 
                ->dailyAt('14:00');
        $schedule->command('addon:usage')                 
                ->dailyAt('19:00');
                
        // $schedule->command('addon:usage')                 
        //         ->cron('* * * * *');

        // $schedule->command('switch:totalusage')                 
        //        ->dailyAt('09:30');
    }

    /**
    * Get the timezone that should be used by default for scheduled events.
    *
    * @return \DateTimeZone|string|null
    */
    /*protected function scheduleTimezone()
    {
        return 'Asia/Kolkata';
    }*/
    
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
