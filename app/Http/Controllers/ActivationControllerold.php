<?php

namespace App\Http\Controllers;

use DB;
use Str;
use Mail;
use Auth;
use Hash;
use Crypt;
use Helper;
use Carbon;
// use Redirect;
use SwitchHelper;
use App\Models\User;
use App\Models\Country;
use App\Models\SimList;
use App\Models\SimStock;
use App\Models\UserData;
use App\Models\VerifyUser;
use App\Models\TblPorting;
use App\Models\SimRequest;
use App\Models\UserPayment;
use App\Models\BridgeServer;
use App\Models\AutoRecharge;
use App\Models\UserCommission;
use App\Models\PlanCommission;
use App\Models\UserCreditCard;
use App\Models\PaymentCommission;
use App\Models\Admins;
use App\Models\SwitchLog;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use App\Mail\OrderComplete;
use App\Mail\Registration;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
use Illuminate\Support\Facades\Validator;


class ActivationControllerold extends Controller
{
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
    	$this->middleware('auth');
    }

    /**
    * Order Detail View 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */ 
    public function order_details(Request $request)
    {    
    	if ((!Helper::has_permission('orders') || !Helper::has_permission('orders','view_own')) && Helper::has_permission('orders','edit')) {
    		abort(403,'Access denied');
    	}
        $switch_id = 1;
        $admin = Auth::user();
        $admin_id = $admin->id;
        $promocode = $admin->promocode;
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();        
        array_push($where, $promocode);
        $request_id = $sim_list = $sim_request = [];
        $sim_number = ($request->sim_number)?:'';

    	if($sim_number){ 
    		if (substr($sim_number,0,2) == '44') {
    			$prefix_num = $sim_number;
    		} else if (substr($sim_number,0,3) == '+44') {
    			$prefix_num = substr($sim_number,1);
    		} else if (substr($sim_number,0,1) == '0') {
    			$prefix_num = '44'.substr($sim_number,1);
    		} else {
    			$prefix_num = $sim_number;
    		}
    		$stock = SimStock::where('sim_number', $sim_number)
    		            ->orWhere('phone_number', $prefix_num)->first();
			if($stock){
				if(isset($stock->list->autoplan_id)){
                    //$data = DB::select('select GROUP_CONCAT(id) as request_id from tbl_sim_request where order_id = ?',[$stock->list->sim_request->order_id])[0];
                    $data = SimRequest::select(DB::raw('GROUP_CONCAT(id) as request_id'))
                            ->where('order_id', [$stock->list->sim_request->order_id]);		    
            	    



                    if(Helper::has_permission('orders','view_own')) {
                        $data = $data->where('promocode',$promocode);
                    }


                    $data     = $data->first();
                    $request_id = explode(',', $data->request_id);
                    $sim_request = SimRequest::whereIn('id',$request_id)->get();
                    $sim_list = SimList::selectRaw('count(*) as sim_count,autoplan_id')->whereIn('request_id', $request_id)->groupBy('autoplan_id')->get();
				}
                if(!$sim_request->isEmpty())
                $switch_id = $sim_request[0]->user->switch_id;
			}            
		}else if($request->order_id){
			$data = SimRequest::select(DB::raw('GROUP_CONCAT(id) as request_id'))
			->where('order_id', $request->order_id);
            

            if(Helper::has_permission('orders','view_own')) {
                $data = $data->where('promocode',$promocode);
            }


            $data     = $data->first();
			$request_id = explode(',', $data->request_id);        	       
			$sim_list = SimList::selectRaw('count(*) as sim_count,autoplan_id')->whereIn('request_id', $request_id)->groupBy('autoplan_id')->get();
			$sim_request = SimRequest::whereIn('id',$request_id)->get();

            if(!$sim_request->isEmpty())
            $switch_id = $sim_request[0]->user->switch_id;
		}
        
        $currency = Helper::get_option('currency_symbol');
        $credits =DB::table('credits')->select('id','amount','default_amount')
                  ->where('switch_id', $switch_id)
                  ->where('status','1')->get();

		return view('old.activation-list', compact('sim_list','sim_number','sim_request','request_id','credits','currency'));
	}

    /*
    * Mark welcome call to a sim
    * if all sim welcome call completed status in request updated
    */
    public function mark_welcome_call(Request $request)
    {
    	if (!Helper::has_permission('orders','edit')) {
    		return[
    			'error' => true,
    			'message' => 'Access denied'
    		];
    	} 

    	if ($request->sim_id == '') {
    		return[
    			'error' => true,
    			'message' => 'Invalid sim identity'
    		];
    	}
    	$id = Crypt::decrypt($request->sim_id);

    	$sim_list = SimList::where('id', $id)->first();

    	if (!$sim_list) {
    		return[
    			'error' => true,
    			'message' => 'Invalid sim identity'
    		];
    	}

    	$sim_list->reg_status = 2;

    	$flag = true;
    	DB::beginTransaction();

    	if (!$sim_list->save()) {
    		$flag = false;
    	}


    	$pendingCallBack = SimList::where('request_id', $sim_list->request_id)
    	->where(function ($query) {
    		$query->where('reg_status', '0')
    		->orwhere('reg_status', '1');
    	})->count();

    	if ($pendingCallBack == 0) {
    		$result = SimRequest::where('id', $sim_list->request_id)->update(['delivery_status' => 3]);
    		if (!$result) {
    			$flag = false;
    		}
    	}

    	if ($flag) {
    		DB::commit();
    		return[
    			'error' => false
    		];
    	} else {
    		DB::rollback();
    		return[
    			'error' => true,
    			'Message' => 'Failed to change status'
    		];
    	}
    }

    public function show_activation_list(Request $request)
    {
    	if (!Helper::has_permission('orders','edit')) {
    		return[
    			'error' => true,
    			'message' => '<div class="alert alert-danger">Access denied</div>',
    		];
    	}

    	$sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
    	$currency = Helper::get_option('currency_symbol');
        if($sim_list[0]->stock->provider == 'EE'){
    	   $view = view('activation.activation_wizard', compact('sim_list', 'currency'))->render();
        }else{
            $user = User::where('id',$sim_list[0]->sim_request->user_id)->first();
            $credit_cards = DB::table('user_credit_cards')
                                ->where(['user_id' => $user->id, 'gateway' => 1])->get(); 
            $view = view('activation.custom_wizard', compact('sim_list', 'currency','user','credit_cards'))->render(); 
        }

    	return[
    		'error' => false,
    		'html' => $view
    	];
    }

    public function prorata_billing_process(Request $request)
    {   
        $user_id = $request->user_id;
        $autoplan_id = $request->autoplan_id;
        $bill_amount = Crypt::decrypt($request->billing_token);
        // $bill_amount = '';
        $card_id = Crypt::decrypt($request->credit_card); 
        
        $default_credit = UserCreditCard::where('user_id', $user_id)
                        ->where('id', $card_id)->first();
        if(Carbon::parse($default_credit->card_expiry)->lt(Carbon::now())){            
            return response()->json(['error' => true, 'message' => 'Card validity expired.']);
        }

        $user = User::select('users.id','name','i_account','currency_symbol',
                'tax','currency','short_code','email')
                ->join('country','country.id','=','country_id')
                ->where('users.id', $user_id)->first();

        $reference_id    = $default_credit->transaction_id;
        $currency       = $user->currency;
        $i_account      = $user->i_account;

        $environment    = Helper::get_option('paypal_nvp_mode');
        $api_endpoint   = 'https://api-3t.paypal.com/nvp';
        $api_user       = Helper::get_option('paypal_nvp_username');
        $api_password   = Helper::get_option('paypal_nvp_password');
        $api_signature  = Helper::get_option('paypal_nvp_signature');
        if('sandbox' === $environment) {
            $api_endpoint   = "https://api-3t.sandbox.paypal.com/nvp";
            $api_user       = Helper::get_option('paypal_nvp_username_sandbox');
            $api_password   = Helper::get_option('paypal_nvp_password_sandbox');
            $api_signature  = Helper::get_option('paypal_nvp_signature_sandbox');
        }

        $version        = urlencode('86.0');  
        $method_name    = 'DoReferenceTransaction';
        $payment_type   = urlencode('Sale');

        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$bill_amount&REFERENCEID=$reference_id&CURRENCYCODE=$currency&CUSTOM=$i_account";

        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 

        // $response = ['ACK'=>'Failed','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
        // $response = ['ACK'=>'SUCCESS','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
        $rsponse_status = strtoupper($response["ACK"]);
        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
            $renew_on = date('Y-m-01',strtotime('next month'));
            $now = Carbon::now()->format('Y-m-d H:i:s'); 
            $i_billplan = '203'; // plan -> switch_billing_plan; 
            DB::table('auto_plan_custom')->insert(['user_id' => $user_id, 'autoplan_id' => $autoplan_id, 'renew_on' => $renew_on, 'switch_billing_plan' => $i_billplan, 'created_at' => $now]);
            $txn_id = $response['TRANSACTIONID'];
            DB::table('user_credit_cards')->where('id', $card_id)->update(['transaction_id' => $txn_id]);

            $payment = ['transaction_id' => $txn_id, 'total_amount' => $bill_amount, 'payment_method' => 'Paypal', 'payment_for' => 'Plan Subscription Pro-rata Payment', 'user_id' => $user_id, 'amount' => $bill_amount, 'tax_amount' => 0, 'description' => 'Plan Subscription Pro-rata Payment', 'status' => '1', 'buy_price' => 0];
            $paymentid = UserPayment::insertGetId($payment);
            $auto_plan_data['next_renewal'] = date('Y-m-t',strtotime('next month'));            
            DB::table('auto_plan')->where('id', $autoplan_id)->update($auto_plan_data);  
            return response()->json(['success' => true]);     
        }else{
            $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0']]);

            $payment = ['transaction_id' => '', 'total_amount' => $bill_amount, 'payment_method' => 'Paypal', 'payment_for' => 'Plan Subscription Pro-rata Payment', 'user_id' => $user_id, 'amount' => $bill_amount, 'tax_amount' => 0, 'description' => 'Plan Subscription Pro-rata Payment,msg:'.$pay_error.')', 'status' => '0', 'buy_price' => 0];

            $paymentid = UserPayment::insertGetId($payment);

            NotificationLog::create(['user_id'=>$user_id,'message' => 'Plan Subscription Pro-rata Payment Failed','description'=>'Sub ID:'.$autoplan_id.', msg:'.$pay_error.', admin:'.Auth::id(),'status'=>'0']);
            return response()->json(['error' => true, 'message' => 'Payment Failed. Please try again..']);
        }
    }

    public function activate(Request $request)
    {
    	$stock_id = unserialize($request->stock_id);  
    	$flag = 0;
    	$mvno_key = Helper::get_option('bundle_mvno_key');
        $customer_id = Helper::get_option('mvno_customer_id');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
    	foreach($simList as $sim_data){
    		$address = json_decode($sim_data->sim_request->billing_address);
    		$parent_id = $sim_data->sim_request->user_id;    		
    		$cli_number = $sim_data->stock->phone_number;
    		$phone_number = '+'.$cli_number;
    		$user = User::where('stock_id', $sim_data->stock_id)->first();

    		if(!$user){
    			$user = User::where('id', $parent_id)->first();
	                $ip_address = $user->userDetail->ip_address;
	                // if(User::where('phone', $phone_number)->exists()){
	                //     $user = User::where('phone', $phone_number)->first();
	                // }
    			// if((!is_null($user->stock_id) && $user->stock_id != $sim_data->stock_id)){
    			// 	$flag = 1;
    			// }
    			if(!is_null($user->stock_id)){                    
    				$password = Helper::unique_code(8);					
    				$user = User::create([                        
    					'username' => $cli_number,           
    					'phone' => $phone_number,
    					'password' => Hash::make($password),                        
    					'country_id' => 238,
    					'parent_id' => $parent_id,
    					'stock_id' => $sim_data->stock_id,
    					'status' => 0
    				]); 

    				$username = Helper::unique_code(16);
    				$username = 'GB-'.$username;
    				$vm_password = Helper::random(10, implode(range('0','9')));
	                        $vp_password = Helper::unique_code(10);
	                        $country = Country::whereId(238)->first();
	                        $call_settings = ['accessnumber_support' => $country->accessnumber_support, 'wifi_support' => $country->wifi_support, 'callback_support' => $country->callback_support, 'conference_support' => $country->wifi_support, 'bundle_o' => 0];
	                        $call_settings = json_encode($call_settings);

    				$user_data = ['user_id' => $user->id, 'auth_name' => $username, 'vm_password' => $vm_password, 'vp_password' => $vp_password, 'address' => $address->street, 'city' => $address->city, 'call_settings' => $call_settings, 'state' => $address->country, 'postal_code' => $address->postal_code, 'ip_address' => $ip_address, 'user_platform' => config('settings.app_prefix'), 'register_status' => 0];
    				DB::table('user_data')->insert($user_data);
                    		DB::table('account_balance')->insert(['user_id' => $user->id,'balance_amount' => 0, 'balance_minutes'=> 0]);
    			}else{
    				User::where('id', $parent_id)->update(['username' => $cli_number, 'phone' => $phone_number, 'alt_phone' => $user->phone, 'stock_id' => $sim_data->stock_id]);
    			}                
    		}

    		$flag = 1;
            $provider = $sim_data->stock->provider;
            if($provider == 'EE'){
        		$sim_account_id = $user->userDetail->sim_account_id;			
        		if(is_null($sim_account_id) || $sim_account_id == ''){
        			$end_point = '/core/accounts?MVNO='.$mvno_key;
        			$data = new \stdClass();
        			$data->AccountInfo = (object) array(
        				'AccountType' => 'Prepaid',
        				'CustomerId' => (string)$customer_id,
        				'ExternalAccountId' => 'AVC'.$user->id,
        				'AccountStatus' => 'Active',     		
        				'Names' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
        				'Descriptions' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
        				'AccountCurrency' => 'GBP',
        				'Balance' => 1,
        				'CreditLimit' => 1
        			);

        			$response = Helper::call_sim_process_api($end_point, json_encode($data));
    		    	// $response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [{}]}');
                    	//$accounts['respo'] = $response;
        			if($response->resultType == 'Ok' && $response->resultCode == 0){
        				$log_data = array(
        					'user_id' => $user->id,
                            'stock_id' => $sim_data->stock_id,
        					'msisdn' => $cli_number,
        					'category' => 'CREATION',
                            'staff_id' => Auth::id(),
        					'reference_id' => $response->orderCode
        				);
        				DB::table('avoo_sim_log')->insert($log_data);
        				$account_id = $response->AccountId;
        				$accounts[$sim_data->stock_id] = $account_id;
        				DB::table('user_data')->where('user_id', $user->id)
        				->update(['sim_account_id' => $account_id]);
        				SimList::where('id', $sim_data->id)->update(['user_id' => $user->id]);
        			}else{
        				$accounts[$sim_data->stock_id] = '';		    		
        			}
        		}else{
        			$accounts[$sim_data->stock_id] = $sim_account_id;		    	
        		}
            }else{
                $account_id = 'AVC'.$provider.$user->id;
                DB::table('user_data')->where('user_id', $user->id)
                        ->update(['sim_account_id' => $account_id]);
                SimList::where('id', $sim_data->id)->update(['user_id' => $user->id]);
                $accounts[$sim_data->stock_id] = $account_id;
            }
    	}
        if($provider == 'EE'){
	        return view('activation.activate',compact('simList','accounts'))->render();
        }else{
            return view('activation.custom_activate',compact('simList','accounts'))->render();
        }
    }


    public function subscribe(Request $request)
    {
    	$stock_id = unserialize($request->stock_id);
    	$mvno_key = Helper::get_option('bundle_mvno_key');
        $customer_id = Helper::get_option('mvno_customer_id');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
    	foreach($simList as $sim_data){
    		$sim_subscription_id = '';
    		if(is_null($sim_data->user_id)){
    			$status[$sim_data->stock_id] = '';
    			continue;
    		}    		
    		$cli_number = $sim_data->stock->phone_number;
    		$auto_plan_id = $sim_data->autoplan_id;
            $provider = $sim_data->stock->provider;
    		$user = User::where('id', $sim_data->user_id)->first();
    		$user_data = $user->userDetail;	

    		if(!is_null($user_data->sim_subscription_id)){
    			$status[$sim_data->stock_id] = $user_data->sim_subscription_id;
    			continue;	
    		}

    		$plan = DB::table('auto_plan')
    		->select('auto_plan.plan_id','tbl_plans.sim_billing_plan','tbl_plans.buy_price','next_renewal','total_amount','user_list')
    		->join('tbl_plans', 'auto_plan.plan_id', '=' ,'tbl_plans.id')
    		->where('auto_plan.id', $auto_plan_id)->first();

    		if(is_null($user_data->sim_subscription_id) && $provider == 'EE'){
                $end_point = '/core/subscriptions?MVNO='.$mvno_key;
    			$data = (object)[
    				'CustomerId' => (int)$customer_id,
    				'Items' => [ (object)[
    					'AccountId' => (string)$user_data->sim_account_id,
    					'ProductOfferings' => [
    						(object)[
    							'ProductOfferingId' => (int)$plan->sim_billing_plan,
    							'OrderedProductCharacteristics' => [
    								(object)[
    									'Name' => 'MSISDN',
    									'Value' => $cli_number
    								]
    							]
    						]
    					],
    					'ServiceAddress' => (object)[			 
    						'Address' => 'unknown',
    						'HouseNo' => 'unknown',
    						'City' => 'unknown',
    						'ZipCode' => 'unknown',
    						'State' => 'unknown',
    						'CountryId' => '76'
    					]
    				] ],
    				'channel' => 'Web',
    			];

    			$response = Helper::call_sim_process_api($end_point, json_encode($data));	
            } else {
                $response = json_decode('{"orderCode":"o2order","Subscription":{"SubscriptionId":"o2subid"},"resultType":"Ok","resultCode":"0"}');
            }	
			// $response = json_decode('{"CustomerOrderId":"1050091279","Subscription":{"CustomerId":"1050077251","SubscriptionId":"1050077253","AccountId":"1065000000000000747","StartDate":"2019-08-15T15:48:33.3795171+02:00","EndDate":{},"Status":"Active","StatusReason":{},"Promotions":{},"Services":[{"ServiceId":"1050063957","Category":"CORESERVERCHARGING","Status":"active","ExternalId":{},"StartDate":"2019-08-15T15:48:32.9435134+02:00","EndDate":{},"ServiceCharacteristics":[null]}],"Products":[{"ProductId":"1003000280","ProductOfferingId":"1019000245","SubscriptionProductAssnId":"1050000000000006675","ProductChargePurchaseId":"1006000221","ProductCharacteristics":[null],"StartDate":"2019-08-15T15:48:32.9454952+02:00","EndDate":{},"RecurringAmount":{"Amount":"0","Currency":"GBP"},"NonRecurringAmount":{"Amount":"0","Currency":"GBP"},"UnbilledBalanceAmount":{"Amount":"0","Currency":"GBP"}}],"DeliveryAddress":{"Address":"unknown","HouseExtension":{},"HouseNo":"unknown","City":"unknown","ZipCode":"unknown","State":"unknown","CountryId":"76","ExternalAddressId":{}}},"orderCode":"6725390130994741271","resultType":"Ok","resultCode":"0","messages":["Order submitted"]}');		

			if($response->resultType == 'Ok' && $response->resultCode == 0){
				$log_data = array(
					'user_id' => $user->id,
                    		'stock_id' => $sim_data->stock_id,
					'msisdn' => $cli_number,
					'category' => 'BUNDLE_SUBSCRIPTION',
					'value' => $plan->buy_price,
	                        'plan_id' => $plan->plan_id,
	                        'staff_id' => Auth::id(),
					'reference_id' => $response->orderCode
				);
				DB::table('avoo_sim_log')->insert($log_data);

				$sim_subscription_id = $response->Subscription->SubscriptionId;			    	
				DB::table('user_data')->where('user_id', $user->id)
				->update(['sim_subscription_id' => $sim_subscription_id]);	

				DB::table('user_plans')->insert(['user_id' => $user->id, 'plan_id' => $plan->plan_id, 'status' => 1, 'plan_type' => 'sim']);		    	
			}
    			
    		$status[$sim_data->stock_id] = $sim_subscription_id;
    	}

        if($provider == 'EE'){
            return view('activation.subscribe', compact('simList','status'))->render();
        }else{
            return view('activation.custom_subscribe',compact('simList','status'))->render();
        }    	
    }

    public function create_sippy_account(Request $request)
    {
        $process_flag = $register_notify = 0;
    	$stock_id = unserialize($request->stock_id);

    	$request_id = json_decode($request->request_id);        
    	$mvno_key = Helper::get_option('bundle_mvno_key');
        // $customer_id = Helper::get_option('mvno_customer_id');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
        $user_ids = array_column($simList->toArray(), 'user_id');
        $user_list = implode(',', $user_ids);
    	foreach($simList as $sim_data){  
    		$promocode = $sim_data->sim_request->promocode;   		  	
    		if(is_null($sim_data->user_id)){
    			$status[$sim_data->stock_id] = '';
    			continue;
    		}

    		$cli_number = $sim_data->stock->phone_number;
            $provider = $sim_data->stock->provider;
            $trust_number = '+'.$cli_number;
    		$auto_plan_id = $sim_data->autoplan_id;
    		$txn_id = $sim_data->auto_plan->transaction_id;
            $payment_method = $sim_data->sim_request->payment->payment_method; 
    		$card_type = $sim_data->auto_plan->card_type;
    		$switch_billing = '203';//$sim_data->auto_plan->plan->switch_billing_plan;
            $in_call_limit = '1000';//$sim_data->auto_plan->plan->in_call_limit;

    		$parent_id = $sim_data->sim_request->user_id; 

    		$plan = DB::table('auto_plan')->select('next_renewal','status')
                    ->where('auto_plan.id', $auto_plan_id)->first();

    		$user = User::where('id', $sim_data->user_id)->first();		
    		$user_data = $user->userDetail;	

            if($provider == 'EE'){
        		if(is_null($user_data->sim_account_id) || is_null($user_data->sim_subscription_id)){
        			$status[$sim_data->stock_id] = '';
        			continue;
        		}
            }

    		$country = Country::join('switch_template','switch_id','=','switch_template.id')
    		->where('country.id',$user->country_id)->first();

			//Switch Activation Process
    		if(is_null($user->i_account)){
    			$data = [ 'username' => $user_data->auth_name, 'a_class' => $country->a_class,
    			'tariff' => $country->tariff, 'timezone_value' => $country->timezone_value,
    			'balance' => $country->default_balance, 'phone' => $cli_number,
    			'translation_rule' => $country->translation_rule, 
    			'export_type' => $country->export_type, 'currency' => $country->currency, 
	            'vp_password' => $user_data->vp_password, 'vm_password' => $user_data->vm_password,
	            'notify_email' => ($user->email)?:'', 'first_name'=> ($user->first_name)?:'',
	            'last_name'=> ($user->last_name)?:'', 'company_name' => $user_data->username,
	            'billing_plan' => $switch_billing, 'routing_group' => $country->routing_group];	

                //'cli_number' => $trust_number,
                              
    			$xml_data = SwitchHelper::switch_create_account_xml($data);
    			$temp =  SwitchHelper::call_switch_api($xml_data);
                	// $temp = []; 
    			$switch_status = 'F';
    			if (array_key_exists("fault", $temp)) {
    			    $i_account = NULL;	
                    $swithlog['description']    = 'Activation Failed';  
                    $swithlog['user_id']        =  $user->id; 
                    $swithlog['status']         = 2;
                    SwitchLog::insertGetId($swithlog);            
    			} else {
    				$array = $temp['params']['param']['value']['struct']['member'];
    				$response = [];
    				for ($i = 0; $i < count($array); $i++) {
    					$array_key = array_keys($array[$i]['value'])[0];
    					if (empty($array[$i]['value'][$array_key])) {
    						$response[$array[$i]['name']] = NULL;
    					} else {
    						$response[$array[$i]['name']] = $array[$i]['value'][$array_key];
    					}
    				}		             
    				if ($response['result'] == 'OK') {
    					$i_account = $response['i_account'];
    					$switch_status = "S";
                		$switch_log['description'] = 'Activation success '.$i_account; 
    				}
    			}

                // $switch_log['user_id'] = $user->id;                
                // DB::table('switch_log')->insert($switch_log);
    			if($switch_status == 'S'){
                    $default_number = 1;
                    
    				DB::table('trusted_numbers')->insert(['user_id' => $user->id, 'trusted_number' => $trust_number, 'verified' => 1, 'api_token' => Str::random(90),'default_number' => $default_number]);                     
    				$xml_data = SwitchHelper::switch_add_cli_xml($i_account,$trust_number);
    				$temp = SwitchHelper::call_switch_api($xml_data);

                    $bridgeips = BridgeServer::where('set_default' ,1)->get();

                    if($bridgeips){
                        $clinumber = str_replace('+', '', $trust_number);
                        $auth_rule = array('i_account' => $i_account, 'cli_number' => $clinumber);
                        foreach($bridgeips as $bridge){
                            $auth_rule['remote_ip'] = $bridge->server;
                            $xml_data   = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
                            $temp       =  SwitchHelper::call_switch_api($xml_data);
                            $auth_rule['cld_number']      = $clinumber;
                            $auth_rule['cld_translation'] = $clinumber;
                            $auth_rule['cli_translation'] = '+'.$bridge->service_no;
                            $xml_data = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
                            $temp = SwitchHelper::call_switch_api($xml_data);
                        }
                    }    				

    				User::where('id', $user->id)
    				    ->update(['i_account' => $i_account, 'switch_id' => $country->switch_id, 'time_zone' => $country->time_zone, 'status' => 1]);
                    
                    DB::table('account_balance')->where('user_id', $user->id)
                        ->update(['balance_minutes' => $in_call_limit]);

    				DB::table('user_data')->where('user_id', $user->id)->update(['i_account' => $i_account, 'register_status' => 1 ]);	

    				SimList::where('id',$sim_data->id)->update(['reg_status' => 1]);
    				
    				$auto_plan_data['user_list'] = $user_list;
    				if(is_null($plan->next_renewal) || $plan->status == 0){
    					$auto_plan_data['status'] = 1;
                        if(is_null($plan->next_renewal)){
                            $auto_plan_data['switch_billing_plan'] = $sim_data->auto_plan->plan->switch_billing_plan;
    					    $auto_plan_data['next_renewal'] = Carbon::now()->addDays(30)->format('Y-m-d');
                        }
                        if($user->id == $parent_id){
                            $register_notify = 1;
                        }
    				}
    				DB::table('auto_plan')->where('id', $auto_plan_id)->update($auto_plan_data);

                    $welcome_msg = "Greetings! from AVOO Mobile. Now you can download AVOO Mobile App for making FREE and affordable international calls. https://bit.ly/2C1SyOw\n\nThankyou.";

                    $account_sid = Helper::get_option('twilio_account_sid');
                    $auth_token =  Helper::get_option('twilio_auth_token');
                    $twilio_number = Helper::get_option('twilio_number');                            
                    try {             
                        $client = new Client($account_sid, $auth_token);
                        $client->messages->create(
                            $trust_number,
                            array(
                                'from' => $twilio_number,
                                'body' => $welcome_msg
                            )
                        );
                    } catch (RestException $exception) {                  
                        
                    }
                }
                $process_flag = 1;
    		}else{
    			$i_account = $user->i_account;
                $default_number = 0;
                if(!DB::table('trusted_numbers')->where('trusted_number',$trust_number)->exists()) {
					DB::table('trusted_numbers')->insert(['user_id' => $user->id, 'trusted_number' => $trust_number, 'verified' => 1, 'api_token' => Str::random(90),'default_number' => $default_number]);                     
					$xml_data = SwitchHelper::switch_add_cli_xml($i_account,$trust_number);
					$temp = SwitchHelper::call_switch_api($xml_data);

					$bridgeips = BridgeServer::where('set_default' ,1)->get();

                    if($bridgeips){
                        $clinumber = str_replace('+', '', $trust_number);
                        $auth_rule = array('i_account' => $i_account, 'cli_number' => $clinumber);
                        foreach($bridgeips as $bridge){
                            $auth_rule['remote_ip'] = $bridge->server;
                            $xml_data   = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
                            $temp       =  SwitchHelper::call_switch_api($xml_data);
                            $auth_rule['cld_number']      = $clinumber;
                            $auth_rule['cld_translation'] = $clinumber;
                            $auth_rule['cli_translation'] = '+'.$bridge->service_no;
                            $xml_data = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
                            $temp = SwitchHelper::call_switch_api($xml_data);
                        }
                    }

                    $welcome_msg = "Greetings! from AVOOMobile. Now you can download AVOOMobile App for making FREE and affordable international calls. https://bit.ly/2C1SyOw\n\nThankyou.";

                    $account_sid = Helper::get_option('twilio_account_sid');
                    $auth_token =  Helper::get_option('twilio_auth_token');
                    $twilio_number = Helper::get_option('twilio_number');                            
                    try {             
                        $client = new Client($account_sid, $auth_token);
                        $client->messages->create(
                            $trust_number,
                            array(
                                'from' => $twilio_number,
                                'body' => $welcome_msg
                            )
                        );
                    } catch (RestException $exception) {                  
                
                    }
				}

                $method = 'accountCredit';        
                $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, '0.01', $country->currency,'');
                $temp =  SwitchHelper::call_switch_api($credit_xml);
                if (array_key_exists("fault", $temp)) {
                    NotificationLog::create(['user_id'=>$user->id,'message' => 'Switch Balance Update Failed','description'=>'sub id:'.$plans->id.', msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']); 
                }

                $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $switch_billing);
                $temp     = SwitchHelper::call_switch_api($xml_data);
                if (array_key_exists("fault", $temp)) {
                    NotificationLog::create(['user_id'=>$user->id,'message' => 'Subscription Switch Plan Update Failed','description'=>'sub id:'.$plans->id.', msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']); 
                }else{
                    DB::table('account_balance')->where('user_id', $user->id)
                        		->update(['balance_minutes' => $in_call_limit]);
                }
				SimList::where('id',$sim_data->id)->update(['reg_status' => 1]);
				
				$auto_plan_data['user_list'] = $user_list;
                if(is_null($plan->next_renewal) || $plan->status == 0){
                    $auto_plan_data['status'] = 1;
                    if(is_null($plan->next_renewal)){
                       $auto_plan_data['next_renewal'] = Carbon::now()->addDays(30)->format('Y-m-d');
                    }
                    if($user->id == $parent_id){
                        $register_notify = 1;
                    }
                }

				DB::table('auto_plan')->where('id', $auto_plan_id)->update($auto_plan_data);
                $process_flag = 1;	        	
    		}

    		$status[$sim_data->stock_id] = $i_account;

    		if($sim_data->credit > 0 && !is_null($i_account)){
    			// $topup_end_point = '/superapi/topup?mvno='.$mvno_key;
    			// $topup_data = (object)[
    			// 	'msisdn' => $cli_number,
    			// 	'amount' => 100 * $sim_data->credit
    			// ];

    			// $topup_response = Helper::call_sim_process_api($topup_end_point, json_encode($topup_data));
	 			// $topup_response =  json_decode('{"customerId":"901000315","orderCode":"6722352966695780357","resultType":"SUCCESS","resultCode":"0","messages":[{}]}');

    			// if($topup_response->resultType == 'SUCCESS' && $topup_response->resultCode == 0){
    				// $log_data = array(
    				// 	'user_id' => $user->id,
    				// 	'msisdn' => $cli_number,
    				// 	'category' => 'EXTERNAL_TOPUP',
    				// 	'value' => $sim_data->credit			    		
    				// );
    				// DB::table('avoo_sim_log')->insert($log_data);

    				$payment = ['user_id' => $parent_id, 'pay_to' => $user->id, 'transaction_id' => $txn_id, 
                            'buy_price' => '0', 'amount' => $sim_data->credit, 'tax_amount' => 0, 
                            'total_amount' => $sim_data->credit, 'payment_method' => $payment_method,
                            'payment_for' => 'Credit Added','description' => 'credit added card :('.$card_type.')'
                            ];					

					$method = 'accountCredit'; //accountAddFunds
                    $swithlog['description']    = 'accountCredit';  
                    $swithlog['user_id']        =  $user->id;  
					$xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$sim_data->credit,$country->currency);
					$temp =  SwitchHelper::call_switch_api($xml_data);
					// $temp = [];
					if (array_key_exists("fault", $temp)) {
						$payment['status'] = '2';
                        			$swithlog['status'] = 2;
					} else {  
						$payment['status'] = '1';						
                        			$swithlog['status'] = 1;						
						DB::table('account_balance')->where('user_id', $user->id)
							->increment('balance_amount', $sim_data->credit);				
					}
                    			SwitchLog::insertGetId($swithlog); 
					DB::table('user_payments')->insert($payment);
					SimList::where('id',$sim_data->id)->update(['credit' => 0]);
                    $process_flag = 1;
				// } else {	
				// 	$status[$sim_data->stock_id] = '';
				// }
			}
		}

        $pending = SimList::whereIn('request_id', $request_id)->where('reg_status', '0')->count();
		if($pending == 0 && $process_flag == 1){
			foreach($request_id as $rqst_id){
				$note = 'Activated by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on';
				DB::table('delivery_history')->insert(['sim_request_id' => $rqst_id ,'proceed_by' => Auth::id(),  'type' => 2, 'note' => $note]);
				DB::table('tbl_sim_request')->where('id', $rqst_id)->update(['delivery_status' => 2]);
			}				
		}

        $list_pending = SimList::where('autoplan_id', $auto_plan_id)->where('reg_status', '0')->count();
        if($list_pending == 0 && $process_flag == 1) {
        	if(!is_null($promocode) && ($promocode != 'web')) {            
	            $idata =  DB::table('tbl_sim_request as sr')->select('ap.plan_id','ap.bundle_id','ap.total_amount','ad.id as commuser', 'ad.role as commuserrole')
	                    ->join('tbl_sim_list as sl', 'sl.request_id', '=', 'sr.id')
	                    ->join('auto_plan as ap', 'ap.id', '=', 'sl.autoplan_id')
	                    ->leftJoin('admins as ad', 'ad.promocode', '=', 'sr.promocode')
	                    ->where('ap.id', $auto_plan_id)->first(); //get plan to provide commission

	            $planType = ($idata->bundle_id === 0) ? 1 : 2;  
	            $planId   = $idata->plan_id;
	            if( $planType === 2){
	                $planId = $idata->bundle_id;
	            }

	            $role_details = DB::table('tbl_roles')->where('id', $idata->commuserrole)->first();
	            if($role_details->short_code == 'DEALER') {
	                if($idata->commuser != "")
	                {
	                    $commusers = Admins::find($idata->commuser)->ascendings();
	                    $commusers->push($idata->commuser);
	                    $i =0;
	                    foreach ($commusers as  $benifuser) {
	                        $commStud = UserCommission::join('comm_durations as cd', 'cd.id', '=', 'userplan_commissions.comm_duration')
	                         ->where('user_id',$benifuser)
	                         ->where('plan_type',$planType)
	                         ->where('plan_id',$planId)
	                         ->where('status',1)
	                         ->get();  //To check dealer have special commission rates

	                        if( $commStud->isEmpty()){

	                            $checkparent = DB::table('admins')->select('parent_id')
	                                            ->where('id',$benifuser)->first();

	                            if($checkparent->parent_id != 0){ //check whether this parent if child enter loop

	                                $getparents = Admins::find($benifuser)->ascendings();

	                                foreach ($getparents as $parentid) {

	                                    $commStud = UserCommission::join('comm_durations as cd', 'cd.id', '=', 'userplan_commissions.comm_duration')
	                                         ->where('user_id',$parentid)
	                                         ->where('plan_type',$planType)
	                                         ->where('plan_id',$planId)
	                                         ->where('status',1)
	                                         ->get();
	                                    if( $commStud->isEmpty()){ continue; }else { break; } //break the loop if commission exists
	                                }
	                                if( $commStud->isEmpty()){
	                                    $commStud = PlanCommission::join('comm_durations as cd', 'cd.id', '=', 'plan_commissions.comm_duration')
	                                     ->where('plan_type',$planType)
	                                     ->where('plan_id',$planId)
	                                     ->where('status',1)
	                                     ->get();          //To take the default commission rates
	                                }
	                            }else{ //if parent take default plan rates since special commission doesn't exist

	                                $commStud = PlanCommission::join('comm_durations as cd', 'cd.id', '=', 'plan_commissions.comm_duration')
	                                     ->where('plan_type',$planType)
	                                     ->where('plan_id',$planId)
	                                     ->where('status',1)
	                                     ->get();          //To take the default commission rates
	                            }
	                        }
                            
	                        foreach ($commStud as $key => $comm) { 
                                $commAmount = $planAmount = $commAmount = 0;
                                $commArray    = [];
                                $planAmount   = $idata->total_amount;
                                $commAmount   = $comm->comm_rate;
                                $paymentDate  = Carbon::now()->addDays($comm->days)->format("Y-m-d");
                                $paymentEnd= Carbon::parse($paymentDate)->addDays(1)->format("Y-m-d");
                                if($comm->comm_type === 2) {
                                    $percent = ($commAmount / 100) * $planAmount;
                                    $commAmount = number_format(floor($percent*100)/100, 2);
                                }
                                $commArray['autoplan_id']     = $auto_plan_id;
                                $commArray['comm_user']       = $benifuser;
                                $commArray['payment_date']    = $paymentDate;
                                $commArray['payment_end']     = $paymentEnd;
                                $commArray['pay_amount']      = $commAmount;
                                $commArray['comm_rate']       = $comm->comm_rate;
                                $commArray['comm_type']       = $comm->comm_type;
                                PaymentCommission::firstOrCreate(['autoplan_id' => $auto_plan_id,'comm_user' => $benifuser,'payment_date' => $paymentDate], $commArray);
	                        }
	                    }
	                }
	            }
	        }
        }

		if($register_notify){
            $verify_token = sha1(time());
            VerifyUser::updateOrCreate(
                ['email' => $user->email],
                ['token' => $verify_token]
            ); 
            $password = Helper::unique_code(8);
            $hash = Hash::make($password);
            User::where('id', $parent_id)->update(['password'=> $hash]);
            $obj = new \stdClass();
            $obj->name = ($user->name)?:'User';
            $obj->subject = 'Welcome to AVOOMobile';
            $obj->heading  = 'Welcome To AVOOMobile';
            $obj->email = $user->email;
            $obj->username = $user->username;
            $obj->password = $password;
            $obj->token = $verify_token;
            
            $bcc_emails = Helper::get_option('bcc_emails');
            $bcc_emails = explode(',', $bcc_emails);
            Mail::to($user->email)
                ->bcc($bcc_emails)
                ->send(new Registration($obj));
        }
		return view('activation.create_sippy', compact('simList','status'))->render(); 
	}

	public function enable_auto_recharge(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'stock_id' =>['required'], 
			'amount' => ['required'], 
        ]);
		if ($validator->fails()) {
			return response()->json(['error' => 'Enter a valid amount!']);
		}

		$stock_id = $request->stock_id;
		$amount = $request->amount;
		$status = $request->status;
		$sim_data = SimList::where('stock_id', $stock_id)->first();
		$user_id = $sim_data->user_id;
		$txn_id = $sim_data->auto_plan->transaction_id;
		$card_type = $sim_data->auto_plan->card_type;
		$card_expiry = $sim_data->auto_plan->card_expiry;
		
		AutoRecharge::updateOrCreate(
			['user_id' => $user_id],
			['transaction_id' => $txn_id, 'amount' => $amount, 'tax' => 0, 'total_amount' => $amount, 
			'card_expiry' => $card_expiry, 'card_type' => $card_type, 'status' => $status]
		);

		return response()->json(['success' => 1]);
	}

    public function process_order_email(Request $request)
    {
        $stock_id = unserialize($request->stock_id);  
        $order = SimList::whereIn('stock_id', $stock_id)->get();
        $order_id = $order[0]->sim_request->order_id;
        $parent_id =   $order[0]->sim_request->user_id;   
        foreach($order as $item){
            $auto_amount = 0;
            if(isset($item->auto_recharge->status) &&  $item->auto_recharge->status == 1){
                $auto_amount = $item->auto_recharge->total_amount;
            }
            $sim['phone'] = '0'.ltrim($item->stock->phone_number,'44');
            $sim['auto_recharge'] =  $auto_amount; 
            $sim['plan'] = $item->auto_plan->plan->plan_name ;
            $sim['next_renewal'] = $item->auto_plan->next_renewal;
            $simDetails[] =  $sim;
        }        

        $user = User::where('id', $parent_id)->first();

        $obj = (object) array(
            'order' => $simDetails,
            'user' => $user,
            'order_id' => $order_id,
            'subject' => 'Sim Activation Completed',
            'heading' => 'Sim Activation',
        ); 

        // $send_at = Carbon::now()->addMinutes(10);

        Mail::to($user->email)
            // ->cc()
            ->bcc('jijojoseph001@gmail.com')
            ->send(new OrderComplete($obj)); 
        return response()->json(['success' => 1]);
    }

    /*
    * Function porting manage
    */
    public function change_port_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'list_id' => ['required'], 
            'stock_id' => ['required'],             
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid Data!']);
        }

        SimList::where('stock_id', $request->stock_id)->update(['port' => $request->status]);
        $user = DB::table('tbl_sim_list as sl')
            ->join('tbl_sim_request as sr', 'sl.request_id', '=', 'sr.id')
            ->where('sl.stock_id', $request->stock_id)->first();
        $desc = ['PAC submitted by '.Auth::user()->first_name.' '.Auth::user()->last_name];
        TblPorting::updateOrCreate(
            ['stock_id' => $request->stock_id],
            ['pac_number' => $request->pac, 'porting_to' => $request->port_to, 'status' => 0, 
            'promocode' => $user->promocode,'description' =>json_encode($desc)]
        );
        return response()->json(['success' => 1]);
    }

    /*
    * Show details of sim in a list
    */
    public function bundle_sim_list(Request $request)
    {
        $stock_id = unserialize($request->stock_id);

        $simList = SimList::whereIn('stock_id', $stock_id)->get();
        return view('activation.bundle_sim_list', compact('simList'))->render();
    }


    /*
    * Show details of sim in a list
    */
    public function porting_request()
    {
        $data = TblPorting::orderBy('created_at','desc')->get();
        return view('porting-list', compact('data'));
    }

    /*
    * Show details of sim in a list
    */
    public function edit_porting_request($id)
    {
        $port = TblPorting::where('id', $id)->first();
        return view('porting-edit', compact('port'));
    }

    /*
    * Function Update Porting status
    */
    public function update_porting_request(Request $request)
    {
        $params = [];
        parse_str($request->data, $params);     
        $port = TblPorting::where('id', $params['id'])->first(); 
        $data['pac_number'] = $params['pac_number'];
        $data['porting_to'] = $params['porting_to'];
        $data['provider'] =  ($params['provider'] != 'other') ? $params['provider'] : $params['other_provider'];
        $data['reference_id'] = $params['reference_id'];            
        $data['expected_date'] = $params['expected_date'];
        $data['staff_id'] = Auth::id();
        $user = DB::table('admins')->where('id', Auth::id())->first();

        $description = ($port->description)?json_decode($port->description):[]; 
        $steps = ['Requested','Initiated','Confirmed'];
        $desc = $steps[$request->step].' by '.Auth::user()->first_name.' '.Auth::user()->last_name; 
        array_push($description, $desc);        
        $data['description'] = json_encode($description);
        $step = $request->step+1;
        $data['status'] = $step;
        $respo = TblPorting::where('id', $params['id'])->update($data);

        $port = TblPorting::where('id', $params['id'])->first();

        $html = view('porting-wizard', compact('port'))->render();

        return [ 'html' => $html ];         
    }

    /*
    * Function Update Porting status
    */
    public function finish_porting_process(Request $request)
    {
        $port = TblPorting::where('id', $request->port_id)->first();
        $description = ($port->description)?json_decode($port->description):[]; 
        $steps = ['Requested','Initiated','Confirmed'];
        $desc = 'Completed by '.Auth::user()->first_name.' '.Auth::user()->last_name; 
        array_push($description, $desc);        
        $description = json_encode($description);
        TblPorting::where('id', $port->id)->update(['description' => $description]);
        $temp_number = $port->stock->phone_number;
        $user = User::where('id', $port->list->user_id)->first();
        $mvno_key = Helper::get_option('bundle_mvno_key');
        $cli_number = $port->porting_to;
        $phone_number = '+'.$cli_number;
        $trusted = DB::table('trusted_numbers')->where('trusted_number', 'like', '%'.$temp_number.'%')->first();
        if($trusted) { 
            if($port->sub_status == 0){  
                $end_point = '/superapi/subscriptions/'. $user->userDetail->sim_subscription_id.'/msisdn?from='.$temp_number.'&to='.$cli_number.'&mvno='.$mvno_key;

                $response = Helper::call_sim_process_api($end_point, '', 'patch');

                if($response->resultCode == '0' && $response->resultType == 'Success'){
                    TblPorting::where('id', $port->id)->update(['sub_status' => 1]);
                }else{                    
                    return ['error' => true,'source'=>'ee','response'=>json_encode($response),'endpoint'=>$end_point];
                }
            }
            DB::beginTransaction();
            $trust = DB::table('trusted_numbers')->where('id', $trusted->id)
                        ->update(['trusted_number' => $phone_number]);

            $xml_data = SwitchHelper::switch_delete_cli_xml($user->i_account, $trusted->trusted_number);
            $temp =  SwitchHelper::call_switch_api($xml_data);
            if (array_key_exists("fault", $temp)) {
                DB::rollback();
                return ['error' => true,'response'=>json_encode($temp)];
            }

            $xml = SwitchHelper::switch_add_cli_xml($user->i_account, $phone_number);
            $temp = SwitchHelper::call_switch_api($xml);
            if (array_key_exists("fault", $temp)) {
                DB::rollback();
                return ['error' => true,'response'=>json_encode($temp)];
            }
            if($trusted->default_number == 1){
                $u_cli = ['i_account'=>$user->i_account,'cli_number'=>$phone_number];
                $xml_data = SwitchHelper::switch_change_default_cli_xml($u_cli);
                $temp =  SwitchHelper::call_switch_api($xml_data);
            }            
        } else {
            DB::beginTransaction();
            $trust_data = ['user_id' => $user->id, 'trusted_number' => $phone_number,
            'verified' => 1];
            DB::table('trusted_numbers')->insert($trust_data);
            $xml = SwitchHelper::switch_add_cli_xml($user->i_account, $phone_number);
            $temp = SwitchHelper::call_switch_api($xml);
            if (array_key_exists("fault", $temp)) {
                DB::rollback();
                return ['error' => true,'response'=>json_encode($temp)];
            }
            $trust = 1;
        }
        
        $stock = DB::table('tbl_sim_stock')->where('id', $port->stock_id)->update(['phone_number'=> $cli_number, 'temp_number' => $temp_number]);

        $user_exist = User::where('phone', 'like', '%'.$temp_number.'%')
            ->orWhere('username', 'like', '%'.$temp_number.'%')
            ->where('id', $user->id)->exists();

        User::where('phone', 'like', '%'.$temp_number.'%')->update(['phone' => $phone_number]);
        User::where('username', 'like', '%'.$temp_number.'%')->update(['username' => $cli_number]);

        $finish = TblPorting::where('id', $port->id)->update(['status' => 4, 'staff_id' => Auth::id()]);
        if($stock && $trust && $finish) {
            DB::commit();
        } else {
            DB::rollback();
            return ['error' => true];
        }
        return ['success' => true];        
    }

}
