<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB;
use Carbon;
use App\Jobs\Alerts\UsageAlertsJob;
class SimUsageAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->data)){

            $userIds = array_column($this->data, 'id');
            $getlist   = DB::table('user_plans as up')
                        ->select('up.data_usage','up.user_id','tp.data_limit','tp.provider','prorata','up.created_at','up.usage_notify','up.id as planid')
                        ->join('tbl_plans as tp','tp.id','=','up.plan_id')
                        ->where('up.status',1)
                        ->whereIn('up.user_id',$userIds)
                        ->where('up.plan_type','sim')
                        ->groupBy('user_id')
                        ->get()->keyBy('user_id');

            foreach ($this->data as $key => $user) {
                $list = isset($getlist[$user->id]) ? $getlist[$user->id] : false;
                if($list){
                    if($list->provider != 'EE'){
                        if($list->usage_notify < 3){ // number of times alert send

                            $data_limit   = $list->data_limit;
                            if($data_limit > 0 && is_numeric($data_limit)){
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
                                
                                $notifyusers = (object)[
                                    'user_id'=> $list->user_id,
                                    'limit'=>  $usagelimit,
                                    'planid' => $planid
                                ];

                                $togb    = round($list->data_usage / pow(1024, 3),4);
                                $calperc = ceil(($togb / $usagelimit) * 100);
                                if($calperc >= 90 && $list->usage_notify < 3){
                                    $calperc = ($calperc > 100)? 100:$calperc;
                                    $notifyusers->usage   = $calperc;
                                    $notifyusers->category = 3;
                                
                                }elseif($calperc >= 80 && $list->usage_notify < 2){
                                    $notifyusers->usage   = $calperc;
                                    $notifyusers->category = 2;

                                }elseif($calperc >= 70 && $list->usage_notify < 1){
                                    $notifyusers->usage   = $calperc;
                                    $notifyusers->category = 1;

                                }
                                if(isset($notifyusers->usage)){
                                    UsageAlertsJob::dispatch($notifyusers); 
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
