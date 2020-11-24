<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon;
use Cron\CronExpression;
use App\Jobs\SimUsageAlerts;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

class UsageAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sim data usage alert if reached 90%';

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
            $datalimit = DB::table('user_plans as up')
                          ->select('up.data_usage','up.user_id','tp.data_limit','tp.provider','prorata','up.created_at','up.usage_notify','up.id as planid')
                          ->join('tbl_plans as tp','tp.id','=','up.plan_id')
                          ->where('up.status',1)->where('up.plan_type','sim')->get();

            if($datalimit->isNotEmpty()){
              $notifyusers = [];
              foreach ($datalimit as $dkey => $list) {
                if($list->provider != 'EE'){
                  if($list->usage_notify < 3){ // number of times alert send
                    $data_limit   = $list->data_limit;
                    if($data_limit != 0 && is_numeric($data_limit)){
                      $prorata  = $list->prorata;
                      $planid   = $list->planid;
                      $noofdays = $daysbetween = $togb = $calperc = 0;
                      switch ($prorata) {
                        case 1:
                          $planstart   = $list->created_at;
                          $planend     = Carbon::parse($planstart)->endOfMonth();
                          $noofdays    = Carbon::parse($planstart)->daysInMonth;
                          $daysbetween = Carbon::parse($planstart)->diffInDays($planend) + 1;
                          $usagelimit  = round((($data_limit/$noofdays) * $daysbetween),2);
                        break;          
                        default:
                          $usagelimit = $data_limit;
                        break;
                      }
                      $togb    = round($list->data_usage / pow(1024, 3),4);
                      $calperc = ceil(($togb / $usagelimit) * 100);
                      if($calperc >= 90 && $list->usage_notify < 3){
                          $calperc = ($calperc > 100)? 100:$calperc;
                          $obj = (object)['user_id' => $list->user_id, 'usage' => $calperc, 'limit'=> $usagelimit, 'planid' => $planid, 'category' => 3];
                          array_push($notifyusers, $obj);
                      }elseif($calperc >= 80 && $list->usage_notify < 2){
                          $obj = (object)['user_id' => $list->user_id, 'usage' => $calperc, 'limit'=> $usagelimit, 'planid' => $planid, 'category' => 2];
                          array_push($notifyusers, $obj);
                      }elseif($calperc >= 70 && $list->usage_notify < 1){
                          $obj = (object)['user_id' => $list->user_id, 'usage' => $calperc, 'limit'=> $usagelimit, 'planid' => $planid, 'category' => 1];
                          array_push($notifyusers, $obj);
                      }else{ continue; }
                    }
                  }
                }
              }
              if(!empty($notifyusers)){
                  SimUsageAlerts::dispatch($notifyusers)
                    ->delay(Carbon::now()->addSeconds(10));  
                }
            }
            $end_time = microtime(true);
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
