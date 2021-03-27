<?php

namespace App\Jobs\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\AutoPlan;
use App\Models\User;
use App\Models\AvooSimLog;
use App\Models\NotificationLog;

class SimSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;

    protected $autoplan_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$autoplan_id)
    {
        $this->user_id      = $user_id;
        $this->autoplan_id  = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user     = User::find($this->user_id);
        $autoplan = AutoPlan::whereId($this->autoplan_id)->first();
        if(!empty($autoplan)){
            if(in_array($autoplan->plan->provider,['O2','EE_O2','VUK'])){
                $response = json_decode('{"orderCode":"'.$autoplan->plan->provider.'customorder","resultType":"Ok","resultCode":"0"}');
            }
            if($response->resultType == 'Ok' && $response->resultCode == 0){                                   
                $log_data = array(
                    'user_id' => $user->id,
                    'stock_id' => $user->stock_id,
                    'msisdn' => $user->msisdn->phone_number,
                    'category' => 'RECURRING',
                    'plan_id' => $autoplan->plan_id,
                    'value' => $autoplan->plan->buy_price,
                    'staff_id' => '0',
                    'reference_id' => $response->orderCode,
                    'created_at'=>'2020-11-01 01:30:10'
                );
                AvooSimLog::insert($log_data);
            }else{
                $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                $notifydata = ['user_id'=>$user->id,'message' => 'Auto Subscription '.$autoplan->plan->provider.' Subscription Renewal Error','description'=>'Sub ID:'.$autoplan->id.', msg:'.json_encode($response), 'request'=>json_encode($response), 'status'=>'0','payload'=>$payload];
                NotificationLog::insertGetId($notifydata);
            }
        }
    }
}
