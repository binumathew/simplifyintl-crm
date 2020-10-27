<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Helper;
use Crypt;
use Hash;
use Mail;
use Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Country;
use App\Models\CartList;
use App\Models\Throttle;
use App\Models\UserCreditCard;
use App\Models\SimRequest;
use App\Models\AutoPlan;
use App\Models\SimStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Mail\UserSelfPaymentLink;
use App\Mail\OrderRequest;

class SimController extends Controller
{
    /**
    * Show the Billing Page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function billing($id)
    { 
        $payload        = $id;
        $product_data   = Crypt::decrypt($payload);
        $product_data   = explode('~', $product_data); 
        $product   = explode(':', $product_data[0]);   
        $category       = $product[0];
        $product_id     = $product[1];
        $user_id = $product_data[1];
        $user = '';
        if($user_id){
           $user_id = Crypt::decrypt($user_id);
           $user = User::find($user_id);
        }

        $currency       = Helper::get_option('currency_symbol');    

        if($category == 'plan'){
            $product = DB::table('tbl_plans')
                            ->select('id','plan_name as product_name','sell_price','period','in_call_limit')
                            ->where('status', 1)->where('id', $product_id)->first();
            $product->sim_count = 1;
        }else if($category == 'bundle'){
            $product = DB::table('tbl_bundles')
                        ->select('id','plan_name as product_name','sell_price','period','sim_count','in_call_limit')
                        ->where('status', 1)->where('id', $product_id)->first();                    
        }

        // $user_id        = json_decode(session('sim_request')['user_id']);
        // $cart_id = session('cart_id');        
        // if(!$cart_id){
            $user_id = ($user_id)?$user_id:0;
            $promocode  = Auth::user()->promocode;
            $cart = ['category' => $category, 'category_id' => $product_id, 'user_id' => $user_id, 
                     'sim_count' => $product->sim_count, 'amount' => $product->sell_price, 'promocode' => $promocode];

            $cart = Cart::create($cart);
            $list = $cart->list;
            $cart_id = $cart->id;
        //     session(['cart_id' => $cart_id]);
        // }
        return view('sim.billing', compact('product','currency','payload','cart_id','user'));               
    }
    /**
    * Show the process Billing.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function process_billing($id, Request $request)
    { 
        $payload        = $id;
        $product_data   = Crypt::decrypt($payload);
        $product_data   = explode(':', $product_data);   
        $category       = $product_data[0];
        $product_id     = $product_data[1];

        $currency = Helper::get_option('currency_symbol');
        $dialcode = Helper::get_option('dial_code');
        $password = Helper::unique_code(8);
        $phone = $dialcode.ltrim($request->contact_number,'0');
        if($request->user_id){
            $user = User::find($request->user_id);
        } else {
            $user = User::where('email',$request->user_email)->orWhere('phone', $phone)->first();
        }

        if($user) {
            // if(!Auth::check()){
            //     $response['user_exist'] = 1; 
            // }            
        } else {
            $user = User::create([
                        'name' => $request->first_name.' '.$request->last_name,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'username' => str_replace('+', '', $phone),
                        'email' => $request->user_email,
                        'phone' => $phone,
                        'password' => Hash::make($password),                        
                        'country_id' => 238,
                        'switch_id' => 1,
                        'status' => 0
                    ]);

            $username = Helper::unique_code(16);
            $username = 'GB-'.$username;
            $vm_password = Helper::random(10, implode(range('0','9')));
            $vp_password = Helper::unique_code(10);

            $country = Country::whereId(238)->first();
            $call_settings = ['accessnumber_support' => $country->accessnumber_support, 'wifi_support' => $country->wifi_support, 'callback_support' => $country->callback_support, 'conference_support' => $country->wifi_support, 'bundle_o' => 0];
            $call_settings = json_encode($call_settings);

            $user_data = ['user_id'=>$user->id, 'auth_name'=>$username, 'vm_password'=>$vm_password, 'vp_password' => $vp_password,'house_no'=>$request->house_no, 'address'=>$request->street, 'city'=>$request->city, 'state'=>$request->country,   'postal_code'=>strtoupper($request->postal_code), 'call_settings' => $call_settings, 'ip_address' => \Request::ip(), 'user_platform'=>'AVM', 'register_status' => 0];
            DB::table('user_data')->insert($user_data);
            DB::table('account_balance')->insert(['user_id' =>$user->id,'balance_amount'=> 0, 'balance_minutes'=> 0]);
            // $otp = new Otp;
            // $otp->user_id = $user->id;
            // $otp->phone =  $user->phone; 
            // $otp_code = rand(1000, 9999);
            // $otp->otp = $otp_code;
            // $otp->save();
            
            // $msg = "Dear User, Your ". env('APP_NAME') ." verification code is ".$otp_code;

            // $account_sid = Helper::get_option('twilio_account_sid');
            // $auth_token =  Helper::get_option('twilio_auth_token');
            // $twilio_number = Helper::get_option('twilio_number');
            // $phone = '+919746291804';
                  
            // $client = new Client($account_sid, $auth_token);
            // $client->messages->create(
            //     $phone,
            //     array(
            //         'from' => $twilio_number,
            //         'body' => $msg
            //     )
            // );            
        }    

        $billing_address = json_encode(array('postal_code' => ($request->postal_code)?:'','street' => ($request->street)?:'', 'city' => ($request->city)?:'', 'country' => ($request->country)?:''));

        $shipping_address = ($request->shipping_address == '1')? $billing_address : json_encode(array('postal_code' => ($request->ship_postal_code)?:'','street' => ($request->ship_street)?:'', 'city' => ($request->ship_city)?:'', 'country' => ($request->ship_country)?:''));

        $card_address = json_encode(array('first_name' => $request->first_name, 'last_name' => $request->last_name, 'email' => $request->user_email, 'house_no' => $request->house_no, 
            'postal_code' => $request->postal_code,'street' => $request->street, 'city' => $request->city, 'country' => $request->country));

        $promocode  = Auth::user()->promocode;    

        DB::table('user_data')->where('user_id', $user->id)->update(['billing_address' => $billing_address, 'shipping_address' => $shipping_address, 'house_no'=>$request->house_no,
             'address'=>$request->street, 'city'=>$request->city, 'state'=>$request->country,
             'postal_code'=> strtoupper($request->postal_code)]);
        DB::table('tbl_cart')->where('id',$request->cart_id)->update(['user_id' => $user->id, 'promocode' => $promocode]);

        $user_data = ['shipping_address' => $shipping_address, 'billing_address' => $billing_address, 
                'user_id' => $user->id, 'card_address' => $card_address , 'promocode' => $promocode];

        $request->session()->put('sim_request', $user_data);          
        $response['status'] = true;           
        return response()->json(['success' => $response]);         
    }
    /**
    * Show the payment.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function payment(Request $request)
    {    
        if(!$request->session()->has('sim_request')){
            return redirect('/');
        }

        if(Auth::user()->role == 5){ 
           $dealers   = DB::table('admins')->select('id','first_name','last_name')
                        ->where('id', Auth::id())->get(); 
            // $comm = new \stdClass();
            // $comm->id = 1;
            // $comm->first_name = "AVOO";
            // $comm->last_name  = "Numbers";
        }else{
            $dealers   = DB::table('admins')->select('id','first_name','last_name')
                        ->where('id', 1)->orWhere('role',5)->get(); 
        }
        $dealer_id = (Auth::user()->role == 5)?Auth::id():1;

        $currency   = Helper::get_option('currency_symbol');    
        $user_id    = json_decode(session('sim_request')['user_id']);
        $cart       = Cart::where('user_id', $user_id)->get();
        $plans      = DB::table('tbl_plans')->where(['status'=> 1,'category'=>1])->get();
        $bundles    = DB::table('tbl_bundles')->where(['status'=> 1,'category'=>1])->get(); 
        $card_address = json_decode(session('sim_request')['card_address']); 
        $credit_cards = DB::table('user_credit_cards')->where('user_id',$user_id)->get();

        $box_no = Helper::get_option('sim_stock_box_no');

        $query = DB::table('tbl_sim_stock')->select('id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                    ->whereNOTIn('id', function($query){
                         $query->select('stock_id')->from('tbl_cart_details');
                    })->where('category', 'normal')->where('status', 1)
                    ->where('dealer_id',  $dealer_id);
                if($dealer_id == 1){
                    $query->where('box_no', $box_no);
                }
        $normal = $query->inRandomOrder()->take(12)->get();
        
        $silver = DB::table('tbl_sim_stock')->select('id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                    ->whereNOTIn('id', function($query){
                         $query->select('stock_id')->from('tbl_cart_details');
                    })->where('category', 'silver')->where('status', 1)->where('dealer_id',  $dealer_id)
                    ->inRandomOrder()->take(12)->get();
        $gold =  DB::table('tbl_sim_stock')->select('id',DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                    ->whereNOTIn('id', function($query){
                         $query->select('stock_id')->from('tbl_cart_details');
                    })->where('category', 'gold')->where('status', 1)->where('dealer_id',  $dealer_id)
                    ->inRandomOrder()->take(12)->get();
        $allowed = DB::table('tbl_whitelist')->where('ip_address', $request->ip())->exists();

        DB::table('tbl_cart')->where('user_id',$user_id)->update(['discount_code' => '']);

        return view('sim.cart', compact('cart', 'currency', 'card_address', 'bundles', 'plans', 'normal', 'silver','gold','allowed', 'user_id','dealers','credit_cards')); 
    }
    /**
    * Remove Cart Item. 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function remove_cart(Request $request)
    {         
        $cart_id = $request->cart_id;
        DB::table('tbl_cart')->where('id', $cart_id)->delete();
        DB::table('tbl_cart_details')->where('cart_id', $cart_id)->delete();    
        return response()->json(['success' => 1]);  
    }
    /**
    * Get Cart Item List.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_cart_item_data(Request $request)
    {         
        $cart_id = $request->cart_id;
        $data = DB::table('tbl_cart_details')->select('tbl_cart_details.id','stock_id','cart_id','port', 
                        DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 
                        DB::raw('DATE_SUB(expire_at, INTERVAL 10 MINUTE) as expire_at'))
                    ->join('tbl_sim_stock','stock_id','=','tbl_sim_stock.id')
                    ->where('cart_id', $cart_id)->get(); 

        return response()->json(['data' => $data]);  
    }
    /**
    * Update Cart Item.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_cart_item(Request $request)
    {         
        $data = $request->all();
        DB::table('tbl_cart_details')->where('id', $data['id'])->update($data);    
        return response()->json(['success' => 1]);  
    }
    /**
    * Add / Remove Reserve List.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function reserve_sim(Request $request)
    {
        $action = $request->action;
        $stock_id = $request->stock_id;
        if($action == 'add'){
            $cart_id = $request->cart_id;
            if(DB::table('tbl_cart_details')->where('stock_id', $stock_id)->exists()){
                return response()->json(['error' => 1]);
            }else{
                $sim_count = DB::table('tbl_cart')->where('id', $cart_id)->value('sim_count');
                if($sim_count > DB::table('tbl_cart_details')->where('cart_id', $cart_id)->count()){
                    $expire_at = Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s');
                    $reserve = ['cart_id' => $cart_id, 'stock_id' => $stock_id, 
                                'created_at' => Carbon::now(), 'expire_at' => $expire_at];
                    DB::table('tbl_cart_details')->insert($reserve);    
                    $expire_at = Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s'); 
                    return response()->json(['success' => $expire_at]); 
                }else{
                    return response()->json(['error' => 2,'message'=>'Please remove any of the selected number & try agian']);
                }      
            }               
        }else{
            DB::table('tbl_cart_details')->where('stock_id', $stock_id)->delete(); 
            return response()->json(['success' => 1]);                     
        }
    }
    /**
    * Show the postal code
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_postcode(Request $request)
    {   
        $post_code = $request->post_code;
        $house_no = ($request->house_no)?:'';
        $result = Helper::get_postal_address($post_code, $house_no);
        if (isset($result->Message)) {
            return [
                'error' => true,                
                'message' => 'Sorry we couldn\'t find your address. Please enter your address manually',
            ];
        }

        $postcode = [];
        if(isset($result->addresses)){            
            foreach($result->addresses as $address){           
                $postcode[] = implode(',',$address->formatted_address).','.$address->country.','.$result->postcode;                
            }               
        }         
        return $postcode;
    } 

    /**
    * Cart Item View.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_cart()
    {                
        $user_id = json_decode(session('sim_request')['user_id']);
        $cart = Cart::where('user_id', $user_id)->get();
        $currency = Helper::get_option('currency_symbol');    
        return view('sim.cart_item_ajax', compact('cart', 'currency'))->render();
    }
    
    /**
    * Add Cart Item.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_cart_item(Request $request)
    {         
        $item = unserialize($request->item);
        $promocode = Auth::user()->promocode;
        $user_id = json_decode(session('sim_request')['user_id']);
        $cart_data = ['user_id' => $user_id, 'category' => $item['cat'], 'category_id' => $item['cat_id'], 'sim_count' => $item['count'], 'amount' => $item['amount'], 'promocode' => $promocode];
        $cart_id = DB::table('tbl_cart')->insertGetId($cart_data);

        $cart = Cart::where('id', $cart_id)->get();
        $currency = Helper::get_option('currency_symbol');    
        return view('sim.cart_item', compact('cart', 'currency'))->render();
    }
    /**
    * Function Process Payment.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function process_payment(Request $request)
    {    
        $is_from_link = $request->has('from_link_user_id') ? 1 : 0;
        if($is_from_link) {
            $user_id = Crypt::decrypt($request->from_link_user_id);
            $user_data = DB::table('user_data')->select('user_id', 'billing_address', 'shipping_address')->where('user_id', $user_id)->get()->first(); 
            $user_data = json_decode(json_encode($user_data), true);
            //var_dump($user_data);
        }
        else{
            $user_data = $request->session()->get('sim_request');  
            //var_dump($user_data);
        }
                           
        $user = DB::table('users')->where('id', $user_data['user_id'])->first(); 

        if(!Cart::where('user_id',$user->id)->count()){
            return redirect()->back()->with('error','Please select your Data Package!');
            //->withInput(Input::all())
        }

        $blocked = Helper::check_fraudster($user->id);   
        if($blocked){
            return redirect('/payment')
                        ->with('error','Somthing went wrong, please contact our customer care!');           
        }

        $threshold = Helper::get_option('threshold_limit');
        $period = Carbon::now()->subMinutes(60);
        $countofattempt = Throttle::where('attempted_at', '>', $period)
                            ->where('ip_address', $request->ip())
                            ->orWhere('identifier', $user->email)->count(); 

        if($countofattempt > $threshold){
            Fraudster::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),  
                'created_at' => Carbon::now()
            ]);
            return redirect('/payment')
                    ->with('error','Somthing went wrong, please contact our customer care!');        
        }
        
        $currency = Helper::get_option('currency');
        $tax = Helper::get_option('country_tax');
        $country_code =Helper::get_option('country_short_code');

        $cart = Cart::where('user_id', $user->id)->get();

        $amount = $extra_credit = $sim_cost = $buy_price = 0;
        $discount_code = '';
        foreach($cart as $item){  
            $buy_price += $item->product->buy_price;      
            $amount += $item->amount;
            $discount_code = $item->discount_code;
            foreach ($item->list as $list) {
                $extra_credit += $list->credit;
                $sim_cost += $list->stock->price;
            }
        }

        $total = $amount + $extra_credit + $sim_cost;  

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

              
        $tax_amount = ($amount * $tax)/100;
        $amount = $total - $tax_amount;
        $total = $total - $discount_amount;
        $total_amount = number_format($total, 2, '.', ""); 

        $credit_card_id = $request->credit_card;
        $payment['user_id'] = $user->id;         
        $payment['amount'] = $amount;
        $payment['tax_amount'] = $tax_amount;
        $payment['total_amount'] = $total_amount;
        $payment['discount_amount'] = $discount_amount;
        $payment['discount_coupon'] = $discount_code;
        $payment['buy_price'] = $buy_price;                              
        $payment['payment_for'] =  'Sim Purchase, Plan Subscription and Extra Credit';
        
        
        if($credit_card_id === 'cash'){
            $payment['payment_method'] = 'Direct Cash';
            $rsponse_status = 'SUCCESS'; 
            $response['TRANSACTIONID'] = '';
            $mode = ($amount>0)?'Cash Paid':'Base Pack(Free)';
            $payment['description'] = 'subscription charge : '.($amount + $tax_amount).', addtional credit added : '.$extra_credit.', sim cost : '. $sim_cost .', card :('.$mode.')'; 
            $credit_card_id = 0; 
            $card_expire = '';
            $card_type = '';
        } else {
            $payment['payment_method'] = 'Paypal'; 

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

            $version = urlencode('86.0');
            $payment_type = urlencode('Sale'); 

            if($credit_card_id === 'new') {
                $firstname = urlencode($request->first_name);
                $lastname = urlencode($request->last_name);
                $address = urlencode($request->street);        
                $city = urlencode($request->city);
                $state = urlencode($request->country);
                $postal_code = urlencode($request->postal_code);
                $card_number = urlencode(str_replace('-', '', $request->card_number));
                $exp_month = str_pad($request->expiry_month, 2, '0', STR_PAD_LEFT); 
                $exp_year = $request->expiry_year;     
                $cvv = $request->card_cvv;
                $email = $user->email;

                $exp_day = date('t',strtotime($exp_year.'-'.$exp_month));
                $card_expire = $exp_year.'-'.$exp_month.'-'.$exp_day;
                $card_type = ucfirst($request->card_type).' ****'.substr($card_number, -4);

                $method_name = 'DoDirectPayment';

                // Add request-specific fields to the request string.
                $nvp_str = "&PAYMENTACTION=$payment_type&AMT=$total_amount&ACCT=$card_number&EXPDATE=$exp_month$exp_year&CVV2=$cvv&FIRSTNAME=$firstname&LASTNAME=$lastname&CURRENCYCODE=$currency&CUSTOM=1&EMAIL=$email&COUNTRYCODE=$country_code&STATE=$state&CITY=$city&STREET=$address&ZIP=$postal_code";

                $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature$nvp_str";
            } else {
                $method_name    = 'DoReferenceTransaction';

                $card_data = UserCreditCard::where('id', $credit_card_id)->first();
                if(!$card_data){
                    return redirect()->back()->with('error','Invalid Credit Card');
                    //->withInput(Input::all())
                }

                $txn_id = $card_data->transaction_id;
                $card_expire = $card_data->card_expiry;
                $card_type = $card_data->card_type;

                $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$total_amount&REFERENCEID=$txn_id&CURRENCYCODE=$currency&CUSTOM=$user->i_account";
            }  

            $payment['description'] = 'subscription charge : '.($amount + $tax_amount).', addtional credit added : '.$extra_credit.', sim cost : '. $sim_cost .', card :('.$card_type.')';   

            $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 
            // $response = ['ACK' => 'SUCCESS', 'TRANSACTIONID' => '62X741095J799080H'];
            $rsponse_status = strtoupper($response['ACK']); 
        }
        

        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
            $txn_id = $response['TRANSACTIONID'];    
            $payment['transaction_id'] = $txn_id;                                 
            $payment['status'] = '1';
            $payment_id = DB::table('user_payments')->insertGetId($payment);
            
            if($credit_card_id === 'new') {
                // save the credit card
                if($request->is_default == 0) {
                    $default_credit = UserCreditCard::where('user_id', $user->id)
                                        ->where('is_default', 1)->first();
                    if(!$default_credit) {
                        $card_is_default = 1;
                    } else {
                        $card_is_default = 0;
                    }
                } else if($request->is_default == 1) {
                    $card_is_default = 1;
                    $affected = UserCreditCard::where('user_id', $user->id)->update(['is_default' => 0]);
                }

                $credit_card = UserCreditCard::updateOrCreate(['user_id' => $user->id, 'card_type' => $card_type, 'card_expiry' => $card_expire],['transaction_id' => $txn_id, 'is_default' => $card_is_default]);

                $credit_card_id = $credit_card->id;                
            }

            $order_id = 'AVO'.mt_rand(1111, 9999).$payment_id;
            $sim_request['user_id'] = $user->id;                    
            $sim_request['payment_id'] = $payment_id;            
            $sim_request['order_id'] = $order_id;
            $sim_request['billing_address'] = $user_data['billing_address'];
            $sim_request['shipping_address'] = $user_data['shipping_address'];
            $sim_request['promocode'] = $cart[0]->promocode;
            // if(Auth::user()->role == 5){
            //     $sim_request['delivery_status'] = 1;
            // }            
            $request_id = DB::table('tbl_sim_request')->insertGetId($sim_request);

            foreach($cart as $item) {
                if($item->category == 'plan') {
                    $plan_id = $item->category_id;
                    $bundle_id = 0;
                    $adv_pay = 0;
                } else {
                    $plan_id = $item->product->plan_id;
                    $bundle_id = $item->category_id;
                    $adv_pay = 0;
                }
                $item_tax = ($item->amount * $tax)/100;
                $amount = ($item->amount - $item_tax);
                $auto_plan_id = DB::table('auto_plan')->insertGetId(
                    ['user_id' => $user->id, 'plan_id' => $plan_id, 'bundle_id' => $bundle_id, 'transaction_id' => $txn_id,
                    'card_id' => $credit_card_id, 'amount' => $amount, 'tax' => $item_tax, 'total_amount' => $item->amount, 
                    'card_expiry' => $card_expire, 'card_type' => $card_type, 'gateway' => 'Paypal', 'adv_pay' => $adv_pay, 'status' => '0']); 

                foreach ($item->list as $list) {
                    $sim_list['request_id'] = $request_id;
                    $sim_list['autoplan_id'] = $auto_plan_id;
                    $sim_list['stock_id'] = $list->stock_id;  
                    $sim_list['credit'] = $list->credit;
                    $sim_list['port'] = $list->port;
                    
                    if($list->port){
                        $port_data['stock_id'] = $list->stock_id;
                        $port_data['promocode'] = $sim_request['promocode'];
                        $port_data['status'] = 0;
                        DB::table('tbl_porting')->insert($port_data);
                    }
                    DB::table('tbl_sim_list')->insert($sim_list);
                    DB::table('tbl_sim_stock')->where('id', $list->stock_id)->update(['status' => 0]);
                }
                DB::table('tbl_cart')->where('id', $item->id)->delete();
                DB::table('tbl_cart_details')->where('cart_id', $item->id)->delete();
            }
            $payment_id = Crypt::encrypt($payment_id);
            return redirect('/success/'.$payment_id);
        } else {
            $payment['transaction_id'] = '';
            $payment['description'] = 'error : '.$response['L_LONGMESSAGE0'].', '.$payment['description']; 
            $payment['status'] = '0';
            DB::table('user_payments')->insert($payment);
            Throttle::create([  
                'identifier' => $user->email,  
                'ip_address' => $request->ip(),  
                'attempted_at' => Carbon::now()  
            ]);

            $error_msg = $user->id.''.json_encode($response).chr(10).chr(10);
            $fp = fopen('card_error.txt', 'a+');
            fwrite($fp, $error_msg);
            fclose($fp);

            return redirect()->back()->with('error', $response['L_LONGMESSAGE0']);
        }    

    }
    /**
    * Mail user self payment link
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function send_user_self_payment_link(Request $request)
    {  
       
        $user = DB::table('users')->select('email', 'name')->where('id', $request->user_id)->first(); 

        $data_obj = new \stdClass();
        $data_obj->name = $user->name;
        $data_obj->link = 'user-payment/'.Crypt::encrypt($request->user_id);
        $data_obj->subject = 'AVOOMobile Self Payment Link';    
        $data_obj->heading = 'Self Payment Link'; 

        Mail::to($user->email)
                // ->cc()
                ->bcc('jijo.joseph@gencomtel.com')
                ->send(new UserSelfPaymentLink($data_obj));

        return response()->json(['success' => 1, 'message' => 'Self payment link sent successfully']);  
    }

    /**
    * Show the list of bundles deal.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function reload_sim(Request $request)
    {
        $dealer = (Auth::user()->role == 5)?Auth::id():1;
        $category = $request->category;
        if($category != 'normal'){
            $bundles = DB::table('tbl_sim_stock')->select('id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                        ->whereNOTIn('id', function($query){
                             $query->select('stock_id')->from('tbl_cart_details');
                        })->where('category', $category)->where('status', 1)
                        ->where('dealer_id', $dealer)
                        ->inRandomOrder()->take(12)->get();                    
        } else {
            $box_no = Helper::get_option('sim_stock_box_no');
            $query = DB::table('tbl_sim_stock')->select('id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                        ->whereNOTIn('id', function($query){
                             $query->select('stock_id')->from('tbl_cart_details');
                        })->where('category', $category)->where('status', 1)
                        ->where('dealer_id', $dealer);

                        if($dealer == 1){
                            $query->where('box_no', $box_no);
                        }
            $bundles = $query->inRandomOrder()->take(12)->get();                
        }

        return $bundles;
    }

    /**
    * Function search custom number.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function search_number(Request $request)
    {
        $number = ltrim($request->key,'0');
        $key = $request->key;
        $dealer_id = $request->dealer;
        $dealer = (Auth::user()->role == 5)?Auth::id():1;
        $limit = ($request->limit)?:12;
        $query = DB::table('tbl_sim_stock')->select('id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), 'price')
                    ->whereNOTIn('id', function($sub_query){
                        $sub_query->select('stock_id')->from('tbl_cart_details');
                    });
            $query->where(function($query) use($number, $key) {
                $query->where('phone_number', 'like', '%'.$number.'%')
                ->orWhere('phone_number', 'like', '%'. $key .'%');
            });
            if(Auth::user()->role == 5){
                $query->where('dealer_id', $dealer);
            }else{
                $query->whereIn('dealer_id', [1,Auth::id(),$dealer_id]);
            }

        $bundles = $query->where('status', 1)
            ->inRandomOrder()->take($limit)->get();
            
        return $bundles;
    }
    /**
    * Check Discount code 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function check_discount_coupon(Request $request)
    {   
        $coupon_code = $request->coupon_code; 
        $now = Carbon::now()->format('Y-m-d');
        $discount_coupon = DB::table('discount_coupons')->where('coupon_code', $coupon_code)
                            ->where('status', 1)
                            ->where('expiry_date', '>=', $now)->first();

        if($discount_coupon) {
            $response['allow'] = true;
            $response['discount_value'] = $discount_coupon->discount_value;
            $response['is_fixed'] = $discount_coupon->is_fixed;
        }
        else {
            $response['allow'] = false;
            $response['msg']   = "Invalid Coupon Code";
        }

        return $response;  
    } 
    /**
    * Action Discount code 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function action_discount(Request $request)
    { 
        $coupon_code = $request->coupon_code;
        $user_id    = $request->session()->get('sim_request')['user_id'];
        $action     = $request->action;
        $checkvalid = $this->check_discount_coupon($request);

        $response = [];

        switch ($action) {
            case 'apply':

                if( $checkvalid['allow'] ){
                    $update = DB::table('tbl_cart')->where('user_id',$user_id)->update(['discount_code' => $coupon_code]);

                    $response['status'] = true;
                    $response['msg']    = "Discount code applied successfully";
                    $response['data'] = ['discount_value' => $checkvalid['discount_value'], 
                        'is_fixed' => $checkvalid['is_fixed']];
                 
                }else{
                    $response['status'] = false;
                    $response['msg']    = $checkvalid['msg'];
                }
                break;

            case 'remove':
                $update = DB::table('tbl_cart')->where('user_id', $user_id)
                            ->update(['discount_code' => ""]);
                if($update){
                    $response['status'] = true;
                    $response['msg']    = "Discount code removed successfully";
                }else{
                    $response['status'] = false;
                    $response['msg']    = "Failed";
                }
                break;   
        }
        
        return $response; 
    }
    /**
    * Payment Success Page.     
    * @return Payment Success Page with status
    */
    public function payment_success($id, Request $request)
    {  
        $id     = Crypt::decrypt($id);
        $payment    = DB::table('user_payments')->where('id',$id)->first(); 
        $order      = SimRequest::where('payment_id', $payment->id)->get();  

        foreach ($order as $item) {
            foreach($item->list as $simList) {
                $sim['plan_name']       = $simList->auto_plan->plan->plan_name;
                $sim['plan_price']      = $simList->auto_plan->amount;
                $sim['phone_number']    = $simList->stock->phone_number;   
                $sim['sim_cost']        = $simList->stock->price;         
                $sim['extra_credit']    = $simList->credit;  
                $simDetails[$simList->autoplan_id][] =  $sim;          
            }
            $item->sim_data = $simDetails;
        }
        
        $user = User::select('first_name','last_name','phone','email')
                 ->where('id', $payment->user_id)->first();
        if($request->session()->has('sim_request')){
            $obj = (object) array(
                'request_id' => $order[0]->id,
                'subject' => 'Order Confirmation #'.$order[0]->order_id,
                'heading' => 'Order Confirmation #'.$order[0]->order_id,
            );  

            $when = now()->addMinutes(5);
            Mail::to($user->email)
                // ->cc()
                ->bcc('jijo.joseph@gencomtel.com')
                ->send(new OrderRequest($obj));
                //->later($when, new OrderRequest($obj));
        }
        
        // $request->session()->forget('cart_id');
        $request->session()->forget('sim_request');
        $currency = Helper::get_option('currency_symbol');
        return view('sim.payment-success', compact('order','payment','user','currency'));
    }

    public function get_server_time(){
        return Carbon::now()->format('Y-m-d H:i:s');
    }   
}
