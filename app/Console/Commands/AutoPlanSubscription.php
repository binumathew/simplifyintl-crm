<?php

namespace App\Console\Commands;

use DB;
use Carbon;
use Helper;
use SwitchHelper;
use App\Models\Account;
use App\Models\UserPlan;
use App\Models\UserPayment;
use App\Models\PaymentCommission;
// use App\Mail\PlanPayment;
use App\Models\AutoPlan;
use App\Helpers\SIMHelper;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\AutoPlanHistory;
use App\Models\User as ModalUser;
use Illuminate\Console\Command;
use App\Models\NotificationLog;
use App\Jobs\FailureNotification;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
use Illuminate\Console\Scheduling\Schedule;

class AutoPlanSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plan will subscribe every month';

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
            $curr_day = Carbon::now()->format('Y-m-d');
            $customer_id = Helper::get_option('mvno_customer_id');
            $mvno_key = Helper::get_option('bundle_mvno_key');

            $getuserid = DB::table("auto_plan")->select('user_id', 'gateway', 'transaction_id', DB::raw('sum(total_amount) as total_amount'), DB::raw('count(user_id) as total_count'), DB::raw("GROUP_CONCAT(id) as id"))
                    ->where('next_renewal', $curr_day) //'<=',
                    ->where('status', 1)->where('adv_pay', 0)
                    ->where('card_expiry', '>=', $curr_day)
                    ->groupBy('user_id','transaction_id','gateway')
                    ->orderBy('user_id', 'asc')
                    ->get(); //autoplan get userid & total amount for current date

            if($getuserid->isNotEmpty()) { //check for user exists
                foreach ($getuserid as $userdetails) {
                    $user_id = $userdetails->user_id; //taking the userid

                    $user = DB::table('users')->select('name','first_name','email','phone','i_account','currency','currency_symbol')
                                ->join('country','country_id','=','country.id')
                                ->where('users.id', $user_id)->where('users.status', '1')
                                ->first(); //taking user details from users table against the user

                    $blocked = Helper::check_fraudster($user_id); //check whether user is in fraud list
                    if(!$user || $blocked){  //if in fraud or user doen't active escape
                        continue;
                    }

                    $next_renewal = Carbon::now()->addDays(30)->format('Y-m-d');
                    $billamount     = $userdetails->total_amount;
                    $referenceid    = $userdetails->transaction_id;
                    $currency       = $user->currency;
                    $i_account      = $user->i_account; //parent user account
                    $autoplanid     = explode(',', $userdetails->id); //unique id from autoplan table of the grouped users

                    if($userdetails->gateway == 'Paypal'){
                        $api_endpoint   = 'https://api-3t.paypal.com/nvp';
                        $api_user       = Helper::get_option('paypal_nvp_username');
                        $api_password   = Helper::get_option('paypal_nvp_password');
                        $api_signature  = Helper::get_option('paypal_nvp_signature');
                        $version        = urlencode('86.0');
                        $method_name    = 'DoReferenceTransaction';
                        $payment_type   = urlencode('Sale');

                        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$billamount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

                        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);
                        $rsponse_status = strtoupper($response["ACK"]);
                    }elseif($userdetails->gateway == 'Braintree'){
                        $gateway = Helper::get_btree_gateway();

                        $result = $gateway->transaction()->sale([
                                'amount' => $billamount,
                                'paymentMethodToken' => $referenceid,
                                'options' => [
                                    'submitForSettlement' => True
                                ]
                            ]);

                        $fp = fopen('btreeautoerror.txt', 'a+');
                        fwrite($fp, $user_id.'--- auto ---'.json_encode($result).chr(10));
                        fclose($fp);
                        $respData = json_encode($result);
                        $result = json_decode($respData);

                        if($result->success){
                            $rsponse_status = 'SUCCESS';
                            $response['TRANSACTIONID'] = $result->transaction->id;
                        }else{
                            $rsponse_status = 'FAILED';
                            $response['L_ERRORCODE0'] = $result->transaction->processorResponseCode;
                            $sht_msg = $result->transaction->status.'-'.$result->transaction->processorResponseType;
                            $response['L_SHORTMESSAGE0'] = $sht_msg;
                            $response['L_LONGMESSAGE0'] = $result->transaction->processorResponseText;
                        }
                    }

                    if($rsponse_status === 'SUCCESS') {
                        $txn_id = $response['TRANSACTIONID'];
                        $payment['transaction_id']  = $txn_id;
                        $payment['payment_method']  = $userdetails->gateway;
                        $payment['payment_for']     = 'Monthly Subscription';

                        foreach($autoplanid as $autoid){
                            $plans =  AutoPlan::where('id', $autoid)->first();
                            $autoplan['transaction_id'] = $txn_id;
                            $payment['user_id']     = $user_id;
                            $payment['amount']      = $plans->amount;
                            $payment['tax_amount']  = $plans->tax;
                            $payment['total_amount'] = $plans->total_amount;
                            $payment['currency'] = $currency;
                            $payment['category'] = $plans->plan_type;
                            $payment['card_type'] = $plans->card_type;
                            $payment['description'] = 'Monthly Subscription (users: '.$plans->user_list.')';
                            $payment['status']      = '1';

                            if( $plans->plan_type === 'sim'){
                                $plandetail = $plans->getplan;
                                $i_billplan = '203'; //$plans->switch_billing_plan;
                                $in_call_limit = '1000'; // $plans->plan->in_call_limit;
                                $simbillplan   = $plans->plan->sim_billing_plan;
                                $payment['buy_price']  = $plandetail->buy_price;
                                if($plans->plan->provider != 'EE'){
                                    $next_renewal = date('Y-m-t', strtotime(Carbon::tomorrow()));
                                }
                                $buy_price = '0.01';
                            }else if( $plans->plan_type === 'switch' ){
                                $plandetail = $plans->plan;
                                $i_billplan = $plandetail->switch_billing_plan;
                                $in_call_limit = $plandetail->minutes;
                                $payment['buy_price']  = $plandetail->buy_price;
                                $buy_price = $plandetail->buy_price;
                                $next_renewal = Carbon::now()->addDays(30)->format('Y-m-d');
                            }else if( $plans->plan_type === 'bridge'){
                                $plandetail = $plans->plan;
                                $i_billplan = $plandetail->switch_billing_plan;
                                $in_call_limit = $plandetail->minutes;
                                $payment['buy_price']  = $plandetail->buy_price;
                                $buy_price = $plandetail->buy_price;
                                $next_renewal = Carbon::now()->addDays(30)->format('Y-m-d');
                            }
                            $autoplan['next_renewal'] = $next_renewal;
                            AutoPlan::where('id', $autoid)->update($autoplan);
                            $paymentid = UserPayment::insertGetId($payment);

                            $childlist = explode(',', $plans->user_list); //child list for the user

			                $dealercommcount    = count($childlist); //calculate commission for child
                            $promocode = $plans->sim[0]->sim_request->promocode;
                            $dealerWhere = ['promocode' => $promocode, 'short_code' => 'DEALER'];
                            $dealer = DB::table('admins')->select('admins.id as dealer_id')
                                        ->leftJoin('tbl_roles as r', 'role', '=', 'r.id')
                                        ->where($dealerWhere)->first();

                            if($dealer){
                                $dealer_id =  $dealer->dealer_id;
                                $revenue = DB::table('dealer_revenue')->select('amount')
                                              ->where('dealer_id',$dealer_id)
                                              ->where('expiry_at','>=', $curr_day)
                                              ->where('status',1)->first();

                                if($revenue){
                                    $amount = $revenue->amount;
                                    //total commission for dealer
                                    $revenuecomm = $amount * $dealercommcount;
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

                            foreach ($childlist as $child) {
                                $userdata = ModalUser::find($child); //child user details
                                $i_account = $userdata->i_account;

                                if($plans->plan_type === 'sim'){



                                    $msisdn = $userdata->msisdn->phone_number;

                                    if($plans->plan->provider == 'EE'){
                                        $subscription_id = $userdata->userDetail->sim_subscription_id;
                                        $end_point = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim
                                        $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$simbillplan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];

                                        $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                                    }else{
                                        $response = json_decode('{"orderCode":"O2customorder","resultType":"Ok","resultCode":"0"}');
                                    }

                                    if($response->resultType == 'Ok' && $response->resultCode == 0){
                                        $log_data = array(
                                            'user_id' => $userdata->id,
                                            'stock_id' => $userdata->stock_id,
                                            'msisdn' => $msisdn,
                                            'category' => 'RECURRING',
                                            'plan_id' => $plans->plan_id,
                                            'value' => $plandetail->buy_price,
                                            'staff_id' => '0',
                                            'reference_id' => $response->orderCode
                                        );
                                        DB::table('avoo_sim_log')->insert($log_data);
                                        $pl_cnt = UserPlan::where(['user_id'=>$child,'plan_type'=>'sim'])->count();
                                        $i_billplan = ($pl_cnt >= 2)? $plans->switch_billing_plan:'203';
                                        $in_call_limit = ($pl_cnt >= 2)? $plans->plan->in_call_limit:'1000';

                                        if($plans->created_at > '2020-06-02' && in_array($plans->plan_id, [1,15])){
                                            $i_billplan = $plans->switch_billing_plan;
                                            $in_call_limit = $plans->plan->in_call_limit;
                                        }
                                    }else{
                                        $notification = NotificationLog::create(['user_id'=>$child,'message' => 'Auto Subscription EE Subscription Renewal Error','description'=>'Sub ID:'.$autoid.', msg:'.json_encode($response), 'request'=>json_encode($sim_api_data), 'status'=>'0']);
                                        $obj = (object) array(
                                            'notify_id' => $notification->id,
                                            'subscripton'=> $autoid,
                                        );
                                        FailureNotification::dispatch($obj)
                                            ->delay(now()->addMinutes(1));
                                        continue;
                                    }
                                }
                                if($i_billplan != 0 || !is_null($i_billplan)){
                                    $defult_plan = DB::table('switch_template')->where('id', $userdata->switch_id)->value('billing_plan');
                                    $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
                                    $temp     = SwitchHelper::call_switch_api($xml_data);

	                                $method = 'accountCredit';
	                                $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, $buy_price, $currency);
                                	$temp =  SwitchHelper::call_switch_api($credit_xml);
	                                if (array_key_exists("fault", $temp)) {
	                                    $notification = NotificationLog::create(['user_id'=>$child, 'message' => 'Auto Subscription Switch AddCredit Failed','description'=>'Sub ID:'.$autoid.', msg:'.json_encode($temp),'status'=>'0']);
	                                    $obj = (object) array(
                                            'notify_id' => $notification->id,
                                            'subscripton'=> $autoid,
                                        );
                                        FailureNotification::dispatch($obj)
                                            ->delay(now()->addMinutes(1));
                                        continue;
	                                }

	                                $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $i_billplan);
	                                $temp     = SwitchHelper::call_switch_api($xml_data);

	                                if (array_key_exists("fault", $temp)) {
	                                    $notification = NotificationLog::create(['user_id'=>$child,'message' => 'Auto Subscription Switch Plan Update Failed','description'=>'sub id:'.$autoid.', msg:'.json_encode($temp),'status'=>'0']);
	                                    $obj = (object) array(
                                            'notify_id' => $notification->id,
                                            'subscripton'=> $autoid,
                                        );
                                        FailureNotification::dispatch($obj)
                                            ->delay(now()->addMinutes(1));
                                        $balance['balance_minutes'] = 0;
                                        Account::where('user_id',$child)->update($balance);
                                        $where['user_id'] = $child;
                                        $where['plan_type'] = $plans->plan_type;
                                        $update['status'] = 0;
                                        UserPlan::where($where)->update($update);
	                                }else{
	                                    $balance['balance_minutes'] = $in_call_limit;
	                                    Account::where('user_id',$child)->update($balance);
	                                    $where['user_id']       = $child;
	                                    $where['plan_type']     = $plans->plan_type;
	                                    $update['status']       = 0;
	                                    UserPlan::where($where)->update($update);
	                                    $usage['plan_id']       = $plans->plan_id;
	                                    $usage['user_id']       = $child;
	                                    $usage['payment_id']    = $paymentid;
	                                    $usage['status']        = 1;
	                                    $usage['plan_type']     = $plans->plan_type;
	                                    UserPlan::create($usage);
	                                }
                                }
                            }
                        }
                    } else if($rsponse_status === 'SUCCESSWITHWARNING') {
                        $plans =  AutoPlan::find($autoid);//update txn num
                        $notification = NotificationLog::create(['user_id'=>$plans->user_id,'message' => 'Auto Subscription Payment Warning','description'=>'Sub ID:'.$autoid.', card:'.$plans->card_type.', msg:'.json_encode($response),'status'=>'0']);
                        $obj = (object) array(
                            'notify_id' => $notification->id,
                            'subscripton'=> $autoid,
                        );
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1));
                    } else {
                        $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0']]);
                        foreach($autoplanid as $autoid){
                            $plans = AutoPlan::find($autoid);//update txn num
                            $notification = NotificationLog::create(['user_id'=>$plans->user_id,'message' => 'Auto Subscription Payment Failed','description'=>'Sub ID:'.$autoid.', card:'.$plans->card_type.', msg:'.$pay_error,'status'=>'0']);
                            $payment = ['transaction_id' => '', 'total_amount' => $plans->total_amount, 'payment_method' => 'Paypal', 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $user_id, 'amount' => $plans->amount, 'tax_amount' => $plans->tax, 'description' => 'Subscription Error :'.$response['L_LONGMESSAGE0'].', ntfy_id:'.$notification->id, 'status' => '0', 'buy_price' => '0'];
                            $paymentid = UserPayment::insertGetId($payment);

                            $obj = (object) array(
                                'notify_id' => $notification->id,
                                'subscripton'=> $autoid,
                            );
                            FailureNotification::dispatch($obj)
                                ->delay(now()->addMinutes(1));
                            $plans = AutoPlan::where('id', $autoid)->first();

                            $childlist = explode(',', $plans->user_list);
                            foreach ($childlist as $child) {
                                $where['user_id']   = $child;
                                $where['plan_type'] = $plans->plan_type;
                                UserPlan::where($where)->update(['status' => 0]);
                                $balance['balance_minutes'] = 0;
                                Account::where('user_id',$child)->update($balance);
                            }
                        }

                        $msg = 'Hi '. $user->first_name.' Our attempt to process subscription failed. Please contact '.config('settings.app_name').' support ASAP on '.json_decode(config('settings.company_details'))->company_phone.' or update card, to avoid loss of service. Thanks';

                        $account_sid = Helper::get_option('twilio_account_sid');
                        $auth_token =  Helper::get_option('twilio_auth_token');
                        $twilio_number = Helper::get_option('twilio_number');

                        $client = new Client($account_sid, $auth_token);
                        try {
                            $client->messages->create(
                                $user->phone,
                                array(
                                    'from' => $twilio_number,
                                    'body' => $msg
                                )
                            );
                        } catch (RestException $exception) {
                            // if ($exception->getCode() === 21211) {

                            // }
                        }
                    }
                }
            }

            $custom_renew = DB::table('auto_plan_custom')->where('renew_on', $curr_day)->get();
            if($custom_renew->isNotEmpty()) {
                foreach ($custom_renew as $renew) {
                    $user_id = $renew->user_id;
                    $user = DB::table('users')->select('name','first_name','email','phone',
                                'users.switch_id','i_account','currency','currency_symbol')
                                ->join('country','country_id','=','country.id')
                                ->where('users.id', $user_id)->where('users.status', '1')
                                ->first(); //taking user details from users table against the user

                    $blocked = Helper::check_fraudster($user_id); //check whether user is in fraud list
                    if(!$user || $blocked){  //if in fraud or user doen't active escape
                        continue;
                    }
                    $i_account = $user->i_account;
                    $defult_plan = DB::table('switch_template')->where('id', $user->switch_id)->value('billing_plan');
                    $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
                    $temp = SwitchHelper::call_switch_api($xml_data);

                    $currency = $user->currency;
                    $i_billplan = $renew->switch_billing_plan;
                    $in_call_limit = '1000';
                    $method = 'accountCredit';
                    $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, '0.01', $currency);
                    $temp = SwitchHelper::call_switch_api($credit_xml);
                    if (array_key_exists("fault", $temp)) {
                        $notification = NotificationLog::create(['user_id' => $user_id, 'message' => 'Auto Subscription Switch AddCredit Failed', 'description' => 'Sub ID:'. $renew->autoplan_id .', msg:'.json_encode($temp), 'status' => '0']);
                        $obj = (object) array(
                            'notify_id' => $notification->id,
                            'subscripton'=> $renew->autoplan_id,
                        );
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1));
                        continue;
                    }
                    $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $i_billplan);
                    $temp     = SwitchHelper::call_switch_api($xml_data);
                    if (array_key_exists("fault", $temp)) {
                        $notification = NotificationLog::create(['user_id' => $user_id, 'message' => 'Auto Subscription Switch Plan Update Failed', 'description' => 'sub id:'.$renew->autoplan_id.', msg:'.json_encode($temp),'status'=>'0']);
                        $obj = (object) array(
                            'notify_id' => $notification->id,
                            'subscripton'=> $renew->autoplan_id,
                        );
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1));
                    }else{
                        $custom_balance['balance_minutes'] = $in_call_limit;
                        Account::where('user_id',$user_id)->update($custom_balance);
                        $plans = AutoPlan::find($renew->autoplan_id);
                        $where['user_id']       = $user_id;
                        $where['plan_type']     = $plans->plan_type;
                        $update['status']       = 0;
                        UserPlan::where($where)->update($update);
                        $usage['plan_id']       = $plans->plan_id;
                        $usage['user_id']       = $user_id;
                        $usage['payment_id']    = 0;
                        $usage['status']        = 1;
                        $usage['plan_type']     = $plans->plan_type;
                        UserPlan::create($usage);
                    }
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

            ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}
