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
use Illuminate\Console\Scheduling\Schedule;

class AdvPaidSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advpaid:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance paid subscription';

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
            $next_renewal = Carbon::now()->addDays(31)->format('Y-m-d');
            $customer_id = Helper::get_option('mvno_customer_id');
            $mvno_key = Helper::get_option('bundle_mvno_key');

            $paidUsers = DB::table('auto_plan')->where('next_renewal', $curr_day)
                        ->where('adv_pay','>', 0)->where('status', 1)
                        ->where('card_expiry', '>=', $curr_day)                            
                        ->orderBy('user_id', 'asc')                        
                        ->get();

            if($paidUsers->isNotEmpty()) { //check for user exists                
                foreach ($paidUsers as $userdetails) {
                    $user_id = $userdetails->user_id; //taking the userid 
                    
                    $user = DB::table('users')->select('name','email','phone','i_account',
                                'currency','currency_symbol')
                                ->join('country','country_id','=','country.id')
                                ->where('users.id', $user_id)->where('users.status', '1')
                                ->first(); //taking user details from users table against the user

                    $blocked = Helper::check_fraudster($user_id); //check whether user is in fraud list
                    if(!$user || $blocked){  //if in fraud or user doen't active escape
                        continue;
                    }
                    
                    $billamount = $userdetails->total_amount;
                    $currency = $user->currency;
                    $i_account = $user->i_account; // parent user account
                    $autoid = $userdetails->id; // unique id from autoplan table of the grouped users

                    $autoplan['adv_pay'] = $userdetails->adv_pay - 1;
                    $autoplan['next_renewal'] = $next_renewal;
                    $plans = AutoPlan::updateOrCreate(['id' => $autoid], $autoplan);//update txn num

                    $payment['transaction_id'] = '';                        
                    $payment['payment_method'] = $plans->gateway;
                    $payment['payment_for'] = 'Advanced Subscription Renewal';  
                    $payment['user_id'] = $user_id;
                    $payment['amount'] = 0;  
                    $payment['currency'] = $currency;
                    $payment['category'] = $plans->plan_type;    
                    $payment['tax_amount'] = 0;
                    $payment['total_amount'] = 0;
                    $payment['description'] = 'Advanced Subscription Renewal (users: '.$plans->user_list.')';
                    $payment['status'] = '1';

                    if( $plans->plan_type === 'switch' ){
                        $plandetail = $plans->plan;
                        $i_billplan = '203'; //$plandetail->plan_id;
                        $in_call_limit = '1000'; //$plandetail->minutes;   
                        $payment['buy_price']  = $plandetail->buy_price;
                    }else if( $plans->plan_type === 'sim'){
                        $plandetail = $plans->getplan;                            
                        $i_billplan = '203'; //$plans->plan->switch_billing_plan;
                        $in_call_limit = '1000'; // $plans->plan->in_call_limit;
                        $simbillplan   = $plans->plan->sim_billing_plan;
                        $payment['buy_price']  = $plandetail->buy_price;
                    }

                    $paymentid = UserPayment::insertGetId($payment);
                        
                    $childlist = explode(',', $plans->user_list); //child list for the user
                                         
	                $dealercommcount = count($childlist); //calculate commission for child
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
                                      ->where('status', 1)->first(); 

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
                            $subscription_id = $userdata->userDetail->sim_subscription_id;                                    
                            $msisdn = $userdata->msisdn->phone_number;
                        
                            $end_point = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim
                            $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$simbillplan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];

                            $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));

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
                            }else{   
                                NotificationLog::create(['user_id'=>$child,'message' => 'Advanced Subscription EE Subscription Renewal Error','description'=>'Sub ID:'.$autoid.', msg:'.json_encode($response),'request'=>json_encode($sim_api_data),'status'=>'0']);                                    
                                continue;                                   
                            }
                        }

                        if($i_billplan != 0 || !is_null($i_billplan)){
                            $method = 'accountCredit';
                            $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, '0.01', $currency); 
                        	$temp =  SwitchHelper::call_switch_api($credit_xml);
                            if (array_key_exists("fault", $temp)){
                                NotificationLog::create(['user_id' => $child, 'message' => 'Advanced Subscription Switch AddCredit Failed','description'=>'Sub ID:'.$autoid.', msg:'.json_encode($temp),'status'=>'0']);                                
                                continue;
                            }

                            $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $i_billplan);
                            $temp = SwitchHelper::call_switch_api($xml_data);
                            if(array_key_exists("fault", $temp)) {
                                NotificationLog::create(['user_id'=>$child,'message' => 'Advanced Subscription Switch Plan Update Failed','description'=>'sub id:'.$autoid.', msg:'.json_encode($temp),'status'=>'0']);
                                $balance['balance_minutes'] = 0;
                                Account::where('user_id',$child)->update($balance);
                                $where['user_id'] = $child; 
                                $where['plan_type'] = $plans->plan_type;
                                $update['status'] = 0; 
                                UserPlan::where($where)->update($update);
                            }else{
                                $balance['balance_minutes'] = $in_call_limit;
                                Account::where('user_id',$child)->update($balance);
                                $where['user_id'] = $child; 
                                $where['plan_type'] = $plans->plan_type;
                                $update['status'] = 0; 
                                UserPlan::where($where)->update($update);   
                                $usage['plan_id'] = $plans->plan_id;  
                                $usage['user_id'] = $child;
                                $usage['payment_id'] = $paymentid; 
                                $usage['status'] = 1; 
                                $usage['plan_type'] = $plans->plan_type;
                                UserPlan::create($usage);  
                            }
                        }
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

            ScheduledTask::where('command', $this->signature)
                ->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}
