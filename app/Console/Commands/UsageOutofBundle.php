<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Carbon;
use Cron\CronExpression;

use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

use App\Jobs\OutofBundle;

class UsageOutofBundle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:outofbundle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerts the user if out of bundle usage';

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
        $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
        if($task){
            $start_time = microtime(true);
            $notifybundle = [];
            $outofbundle = DB::table('user_plans')->select('user_id','service_total','outofbundle_notify','id as planid')                     
                            ->where(['plan_type' => 'sim','status' => 1, 'outofbundle_notify' => 0])->where('service_total', '>', 0)->get();

            if($outofbundle->isNotEmpty()){
                OutofBundle::dispatch($outofbundle)
                        ->delay(Carbon::now()->addSeconds(10));  
            }
            
            $end_time  = microtime(true);
            $exec_time = round(($end_time - $start_time), 5);

            $next_run = '';
            collect($this->schedule->events())->map(function ($event) use(&$next_run) {
                if(strpos($event->command, $this->signature)){
                  $next = CronExpression::factory($event->expression)->getNextRunDate();
                  $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
                }
            });
            ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time,'next_run' => $next_run,'executed' => 1]);
        }
    }
}
