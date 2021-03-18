<?php

namespace App\Jobs\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use SwitchHelper;
use Log;
use NotificationHelper;
use UserHelper;

use App\Models\User;
use App\Models\AutoPlan;
use App\Models\UserPlan;
use App\Models\Account;
use App\Models\NotificationLog;
class SwitchSubscriptionJob implements ShouldQueue
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
        $this->user_id     = $user_id;
        $this->autoplan_id = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user           = User::find($this->user_id);
        $autoplan       = AutoPlan::find($this->autoplan_id);

        $i_billplan     = $autoplan->switch_billing_plan;

        if($autoplan->plan_type === 'sim'){
            $in_call_limit  = $autoplan->plan->in_call_limit;
        }

        if($i_billplan != 0 && !is_null($i_billplan)){

            $buyprice       = '0.01';
            
            $xml_data       = SwitchHelper::update_account_xml($user->i_account,2,$i_billplan);
            $plan_temp      = SwitchHelper::call_switch_api($xml_data);

            $method     = 'accountAddFunds';
            $credit_xml = SwitchHelper::switch_account_bal_xml($method, $user->i_account, $buyprice, $user->country->currency);
            $temp       = SwitchHelper::call_switch_api($credit_xml);

            if (array_key_exists("fault", $temp)){
                Log::error('accountAddFunds',[
                    'error' => json_encode($temp),
                    'i_account' => $user->i_account,
                    'user_id'=>$user->id
                ]);
                $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                $notifydata =['user_id'=>$user->id,'message' => 'Auto Subscription Switch AddCredit Failed','description'=>'sub id:'.$autoplan->id.', msg:'.json_encode($temp),'status'=>'0','payload'=>$payload];
                NotificationLog::insertGetId($notifydata);
            }

            if (array_key_exists("fault", $plan_temp)) {
                Log::error('switch_update_plan',[
                    'error' => json_encode($temp),
                    'i_account' =>   $user->i_account,
                    'user_id'=>$user->id
                ]);
                Account::where('user_id',$user->id)->limit(1)->update(['balance_minutes'=>0]);

                $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                $notifydata =['user_id'=>$user->id,'message' => 'Auto Subscription Switch Plan Update Failed','description'=>'sub id:'.$autoplan->id.', msg:'.json_encode($temp),'status'=>'0','payload'=>$payload];
                NotificationLog::insertGetId($notifydata);
            }else{
                Account::where('user_id',$user->id)->limit(1)->update(['balance_minutes'=>$in_call_limit]);
            }
            $xml_data         = SwitchHelper::update_account_xml($user->i_account,0);
            $revert_plan_temp = SwitchHelper::call_switch_api($xml_data);

            if (array_key_exists("fault", $revert_plan_temp)){
                Log::error('switchplanrevert',[
                    'error' => json_encode($temp),
                    'i_account' => $user->i_account,
                    'user_id'=>$user->id
                ]);
                $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                $notifydata =['user_id'=>$user->id,'message' => 'Auto Subscription Switch plan revert Failed','description'=>'sub id:'.$autoplan->id.', msg:'.json_encode($temp),'status'=>'0','payload'=>$payload];
                NotificationLog::insertGetId($notifydata);
            }
        }
    }
}
