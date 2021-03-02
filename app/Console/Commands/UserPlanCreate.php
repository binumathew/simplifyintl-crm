<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;
use Log;
use App\Models\ScheduledTask;
use App\Models\AutoPlan;

use App\Jobs\Plans\UpdateorCreateUserPlanJob;
class UserPlanCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'userplan:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create active users plan entry';

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
                $start_time      = microtime(true);
                $currDay         = Carbon::now()->startOfMonth()->toDateString();
                $getsubscription =  AutoPlan::select('user_list','plan_id')
                                    ->where(function($query) use ($currDay){
                                        $query->where('status',1);
                                        $query->orWhereDate('status_changeon', '>=', $currDay);
                                    })
                                    ->whereDate('start_date', '<', $currDay)
                                    ->whereNotNull('user_list')
                                    ->whereIn('id',[100007])
                                    ->get();

                $getsubscription->each(function ($item, $key){
                    try {
                       UpdateorCreateUserPlanJob::dispatch($item->user_list,$item->plan_id);
                    } catch (\Exception $e) {
                        Log::error('UpdateorCreateUserPlanJob',[
                            'params' =>   $item,
                            'error'=>$e->getMessage()
                        ]);
                    }
                    
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
                Log::error('UpdateorCreateUserPlanJob',[
                    'error'=>$e->getMessage()
                ]);
            }
        }
    }
}
