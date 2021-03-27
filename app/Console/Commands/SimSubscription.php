<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;
use Log;
use DB;
use App\Models\ScheduledTask;
use App\Models\AutoPlan;

use App\Jobs\Subscription\SimSubscriptionJob;

class SimSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sim subscription';

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
                $currDay         = '2020-12-01';//Carbon::now()->startOfMonth()->toDateString();
                $getsubscription =  AutoPlan::select('auto_plan.id','auto_plan.user_list')
                                    ->where(function($query) use ($currDay){
                                        $query->where('auto_plan.status',1);
                                        $query->orWhereDate('auto_plan.status_changeon', '>=', $currDay);
                                    })
                                    ->join('tbl_plans as tp','tp.id','=','auto_plan.plan_id')
                                    ->whereIn('tp.provider',['O2','VUK'])
                                    ->where('auto_plan.status',1)
                                    ->whereDate('auto_plan.start_date', '<', $currDay)
                                    ->whereNotNull('auto_plan.user_list')
                                    ->get();
                $getsubscription->each(function ($item, $key){
                    try {
                        SimSubscriptionJob::dispatch($item->user_list,$item->id);
                    } catch (\Exception $e) {
                        Log::error('SimSubscriptionJob',[
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
                Log::error('SwitchSubscriptionJob',[
                    'error'=>$e->getMessage()
                ]);
            }
        }
    }
}
