<?php

namespace App\Http\Controllers;

use DB;
use Str;
use Auth;
use Hash;
use Crypt;
use Helper;
use Carbon;
// use Redirect;
use SwitchHelper;
use App\Models\User;
use App\Models\AutoPlan;
use App\Models\Country;
use App\Models\SimList;
use App\Models\SimStock;
use App\Models\UserData;
use App\Models\SimRequest;
use Illuminate\Http\Request;


class DealerController extends Controller
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
    * Dealer List
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_dealers(){     
    	$dealers = DB::table('tbl_dealers')->get();
    	return view('dealer-list', compact('dealers'));
    }

    /**
    * Dealer Create or Edit
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_edit_dealer($id=''){
    	if($id){
	    	$dealer = DB::table('tbl_dealers')->whereId($id)->first();
	    	return view('edit-dealer', compact('dealer'));
	    }else{
	    	return view('create-dealer');
	    }
    }
    
    /**
    * Dealer Create or Edit
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_update_dealer(Request $request)
    {	
    	$data['first_name'] = $request->first_name;
    	$data['last_name'] = $request->last_name;
    	$data['telephone'] = $request->telephone;
    	$data['email'] = $request->email;
    	$data['doc_type'] = $request->doc_type;
    	$data['doc_number'] = $request->doc_number;
    	$data['postal_code'] = $request->postal_code;
    	$data['house_no'] = $request->house_no;
    	$data['street'] = $request->street;
    	$data['city'] = $request->city; 
    	$data['state'] = $request->state;    	
    	$data['nationality'] = 'GB';
    	$data['country_id'] = '76';
    	$data['language'] = 'eng';
    	$data['comments'] =  $request->comments;

    	DB::beginTransaction();
    	$customer = new \stdClass();
    	if($request->dealer_id){
    		DB::table('tbl_dealers')->where('id', $request->dealer_id)->update($data);
			$dealer = DB::table('tbl_dealers')->where('id', $request->dealer_id)->first();	
			$method = 'patch';					
			$customer = (object) array(
				'CustomerId' => $dealer->customer_id,				
			);			
    	}else{
    		$dealer_id = DB::table('tbl_dealers')->insertGetId($data);
    		$dealer = DB::table('tbl_dealers')->where('id', $dealer_id)->first();
    		$method = 'post';    		   	
    	}

		$customer->CustomerData = (object) array(    		  	
    		'ExternalCustomerId' => (string) $dealer->id,
    		'FirstName' => $dealer->first_name,
    		'LastName' => $dealer->last_name,
    		'LastName2' => $dealer->last_name,		
    		'CustomerDocumentType' => $dealer->doc_type,
    		'DocumentNumber' => $dealer->doc_number,
    		'Telephone' => $dealer->telephone,
    		'Email' => $dealer->email,
    		'LanguageName' => $dealer->language,   		
    		'FiscalAddress' => (object) array(
		    		'Address' => $dealer->street,
		    		'City' => $dealer->city,
		    		'CountryId' => (string) $dealer->country_id,
		    		'HouseNo' => $dealer->house_no,
		    		'State' => $dealer->state,
		    		'ZipCode' => $dealer->postal_code
		    	),
			'CustomerAddress' => (object) array(
					'Address' => $dealer->street,
					'City' => $dealer->city,
					'CountryId' => (string) $dealer->country_id,
					'HouseNo' => $dealer->house_no,
					'State' => $dealer->state,
					'ZipCode' => $dealer->postal_code
				),
			'Nationality' => $dealer->nationality,					
		);		
		$customer->channel = 'Web';
		$customer->comments = $dealer->comments;
    	
    	
    
    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	$end_point = '/core/customers?MVNO='.$mvno_key;
   		   		    	
    	$response = Helper::call_sim_process_api($end_point, json_encode($customer), $method);

    	if($response->resultType == 'Ok' && $response->resultCode == 0){
    		DB::commit();
    		$message = 'Dealer Updated succesfully';
    		if(isset($response->customerId)){
    			DB::table('tbl_dealers')->where('id', $dealer->id)
    				->update(['customer_id' => $response->customerId,'order_id' => $response->orderCode]);
    			$message = 'Dealer created succesfully';	
    		}   
    		return redirect('/dealer/'.$dealer->id)->with('message', $message); 		    
    	}else{
    		DB::rollback();
    		return redirect('/dealers')->with('error', $response->messages[0]);
    	}	
    }

    /**
    * Dealer Create or Edit
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    /*public function delete_dealer(Request $require)
    {
    	echo $request->dealer_id;
    }*/


    /**
    * Add Dealer 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_dealer(Request $request){
    	die();
        ini_set('memory_limit', '-1');
	
        // $logs = DB::table('avoo_sim_log')->where('category', 'BUNDLE_SUBSCRIPTION')->get();
        // foreach($logs as $log){
        //     $sim_data = SimList::where('user_id', $log->user_id)->first();
        //     $auto = AutoPlan::where('id', $sim_data->autoplan_id)->first();
        //     DB::table('avoo_sim_log')->where('id', $log->id)->update(['stock_id' => $sim_data->stock_id, 'plan_id' => $auto->plan_id]);
        // }

        // $subs = AutoPlan::where('status', 1)->get();
        // foreach($subs as $sub){
        //     if($sub->bundle_id == 0){
        //         $plan = DB::table('tbl_plans')->where('id', $sub->plan_id)->first();
        //     }else{
        //         $plan = DB::table('tbl_bundles')->where('id', $sub->bundle_id)->first();
        //     }
        //     $tax = Helper::get_option('country_tax');
        //     $tax_amount = ($plan->sell_price * $tax)/100;
        //     $amount = $plan->sell_price - $tax_amount;
        //     // AutoPlan::where('id', $sub->id)->update(['amount' => $amount,'tax' => $tax_amount,'total_amount' => $plan->sell_price]);
        //     echo $sub->total_amount. '---'. $plan->sell_price.chr(10);
        // }

        // $user = User::where('id', '10102')->first();

        // $payment = ['user_id' => $user->id, 'pay_to' => '0', 'transaction_id' => '', 
        //         'buy_price' => '0', 'amount' => '10', 'tax_amount' => '0', 
        //         'total_amount' => '10', 'payment_method' => 'Credit Transfered',
        //         'payment_for' => 'Credit Added - From RingtoIndia','description' => 'credit added sourse :(RingtoIndia ID : 190011)'
        //         ];                  
        // $i_account = $user->i_account;
        // $method = 'accountCredit'; //accountAddFunds
        // $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,10,'GBP');
        // $temp =  SwitchHelper::call_switch_api($xml_data);
        // // $temp = [];
        // if (array_key_exists("fault", $temp)) {
        //     $payment['status'] = '2';
        // } else {  
        //     $payment['status'] = '1';                       
        //     DB::table('account_balance')->where('user_id', $user->id)
        //         ->increment('balance_amount', 10);               
        // } 
        // DB::table('user_payments')->insert($payment);

        $user_id = 10067;
        $mvno_key   = Helper::get_option('bundle_mvno_key');
        $user = User::where('id', $user_id)->first();        
        $from = "";//DB::table('user_calls')
                        // ->where('user_id', $user_id)->where('history_from', 2)
                        // ->max('connect_date');
        if($from == ""){
            $from = $user->created_at;
        }
        if($from != ""){
            $fromdate = Carbon::parse($from)->format('Y-m-d H:i:s');
            $todate = Carbon::now()->format('Y-m-d H:i:s');
            $msisdn = $user->msisdn->phone_number;   
            // $msisdn = $user->msisdn->temp_number;            
            $params = 'msisdn='.$msisdn.'&fromDate='.urlencode($fromdate).'&toDate='.urlencode($todate);
            $end_point = '/superapi/usage?'.$params.'&mvno='.$mvno_key;
            echo  $end_point;
            $response = Helper::call_sim_process_api($end_point,'','get');

            if($response->message == 'Success' && $response->statusCode == 0){ 
                if(!empty($response->usages)){  
                    $data = 0;
                    foreach($response->usages as $history){
                        if($history->serviceType == 'DATA' || $history->serviceType == 'SMS_MO'){
                            $date = Carbon::parse($history->date)->format('Y-m-d H:i:s');
                            $usage_exist = DB::table('usage_history')
                                ->where('date', $date)->where('user_id', $user_id)->exists();
                            if($usage_exist){
                                continue;
                            }

                            $data_history = ['user_id'=> $user_id,'from_number'=>$history->from,'to_number'=>$history->to,'date'=>$date,'duration'=>$history->duration,'amount'=> $history->amount,'service_type' => $history->serviceType ];
                            DB::table('usage_history')->insert($data_history);
                            // $data += $history->duration; 
                        }

                        // $connect = Carbon::parse($history->date)->format('Y-m-d H:i:s');
                        // $duration = $history->duration;
                        // $disconnect = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));
                        // $history_exist = DB::table('user_calls')
                        //     ->where('connect_date', $connect)->where('history_from', 2)
                        //     ->where('user_id', $user_id)->exists();
                        // if($history_exist){
                        //     continue;
                        // }

                        // if($history->serviceType !== 'VOICE_MO' && $history->serviceType !== 'VOICE_MT'){                           
                        //     continue;
                        // }

                        // $service_type = ($history->serviceType == 'VOICE_MO')?'1':'2';
                        // if($history->to == '447973101233'){
                        //     $service_type = 3;
                        // }
                        // $i_cdr = time().mt_rand(1000, 9999);
                        // $simhis_data = ['user_id' =>$user_id, 'connect_date' =>$connect, 'disconnect_date' => $disconnect, 'cli' => $history->from,   'cli_in' => $history->from, 'cld'=> $history->to, 'i_cdr' => $i_cdr, 'duration' => $duration, 'cost' => $history->amount, 'history_from' => 2, 'created_at'=> $todate, 'service_type'=> $service_type];                            
                        // DB::table('user_calls')->insert($simhis_data);
                    }
                    // echo $data;
                }
            }
        }

        die();
        $mvno_key = Helper::get_option('bundle_mvno_key');
        $end_point = '/superapi/accountInformation/447988008525?mvno='.$mvno_key;
               
        $response = Helper::call_sim_process_api($end_point, '', 'get');
        print_r($response);
        die();
    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	//$sim_data = DB::table('tbl_sim_stock')->where('id', '10002')->first();

		$dealer = DB::table('tbl_dealers')->where('id', 2)->first();

		$end_point = '/core/accounts?MVNO='.$mvno_key;
    	$data = new \stdClass();
    	$data->AccountInfo = (object) array(
    		'AccountType' => 'Prepaid',
    		'CustomerId' => (string)$dealer->customer_id,
    		'ExternalAccountId' => 'ecomo_test_5',
    		'AccountStatus' => 'Active',     		
    		'Names' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
    		'Descriptions' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
    		'AccountCurrency' => 'GBP',
    		'Balance' => 1,
    		'CreditLimit' => 1
        );
        
    	$response = Helper::call_sim_process_api($end_point, json_encode($data));
    	print_r($response).chr(10);
    	echo 'Accout : '.json_encode($response).chr(10);
 
    	if($response->resultType == 'Ok' && $response->resultCode == 0){ 			
			// $plan = DB::table('tbl_plans')->where('id', )->first(); 	
 			$sub_end_point = '/core/subscriptions?MVNO='.$mvno_key;
 			$sub_data = (object)[
			    'CustomerId' => $dealer->customer_id,
				'Items' => [ (object)[
					'AccountId' => $response->AccountId,
					'ProductOfferings' => [
						(object)[
							'ProductOfferingId' => (int)'1019000245',//$plan->plan_id,
							'OrderedProductCharacteristics' => [
								(object)[
									'Name' => 'MSISDN',
									'Value' => '447421973366'
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

			$sub_response = Helper::call_sim_process_api($sub_end_point, json_encode($sub_data));
			print_r($sub_response).chr(10);
			echo 'Subscription : '.json_encode($sub_response).chr(10);

			$topup_end_point = '/superapi/topup?mvno='.$mvno_key;
	 		$topup_data = (object)[
	 			'msisdn' => '447421973366',
	 			'amount' => 1000
	 		];

	 		$topup_response = Helper::call_sim_process_api($topup_end_point, json_encode($topup_data));
			print_r($topup_response).chr(10);
			echo 'Topup';
			die();

    	}
    	die();



		$usage_end_point = '/superapi/usage?mvno='.$mvno_key.'&';
 		$usage_data['msisdn'] = $sim_data->phone_number;
 		$usage_data['fromDate'] = '2019-08-07';
 		$usage_data['toDate'] = '2019-08-09';
 		$usage_end_point .= http_build_query($usage_data);
 		// echo $usage_end_point;
 		$topup_response = Helper::call_sim_process_api($usage_end_point, '', 'get');
		print_r($topup_response);
		die();

		
		$end_point = '/core/customers/1065076890?mvno='.$mvno_key;
    	$response = Helper::call_sim_process_api($end_point,'','get');
    	print_r($response);

    	die();
		$topup_end_point = '/superapi/topup?mvno='.$mvno_key;
 		$topup_data = (object)[
 			'msisdn' => $sim_data->phone_number,
 			'amount' => 100
 		];

 		$topup_response = Helper::call_sim_process_api($topup_end_point, json_encode($topup_data));
		print_r($topup_response);
		die();

    	$response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [ {} ] }');

    	if($response->resultType == 'Ok' && $response->resultCode == 0){	
 			// $plan = DB::table('auto_plan')->select('auto_plan.id', 'user_id', 'user_list',
 			// 			'auto_plan.plan_id','tbl_plans.plan_id as bundle_plan')
	 		// 			->join('tbl_plans', 'auto_plan.plan_id', '=' ,'tbl_plans.id')
	 		// 			->where('request_id', $request->request_id)->first(); 				
 			$plan = DB::table('tbl_plans')->where('id', 2)->first(); 	
 			$sub_end_point = '/core/subscriptions?MVNO='.$mvno_key;
 			$sub_data = (object)[
			    'CustomerId' => $dealer->customer_id,
				'Items' => [ (object)[
					'AccountId' => $response->AccountId,
					'ProductOfferings' => [
						(object)[
							'ProductOfferingId' => (int)$plan->plan_id,
							'OrderedProductCharacteristics' => [
								(object)[
									'Name' => 'MSISDN',
									'Value' => $sim_data->phone_number
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

			$sub_response = Helper::call_sim_process_api($sub_end_point, json_encode($sub_data));
			print_r($sub_response);

			//stdClass Object ( [CustomerOrderId] => 1060091454 [Subscription] => stdClass Object ( [CustomerId] => 1065076890 [SubscriptionId] => 1065076891 [AccountId] => 1050000000000000854 [StartDate] => 2019-08-08T17:45:02.0108988+02:00 [EndDate] => stdClass Object ( ) [Status] => Active [StatusReason] => stdClass Object ( ) [Promotions] => stdClass Object ( ) [Services] => Array ( [0] => stdClass Object ( [ServiceId] => 1060074277 [Category] => CORESERVERCHARGING [Status] => active [ExternalId] => stdClass Object ( ) [StartDate] => 2019-08-08T17:45:00.7548247+02:00 [EndDate] => stdClass Object ( ) [ServiceCharacteristics] => Array ( [0] => ) ) ) [Products] => Array ( [0] => stdClass Object ( [ProductId] => 1003000280 [ProductOfferingId] => 1019000245 [SubscriptionProductAssnId] => 1060000000000006552 [ProductChargePurchaseId] => 1006000221 [ProductCharacteristics] => Array ( [0] => ) [StartDate] => 2019-08-08T17:45:00.7578259+02:00 [EndDate] => stdClass Object ( ) [RecurringAmount] => stdClass Object ( [Amount] => 0 [Currency] => GBP ) [NonRecurringAmount] => stdClass Object ( [Amount] => 0 [Currency] => GBP ) [UnbilledBalanceAmount] => stdClass Object ( [Amount] => 0 [Currency] => GBP ) ) ) [DeliveryAddress] => stdClass Object ( [Address] => unknown [HouseExtension] => stdClass Object ( ) [HouseNo] => unknown [City] => unknown [ZipCode] => unknown [State] => unknown [CountryId] => 76 [ExternalAddressId] => stdClass Object ( ) ) ) [orderCode] => 6722822552307105811 [resultType] => Ok [resultCode] => 0 [messages] => Array ( [0] => Order submitted ) )
	    	die();

	    	  	//   	$end_point = '/core/subscriptions/1050077253?MVNO=902450';  	
		  		//   	$sub_response = Helper::call_sim_process_api($end_point,'','get');
				// print_r(json_encode($sub_response));
				// die();
    	}

    	$sim_data = DB::table('tbl_sim_stock')->where('id', '5003')->first();
		$dealer = DB::table('tbl_dealers')->where('id', $sim_data->dealer_id)->first();
    	 //$sim_data->phone_number
    	
    	die();
    	//dealer 

    	$dealer = DB::table('tbl_dealers')->where('id', 2)->first();

    	$data = new \stdClass();
    	$data->CustomerData = (object) array(
    		'ExternalCustomerId' => (string) $dealer->id,
    		'FirstName' => $dealer->first_name,
    		'LastName' => $dealer->last_name,
    		'LastName2' => $dealer->last_name,		
    		'CustomerDocumentType' => $dealer->doc_type,
    		'DocumentNumber' => $dealer->doc_number,
    		'Telephone' => $dealer->telephone,
    		'Email' => $dealer->email,
    		'LanguageName' => $dealer->language,   		
    		'FiscalAddress' => (object) array(
		    		'Address' => $dealer->street,
		    		'City' => $dealer->city,
		    		'CountryId' => (string) $dealer->country_id,
		    		'HouseNo' => $dealer->house_no,
		    		'State' => $dealer->state,
		    		'ZipCode' => $dealer->postal_code
		    	),
			'CustomerAddress' => (object) array(
					'Address' => $dealer->street,
					'City' => $dealer->city,
					'CountryId' => (string) $dealer->country_id,
					'HouseNo' => $dealer->house_no,
					'State' => $dealer->state,
					'ZipCode' => $dealer->postal_code
				),
			'Nationality' => $dealer->nationality,
			'comments' => $dealer->comments
		);
    
    	$mvno_key = Helper::get_option('bundle_mvno_key');
    	$end_point = '/core/customers?MVNO='.$mvno_key;
   	

    	$response = Helper::call_sim_process_api($end_point, json_encode($data));
    	// $response = '{"customerId":"901000315","orderCode":"6722352966695780357","resultType":"Ok","resultCode":"0","messages":[{}]}';
    	print_r($response);
    }
}
