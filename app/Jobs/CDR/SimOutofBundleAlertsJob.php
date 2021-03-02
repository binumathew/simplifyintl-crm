<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB;
use App\Jobs\Alerts\OutofBundleAlertsJob;
class SimOutofBundleAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $list = DB::table('user_plans as up')
                        ->select('up.user_id','up.service_total','up.outofbundle_notify','up.id as planid','ud.bill_limit')
                        ->join('user_data as ud', 'ud.user_id', '=', 'up.user_id')                    
                        ->where(['up.plan_type' => 'sim','up.status' => 1])
                        ->where('up.service_total', '>', 0)
                        ->where('up.user_id',$this->user_id)
                        ->first();
        if($list){

            $bill_limit = ($list->bill_limit != 0 ) ? $list->bill_limit : config('general.settings.bill_limit');

            $notifyusers = (object)[
                'user_id'=> $list->user_id,
                'limit'=>  $bill_limit,
                'planid' => $list->planid
            ];

            if($list->outofbundle_notify < 4){                
                $calperc = ceil(($list->service_total / $bill_limit) * 100);

                if($calperc >= 90 && $list->outofbundle_notify < 4){
                    $calperc = ($calperc > 100)? 100:$calperc;
                    $notifyusers->usage   = $calperc;
                    $notifyusers->category = 4;

                }elseif($calperc >= 50 && $list->outofbundle_notify < 3){
                    $notifyusers->usage    = $calperc;
                    $notifyusers->category = 3;

                }elseif($calperc >= 25 && $list->outofbundle_notify < 2){

                    $notifyusers->usage   = $calperc;
                    $notifyusers->category = 2;
                }elseif($list->outofbundle_notify < 1){

                    $notifyusers->usage   = 1;
                    $notifyusers->category = 1;
                }                
            }
            
            if(isset($notifyusers->usage)){
                OutofBundleAlertsJob::dispatch($notifyusers); 
            }
        }
    }
}
