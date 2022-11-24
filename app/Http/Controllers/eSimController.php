<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Helper;
use DB;
use Utils;
use Validator;
use Hash;
use Log;

use App\Models\{
    Country,
    User,
    TblPlan,
    SimStock
};
use App\Jobs\eSim\Activation\BulkActivationJob;

class eSimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * eSim bulk activation
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function bulkActivation(Request $request)
    { 
        if (!Helper::has_permission('eSim_activation')) {
            abort(403,'Access denied');
        }
        Log::error('web:bulk-activation',[
                'request'=> $request->all()
            ]);
        $countries  = Utils::countries();
        $currency   = Helper::get_option('currency_symbol');
        $provider = ['TEL'];
        $plans 	= DB::table('tbl_plans')
	                    ->where(['status'=>1,'is_esim'=>1])
	                    ->whereIn('provider', $provider)
	                    ->orderBy('plan_name')->get();

	    $box_category = json_decode(Helper::get_option('sim_stock_box_category'), true);
        $box_no = $box_category[$provider[0]];
        $stocks =  DB::table('tbl_sim_stock')->select('id','sim_number')
            ->whereNOTIn('id', function($query){
                $query->select('stock_id')->from('tbl_cart_details');
            })->inRandomOrder()
            ->where('category', 'normal')
            ->where('box_no', $box_no)
            ->where('dealer_id', 1)
            ->whereIn('provider', $provider)
            ->where('is_esim',1)
            ->where('status', 1)->orderBy('sim_number')->get();

        return view('eSim.bulk_activation',compact('plans','stocks','countries','currency'));
    }
    /**
    * eSim Bulk Activation
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function bulkActivationActions(Request $request)
    { 
        if (!Helper::has_permission('eSim_activation')) {
            abort(403,'Access denied');
        }
        try{
            $country = Country::whereId($request->country_id)->first();
    		$request->merge([
                'phone' => '+'.preg_replace('/\D+/', '', $country->dial_code.ltrim($request->phone,0)),
            ]);
          	$validator = Validator::make($request->all(), [
    	      		'first_name'=>'required',
    	      		'last_name'=>'required',
        			'country_id'=>'required',
                    'phone' => 'required|phone:AUTO',
                    'email'=> 'required|email',
                    'stock_id'=> 'required|min:1|exists:tbl_sim_stock,id',
                    'plan_id'=> 'required|min:1|exists:tbl_plans,id'
                ],[
                	'first_name.required' => 'Please provide your first name',
                	'last_name.required' => 'Please provide your last name',
                	'country_id.required' => 'Please choose your mobile country', 
                	'phone.required' => 'Please provide your phone number',
                	'phone.phone' => 'Not a valid phone number',
                	'email.required' => 'Please provide a email',
                	'email.email' => 'Not a valid email',
                	'stock_id.required' => 'Please choose sim',
                	'plan_id.required' => 'Please choose a plan',

                ]);

            // Validate the input and return correct response
            if ($validator->fails()){
              return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
            }else{
                $user   = User::where('phone', $request->phone)->first();
            	if(!$user){
                    $password = Helper::unique_code(8);
                    $newUser = [
                        'name' => ucfirst($request->first_name).' '.ucfirst($request->last_name),
                        'first_name' => ucfirst($request->first_name),
                        'last_name' => ucfirst($request->last_name),
                        'username' => str_replace('+', '', $request->phone),
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'password' => Hash::make($password),
                        'country_id' => $country->id,
                        'switch_id' => 1,
                        'status' => 0
                    ];
                    $username    = Helper::unique_code(16);
                    $username    = $country->short_code.'-'.$username;
                    $vm_password = Helper::random(10, implode(range('0','9')));
                    $vp_password = Helper::unique_code(10);
                    $call_settings = [
                        'accessnumber_support' => $country->accessnumber_support, 
                        'wifi_support' => $country->wifi_support, 
                        'callback_support' => $country->callback_support, 
                        'conference_support' => $country->wifi_support, 
                        'bundle_o' => 0
                    ];
                    $call_settings = json_encode($call_settings);
                    $newUserData = [
                        'auth_name'=>$username,
                        'vm_password'=>$vm_password, 
                        'vp_password' => $vp_password, 
                        'call_settings' => $call_settings,
                        'user_platform'=> config('app.platform'), 
                        'ip_address' => \Request::ip(), 
                        'register_status'=> 0
                    ];
                    DB::beginTransaction();
                        $user = User::create($newUser);
                        $newUserData['user_id'] = $user->id;
                        DB::table('user_data')->insert($newUserData);
                        DB::table('account_balance')->insert([
                                'user_id' =>$user->id,
                                'balance_amount'=> 0, 
                                'balance_minutes'=> 0
                            ]); 
                    DB::commit();
                }else{
                    User::where('id',$user->id)->update([
                        'first_name'=> ucfirst($request->first_name), 
                        'last_name'=>  ucfirst($request->last_name), 
                        'name' => ucfirst($request->first_name).' '.ucfirst($request->last_name)
                    ]); 
                }
                $plan   = TblPlan::whereId($request->plan_id)->first();
                $stocks = SimStock::whereIn('id',$request->stock_id)->get();
                $total  = $plan->sell_price * $stocks->count();

                $currency   = Helper::get_option('currency');
                $countryTax = Helper::get_option('country_tax');
                $getamount  = Helper::vatreduceCalculation($total,$countryTax);

                $payment = [
                    'user_id' => $user->id, 
                    'currency' => $currency, 
                    'amount' => $getamount->amount, 
                    'tax_amount' => $getamount->tax_amount, 
                    'total_amount' => $getamount->total_amount,
                    'category' => 'sim',
                    'payment_for' => 'Sim Purchase, Plan Subscription',
                    'description' => 'subscription charge : '.($getamount->total_amount),
                    'status' => 0

                ];
                $payment_id = DB::table('user_payments')->insertGetId($payment);

                $sim_request = [
                    'user_id' => $user->id,
                    'payment_id'=>$payment_id,
                    'order_id'=> config('app.platform').Utils::otp(8),
                    'delivery_status'=>1
                ];

                SimStock::whereIn('id',$request->stock_id)->update(['status'=> 0]);

                $simReqid = DB::table('tbl_sim_request')->insertGetId($sim_request);

                foreach ($stocks as $key => $stock) {

                    $autoPlanId = DB::table('auto_plan')->insertGetId([
                            'user_id' => $user->id, 
                            'user_list' => $user->id, 
                            'plan_id' => $plan->id, 
                            'bundle_id' => 0, 
                            'card_id' => 0,
                            'amount' => $plan->sell_price, 
                            'tax' =>$getamount->tax_amount, 
                            'total_amount' => $plan->sell_price, 
                            'adv_pay' =>0, 
                            'switch_billing_plan' => 0,  
                            'status' => 0
                        ]);

                    $simListId = DB::table('tbl_sim_list')->insertGetId([
                            'request_id'=> $simReqid,
                            'autoplan_id'=> $autoPlanId,
                            'stock_id'=> $stock->id,
                        ]);

                    BulkActivationJob::dispatch($autoPlanId,$user->id,$plan->id,$simListId,$stock->sim_number);
                }
                User::whereId($user->id)->limit(1)
                        ->update(['status'=>1]);
                return response()->json(['status'=>200,'msg'=>'Bulk activation done successfully']);
            }
        }catch(\Exception $e){
            Log::error('web:bulk-activation',[
                'request'=> $request->all(),
                'error'=>$e->getMessage()
            ]);
            return response()->json(['status'=>422,'msg'=>'Something went wrong..']);
        }
    }
}
