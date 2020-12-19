<?php

namespace App\Jobs\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use Log;
use Utils;
use DB;
use Helper;
use GlobalSim;
use SwitchHelper;
use Notification;

use App\Models\AutoPlan;
use App\Models\User;
use App\Models\PaymentCommission;
use App\Models\NotificationLog;
use App\Models\Account;
use App\Models\UserPlan;
use App\Models\UserPayment;

use App\Jobs\Subscription\SubscriptionFailedNotificationJob;

class AutoPlanSubscriptionJob //implements ShouldQueue
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
        $user_id    = $this->user_id;
        try {
            $autoplan   = AutoPlan::where('user_id',$user_id)->get();
            $autoplan->each(function ($item, $key) {

                $user    = User::whereId($item->user_id)->where('status',1)->first();
                $blocked = Helper::check_fraudster($item->user_id);

                if($user && !$blocked){
                    $next_renewal       = Carbon::now()->addDays(30)->format('Y-m-d');

                    $childlist          = explode(',', $item->user_list); 
                    $childcount         = count($childlist);
                    $net_out_charge     = DB::table('user_plans')->whereIn('user_id',$childlist)
                                            ->where('out_pay_status', 0)->sum('service_total');
                    $outcharge          = Helper::vataddCalculation($net_out_charge,$user->country->tax);
                    $vat_out_charge     = $outcharge->tax_amount;
                    $total_out_charge   = $outcharge->total_amount;
                    $billamount         = $item->amount + ($net_out_charge);
                    $billtax            = $item->tax + ($vat_out_charge);
                    $billtotal          = $item->total_amount + ($total_out_charge);

                    if($item->plan->provider == 'E_SIM'){
                        if($item->plan->prepaid_credit != 0){
                            $esim_credit_amount = ($item->plan->prepaid_credit * $childcount);
                            $esim_credit        = Helper::vataddCalculation($esim_credit_amount,$user->country->tax);
                            $esim_credit_tax    = $esim_credit->tax_amount;
                            $esim_credit_total  = $esim_credit->total_amount;
                            $billamount = $billamount + $esim_credit_amount;
                            $billtax    = $billtax + $esim_credit_tax;
                            $billtotal  = $billtotal + $esim_credit_total;
                        }
                    }

                    $billamount     = round($billamount,2);
                    $billtax        = round($billtax,2);
                    $billtotal      = round($billtotal,2);

                    if($item->gateway == 'Paypal'){
                        $result = Helper::paypalReferencePayment($billtotal,$item->transaction_id,$user->country->currency,$user->userDetail->i_account);

                    }else if($item->gateway == 'Braintree'){
                        $result = Helper::braintreeReferencePayment($billtotal,$item->transaction_id);

                    }else if($item->gateway == 'Stripe'){
                        $result = Helper::stripeReferencePayment($billtotal,$item->transaction_id,$item->user_id);
                    }

                    if($result && $result['status'] == 'SUCCESS'){
                        $txn_id                     = $result['TRANSACTIONID'];
                        $payment['transaction_id']  = $txn_id;                        
                        $payment['payment_method']  = $item->gateway;
                        $payment['payment_for']     = 'Monthly Subscription';  
                        $payment['user_id']         = $item->user_id;            
                        $payment['amount']          = $billamount;      
                        $payment['tax_amount']      = $billtax;
                        $payment['out_charge']      = $net_out_charge;
                        $payment['total_amount']    = $billtotal;
                        $payment['currency']        = $user->country->currency;
                        $payment['category']        = $item->plan_type;
                        $payment['card_type']       = $item->card_type;
                        $payment['description']     = 'Monthly Subscription (users: '.$item->user_list.')';
                        $payment['status']          = 1;  

                        if( $item->plan_type === 'sim' ){
                            if($item->plan->provider != 'EE'){
                                $next_renewal = Carbon::now()->addMonth()->firstOfMonth()->format('Y-m-d');
                            }
                        }
                        $autoplanData['next_renewal']   = $next_renewal;
                        $autoplanData['transaction_id'] = $txn_id;
                        if($item->gateway == 'Stripe'){
                            $autoplanData['transaction_id'] = $result['txn_card_id'];
                        }
                        AutoPlan::where('id', $item->id)->update($autoplanData);
                        $paymentid = UserPayment::insertGetId($payment);  
                        DB::table('user_plans')->whereIn('user_id',$childlist)
                                    ->where('out_pay_status', 0)
                                    ->update(['out_pay_status'=>1]); 

                        foreach($childlist as $child_id){
                            $addcredit = $this->simCredit($child_id,$item->id);
                            if($addcredit){
                                $this->modifyUserBalance($child_id,$item->plan->prepaid_credit);
                            }
                            $plansubcribe = $this->planSubscribe($child_id,$item->id,$paymentid);
                            if($plansubcribe === false){
                                continue;
                            }
                        }
                        $promocode = $item->sim[0]->sim_request->promocode; 
                        $this->delearRevenue($promocode,$childcount,$item->id);

                    }else if($result['status'] === 'SUCCESSWITHWARNING') {

                        $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$item->id]);
                        $notifydata = ['user_id'=>$item->user_id,'message' => 'Auto Subscription Payment Warning','description'=>'Sub ID:'.$item->id.', card:'.$item->card_type.', msg:'.json_encode($result),'status'=>'0','payload'=>$payload];
                        $this->failedNotify($notifydata,$item->id);

                    }else{
                        $error_code = isset($result['L_ERRORCODE0'])?$result['L_ERRORCODE0']:'';
                        $s_msg = isset($result['L_SHORTMESSAGE0'])?$result['L_SHORTMESSAGE0']:'';
                        $l_msg = isset($result['L_LONGMESSAGE0'])?$result['L_LONGMESSAGE0']:'';
                        $pay_error = json_encode(['code'=>$error_code,'smsg'=>$s_msg,'lmsg'=>$l_msg]);

                        $notifydata = ['user_id'=>$item->user_id,'message' => 'Auto Subscription Payment Failed','description'=>'Sub ID:'.$item->id.', card:'.$item->card_type.', msg:'.$pay_error,'status'=>'0'];

                        $notify = $this->failedNotify($notifydata,$item->id,1); 

                        $payment = ['transaction_id' => '', 'total_amount' => $item->total_amount, 'payment_method' => $item->gateway, 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $item->user_id, 'amount' => $item->amount, 'tax_amount' => $item->tax, 'description' => 'Subscription Error :'.$l_msg.', ntfy_id:'.$notify, 'status' => '0', 'buy_price' => '0'];

                        $paymentid = UserPayment::insertGetId($payment); 

                        $this->modifyUserPlans($item->user_id,$item->plan_type,0);

                        $this->modifyUserMinutes($item->user_id,0);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('AutoPlanSubscriptionJob',[
                'user_id' => $user_id,
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
        
    }
    private function planSubscribe($user_id,$autoplan_id,$paymentid){
        try {
            $user     = User::find($user_id);
            $autoplan = AutoPlan::find($autoplan_id);
            if($autoplan->plan_type === 'sim'){

                $i_billplan     = $autoplan->plan->switch_billing_plan;
                $in_call_limit  = $autoplan->plan->in_call_limit;

                if($autoplan->plan->provider == 'E_SIM'){
                    if($autoplan->plan->sim_billing_plan != 0){
                        $esim_unsub = false;
                        try {
                            $unsubscribe = new \StdClass;
                            $unsubscribe->bundle_id = $autoplan->plan->sim_billing_plan;
                            $unsubscribe->msisdn    = $user->msisdn->phone_number;
                            $unsubscribe->action    = 'unsubscribe';
                            $sim_api_data           = $unsubscribe;
                            $bundleunsubscrib       = GlobalSim::BundleUnSubscribe($sim_api_data);
                            if($bundleunsubscrib == false || $bundlesubscrib['@attributes']['status'] == 'fail'){
                                Log::error('ESIMUNSUBSCRIPTION',[
                                    'user_id' => $user->id,
                                    'error' =>   $bundleunsubscrib
                                ]);
                                $response = json_decode('{"orderCode":"e_simcustomorder","resultType":"false","resultCode":"1","response":'.json_encode($bundleunsubscrib).'}'); 
                            }else{
                                $esim_unsub = true; 
                            }
                        } catch (\Exception $e) {
                            Log::error('ESIMUNSUBSCRIPTION',[
                                'user_id' => $user->id,
                                'error' =>   $e->getMessage()
                            ]);
                        }
                        if($esim_unsub){
                            try {
                                $subscribe = new \StdClass;
                                $subscribe->bundle_id = $autoplan->plan->sim_billing_plan;
                                $subscribe->msisdn    = $user->msisdn->phone_number;
                                $subscribe->date      = Carbon::now()->format('Y-m-d H:i:s');
                                $subscribe->actfirstuse     = $user->userDetail->activate_onfirstuse;
                                $subscribe->sendsms         = $user->userDetail->send_sms;
                                $subscribe->takepayment     = $user->userDetail->take_payment;
                                $sim_api_data               = $subscribe;
                                $bundlesubscrib = GlobalSim::BundleSubscribe($sim_api_data);

                                if($bundlesubscrib == false || $bundlesubscrib['@attributes']['status'] == 'fail'){
                                    Log::error('ESIMSUBSCRIPTION',[
                                        'user_id' => $user->id,
                                        'error' =>   $bundlesubscrib
                                    ]);
                                    $response = json_decode('{"orderCode":"e_simcustomorder","resultType":"false","resultCode":"1","response":'.json_encode($bundlesubscrib).'}'); 
                                }
                                $response = json_decode('{"orderCode":"e_simcustomorder","resultType":"Ok","resultCode":"0"}');
                            } catch (\Exception $e) {
                                Log::error('ESIMSUBSCRIPTION',[
                                    'user_id' => $user->id,
                                    'error' =>   $e->getMessage()
                                ]);
                            }
                        }
                    }
                }else if( $autoplan->plan->provider == 'EE' ){
                    $customer_id        = Helper::get_option('mvno_customer_id');
                    $mvno_key           = Helper::get_option('bundle_mvno_key');
                    $subscription_id    = $user->userDetail->sim_subscription_id;
                    $end_point          = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim
                    $sim_api_data       = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$autoplan->plan->sim_billing_plan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$user->msisdn->phone_number])]),"Channel"=>"Web"];
                    $response           = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                }else{
                    $response = json_decode('{"orderCode":"O2customorder","resultType":"Ok","resultCode":"0"}'); 
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
                        'reference_id' => $response->orderCode
                    );
                    DB::table('avoo_sim_log')->insert($log_data);

                    $pl_cnt         = UserPlan::where(['user_id'=>$user->id,'plan_type'=>'sim'])->count();
                    $i_billplan     = ($pl_cnt >= 2)? $autoplan->plan->switch_billing_plan:'203';
                    $in_call_limit  = ($pl_cnt >= 2)? $autoplan->plan->in_call_limit:'1000';

                    if($autoplan->created_at > '2020-06-02' && in_array($autoplan->plan_id, [1,15])){
                        $i_billplan     = $autoplan->plan->switch_billing_plan;
                        $in_call_limit  = $autoplan->plan->in_call_limit;
                    }
                }else{
                    $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                    $notifydata = ['user_id'=>$user->id,'message' => 'Auto Subscription '.$autoplan->plan->provider.' Subscription Renewal Error','description'=>'Sub ID:'.$autoplan->id.', msg:'.json_encode($sim_api_data), 'request'=>json_encode($response), 'status'=>'0','payload'=>$payload];
                    $this->failedNotify($notifydata,$autoplan->id);
                    //return false;
                }
            }else if($autoplan->plan_type === 'switch'){
                $i_billplan    = $autoplan->plan->switch_billing_plan;
                $in_call_limit = $autoplan->plan->minutes; 
            }else if( $autoplan->plan_type === 'bridge' ){
                $i_billplan    = $autoplan->plan->switch_billing_plan;
                $in_call_limit = $autoplan->plan->minutes; 
            }
            if($i_billplan != 0 || !is_null($i_billplan)){
                try {
                    $defult_plan = DB::table('switch_template')->where('id', $user->switch_id)->value('billing_plan');
                    $xml_data = SwitchHelper::switch_update_plan_xml($user->userDetail->i_account, $defult_plan);
                    $temp     = SwitchHelper::call_switch_api($xml_data);

                    $xml_data = SwitchHelper::switch_update_plan_xml($user->userDetail->i_account, $i_billplan);
                    $temp     = SwitchHelper::call_switch_api($xml_data);

                    if (array_key_exists("fault", $temp)) {
                        Log::error('switch_update_plan',[
                            'error' => json_encode($temp),
                            'i_account' =>   $user->userDetail->i_account
                        ]);
                        $this->modifyUserMinutes($user->id,0);

                        $this->modifyUserPlans($user->id,$autoplan->plan_type,0);
 
                        $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                        $notifydata =['user_id'=>$user->id,'message' => 'Auto Subscription Switch Plan Update Failed','description'=>'sub id:'.$autoplan->id.', msg:'.json_encode($temp),'status'=>'0'];

                        $this->failedNotify($notifydata,$autoplan->id);

                    }else{
                        $this->modifyUserMinutes($user->id,$in_call_limit);

                        $this->modifyUserPlans($user->id,$autoplan->plan_type,0);

                        $this->createUserPlans($user->id,$autoplan->plan_id,$autoplan->plan_type,$paymentid);
                        
                    }
                } catch (\Exception $e) {
                    Log::error('switch_update_plan',[
                        'error' => $e->getMessage(),
                        'i_account' =>   $user->userDetail->i_account
                    ]);
                }
                try {
                    $method = 'accountAddFunds';
                    $credit_xml = SwitchHelper::switch_account_bal_xml($method, $user->userDetail->i_account, $autoplan->plan->buy_price, $user->country->currency); 
                    $temp =  SwitchHelper::call_switch_api($credit_xml);

                    if (array_key_exists("fault", $temp)) {
                        $payload = json_encode(['type'=>'plan_recurring','auto_plan'=>$autoplan->id]);
                        $notifydata =['user_id'=>$user->id,'message' => 'Auto Subscription Switch AddCredit Failed','description'=>'sub id:'.$autoplan->id.', msg:'.json_encode($temp),'status'=>'0'];
                        $this->failedNotify($notifydata,$autoplan->id);
                    } 
                } catch (\Exception $e) {
                   Log::error('accountAddFunds',[
                        'error' => $e->getMessage(),
                        'i_account' => $user->userDetail->i_account
                    ]);
                }
            }else{
                $this->modifyUserPlans($user->id,$autoplan->plan_type,0);

                $this->createUserPlans($user->id,$autoplan->plan_id,$autoplan->plan_type,$paymentid); 
            }
        } catch (\Exception $e) {
            Log::error('plansubscriprecurring',[
                'error' => $e->getMessage(),
                'autoplan_id' =>   $autoplan_id
            ]);
           return false;
        }

    }
    private function simCredit($user_id,$autoplan_id){
        try {
            $user     = User::find($user_id);
            $autoplan = AutoPlan::find($autoplan_id);
            if($autoplan->plan->provider == 'E_SIM'){
                if($autoplan->plan->reset_prepaid_credit){
                    /* function to reset credit */
                }
                if($autoplan->plan->prepaid_credit != 0){
                    try {
                        $addcreditreq = GlobalSim::AddPrePaidCredit($user->userDetail->esim_customer,$autoplan->plan->prepaid_credit);
                        if($addcreditreq == false || $addcreditreq['@attributes']['status'] == 'fail'){
                            Log::error('AddPrePaidCredit',[
                                'esim_customer' => $user->userDetail->esim_customer,
                                'error' =>   $addcreditreq
                            ]);
                            return false;
                        }
                       return true;
                    } catch (\Exception $e) {
                        Log::error('AddPrePaidCredit',[
                            'esim_customer' => $user->userDetail->esim_customer,
                            'error' =>   $e->getMessage()
                        ]);
                        return false;
                    }
                }
            }
        } catch (\Exception $e) {
             Log::error('simCredit',[
                'error' => $e->getMessage(),
                'user_id' =>   $user_id
            ]);
            return false;
        }
    }
    private function modifyUserBalance($user_id,$amount){
        try {
            $user_balance = Account::where('user_id',$user_id)->first();
            $balance['balance_amount'] = $user_balance->balance_amount + $amount;
            Account::where('user_id', $user_id)->update($balance);
            return true;
        } catch (\Exception $e) {
            Log::error('modifyUserBalance',[
                'user_id' => $user_id,
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    private function modifyUserMinutes($user_id,$minutes){
        try {
            $balance['balance_minutes'] = $minutes;
            Account::where('user_id',$user_id)->update($balance);
            return true;
        } catch (\Exception $e) {
            Log::error('modifyUserMinutes',[
                'user_id' => $user_id,
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    private function modifyUserPlans($user_id,$plan_type,$status){
        try {
            UserPlan::where('user_id',$user_id)
            ->where('plan_type',$plan_type)
            ->update(['status'=>$status]);
            return true;
        } catch (\Exception $e) {
            Log::error('modifyUserPlans',[
                'user_id' => $user_id,
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    private function createUserPlans($user_id,$plan_id,$plan_type,$paymentid){
        try {
            UserPlan::create(['user_id'=>$user_id,'plan_id'=>$plan_id,'payment_id'=>$paymentid,'plan_type'=>$plan_type,'status'=>1]);   
            return true;
        } catch (\Exception $e) {
            Log::error('createUserPlans',[
                'user_id' => $user_id,
                'error' =>   $e->getMessage()
            ]);
            return false;
        }
    }
    private function failedNotify($data,$autoplan_id,$sms = 0){
        $notification = NotificationLog::create($data);                                 
        $obj = (object) array(
            'notify_id' => $notification->id,
            'subscripton'=> $autoplan_id,
            'sms'=> $sms,
        );  
        SubscriptionFailedNotificationJob::dispatch($obj)
            ->delay(now()->addMinutes(1)); 
        return $notification->id;  
    }
    private function delearRevenue($promocode,$childcount,$autoid){
        try {
            $curr_day    = Carbon::now()->format('Y-m-d');
            $dealerWhere = ['promocode' => $promocode, 'short_code' => 'DEALER'];      
            $dealer = DB::table('admins')->select('admins.id as dealer_id')
                        ->leftJoin('tbl_roles as r', 'role', '=', 'r.id')
                        ->where($dealerWhere)->first();
            if($dealer){                 
                $dealer_id = $dealer->dealer_id;
                $revenue = DB::table('dealer_revenue')->select('amount')
                                ->where('dealer_id',$dealer_id)
                                ->where('expiry_at','>=', $curr_day)
                                ->where('status',1)->first(); 
                if($revenue){
                    $amount = $revenue->amount;
                    //total commission for dealer
                    $revenuecomm = $amount * $childcount;                          
                    $maxend = DB::table('commission_payments')
                        ->selectRaw('MAX(payment_end) AS max_end')
                        ->where('comm_user', $dealer_id)
                        ->where('autoplan_id',$autoid)->value('max_end');

                    //to check dealer commission ended to start revenue commission    
                    if(!$maxend || Carbon::parse($maxend)->lessThanOrEqualTo($curr_day)){
                        $commArray['autoplan_id']     = $autoid;
                        $commArray['comm_user']       = $dealer_id;
                        $commArray['payment_date']    = $curr_day;
                        $commArray['payment_end']     = Carbon::tomorrow();
                        $commArray['pay_amount']      = $revenuecomm;
                        $commArray['comm_rate']       = $amount;
                        $commArray['comm_type']       = 1;
                        PaymentCommission::firstOrCreate(['autoplan_id' => $autoid,'comm_user' => $dealer_id,'payment_date' => $curr_day], $commArray);
                    }                                    
                }
            }
        } catch (\Exception $e) {
            Log::error('delearRevenue',[
                'error' => $e->getMessage(),
                'autoplan_id' =>   $autoid
            ]);
        }
    }
}
