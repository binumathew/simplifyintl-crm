<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Auth;
use Carbon;
use Excel;
use Helper;
use Crypt;
// use Storage;
use App\Exports\CustomExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cron\CronExpression;
use App\Models\BridgeServer;
use App\Models\ScheduledTask;
use App\Models\User as ModalUser;
use App\Models\UserCreditCard;
use Illuminate\Console\Scheduling\Schedule;

use App\Mail\PaymentStatus;
use Illuminate\Support\Facades\File;

use SwitchHelper;
use App\Models\Admins;
use App\Models\AutoPlan;
use App\Models\UserPayment;
use App\Helpers\SIMHelper;
use App\Models\Account;
use App\Models\UserPlan;
use App\Models\UserData;
use App\Models\Country;
use App\Models\TblPorting;
use App\Models\NotificationLog;
use App\Models\ConferenceCreate;
use App\Models\ConferenceContact;
use App\Models\SimStock;
use App\Models\SimList;
use App\Models\SimRequest;
use Twilio\Rest\Client; 
use Twilio\Exceptions\RestException;

use App\Jobs\FailureNotification;
use App\Mail\OrderComplete;
use Illuminate\Support\Facades\Validator;


class TestController extends Controller
{
    protected $signature = 'sim:history';
    
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 1000);
    } 

    public function check_tariff($phone, $currency = 'EURO'){
        $currency = strtolower($currency);
        $data = DB::table('tbl_tariff')->select('tariff_'.$currency.' as tariff','time_zone')
                        ->where('prefix', 'like', '%,'.$phone.',%')->first();
        if(!$data){
            $phone = substr($phone, 0, -1);
            if(strlen($phone) > 1){
                $data = $this->check_tariff($phone, $currency);
                return ['tariff' => $data['tariff'], 'time_zone' => $data['time_zone']];
            }else{
                return ['tariff' => '0','time_zone' => ''];
            }            
        }else{
            return ['tariff'=>$data->tariff,'time_zone'=>$data->time_zone];
        }
    }

    public function testing($user_id = '10507',Request $request)
    {   

            
        //======================================================================
        // $payments = UserPayment::select('user_payments.id','c.currency','user_payments.currency as ext_currency')
        //                 ->join('users as usr','usr.id','=','user_id')
        //                 ->join('country as c','usr.country_id','=','c.id')->get();

        // foreach ($payments as $payment) {
        //     if($payment->currency != $payment->ext_currency){
        //         echo $payment->id.'--'.$payment->currency.'--'.$payment->ext_currency.chr(10);
        //         DB::table('user_payments')->where('id',$payment->id)->update(['currency'=>$payment->currency]);
        //     }
        // }      

        //======================================================================         
        // $users = ModalUser::all();
        // foreach($users as $user){
        //     // $cards = DB::table('user_credit_cards')->where('user_id',$user->id)->get();
        //     // if(count($cards) > 1){
        //         $payments = DB::table('user_payments')->where('user_id',$user->id)->where('card_type','-')->get();
        //         foreach( $payments as  $payment ){
        //             // if(strpos($payment->description, 'Mastercard ****')){
        //             //     // echo $payment->id.'--'.substr($payment->description, strpos($payment->description, 'Visa ***'), 13).chr(10);
        //             //     $card_type = substr($payment->description, strpos($payment->description, 'Mastercard ****'), 19);
        //             //     // echo $card_type.chr(10);
        //             //     DB::table('user_payments')->where('id',$payment->id)->update(['card_type'=>$card_type]);
        //             // }
        //             //echo $payment->id.'--'.substr($payment->description, strpos($payment->description, 'Visa ***'), 13).chr(10);
        //             echo $payment->id.'--'.$payment->description.chr(10);
        //         }
        //         // 
        //     // }
        // }
        die();
        // $simList = SimList::whereNotNull('user_id')->get();
        // foreach($simList as $sim){
        //     $admin = Admins::where('promocode',$sim->sim_request->promocode)->first();
        //     if($admin)
        //         ModalUser::where('id',$sim->user_id)->update(['dealer_id' => $admin->id]);
        // }
        die();
       //  $bolt = SIMHelper::dwp_sim_available_bolt_ons_xml();
       //  $response = SIMHelper::dwp_process_api($bolt);
       //  print_r($response);

       //  libxml_use_internal_errors(true);
       //  $xml = simplexml_load_string($output);

       //  if(Auth::id()==1){
       //      libxml_use_internal_errors(true);
       //      $xml = simplexml_load_string($output);
       //      die();  
       // }
       //  die();
        $users = ModalUser::select('id','first_name','last_name','email','phone','parent_id','created_at')->get();
        $i = 0;
        $data =[];
        foreach ($users as $key => $user) {
            $data[$key]['name'] = $user->first_name.' '.$user->last_name;
            $data[$key]['email'] = $user->email;
            $data[$key]['phone'] = $user->phone;
            $data[$key]['platform'] = $user->userDetail->user_platform;
            $data[$key]['parent_id'] = ($user->parent_id)?'C':'P';
            $data[$key]['payment'] = UserPayment::where('user_id', $user->id)->count();
            if($user->parent_id){
                $order = 0;
            }else{
                $order = SimRequest::where('user_id', $user->id)->count();
            }
            $data[$key]['order'] = $order;
            $data[$key]['created_at'] =  Carbon::parse($user->created_at)->format('Y-m-d');
            // if($i == 50){
            //     break;
            // }
            // $i++;
        }
        array_unshift($data,array('Name','email','Phone','Platform','Parent/Child','Payment','Order','Created'));
        return Excel::download(new CustomExport($data), 'users.csv');
        die();
        $users = ModalUser::whereNotNull('i_account')->get(); //
        $inc = 1;
        foreach ($users as $user) {
            $inc++;
            if($inc == 10){
                break;
            }
            $i_account = $user->i_account;
            $data['i_account'] = $i_account; 
            $xml_data = '<?xml version="1.0"?>
            <methodCall>
              <methodName>listAuthRules</methodName>
              <params>
                <param>
                  <value>
                    <struct>
                      <member>
                        <name>i_account</name>
                        <value><int>'. $data['i_account'] .'</int></value>
                      </member>
                    </struct>
                  </value>
                </param>
              </params>
            </methodCall>';

            $temp =  SwitchHelper::call_switch_api($xml_data);

            if (array_key_exists("fault", $temp)) {
                echo 'Failed - '.$data['i_account'].chr(10);
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
                // print_r($response['authrules']['data']);               
                if(isset($response['authrules']['data']['value'])){
                    $ex_auth_response = [];
                    $ex_auth = $response['authrules']['data']['value'];
                    for ($i = 0; $i < count($ex_auth); $i++) {                        
                        $member = $ex_auth[$i]['struct']['member'];
                        for($j = 0; $j < count($member); $j++){
                            $ex_array_key = $member[$j]['name'];       
                            $auth_value = isset($member[$j]['value']['int']) ? $member[$j]['value']['int'] : (isset($member[$j]['value']['string'])? $member[$j]['value']['string']: '');               
                            $ex_auth_response[$i][$ex_array_key] = $auth_value;                        
                        }                    
                    }
                    
                    if(in_array('149.36.7.61', array_column($ex_auth_response, 'remote_ip'))) {
                        echo 'Auth exist'. $user->userDetail->auth_name.chr(10);
                    }else{
                         echo 'Auth Added'. $user->userDetail->auth_name.chr(10);
                        $bridgeips = BridgeServer::where('set_default' ,1)->get();

                        if($bridgeips){
                            $clinumber = str_replace('+', '', $user->phone);
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
                    }                    
                }else{
                    echo 'Auth Added'. $user->userDetail->auth_name.chr(10);
                    $bridgeips = BridgeServer::where('set_default' ,1)->get();

                    if($bridgeips){
                        $clinumber = str_replace('+', '', $user->phone);
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
                }                
            }
            echo '================================================'.chr(10);
        }


        // $gateway = Helper::get_btree_gateway(1);
        // $result = $gateway->transaction()->sale([
        //         'amount' => 5,
        //         'paymentMethodToken' => '4mg74kg',
        //         'options' => [
        //             'submitForSettlement' => True
        //         ]
        //     ]); 

        // print_r($result);
        // echo '2222222222';
        die();

        $respo = '{"success":true,"transaction":{"id":"jf8njbvr","status":"submitted_for_settlement","type":"sale","currencyIsoCode":"GBP","amount":"2.99","merchantAccountId":"wcc3mq3gkdm8r5vf","subMerchantAccountId":null,"masterMerchantAccountId":null,"orderId":null,"createdAt":{"date":"2020-05-04 13:40:41.000000","timezone_type":3,"timezone":"UTC"},"updatedAt":{"date":"2020-05-04 13:40:41.000000","timezone_type":3,"timezone":"UTC"},"customer":{"id":"568819833","firstName":"Jijo","lastName":"Joseph","company":null,"email":"jijo.joseph@gencomtel.com","website":null,"phone":"+917907105209","fax":null,"globalId":"Y3VzdG9tZXJfNTY4ODE5ODMz"},"billing":{"id":"xr","firstName":null,"lastName":null,"company":null,"streetAddress":null,"extendedAddress":null,"locality":null,"region":null,"postalCode":"10000","countryName":null,"countryCodeAlpha2":null,"countryCodeAlpha3":null,"countryCodeNumeric":null},"refundId":null,"refundIds":[],"refundedTransactionId":null,"partialSettlementTransactionIds":[],"authorizedTransactionId":null,"settlementBatchId":null,"shipping":{"id":null,"firstName":null,"lastName":null,"company":null,"streetAddress":null,"extendedAddress":null,"locality":null,"region":null,"postalCode":null,"countryName":null,"countryCodeAlpha2":null,"countryCodeAlpha3":null,"countryCodeNumeric":null},"customFields":null,"avsErrorResponseCode":null,"avsPostalCodeResponseCode":"M","avsStreetAddressResponseCode":"I","cvvResponseCode":"I","gatewayRejectionReason":null,"processorAuthorizationCode":"R8F71T","processorResponseCode":"1000","processorResponseText":"Approved","additionalProcessorResponse":null,"voiceReferralNumber":null,"purchaseOrderNumber":null,"taxAmount":null,"taxExempt":false,"creditCard":{"token":"5d4rfn6","bin":"411111","last4":"1111","cardType":"Visa","expirationMonth":"08","expirationYear":"2022","customerLocation":"US","cardholderName":null,"imageUrl":"https:\/\/assets.braintreegateway.com\/payment_method_logo\/visa.png?environment=sandbox","prepaid":"Unknown","healthcare":"Unknown","debit":"Unknown","durbinRegulated":"Unknown","commercial":"Unknown","payroll":"Unknown","issuingBank":"Unknown","countryOfIssuance":"Unknown","productId":"Unknown","globalId":"cGF5bWVudG1ldGhvZF9jY181ZDRyZm42","accountType":null,"uniqueNumberIdentifier":"2fa322985a3db328b16e2f9db98bf8fb","venmoSdk":false},"statusHistory":[{},{}],"planId":null,"subscriptionId":null,"subscription":{"billingPeriodEndDate":null,"billingPeriodStartDate":null},"addOns":[],"discounts":[],"descriptor":{},"recurring":false,"channel":null,"serviceFeeAmount":null,"escrowStatus":null,"disbursementDetails":{},"disputes":[],"authorizationAdjustments":[],"paymentInstrumentType":"credit_card","processorSettlementResponseCode":null,"processorSettlementResponseText":null,"networkResponseCode":null,"networkResponseText":null,"threeDSecureInfo":null,"shipsFromPostalCode":null,"shippingAmount":null,"discountAmount":null,"networkTransactionId":"020200504134041","processorResponseType":"approved","authorizationExpiresAt":{"date":"2020-05-11 13:40:41.000000","timezone_type":3,"timezone":"UTC"},"refundGlobalIds":[],"partialSettlementTransactionGlobalIds":[],"refundedTransactionGlobalId":null,"authorizedTransactionGlobalId":null,"globalId":"dHJhbnNhY3Rpb25famY4bmpidnI","retryIds":[],"retriedTransactionId":null,"retrievalReferenceNumber":"1234567","creditCardDetails":{},"customerDetails":{},"billingDetails":{},"shippingDetails":{},"subscriptionDetails":{}}}';

        $responce = json_decode($respo);
        print_r( $responce );
        // echo $responce->transaction->creditCard->token;
        // echo $responce->transaction->creditCard->cardType.' ****'.$responce->transaction->creditCard->last4;
        // echo $responce->transaction->creditCard->expirationMonth;
        // echo $responce->transaction->creditCard->expirationYear;
    // $autoplan = AutoPlan::all();
    // foreach($autoplan as $auto){
    //     AutoPlan::where('id', $auto->id)->update(['switch_billing_plan' => $auto->plan->switch_billing_plan]);
    // }
    die();

        // $orders = SimRequest::where('promocode','AVTHMS809')->get();
        // $data = [];
        // foreach($orders as $order){
        //     foreach($order->list as $sim){
        //         $user = ModalUser::where('id',$sim->user_id)->first();
        //         // print_R($user);
        //         if($user){
        //             $payments = UserPayment::where('user_id',$user->id)->get();
        //             $pay = [];
        //             foreach($payments as $payment){
        //                 $pay[] = ['amount' => $payment->total_amount,'description'=>$payment->description,'date'=>date('Y-m-d',strtotime($payment->created_at))];
        //             }
        //             $data[] = ['id' =>$user->id, 'parent'=>$user->parent_id, 'name'=>$user->name,'phone' =>$user->phone, 'payment'=> $pay];
        //         }else{
        //             echo 'no user'.$order->id;
        //         }
        //     }
        // }
        // print_r($data);

        if(Auth::id()==1){
         // $contacts = DB::table('conference_contact')->where('user_id',10307)->orderby('created_at','desc')->get();
         // foreach ($contacts as $key => $contact) {
         //    echo '========================================'.$contact->created_at.chr(10);
         //    $cts = Crypt::decrypt($contact->contacts);
         //    print_r($cts);
         // }
        }
        die();
        $users = ModalUser::whereNotNull('i_account')->get(); //
        $data = [];
        $i = 0;
        foreach ($users as $user) {
            $user_plans = UserPlan::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(2)->get();
            $expiry = false; 
            $usage['ID'] = $user->id;
            $usage['user_name'] = $user->name;
            $usage['phone'] = $user->phone;
            $usage['user_type'] = ($user->parent_id)?'C':'P';
            if($user_plans->isNotEmpty()){
                foreach($user_plans as $index => $plans){
                    $activated = Carbon::parse($plans->created_at)->format('Y-m-d');
                    // echo $user->id.'---'.$activated.chr(10);
                    // continue;

                    if($expiry && $activated < $expiry){                    
                        $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');
                        // $new_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                        //                     'service_type'=>'DATA'])->where('date','>',$activated)
                        //                     ->where('date','<',$expiry)->sum('duration');
                        // $new_usage = number_format(($new_usage/(1024*1024*1024)),2,'.','');
                        // $data[$i-1]['usage'] = $data_usage-$new_usage;
                        $data[$i-1]['call_duration'] -= round($call_duration/60);
                        // $data[$i-1]['note'] = ($plans->plan->data_limit)?round(($data[$i-1]['usage']/$user_plans[$index-1]->plan->data_limit)*100,2):'-';
                        $data[$i-1]['extra'] = 1;
                        //$data[$i-1]['usage'] = 'processing'; //
                    }

                    $expiry = Carbon::parse($activated)->addDays(31)->format('Y-m-d');
                    $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');

                    // $data_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                    //                     'service_type'=>'DATA'])->where('date','>',$activated)
                    //                     ->where('date','<',$expiry)->sum('duration');
                    // $data_usage = number_format(($data_usage/(1024*1024*1024)),2,'.','');                    
                    $usage['plan'] = $plans->plan->plan_name;
                    $usage['provider'] = $plans->plan->provider;
                    $usage['buy_price'] = $plans->plan->buy_price+.42;                    
                    $usage['activated'] = $activated;
                    $usage['usage'] = '';//$data_usage;
                    $usage['call_duration'] = round($call_duration/60);
                    $usage['vat'] = round($plans->plan->sell_price - ($plans->plan->sell_price/1.2),2);
                    //net=100/(100+vatRate)*input;
                    //vat=input-net;
                    $usage['transaction_fee'] = round(($plans->plan->sell_price*4)/100,2);
                    $usage['total_amount'] = $plans->plan->sell_price;
                    $usage['profit'] = $plans->plan->sell_price - ($usage['buy_price']+$usage['vat']+$usage['transaction_fee']);
                    $usage['note'] = '';//($plans->plan->data_limit)?round(($data_usage/$plans->plan->data_limit)*100,2):'-';
                    $usage['extra'] ='';
                    $data[$i] = $usage;
                    $i++;
                }
            }else{
                $activated = Carbon::parse($user->created_at)->format('Y-m-d');                
                $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                    'history_from'=>1])->where('connect_date','>=',$activated)
                                    ->where('connect_date','<=',$expiry)->sum('duration');
                $usage['plan'] = 'Default Plan';
                $usage['provider'] = '';
                $usage['buy_price'] = '';                
                $usage['activated'] = $activated;
                $usage['usage'] = '';
                $usage['call_duration'] = round($call_duration/60);
                $usage['vat'] = '';
                $usage['transaction_fee'] = '';
                $usage['total_amount'] = '';
                $usage['profit'] = '';
                $usage['note'] = '';
                $usage['extra'] ='';
                $data[$i] = $usage;
                $i++;
            }   
        } 
        // print_R($data); 
        array_unshift($data,array('ID','Name','Phone','Parent/Child','Plan','Provider','Buy Price', 'Activated','Data Usage','Call Duration','Vat','Transaction Fee','Totoal Amount','Profit','Usage in %','Extra'));
        return Excel::download(new CustomExport($data), 'usage.csv');

        die();  
        die();

        $orders = SimRequest::whereIn('promocode', ['','AVOOSIM','AVDSIN','AV_DELRVND','STVIN002','MAHSH2020','AVJERIN','AVCHMMCWA','SJ100','AVDBBRN'])->get(); //
        foreach($orders as $order){
            $list = $pay = [];
            $user = ModalUser::find($order->user_id);
            foreach($order->list as $item){                
                if(!is_null($item->user_id)){ 
                    $user = ModalUser::find($item->user_id);                                  
                }
                if(!$user){
                    // echo $item->user_id.chr(10);
                    // echo $order->user_id;
                    continue;
                }
                $list['name'] = $user->name;
                $list['phone_number'] = $item->stock->phone_number;
                $list['agent'] = $order->promocode;
                $plans = UserPlan::where(['user_id'=>$user->id])->get();//,'plan_type'=>'sim'
                $list['activated'] = ($plans->isEmpty())?'':'1';
                $list['subscription'] = count($plans); 
                $payments = UserPayment::where('user_id',$user->id)->where(['status'=>1])->get();
                //'payment_method'=>'Direct Cash',
                if($payments->isEmpty()){
                    continue;
                }
                $i = 1;
                foreach($payments as $key => $payment){                    
                    $pay_date = Carbon::parse($payment->created_at)->format('Y-m-d H:i:s');
                    $list['amount_'.$i] = $payment->total_amount;
                    $list['txn_id_'.$i] = $payment->transaction_id;
                    $list['activated_'.$i] = $pay_date;                    
                    $i++;
                }            
                $data[] = $list;                 
            }                    
        }

        array_unshift($data,array('Name','Phone Number','Agent','Active','No Subscription','Amount','Transaction','Activated','Amount','Transaction','Activated'));
        return Excel::download(new CustomExport($data), 'direct-sale.csv');
        die();
        
        $users = ModalUser::whereNotNull('i_account')->get(); //->where('id',10102)
        $data = [];
        $i = 0;
        foreach ($users as $user) {
            $user_plans = UserPlan::where('user_id', $user->id)->get();
            $expiry = false; 
            $usage['ID'] = $user->id;
            $usage['user_name'] = $user->name;
            $usage['phone'] = $user->phone;
            $usage['user_type'] = ($user->parent_id)?'C':'P';
            if($user_plans->isNotEmpty()){
                foreach($user_plans as $index => $plans){
                    $activated = Carbon::parse($plans->created_at)->format('Y-m-d');
                    if($expiry && $activated < $expiry){                    
                        $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');
                        // $new_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                        //                     'service_type'=>'DATA'])->where('date','>',$activated)
                        //                     ->where('date','<',$expiry)->sum('duration');
                        // $new_usage = number_format(($new_usage/(1024*1024*1024)),2,'.','');
                        // $data[$i-1]['usage'] = $data_usage-$new_usage;
                        $data[$i-1]['call_duration'] -= round($call_duration/60);
                        // $data[$i-1]['note'] = ($plans->plan->data_limit)?round(($data[$i-1]['usage']/$user_plans[$index-1]->plan->data_limit)*100,2):'-';
                        $data[$i-1]['extra'] = 1;
                        //$data[$i-1]['usage'] = 'processing'; //
                    }

                    $expiry = Carbon::parse($activated)->addDays(31)->format('Y-m-d');
                    $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');

                    // $data_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                    //                     'service_type'=>'DATA'])->where('date','>',$activated)
                    //                     ->where('date','<',$expiry)->sum('duration');
                    // $data_usage = number_format(($data_usage/(1024*1024*1024)),2,'.','');                    
                    $usage['plan'] = $plans->plan->plan_name;
                    $usage['provider'] = $plans->plan->provider;
                    $usage['buy_price'] = $plans->plan->buy_price+.42;                    
                    $usage['activated'] = $activated;
                    $usage['usage'] = '';//$data_usage;
                    $usage['call_duration'] = round($call_duration/60);
                    $usage['vat'] = round($plans->plan->sell_price - ($plans->plan->sell_price/1.2),2);
                    //net=100/(100+vatRate)*input;
                    //vat=input-net;
                    $usage['transaction_fee'] = round(($plans->plan->sell_price*4)/100,2);
                    $usage['total_amount'] = $plans->plan->sell_price;
                    $usage['profit'] = $plans->plan->sell_price - ($usage['buy_price']+$usage['vat']+$usage['transaction_fee']);
                    $usage['note'] = '';//($plans->plan->data_limit)?round(($data_usage/$plans->plan->data_limit)*100,2):'-';
                    $usage['extra'] ='';
                    $data[$i] = $usage;
                    $i++;
                }
            }else{
                $activated = Carbon::parse($user->created_at)->format('Y-m-d');                
                $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                    'history_from'=>1])->where('connect_date','>=',$activated)
                                    ->where('connect_date','<=',$expiry)->sum('duration');
                $usage['plan'] = 'Default Plan';
                $usage['provider'] = '';
                $usage['buy_price'] = '';                
                $usage['activated'] = $activated;
                $usage['usage'] = '';
                $usage['call_duration'] = round($call_duration/60);
                $usage['vat'] = '';
                $usage['transaction_fee'] = '';
                $usage['total_amount'] = '';
                $usage['profit'] = '';
                $usage['note'] = '';
                $usage['extra'] ='';
                $data[$i] = $usage;
                $i++;
            }   
        } 
        // print_R($data); 

        array_unshift($data,array('ID','Name','Phone','Parent/Child','Plan','Provider','Buy Price', 'Activated','Data Usage','Call Duration','Vat','Transaction Fee','Totoal Amount','Profit','Usage in %','Extra'));
        return Excel::download(new CustomExport($data), 'usage.csv');

        die();
        $user = ModalUser::find($user_id);
        $i_account = $user->i_account; 
        echo $i_account.chr(10);
        $remoteips = DB::table('bridge_server')->where('country_id',238)->get();
        if($remoteips){
            $auth_rule = array('i_account' => $i_account, 'cli_number' => $user->phone);
            foreach($remoteips as $bridge){
                $auth_rule['remote_ip'] = $bridge->server;
                $xml_data = SwitchHelper::switch_add_auth_rule_xml1($auth_rule);
                $temp =  SwitchHelper::call_switch_api($xml_data);
                print_r($temp);
                $auth_rule['cld_number']      = $user->phone;
                $auth_rule['cld_translation'] = $user->phone;
                $auth_rule['cli_translation'] = $bridge->service_no;
                $xml_data = SwitchHelper::switch_add_auth_rule_xml1($auth_rule);
                $temp = SwitchHelper::call_switch_api($xml_data);
                print_r($temp);
            }
        }

        die();

        $bolt = SIMHelper::dwp_sim_available_bolt_ons_xml();
        $response = SIMHelper::dwp_process_api($bolt);
        print_r($response);

        // libxml_use_internal_errors(true);
        // $xml = simplexml_load_string($output);

        if(Auth::id()==1){
            // libxml_use_internal_errors(true);
            // $xml = simplexml_load_string($output);
            die();  
       }

        $orders = SimRequest::whereIn('promocode', ['','AVOOSIM','AVDSIN','AV_DELRVND','STVIN002','MAHSH2020','AVJERIN','AVCHMMCWA','SJ100','AVDBBRN'])->get(); //
        foreach($orders as $order){
            $list = $pay = [];
            $user = ModalUser::find($order->user_id);
            foreach($order->list as $item){                
                if(!is_null($item->user_id)){ 
                    $user = ModalUser::find($item->user_id);                                  
                }
                if(!$user){
                    // echo $item->user_id.chr(10);
                    // echo $order->user_id;
                    continue;
                }
                $list['name'] = $user->name;
                $list['phone_number'] = $item->stock->phone_number;
                $list['agent'] = $order->promocode;
                $plans = UserPlan::where(['user_id'=>$user->id])->get();//,'plan_type'=>'sim'
                $list['activated'] = ($plans->isEmpty())?'':'1';
                $list['subscription'] = count($plans); 
                $payments = UserPayment::where('user_id',$user->id)->where(['status'=>1])->get();
                //'payment_method'=>'Direct Cash',
                if($payments->isEmpty()){
                    continue;
                }
                $i = 1;
                foreach($payments as $key => $payment){                    
                    $pay_date = Carbon::parse($payment->created_at)->format('Y-m-d H:i:s');
                    $list['amount_'.$i] = $payment->total_amount;
                    $list['txn_id_'.$i] = $payment->transaction_id;
                    $list['activated_'.$i] = $pay_date;                    
                    $i++;
                }            
                $data[] = $list;                 
            }                    
        }

        array_unshift($data,array('Name','Phone Number','Agent','Active','No Subscription','Amount','Transaction','Activated','Amount','Transaction','Activated'));
        return Excel::download(new CustomExport($data), 'sale_stivin.csv');

        //print_r($data);
        // $customer_id = Helper::get_option('mvno_customer_id');
        // echo Helper::get_option('bundle_mvno_key');
        // $userdata = ModalUser::find(11063);
        // $plans = AutoPlan::find(427);
        // $msisdn = $userdata->msisdn->phone_number; 
        // $subscription_id = $userdata->userDetail->sim_subscription_id;
        // $simbillplan   = $plans->plan->sim_billing_plan;                                       
        // $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$simbillplan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];
        // print_r(json_encode($sim_api_data));                 
        die();
        $end_point = 'https://avoomobile.com/api/v1/get_plan_list';
        $trust = DB::table('trusted_numbers')->where('id', 306)->first();
        $token = $trust->api_token;
        $headers = array(
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            // 'Content-Type: application/json'
        );
        $data['country_id'] = $user_id;
        
        $ch = curl_init($end_point);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch);       
        curl_close($ch);

        $data = json_decode($output);
        print_r($data);
        // $defult_plan = '182';
        // $i_account = '15472';
        // $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
        // $temp     = SwitchHelper::call_switch_api($xml_data);
        // print_r($temp);

        // $method = 'accountCredit';
        // $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, '9.99', 'USD'); 
        // $temp =  SwitchHelper::call_switch_api($credit_xml);

        // $xml_data = SwitchHelper::switch_update_plan_xml($i_account, 224);
        // $temp     = SwitchHelper::call_switch_api($xml_data);
        // echo '========='.chr(10);
        // print_r($temp);
        die();
        // $countries = DB::table('country')->get();
        // foreach ($countries as $country) {
        //     DB::table('tbl_rate_sheet')->where('country_code',$country->short_code)->update(['country_id'=>$country->id]); 
        // }
        // die();
        $countries = DB::table('country')->get();
        foreach ($countries as $country) {
            $details = [];
            $country_name = str_replace(' ', '-', $country->country_name);
            echo $country_name.chr(10);
            $details=DB::table('rate_details')->where('description',$country_name)->first();
            if(!$details){ 
                echo $country->id.'----'.$country->country_name.chr(10);
                //print_r($details);
                echo '====================================================='.chr(10);
            }
            if($details){
                //DB::table('rate_details')->where('country_id',$details->country_id)->update(['test_ctry_id'=> $country->id,'modify'=>1]);
            }
            // if(!$details){
            //     $details = DB::table('rate_details')->where('description',str_replace(' ', '-', $country->country_name))->first();
            // }
            // if($details){
            //     DB::table('rate_details')->where('country_id',$details->country_id)->update(['country_id'=>$country->id,'modify'=>1]);
            // }else{
            //     DB::table('rate_details')->where('country_id',$details->country_id)->update(['country_id'=> 0,'modify'=>1]);
            // }
        }            
        die();
        if(Auth::id() == 1){
            $contacts = DB::table('conference_contact')->where('user_id', $user_id)->orderby('created_at','desc')->get();
            foreach ($contacts as $key => $contact) {
               echo $key.'========================================'.$contact->created_at.chr(10);
               //print_r(json_decode(Crypt::decrypt($contact->contacts)));
               print_r(Crypt::decrypt($contact->contacts));
               
               //die();
            }
        //     //$base_token = base64_decode(Crypt::decrypt('123'));
        }
        die();
        $notification = NotificationLog::create(['user_id'=>'10000','message' => 'Auto Subscription EE Subscription Renewal Error','description'=>'Sub ID:1, msg:','status'=>'0']);
        print_R($notification);
        $obj = (object) array(
            'notify_id' => $notification->id,
            'subscripton'=> 1,
        );  

        FailureNotification::dispatch($obj)
            ->delay(now()->addMinutes(1));

        die();
        // $sold = ['447421956894','447421956896',
        //         '447421956897','447421956898','447421956899','447421956901','447421956902',
        //         '447421956903','447421956904','447421956905','447421956906','447421956907',
        //         '447421956908','447421956909','447421956910','447421956911','447421956913',
        //         '447421956914','447421956915','447421956916','447421956917','447421956918',
        //         '447421956919','447421956920','447421956921','447421956922','447421956923',
        //         '447421956924','447421956925','447421956926','447421956927','447421956928',
        //         '447421956929','447421956930','447421956931','447421956933','447421956934',
        //         '447421956935','447421956936','447421956937','447421956938','447421956939',
        //         '447421956940','447421956942','447421956960','447421956961','447421956963'];
        // foreach($sold as $stock){
        //     SimStock::where('phone_number',$stock)->update(['status' => 0]);
        //     $stock_id = SimStock::where('phone_number',$stock)->value('id');
        //     $autoplan = AutoPlan::create(['user_id'=>'11237','transaction_id'=>'','card_id'=>0,'plan_id'=>15,'plan_type'=>'sim','bundle_id'=>0,'amount'=>'5.83','tax'=>'1.17','total_amount'=>'6.99','card_expiry'=>'0000-00-00','card_type'=>'','gateway'=>'Paypal','adv_pay'=>'0','status'=>'0']);

        //     SimList::insert(['request_id'=> '516','autoplan_id'=>$autoplan->id,'stock_id'=> $stock_id]);
                            
        // }      

        // $contacts = DB::table('conference_contact')->where('user_id', 11139 $user_id)->orderby('created_at','desc')->get();
        // foreach ($contacts as $key => $contact) {
        //    echo $key.'========================================'.$contact->created_at.chr(10);
        //    print_r(json_decode(Crypt::decrypt($contact->contacts)));
        //    die();
        // }
        // die();
        $conf_id = 1;
       
        $source = 'http://149.36.7.16:81/24-1-2020/0-91-1922-2157-2157-447766742689-o-2340-240120-124207.wav';

        $path = public_path().'/record/'.$user_id; 
        $destination = $path.'/'.time().$conf_id.'.wav';                           
        //$source = 'https://www.avoomobile.com/public/rate-sheet.pdf';
        if (!File::exists($path)) {      
            File::makeDirectory($path, 0777, true, true);
        }
        if ( copy($source, $destination) ) {
            echo "Copy success!";
        }else{
            echo "Copy failed.";
        }

        $source = '149.36.7.16:81//31-1-2020//0-91-2064-2245-2245-919746291804-o-2524-310120-094110.wav';
        if(!@copy($source, $destination))
        {
            $errors= error_get_last();
            print_r($errors);
            echo "COPY ERROR: ".$errors['type'];
            echo "<br />\n".$errors['message'];
        } else {
            echo "File copied from remote!";
        }

        die();
           
        $users = ModalUser::whereNotNull('i_account')->get(); //->where('id',10102)
        $data = [];
        $i = 0;
        foreach ($users as $user) {
            $user_plans = UserPlan::where('user_id', $user->id)->get();
            $expiry = false; 
            $usage['ID'] = $user->id;
            $usage['user_name'] = $user->name;
            $usage['phone'] = $user->phone;
            $usage['user_type'] = ($user->parent_id)?'C':'P';
            if($user_plans->isNotEmpty()){
                foreach($user_plans as $index => $plans){
                    $activated = Carbon::parse($plans->created_at)->format('Y-m-d');
                    if($expiry && $activated < $expiry){                    
                        $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');
                        $new_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                                            'service_type'=>'DATA'])->where('date','>',$activated)
                                            ->where('date','<',$expiry)->sum('duration');
                        $new_usage = number_format(($new_usage/(1024*1024*1024)),2,'.','');
                        $data[$i-1]['usage'] = $data_usage-$new_usage;
                        $data[$i-1]['call_duration'] -= round($call_duration/60);
                        $data[$i-1]['note'] = ($plans->plan->data_limit)?round(($data[$i-1]['usage']/$user_plans[$index-1]->plan->data_limit)*100,2):'-';
                        $data[$i-1]['extra'] = 1;
                        //$data[$i-1]['usage'] = 'processing'; //
                    }

                    $expiry = Carbon::parse($activated)->addDays(31)->format('Y-m-d');
                    $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');

                    $data_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                                        'service_type'=>'DATA'])->where('date','>',$activated)
                                        ->where('date','<',$expiry)->sum('duration');
                    $data_usage = number_format(($data_usage/(1024*1024*1024)),2,'.','');                    
                    $usage['plan'] = $plans->plan->plan_name;
                    $usage['buy_price'] = $plans->plan->buy_price+.42;                    
                    $usage['activated'] = $activated;
                    $usage['usage'] = $data_usage;
                    $usage['call_duration'] = round($call_duration/60);
                    $usage['vat'] = round($plans->plan->sell_price - ($plans->plan->sell_price/1.2),2);
                    //net=100/(100+vatRate)*input;
                    //vat=input-net;
                    $usage['transaction_fee'] = round(($plans->plan->sell_price*4)/100,2);
                    $usage['total_amount'] = $plans->plan->sell_price;
                    $usage['profit'] = $plans->plan->sell_price - ($usage['buy_price']+$usage['vat']+$usage['transaction_fee']);
                    $usage['note'] = ($plans->plan->data_limit)?round(($data_usage/$plans->plan->data_limit)*100,2):'-';
                    $usage['extra'] ='';
                    $data[$i] = $usage;
                    $i++;
                }
            }else{
                $activated = Carbon::parse($user->created_at)->format('Y-m-d');                
                $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                    'history_from'=>1])->where('connect_date','>=',$activated)
                                    ->where('connect_date','<=',$expiry)->sum('duration');
                $usage['plan'] = 'Default Plan';
                $usage['buy_price'] = '';                
                $usage['activated'] = $activated;
                $usage['usage'] = '';
                $usage['call_duration'] = round($call_duration/60);
                $usage['vat'] = '';
                $usage['transaction_fee'] = '';
                $usage['total_amount'] = '';
                $usage['profit'] = '';
                $usage['note'] = '';
                $usage['extra'] ='';
                $data[$i] = $usage;
                $i++;
            }   
        } 
        // print_R($data);

        array_unshift($data,array('ID','Name','Phone','Parent/Child','Plan','Buy Price', 'Activated','Data Usage','Call Duration','Vat','Transaction Fee','Totoal Amount','Profit','Usage in %','Extra'));
        return Excel::download(new CustomExport($data), 'usage.csv');
        
        die();        
        /*$time_zone = 'Asia/Kolkata';
        $default_zone = date_default_timezone_get();
        // date_default_timezone_set($time_zone);
        echo Carbon::parse('2020-01-16 14:55:29')->timezone($default_zone)->format('Y-m-d H:i:s'); 


        die();
        $conf = DB::table('conference_create')->where('user_id',10030)->first();
        $msisdn = json_decode($conf->msisdnlist,true);
        $phones = array_column($msisdn, 'emailid');

        print_r($msisdn);
        die();
        $bolt = SIMHelper::dwp_sim_available_bolt_ons_xml();
        $response = SIMHelper::dwp_process_api($bolt);
        // print_r($response);

        die();
        $start_time = microtime(true);
        $trust = DB::table('trusted_numbers')->where('id', 306)->first();
        $token = $trust->api_token;
        $data['offset'] = 1;
        $end_point = 'https://avoomobile.com/api/v1/conference_history';

        $headers = array(
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            // 'Content-Type: application/json'
        );
        
        $ch = curl_init($end_point);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch);       
        curl_close($ch);
     
        $resultdata = json_decode($output);
        print_r($resultdata);
        $end_time = microtime(true);
        $exec_time = round(($end_time - $start_time), 5);

        echo 'exec '. $exec_time;

        die();*/
        $users = ModalUser::whereNotNull('i_account')->where('id',10102)->get(); //->take(5)
        $data = [];
        $i = 0;
        foreach ($users as $user) {
            $user_plans = UserPlan::where('user_id', $user->id)->get();
            $expiry = false; 
            $usage['ID'] = $user->id;
            $usage['user_name'] = $user->name;
            $usage['phone'] = $user->phone;
            $usage['user_type'] = ($user->parent_id)?'C':'P';
            if($user_plans->isNotEmpty()){
                foreach($user_plans as $index => $plans){
                    $activated = Carbon::parse($plans->created_at)->format('Y-m-d');
                    if($expiry && $activated < $expiry){                    
                        $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');
                        $new_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                                            'service_type'=>'DATA'])->where('date','>',$activated)
                                            ->where('date','<',$expiry)->sum('duration');
                        $new_usage = number_format(($new_usage/(1024*1024*1024)),2,'.','');
                        $data[$i-1]['usage'] = $data_usage-$new_usage;
                        $data[$i-1]['call_duration'] -= round($call_duration/60);
                        $data[$i-1]['note'] = round(($data[$i-1]['usage']/$user_plans[$index-1]->plan->data_limit)*100,2);
                        $data[$i-1]['extra'] = 1;
                        //$data[$i-1]['usage'] = 'processing'; //
                    }

                    $expiry = Carbon::parse($activated)->addDays(31)->format('Y-m-d');
                    $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                        'history_from'=>1])->where('connect_date','>=',$activated)
                                        ->where('connect_date','<=',$expiry)->sum('duration');

                    $data_usage = DB::table('usage_history')->where(['user_id'=>$user->id,
                                        'service_type'=>'DATA'])->where('date','>',$activated)
                                        ->where('date','<',$expiry)->sum('duration');
                    $data_usage = number_format(($data_usage/(1024*1024*1024)),2,'.','');                    
                    $usage['plan'] = $plans->plan->plan_name;
                    $usage['buy_price'] = $plans->plan->buy_price+.42;                    
                    $usage['activated'] = $activated;
                    $usage['usage'] = $data_usage;
                    $usage['call_duration'] = round($call_duration/60);
                    $usage['vat'] = round($plans->plan->sell_price - ($plans->plan->sell_price/1.2),2);
                    $usage['transaction_fee'] = round(($plans->plan->sell_price*4)/100,2);
                    $usage['total_amount'] = $plans->plan->sell_price;
                    $usage['profit'] = $plans->plan->sell_price - ($usage['buy_price']+$usage['vat']+$usage['transaction_fee']);
                    $usage['note'] = round(($data_usage/$plans->plan->data_limit)*100,2);
                    $usage['extra'] ='';
                    $data[$i] = $usage;
                    $i++;
                }
            }else{
                $activated = Carbon::parse($user->created_at)->format('Y-m-d');                
                $call_duration = DB::table('user_calls')->where(['user_id'=>$user->id,
                                    'history_from'=>1])->where('connect_date','>=',$activated)
                                    ->where('connect_date','<=',$expiry)->sum('duration');
                $usage['plan'] = 'Default Plan';
                $usage['buy_price'] = '';                
                $usage['activated'] = $activated;
                $usage['usage'] = '';
                $usage['call_duration'] = round($call_duration/60);
                $usage['vat'] = '';
                $usage['transaction_fee'] = '';
                $usage['total_amount'] = '';
                $usage['profit'] = '';
                $usage['note'] = '';
                $usage['extra'] ='';
                $data[$i] = $usage;
                $i++;
            }   
        } 
        // print_R($data);

        array_unshift($data,array('ID','Name','Phone','Parent/Child','Plan','Buy Price', 'Activated','Data Usage','Call Duration','Vat','Transaction Fee','Totoal Amount','Profit','Usage in %','Extra'));
        return Excel::download(new CustomExport($data), 'usage.csv');
        



        // $lists = [];        
        // $phone_number = '447591111141';
        // foreach ($lists as $list) {
        //     $phone_number = $phone_number+1;
        //     $row['imsi_number'] = $list;
        //     $row['sim_number'] = $list;
        //     $row['phone_number'] = $phone_number;
        //     $row['category'] = 'normal';
        //     $row['price'] = '0.00';
        //     $row['status'] = 1;
        //     $row['dealer_id'] = 1;
        //     $row['box_no'] = 'B5/1';
        //     $row['provider'] = 'VUK';
        //     $row['verified'] = 0;
        //     DB::table('tbl_sim_stock')->insert($row);  
        // }
        die();
        $user_id = 10030;
        $country_id = 238;

        $stock_id = ModalUser::where('id', $user_id)->value('stock_id');
        $access_number = '';
        $call_duration = 0;
        if($stock_id){
            $provider = SimStock::where('id', $stock_id)->value('provider');
            if($provider == 'O2'){
                $activated = DB::table('user_plans')->where(['user_id' => $user_id, 'status' => 1])->value('created_at');
                if($activated){
                    $call_duration =  DB::table('user_calls')->where(['user_id' => $user_id, 'history_from' => 1])
                                        ->where('connect_date', '>', $activated)->sum('duration');
                }                
                $call_duration = $call_duration/60;
                if($call_duration < '275'){
                    $access_number = DB::table('tbl_common_access')->where('country_id', $country_id)
                                        ->where('category', $provider)->inRandomOrder()->value('access_number');
                }
            }
        }

        $country = Country::select('dial_code','access_number')
                        ->where('id', $country_id)->first();

        if($access_number == ''){
            $access_number = $country->access_number;
        }

        $dial_code = $country->dial_code;
        // echo $access_number;

        /*$users = ModalUser::select('id','i_account')->whereNotNull('i_account')->get();
        foreach ($users as $user) {
            $trust = DB::table('trusted_numbers')->where('user_id', $user->id)->where('default_number', 1)->first();

            $xml_data = '<?xml version="1.0"?>      
            <methodCall>
              <methodName>updateAccount</methodName>
              <params>
                <param>
                  <value>
                    <struct>
                      <member>
                        <name>i_account</name>
                        <value><int>'. $user->i_account .'</int></value>
                      </member>                      
                      <member>
                        <name>cli_translation_rule</name>
                        <value><string>'. $trust->trusted_number.'</string></value>
                      </member>
                    </struct>
                  </value>
                </param>
              </params>
            </methodCall>';

            $temp =  SwitchHelper::call_switch_api($xml_data);
            if (array_key_exists("fault", $temp)) {
                echo $user->id;
            }
        }*/
        
        // // echo Crypt::encrypt(911);
        
        // $users = ModalUser::select('users.id','name','phone','email','ud.user_platform','users.created_at')->join('user_data as ud','users.id', '=', 'user_id')->whereNull('stock_id')->get();
        // foreach ($users as $user) {
        //     if($user->order){
        //         $user->status = 'In-Active';
        //     } else {
        //         $user->status = '';
        //     }
        // }
        
        // $users->prepend(array('ID','Name','Phone','Email','Platform','Created','Status'));
        // return Excel::download(new CustomExport($users->toArray()), 'users.csv');
        
        die();
        $curr_day = Carbon::now()->format('Y-m-d');
        $next_renewal = Carbon::now()->addDays(31)->format('Y-m-d');

        $paidUsers = DB::table('auto_plan')->where('next_renewal','>=', $curr_day)
                        ->where('adv_pay','>', 0)->where('status', 1)
                        ->where('card_expiry', '>=', $curr_day)                            
                        ->orderBy('user_id', 'asc')                        
                        ->get(); 

        if($paidUsers->isNotEmpty()) {     
            print_R($paidUsers);
            die();        
            foreach ($paidUsers as $subDetails) {
                $user_id = $subDetails->user_id;
                $user = DB::table('users')->select('name','email','phone','i_account',
                            'currency','currency_symbol')
                            ->join('country','country_id','=','country.id')
                            ->where('users.id', $user_id)->where('users.status', '1')
                            ->first(); //taking user details from users table against the user

                $blocked = Helper::check_fraudster($user_id); //check whether user is in fraud list
                if(!$user || $blocked){  //if in fraud or user doen't active escape
                    continue;
                }
                
                $billamount     = $subDetails->total_amount;
                $referenceid    = $subDetails->transaction_id;
                $currency       = $user->currency;
                $i_account      = $user->i_account; //parent user account
                // $autoplanid     = explode(',', $subDetails->id); //unique id from autoplan table of the grouped users

                $autoplan['adv_pay'] = $subDetails->adv_pay - 1;
                $autoplan['next_renewal'] = $next_renewal;
                $plans =  AutoPlan::updateOrCreate(['id' => $subDetails->id],$autoplan);//update txn num
                        
            }
        }
        
        die();
        $users = ModalUser::whereNotNull('i_account')->get();
        $did_number = '+443339980530';
        foreach($users as $user){
            // $vp_password = Helper::unique_code(10);
            // $data['i_account'] = $user->i_account;
            // $data['vp_password'] = $vp_password;

            // $xml_data = SwitchHelper::switch_custom_profile_xml($data);
            // $temp =  SwitchHelper::call_switch_api($xml_data);

            // if (array_key_exists("fault", $temp)) {
            //     echo 'failed-'.$user->id.chr(10);
            // } else {
            //     DB::table('user_data')->where('user_id', $user->id)->update(['vp_password' => $vp_password]);
            //     echo 'success-'.$user->id.chr(10);
            // }

            $xml_data = SwitchHelper::switch_delete_smartdial_xml($user->i_account,$did_number);
            $temp =  SwitchHelper::call_switch_api($xml_data);

            if (array_key_exists("fault", $temp)) {
                echo 'failed-'.json_encode($temp).chr(10);
            } else {
                DB::table('diddial')->where('id', $data->id)->where('app_user', 1)->where('user_id', $user->id)->delete();
                echo 'success-'.$user->id.chr(10);
            }
        }

        die(); 
        foreach ($conf as $key => $value) {
            $data['msisdnlist'] = [["name"=>"Shine","phoneno"=>"+447766742689","email"=>""],["name"=>"Support","phoneno"=>"+919188150733","email"=>""]];
            $data['schedule'] = 1;
  
            $trust = DB::table('trusted_numbers')->where('id', 306)->first();
            $token = $trust->api_token;

            $end_point = 'https://avoomobile.com/api/v1/schedule_conference';

            $headers = array(
                'Content-type: application/json',
                'Authorization: Bearer '.$token,
                // 'Content-Type: application/json'
            );
            
            $ch = curl_init($end_point);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $output = curl_exec($ch);       
            curl_close($ch);
         
            $resultdata = json_decode($output);
            // print_r($resultdata);
            // die();
            $data['conference_id'] = $resultdata->success->conference->id;

            $end_point = 'https://avoomobile.com/api/v1/confirm_conference';

            $ch = curl_init($end_point);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $output = curl_exec($ch);       
            curl_close($ch);
            
            print_r($output);
        }





        die();
        $stock_id = ['1173'];  
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

        $user = ModalUser::where('id', $parent_id)->first();

        $obj = (object) array(
            'order' => $simDetails,
            'user' => $user,
            'order_id' => $order_id,
            'subject' => 'Sim Activation Completed',
            'heading' => 'Sim Activation',
        ); 

        // $send_at = Carbon::now()->addMinutes(10);

        Mail::to('jijojoseph001@gmail.com')
            // ->cc()
            ->bcc('nettransinfotech123@gmail.com')
            ->send(new OrderComplete($obj)); 
        // return response()->json(['success' => 1]);

        die();


        $promocodes = DB::table('admins')->where('parent_id', 34)->get()->pluck('promocode')->toArray(); 

        $order_list = DB::table('tbl_smi_request')->whereIn('promocode', $promocodes)->get();
        print_r($order_list);
        die();
        // $conf = DB::Table('conference_create')->where('id', 541)->first();
        // $user = ModalUser::find(10238);
        // $username = $user->first_name.' '.$user->last_name;
        // $useremail = $user->email;
        // $conf_key = Helper::get_option('conference_key');
        // $end_point  = '/CreateAudioConf?';
        // $settings = json_decode($conf->conf_settings);
        // $voicerec = ($settings->voicerecord)?true:false;
        // $dialhost = ($settings->dialhost)?true:false;
        // $endhost  = ($settings->endhostconf)?true:false;
        // $askname  = ($settings->asknamedial)?true:false;
        // $askinput = ($settings->askdialinput)?true:false;
        // $issecure = ($settings->issecure)?true:false;
        // $sendnow = ($conf->sendnow)?true:false;
        // // $start_time = Carbon::now()->format('Y-m-d H:i:s');
        // // $startdatetime = ($conf->startdatetime < $start_time)?$start_time:$conf->startdatetime;
        // // $startdatetime = ($conf->sendnow == 1) ? $start_time : $startdatetime; //->addMinutes(1)
        // $startdatetime = Carbon::parse($conf->startdatetime)->format('m/d/Y H:i:s');
        // $endtime  = Carbon::parse($conf->enddatetime)->format('m/d/Y H:i:s');
        // $conf->schdreminmincsv = 0;    
        // $apidata  = ['ukey'=>$conf_key,'serviceno'=> $conf->serviceno,'sendcli'=>$conf->sendcli, 'audioconfid'=>0,'confname'=> $conf->confname,'audioconftype'=>2,'isenabled'=>true,'issecure_audioconf'=>$issecure,'isvoicerecording'=>$voicerec,'dialhostfirst'=>$dialhost,'endconfhostexist'=>$endhost,'isaskname'=>$askname,'isaskinput_dialout'=>$askinput,'isplay_endconf'=>false,'notifyvalidityinmin'=>$conf->notifyvalidityinmin,'desc'=>$conf->description,'maxmembers'=>$conf->maxmembers,'schdtype'=>$conf->schdtype,'schdreminmincsv'=>$conf->schdreminmincsv,'contacttype'=>2,'filetype'=>$conf->filetype,'arrcontactgroup'=>[],'msisdnlist'=>json_decode($conf->msisdnlist),'chairperson_contactno'=>$conf->sendcli,'chairperson_name'=>$username,'chairperson_emailid'=>$useremail,'isincrementcontacts'=>true,'startdatetime'=>$startdatetime,'enddatetime'=>$endtime,'tmpschdtimerange'=>"",'selectedweekdays'=>json_decode($conf->selectedweekdays),'isschd'=>$sendnow];

        // print_r(json_decode(json_encode($apidata)));
        // die();


        // if(Auth::id() == 1){
        //     $contacts = DB::table('conference_contact')->where('user_id',10030)->get();
        //     foreach ($contacts as $contact) {
        //        print_r(json_decode(Crypt::decrypt($contact->contacts)));
        //     }            
        // }
        // die(); 
         $users = ['10322'];
        die();
        if(Auth::id() == 1){
            foreach($users as $user_id){
                 $user = ModalUser::select('users.id','name','i_account','currency_symbol',
                        'tax','currency','short_code','email')
                        ->join('country','country.id','=','country_id')
                        ->where('users.id', $user_id)->first();     
                
                $card_data = UserCreditCard::where('user_id', $user_id)->first();
      
                $today = Carbon::now()->format('Y-m-d');
                $today_payment = UserPayment::where('user_id', $user_id)->whereDate('created_at',$today)->count();

                if($today_payment == 1){
                    $email = $user->email;
                    $referenceid    = $card_data->transaction_id;
                    $amount         = '11.99';
                    $tax_amount     = ($amount * $user->tax)/100;
                    $billamount     = number_format($amount, 2, '.', "");
                    $currency       = $user->currency;
                    $i_account      = $user->i_account;

                    $environment    = Helper::get_option('paypal_nvp_mode');
                    $api_endpoint   = 'https://api-3t.paypal.com/nvp';
                    $api_user       = Helper::get_option('paypal_nvp_username');
                    $api_password   = Helper::get_option('paypal_nvp_password');
                    $api_signature  = Helper::get_option('paypal_nvp_signature');


                    $version        = urlencode('86.0');  
                    $method_name    = 'DoReferenceTransaction';
                    $payment_type   = urlencode('Sale');

                    $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$billamount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";
                    echo $nvp_req;
                   die();
                    // $response = ['ACK'=>'Failed','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
                    $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 
                    $rsponse_status = strtoupper($response["ACK"]);

                    $payment['user_id']         = $user_id;                         
                    $payment['amount']          = $amount;      
                    $payment['tax_amount']      = $tax_amount;
                    $payment['total_amount']    = $billamount;
                    $payment['payment_for']     = 'Plan subscriptions charge for Oct-Nov';
                    $payment['payment_method']  = 'Paypal';  
                    $payment['description']     = 'Plan subscriptions charge for Oct-Nov by '.Auth::user()->first_name.' '.Auth::user()->last_name;

                    
                    if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                        $txn_id = $response['TRANSACTIONID'];
                        $payment['transaction_id']  = $txn_id; 
                        $payment['status'] = '1';
                        DB::table('user_payments')->insert($payment);
                        
                        $obj = new \stdClass();
                        $obj->name = $user->name;
                        $obj->amount = $user->currency_symbol.$billamount;
                        $obj->transaction_id = $txn_id;            
                        $obj->subject = 'AVOOMobile Payment Confirmation';
                        $obj->heading = 'Payment Status - Success';

                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){                 
                            Mail::to($email)
                                // ->cc()
                                ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                    }else{
                        $error_msg = $user_id.''.json_encode($response).chr(10).chr(10);
                        $fp = fopen('card_error.txt', 'a+');
                        fwrite($fp, $error_msg.chr(10));
                        fclose($fp);
                                   
                        $payment['transaction_id'] = ''; 
                        $payment['description'] = 'error : '.$response['L_LONGMESSAGE0']; 
                        $payment['status'] = '0';
                        // print_r($payment);
                        DB::table('user_payments')->insert($payment);
                    }
                }
            }
        }
        die();

        $contacts = DB::table('conference_contact')->whereId(393)->first();

        print_r(json_decode(Crypt::decrypt($contacts->contacts)));
        Crypt::encrypt(708);
        die();
        // $stock_id = ['1972','1946','1947'];
        // $simList = SimList::whereIn('stock_id', $stock_id)->get();
        // $user_ids = array_column($simList->toArray(), 'user_id');
        // $user_list = implode(',', $user_ids);
        // print_r($user_list);
        // print_r($simList);
        // die();
        // $user = ModalUser::find(10507); 
        // $country_id = $user->country_id;
        // $country_code = $user->country->country_code; 
        // $currency = $user->country->currency;
        // $currency_symbol = $user->country->currency_symbol;           


        // $default_zone = date_default_timezone_get();
        // date_default_timezone_set($user->time_zone);

        // $start_time = Carbon::now()->format('Y-m-d H:i:s');
        // echo $start_time.chr(10);
        // $start_time = Carbon::parse($start_time)->timezone($default_zone)->format('Y-m-d H:i:s');
        // $end_time = Carbon::parse($start_time)->addMinutes(60)->timezone($default_zone)->format('Y-m-d H:i:s');

        // echo $start_time.chr(10);
        // echo $end_time.chr(10);
        // die();
        // $default_zone = date_default_timezone_get();
        // echo $default_zone.chr(10);
        // // date_default_timezone_set($user->time_zone);
        // date_default_timezone_set('Asia/Kolkata');
        // $this->index();
        // $default_zone = date_default_timezone_get();
        // echo $default_zone.chr(10);
        // die();

        // $start_time = ($request->startdatetime)?:Carbon::now()->format('Y-m-d H:i:s');
        // $start_time = Carbon::parse($start_time)->format('Y-m-d H:i:s');
        // $end_time = Carbon::parse($start_time)->addMinutes(60)->format('Y-m-d H:i:s');    

        // die();     
        // $bridges = DB::table('bridge_server')->get(); //->where('country_id', $user->country_id)

        // $auth_rule = array('i_account' => '10000', 'cli_number' => '919746291804');
        // foreach($bridges as $bridge){
        //     $auth_rule['remote_ip'] = $bridge->server;
        //     print_r($auth_rule);
        //     // $xml_data = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
        //     // $temp =  SwitchHelper::call_switch_api($xml_data);
        // }

        // die();
        // $data = ['name' => 'q5UIpPuRrsH3BNKvSJIM63qIPpTdC6odSHC3xB4mgiEEdTXWUJsAWKwArWGCUCuuwNw6aVUl6Qe8oW5bWslhmYtWG8'];
        // $validator = Validator::make($data, ['name' => 'nullable|max:20']); 

        // if ($validator->fails()){
        //     print_r($validator->errors()->all());            
        // } else { 
        //     echo 'testing';
        // }
        // die();

        $data['msisdnlist'] = [["name"=>"Shine","phoneno"=>"+447766742689","email"=>""],["name"=>"Support","phoneno"=>"+919188150733","email"=>""]];
        $data['schedule'] = 1;
        date_default_timezone_set('Asia/Kolkata');

        $data['startdatetime'] = Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s');
        $trust = DB::table('trusted_numbers')->where('id', 306)->first();
        $token = $trust->api_token;

        $end_point = 'https://avoomobile.com/api/v1/schedule_conference';

        $headers = array(
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            // 'Content-Type: application/json'
        );
        
        $ch = curl_init($end_point);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch);       
        curl_close($ch);
     
        $resultdata = json_decode($output);
        // print_r($resultdata);
        // die();
        $data['conference_id'] = $resultdata->success->conference->id;

        $end_point = 'https://avoomobile.com/api/v1/confirm_conference';

        $ch = curl_init($end_point);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch);       
        curl_close($ch);
        
        print_r($output);

        die();

        // Carbon::setUserTimezone('Asia/Kolkata');
        // echo Carbon::createFromTime(12, 0, 0, 'UTC')->tzFormat('H:i'); // 14:00        
        date_default_timezone_set('Asia/Kolkata');
        $default_zone = date_default_timezone_get();
        $user_zone = 'Europe/London';
        $start_time = '2019-11-20 17:50:10';
        echo Carbon::parse($start_time)->toDateTimeString().chr(10);
        echo Carbon::parse($start_time)->timezone($user_zone)->toDateTimeString();
        die();
        $users = ModalUser::all();

        foreach($users as $user){
            DB::table('users')->where('id', $user->id)->update(['time_zone'=>$user->country->time_zone]);            
        }

        die();
        $tariff = DB::table('tbl_tariff')->get();

        foreach($tariff as $conf_t){
            DB::table('country')->where('id',$conf_t->country_id)->update(['time_zone'=>$conf_t->time_zone]);
        }


        die();
        // $data['askdialinput'] = 1;
        // $data['asknamedial'] = 1;
        // $data['confname'] = 'sample3';
        // $data['dialhost'] = '1';
        // $data['endhostconf'] = '1';
        // $data['startdatetime'] = '2019-11-20 11:39:35';
        // $data['voicerecord'] = '0';
        $data['msisdnlist'] = [["name"=>"Akshara Uuu1","phoneno"=>"12345646795","email"=>""],["name"=>"Berry Weiss","phoneno"=>"9395153617","email"=>""]];

        // $data = ['conf_name'=> 'Blockchain Conference' , 'schedule_at' =>'2',  'startime' => '2019-10-24 10:10:10', 'description' => 'Blockchain Developers confrenece', 'msisdnlist' => [['name'=>'Jijo Joseph','phone'=>'919746291804'],['name'=>'Jo Joseph','phone'=>'919526764512']]];

            // print_r($data);
            // die();

        $token = 'ONs7M4GbKvUFCtaeH82fWgnsI031JYj4m9IVWVUmR9Qh8IMPzLDXQrrtLSyN23QfdG5pXjVB4I0fNIhzXrAjgwR3fu';

            $end_point = 'https://avoomobile.com/api/v1/schedule_conference';

            // $end_point = 'https://avoomobile.com/api/v1/list_conference';
            // $data = json_encode($data);

            $headers = array(
                'Content-type: application/json',
                'Authorization: Bearer '.$token,
                // 'Content-Type: application/json'
            );
            
            $ch = curl_init($end_point);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //        'Content-Type: application/json',
            //        'Authorization: Bearer '.$token,
                   
            //        ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            // curl_setopt($ch, CURLOPT_USERPWD, "$api_userpwd");
            // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $output = curl_exec($ch);       
            curl_close($ch);
         
            $data = json_decode($output);
            print_r($data);
            die();


        die();
        $msdnData = json_decode('{"919746291804":{"tariff":"0.0112","time_zone":"Asia\/Kolkata","time":"2019-11-13 19:25:18"},"443339980500":{"tariff":"0.0083","time_zone":"Europe\/London","time":"2019-11-13 13:55:18"},"127766742689":{"tariff":"0","time_zone":"","time":""}}',true);

        $msisdnlist = [['phoneno'=>'919746291804','name'=>'Jijo'],['phoneno'=>'443339980500','name'=>'Shine'],['phoneno'=>'447766742689','name'=>'Shine']];

        $msisdn = $msisdn_tariff = [];
        $start_time = microtime(true);
        $start_date = Carbon::now();
        foreach ($msisdnlist as $contacts) {                    
            $email = isset($contacts['email'])?$contacts['email']:'';
            $msisdn[] = array('phoneno'=>$contacts['phoneno'],'emailid'=>$email,'contact_person_name'=>$contacts['name']);
            if(array_key_exists($contacts['phoneno'], $msdnData)){
                $msisdn_data[$contacts['phoneno']] = $msdnData[$contacts['phoneno']];
            }else{
                $tariff = $this->check_tariff($contacts['phoneno']);            
                $tariff['time'] = Carbon::parse($start_date)->timezone($tariff['time_zone'])->toDateTimeString();
                $msisdn_data[$contacts['phoneno']] = $tariff;                        
            }
        } 
        $end_time = microtime(true);
        $exec_time = round(($end_time - $start_time), 5);
        // echo sprintf('%.8f',floatval($exec_time)).chr(10);
        $est_cost = array_sum(array_column($msisdn_tariff,'tariff')) * 30;
        // echo $est_cost;
        print_r($msisdn_data);
        // print_r($msisdn);

        die();
        $tariff = DB::table('tbl_tariff')->select('tbl_tariff.*','short_code')->join('country','country_id','=','country.id')
                        ->whereNull('time_zone')->get();

        foreach($tariff as $conf_t){
            $timezone = timezone_identifiers_list(4096, $conf_t->short_code);
            
            // print_r($conf_t);
            if(count($timezone)>1){
                $county[] = $conf_t->country_id;
                echo $conf_t->description .chr(10);
                print_r($timezone);
                // DB::table('tbl_tariff')->where('id',$conf_t->id)->update(['time_zone'=>$timezone[0]]);
            }
            //

            //Carbon::parse('2000-02-20 00:00', 'asia/kathmandu')
        }
        print_r(array_unique($county));
        die(); 

        $tariff_list = DB::table('tbl_tariff_list')->select(DB::raw("group_concat(prefix) as prefix"),'description','tariff','country_id','country_name','country_code')->groupBy('description','tariff')->get(); 
        // $tariff
        foreach($tariff_list as $tariff){
            $rate['prefix'] = ','.$tariff->prefix.',';
            $rate['description'] = $tariff->description;
            $rate['tariff'] = $tariff->tariff;
            $rate['country_id'] = $tariff->country_id;
            $rate['country_name'] = $tariff->country_name;
            $rate['country_code'] = $tariff->country_code;
            DB::table('tbl_tariff')->insert($rate);
        }   
        $tariff = DB::table('tbl_tariff')->get();
        foreach($tariff as $conf_t){
            if(strpos(str_replace(' ','_',strtolower($conf_t->description)), str_replace(' ','_',strtolower($conf_t->country_name))) !== false){
                // echo "Word Found!";
            } else{
                if(is_null( $conf_t->time_zone))
                    print_r($conf_t);
                               
            }
            // if (!strpos($conf_t->description, $conf_t->country_name)) {
            //     // print_r($conf_t);
            //     echo $conf_t->description.', '. $conf_t->country_name.chr(10);
            // }
        }     
        die();
        // $mvno_key = Helper::get_option('bundle_mvno_key');
        // $end_point = '/core/sims/swap?MVNO='.$mvno_key; //api for sim swap 
        // $sim_api_data = ["msisdn"=>'447421938135',"newIccId"=>'8944125646610074966',"oldIccId"=>'8944125646610079205',"externalReference"=>'AVC10167',"channel"=>"Web","comments"=>'tried-JJ'];

        // $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
        // print_r($response);
        die();
        $users = DB::table('users')->get();
        foreach($users as $user){
            $country_id = $user->country_id;
            $country = Country::whereId($country_id)->first();
            $call_settings = ['accessnumber_support' => $country->accessnumber_support, 'wifi_support' => $country->wifi_support,
                            'callback_support' => $country->callback_support, 'conference_support' => $country->wifi_support];
            $call_settings = json_encode($call_settings);
            DB::table('user_data')->where('user_id', $user->id)->update(['call_settings' => $call_settings]);
        }
        // $users = DB::table('user_data')->get();
        // foreach($users as $user){
        //     $settings = json_decode($user->call_settings);
        //     $call_settings = ['accessnumber_support' => ($settings->accessnumber_support)?1:0, 'wifi_support' => ($settings->wifi_support)?1:0, 'callback_support' => ($settings->callback_support)?1:0, 'conference_support' => ($settings->wifi_support)?1:0, 'bundle_o' => 0];
        //     $call_settings = json_encode($call_settings);
        //     DB::table('user_data')->where('id', $user->id)->update(['call_settings' => $call_settings]);
        // }
        die();
        
            

            // $mvno_key = Helper::get_option('bundle_mvno_key');
            // $ports = TblPorting::where('status','4')->get();
            // foreach($ports as $port){
            //     if($port->porting_to != '447388367998'){
            //         $user = DB::table('user_data')->where('user_id', $port->list->user_id)->first();

            //         $end_point = '/superapi/subscriptions/'. $user->sim_subscription_id.'/msisdn?from='.$port->stock->temp_number.'&to='.$port->porting_to.'&mvno='.$mvno_key;                    
            //         $response = Helper::call_sim_process_api($end_point, '', 'patch');

            //         echo $response->resultType .'--'.$port->list->user_id.chr(10);
            //         print_r($response->messages);
                    
            //     }
            // }

            die();
            // $customer_id = Helper::get_option('mvno_customer_id');
            // $mvno_key = Helper::get_option('bundle_mvno_key');
            // $end_point = '/core/subscriptions?CustomerId='.$customer_id.'&MVNO='.$mvno_key; //api for sim
            // $response = Helper::call_sim_process_api($end_point, '', 'get');

            // foreach($response->Subscriptions as $sub){
                
            //     // DB::table('user_data')->where('sim_account_id', $sub->AccountId)->update(['sim_subscription_id'=>$sub->SubscriptionId]);
            //     // echo $sub->AccountId.'--'.$sub->SubscriptionId.'--'.$user->sim_subscription_id.chr(10);
            //     //
            // }


            
            die();
            $user_id = 10239;
            $user = ModalUser::where('id',$user_id)->first();
            // $user_data = UserData::where('user_id',$user_id)->get();
            $country_id = $user->country_id;
            $country = Country::join('switch_template','switch_id','=','switch_template.id')
                   ->where('country.id',$country_id)->first();

            $cli_number = str_replace('-', '', $user->phone);
            $username = Helper::unique_code(16);
            $username = $country->short_code."-".$username;
            $vm_password = mt_rand(1000000000, 9999999999);

            $data = array(
                'username' => $username,
                'a_class' => $country->a_class,
                'tariff' => $country->tariff,
                'timezone_value' => $country->timezone_value,
                'balance' => $country->default_balance,
                'translation_rule' => $country->translation_rule,
                'cli_number' => $cli_number,
                'export_type' => $country->export_type,
                'vm_password' => $vm_password,
                'notify_email' => $user->email,
                'company_name' => $username,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,                
                'phone' => $cli_number,
                'currency' => $country->currency,
                'billing_plan' => $country->billing_plan,
                'routing_group' => $country->routing_group,
            );
            $xml_data = SwitchHelper::switch_create_account_xml($data);
            $temp =  SwitchHelper::call_switch_api($xml_data);

            if (array_key_exists("fault", $temp)) {
                $i_account = NULL;
                $switch_status = 'F';
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

            if ($switch_status == 'S') {                                
                /*Add phone number as trusted number */
                // $trust['user_id'] = $user->id;
                // $trust['trusted_number'] = $user->phone;
                // $trust['default_number'] = 1;
                // $trust['verified'] = 1;
                // DB::table('trusted_numbers')->insert($trust);

                $xml_data = SwitchHelper::switch_add_cli_xml($i_account, $user->phone);          
                $temp =  SwitchHelper::call_switch_api($xml_data);

                if (array_key_exists("fault", $temp)) {
                    // TrustedNumber::where('user_id', $user_id)
                    //         ->where('trusted_number', $cli_number)
                    //         ->update(['default_number' => 0]);
                }else{
                    DB::table('users')->where('id', $user->id)
                    ->update(['i_account' => $i_account]);
                    DB::table('user_data')->where('user_id', $user->id)
                    ->update(['i_account' => $i_account,'auth_name'=>$username,'vm_password'=>$vm_password]);
                }

                // $remoteips = DB::table('remoteips')->where('status' ,1)->get();
                // if($remoteips){
                //     $auth_rule = array('i_account' => $i_account, 'cli_number' => $user->phone);
                //     foreach($remoteips as $remoteip){
                //         $auth_rule['remote_ip'] = $remoteip->ip_address;
                //         $xml_data = SwitchHelper::switch_add_auth_rule_xml($auth_rule);
                //         $temp =  SwitchHelper::call_switch_api($xml_data);
                //     }
                // }
               
                $register_status = 0;
            } 

            die();

    }

    /**
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index()
    {           
        $default_zone = date_default_timezone_get();
        echo $default_zone.chr(10);
        // array('Abingdon-on-Thames');

        // $address = $dlocation; // Google HQ
        // $prepAddr = str_replace(' ','+','Abingdon-on-Thames');
        // $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address=Abingdon-on-Thames&sensor=false');
        // $output= json_decode($geocode);
        // $latitude = $output->results[0]->geometry->location->lat;
        // $longitude = $output->results[0]->geometry->location->lng;

        // $data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=Abingdon-on-Thames&key=AIzaSyAR4d_zdr1nFEHAa1QGqTN5zr87jyJGReM');

        // $data = file_get_contents('https://www.avon.uk.com/api/findarepapi/findbylocation/?longitude=-0.18&latitude=53.26&postcode=Adlington&filterOutTrendsetterRepresentatives=false&cb=1900678954');
        // print_r(json_decode($data));

    }
}
