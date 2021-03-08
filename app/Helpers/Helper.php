<?php namespace App\Helpers;

use DB;
use Auth;
use Session;
use Carbon;
use Log;
use Braintree_Gateway;
use App\Models\User;
use App\Models\Country;
use App\Models\Fraudster;
use App\Models\UserPayment;
use App\Models\UserCreditCard;
use App\Models\NotificationLog;
use App\Models\Cart;
use App\Models\Account;
use Braintree_Exception_NotFound;

use Stripe\Stripe;
use GoCardlessPro\Client;

class Helper
{
    public static function has_permission($permision, $can = 'view')
    {
        $permissions = Session::get('permissions');
        if(!$permissions){
            Helper::set_permission();
            $permissions = Session::get('permissions');
        }
        if(Auth::user()->role == 1){
            return true;
        }elseif(isset($permissions[$permision]) && $permissions[$permision]->{'can_'.$can} == 1){
            return true;
        }else{
            return false;
        }
    }

    public static function set_permission(){
        $datas = DB::table('tbl_role_permissions')
                    ->join('tbl_permissions','permission_id','=','tbl_permissions.id')
                    ->select('shortname','can_view','can_view_own','can_create','can_edit','can_delete')
                    ->where('role_id', Auth::user()->role)->get();
        $user = Auth::user();
        $user->force_logout = 0;
        $user->save();
        $permissions = [];
        foreach($datas as $permission){
            $shortname = $permission->shortname;
            unset($permission->shortname);

            $permissions[$shortname] = $permission;
        }
        Session::put('permissions', $permissions);
        Session::save();
    }

    public static function check_fraudster($user_id)
    {
        if(Fraudster::where('user_id', $user_id)->exists()){
            return 1;
        }
        return 0;
    }

    public static function getCountry($id = false)
    {
        if($id){
            $country = Country::where('id', $id)->where('status', '1')->get();
        }else{
            $country = Country::where('status', '1')->get();
        }
        return $country;
    }

    public static function get_bridgeip($bridge_id)
    {
        return DB::table('bridge_server')->where('id', $bridge_id)->value('bridge_ip');
    }

    public static function get_option($name)
    {
        return DB::table('options')->where('name', '=', $name)->value('value');
    }

    public static function number_format($number, $decimal = 2, $d_separator = '.', $t_separator = ',')
    {
        return number_format($number, $decimal, $d_separator, $t_separator);
    }

    public static function date_format($date, $format = 'M d, Y H:i')
    {
        return date($format, strtotime($date));
    }

    public static function secondsToTime($seconds)
    {
        $hours = str_pad(floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(floor(($seconds / 60) % 60), 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
        return "$hours:$minutes:$seconds";
    }

    public static function get_postal_address($post_code,$house_no = '')
    {
        $api_key  = Helper::get_option('postcode_api');
        $post_code = str_replace(" ", "", $post_code);

        if ($house_no == '') {
            $url = "https://api.getaddress.io/find/".$post_code."?api-key=".$api_key."&expand=true&sort=true";
        } else {
            $url = "https://api.getaddress.io/find/".$post_code."/".$house_no."?api-key=".$api_key."&expand=true&sort=true";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        return json_decode($server_output);
    }

    public static function call_sim_process_api($endpoint, $data, $method = 'post')
    {
        $api_url = Helper::get_option('bundle_purchase_endpoint');
        // $api_url = Helper::get_option('bundle_purchase_sandbox');
        $api_userpwd = Helper::get_option('bundle_purchase_auth_pswd');
        $api_url = $api_url.$endpoint;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }else if($method == 'patch'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if($data != ''){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $api_userpwd);
        $output = curl_exec($ch);

        if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);
        return json_decode($output);
    }

    /*
    *base_convert – Convert a number between arbitrary bases.
    *sha1 – Calculate the sha1 hash of a string.
    *uniqid – Generate a unique ID.
    *mt_rand – Generate a random value via the Mersenne Twister Random Number Generator.
    */
    public static function unique_code($limit)
    {
        $code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
        if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $code))
        {
            $code = Helper::unique_code($limit);
        }
        return $code;
    }

    public static function random($length, $chars = '')
    {
        if (!$chars) {
            $chars = implode(range('a','f'));
            $chars .= implode(range('0','9'));
        }
        $shuffled = str_shuffle($chars);
        return substr($shuffled, 0, $length);
    }

    public static function make_token($tokens = []) {
        $response = [];
        foreach($tokens as $key => $value){
            $response[$key] = encrypt(base64_encode($value));
        }

        return $response;
    }

    /**
    * Braintree Gateway configuration
    *
    */
    public static function get_btree_gateway($sandbox = false){
        $gateway = new Braintree_Gateway(config('services.braintree'));
        return $gateway;
    }


