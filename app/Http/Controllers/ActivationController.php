<?php

namespace App\Http\Controllers;

use DB;
use Str;
use Mail;
use Auth;
use Hash;
use Crypt;
use Carbon;
use Helper;
use Utils;
use Log;
use AttHelper;
use App\Helpers\DwpHelper;
use SwitchHelper;
use GlobalSim;
use App\Models\User;
use App\Models\Admins;
use App\Models\SimList;
use App\Models\Country;
use App\Models\AutoPlan;
use App\Models\SwitchLog;
use App\Models\VerifyUser;
use App\Models\BridgeServer;
use App\Models\PlanCommission;
use App\Models\UserCommission;
use App\Models\NotificationLog;
use App\Models\PaymentCommission;

use App\Mail\OrderComplete;
use App\Mail\Registration;


use App\Models\SimStock;
use App\Models\UserData;
use App\Models\TblPorting;
use App\Models\SimRequest;
use App\Models\UserPayment;
use App\Models\AutoRecharge;
use App\Models\UserCreditCard;
use App\Models\Account;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
use Illuminate\Support\Facades\Validator;


class ActivationController extends Controller
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
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function show_sim_list(Request $request)
    {
    	if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
    	}

        $step = 1;
    	$sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
        $currency = Helper::get_option('currency_symbol');
        foreach($sim_list as $sim_data){
            $provider = $sim_data->stock->provider;
            if(!$sim_data->stock->verified){
                if($provider == 'E_SIM'){
                    try {
                        $provision_status =  $sim_data->provision;
                        if($provision_status == 4){
                            $iccid          = $sim_data->stock->sim_number;
                            
                            // $getmsisdn      = GlobalSim::AssignMsisdn($iccid);
                            // if($getmsisdn == false){
                            //     return response()->json(['error' => true, 'message' =>'Failed to assign msisdn']);
                            // }
                            // if($getmsisdn['@attributes']['status'] == 'fail'){
                                $getsiminfo     = GlobalSim::getSimInfo($iccid);
                                if($getsiminfo == false){
                                    return response()->json(['error' => true, 'message' =>'Failed to fetch sim info']);
                                }
                                if($getsiminfo['@attributes']['status'] == 'success'){
                                    if(gettype($getsiminfo['Sim']['ActiveProfileLastUsed']) == 'array'){
                                        return response()->json(['error' => true, 'message' =>'Inactive Sim profile.']);  
                                    }else{
                                        $msisdn         = $getsiminfo['Sim']['PublicNumber'];
                                        $esimuser       = $getsiminfo['Sim']['UserId'];
                                        $esimcustomer   = $getsiminfo['Sim']['CustomerId'];
                                    }
                                }else{
                                    return response()->json(['error' => true, 'message' =>'Inactive Sim profile.']);  
                                }
                            // }else{
                            //     $msisdn  = $getmsisdn['STATUS_Response']['MSISDN'];
                            // }
                            SimStock::whereId($sim_data->stock->id)->update(['phone_number'=>$msisdn,'verified'=>1]);
                            SimList::whereId($sim_data->id)->update(['esim_customer'=>$esimcustomer,'esim_user'=>$esimuser]);
                        }else{
                            return response()->json(['error' => true, 'message' =>'Please complete the provision' ]);  
                        }
                    } catch (\Exception $e) {
                        Log::error('ASSIGNMSISDN',[
                            'order' => $sim_data->sim_request->order_id,
                            'simnumber' => $sim_data->stock->sim_number,
                            'error' =>   $e->getMessage()
                        ]);
                        return response()->json(['error' => true, 'message' =>'Assign number failed..','err' => $e->getMessage() ]);
                    }
                }
            }
        }
        $sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
        // if($sim_list[0]->stock->provider == 'EE'){
    	    $view = view('activation.list_wizard', compact('sim_list', 'currency','step'))->render();
        // }else{
        //     $user = User::where('id',$sim_list[0]->sim_request->user_id)->first();
        //     $gateways = ['Paypal'=>1,'Braintree'=>2];
        //     $gateway = $gateways[$sim_list[0]->auto_plan->gateway];
        //     $credit_cards = DB::table('user_credit_cards')
        //                         ->where(['user_id' => $user->id, 'gateway' => $gateway])->get();
        //     $view = view('activation.custom_list_wizard', compact('sim_list', 'currency','user','credit_cards','step'))->render();
        // }

        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Order Prorata Details
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function prorata_billing(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }
        $prorata_billing = 1;
        $date_from = ($request->date_from)?:date('Y-m-d');
        $date_to = ($request->date_to)?:date('Y-m-t');
        $simList = SimList::where('autoplan_id', $request->autoplan)->get();
        $user_id = $simList[0]->sim_request->user->id;
        $currency = $simList[0]->sim_request->user->country->currency_symbol;
        $cards = UserCreditCard::where('user_id', $user_id)->get();
        if($cards->isEmpty()){
            return response()->json(['error' => true, 'message' => 'Card not exist!']);
        }

        $html = view('modal-popup', compact('simList','cards','prorata_billing','date_from','date_to','currency'))->render();

        return response()->json(['error' => false, 'html' => $html]);
    }

    /**
    * Prorata Collectionn Process
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function prorata_billing_process(Request $request)
    {
        $date_from = $request->date_from;
        $autoplan_id = $request->autoplan;
        $card_id = Crypt::decrypt($request->credit_card);
        $plan = AutoPlan::where('id', $autoplan_id)->first();
        $simList = SimList::where('autoplan_id', $autoplan_id)->get();
        $user = User::find($simList[0]->sim_request->user->id);
        $card = UserCreditCard::where('id', $card_id)->first();
        $bill_amount = $buy_amount = 0; $endofday = date('t'); $cur_day = date('d',strtotime($date_from));
        foreach($simList as $sim){
            $sell_price = $sim->auto_plan->plan->sell_price;
            $buy_price = $sim->auto_plan->plan->buy_price;
            $remain_days = $endofday - $cur_day + 1;
            $pro_rata_bill = ($sell_price/$endofday) * $remain_days;
            $pro_rata_buy = ($buy_price/$endofday) * $remain_days;
            $bill_amount += $pro_rata_bill;
            $buy_amount += $pro_rata_buy;
        }

        $data['card_id'] = $card_id;
        $data['user_id'] = $user->id;
        $data['i_account'] = $user->i_account;
        $data['currency'] = $user->country->currency;
        $net_amount = 100/(100+$user->country->tax) * $bill_amount;
        $vat_amount = $bill_amount - $net_amount;
        $data['net_amount'] = Helper::number_format($net_amount);
        $data['vat_amount'] = Helper::number_format($vat_amount);
        $data['total_amount'] = Helper::number_format($bill_amount);
        $data['buy_price'] = Helper::number_format($buy_amount);
        $data['discount_amount'] = 0;
        $data['discount_coupon'] = '';
        $data['payment_for'] = 'Plan Subscription Pro-rata Payment';
        $data['description'] = 'Plan Subscription Pro-rata Payment - processed by '. Auth::user()->first_name.' '.Auth::user()->last_name;
        $data['category'] = 'sim';

        if($card->gateway == 1){
            $response = Helper::paypal_payment_process($data);
        }elseif($card->gateway == 2){
            $response = Helper::braintree_payment_process($data);
        }elseif($card->gateway == 3){
            $response = Helper::stripe_payment_process($data);
        }else{
            return response()->json(['error' => true, 'message' => 'Invalid Card, please add new card..']);
        }

        if($response['status']){
            $txn_id = $response['transaction_id'];
            $payment_id = $response['payment_id'];
            $renew_on = date('Y-m-01',strtotime('next month'));
            $now = Carbon::now()->format('Y-m-d H:i:s');
            if($card->gateway == 'Paypal'){
                $auto_plan_data['transaction_id'] = $txn_id;
                DB::table('user_credit_cards')->where('id', $card_id)->update(['transaction_id' => $txn_id]);
            }
            $auto_plan_data['next_renewal'] = $renew_on;
            $auto_plan_data['switch_billing_plan'] = $plan->plan->switch_billing_plan;
            $auto_plan_data['adv_pay'] = $plan->adv_pay+1;
            DB::table('auto_plan')->where('id', $autoplan_id)->update($auto_plan_data);
            return response()->json(['error' => false, 'message' => $response['message'], 'next_renewal' => $auto_plan_data['next_renewal']]);
        }else{
            return response()->json(['error' => true, 'message' => 'Payment Failed. Please try again..']);
        }
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function terms_and_condition(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }
        if($request->wizard_error){
            return response()->json(['error' => true, 'message' => 'Update Phone Number OR Invalid data ']);
        }

        $step = $request->step;
        $sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
        $currency = Helper::get_option('currency_symbol');
        $view = view('activation.list_wizard', compact('sim_list', 'currency','step'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function activate(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }
        if($request->wizard_error != 0){
            return response()->json(['error' => true, 'message' => 'Invalid data']);
        }

        $step = $request->step;
        $stock_id = unserialize($request->stock_id);
        $mvno_key = Helper::get_option('bundle_mvno_key');
        $customer_id = Helper::get_option('mvno_customer_id');
        $sim_list = SimList::whereIn('stock_id', $stock_id)->get();
        foreach($sim_list as $sim_data){
            $address = json_decode($sim_data->sim_request->billing_address);
            $parent_id = $sim_data->sim_request->user_id;
            $cli_number = $sim_data->stock->phone_number;
            $phone_number = '+'.$cli_number;
            $user = User::where('stock_id', $sim_data->stock_id)->first();
            $admin = Admins::where('promocode',$sim_data->sim_request->promocode)->first();
            $dealer_id = ($admin)?$admin->id:1;

            if(!$user){
                $user = User::where('id', $parent_id)->first();
                $ip_address = $user->userDetail->ip_address;
                // print_R($user);
                // die();
                if(!is_null($user->stock_id)){
                    $user = User::where('phone', $phone_number)->first();
                    if(!$user){
                        $trust = DB::table('trusted_numbers')->where('trusted_number', $phone_number)->first();
                        if($trust){
                            $trust_user = User::where('id', $trust->user_id)->first();
                            $xml_data = SwitchHelper::switch_delete_cli_xml($trust_user->i_account, $trust->trusted_number);
                            $switch = DB::table('switch_template')->whereId($trust_user->switch_id)->first();
                            $temp =  SwitchHelper::call_switch_api($xml_data, $switch->customer, $switch->token);

                            if (array_key_exists("fault", $temp)) {
                                return response()->json(['error' => true, 'message' => 'Trusted number already exist!']);
                            } else {
                                DB::table('trusted_numbers')->where('id', $trust->id)->delete();
                            }
                        }
                    }

                    if(!$user || !is_null($user->stock_id)){
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
                        $country  = Country::whereId(238)->first();
                        $username = Helper::unique_code(16);
                        $username = config('settings.app_prefix').'-'.$country->short_code.'-'.$username;

                        $vm_password = Helper::random(10, implode(range('0','9')));
                        $vp_password = Helper::unique_code(10);

                        $call_settings = ['accessnumber_support' => $country->accessnumber_support, 'wifi_support' => $country->wifi_support, 'callback_support' => $country->callback_support, 'conference_support' => $country->wifi_support, 'bundle_o' => 0];
                        $call_settings = json_encode($call_settings);

                        $user_data = ['user_id' => $user->id, 'auth_name' => $username, 'vm_password' => $vm_password, 'vp_password' => $vp_password, 'address' => $address->street, 'city' => $address->city, 'call_settings' => $call_settings, 'state' => $address->country, 'postal_code' => $address->postal_code, 'ip_address' => $ip_address, 'user_platform' => config('settings.app_prefix'), 'register_status' => 0];
                        DB::table('user_data')->insert($user_data);
                        DB::table('account_balance')->insert(['user_id' => $user->id,'balance_amount' => 0, 'balance_minutes'=> 0]);
                    }else{
                        User::where('id', $user->id)->update(['username' => $cli_number, 'phone' => $phone_number,'stock_id' => $sim_data->stock_id, 'parent_id' => $parent_id]);
                    }
                }else{
                    User::where('id', $parent_id)->update(['username' => $cli_number, 'phone' => $phone_number, 'alt_phone' => $user->phone, 'stock_id' => $sim_data->stock_id]);
                }
            }else{
                User::where('id', $user->id)->update(['username' => $cli_number, 'phone' => $phone_number]);
            }

            $provider = $sim_data->stock->provider;
            if($provider == 'EE'){
                $sim_account_id = $user->userDetail->sim_account_id;
                if(is_null($sim_account_id) || $sim_account_id == ''){
                    $end_point = '/core/accounts?MVNO='.$mvno_key;
                    $data = new \stdClass();
                    $data->AccountInfo = (object) array(
                        'AccountType' => 'Prepaid',
                        'CustomerId' => (string)$customer_id,
                        'ExternalAccountId' => config('settings.app_prefix').$user->id,
                        'AccountStatus' => 'Active',
                        'Names' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
                        'Descriptions' => [ (object)[ 'LanguageCode' => 'eng', 'Text' => 'Account' ] ],
                        'AccountCurrency' => 'GBP',
                        'Balance' => 1,
                        'CreditLimit' => 1
                    );

                    $response = Helper::call_sim_process_api($end_point, json_encode($data));
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
            }else if( $provider == 'E_SIM'){
                
                try {
                    /* Add/modify customer for Esim */
                    $setlimit      = new \stdClass();
                    $setlimit->bill_limit   = $sim_data->bill_limit;
                    $setlimit->warn_limit   = $sim_data->warn_limit;
                    $setlimit->lock_limit   = $sim_data->lock_limit;
                   
                    if($sim_data->esim_customer){
                        $modifycustomer  = GlobalSim::ModifyCustomer($user,$setlimit,$sim_data->esim_customer);
                        if($modifycustomer == false || $modifycustomer['@attributes']['status'] == 'fail'){
                            Log::error('ModifyCustomer',[
                                'user_id' => $user->id,
                                'error' =>   $modifycustomer
                            ]);
                            return response()->json(['error' => true, 'message' => 'Modify customer failed..']);
                        }
                        $esim_customer_id = $modifycustomer['customer']['id'];
                    }else{
                        $addcustomer  = GlobalSim::AddCustomer($user,$setlimit);
                        if($addcustomer == false ||  $addcustomer['@attributes']['status'] == 'fail'){
                            Log::error('AddCustomer',[
                                'user_id' => $user->id,
                                'error' =>   $addcustomer
                            ]);
                            return response()->json(['error' => true, 'message' => 'Adding customer failed..']);
                        }
                        $esim_customer_id = $addcustomer['customer']['id'];
                    }
                    /* END Customer for Esim */

                    /* Add/modify user for Esim */
                    if($sim_data->esim_user){
                        $modifyuser      = GlobalSim::ModifyUser($user,$esim_customer_id,$sim_data->esim_user);
                        if($modifyuser == false || $modifyuser['@attributes']['status'] == 'fail'){
                            Log::error('ModifyUser',[
                                'user_id' => $user->id,
                                'error' =>   $modifyuser
                            ]);
                            return response()->json(['error' => true, 'message' => 'Modify user failed..']);
                        }
                        $esim_user_id = $modifyuser['user']['id'];
                    }else{
                        $adduser      = GlobalSim::AddUser($user,$esim_customer_id);
                        if($adduser == false || $adduser['@attributes']['status'] == 'fail'){
                            Log::error('AddUser',[
                                'user_id' => $user->id,
                                'error' =>   $modifyuser
                            ]);
                            return response()->json(['error' => true, 'message' => 'Adding user failed..']);
                        }
                        $esim_user_id = $adduser['user']['id'];
                    }
                    /*  END                 */
                    $account_id = config('settings.app_prefix').$provider.$user->id;
                    DB::table('user_data')->where('user_id', $user->id)
                            ->update(['sim_account_id'=>$account_id,'esim_customer'=>$esim_customer_id,'esim_user'=>$esim_user_id,'bill_limit'=>$sim_data->bill_limit,'warn_limit'=>$sim_data->warn_limit,'lock_limit'=>$sim_data->lock_limit]);
                    $accounts[$sim_data->stock_id] = $account_id;
                } catch (\Exception $e) {
                    Log::error('ESIMACTIVATION',[
                        'user_id' => $user->id,
                        'error' =>   $e->getMessage()
                    ]);
                    return response()->json(['error' => true, 'message' => 'failed to activate account','error' =>   $e->getMessage()]);
                }
            }else if( $provider == 'O2' || $provider == 'EE_O2' || $provider == 'VUK'){
                $sim_account_id = $user->userDetail->site_id;
                // if(is_null($sim_account_id) || $sim_account_id == ''){
                //     $client       = DwpHelper::initiate_soap_client();
                //     if($client){
                //         $createuser   = DwpHelper::create_new_site($client,$user);
                //         if($createuser->status == 200){
                //             $sim_account_id = $createuser->siteId;
                //             DB::table('user_data')->where('user_id',$user->id)->update(['site_id'=>$sim_account_id]);
                //         }else{
                //            return response()->json(['error' => true, 'message' => $createuser->error]);
                //         }
                //     }else{
                //         return response()->json(['error' => true, 'message' => 'SoapClient error']);
                //     }
                // }
                $accounts[$sim_data->stock_id] = $sim_account_id;
                $account_id = config('settings.app_prefix').$provider.$user->id;
                DB::table('user_data')->where('user_id', $user->id)
                        ->update(['sim_account_id' => $account_id]);
            }else{
                $account_id = config('settings.app_prefix').$provider.$user->id;
                DB::table('user_data')->where('user_id', $user->id)
                        ->update(['sim_account_id' => $account_id]);
                $accounts[$sim_data->stock_id] = $account_id;
            }
            AutoPlan::whereId($sim_data->autoplan_id)->update(['start_date'=>Carbon::now()->format('Y-m-d')]);
            SimList::where('id', $sim_data->id)->update(['user_id' => $user->id]);
            User::where('id', $user->id)->update(['dealer_id' => $dealer_id]);
        }

        $view = view('activation.list_wizard', compact('sim_list','accounts','step'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function subscribe(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }

        $step = $request->step;
        $stock_id = unserialize($request->stock_id);
        $mvno_key = Helper::get_option('bundle_mvno_key');
        $customer_id = Helper::get_option('mvno_customer_id');
        $sim_list = SimList::whereIn('stock_id', $stock_id)->get();
        foreach($sim_list as $sim_data){
            $sim_subscription_id = '';
            if(is_null($sim_data->user_id)){
                $status[$sim_data->stock_id] = '';
                continue;
            }
            $cli_number = $sim_data->stock->phone_number;
            $trust_number = '+'.$cli_number;
            $auto_plan_id = $sim_data->autoplan_id;
            $promocode = $sim_data->sim_request->promocode;
            $provider = $sim_data->stock->provider;
            $user = User::where('id', $sim_data->user_id)->first();
            $user_data = $user->userDetail;

            if(!is_null($user_data->sim_subscription_id)){
                $status[$sim_data->stock_id] = $user_data->sim_subscription_id;
                continue;
            }

            $plan = DB::table('auto_plan')->select('auto_plan.plan_id','tp.sim_billing_plan','tp.buy_price','next_renewal','total_amount','user_list','auto_plan.status')
                    ->join('tbl_plans as tp', 'auto_plan.plan_id', '=' ,'tp.id')
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

                // $response = Helper::call_sim_process_api($end_point, json_encode($data));
                $response = json_decode('{"orderCode":"ee_custom","Subscription":{"SubscriptionId":"ee_custom_id"},"resultType":"Ok","resultCode":"0"}');
            }if(is_null($user_data->sim_subscription_id) && $provider == 'E_SIM'){
                if($sim_data->auto_plan->plan->sim_billing_plan != 0){
                    try {
                        $subscribe = new \StdClass;
                        $subscribe->bundle_id = $sim_data->auto_plan->plan->sim_billing_plan;
                        $subscribe->msisdn    = $sim_data->stock->phone_number;
                        $subscribe->date      = Carbon::parse($sim_data->activation)->format('Y-m-d H:i:s');
                        $subscribe->actfirstuse     = $sim_data->activate_onfirstuse;
                        $subscribe->sendsms         = $sim_data->send_sms;
                        $subscribe->takepayment     = $sim_data->take_payment;
                        $bundlesubscrib = GlobalSim::BundleSubscribe($subscribe);
                        if($bundlesubscrib == false || $bundlesubscrib['@attributes']['status'] == 'fail'){
                            Log::error('ESIMSUBSCRIPTION',[
                                'user_id' => $user->id,
                                'error' =>   $bundlesubscrib
                            ]);
                            return response()->json(['error' => true, 'message' =>'Bundle subscription failed']);
                        }
                        $subsrib_id = $bundlesubscrib['subscriptionid'];
                        DB::table('user_data')->where('user_id', $user->id)
                        ->update(['activate_onfirstuse' => $sim_data->activate_onfirstuse,'send_sms'=>$sim_data->send_sms,'take_payment'=>$sim_data->take_payment]);
                    } catch (\Exception $e) {
                        Log::error('ESIMSUBSCRIPTION',[
                            'user_id' => $user->id,
                            'error' =>   $e->getMessage()
                        ]);
                        return response()->json(['error' => true, 'message' => 'subscription failed..']);
                    }
                }
                if($sim_data->credit != 0){
                    try {
                        $addcreditreq = GlobalSim::AddPrePaidCredit($user->userDetail->esim_customer,$sim_data->credit);
                        if($addcreditreq == false || $addcreditreq['@attributes']['status'] == 'fail'){
                            Log::error('AddPrePaidCredit',[
                                'user_id' => $user->id,
                                'error' =>   $addcreditreq
                            ]);
                            return response()->json(['error' => true, 'message' =>'Add credit failed']);
                        }
                        $user_balance = Account::where('user_id',$user->id)->first();
                        $balance['balance_amount'] = $user_balance->balance_amount + $sim_data->credit;
                        Account::where('user_id', $user->id)->update($balance);
                    } catch (\Exception $e) {
                        Log::error('AddPrePaidCredit',[
                            'user_id' => $user->id,
                            'error' =>   $e->getMessage()
                        ]);
                        return response()->json(['error' => true, 'message' => 'Add credit failed..']);
                    }
                }
                $response = json_decode('{"orderCode":"E_SIMorder","Subscription":{"SubscriptionId":'.$subsrib_id.'},"resultType":"Ok","resultCode":"0"}');
            } else {
                $response = json_decode('{"orderCode":"o2order","Subscription":{"SubscriptionId":"o2subid"},"resultType":"Ok","resultCode":"0"}');
            }

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
                $user_plan = ['user_id' => $user->id, 'plan_id' => $plan->plan_id, 'status' => 1, 'plan_type' => 'sim'];
                if($sim_data->stock->network->service_type == 2){
                    $user_plan['prorata'] = 1;
                }
                DB::table('user_plans')->insert($user_plan);

                if($provider == 'E_SIM'){
                    $request_id = json_decode($request->request_id);
                    $user_ids = array_column($sim_list->toArray(), 'user_id');
                    $user_list = implode(',', $user_ids);

                    if(!DB::table('trusted_numbers')->where('trusted_number',$trust_number)->exists()) {

                    DB::table('trusted_numbers')->insert(['user_id' => $user->id, 'trusted_number' => $trust_number, 'verified' => 1, 'api_token' => Str::random(90),'default_number' => 1]);
                    }
                    if( $provider == 'O2' || $provider == 'EE_O2' || $provider == 'VUK'){

                        $trusted = DB::table('trusted_numbers')->where('trusted_number',$trust_number)->first();
                        // if($trusted && (is_null($trusted->cli_id) || $trusted->cli_id == 0)){
                        //     $siteId = $user->userDetail->site_id;
                        //     $client     = DwpHelper::initiate_soap_client();
                        //     if($client){
                        //         $phoneNumber = '0'.ltrim($cli_number,'+44');
                        //         $addcli     = DwpHelper::add_cli($client,$siteId,$phoneNumber);
                        //         if($addcli->status == 200){
                        //             DB::table('trusted_numbers')->where('trusted_number',$trust_number)->update(['cli_id'=>$addcli->newCli]);
                        //         }else{
                        //           NotificationLog::create(['user_id'=>$user->id,'message' => 'Add CLI failed SoapClient error','description'=>'site id:'.$siteId.', msg: cli'.$cli_number.', admin:'.Auth::id(),'status'=>'0']);
                        //         }
                        //     }else{
                        //         NotificationLog::create(['user_id'=>$user->id,'message' => 'Add CLI failed SoapClient error','description'=>'site id:'.$siteId.', msg: cli'.$cli_number.', admin:'.Auth::id(),'status'=>'0']);
                        //     }
                        // }
                    }

                    SimList::where('id',$sim_data->id)->update(['reg_status' => 1, 'provision' => 4]);

                    $this->calculate_dealer_commision($auto_plan_id, $promocode);

                    $auto_plan_data['user_list'] = $user_list;
                    if(is_null($plan->next_renewal) || $plan->status == 0){
                        $auto_plan_data['status'] = 1;
                        if(is_null($plan->next_renewal)){
                            $auto_plan_data['switch_billing_plan'] = 0;
                            if($sim_data->stock->network->service_type == 2){
                                $auto_plan_data['next_renewal'] = date('Y-m-t', strtotime(Carbon::now()));
                            }else{
                                $auto_plan_data['next_renewal'] = Carbon::now()->addDays(30)->format('Y-m-d');
                            }
                        }
                    }
                    DB::table('auto_plan')->where('id', $auto_plan_id)->update($auto_plan_data);

                    $pending = SimList::whereIn('request_id', $request_id)->where('reg_status', '0')->count();
                    if($pending == 0){
                        foreach($request_id as $rqst_id){
                            $note = 'Activated by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on';
                            DB::table('delivery_history')->insert(['sim_request_id' => $rqst_id ,'proceed_by' => Auth::id(),  'type' => 2, 'note' => $note]);
                            DB::table('tbl_sim_request')->where('id', $rqst_id)->update(['delivery_status' => 2]);
                        }
                    }

                    $welcome_msg = "Greetings! from ".config('settings.app_name')." Mobile. Now you can download ".config('settings.app_name')." Mobile App for making FREE and affordable international calls. https://bit.ly/2C1SyOw\n\nThankyou.";

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
            }else{
                NotificationLog::create(['user_id'=>$user->id,'message' => 'Plan Subscription Failed','description'=>json_encode($response).', admin:'.Auth::id(),'status'=>'0']);
            }
            $status[$sim_data->stock_id] = $sim_subscription_id;
        }

        $view = view('activation.list_wizard', compact('sim_list','status','step'))->render();

        if($provider == 'E_SIM'){
            $complete = ['error' => false, 'html' => $view,'complete'=>true,'message' => 'Activation process completed'];
        }else{
            $complete = ['error' => false, 'html' => $view];
        }
        
        return response()->json($complete);
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_sippy_account(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }

        $step = $request->step;
        $process_flag = $register_notify = 0;
        $stock_id = unserialize($request->stock_id);
        $request_id = json_decode($request->request_id);
        $sim_list = SimList::whereIn('stock_id', $stock_id)->get();
        $user_ids = array_column($sim_list->toArray(), 'user_id');
        $user_list = implode(',', $user_ids);
        foreach($sim_list as $sim_data){
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
            $switch_billing = $sim_data->auto_plan->plan->switch_billing_plan;
            $in_call_limit = $sim_data->auto_plan->plan->in_call_limit;
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
                    $swithlog['description']    = 'Activation Failed-'.json_encode($temp);
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

                    SimList::where('id',$sim_data->id)->update(['reg_status' => 1, 'provision' => 4]);

                    $this->calculate_dealer_commision($auto_plan_id, $promocode);

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

                    $welcome_msg = "Greetings! from ".config('settings.app_name')." Mobile. Now you can download ".config('settings.app_name')." Mobile App for making FREE and affordable international calls. https://bit.ly/2C1SyOw\n\nThankyou.";

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

                    $welcome_msg = "Greetings! from ".config('settings.app_name').". Now you can download ".config('settings.app_name')." App for making FREE and affordable international calls. https://bit.ly/2C1SyOw\n\nThankyou.";

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
                SimList::where('id',$sim_data->id)->update(['reg_status' => 1, 'provision' => 4]);

                $this->calculate_dealer_commision($auto_plan_id, $promocode);

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

            if( $provider == 'O2' || $provider == 'EE_O2' || $provider == 'VUK'){
                $trusted = DB::table('trusted_numbers')->where('trusted_number',$trust_number)->first();
                // if($trusted && (is_null($trusted->cli_id) || $trusted->cli_id == 0)){
                //     $siteId = $user->userDetail->site_id;
                //     $client     = DwpHelper::initiate_soap_client();
                //     if($client){
                //         $phoneNumber = '0'.ltrim($cli_number,'+44');
                //         $addcli     = DwpHelper::add_cli($client,$siteId,$phoneNumber);
                //         if($addcli->status == 200){
                //             DB::table('trusted_numbers')->where('trusted_number',$trust_number)->update(['cli_id'=>$addcli->newCli]);
                //         }else{
                //           NotificationLog::create(['user_id'=>$user->id,'message' => 'Add CLI failed SoapClient error','description'=>'site id:'.$siteId.', msg: cli'.$cli_number.', admin:'.Auth::id(),'status'=>'0']);
                //         }
                //     }else{
                //         NotificationLog::create(['user_id'=>$user->id,'message' => 'Add CLI failed SoapClient error','description'=>'site id:'.$siteId.', msg: cli'.$cli_number.', admin:'.Auth::id(),'status'=>'0']);
                //     }
                // }
            }

            $status[$sim_data->stock_id] = $i_account;

            if($sim_data->credit > 0 && !is_null($i_account)){
                // $topup_end_point = '/superapi/topup?mvno='.$mvno_key;
                // $topup_data = (object)[
                //  'msisdn' => $cli_number,
                //  'amount' => 100 * $sim_data->credit
                // ];

                // $topup_response = Helper::call_sim_process_api($topup_end_point, json_encode($topup_data));
                // $topup_response =  json_decode('{"customerId":"901000315","orderCode":"6722352966695780357","resultType":"SUCCESS","resultCode":"0","messages":[{}]}');

                // if($topup_response->resultType == 'SUCCESS' && $topup_response->resultCode == 0){
                    // $log_data = array(
                    //  'user_id' => $user->id,
                    //  'msisdn' => $cli_number,
                    //  'category' => 'EXTERNAL_TOPUP',
                    //  'value' => $sim_data->credit
                    // );
                    // DB::table('avoo_sim_log')->insert($log_data);

                    // $payment = ['user_id' => $parent_id, 'pay_to' => $user->id, 'transaction_id' => $txn_id, 'buy_price' => '0',
                    //     'amount' => $sim_data->credit, 'tax_amount' => 0, 'total_amount' => $sim_data->credit, 'currency' => $country->currency, 'category' => 'switch', 'card_type' => $card_type,  'payment_method' => $payment_method, 'payment_for' => 'Credit Added','description' => 'credit added to app'];

                    // $method = 'accountCredit'; //accountAddFunds
                    // $swithlog['description']    = 'accountCredit';
                    // $swithlog['user_id']        =  $user->id;
                    // $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$sim_data->credit,$country->currency);
                    // $temp =  SwitchHelper::call_switch_api($xml_data);
                    // // $temp = [];
                    // if (array_key_exists("fault", $temp)) {
                    //     $payment['status'] = '2';
                    //     $swithlog['status'] = 2;
                    // } else {
                    //     $payment['status'] = '1';
                    //     $swithlog['status'] = 1;
                    //     DB::table('account_balance')->where('user_id', $user->id)
                    //         ->increment('balance_amount', $sim_data->credit);
                    // }
                    //             SwitchLog::insertGetId($swithlog);
                    // DB::table('user_payments')->insert($payment);
                    // SimList::where('id',$sim_data->id)->update(['credit' => 0]);
                    $process_flag = 1;
                // } else {
                //  $status[$sim_data->stock_id] = '';
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
            $obj->subject = 'Welcome to '.config('settings.app_name');
            $obj->heading  = 'Welcome To '.config('settings.app_name');
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


        $view = view('activation.list_wizard', compact('sim_list', 'status','step'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function calculate_dealer_commision($auto_plan_id, $promocode)
    {
        $list_pending = SimList::where('autoplan_id', $auto_plan_id)->where('reg_status', '0')->count();
        if($list_pending == 0) {
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
        return 1;
    }

    /**
    * Order Sim Detail View
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function order_provision(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }

        $step = 1;
        $stock_id = $request->stock_id;
        $sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
        $currency = Helper::get_option('currency_symbol');
        $view = view('activation.provision', compact('sim_list','stock_id','step'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Update Porting Request
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function port_request(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }
        SimList::where('id', $request->sim_id)->update(['port' => $request->status]);

        return response()->json(['error' => false]);
    }

    /**
    * Update Porting Request
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function provision_request(Request $request)
    {
        if (!Helper::has_permission('orders','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied!']);
        }

        $step = 2;
        $stock_id = $request->stock_id;
        $sim_list = SimList::whereIn('stock_id', unserialize($request->stock_id))->get();
        $currency = Helper::get_option('currency_symbol');
        $provider = [];
        $user     = [];
        foreach ($sim_list as $sim) {
            array_push($provider,$sim->auto_plan->plan->provider);
            $userid = $sim->sim_request->user_id;
            $user   = User::find($userid);
        }
        $view = view('activation.provision', compact('sim_list', 'stock_id','step','provider','user'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /**
    * Verfiy Sim Number
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function verfiy_sim_number(Request $request)
    {
        // $api_user = Helper::get_option('dwp_auth_username');
        // $api_pwd  = Helper::get_option('dwp_auth_password');
        // $xml = '<?xml version="1.0" ? >
        // <Request module="dwapi" call="mobile_product_list" id="'.Helper::unique_code(32).'" version="1.0">
        //   <block name="auth">
        //     <a name="username" format="text">'. $api_user .'</a>
        //     <a name="password" format="password">'. $api_pwd .'</a>
        //     <a name="client-id" format="text">1</a>
        //   </block>
        //   <a name="sub-account" format="text">GZR38415</a>
        // </Request>';

        // $response  = DwpHelper::dwp_process_api($xml);
        // echo  $response;
        // $response  = json_decode(DwpHelper::dwp_response_handler($response));
        // print_r($response);
        // die();

        // $data['cli'] = '07766742689';
        // $check_sim_xml = DwpHelper::dwp_check_mobile_bars_xml($data);
        // $response  = DwpHelper::dwp_process_api($check_sim_xml);
        // $response = json_decode(DwpHelper::dwp_response_handler($response));
        // print_R($response);
        // die();

        $stock = SimStock::where('sim_number',$request->sim_number)->first();
        $data['sim_number'] = $stock->sim_number;
        $data['network'] = $stock->network->provider;
        $check_sim_xml = DwpHelper::dwp_check_sim_xml($data);
        $response  = DwpHelper::dwp_process_api($check_sim_xml);
        $response = json_decode(DwpHelper::dwp_response_handler($response));
        if($response->children[0]->no == 0){
            return response()->json(['error' => false]);
        }else{
            return response()->json(['error' => true, 'message' => $response->children[0]->text]);
        }
    }

    /**
    * Verfiy Sim Number
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function verfiy_pac_code(Request $request)
    {
        $data['pac_code'] = $request->pac_code;
        // SimList::where('id', $request->list_id)
        //     ->update(['porting_to' => $request->cli, 'pac_no' => $request->pac_code]);
        if (preg_match('/^(07)[0-9]{9}$/', $request->cli)) {
            $data['cli'] = $request->cli;
        } else {
            return response()->json(['error' => true, 'message' => 'Enter a valid mobile number to keep']);
        }
        $check_pac_xml = DwpHelper::dwp_check_pac($data);
        $response = DwpHelper::dwp_process_api($check_pac_xml);
        $response = json_decode(DwpHelper::dwp_response_handler($response));
        if($response->children[0]->no == 0){
            return response()->json(['error' => false]);
        }else{
            return response()->json(['error' => true, 'message' => $response->children[0]->text]);
        }
    }

    /**
    * Verfiy Sim Number
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function provision_process(Request $request)
    {
        //dd($request->all());
        parse_str($request->provision, $provision);
        $sim_list = SimList::where('id', $provision['list_id'])->first();
        $provider = $sim_list->stock->provider;
        if($provider == 'E_SIM'){
            try {
                SimList::where('id', $provision['list_id'])->update(['activate_onfirstuse' => $provision['activate_onfirstuse'], 'send_sms' => $provision['send_sms'],'take_payment'=>$provision['take_payment'],'provision_date'=>$provision['activation'],'bill_limit'=>$provision['bill_limit'],'warn_limit'=>$provision['warn_limit'],'lock_limit'=>$provision['warn_limit']]);
                $provision_status =  $sim_list->provision;
                if($provision_status == 0){
                    //$iccid      = $sim_list->stock->sim_number;
                    //$getmsisdn   = GlobalSim::AssignMsisdn($iccid);
                    //$getsimdetails = GlobalSim::GetGlobalDetails($iccid);
                    // dd($getmsisdn);
                    // if($getmsisdn == false){
                    //     return response()->json(['error' => true, 'message' =>'Failed to assign msisdn']);
                    // }
                    // $msisdn  = $getmsisdn['STATUS_Response']['MSISDN'];
                    // $transid = $getmsisdn['STATUS_Response']['TRANSACTION_ID'];
                    // SimStock::whereId($sim_list->stock->id)->update(['phone_number'=>$msisdn,'verified'=>1]);
                    SimList::where('id', $provision['list_id'])->update(['provision' => 4]);
                }
                return response()->json(['error' => false]);
            } catch (\Exception $e) {
                Log::error('ESIMPROVISION',[
                    'order' => $sim_list->sim_request->order_id,
                    'simnumber' => $sim_list->stock->sim_number,
                    'error' =>   $e->getMessage()
                ]);
                return response()->json(['error' => true, 'message' =>'Provision failed..','err' => $e->getMessage() ]);
            }
        }else{

        SimList::where('id', $provision['list_id'])
            ->update(['porting_to' => $provision['porting_to'], 'pac_no' => $provision['pac_code']]);

        $sim_number = $sim_list->stock->sim_number;
        $note = 'Provisioned ( '.$sim_number.' ) by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on';
        DB::table('delivery_history')->insert(['sim_request_id' => $sim_list->request_id ,'proceed_by' => Auth::id(),  'type' => 1, 'note' => $note]);

        if($sim_list->port){
            $data['pac_code'] = $sim_list->pac_no;
            $data['cli'] = $sim_list->porting_to;
            $check_pac_xml = DwpHelper::dwp_check_pac($data);
            $response = DwpHelper::dwp_process_api($check_pac_xml);
            $response = json_decode(DwpHelper::dwp_response_handler($response));
            if($response->children[0]->no != 0){
                return response()->json(['error' => true, 'message' => $response->children[0]->text]);
            }
        }

        $data['sim_number'] = $sim_number;
        $data['network'] = ($sim_list->stock->network->provider == 'EE_O2')?'O2':$sim_list->stock->network->provider;
        $check_sim_xml = DwpHelper::dwp_check_sim_xml($data);
        $response  = DwpHelper::dwp_process_api($check_sim_xml);
        $response = json_decode(DwpHelper::dwp_response_handler($response));
        if($response->children[0]->no != 0){
            return response()->json(['error' => true, 'message' => $response->children[0]->text .'--'.$data['network']]);
        }

        $provision_status =  $sim_list->provision;
        $order_id = $sim_list->provision_id;
        if($provision_status == 0){
            $data['assign'] = $sim_list->sim_request->user->name;
            $data['order_id'] = $sim_list->sim_request->order_id;
            $order_xml = DwpHelper::dwp_order_new($data);
            $response = DwpHelper::dwp_process_api($order_xml);
            $response = json_decode(DwpHelper::dwp_response_handler($response));

            if($response->children[0]->no == 0){
                $order_id = $response->children[1]->html;
                $provision_status = 1;
                SimList::where('id', $provision['list_id'])
                    ->update(['provision_id' => $order_id, 'provision' => $provision_status]);
            }else{
                return response()->json(['error' => true, 'message' => $response->children[0]->text]);
            }
        }

        if($provision_status == 1){
            $type = ($sim_list->port)?'port':'new';
            $product_id = $sim_list->auto_plan->plan->sim_billing_plan;
            $data['type'] = $type;
            $data['product_id'] = $product_id;
            $data['order_id'] = $order_id;
            $data['bill_limit'] = $provision['bill_limit'];
            $data['user_name'] = $sim_list->sim_request->order_id;
            if($sim_list->port){
                $data['port_cli'] = $sim_list->porting_to;
                $data['pac_code'] = $sim_list->pac_no;
                $data['port_date'] = $provision['transfer'];
                $provision_date = $provision['transfer'];
            }else{
                $data['activation'] = $provision['activation'];
                $provision_date = $provision['activation'];
            }

            $product_xml = DwpHelper::dwp_order_add_product($data);
            $response = DwpHelper::dwp_process_api($product_xml);
            $response = json_decode(DwpHelper::dwp_response_handler($response));
            Log::error('PROVISIONPROCESS',[
                'response' => $response,
                'request' => $product_xml
            ]);
            if($response->children[0]->no == 0 && $response->children[1]->html == 1){
                $provision_status = 2;
                SimList::where('id', $provision['list_id'])->update(['provision' => $provision_status, 'provision_date' => $provision_date]);
            }elseif($response->children[0]->no != 0){
                return response()->json(['error' => true, 'message' => $response->children[0]->text]);
            }elseif($response->children[1]->html == 0){
                SimList::where('id', $provision['list_id'])->update(['provision' => 0]);
                return response()->json(['error'=>true,'message'=>'Failed to provision, please try again']);
            }else{
                return response()->json(['error' => true, 'message' => $response->children[0]->text]);
            }
        }

        if($provision_status == 2){
            $data['order_id'] = $order_id;
            $provision_xml = DwpHelper::dwp_order_provision($data);
            $response = DwpHelper::dwp_process_api($provision_xml);
            $response = json_decode(DwpHelper::dwp_response_handler($response));

            if($response->children[0]->no == 0){
                $provision_status = 3;
                $order_id = $response->children[1]->html;
                SimList::where('id', $provision['list_id'])
                    ->update(['provision_id' => $order_id, 'provision' => $provision_status]);
                return response()->json(['error' => false]);
            }else{
                return response()->json(['error' => true, 'message' => $response->children[0]->text]);
            }

            // $xml = '<?xml version="1.0"     ? >
            // <Request module="dwapi" call="mobile_compatibilities" id="e21aa535fde298bd50f5ece8e8b5b2e0" version="1.0">
            //   <block name="auth">
            //     <a name="username" format="text">'. $api_user .'</a>
            //     <a name="password" format="password">'. $api_pwd .'</a>
            //     <a name="client-id" format="text">1</a>
            //   </block>
            //   <a name="mobile-number" format="phone">07766742689</a>
            // </Request>';

            // $response  = DwpHelper::dwp_process_api($xml);
            // $response  = json_decode(DwpHelper::dwp_response_handler($response));
            // print_r($response);
            // die();
        }
        }
    }

    /**
    * Verfiy Sim Number
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function provision_check(Request $request)
    {
        $provision_id = SimList::where('id', $request->sim_id)->value('provision_id');
        $data['order_id'] = /*'3277455';*/ $provision_id;
        $search_xml = DwpHelper::dwp_order_search($data);
        $response  = DwpHelper::dwp_process_api($search_xml);
        $response  = json_decode(DwpHelper::dwp_response_handler($response));

        if($response->children[0]->no == 0){
            $provision_check = 4;
            $result = $this->dwp_response($response->children);
            $status['request_id'] = $result['orders']['block']['id'];
            $status['state'] =  ucfirst($result['orders']['block']['components']['block']['state']);
            $status['updated_at'] = $result['orders']['block']['components']['block']['last-update'];
            $status['request_status'] = $result['orders']['block']['request-stage'];
            $html = view('modal-popup', compact('status','provision_check'))->render();
            if($status['state'] == 'Completed' && $status['request_status'] == 'Complete'){
                SimList::where('id', $request->sim_id)->update(['provision' => 4]);
            }
            return response()->json(['error' => false,'html' => $html]);
        }else{
            return response()->json(['error' => true, 'message' => $response->children[0]->text]);
        }
    }

    public function dwp_response($children)
    {
        foreach($children as $details){
            if(isset($details->children)){
                $key = (isset($details->name))?$details->name:((isset($details->tag))?$details->tag:((isset($details->id))?$details->id:'key'));
                $dwp[$key] = $this->dwp_response($details->children);
            }else{
                $key = (isset($details->name))?$details->name:((isset($details->tag))?$details->tag:((isset($details->id))?$details->id:'key'));
                $dwp[$key] = (isset($details->html))?$details->html:$details;
            }
        }
        return $dwp;
    }

}
