<?php

namespace App\Console\Commands;

use DB;
use Mail;
use Carbon;
use Helper;
use SwitchHelper;
use App\Models\Account;
use App\Models\UserPayment;
use App\Models\AutoRechargeHistory;
use App\Models\NotificationLog;
use App\Mail\PaymentStatus;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class AutoRecharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:autoRecharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Recharge runs, when user balance get below threshold';

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
        if(ScheduledTask::where(['command'=>$this->signature, 'status'=>1])->exists()){
            $start_time = microtime(true);
            $users = DB::table('auto_recharge')->select('auto_recharge.id','auto_recharge.user_id','transaction_id',
                        'amount','tax','total_amount','card_type')
                      ->join('account_balance','auto_recharge.user_id','=','account_balance.user_id')
                      ->where('balance_amount', '<=', 3)->where('card_expiry', '>=', date('Y-m-d'))
                      ->where('status', '1')->get();

            $environment = Helper::get_option('paypal_nvp_mode');
            $api_endpoint = "https://api-3t.paypal.com/nvp";
            if("sandbox" === $environment || "beta-sandbox" === $environment) {
                $api_endpoint = "https://api-3t.$environment.paypal.com/nvp";
            }
            $api_user = Helper::get_option('paypal_nvp_username');
            $api_password = Helper::get_option('paypal_nvp_password');
            $api_signature = Helper::get_option('paypal_nvp_signature');
            $version = urlencode('86.0');  
            $method_name = 'DoReferenceTransaction';
            $payment_type = urlencode('Sale');

            foreach($users as $user){           
                $user_id = $user->user_id;
                $u_data = DB::table('users')->select('name','email','phone','i_account',
                                    'currency','currency_symbol')
                                ->join('country','country_id','=','country.id')
                                ->where('users.id', $user_id)->where('users.status', '1')
                                ->first();

                $blocked = Helper::check_fraudster($user_id); //check whether user is in fraud list
                if(!$u_data || $blocked){  //if in fraud or user doen't active escape
                        continue;
                }

	        // check if payment was done today but not recharged
	        $recharge_history = AutoRechargeHistory::where('auto_recharge_id', $user->id)
	                ->whereDate('recharge_date', '=', date('Y-m-d'))
	                ->where('status', 0)
	                ->first();
	        if($recharge_history) {
	             continue;
	        }
                $amount = $user->amount;
                $tax_amount = $user->tax;
                $total_amount = $user->total_amount;
                $referenceid = $user->transaction_id;
                $currency = $u_data->currency;
                $i_account = $u_data->i_account;
	        $auto_recharge_history = new AutoRechargeHistory();
	        $auto_recharge_history->auto_recharge_id = $user->id;            
	        $auto_recharge_history->recharge_date = date('Y-m-d H:i:s');
            

                $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$total_amount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

                $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);
                $rsponse_status = strtoupper($response["ACK"]);
                if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                    $txn_id = $response['TRANSACTIONID'];
                    $payment['user_id'] = $user_id;             
                    $payment['transaction_id'] = $txn_id; 
                    $payment['amount'] = $amount;
                    $payment['tax_amount'] = $tax_amount;
                    $payment['total_amount'] = $total_amount;
                    $payment['payment_method'] = 'Auto Recharge';  
                    $payment['description'] = 'Auto Recharge ('.$user->card_type.')';

                    $obj = new \stdClass();
                    $obj->name = $u_data->name;
                    $obj->subject = config('settings.app_name').' Auto Recharge Payment';    
                    $obj->heading = 'Payment Status - Success';
                    $obj->amount = $u_data->currency_symbol.$amount;
                    $obj->transaction_id = $txn_id;                            

                    $method = 'accountCredit'; //accountAddFunds
                    $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$amount,$currency);
                    $temp =  SwitchHelper::call_switch_api($xml_data);
                    if (array_key_exists("fault", $temp)) {
                        $payment['status'] = '2';
                    	$payment_id = UserPayment::insertGetId($payment);

                    	$auto_recharge_history->payment_id = $payment_id;
                    	$auto_recharge_history->auto_recharge_desc = "Sippy credit error";
                    	$auto_recharge_history->status = 0;
                    	$auto_recharge_history->save();

                    	// notification message to fix the issue
                    	$notification_log = new NotificationLog();
                    	$notification_log->user_id = $user_id;
                        $notification_log->message = 'Autorecharge Switch Error';
                    	$notification_log->description = 'Autorecharge Error. Auto recharge history id '.$auto_recharge_history->id;
                    	$notification_log->status = 0;
                    	$notification_log->save();

                        $obj->heading = 'Payment Status - Error';   
                        $obj->error = 1;
                    } else {  
                        $payment['status'] = '1';
                    	$payment_id = UserPayment::insertGetId($payment); 

                    	$auto_recharge_history->payment_id = $payment_id;
                    	$auto_recharge_history->auto_recharge_desc = "Recharge successful";
                    	$auto_recharge_history->status = 1;
                    	$auto_recharge_history->save();
                        $user_balance = Account::where('user_id',$user_id)->first();
                        $ar_offer = '0.60';
                        $balance['balance_amount'] = $user_balance->balance_amount + $amount + $ar_offer;
                        Account::where('user_id',$user_id)->update($balance);
                        $obj->note = 'You have successfully received '.config('settings.app_name').' free 60 minutes to India.';
                    } 
                    if(filter_var($u_data->email, FILTER_VALIDATE_EMAIL)){
                    Mail::to($u_data->email)
                            // ->cc($cc_emails)
                            // ->bcc($bcc_emails)   
                            ->send(new PaymentStatus($obj));
                    } 
                }else{        
                	$auto_recharge_history->auto_recharge_desc = "Payment error";
                	$auto_recharge_history->status = 0;
                	$auto_recharge_history->save();

                	// notification message to fix the issue
                	$notification_log = new NotificationLog();
                	$notification_log->user_id = $user_id;
                    	$notification_log->message = 'Autorecharge Payment Error';
                	$notification_log->description = 'Autorecharge Error. Auto recharge history id '.$auto_recharge_history->id;
                	$notification_log->status = 0;
                	$notification_log->save();
                    // $obj = new \stdClass();
                    // $obj->name = 'Jijo Joseph';
                    // $obj->email = 'jijo.joseph@gmail.com';
                    // $obj->creatername = 'Joseph';
                    // $obj->creatertime = '2019-07-10 12:12:12';
                    // $obj->nameconf = 'Test Mail';

                    // if(filter_var($u_data->email, FILTER_VALIDATE_EMAIL)){
                    //     // Mail::to($u_data->email)
                    //     //     ->send(new AutoRechargeFailure($obj)); 
                    //         // ->cc($cc)
                    //         // ->bcc($bcc)                        
                    // }
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
