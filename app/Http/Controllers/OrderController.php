<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Hash;
use Crypt;
use Carbon;
use Helper;
use DataTables;
use App\Models\User;
use App\Models\Cart;
use App\Models\Plan;
use App\Models\Country;
use App\Models\Boltons;
use App\Models\TblPlan;
use App\Models\TblBundle;
use App\Models\Provider;
use App\Models\CartList;
use App\Models\SimStock;
use App\Models\SimList;
use App\Models\Throttle;
use App\Models\Fraudster;
use App\Models\SimRequest;
use App\Models\Admins;
use App\Jobs\OrderRequestJob;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\UserCreditCard;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /*
    * Creating a new order
    * Incomplete orders list
    */
    public function create_order(Request $request)
    {
        if (!Helper::has_permission('orders','create')) {
            abort(403,'Access denied');
        }
        $users = Cart::select('user_id')->whereNotNull('user_id')->where('user_id','!=', 0)
                    ->groupBy('user_id')->take(5)->orderBy('id','desc')->get();
        $data = [];
        foreach($users as $key => $user){
            $data[] = Cart::where('user_id', $user->user_id)->get();
        }
        $request->session()->forget(['cart_id', 'user_id']);
        // $carts = Cart::where('user_id', 10507)->get();
        // foreach($carts as $cart){
        //     DB::table('tbl_cart_details')->where('cart_id', $cart->id)->delete();
        //     Cart::where('id', $cart->id)->delete();
        // }
        //$request->session()->put('cart_id', [18110,18141,18143]);
        return view('orders.new-order', compact('data'));
    }

    /*
    * Plan List  
    * All available plans listed
    */
    public function select_plan(Request $request)
    {
        $admins     = Admins::find(Auth::id());
        $role       = $admins->roles->short_code;
        $dealerid   = ($role == 'DEALER') ? Auth::id() : 0;

        $providers = Provider::where('status',1)->get();
        $plans = [];
        foreach ($providers as $provider) {
            $plans[$provider->short_code] = TblPlan::where('provider', $provider->short_code)
                                                ->where('status',1)
                                                ->where('dealer_id',$dealerid)->get();
        }
        $plans['EE_O2'] = TblPlan::where('provider', 'EE_O2')->where('dealer_id',$dealerid)->get();
        
        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];
        $cart = Cart::select('category_id', DB::raw('SUM(item_count) as sim_count'))->whereIn('id', $cartIds)
                        ->groupBy('category_id')->pluck('sim_count','category_id')->toArray();

        return view('orders.plan-list', compact('providers','plans','cart'));
    }

    /**
    * selected Plan  
    * @return status
    */
    public function selected_plan(Request $request)
    {    
        $error = true; $user_id = 0;
        $promo = Auth::user()->promocode;
        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];   
        $is_esim = $request->is_esim ?? 1;    
        $credit  = 0; 
        foreach($request->product as $key => $quantity){
            $plan = TblPlan::where('id', $key)->first();

            if(in_array( $plan->sim_provider->short_code,['E_SIM'])){
                $credit = array_filter(config('topup.topup_amounts'), function($ar) {
                            return ($ar['default'] == '1');
                        });
                $credit = (!empty($credit)) ? $credit[array_key_first($credit)]['amount']/100 : 0;
            }
            if($quantity){ 
                $credit = $credit * $quantity;               
                if(Cart::whereIn('id', $cartIds)->where('category_id', $plan->id)->exists()){
                    $cart = Cart::whereIn('id', $cartIds)->where('category_id', $plan->id)->update(['sim_count' => 1, 'item_count' => $quantity, 'amount' => $quantity * $plan->sell_price,'promocode' => $promo,'is_esim'=>$is_esim,'credit'=>$credit]);
                }else{
                    $cart = Cart::create(['category' => 'plan', 'category_id' => $plan->id, 'provider' => $plan->sim_provider->id, 'sim_count' => 1, 'item_count' => $quantity, 'amount' => $quantity * $plan->sell_price, 'promocode' => $promo, 'user_id' => $user_id,'is_esim'=>$is_esim,'credit'=>$credit]);
                    array_push($cartIds, $cart->id);            
                    $request->session()->put('cart_id', $cartIds);
                }
                $error = false;
            }else{
                $cart = Cart::whereIn('id', $cartIds)->where('category_id', $plan->id)->first();                
                if($cart){
                    Cart::where('id', $cart->id)->delete();
                    CartList::where('cart_id', $cart->id)->delete();
                    if (($key = array_search($cart->id, $cartIds)) !== false) {
                        unset($cartIds[$key]);                       
                    }
                    $request->session()->put('cart_id', $cartIds);                    
                }
            }
        }

        return response()->json(['error' => $error, 'message' => 'Please select atleast one product!']);
    }

    /**
    * Listing Bolt-ons Plan  
    * @return list view
    */
    public function select_bolt_ons(Request $request)
    {
        $plans = [];
        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];
        $cart =  Cart::whereIn('id', $cartIds)->get()->toArray();
        $pro_ids = array_column($cart, 'provider');
        $providers = Provider::whereIn('id', $pro_ids)->where('status',1)->get();        
        $mobileapp = Helper::get_option('enable_switch_support');
        foreach ($providers as $provider) {
            $plans[$provider->id] = Boltons::where('provider', $provider->id)
                                            ->where('status',1)->get();
        }
        if($mobileapp){
            $plans['app'] = Plan::whereIn('plan_type',[2,3])->where('switch_id', 1)->get();
        }
        return view('orders.bolt-ons',compact('providers','plans','mobileapp'));
    }

    /**
    * Listing Bolt-ons Plan  
    * @return list view
    */
    public function selected_plan_popup(Request $request)
    {
        $selected_plans = [];
        $provider = $request->provider;
        $cartIds = $request->session()->get('cart_id');
        if($provider == 'app'){
            $bolt = Plan::where('id', $request->bolt)->first();
            $selected_plans = Cart::whereIn('id', $cartIds)->get();
        }else{
            $bolt = Boltons::where('id', $request->bolt)->first();
            $selected_plans = Cart::whereIn('id', $cartIds)->where('provider', $provider)->get();
        }
        $html = view('modal-popup', compact('selected_plans','bolt','provider'))->render();

        return response()->json(['error' => false, 'html' => $html]);
    }

    /**
    * Add Bolt-ons to selected Plan  
    * @return void
    */
    public function manage_bolt_ons(Request $request)
    {   
        foreach($request->bolt_ons as $key => $bolt){
            $bolt_id = ($bolt)?:0;
            if($request->provider == 'app'){
                DB::table('tbl_cart_details')->where('id', $key)->update(['app_bolt' => $bolt_id]);
            }else{
                DB::table('tbl_cart_details')->where('id', $key)->update(['sim_bolt' => $bolt_id]);
            }            
        }
        return response()->json(['error' => false]);
    }

    /**
    * Add Bolt-ons to selected Plan  
    * @return void
    */
    public function provision_request(Request $request)
    {   
        $cartIds = $request->session()->get('cart_id');
        $selected_plans = Cart::whereIn('id', $cartIds)->get();
        return view('orders.provision', compact('selected_plans'));
    }

    /**
    * Add Bolt-ons to selected Plan  
    * @return void
    */
    public function provision_process(Request $request)
    { 
        parse_str($request->provision, $provision);
        foreach ($provision['connection'] as $key => $value) {
            $list['port'] = $value;
            $list['porting_to'] = $provision['porting_to'][$key];
            $list['pac_no'] = $provision['pac_code'][$key];
            $list['provision_date'] = $provision['transfer'][$key];
            $list['credit'] = $provision['credit'][$key];
            CartList::where('id', $key)->update($list);
        }

        return response()->json(['error' => false]);
    }


    /**
    * Customer Info / Billing
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function billing(Request $request)
    {   
        $user = '';
        $user_id = $request->session()->has('user_id')?$request->session()->get('user_id'):'';
        if($user_id)
            $user = User::find($user_id);
        
        return view('orders.billing',compact('user'));
    }


    /**
    * Customer Info / Billing
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function billing_process(Request $request)
    {   
        $validator = Validator::make($request->all(), 
          [ 'first_name'=>'required|max:50|regex:/^[a-zA-Z0-9 ]+$/',
            'last_name' => 'required|max:50|regex:/^[a-zA-Z0-9 ]+$/',
            'email' => 'required|email|max:75',
            'phone' => 'required|between:8,15|regex:/^[0-9 +()]+$/',
            'postal_code' => 'required|regex:/^[a-zA-Z0-9 ]+$/',
            'address' => 'required|regex:/^[a-zA-Z0-9-,.\' ]+$/',
            'city' => 'required|regex:/^[a-zA-Z0-9-,.\' ]+$/',
            'country' => 'required|regex:/^[a-zA-Z0-9- ]+$/',       
            'ship_first_name' => 'nullable|required_if:shipping_address,0|max:50|regex:/^[a-zA-Z0-9 ]+$/',
            'ship_last_name' => 'nullable|required_if:shipping_address,0|max:50|regex:/^[a-zA-Z0-9 ]+$/',
            'ship_postal_code' => 'nullable|required_if:shipping_address,0|regex:/^[a-zA-Z0-9 ]+$/',
            'ship_address' => 'nullable|required_if:shipping_address,0|regex:/^[a-zA-Z0-9-,.\' ]+$/',
            'ship_city' => 'nullable|required_if:shipping_address,0|regex:/^[a-zA-Z0-9-,.\' ]+$/',
            'ship_country' => 'nullable|required_if:shipping_address,0|regex:/^[a-zA-Z0-9- ]+$/'
          ], ['first_name.required' => 'Customer firstname required',
            'last_name.required' => 'Customer lastname required',
            'email.required' => 'Customer email required',
            'phone.required' => 'Customer phone number required',
            'postal_code.required' => 'Billing postcode required',
            'address.required' => 'Billing stree required',
            'city.required' => 'Billing city required',
            'country.required' => 'Billing country required',
            'ship_first_name.required_if' => 'Shipping address first name required',
            'ship_last_name.required_if' => 'Shipping address last name required',
            'ship_postal_code.required_if' => 'Shipping postalcode required',
            'ship_address.required_if' => 'Shipping street required',
            'ship_city.required_if' => 'Shipping city required',
            'ship_country.required_if' => 'Shipping country required'
          ]
        );

        if ($validator->fails()){
            $response['message'] = $validator->errors()->all();
            return response()->json(['error' => $response]);
        }

        $password = Helper::unique_code(8);
        $country = Country::whereId($request->country_id)->first();
        if (substr($request->phone, 0, 2) == preg_replace('/[^0-9]/', '', $country->dial_code)) {
            $phone = '+'.$request->phone;
        } else if (substr($request->phone, 0, 3) == $country->dial_code) {
            $phone = $request->phone;
        } else if (substr($request->phone, 0, 1) == '0') {
            $phone = $country->dial_code.substr($request->phone, 1);
        } else {
            $phone = $country->dial_code.$request->phone;
        }        

        $user = User::where('email', $request->email)->orWhere('phone', $phone)->first();
        if($user) {            
            User::where('id',$user->id)->update(['first_name'=>ucfirst($request->first_name), 'last_name'=>ucfirst($request->last_name), 'name' => ucfirst($request->first_name).' '.ucfirst($request->last_name), 'email' => $request->email]);
            $user_data = ['house_no' => $request->house_no, 'address' => $request->address, 'city' => $request->city, 'state' => $request->country, 'postal_code' => strtoupper($request->postal_code), 'user_platform'=>config('settings.app_prefix'), 'ip_address' => \Request::ip()];
            DB::table('user_data')->where('user_id', $user->id)->update($user_data);
        } else {
            $user = User::create([
                    'name' => ucfirst($request->first_name).' '.ucfirst($request->last_name),
                    'first_name' => ucfirst($request->first_name),
                    'last_name' => ucfirst($request->last_name),
                    'username' => str_replace('+', '', $phone),
                    'email' => $request->email,
                    'phone' => $phone,
                    'password' => Hash::make($password),                        
                    'country_id' => $country->id,
                    'switch_id' => $country->switch_id,
                    'status' => 0
                ]);

            $username = Helper::unique_code(16);
            $username = 'GB-'.$username;
            $vm_password = Helper::random(10, implode(range('0','9')));
            $vp_password = Helper::unique_code(10);

            $call_settings = ['accessnumber_support' => $country->accessnumber_support, 'wifi_support' => $country->wifi_support, 'callback_support' => $country->callback_support, 'conference_support' => $country->wifi_support, 'bundle_o' => 0];
            $call_settings = json_encode($call_settings);

            $user_data = ['user_id'=>$user->id, 'auth_name'=>$username, 'vm_password'=>$vm_password, 'vp_password' => $vp_password, 'house_no'=>$request->house_no, 'address'=>$request->address, 'city'=>$request->city, 'state'=>$request->country,'postal_code'=>strtoupper($request->postal_code), 'call_settings' => $call_settings, 'user_platform'=>config('settings.app_prefix'), 'ip_address' => \Request::ip(),'register_status'=> 0];
            DB::table('user_data')->insert($user_data);
            DB::table('account_balance')->insert(['user_id' =>$user->id,'balance_amount'=> 0, 'balance_minutes'=> 0]);
        }

        $billing = json_encode(['postal_code' => strtoupper($request->postal_code),'street' => $request->address, 'city' => $request->city, 'country' => $request->country, 'first_name' => ucfirst($request->first_name), 'last_name' => ucfirst($request->last_name)]);
        $shipping = ($request->shipping_address == 1)? $billing : json_encode(['postal_code' => strtoupper($request->ship_postal_code),'street' => $request->ship_address, 'city' => $request->ship_city, 'country' => $request->ship_country, 'first_name' => ucfirst($request->ship_first_name), 'last_name' => ucfirst($request->ship_last_name)]);

        DB::table('user_data')->where('user_id', $user->id)->update(['billing_address' => $billing, 'shipping_address' => $shipping]);

        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];
        Cart::whereIn('id', $cartIds)->update(['user_id' => $user->id]);
        $request->session()->put('user_id', $user->id);
        return 1;         
    }

    /**
    * Order Summary
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function order_summary(Request $request)
    { 
        $plans = [];
        $user_id = $request->session()->has('user_id')?$request->session()->get('user_id'):'';
        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];
        if(empty($cartIds)){
            return redirect('/select-plan');
        }
        if($user_id){
            $user = User::find($user_id);
            $cart = Cart::whereIn('id', $cartIds)->where('user_id',$user_id)->get();
            // $pro_ids = array_column($cart, 'provider');
            // $providers = Provider::whereIn('id', $pro_ids)->where('status',1)->get();
            // $mobileapp = Helper::get_option('enable_switch_support');
            // if($mobileapp){
            //     $plans['app'] = Plan::whereIn('plan_type',[2,3])->where('switch_id', 1)->get();
            // }
            return view('orders.summary', compact('user', 'cart'));
        }else{
            return redirect('/billing');
        }        
    }

    /**
    *  Payment
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function payment(Request $request)
    {
        $cards = [];
        $user_id = $request->session()->has('user_id')?$request->session()->get('user_id'):'';
        if($user_id)
            $user = User::find($user_id);
        else
            return redirect('/billing');
            
        $gateways = PaymentGateway::where('status', 1)->get();
        foreach($gateways as $gateway){
            $gateway->cards = UserCreditCard::where(['user_id' => $user->id,'gateway'=>$gateway->id])->get();
        }
        $purchase  = [];
        $cartIds  = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[]; 
        $purchase = Helper::plan_purchase_calculate($cartIds,$user);
        return view('orders.payment', compact('user', 'gateways','purchase'));
    }

    /**
    *  Payment
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function process_payment(Request $request)
    {
        // $validator = Validator::make($request->all(), 
        //   [ 'credit_card'=>'required',
        //     'gateway' => 'required',
        //     'card_number' => 'nullable|email|max:75',
        //     'card_type' => 'nullable|between:8,15|regex:/^[0-9 +()]+$/',
        //     'expiry_month' => 'nullable|regex:/^[a-zA-Z0-9 ]+$/',
        //     'expiry_year' => 'nullable|regex:/^[a-zA-Z0-9- ]+$/',
        //     'card_cvv' => 'nullable|regex:/^[a-zA-Z0-9- ]+$/',
        //     'card_holder' => 'nullable|regex:/^[a-zA-Z0-9- ]+$/',       
        //     'card_street' => 'nullable|required_unless:shipping_address,1|max:50|regex:/^[a-zA-Z0-9 ]+$/',
        //     'card_postcode' => 'nullable|required_unless:shipping_address,1|max:50|regex:/^[a-zA-Z0-9 ]+$/'
        //   ], ['first_name.required' => 'Customer name required',
        //     'last_name.required' => 'Participants list missing',
        //     'email.required' => 'Must have 2 participants',
        //     'phone.required' => 'Participants name missing',
        //     'postal_code.required' => 'Participants name only contain letters',
        //     'address.required' => 'Participants name should be less than 100 characters',
        //     'city.required' => 'Participants phone number missing',
        //     'country.required' => 'Participants phone number is invalid',
        //     'ship_first_name.required_unless' => 'Shipping address first name required',
        //     'ship_last_name.required_unless' => 'Shipping address last name required',
        //     'ship_postal_code.required_unless' => 'Shipping postalcode required',
        //     'ship_address.required_unless' => 'Shipping street required',
        //     'ship_city.required_unless' => 'Shipping city required',
        //     'ship_country.required_unless' => 'Shipping country required'
        //   ]
        // );
        $user_id = $request->session()->has('user_id')?$request->session()->get('user_id'):'';
        if($user_id)
            $user = User::find($user_id);

        $cartIds = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[];
        if(!Cart::whereIn('id',$cartIds)->count()){
            return response()->json(['error' => true, 'message' => 'Please select your Data Package!']);           
        }

        $blocked = Helper::check_fraudster($user->id);   
        if($blocked){
            return response()->json(['error' => true, 'message' => 'Somthing went wrong, please contact our customer care!']);           
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
            return response()->json(['error' => true, 'message' => 'Somthing went wrong, please contact our customer care!']);
        }

        $currency = $user->country->currency;
        $tax = $user->country->tax;
        $country_code = $user->country->short_code;
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
        

        if($request->gateway != 'Stripe'){
            if($request->credit_card != 'new'){
                $data['card_id'] = Crypt::decrypt($request->credit_card);
            }else{
                $data['card_number'] = urlencode(str_replace('-', '', $request->card_number));
                $data['expiry_month'] = str_pad($request->expiry_month, 2, '0', STR_PAD_LEFT);
                $data['card_type'] = ucfirst($request->card_type);
                $data['expiry_year'] = '20'.$request->expiry_year;
                $data['card_cvv'] = $request->card_cvv;
                $data['card_holder'] = $request->card_holder;
                $data['card_street'] = $request->card_street;
                $data['card_city'] = '';
                $data['card_state'] = '';
                $data['card_postcode'] = $request->card_postcode;  
                $data['country_code'] = $country_code;
            }  
        }    
        $data['user_id'] = $user->id;
        $data['i_account'] = $user->i_account;
        $data['currency'] = $user->country->currency;              
        $data['net_amount'] = Helper::number_format($net_amount);
        $data['vat_amount'] = Helper::number_format($vat_amount);
        $data['total_amount'] = Helper::number_format($total_amount);
        $data['buy_price'] = Helper::number_format($buy_price);
        $data['payment_for'] = 'Sim Purchase/ Plan Subscription';
        $data['description'] = 'Sim Purchase/ Plan Subscription Payment - processed by '. Auth::user()->first_name.' '.Auth::user()->last_name;
        $data['category'] = 'sim';
        $data['discount_amount'] = $discount_amount;
        $data['discount_coupon'] = $discount_code;
        
        if($request->gateway == 'Paypal'){            
            $response = Helper::paypal_payment_process($data);
        }elseif($request->gateway == 'Braintree'){
            $response = Helper::braintree_payment_process($data);
        }elseif($request->gateway == 'Stripe'){
            $data['stripeToken'] = $request->stripeToken;
            $data['card_id']     = isset($request->credit_card) ? Crypt::decrypt($request->credit_card) : '';
            $response = Helper::stripe_payment_process($data);
        }else{
            return response()->json(['error' => true,'message' => 'Can\'t process this payment']);
        }

        if($response['status']){
            $txn_id = $response['transaction_id'];
            $payment_id = $response['payment_id'];
            $card_id = $response['card_id'];        
            $order_id = config('settings.app_prefix');
            $sim_request['user_id'] = $user->id;
            $sim_request['payment_id'] = $payment_id;
            $sim_request['order_id'] = $order_id;
            $sim_request['billing_address'] = $user->userDetail->billing_address;
            $sim_request['shipping_address'] = $user->userDetail->shipping_address;
            if($promocode == ''){
                $sim_request['promocode'] = $cart[0]->promocode;
            }else{
                $sim_request['promocode'] = $promocode;
            }
            // if(Auth::user()->role == 5){
            //     $sim_request['delivery_status'] = 1;
            // }
            $request_id = DB::table('tbl_sim_request')->insertGetId($sim_request);
            $order_id = config('settings.app_prefix').str_pad($request_id, 4, '0', STR_PAD_LEFT);
            DB::table('tbl_sim_request')->where('id', $request_id)->update(['order_id' => $order_id]);
            $card_data = UserCreditCard::where('id', $card_id)->where('user_id',  $user->id)->first();
            foreach($cart as $item) {
                if($item->category == 'plan') {
                    $plan_id = $item->category_id;
                    $plan = TblPlan::where('id', $plan_id)->first();
                    $sell_price = $plan->sell_price;
                    $bundle_id = $adv_pay = 0;
                } else {
                    $plan_id = $item->product->plan_id;
                    $bundle_id = $item->category_id;
                    $plan = TblBundle::where('id', $bundle_id)->first();
                    $sell_price = $plan->sell_price;
                    $adv_pay = 0;
                }

                $net_sell = 100/(100+$tax) * $sell_price;
                $vat_sell = $sell_price - $net_sell;

                $item_list = $item->list;
                $list_key = 0;
                for($i=1; $i<=$item->item_count; $i++){
                    $auto_plan_id = DB::table('auto_plan')->insertGetId(['user_id' => $user->id, 'plan_id' => $plan_id, 'bundle_id' => $bundle_id, 'transaction_id' => $txn_id, 'card_id' => $card_id, 'amount' => $net_sell, 'tax' => $vat_sell, 'total_amount' => $sell_price, 'card_expiry' => $card_data->card_expiry, 'card_type' => $card_data->card_type, 'gateway' => $request->gateway, 'adv_pay' => $adv_pay, 'status' => '0']);
                    for($j=1; $j<=$item->sim_count; $j++){
                        $sim_list['request_id'] = $request_id;
                        $sim_list['autoplan_id'] = $auto_plan_id;
                        $sim_list['stock_id'] = $item_list[$list_key]->stock_id;  
                        $sim_list['credit'] = $item_list[$list_key]->credit;
                        $sim_list['port'] = $item_list[$list_key]->port;
                        $sim_list['sim_bolt'] = $item_list[$list_key]->sim_bolt;
                        $sim_list['app_bolt'] = $item_list[$list_key]->app_bolt;
                        $sim_list['porting_to'] = $item_list[$list_key]->porting_to;
                        $sim_list['pac_no'] = $item_list[$list_key]->pac_no;
                        $sim_list['provision_date'] = $item_list[$list_key]->provision_date;
                        if($item_list[$list_key]->port){
                            $port_data['stock_id'] = $item_list[$list_key]->stock_id;
                            $port_data['promocode'] = $sim_request['promocode'];
                            $port_data['porting_to'] = $item_list[$list_key]->porting_to;
                            $port_data['pac_number'] = $item_list[$list_key]->pac_no;
                            $port_data['status'] = 0;
                            DB::table('tbl_porting')->insert($port_data);
                        }
                        DB::table('auto_plan_meta')->insert(['autoplan_id'=>$auto_plan_id,'credit'=>$item_list[$list_key]->credit]);
                        DB::table('tbl_sim_list')->insert($sim_list);
                        DB::table('tbl_sim_stock')->where('id', $item_list[$list_key]->stock_id)->update(['status' => 0]);
                        $list_key++;
                    }                
                }
    
                DB::table('tbl_cart')->where('id', $item->id)->delete();
                DB::table('tbl_cart_details')->where('cart_id', $item->id)->delete();
            }
            $request->session()->put('payment_id', Crypt::encrypt($payment_id));
            if($request->gateway == 'Stripe'){
                if ($request->ajax()) {
                    return response()->json(['error' => false, 'message' => $response['message']]);
                }
                return redirect('success');
            }else{
                return response()->json(['error' => false, 'message' => $response['message']]);
            }                  
        }else{
            Throttle::create([  
                'identifier' => $user->email,  
                'ip_address' => $request->ip(),  
                'attempted_at' => Carbon::now()  
            ]);
            if($request->gateway == 'Stripe'){
                return redirect()->back()->with('error', $response['message']); 
            }else{
                return response()->json(['error' => true, 'message' => $response['message']]);
            }
        }

        // $payment['payment_method'] = 'Direct Cash';
        // $rsponse_status = 'SUCCESS'; 
        // $response['TRANSACTIONID'] = '';
        // $mode = ($amount>0)?'Cash Paid':'Base Pack(Free)';
        // $payment['description'] = 'subscription charge : '.($amount + $tax_amount).', addtional credit added : '.$extra_credit.', sim cost : '. $sim_cost .', card :('.$mode.')'; 
        // $credit_card_id = 0; 
        // $card_expire = '';
    }

    /*
    * List all orders which are shipped 
    * Items which are activated and non activated are listed
    */
    public function select_gateway(Request $request)
    {
        $gateway_id = $request->gateway;
        $gateway = PaymentGateway::where('id', $gateway_id)->first();
        $cards = UserCreditCard::where(['user_id' => $request->user_id, 'gateway' => $gateway_id])->get();

        $purchase  = [];
        $user_id = $request->session()->has('user_id')?$request->session()->get('user_id'):'';
        if($user_id)
        $user = User::find($user_id);

        if(isset($gateway->gateway) && $gateway->gateway == 'Stripe'){
          $cartIds  = $request->session()->has('cart_id')?$request->session()->get('cart_id'):[]; 
          $purchase = Helper::plan_purchase_calculate($cartIds,$user);
        }
        $html = view('modal-popup', compact('gateway', 'cards','purchase','user'))->render();
        return response()->json(['error' => false, 'html' => $html]);
    }

    /*
    * Payment Success Page
    * Success page with items and address
    */
    public function payment_success(Request $request)
    {

        $payment_id = $request->session()->has('payment_id') ? $request->session()->get('payment_id') : '';
        // if($payment_id == ''){
           // $payment_id = Crypt::encrypt(4003);     
        // }
        if($payment_id){
           
            $payment_id = Crypt::decrypt($payment_id);
            $title = 'Order Summary | '.config('settings.app_name');
            $payment  = DB::table('user_payments')->where('id',$payment_id)->first(); 
            $order = SimRequest::where('payment_id', $payment->id)->get();  
            $user = User::select('first_name','last_name','phone','email','country_id')
                     ->where('id', $payment->user_id)->first();
            $currency = $user->country->currency_symbol;

            foreach ($order as $item) {
                foreach($item->list as $simList) {
                    $sim['plan_name'] = $simList->auto_plan->plan->plan_name;
                    $sim['plan_price'] = $simList->auto_plan->amount;
                    $sim['phone_number'] = ($simList->stock->verify)?$simList->stock->phone_number:'44759xxxxxxx';   
                    $sim['sim_cost'] = $simList->stock->price;         
                    $sim['extra_credit'] = $simList->credit;  
                    $simDetails[$simList->autoplan_id][] =  $sim;          
                }
                $item->sim_data = $simDetails;
            }
             
            if($request->session()->has('cart_id')){            
                $email = (filter_var($user->email, FILTER_VALIDATE_EMAIL))?$user->email:'jijojoseph001@gmail.com'; //support@avoomobile.com
                // $email = 'jijojoseph001@gmail.com';
                $obj = (object) array(
                    'request_id' => $order[0]->id,
                    'email'=> $email,
                    'subject' => 'Order Confirmation #'.$order[0]->order_id,
                    'heading' => 'Order Confirmation #'.$order[0]->order_id,
                );  
  
                OrderRequestJob::dispatch($obj)
                    ->delay(now()->addMinutes(1));
            }
             
            $request->session()->forget('cart_id');
            return view('orders.success', compact('order','payment','user','currency','title'));
        }else{
            return redirect('/');
        }
    }

    /*
    * List all orders which are shipped 
    * Items which are activated and non activated are listed
    */
    public function orders()
    {
        if (!Helper::has_permission('orders') && !Helper::has_permission('orders','view_own')) {
            abort(403,'Access denied');
        }

        $dealers = DB::table('admins')->select(DB::raw('concat(first_name," ",last_name) as dealer'),'promocode')->where('status', 1)->get();
        return view('orders.orders', compact('dealers'));
    }

    /*
    * Pagination and filter
    */
    public function orders_list(Request $request)
    {
        $admin_id = Auth::user()->id;
        $promocode  = Auth::user()->promocode;
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();        
        array_push($where, $promocode);

        $order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone', DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"), DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"), DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` = rq.id LIMIT 1)) as sim_number"))->join('users as usr','usr.id','=','rq.user_id');

        if($request->sim_number != ''){
            $order_list =  $order_list->join('tbl_sim_list as sl','request_id','=','rq.id')
                                 ->join('tbl_sim_stock as sk','sk.id','=','sl.stock_id');
        }

        if(Helper::has_permission('orders')) {            
        }elseif(Helper::has_permission('orders','view_own')) {
            $order_list = $order_list->whereIn('rq.promocode', $where);
        }else{
            $order_list = $order_list->where('id', 0);
        }

        if ($request->delivery_status == 1) { 
            $order_list =  $order_list->where('delivery_status','1');
        } else if($request->delivery_status == 2) {             
            $order_list =  $order_list->where('delivery_status','2');
        } else if($request->delivery_status == 3) { 
            $order_list =  $order_list->where('delivery_status','0');
        }

        if($request->order_id){
            $order_list =  $order_list->where('rq.order_id', $request->order_id);
        }
        if($request->user_phone){
            $order_list =  $order_list->where('usr.phone', $request->user_phone);
        }
        if($request->from_date){
            $order_list =  $order_list->where('rq.created_at','>=', $request->from_date);
        }
        if($request->to_date){
            $order_list =  $order_list->where('rq.created_at','<=', $request->to_date);
        }
        if($request->sim_number){
            $order_list =  $order_list->where('sk.sim_number', $request->sim_number);
        }
        
        //return DataTables::queryBuilder($order_list)->toJson();
        return Datatables::queryBuilder($order_list)
                ->editColumn('promocode', function ($order) { 
                    return (($order->promocode)?$order->promocode:'SJ100');
                })->editColumn('date', function ($order) { 
                    return Helper::date_format($order->date);
                })->editColumn('ship_date', function ($order) {
                    if($order->ship_date)
                        return Helper::date_format($order->ship_date);
                })
                
                // ->with(['total_sum' => number_format($total,2,'.',''), 'total_buy' => number_format($total_buy,2,'.',''), 'refund' => number_format($refund,2,'.',''), 'currency' => $request->currency])
                ->rawColumns(['promocode'])
                ->make(true);
    }  

    /*
    * Incomplete Orders list Page
    * Success page with incomplete orders
    */
    public function abandoned_orders(Request $request)
    {
        if (!Helper::has_permission('orders') && !Helper::has_permission('orders','view_own')) {
            abort(403,'Access denied');
        }

        return view('orders.incomplete-orders');
    }

    /*
    * Incomplete Orders list Page
    * Success page with incomplete orders
    */
    public function abandoned_orders_list(Request $request)
    {
        $orders = Cart::select('tbl_cart.*')->whereNotNull('user_id')->where('user_id','!=', 0)->groupBy('user_id')
                    ->join('users as u','u.id','=','tbl_cart.user_id');
        return Datatables::eloquent($orders)
                ->editColumn('name', function ($order) {
                   return $order->user->name;  
                })->addColumn('details', function ($order) {
                    $details = '';
                    $cart = Cart::where('user_id', $order->user_id)->get();
                    foreach($cart as $item){
                        $details .= '<b> '.$item->product->sim_provider->provider .'</b>
                        <ul>
                            <li>'. $item->sim_count.' x '.$item->product->plan_name .'
                                <ul>
                                    
                                </ul>
                            </li>
                        </ul>';
                        // <li>1 x DW500LU - Daisy Wholesale 500 Low User (Feb 17)</li>
                        // <li>3 x VFSOU - O&amp;O Unlimited Special</li>
                        // <li>3 x VFSOUIC - O&amp;O Unlimited Special with IC</li>
                    }
                    return $details;
                })
                ->rawColumns(['details','action'])
                ->make(true);
    }

    /*
    * Manage Promocode
    * Function Change Promocode
    */
    public function update_order_promocode(Request $request)
    {
        if(Helper::has_permission('orders','edit')){
            if(Auth::user()->role == 1){
                $promocode = strtoupper($request->promocode);
                $old_promo = DB::table('tbl_sim_request')->where('id', $request->order_id)->value('promocode');
                $note = 'PromoCode changed from '.$old_promo.' to '.$promocode.' by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on ';
                DB::table('delivery_history')->insert(['sim_request_id' => $request->order_id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
                DB::table('tbl_sim_request')->where('id', $request->order_id)->update(['promocode' => $promocode]);
            }
        }
    }

    public function order_details(Request $request)
    {
        if (!Helper::has_permission('orders','edit')){
            abort(403,'Access denied');
        }

        $switch_id = 1; $data = '';
        $admin_id = Auth::user()->id;
        $promocode  = Auth::user()->promocode;
        $promocodes = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();    
        array_push($promocodes, $promocode);
        $sim_number = ($request->sim_number)?:'';
        $order_id = ($request->order_id)?:'';
        $request_id = $sim_list = $sim_request = []; 

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
                    $data = SimRequest::select(DB::raw('GROUP_CONCAT(id) as request_id'))
                            ->where('order_id', [$stock->list->sim_request->order_id]);
                    if (!Helper::has_permission('orders')){                             
                        $data = $data->whereIn('promocode',$promocodes);
                    }
                    $data = $data->first();                    
                }
            }            
        }else if($order_id){
            $data = SimRequest::select(DB::raw('GROUP_CONCAT(id) as request_id'))
                        ->where('order_id', $order_id);
                    if (!Helper::has_permission('orders')){                             
                        $data = $data->whereIn('promocode',$promocodes);
                    }
            $data = $data->first();                    
        }

        if($data){
            $request_id = explode(',', $data->request_id);
            $sim_request = SimRequest::whereIn('id',$request_id)->get();
            $sim_list = SimList::selectRaw('count(*) as sim_count,autoplan_id')->whereIn('request_id', $request_id)->groupBy('autoplan_id')->get();
            $switch_id = $sim_request[0]->user->switch_id;
        }
            

        $currency = Helper::get_option('currency_symbol');
        $credits =DB::table('credits')->select('id','amount','default_amount')
                  ->where('switch_id', $switch_id)
                  ->where('status','1')->get();

        return view('orders.order-details', compact('sim_list','sim_number','order_id','sim_request','request_id','credits','currency'));
    }

    /*
    * List item details 
    * 
    */
    public function sim_list_details(Request $request)
    {
        $stock_id = unserialize($request->stock_id);
        $sim_item_details = 1;
        $simList = SimList::whereIn('stock_id', $stock_id)->get();
        $html = view('modal-popup', compact('sim_item_details','simList'))->render();

        return response()->json(['error' => false, 'html' => $html]);
    }
}
