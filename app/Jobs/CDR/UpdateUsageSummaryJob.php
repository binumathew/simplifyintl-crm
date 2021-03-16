<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use DB;

use App\Models\User;
use App\Models\TblPlan;
use App\Models\UserPlan;

use App\Jobs\CDR\SimUsageAlertsJob;
use App\Jobs\CDR\SimOutofBundleAlertsJob;

class UpdateUsageSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $user_plan_id;
    protected $tbl_plan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$user_plan_id,$tbl_plan_id)
    {
        $this->user_id      = $user_id;
        $this->user_plan_id = $user_plan_id;
        $this->tbl_plan_id  = $tbl_plan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user           = User::whereId($this->user_id)->first();
        $plans          = TblPlan::find($this->tbl_plan_id);
        $userplan       = UserPlan::whereId($this->user_plan_id)->first();

        $serviceType    = $plans->network->service_type;
        $plandate       = $userplan->created_at;

        switch ($serviceType) {
            case 1:
                $startdate  = Carbon::parse($plandate)->format('Y-m-d');
                $enddate    = Carbon::parse($plandate)->addDays($plans->period)->format('Y-m-d');
                break;
            case 2:
                $startdate  = Carbon::parse($plandate)->startOfMonth()->format('Y-m-d');
                $enddate    = Carbon::parse($startdate)->endOfMonth()->format('Y-m-d');
                break;
            default:
                $startdate      = Carbon::parse($plandate)->startOfMonth()->format('Y-m-d');
                $enddate    = Carbon::parse($startdate)->endOfMonth()->format('Y-m-d');
                break;
        }
        $calllogs   = DB::table('user_calls')
                        ->select(DB::raw("SUM(duration) as duration"),DB::raw("SUM(cost) as cost"),DB::raw("SUM(base_cost) as base_cost"),DB::raw("SUM(reseller_cost) as reseller_cost"),DB::raw("COUNT(user_id) as totalcalls"))                                
                        ->whereDate('connect_date', '>=', $startdate)
                        ->whereDate('connect_date', '<=', $enddate)
                        ->where('user_id',$user->id)  
                        ->where('history_from', 2)
                        ->whereIn('service_type',[1,3])
                        ->first();
        
        $datalogs   = DB::table('usage_history')
                        ->select(DB::raw("SUM(duration) as volume"),DB::raw("SUM(amount) as cost"),DB::raw("SUM(base_amount) as base_amount"),DB::raw("SUM(reseller_amount) as reseller_amount"))
                        ->where('service_type', 'DATA')
                        ->where('user_id',$user->id)
                        ->whereDate('date', '>=', $startdate)
                        ->whereDate('date', '<=', $enddate)
                        ->first();

        $smslogs    = DB::table('usage_history')
                        ->select(DB::raw("SUM(amount) as cost"),DB::raw("SUM(duration) as totalsms"),DB::raw("SUM(base_amount) as base_amount"),DB::raw("SUM(reseller_amount) as reseller_amount"))
                        ->whereDate('date', '>=', $startdate)
                        ->whereDate('date', '<=', $enddate)
                        ->where('user_id',$user->id)
                        ->where('service_type','SMS_MO')
                        ->first();

        if($calllogs){
            $call_usage          =($calllogs->duration != 0) ? $calllogs->duration : 0;
            $totalcalls          =($calllogs->totalcalls != 0) ? $calllogs->totalcalls : 0;
            $call_cost          = ($calllogs->cost != 0) ? $calllogs->cost : 0;
            $base_call_cost     = ($calllogs->base_cost != 0) ? $calllogs->base_cost : 0;
            $reseller_call_cost = ($calllogs->reseller_cost != 0) ? $calllogs->reseller_cost : 0;
        }
        if($datalogs){
            $data_usage          = ($datalogs->volume != 0) ? $datalogs->volume : 0;
            $data_cost           = ($datalogs->cost != 0) ? $datalogs->cost : 0;
            $base_data_cost      = ($datalogs->base_amount != 0) ? $datalogs->base_amount : 0;
            $reseller_data_cost  = ($datalogs->reseller_amount != 0) ? $datalogs->reseller_amount : 0;
        }
        if($smslogs){
            $sms_count              = ($smslogs->totalsms != 0) ? $smslogs->totalsms : 0;
            $sms_cost               = ($smslogs->cost != 0) ? $smslogs->cost : 0;
            $base_sms_cost          = ($smslogs->base_amount != 0) ? $smslogs->base_amount : 0;
            $reseller_sms_cost      = ($smslogs->reseller_amount != 0) ? $smslogs->reseller_amount : 0;
        }
        $service_cost   = round($call_cost+ $data_cost + $sms_cost,2);
        $base_cost      = round($base_call_cost+ $base_data_cost + $base_sms_cost,2);
        $reseller_cost  = round($reseller_call_cost+ $reseller_data_cost + $reseller_sms_cost,2);

        $usageupdate    = DB::table('user_plans')
                                ->whereId($this->user_plan_id)
                                ->update(['data_usage'=>$data_usage,'call_usage'=>$call_usage,'sms_count'=>$sms_count,'service_total'=>$service_cost,'data_cost'=>$data_cost,'call_cost'=>$call_cost,'total_calls'=>$totalcalls,'sms_cost'=>$sms_cost,'base_call_cost'=>$base_call_cost,'base_data_cost'=>$base_data_cost,'base_sms_cost'=>$base_sms_cost,'base_total'=>$base_cost,'reseller_call_cost'=>$reseller_call_cost,'reseller_data_cost'=>$reseller_data_cost,'reseller_sms_cost'=>$reseller_sms_cost,'reseller_total'=>$reseller_cost]);
        // SimUsageAlertsJob::dispatch($user->id);
        // SimOutofBundleAlertsJob::dispatch($user->id);
    }
}
