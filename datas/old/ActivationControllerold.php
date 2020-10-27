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
use App\Models\AutoRecharge;
use Illuminate\Http\Request;
use App\Mail\OrderComplete;
use App\Mail\Registration;
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
    * Sim Activation Search 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */ 
    public function activation_view(Request $request)
    {    
    	if (!Helper::has_permission('sim_management')) {
    		abort(403,'Access denied');
    	} 
    	$request_id = $sim_list = $sim_request = [];  
    	$sim_number =  ($request->sim_number)?:'';
        $switch_id = 1;
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
					//$sim_list = SimList::where('autoplan_id', $stock->list->autoplan_id)->get();

                    $data = DB::select('select GROUP_CONCAT(id) as request_id from tbl_sim_request where order_id = ?',[$stock->list->sim_request->order_id])[0];

                    $request_id = explode(',', $data->request_id);
                    $sim_request = SimRequest::whereIn('id',$request_id)->get();
                    $sim_list = SimList::selectRaw('count(*) as sim_count,autoplan_id')->whereIn('request_id', $request_id)->groupBy('autoplan_id')->get();
				}
                $switch_id = $sim_request[0]->user->switch_id;
			}            
		}else if($request->order_id){
			$data = SimRequest::select(DB::raw('GROUP_CONCAT(id) as request_id'))
			->where('order_id', $request->order_id)->first();

			$request_id = explode(',', $data->request_id);        	       
			$sim_list = SimList::selectRaw('count(*) as sim_count,autoplan_id')->whereIn('request_id', $request_id)->groupBy('autoplan_id')->get();
			$sim_request = SimRequest::whereIn('id',$request_id)->get();
            $switch_id = $sim_request[0]->user->switch_id;
		}
        
        $currency = Helper::get_option('currency_symbol');
        $credits =DB::table('credits')->select('id','amount','default_amount')
                  ->where('switch_id', $switch_id)
                  ->where('status','1')->get();

		return view('activation-list', compact('sim_list','sim_number','sim_request','request_id','credits','currency'));
	}

    /*
    * Mark welcome call to a sim
    * if all sim welcome call completed status in request updated
    */
    public function mark_welcome_call(Request $request)
    {
    	if (!Helper::has_permission('sim_management','edit')) {
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
    	if (!Helper::has_permission('sim_management','edit')) {
    		return[
    			'error' => true,
    			'message' => '<div class="alert alert-danger">Access denied</div>',
    		];
    	}

    	$sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
    	$currency = Helper::get_option('currency_symbol');
              
    	$view = view('activation.activation_wizard', compact('sim_list', 'currency'))->render();

    	return[
    		'error' => false,
    		'html' => $view
    	];
    }

    public function activate(Request $request)
    {
    	$stock_id = unserialize($request->stock_id);    
  
    	$flag = 0;
    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
    	foreach($simList as $sim_data){
    		$address = json_decode($sim_data->sim_request->billing_address);
    		$parent_id = $sim_data->sim_request->user_id;    		
    		$customer_id = DB::table('tbl_dealers')
                                ->where('id', $sim_data->stock->dealer_id)->value('customer_id');
    		$cli_number = $sim_data->stock->phone_number;
    		$phone_number = '+'.$cli_number;
    		$user = User::where('stock_id', $sim_data->stock_id)->first();

    		if(!$user){
    			$user = User::where('id', $parent_id)->first();
    			if((!is_null($user->stock_id) && $user->stock_id != $sim_data->stock_id)){
    				$flag = 1;
    			}
    			if(!is_null($user->i_account) || $flag == 1){
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
    				$user_data = ['user_id' => $user->id, 'auth_name' => $username, 'vm_password' => $vm_password, 'address' => $address->street, 'city' => $address->city, 'state' => $address->country, 'postal_code' => $address->postal_code, 'ip_address' => \Request::ip(), 'user_platform' => 'sim', 'register_status' => 0];
    				DB::table('user_data')->insert($user_data);
    			}else{
    				User::where('id', $parent_id)->update(['stock_id' => $sim_data->stock_id]);
    			}
    		}
    		$flag = 1;

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
    			if($response->resultType == 'Ok' && $response->resultCode == 0){
    				$log_data = array(
    					'user_id' => $user->id,
    					'msisdn' => $cli_number,
    					'category' => 'CREATION',
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
    	}

    	return view('activation.activate',compact('simList','accounts'))->render();
    }


    public function subscribe(Request $request)
    {
    	$stock_id = unserialize($request->stock_id);

    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
    	foreach($simList as $sim_data){    		    		
    		$sim_subscription_id = '';
    		if(is_null($sim_data->user_id)){
    			$status[$sim_data->stock_id] = '';
    			continue;
    		}    		
    		$cli_number = $sim_data->stock->phone_number;
    		$auto_plan_id = $sim_data->autoplan_id;
    		$customer_id = DB::table('tbl_dealers')
                            ->where('id', $sim_data->stock->dealer_id)->value('customer_id');

    		$user = User::where('id', $sim_data->user_id)->first();
    		$user_data = $user->userDetail;	

    		if(!is_null($user_data->sim_subscription_id)){
    			$status[$sim_data->stock_id] = $user_data->sim_subscription_id;
    			continue;	
    		}

    		$plan = DB::table('auto_plan')
    		->select('auto_plan.plan_id','tbl_plans.sim_billing_plan','next_renewal',
    			'total_amount','user_list')
    		->join('tbl_plans', 'auto_plan.plan_id', '=' ,'tbl_plans.id')
    		->where('auto_plan.id', $auto_plan_id)->first();

    		if(is_null($user_data->sim_subscription_id)){					 			
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
				// $response = json_decode('{"CustomerOrderId":"1050091279","Subscription":{"CustomerId":"1050077251","SubscriptionId":"1050077253","AccountId":"1065000000000000747","StartDate":"2019-08-15T15:48:33.3795171+02:00","EndDate":{},"Status":"Active","StatusReason":{},"Promotions":{},"Services":[{"ServiceId":"1050063957","Category":"CORESERVERCHARGING","Status":"active","ExternalId":{},"StartDate":"2019-08-15T15:48:32.9435134+02:00","EndDate":{},"ServiceCharacteristics":[null]}],"Products":[{"ProductId":"1003000280","ProductOfferingId":"1019000245","SubscriptionProductAssnId":"1050000000000006675","ProductChargePurchaseId":"1006000221","ProductCharacteristics":[null],"StartDate":"2019-08-15T15:48:32.9454952+02:00","EndDate":{},"RecurringAmount":{"Amount":"0","Currency":"GBP"},"NonRecurringAmount":{"Amount":"0","Currency":"GBP"},"UnbilledBalanceAmount":{"Amount":"0","Currency":"GBP"}}],"DeliveryAddress":{"Address":"unknown","HouseExtension":{},"HouseNo":"unknown","City":"unknown","ZipCode":"unknown","State":"unknown","CountryId":"76","ExternalAddressId":{}}},"orderCode":"6725390130994741271","resultType":"Ok","resultCode":"0","messages":["Order submitted"]}');		

    			if($response->resultType == 'Ok' && $response->resultCode == 0){
    				$log_data = array(
    					'user_id' => $user->id,
    					'msisdn' => $cli_number,
    					'category' => 'BUNDLE_SUBSCRIPTION',
    					'value' => $plan->total_amount,
    					'reference_id' => $response->orderCode
    				);
    				DB::table('avoo_sim_log')->insert($log_data);

    				$sim_subscription_id = $response->Subscription->SubscriptionId;			    	
    				DB::table('user_data')->where('user_id', $user->id)
    				->update(['sim_subscription_id' => $sim_subscription_id]);	

    				DB::table('user_plans')->insert(['user_id' => $user->id, 'plan_id' => $plan->plan_id, 'status' => 1, 'plan_type' => 'sim']);		    	
    			}
    		}			

    		$status[$sim_data->stock_id] = $sim_subscription_id;
    	}

    	return view('activation.subscribe', compact('simList','status'))->render();
    }

    public function create_sippy_account(Request $request)
    {
        $process_flag = $register_notify = 0;
    	$stock_id = unserialize($request->stock_id);
    	$request_id = json_decode($request->request_id);        
    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	$simList = SimList::whereIn('stock_id', $stock_id)->get();
    	foreach($simList as $sim_data){     		  	
    		if(is_null($sim_data->user_id)){
    			$status[$sim_data->stock_id] = '';
    			continue;
    		}

    		$cli_number = $sim_data->stock->phone_number;
            $trust_number = '+'.$cli_number;
    		$auto_plan_id = $sim_data->autoplan_id;
    		$txn_id = $sim_data->auto_plan->transaction_id;
            $payment_method = $sim_data->sim_request->payment->payment_method; 
    		$card_type = $sim_data->auto_plan->card_type;
    		$switch_billing = $sim_data->auto_plan->plan->switch_billing_plan;

    		$parent_id = $sim_data->sim_request->user_id; 

    		$customer_id = DB::table('tbl_dealers')
    		->where('id', $sim_data->stock->dealer_id)->value('customer_id');

    		$plan = DB::table('auto_plan')->select('next_renewal','user_list')
    		->where('auto_plan.id', $auto_plan_id)->first();

    		$user = User::where('id', $sim_data->user_id)->first();		
    		$user_data = $user->userDetail;	

    		if(is_null($user_data->sim_account_id) || is_null($user_data->sim_subscription_id)){
    			$status[$sim_data->stock_id] = '';
    			continue;
    		}

    		$country = Country::join('switch_template','switch_id','=','switch_template.id')
    		->where('country.id',$user->country_id)->first();

			//Switch Activation Process
    		if(is_null($user->i_account)){								                      
    			$data = [ 'username' => $user_data->auth_name, 'a_class' => $country->a_class,
    			'tariff' => $country->tariff, 'timezone_value' => $country->timezone_value,
    			'balance' => $country->default_balance, 'phone' => $cli_number,
    			'translation_rule' => $country->translation_rule, 'cli_number' => $cli_number,
    			'export_type' => $country->export_type, 'currency' => $country->currency,
    			'vm_password' => $user_data->vm_password, 'notify_email' => ($user->email)?:'',
    			'first_name'=> ($user->first_name)?:'', 'last_name'=> ($user->last_name)?:'',
    			'company_name' => $user_data->username, 'billing_plan' => $switch_billing, 
    			'routing_group' => $country->routing_group ];	              
    			$xml_data = SwitchHelper::switch_create_account_xml($data);
    			$temp =  SwitchHelper::call_switch_api($xml_data);
                // $temp = []; 
    			$switch_status = 'F';
    			if (array_key_exists("fault", $temp)) {
    				$i_account = NULL;	               
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
    				}
    			}

    			if($switch_status == 'S'){   
    				DB::table('trusted_numbers')->insert(['user_id' => $user->id, 'trusted_number' => $trust_number, 'verified' => 1, 'api_token' => Str::random(90)]);

    				$xml_data = SwitchHelper::switch_add_cli_xml($i_account, $cli_number);
    				$temp = SwitchHelper::call_switch_api($xml_data);

    				$remoteips = DB::table('remoteips')->where('status', 1)->get();
    				if($remoteips){
    					$auth_rule = array('i_account' => $i_account, 'cli_number' => $cli_number);
    					foreach($remoteips as $remoteip){
    						$auth_rule['remote_ip'] = $remoteip->ip_address;
    						$xml_data = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
    						$temp =  SwitchHelper::call_switch_api($xml_data);
    					}
    				}

    				User::where('id', $user->id)
    				->update(['i_account' => $i_account, 'switch_id' => $country->switch_id, 'status' => 1]);

    				DB::table('user_data')->where('user_id', $user->id)->update(['i_account' => $i_account, 'register_status' => 1 ]);	

    				SimList::where('id',$sim_data->id)->update(['reg_status' => 1]);
    				$user_list = (!is_null($plan->user_list))? $plan->user_list.',': '';
    				$auto_plan_data['user_list'] = $user_list.$user->id;

    				if(is_null($plan->next_renewal)){
    					$auto_plan_data['status'] = 1;
    					$auto_plan_data['next_renewal'] = Carbon::now()->addDays(30)->format('Y-m-d');
                        if($user->id == $parent_id){
                            $register_notify = 1;
                        }
    				}
    				DB::table('auto_plan')->where('id', $auto_plan_id)->update($auto_plan_data);
                }
                $process_flag = 1;
    		}else{
    			$i_account = $user->i_account;	        	
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
					$xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$sim_data->credit,$country->currency);
					$temp =  SwitchHelper::call_switch_api($xml_data);
					// $temp = [];
					if (array_key_exists("fault", $temp)) {
						$payment['status'] = '2';
					} else {  
						$payment['status'] = '1';
						if(DB::table('account_balance')->where('user_id',$user->id)->exists()){
							DB::table('account_balance')->where('user_id', $user->id)
							->increment('balance_amount', $sim_data->credit);
						}else{
							DB::table('account_balance')->insert(['user_id' => $user->id,'balance_amount' => $sim_data->credit, 'balance_minutes'=> 0]);
						}											
					} 
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
        TblPorting::updateOrCreate(
            ['stock_id' => $request->stock_id],
            ['pac_number' => $request->pac, 'porting_to' => $request->port_to, 'status' => $request->status]
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
    * Show details of sim in a list
    */
    public function update_porting_request(Request $request)
    {
        $params = [];
        parse_str($request->data, $params);      
        $data['pac_number'] = $params['pac_number'];
        $data['porting_to'] = $params['porting_to'];
        $data['provider'] =  ($params['provider'] != 'other') ? $params['provider'] : $params['other_provider'];
        $data['reference_id'] = $params['reference_id'];            
        $data['expected_date'] = $params['expected_date'];
                
        $step = $request->step+1;
        $data['status'] = $step;
        $respo = TblPorting::where('id', $params['id'])->update($data);

        $port = TblPorting::where('id', $params['id'])->first();

        $html = view('porting-wizard', compact('port'))->render();

        return [ 'html' => $html ];         
    }
}
