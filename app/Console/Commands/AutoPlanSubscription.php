<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;

use App\Models\ScheduledTask;
use App\Models\AutoPlan;

use App\Jobs\Subscription\AutoPlanSubscriptionJob;

class AutoPlanSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plan will subscribe every month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(ScheduledTask::where(['command' => $this->signature, 'status' => 1])->exists()){
            try {
                $start_time = microtime(true);

                $curr_day   = Carbon::now()->startOfMonth()->format('Y-m-d');
                $getuser    = AutoPlan::whereDate('next_renewal', $curr_day)
                                //->whereNotIn('plan_id', [1, 2, 3, 4, 5, 6, 15])
                                ->where('status', 1)
                                ->where('adv_pay', 0)
                                ->whereDate('card_expiry', '>=', $curr_day)
                                ->groupBy('user_id')
                                ->get();
                $getuser->each(function ($item, $key) {
                    AutoPlanSubscriptionJob::dispatch($item->user_id);
                });

                $end_time = microtime(true);
                $exec_time = round(($end_time - $start_time), 5);
                $next_run = '';
                collect($this->schedule->events())->map(function ($event) use(&$next_run) {
                  if(strpos($event->command, $this->signature)){
                    $next = CronExpression::factory($event->expression)->getNextRunDate();
                    $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
                  }
                });

                ScheduledTask::where('command', $this->signature)
                    ->update(['run_time' => $exec_time,'next_run' => $next_run]);
            } catch (\Exception $e) {
               
            }
        }
    }
}
