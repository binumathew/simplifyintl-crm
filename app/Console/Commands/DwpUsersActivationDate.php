<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;
use Log;

use App\Models\ScheduledTask;
use App\Models\UserPlan;
use App\Models\AutoPlan;

use App\Jobs\Activation\UpdateUserActivationDateJob;

class DwpUsersActivationDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:usersactivationdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Users activation date from DWP';

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
                $userplan   = UserPlan::select('user_plans.id','user_plans.user_id')
                              ->join('tbl_plans as tp','tp.id','=','user_plans.plan_id')
                              ->whereIn('tp.provider',['O2','VUK'])
                              ->where('user_plans.prorata',1)
                              //->where('user_plans.status',1)
                              ->where('user_plans.act_check',0)
                              ->get();

                $userplan->each(function ($item, $key) {
                    UpdateUserActivationDateJob::dispatch($item->user_id,$item->id);
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
               Log::error('usersactivationdate',[
                'error' =>   $e->getMessage()
                ]);
            }
        }
    }
}
