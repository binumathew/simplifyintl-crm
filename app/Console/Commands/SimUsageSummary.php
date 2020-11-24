<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon;
use App\Models\TblPlan;
use App\Models\UserPlan;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

class SimUsageSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:usagesummary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate total usage for user';

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
            $users = DB::table('users as u')->select('u.id','up.id as user_plan_id','plan_id','up.created_at','prorata')
                  ->join('user_plans as up','up.user_id','=','u.id')->where('plan_type', 'sim')
                  ->where('u.status', 1)->where('up.status', 1)->orderBy('plan_id')->get();
            if($users->isNotEmpty()){
                $prevplan = $serviceType = 0;
                foreach ($users as $user) {                            
                    $user_id = $user->id;
                    $user_plan_id = $user->user_plan_id;
                    $planid = $user->plan_id;
                    if($prevplan != $planid){
                      $prevplan = $planid;
                      $plans = TblPlan::find($planid);
                      $serviceType = $plans->network->service_type;
                    }
                    
                    $plandate = $user->created_at;
                    switch ($serviceType) {
                        case 1:
                            $startdate = Carbon::parse($plandate)->format('Y-m-d');
                            $enddate = Carbon::parse($plandate)->addDays(30)->format('Y-m-d');
                        break;
                        case 2:
                            $prorata = $user->prorata;
                            switch ($prorata) {
                                case 0:
                                    $startdate = Carbon::parse($plandate)->startOfMonth()->format('Y-m-d'); 
                                    $enddate = Carbon::parse($startdate)->endOfMonth()->format('Y-m-d');
                                break;
                                case 1:
                                    $startdate = Carbon::parse($plandate)->format('Y-m-d');
                                    $enddate = Carbon::parse($startdate)->endOfMonth()->format('Y-m-d');
                                break;                                    
                                default:
                                break;
                            }
                            break;
                        default:
                            break;
                    }

                    $calllogs = DB::table('user_calls')->select(DB::raw("SUM(duration) as duration"),DB::raw("SUM(cost) as cost"),DB::raw("COUNT(user_id) as totalcalls"))
                                    ->where('service_type', 1)->where('user_id', $user_id)->whereDate('connect_date', '>=', $startdate)
                                    ->whereDate('connect_date', '<=', $enddate)->where('history_from', 2)->groupBy('user_id')->get();
                    
                    $datalogs = DB::table('usage_history')->select(DB::raw("SUM(duration) as volume"),DB::raw("SUM(amount) as cost"))
                                    ->where('service_type', 'DATA')->where('user_id',$user_id)->whereDate('date', '>=', $startdate)
                                    ->whereDate('date', '<=', $enddate)->groupBy('user_id')->get();

                    $smslogs = DB::table('usage_history')->select(DB::raw("SUM(amount) as cost"),DB::raw("SUM(duration) as totalsms"))
                                    ->whereDate('date', '>=', $startdate)->whereDate('date', '<=', $enddate)->where('user_id', $user_id)
                                    ->where('service_type','SMS_MO')->groupBy('user_id')->get();

                    $call_usage = $call_cost = $data_usage = $data_cost = $sms_count = $sms_cost = $service_cost = $totalcalls = 0;
                    
                    if($calllogs->isNotEmpty()){
                        $call_usage = $calllogs[0]->duration;
                        $totalcalls = $calllogs[0]->totalcalls;
                        $call_cost  = $calllogs[0]->cost;
                        $call_cost  = ($call_cost != 0) ? ($call_cost + ($call_cost*.25)) : 0;
                    }
                    if($datalogs->isNotEmpty()){
                        $data_usage = $datalogs[0]->volume;
                        $data_cost  = $datalogs[0]->cost;
                        $data_cost  = ($data_cost != 0) ? ($data_cost + ($data_cost*.25)): 0;
                    }
                    if($smslogs->isNotEmpty()){
                        $sms_count = $smslogs[0]->totalsms;
                        $sms_cost  = $smslogs[0]->cost;
                        $sms_cost  = ($sms_cost != 0) ? ($sms_cost + ($sms_cost*.10)) : 0;
                    }

                    $service_cost = number_format($call_cost+ $data_cost + $sms_cost,2,'.','');
                    DB::table('user_plans')->where('id', $user_plan_id)                            
                        ->update(['data_usage' => $data_usage, 'call_usage' => $call_usage, 'sms_count' => $sms_count, 'service_total' => $service_cost, 'data_cost' => $data_cost, 'call_cost' => $call_cost, 'total_calls' => $totalcalls, 'sms_cost' => $sms_cost]);                
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