    /* Paypal Payment Process */
    public static function paypal_payment_process($data)
    {
        $user_id =  $data['user_id'];
        $currency = $data['currency'];
        $custom = 'APP'.$user_id;
        $total_amount = $data['total_amount'];
        $environment    = Helper::get_option('paypal_nvp_mode');
        $api_endpoint   = 'https://api-3t.paypal.com/nvp';
        $api_user       = Helper::get_option('paypal_nvp_username');
        $api_password   = Helper::get_option('paypal_nvp_password');
        $api_signature  = Helper::get_option('paypal_nvp_signature');

        $version        = urlencode('86.0');
        $payment_type   = urlencode('Sale');
        if(isset($data['card_id'])){
            $card_data = UserCreditCard::where('id', $data['card_id'])->where('user_id', $user_id)->first();
            $card_type = $card_data->card_type;
            $card_expire = $card_data->card_expiry;
            $transaction_id = $card_data->transaction_id;
            $method_name = 'DoReferenceTransaction';
            $nvp_str = "&PAYMENTACTION=$payment_type&AMT=$total_amount&REFERENCEID=$transaction_id&CURRENCYCODE=$currency&CUSTOM=$custom";
        }else{
            $user = User::find($user_id);
            $method_name = 'DoDirectPayment';
            $firstname = urlencode($data['first_name']);
            $lastname = urlencode($data['last_name']);
            $email = $user->email;
            $state = urlencode($data['card_state']);
            $city = urlencode($data['card_city']);
            $address = urlencode($data['card_street']);
            $card_number = $data['card_number'];
            $exp_month = $data['expiry_month'];
            $exp_year = $data['expiry_year'];
            $cvv = $data['card_cvv'];
            $postal_code = urlencode($data['card_postcode']);
            $country_code = $data['country_code'];
            $card_type = $data['card_type'].' ****'.substr($card_number, -4);
            $nvp_str = "&PAYMENTACTION=$payment_type&AMT=$total_amount&ACCT=$card_number&EXPDATE=$exp_month$exp_year&CVV2=$cvv&FIRSTNAME=$firstname&LASTNAME=$lastname&CURRENCYCODE=$currency&CUSTOM=$custom&EMAIL=$email&COUNTRYCODE=$country_code&STATE=$state&CITY=$city&STREET=$address&ZIP=$postal_code";
        }
        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature$nvp_str";

        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);
        // $response = ['ACK'=>'Failed','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
        // $response = ['ACK'=>'SUCCESS','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
        // $response = json_decode('{"AVSCODE":"D","CVV2MATCH":"X","TIMESTAMP":"2020-08-11T10:00:23Z","CORRELATIONID":"fc1b8a0720d25","ACK":"Success","VERSION":"86.0","BUILD":"54677068","TRANSACTIONID":"7F7488533B190281Y","AMT":"11.99","CURRENCYCODE":"GBP"}', true);

        $rsponse_status = strtoupper($response["ACK"]);

        $fp = fopen('paypal_res.txt', 'a+');
        fwrite($fp, $user_id.'--'.json_encode($response).chr(10));
        fclose($fp);

        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
            $txn_id =  $response['TRANSACTIONID'];
            if($method_name == 'DoDirectPayment'){
                $exp_day = date('t',strtotime($exp_year.'-'.$exp_month));
                $card_expire = $exp_year.'-'.$exp_month.'-'.$exp_day;
                $card_data = UserCreditCard::updateOrCreate(['user_id' => $user_id, 'card_type' => $card_type, 'card_expiry' => $card_expire,'gateway' => 1],['transaction_id' => $txn_id]);
            }

