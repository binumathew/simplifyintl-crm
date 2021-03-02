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
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 10000);
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
            // $userlist   = DB::table('users as u')->select('u.id','up.id as user_plan_id','plan_id','up.created_at','prorata')
            //                 ->join('user_plans as up','up.user_id','=','u.id')->where('plan_type', 'sim')
            //                 ->where('u.status', 1)->where('up.status', 1)->orderBy('plan_id')->get();
                            
            $start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end_date   = Carbon::now()->endOfMonth()->format('Y-m-d').' 23:59:59';
            $userlist = DB::table('users as u')->select('u.id','up.id as user_plan_id','plan_id','up.created_at','prorata')
                            ->join('user_plans as up','up.user_id','=','u.id')->where('plan_type', 'sim')
                            // ->where('u.status', 1)
                            // ->where('up.status', 1)
                            ->whereDate('up.created_at', '>=',$start_date)
                            ->whereDate('up.created_at', '<=',$end_date)
                            ->orderBy('plan_id')->get();
                            

            if($userlist->isNotEmpty()){
                $prevplan = $serviceType = 0;
                foreach ($userlist as $user) {
                    $user_id = $user->id;
                    $activeplanid   = $user->user_plan_id;
                    $planid         = $user->plan_id;                    
                    if($prevplan != $planid){
                        $prevplan = $planid;
                        $plans = TblPlan::find($planid);
                        $serviceType = $plans->network->service_type;
                    }
                    $serviceType    = $plans->network->service_type;
                    $plandate       = $user->created_at;

                    switch ($serviceType) {
                        case 1:
                            $startdate = Carbon::parse($plandate)->format('Y-m-d');
                            $enddate = Carbon::parse($plandate)->addDays(30)->format('Y-m-d');
                            break;
                        case 2:
                            $prorata    = $user->prorata;
                            $startdate  = Carbon::parse($plandate)->startOfMonth()->format('Y-m-d');
                            $enddate    = Carbon::parse($startdate)->endOfMonth()->format('Y-m-d');
                            break;
                        default:
                            break;
                    }
                    $calllogs   = DB::table('user_calls')
                            ->select(DB::raw("SUM(duration) as duration"),DB::raw("SUM(cost) as cost"),DB::raw("SUM(base_cost) as base_cost"),DB::raw("SUM(reseller_cost) as reseller_cost"),DB::raw("COUNT(user_id) as totalcalls"))                                
                            ->whereDate('connect_date', '>=', $startdate)
                            ->whereDate('connect_date', '<=', $enddate)
                            ->where('user_id',$user_id)  
                            ->where('history_from', 2)
                            ->whereIn('service_type',[1,3])                                                              
                            ->groupBy('user_id')->get();

                    $datalogs   = DB::table('usage_history')
                            ->select(DB::raw("SUM(duration) as volume"),DB::raw("SUM(amount) as cost"),DB::raw("SUM(base_amount) as base_amount"),DB::raw("SUM(reseller_amount) as reseller_amount"))
                            ->where('service_type', 'DATA')
                            ->where('user_id',$user_id)
                            ->whereDate('date', '>=', $startdate)
                            ->whereDate('date', '<=', $enddate)
                            ->groupBy('user_id')->get();


                    $smslogs    = DB::table('usage_history')
                            ->select(DB::raw("SUM(amount) as cost"),DB::raw("SUM(duration) as totalsms"),DB::raw("SUM(base_amount) as base_amount"),DB::raw("SUM(reseller_amount) as reseller_amount"))
                            ->whereDate('date', '>=', $startdate)
                            ->whereDate('date', '<=', $enddate)
                            ->where('user_id',$user_id)
                            ->where('service_type','SMS_MO')
                            ->groupBy('user_id')->get();

                    $call_usage = $call_cost = $data_usage = $data_cost = $sms_count = $sms_cost = $service_cost = $totalcalls = $base_call_cost = $reseller_call_cost = $base_sms_cost = $base_data_cost= $reseller_data_cost = $reseller_sms_cost = $base_cost = $reseller_cost = 0;
                    
                    if($calllogs->isNotEmpty()){
                        $call_usage          = $calllogs[0]->duration;
                        $totalcalls          = $calllogs[0]->totalcalls;
                        $call_cost           = $calllogs[0]->cost;
                        $base_call_cost      = $calllogs[0]->base_cost;
                        $reseller_call_cost  = $calllogs[0]->reseller_cost;
                        $call_cost           = ($call_cost != 0) ? $call_cost : 0;
                        $base_call_cost      = ($base_call_cost != 0) ? $base_call_cost : 0;
                        $reseller_call_cost  = ($reseller_call_cost != 0) ? $reseller_call_cost : 0;
                    }
                    if($datalogs->isNotEmpty()){
                        $data_usage          = $datalogs[0]->volume;
                        $data_cost           = $datalogs[0]->cost;
                        $base_data_cost      = $datalogs[0]->base_amount;
                        $reseller_data_cost  = $datalogs[0]->reseller_amount;
                        $data_cost           = ($data_cost != 0) ? $data_cost  : 0;
                        $base_data_cost      = ($base_data_cost != 0) ? $base_data_cost : 0;
                        $reseller_data_cost  = ($reseller_data_cost != 0) ? $reseller_data_cost : 0;
                    }
                    if($smslogs->isNotEmpty()){
                        $sms_count              = $smslogs[0]->totalsms;
                        $sms_cost               = $smslogs[0]->cost;
                        $base_sms_cost          = $smslogs[0]->base_amount;
                        $reseller_sms_cost      = $smslogs[0]->reseller_amount;
                        $sms_cost               = ($sms_cost != 0) ? $sms_cost : 0;
                        $base_sms_cost          = ($base_sms_cost != 0) ? $base_sms_cost : 0;
                        $reseller_sms_cost      = ($reseller_sms_cost != 0) ? $reseller_sms_cost : 0;
                    }
                    $service_cost   = round($call_cost+ $data_cost + $sms_cost,2);
                    $base_cost      = round($base_call_cost+ $base_data_cost + $base_sms_cost,2);
                    $reseller_cost  = round($reseller_call_cost+ $reseller_data_cost + $reseller_sms_cost,2);
                    $usageupdate    = DB::table('user_plans')
                                        ->whereId($activeplanid)
                                        ->update(['data_usage'=>$data_usage,'call_usage'=>$call_usage,'sms_count'=>$sms_count,'service_total'=>$service_cost,'data_cost'=>$data_cost,'call_cost'=>$call_cost,'total_calls'=>$totalcalls,'sms_cost'=>$sms_cost,'base_call_cost'=>$base_call_cost,'base_data_cost'=>$base_data_cost,'base_sms_cost'=>$base_sms_cost,'base_total'=>$base_cost,'reseller_call_cost'=>$reseller_call_cost,'reseller_data_cost'=>$reseller_data_cost,'reseller_sms_cost'=>$reseller_sms_cost,'reseller_total'=>$reseller_cost]);
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
