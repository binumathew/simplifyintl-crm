<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use \App\Models\StaffCommission;
use \App\Models\StaffCommissionPayment;
use Illuminate\Console\Scheduling\Schedule;
use \App\Models\StaffPortCommission;
use \App\Models\StaffPaycreditCommission;

class CronStaffCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generating staff commission on monthly basis';

    /**
     * The schedule instance.
     *
     * @var \Illuminate\Console\Scheduling\Schedule
     */
    protected $schedule;

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
            $start_time = microtime(true);
            $firstday   = Carbon::now()->startOfMonth()->subMonth()->toDateString();
            $lastday    = Carbon::now()->subMonth()->endOfMonth()->toDateString();
            $getdata    = DB::table('tbl_sim_request as sr')
                          ->select('sl.stock_id','ad.id as staffid','ad.role')
                          ->join('tbl_sim_list as sl', 'sl.request_id', '=', 'sr.id')
                          ->join('admins as ad','ad.promocode','=','sr.promocode')                          
                          ->whereDate('sr.created_at','>=', $firstday)
                          ->whereDate('sr.created_at','<=', $lastday)
                          ->whereNotNull('sr.promocode')
                          ->orderBy('sr.created_at','asc')->get();
 
            $grouped    = [];             
            foreach ($getdata as $key => $value) {
                if(!isset($grouped[$value->staffid]))
                    $grouped[$value->staffid] = [];
                array_push($grouped[$value->staffid],  $value);
            }

            foreach ($grouped as $key =>$details) {
                $target = 1;
                $check  = true;
                foreach ($details as $comm) {
                    $getcommission = StaffCommission::where('role_id',$comm->role)
                                    ->where(function ($query) use ($target) {
                                        $query->where('target_from', '<=', $target);
                                        $query->where('target_to', '>=', $target);
                                    })->first();

                    if(!isset($getcommission->commission)){
                        if($check){
                            $getrange = DB::table('staff_commission')->where('role_id',$comm->role)
                                        ->where('target_from',$target)->first();
                            if( isset($getrange) ){
                                if($getrange->target_to == 0){
                                  $commission =  $getrange->commission; 
                                  $check      =  false;
                                }
                            }else{
                               $commission  =  0; 
                                $check      =  false; 
                            }
                        }
                    }else{
                        $commission    = $getcommission->commission;
                    }

                    if($commission != 0){
                      $paymentDate                  = $lastday;
                      $commArray['stock_id']        = $comm->stock_id;
                      $commArray['comm_staff']      = $comm->staffid;
                      $commArray['payment_date']    = $paymentDate;
                      $commArray['pay_amount']      = $commission;
                      $commArray['comm_rate']       = $commission;
                      $commArray['comm_for']        = 1; // General commission
                      StaffCommissionPayment::firstOrCreate(['stock_id' => $comm->stock_id,'comm_staff' => $comm->staffid,'comm_for' => 1], $commArray);
                    }
                $target++;
                }
            }
                     //porting commission
            $portdata   = DB::table('tbl_porting as tp')
                          ->select('stock_id','ad.id as staffid','ad.role')
                          ->join('admins as ad','ad.promocode','=','tp.promocode')
                          ->join('tbl_roles as r', 'ad.role', '=', 'r.id')
                          ->whereNotNull('tp.promocode')
                          ->where(function($q) {
                                $q->where('r.short_code', 'RI_STAFF')
                                    ->orWhere('r.short_code', 'AVOO_STAFF');
                            })
                          ->WhereBetween('tp.created_at', [$firstday, $lastday])
                          ->Where('tp.status','!=',5) //exclude cancelled requests
                          ->orderBy('tp.created_at','asc')->get();
            $ported    = [];             
            foreach ($portdata as $key => $value) {
                if(!isset($ported[$value->staffid]))
                    $ported[$value->staffid] = [];
                array_push($ported[$value->staffid],  $value);
            }

            foreach ($ported as $key =>$details) {
                $target = 1;
                $check  = true;

                foreach ($details as $comm) {
                    $getcommission = StaffPortCommission::where('role_id',$comm->role)
                                    ->where(function ($query) use ($target) {
                                        $query->where('target_from', '<=', $target);
                                        $query->where('target_to', '>=', $target);
                                    })->first();

                    if(!isset($getcommission->commission)){
                        if($check){
                            $getrange = DB::table('staff_porting_commission')
                                        ->where('role_id',$comm->role)
                                        ->where('target_from',$target)->first();
                            if( isset($getrange) ){
                                if($getrange->target_to == 0 || is_null($getrange->target_to)){
                                  $commission =  $getrange->commission; 
                                  $check      =  false;
                                }
                            }else{
                               $commission  =  0; 
                               $check       =  true; 
                            }
                        }
                    }else{
                        $commission    = $getcommission->commission;
                    }

                    if($commission != 0){
                        $paymentDate                  = Carbon::now()->format('Y-m-d');
                        $commArray['stock_id']        = $comm->stock_id;
                        $commArray['comm_staff']      = $comm->staffid;
                        $commArray['payment_date']    = $paymentDate;
                        $commArray['pay_amount']      = $commission;
                        $commArray['comm_rate']       = $commission;
                        $commArray['comm_for']        = 2; // porting commission
                        StaffCommissionPayment::firstOrCreate(['stock_id' => $comm->stock_id,'comm_staff' => $comm->staffid,'comm_for' => 2], $commArray);
                    }
                    $target++;
                }
            }

            //pay credit commission
            $paydata    = DB::table('user_payments as up')
                          ->select(DB::raw('sum(up.amount) as payamount'),'ad.id as staffid','ad.role')
                          ->join('admins as ad','ad.promocode','=','up.promocode')
                          ->join('tbl_roles as r', 'ad.role', '=', 'r.id')
                          ->whereNotNull('up.promocode')
                          ->where(function($q) {
                                $q->where('r.short_code', 'RI_STAFF')
                                ->orWhere('r.short_code', 'AVOO_STAFF');
                            })
                          ->WhereBetween('up.created_at', [$firstday, $lastday])
                          ->Where('up.status',1) //include only success details
                          ->groupBy('up.promocode')->get();

            foreach ($paydata as $key =>$details) {
                $totalpay       = 0;
                $totalpay       = round($details->payamount);
                $getcommission  = StaffPaycreditCommission::where('role_id',$details->role)
                                    ->where(function ($query) use ($totalpay) {
                                        $query->where('target_from', '<=', $totalpay);
                                        $query->where('target_to', '>=', $totalpay);
                                    })->first();
                if(!isset($getcommission->commission)){
                    $getrange = DB::table('staff_paycredit_commission')
                                        ->where('role_id',$details->role)
                                        ->where('target_from','<=',$totalpay)->first();
                            if( isset($getrange) ){
                                if($getrange->target_to == 0 || is_null($getrange->target_to)){
                                  $commission =  $getrange->commission; 
                                }
                            }else{
                               $commission  =  0; 
                            }

                    }else{
                        $commission    = $getcommission->commission;
                    }
                if($commission != 0){
                      $paymentDate                  = Carbon::now()->format('Y-m-d');
                      $commArray['stock_id']        = 0;
                      $commArray['comm_staff']      = $details->staffid;
                      $commArray['payment_date']    = $paymentDate;
                      $commArray['pay_amount']      = $commission;
                      $commArray['comm_rate']       = $commission;
                      $commArray['comm_for']        = 3; // paycredit commission
                      StaffCommissionPayment::firstOrCreate(['comm_staff' => $details->staffid,'comm_for' => 3], $commArray);
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

            ScheduledTask::where('command', $this->signature)
                ->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}