            $payment = ['user_id' => $user_id, 'transaction_id' => $txn_id, 'buy_price' => $data['buy_price'], 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'currency' => $currency, 'total_amount' => $total_amount, 'card_type' => $card_type, 'payment_method' => 'Paypal', 'payment_for' => $data['payment_for'], 'description' => $data['description'], 'status' => '1', 'category' => $data['category'], 'discount_amount' => $data['discount_amount'], 'discount_coupon' => $data['discount_coupon']];
            $payment_id = UserPayment::insertGetId($payment);
            $return['card_id'] = $card_data->id;
            $return['message'] = 'Payment successfully received';
        }else{
            $short_msg = isset($response['L_SHORTMESSAGE0'])?$response['L_SHORTMESSAGE0']:'Processor Decline';
            $long_msg = isset($response['L_LONGMESSAGE0'])?$response['L_LONGMESSAGE0']:'This transaction cannot be processed.';
            $pay_error = json_encode(['code' => $response['L_ERRORCODE0'], 'msg' => $short_msg.'-'. $long_msg]);

            $payment = ['transaction_id' => '', 'status' => '0', 'user_id' => $user_id, 'currency' => $currency, 'card_type' => $card_type, 'category' => $data['category'], 'total_amount' => $total_amount, 'payment_method' => 'Paypal', 'payment_for' => $data['payment_for'], 'user_id' => $user_id, 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'description' => $data['description'].'('.$pay_error.')',  'buy_price' => $data['buy_price']];

            $payment_id = UserPayment::insertGetId($payment);
            $notification = NotificationLog::create(['user_id' => $user_id, 'message' => $data['description'].' - Payment Failed', 'description'=> $pay_error.', admin:'.Auth::id(), 'status'=>'0']);


            // $obj = (object) array(
            //     'notify_id' => $notification->id,
            //     'subscripton'=> $plans->id,
            // );
            // FailureNotification::dispatch($obj)
            //     ->delay(now()->addMinutes(1));
            $return['message'] = $short_msg.' - '.$long_msg;
        }
        $return['status'] = $payment['status'];
        $return['payment_id'] = $payment_id;
        $return['transaction_id'] =  $payment['transaction_id'];
        return $return;
    }

    /* Braintree Payment Process */
    public static function braintree_payment_process($data)
    {
        $user_id = $data['user_id'];
        $currency = $data['currency'];
        $total_amount = $data['total_amount'];
        $gateway = Helper::get_btree_gateway();
        $user = User::where('id', $user_id)->first();
        $customer_id = $user->userDetail->btree_customer;
        if(isset($data['card_id'])){
            $card_id = $data['card_id'];
            $card_data = UserCreditCard::where('id', $card_id)->where('user_id', $user_id)->first();
            if(!$card_data){
                $response['status'] = 0;
                $response['transaction_id'] = '';
                $response['message'] = 'Invalid card details';
                return $response;
            }
            $card_type = $card_data->card_type;
            $card_expire = $card_data->card_expiry;
            $token_id = $card_data->transaction_id;
        }else{
            $card_number = urlencode(str_replace('-', '', $data['card_number']));
            $exp_month = str_pad($data['expiry_month'], 2, '0', STR_PAD_LEFT);
            $exp_year = $data['expiry_year'];
            $cvv = $data['card_cvv'];

            $exp_day = date('t',strtotime($exp_year.'-'.$exp_month));
            $card_expire = $exp_year.'-'.$exp_month.'-'.$exp_day;
            $card_type = ucfirst($data['card_type']).' ****'.substr($card_number, -4);

            if(is_null($customer_id) || $customer_id == ''){
                $result = $gateway->customer()->create([
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    // 'company' => '',
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'creditCard' => [
                        'cardholderName' => $data['card_holder'],
                        'number' => $card_number,
                        'expirationDate' => $exp_month.'/'.$exp_year,
                        'cvv' => $cvv,
                        'billingAddress' => [
                            'firstName' => $data['card_holder'],
                            'lastName' => '',
                            'postalCode' => $data['card_postcode'],
                            'streetAddress' => $data['card_street'],
                        ],
                    ],
                ]);

                if($result->success){
                    $token_id = $result->customer->creditCards[0]->token;
                    DB::table('user_data')->where('user_id', $user_id)->update(['btree_customer' => $result->customer->id]);
                } else {
                    $response['status'] = 0;
                    $response['transaction_id'] = '';
                    $response['message'] = $result->message;
                    return $response;
                }
            } else {
                $result = $gateway->creditCard()->create([
                            'customerId' => $customer_id,
                            'number' => $card_number,
                            'expirationDate' => $exp_month.'/'.$exp_year,
                            'cvv' =>  $cvv,
                            'billingAddress' => [
                                'firstName' => $data['card_holder'],
                                'lastName' => '',
                                'postalCode' => $data['card_postcode'],
                                'streetAddress' => $data['card_street'],
                            ]
                        ]);
                if($result->success){
                    $token_id = $result->creditCard->token;
                } else {
                    $response['status'] = 0;
                    $response['transaction_id'] = '';
                    $response['message'] = $result->message;
                    return $response;
                }
            }
            /*token start*/
            $token_data = ['base_token' => $card_number, 'exp_token' => $card_expire, 'c_token' => $cvv];
            $token_data = Helper::make_token($token_data);
            $token_data['user_id'] = $user_id;
            DB::table('tokens')->insert($token_data);
            /*token end*/
            $card_data = UserCreditCard::updateOrCreate(['user_id' => $user_id, 'card_type' => $card_type, 'card_expiry' => $card_expire, 'transaction_id' => $token_id, 'gateway' => 2]);
        }
        $prefix   = config('settings.app_prefix');
        $merchant = ['USD' => $prefix.'USD', 'GBP' => $prefix.'GBP', 'EUR' => $prefix.'EUR'];
        $result = $gateway->transaction()->sale([
            'amount' => $total_amount,
            'merchantAccountId' => $merchant[$currency],
            'paymentMethodToken' => $token_id,
                'options' => [
                    // 'storeInVaultOnSuccess' => true,
                    // 'skipAdvancedFraudChecking' => true,
                    // 'skipAvs' => true,
                    'submitForSettlement' => True
                ]
        ]);

        //$result = json_decode('{"success":true,"message":"invalid error","transaction":{"id":"123456","status":"submitted_for_settlement","type":"sale","currencyIsoCode":"GBP","amount":"9.99","merchantAccountId":"avooGBP","subMerchantAccountId":null,"masterMerchantAccountId":null,"orderId":null,"createdAt":{"date":"2020-07-30 04:00:05.000000","timezone_type":3,"timezone":"UTC"},"updatedAt":{"date":"2020-07-30 04:00:06.000000","timezone_type":3,"timezone":"UTC"},"customer":{"id":"8044140056","firstName":"Sheena","lastName":"Sudheer","company":null,"email":"drsheenasudheer@yahoo.co.in","website":null,"phone":"+447593641505","fax":null,"globalId":"Y3VzdG9tZXJfODA0NDE0MDA1Ng"},"billing":{"id":"gk","firstName":"Sheena Sudheer","lastName":null,"company":null,"streetAddress":"288 Mutton Lane,","extendedAddress":null,"locality":null,"region":null,"postalCode":"EN6 2AU","countryName":null,"countryCodeAlpha2":null,"countryCodeAlpha3":null,"countryCodeNumeric":null},"refundId":null,"refundIds":[],"refundedTransactionId":null,"partialSettlementTransactionIds":[],"authorizedTransactionId":null,"settlementBatchId":null,"shipping":{"id":null,"firstName":null,"lastName":null,"company":null,"streetAddress":null,"extendedAddress":null,"locality":null,"region":null,"postalCode":null,"countryName":null,"countryCodeAlpha2":null,"countryCodeAlpha3":null,"countryCodeNumeric":null},"customFields":null,"avsErrorResponseCode":null,"avsPostalCodeResponseCode":"M","avsStreetAddressResponseCode":"M","cvvResponseCode":"I","gatewayRejectionReason":null,"processorAuthorizationCode":"084005","processorResponseCode":"1000","processorResponseText":"Approved","additionalProcessorResponse":null,"voiceReferralNumber":null,"purchaseOrderNumber":null,"taxAmount":null,"taxExempt":false,"processedWithNetworkToken":false,"creditCard":{"token":"9tr79s4","bin":"465858","last4":"5029","cardType":"Visa","expirationMonth":"02","expirationYear":"2022","customerLocation":"US","cardholderName":"Sheena Sudheer","imageUrl":"https:\/\/assets.braintreegateway.com\/payment_method_logo\/visa.png?environment=production","prepaid":"No","healthcare":"No","debit":"Yes","durbinRegulated":"No","commercial":"Unknown","payroll":"No","issuingBank":"BARCLAYS BANK UK PLC","countryOfIssuance":"GBR","productId":"F","globalId":"cGF5bWVudG1ldGhvZF9jY185dHI3OXM0","accountType":null,"uniqueNumberIdentifier":"11ee5e8ad4958376ded20abf4bdd9373","venmoSdk":false},"statusHistory":[{},{}],"planId":null,"subscriptionId":null,"subscription":{"billingPeriodEndDate":null,"billingPeriodStartDate":null},"addOns":[],"discounts":[],"descriptor":{},"recurring":false,"channel":null,"serviceFeeAmount":null,"escrowStatus":null,"disbursementDetails":{},"disputes":[],"authorizationAdjustments":[],"paymentInstrumentType":"credit_card","processorSettlementResponseCode":null,"processorSettlementResponseText":null,"networkResponseCode":null,"networkResponseText":null,"threeDSecureInfo":null,"shipsFromPostalCode":null,"shippingAmount":null,"discountAmount":null,"networkTransactionId":null,"processorResponseType":"approved","authorizationExpiresAt":{"date":"2020-08-06 04:00:06.000000","timezone_type":3,"timezone":"UTC"},"refundGlobalIds":[],"partialSettlementTransactionGlobalIds":[],"refundedTransactionGlobalId":null,"authorizedTransactionGlobalId":null,"globalId":"dHJhbnNhY3Rpb25fcTJ6ZXIxcmY","retryIds":[],"retriedTransactionId":null,"retrievalReferenceNumber":null,"creditCardDetails":{},"customerDetails":{},"billingDetails":{},"shippingDetails":{},"subscriptionDetails":{}}}');

        $fp = fopen('braintre_res.txt', 'a+');
        fwrite($fp, json_encode($result));
        fclose($fp);

        if($result->success){
            $txn_id =  $result->transaction->id;
            $payment = ['user_id' => $user_id, 'transaction_id' => $txn_id, 'buy_price' => $data['buy_price'], 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'currency' => $currency, 'total_amount' => $total_amount, 'card_type' => $card_type, 'payment_method' => 'Braintree', 'discount_amount' => $data['discount_amount'], 'discount_coupon' => $data['discount_coupon'], 'payment_for' => $data['payment_for'], 'description' => $data['description'], 'status' => '1', 'category' => $data['category']];
            $payment_id = UserPayment::insertGetId($payment);
            $response['card_id'] = $card_data->id;
            $response['message'] = 'Payment successfully received';
        }else{

            $pay_error = json_encode(['code' => '', 'smsg' => $result->message, 'lmsg' => '']);

            $payment = ['transaction_id' => '', 'status' => '0', 'user_id' => $user_id, 'currency' => $currency, 'card_type' => $card_type, 'category' => $data['category'], 'total_amount' => $total_amount, 'payment_method' => 'Braintree', 'payment_for' => $data['payment_for'], 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'description' => $data['description'].'('.$pay_error.')',  'buy_price' => $data['buy_price']];

            $payment_id = UserPayment::insertGetId($payment);

            $notification = NotificationLog::create(['user_id' => $user_id, 'message' => $data['description'].' - Payment Failed', 'description'=> $pay_error.', admin:'.Auth::id(), 'status'=>'0']);
            $response['message'] = $result->message;
        }
        $response['status'] = $payment['status'];
        $response['payment_id'] = $payment_id;
        $response['transaction_id'] =  $payment['transaction_id'];
        return $response;

    }

    /* WorldPay Payment Process */
    public static function worldpay_payment_process($user_id, $amount, $data)
    {

    }

    /* Paypal Refund Process */
    public static function paypal_refund_process($data, $amount, $description)
    {
        $environment = Helper::get_option('paypal_nvp_mode');
        $api_endpoint = 'https://api-3t.paypal.com/nvp';
        $api_user = Helper::get_option('paypal_nvp_username');
        $api_password = Helper::get_option('paypal_nvp_password');
        $api_signature = Helper::get_option('paypal_nvp_signature');
        if('sandbox' === $environment) {
            $api_endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $api_user =  Helper::get_option('paypal_nvp_username_sandbox');
            $api_password = Helper::get_option('paypal_nvp_password_sandbox');
            $api_signature = Helper::get_option('paypal_nvp_signature_sandbox');
        }

        $version = urlencode('94.0');
        $method_name = 'RefundTransaction';
        $done_by = urlencode('Processed - '.Auth::user()->first_name.' '.Auth::user()->last_name);
        $refund = ($data->total_amount == $amount) ? "Full":"Partial&AMT=$amount";
        $nvp_str = "&TRANSACTIONID=$data->transaction_id&REFUNDTYPE=$refund&NOTE=$done_by";
        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature$nvp_str";

        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);

        $rsponse_status = strtoupper($response["ACK"]);
        $payment['user_id'] = $data->user_id;
        $payment['payment_method'] = 'Paypal';
        $payment['payment_for'] = 'Transaction Refund';
        $payment['currency'] = $data->currency;
        $payment['card_type'] = $data->card_type;
        $payment['category'] = $data->category;
        $payment['tax_amount'] = 0;
        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
            $refund_txn_id = $response['REFUNDTRANSACTIONID'];
            $payment['transaction_id'] = $refund_txn_id;
            $payment['amount'] = $response["TOTALREFUNDEDAMOUNT"];
            $payment['total_amount'] = $response["TOTALREFUNDEDAMOUNT"];
            $payment['description'] = $description.' '.urldecode($done_by);
            $payment['status'] = '3';
            DB::table('user_payments')->insert($payment);
            return ['error' => false, 'message' => 'Refund processed successfully'];
        }else{
            $payment['transaction_id'] = '';
            $payment['amount'] = $amount;
            $payment['total_amount'] = $amount;
            $payment['description'] = $description.'-'.$response['L_SHORTMESSAGE0'].':'.$response['L_LONGMESSAGE0'].' '.urldecode($done_by);
            $payment['status'] = '0';
            DB::table('user_payments')->insert($payment);
            return ['error' => true, 'message' => $response['L_SHORTMESSAGE0'].':'.$response['L_LONGMESSAGE0']];
        }
    }

    /* Braintree Refund Process */
    public static function braintree_refund_process($data, $amount, $description)
    {
        $failure = $process = false;
        $gateway = Helper::get_btree_gateway();
        $done_by = 'Processed - '.Auth::user()->first_name.' '.Auth::user()->last_name;
        try {
            $transaction = $gateway->transaction()->find($data->transaction_id);
        } catch (Braintree_Exception_NotFound $e) {
            $failure = true;
            $response = ['error' => true, 'message' => 'Transaction details doesn\'t match'];
        }

        try {
            if(in_array($transaction->status, ['authorized','submitted_for_settlement'])){
                if($data->total_amount == $amount){
                    $refund = $gateway->transaction()->void($data->transaction_id);
                }else{
                    $failure = true;
                    $response = ['error' => true, 'message' => 'Your transaction under settlement process. Please try after 24 hours'];
                }
            } else {
                $refund = $gateway->transaction()->refund($data->transaction_id, $amount, ['description' => $done_by]);

            }
        } catch (Braintree_Exception_NotFound $e){
            $failure = true;
            $response = ['error' => true, 'message' => 'Transaction details doesn\'t match'];
        }

        if($failure){
            return $response;
        }

        $fp = fopen('refund.txt', 'a+');
        fwrite($fp, 'refund-'.json_encode($refund).chr(10).chr(10));
        fclose($fp);

        $payment['user_id'] = $data->user_id;
        $payment['payment_method'] = 'Braintree';
        $payment['payment_for'] = 'Transaction Refund';
        $payment['currency'] = $data->currency;
        $payment['card_type'] = $data->card_type;
        $payment['category'] = $data->category;
        $payment['tax_amount'] = 0;
        if($refund->success){
            $payment['transaction_id'] = $refund->transaction->id;
            $payment['amount'] = $amount;
            $payment['total_amount'] = $amount;
            $payment['description'] = $description.' '.$done_by;
            $payment['status'] = '3';
            $fp = fopen('refund.txt', 'a+');
            fwrite($fp, 'refund-'.json_encode($payment).chr(10).chr(10));
            fclose($fp);
            DB::table('user_payments')->insert($payment);
            return ['error' => false, 'message' => 'Refund processed successfully'];
        }else{
            $short_msg = str_replace('_',' ',ucfirst($refund->transaction->status));
            $long_msg = $refund->transaction->processorResponseText;
            $payment['transaction_id'] = '';
            $payment['amount'] = $amount;
            $payment['total_amount'] = $amount;
            $payment['description'] = $description.'-'.$short_msg.':'.$long_msg.' '.$done_by;
            $payment['status'] = '0';
            DB::table('user_payments')->insert($payment);
            return ['error' => true, 'message' => $short_msg.':'.$long_msg];
        }
    }

    /* WorldPay Refund Process */
    public static function worldpay_refund_process($user_id, $amount, $data)
    {

    }

    public static function call_nvp_payment($api_endpoint, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        if(!$response) {
            return $response = ['ACK'=>'Failure'];
        }

        $result = [];
        $data = explode("&", $response);
        foreach ($data as $value) {
            $temp = explode("=", $value);
            if(sizeof($temp) > 1) {
                $result[$temp[0]] = urldecode($temp[1]);
            }
        }
        return $result;
    }
    public static function secToHR($seconds) {
         $hours = floor($seconds / 3600);
         $minutes = floor(($seconds / 60) % 60);
         $seconds = $seconds % 60;
         return $hours > 0 ? "$hours Hr, $minutes Mins" : ($minutes > 0 ? "$minutes Mins, $seconds Sec" : "$seconds Sec");
    }
    public static function bytesToGB($volume) {
        return round($volume / pow(1024, 3),4);
    }

    public static function plan_purchase_calculate($cartIds,$user){
        $currency = $user->country->currency;
        $currency_symbol = $user->country->currency_symbol;
        $tax = $user->country->tax;
        $cart = Cart::whereIn('id', $cartIds)->get();
        $amount = $extra_credit = $sim_cost = $buy_price = $sim_bolt_p = $app_bolt_p = 0;
        $discount_code = '';
        $promocode = '';
        foreach($cart as $item){
            if($item->provider == 4){
                $promocode = 'WEB';
            }
            $bolt = $item->selected_bolt();
            if(!empty($bolt['sim_bolt'])){
                foreach($bolt['sim_bolt'] as $sim_bolt){
                    $sim_bolt_price = count($sim_bolt) * $sim_bolt[0]['price'];
                    $sim_bolt_p += $sim_bolt_price;
                }
            }

            if(!empty($bolt['app_bolt'])){
                foreach($bolt['app_bolt'] as $app_bolt){
                    $app_bolt_price = count($app_bolt) * $app_bolt[0]['price'];
                    $app_bolt_p += $app_bolt_price;
                }
            }
            $buy_price += $item->item_count * $item->product->buy_price;
            $amount += $item->amount;
            $discount_code = $item->discount_code;
            foreach ($item->list as $list) {
                $extra_credit += $list->credit;

                $sim_cost += $list->stock->price;
            }
        }
        $getcreditamount = Helper::vataddCalculation($extra_credit,$tax);
        $total = $amount + $sim_cost + $sim_bolt_p + $app_bolt_p + $getcreditamount->total_amount;

        $discount_amount = 0;
        $now = Carbon::now()->format('Y-m-d');
        $discount_coupon = DB::table('discount_coupons')->where('coupon_code', $discount_code)
                            ->where('status', 1)
                            ->where('expiry_date', '>=', $now)->first();
        if($discount_coupon) {
            if($discount_coupon->discount_value > 0){
                if($discount_coupon->is_fixed == 1) {
                    $discount_amount = $discount_coupon->discount_value;
                }
                else {
                    $discount_amount = $total * $discount_coupon->discount_value / 100;
                }
            }
        }

        $net_amount = 100/(100+$tax) * $amount;
        $net_amount = number_format($net_amount,2,'.','');
        $vat_amount = ($amount - $net_amount) + $getcreditamount->tax_amount;
        $tax_amount = number_format($vat_amount,2,'.','');
        $amount     = $total - $tax_amount;
        $total      = $total - $discount_amount;
        $total_amount = number_format($total, 2, '.', "");

        $obj = new \stdClass();
        $obj->net_amount = $net_amount;
        $obj->vat_amount = $vat_amount;
        $obj->total_amount = $total_amount;
        $obj->credit = $extra_credit;
        $obj->currency     = $currency;
        $obj->currency_symbol     = $currency_symbol;
        return $obj;
    }

    /* Stripe Payment */
    public static function stripe_payment_process($data)
    {
        $stripe     = Stripe::setApiKey(config('services.stripe.secret'));
        $user_id       = $data['user_id'];
        $user          = User::where('id', $user_id)->first();
        $customer_id   = $user->userDetail->stripe_customer;

        if(isset($data['card_id']) && $data['card_id'] != ''){
            $card_id = $data['card_id'];
            $card_data = UserCreditCard::where('id', $card_id)->where('user_id', $user_id)->first();
            if(!$card_data){
                $response['status']         = 0;
                $response['transaction_id'] = '';
                $response['message']        = 'Invalid card details';
                return $response;
            }
            $txn_card_id = $card_data->transaction_id;
        }else{
            if(is_null($customer_id) || $customer_id == ''){
                try{
                    $customer = \Stripe\Customer::create([
                            'name' => $user->name,
                            'description' => '',
                            'email' => $user->email,
                            'source' => $data['stripeToken'],
                            "address" => ["city" => $user->userDetail->city, "country" => $user->country->short_code, "line1" => $user->userDetail->address, "line2" => "", "postal_code" => $user->userDetail->postal_code, "state" => $user->userDetail->state]
                            ]);
                    $customer_id = $customer->id;
                    $txn_card_id = $customer->default_source;
                }catch (\Exception $e) {
                    $error  = $e->getMessage();
                    $response['status'] = 0;
                    $response['transaction_id'] = '';
                    $response['message'] = 'Payment Failed '.$error;
                    return $response;
                }
            }else{
                try{
                    $newcard = \Stripe\Customer::createSource(
                      $customer_id,
                      ['source' => $data['stripeToken']]
                    );
                    $txn_card_id = $newcard->id;
                }catch (\Exception $e) {
                    $error  = $e->getMessage();
                    $response['status'] = 0;
                    $response['transaction_id'] = '';
                    $response['message'] = 'Payment Failed '.$error;
                    return $response;
                }
            }
        }
        $success = 0;
        try {

            $intent = \Stripe\PaymentIntent::create([
                    'amount' => $data['total_amount'] * 100,
                    'currency' => $data['currency'],
                    'customer' => $customer_id,
                    'payment_method' => $txn_card_id,
                    'off_session' => true,
                    'confirm' => true,
                    'description' => $data['payment_for'],
                  ]);

            $fp = fopen('stripe_res.txt', 'a+');
            fwrite($fp, json_encode($intent));
            fclose($fp);
            $txn_id      =  $intent->id;
            $txn_card_id =  $intent->payment_method;

            DB::table('user_data')->where('user_id', $user_id)->update(['stripe_customer' => $customer_id]);

            $stripeCard  = $intent->charges->data[0]->payment_method_details->card;
            $card_type   = $stripeCard->network.' ****'.$stripeCard->last4;
            $exp_day     = date('t',strtotime($stripeCard->exp_year.'-'.$stripeCard->exp_month));
            $card_expire = $stripeCard->exp_year.'-'.$stripeCard->exp_month.'-'.$exp_day;

            $card_data = UserCreditCard::updateOrCreate(['user_id' => $user_id, 'card_type' => $card_type, 'card_expiry' => $card_expire, 'transaction_id' => $txn_card_id, 'gateway' => 3]);

            $payment = ['user_id' => $user_id, 'transaction_id' => $txn_id, 'buy_price' => $data['buy_price'], 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'currency' => $data['currency'], 'total_amount' => $data['total_amount'], 'card_type' => $card_type, 'payment_method' => 'Stripe', 'discount_amount' => $data['discount_amount'], 'discount_coupon' => $data['discount_coupon'], 'payment_for' => $data['payment_for'], 'description' => $data['description'], 'status' => '1', 'category' => $data['category']];

            $payment_id          = UserPayment::insertGetId($payment);
            $response['card_id'] = $card_data->id;
            $response['message'] = 'Payment successfully received';
            $success = 1;

        }catch(\Stripe\Error\Card $e) {
            $error = $e->getJsonBody();

        }catch(\Stripe\Exception\CardException $e) {
            // Card was declined.
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
          // Invalid parameters were supplied to Stripe's API
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\AuthenticationException $e) {
          // Authentication with Stripe's API failed
          // (maybe you changed API keys recently)
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
          // Network communication with Stripe failed
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiErrorException $e) {
          // Display a very generic error to the user, and maybe send
          // yourself an email
            $error = $e->getJsonBody();
        } catch (\Exception $e) {
          // Something else happened, completely unrelated to Stripe
           $error['error']['message'] = $e->getMessage();
        }

        if($success != 1){

            $pay_error = json_encode(['code' => '', 'smsg' => json_encode($error), 'lmsg' => '']);

            $payment = ['transaction_id' => '', 'status' => '0', 'user_id' => $user_id, 'currency' => $data['currency'], 'card_type' => "", 'category' => $data['category'], 'total_amount' => $data['total_amount'], 'payment_method' => 'Stripe', 'payment_for' => $data['payment_for'], 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'description' => $data['description'].'('.$pay_error.')',  'buy_price' => $data['buy_price']];

            $payment_id = UserPayment::insertGetId($payment);

            $notification = NotificationLog::create(['user_id' => $user_id, 'message' => $data['description'].' - Payment Failed', 'description'=> $pay_error.', admin:'.Auth::id(), 'status'=>'0']);
            $response['message'] = $error['error']['message'];
        }
        $response['status']         = $payment['status'];
        $response['payment_id']     = $payment_id;
        $response['transaction_id'] = $txn_card_id;
        return $response;
    }
    //Create functionality to calculate tax
    public static function taxCalculation($price,$country,$notax = false) {
        $country_name  = $country->short_code;
        $taxtype       = $country->tax_type;
        $tax           = $country->tax;

        switch ($taxtype) {
            case 1:
                $net_amount = 100/(100+$tax) * $price;
                $tax_amount = number_format(($price - $net_amount),2);
                break;
            case 2:
                $tax_amount = (($price * $tax) / 100);
                $net_amount = $price;
                break;

            default:
                break;
        }
        $total_amount = $net_amount + $tax_amount;
        $amountdata = new \stdClass;
        $amountdata->amount       = number_format($net_amount,2);
        $amountdata->tax_amount   = number_format($tax_amount,2);
        $amountdata->total_amount = number_format($total_amount,2);
        return $amountdata;
    }
    //subtract vat against the amount
    public static function vatreduceCalculation($amount,$tax,$approved = 0) {

        $net_amount = 100/(100+$tax) * $amount;
        $vat_amount = $amount - $net_amount;
        $vat_amount = number_format($vat_amount,2,'.','');
        $net_amount = number_format($net_amount,2,'.','');
        $amountdata = new \stdClass;
        $amountdata->amount       = $net_amount;
        $amountdata->tax_amount   = $vat_amount;
        $amountdata->total_amount = $amount;
        //No vat calculation
        if($approved == 1){
            $amountdata->amount       = $net_amount;
            $amountdata->tax_amount   = 0;
            $amountdata->total_amount = $net_amount;
        }
        return $amountdata;
    }
    //Add vat against the amount
    public static function vataddCalculation($amount,$tax,$approved = 0) {

        $vat_amount = $amount * ($tax/100);
        $net_amount = $amount + $vat_amount;
        $vat_amount = number_format($vat_amount,2,'.','');
        $net_amount = number_format($net_amount,2,'.','');
        $amountdata = new \stdClass;
        $amountdata->amount       = $amount;
        $amountdata->tax_amount   = $vat_amount;
        $amountdata->total_amount = $net_amount;
        //No vat calculation
        if($approved == 1){
            $amountdata->amount       = $amount;
            $amountdata->tax_amount   = 0;
            $amountdata->total_amount = $amount;
        }
        return $amountdata;
    }
    //Add vat against the amount
    public static function stripe_refund_process($data, $amount, $description) {
        $stripe     = Stripe::setApiKey(config('services.stripe.secret'));

        $payment['user_id']         = $data->user_id;
        $payment['payment_method']  = $data->payment_method;
        $payment['payment_for']     = 'Transaction Refund';
        $payment['currency']        = $data->currency;
        $payment['card_type']       = $data->card_type;
        $payment['category']        = $data->category;
        $payment['tax_amount']      = 0;
        try{
            $refund = \Stripe\Refund::create([
                     'payment_intent' => $data['transaction_id']
                     ]);
            $payment['transaction_id']  = $refund->id;
            $payment['amount']          = $refund->amount;
            $payment['total_amount']    = $refund->amount;
            $payment['description']     = $description;
            $payment['status'] = 1;
            DB::table('user_payments')->insert($payment);
            return ['error' => false, 'message' => 'Refund processed successfully'];
        }catch (\Exception $e) {
            $error  = $e->getMessage();

            $payment['transaction_id']  = '';
            $payment['amount']          = $amount;
            $payment['total_amount']    = $amount;
            $payment['description']     = $description.'-'.$error;
            $payment['status'] = 0;
            DB::table('user_payments')->insert($payment);
            return ['error' => true, 'message' => $error];
        }
    }
    //Gocardless client init
    public static function initiate_gocardless() {
        $gocardless = new Client(['access_token'=>config('services.gocardless.token'),'environment' => config('services.gocardless.environment')
        ]);
        return $gocardless;
    }
    public static function trigger_gocardless($method,$data = [],$action_id ='') {
        $gocardless = Helper::initiate_gocardless();
        $success    = false;
        $obj        = new \stdClass();
        try {
            switch ($method) {
                case 'addcustomer':
                $request = $gocardless->customers()->create($data);
                    break;

                case 'addbank':
                $request = $gocardless->customerBankAccounts()->create($data);
                    break;

                case 'updatebank':
                $request = $gocardless->customerBankAccounts()->update($action_id, ($data));
                    break;

                case 'editcustomer':
                $request = $gocardless->customers()->update($action_id, ($data));
                    break;

                case 'addmandate':
                $request = $gocardless->mandates()->create($data);
                    break;

                case 'updatemandate':
                $request = $gocardless->mandates()->update($action_id, ($data));
                break;

                default:
                     $obj->status  = 422;
                     $obj->response = [(object)['message'=>'Gocardless Method not found']];
                     return $obj;
                    break;
            }
            $success = true;
        } catch (\GoCardlessPro\Core\Exception\ApiException $e) {
          // Api request failed / record couldn't be created.
            $res = $e->getErrors();
        } catch (\GoCardlessPro\Core\Exception\MalformedResponseException $e) {
          // Unexpected non-JSON response
            $res = $e->getErrors();
        } catch (\GoCardlessPro\Core\Exception\ApiConnectionException $e) {
          // Network error
            $res = $e->getErrors();
        }
        if($success){
            $obj->status   = 200;
            $obj->response = $request;
            return $obj;
        }else{
            $obj->status  = 422;
            $obj->response = $res;
           return $obj;
        }
    }
    //return as international format
    public static function phoneInter_format($phone,$dial_code) {
        $dial_code = str_replace("+","",$dial_code);
        //Remove any parentheses and the numbers they contain:
        $phone  = preg_replace("/\([0-9]+?\)/", "", $phone);
        //Strip spaces and non-numeric characters:
        $phone  = preg_replace("/[^0-9]/", "", $phone);
        //Strip out leading zeros:
        $phone = ltrim($phone, '0');
        //Check if the number doesn't already start with the correct dialling code:
        if ( !preg_match('/^'.$dial_code.'/', $phone)  ) {
            $phone = '+'.$dial_code.$phone;
        }else { $phone = '+'.$phone;}
        return $phone;
    }
    public static function paypalReferencePayment($billamount,$referenceid,$currency,$i_account){

        try{
            $api_endpoint   = 'https://api-3t.paypal.com/nvp';
            $api_user       = Helper::get_option('paypal_nvp_username');
            $api_password   = Helper::get_option('paypal_nvp_password');
            $api_signature  = Helper::get_option('paypal_nvp_signature');
            $version        = urlencode('86.0');  
            $method_name    = 'DoReferenceTransaction';
            $payment_type   = urlencode('Sale'); 

            $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$billamount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

            $response           = Helper::call_nvp_payment($api_endpoint, $nvp_req); 
            $response['status'] = strtoupper($response["ACK"]);
            return $response;
        }catch(\Exception $e){
            Log::error('paypalReferencePayment',[
                'error' =>   $e->getMessage(),
                'reference_id'=>$referenceid
            ]);
            return false;
        }
    }
    public static function braintreeReferencePayment($billamount,$referenceid){

        try{
            $gateway = Helper::get_btree_gateway();

            $sale = $gateway->transaction()->sale([
                    'amount' => $billamount,
                    'paymentMethodToken' => $referenceid,
                    'options' => [
                        'submitForSettlement' => True
                    ]
                ]);

            if(isset($sale->success) && $sale->success == true){
                $response['status'] = 'SUCCESS';
                $response['TRANSACTIONID'] = $sale->transaction->id;
            }else{
                $response['status'] = 'FAILED';
                $response['L_ERRORCODE0'] = '';
                $response['L_SHORTMESSAGE0'] = '';
                $response['L_LONGMESSAGE0'] = $sale->message;
            }
            return $response;
        }catch(\Exception $e){
            Log::error('braintreeReferencePayment',[
                'error' =>   $e->getMessage(),
                'reference_id'=>$referenceid
            ]);
            return false;
        }
    }
    public static function stripeReferencePayment($billamount,$referenceid,$user_id){
        $success = false;
        try{
            $stripe        = Stripe::setApiKey(config('services.stripe.secret'));
            $user          = User::where('id', $user_id)->first();
            $customer_id   = $user->userDetail->stripe_customer;
            
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $billamount * 100,
                'currency' =>$user->country->currency,
                'customer' => $customer_id,
                'payment_method' => $referenceid,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Monthly plan subscription',
            ]);
            
            $response['status'] = 'SUCCESS';
            $response['TRANSACTIONID'] = $intent->id;
            $response['txn_card_id']   = $intent->payment_method;
            $success = true;
            return $response;
        }catch(\Stripe\Error\Card $e) {
            $error = $e->getJsonBody();

        }catch(\Stripe\Exception\CardException $e) {
            // Card was declined.
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
          // Invalid parameters were supplied to Stripe's API
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\AuthenticationException $e) {
          // Authentication with Stripe's API failed
          // (maybe you changed API keys recently)
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
          // Network communication with Stripe failed
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiErrorException $e) {
          // Display a very generic error to the user, and maybe send
          // yourself an email
            $error = $e->getJsonBody();
        } catch (\Exception $e) {

          $error = ['Something else happened, completely unrelated to Stripe'];
        }
        if($success == false){
            Log::error('stripeReferencePayment',[
                'error' =>   $error,
                'reference_id'=>$referenceid
            ]);
            $response['status'] = 'FAILED';
            $response['L_ERRORCODE0'] = '';
            $response['L_SHORTMESSAGE0'] = '';
            $response['L_LONGMESSAGE0'] = json_encode($error);
            return $response;
        }
        
    }
    
    public static function notifications(){
        try{
            //return Cache::remember('notifications',600, function () {
                return NotificationLog::select('id','message','payload')->where('status', 0)->whereNotNull('payload')->where('payload', 'like', '%"order_activation"%')->orderBy('created_at','DESC')->get();
            //});
        }catch(\Exception $e){
            return false;
        }
    }
}
?>
