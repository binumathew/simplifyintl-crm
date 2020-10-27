<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Auth;
use Hash;
use Crypt;
use Excel;
use Carbon;
use Helper;
use DataTables;
use SwitchHelper;
use App\Helpers\SIMHelper;
use App\Models\User;
use App\Models\Country;
use App\Models\Account;
use App\Models\SimList;
use App\Models\UserData;
use App\Models\UserPlan;
use App\Models\SimRequest;
use App\Models\SimStock;
use App\Models\AutoPlan;
use App\Models\VerifyUser;
use App\Models\AutoRecharge;
use App\Models\UserCreditCard;
use App\Models\UserPayment;
use App\Mail\PaymentStatus;
use App\Mail\Registration;
use App\Models\TblPorting;
use App\Models\PaymentCommission;
use App\Models\SwitchLog;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Exports\CustomExport;
use App\Jobs\FailureNotification;
use App\Mail\UserSelfPaymentLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class UserControllerold extends Controller
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
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function users(Request $request)
    {   
        if (!Helper::has_permission('users','view')) {
            abort(403,'Access denied');
        }   
        
        return view('user.list');
    }

    /**
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_users(Request $request)
    {   
        if (!Helper::has_permission('users','view')) {
            abort(403,'Access denied');
        }           
        $users = User::query()->select(['users.id','name','phone','email','users.created_at','ud.user_platform',
                            'users.status'])->join('user_data as ud','users.id','=','user_id')->orderBy('users.id','DESC');
        
        return  Datatables::eloquent($users)
                // ->addColumn('user_platform', function (User $user) {
                //     return $user->userDetail->user_platform;// == 'sim')?'Avoo':'App';
                //     // return isset(($user->userDetail))?'Avoo':$user->id;
                // })                               
                ->addColumn('action', function (User $user) {
                    $parameter= Crypt::encrypt($user->id);
                    $html = '';
                    if (Helper::has_permission('users','edit')) {
                    $html .='<a data-toggle="tooltip" title="Login"  href="'.config('app.liveurl').'admin-authenticate/'.$parameter.'" target="_blank" class="fa fa-sign-in "></a>
                        <a data-toggle="tooltip" title="Edit"  href="'.url('edit-user', [$user->id]).'" class="fa fa-pencil-square-o editbtn"></a>
                        <form class="grid_form" method="post" action="'.url('/show-user').'">'.csrf_field().'<input type="hidden" name="filter" value="'.$user->phone.'"><button type="submit" class="anchor_btn" title="View Details"><i class="fa fa-eye"></i></button></form>';
                    }
                    if(Helper::has_permission('users','delete')) {   
                    $html .='<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_user" user-id="'.$user->id.'"></a>
                        <form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                    }
                    return $html;
                })->make(true);
      
    }

    /**
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function temp_users(Request $request)
    {   
        if (!Helper::has_permission('users','view')) {
            abort(403,'Access denied');
        }   
        
        return view('user.temp-list');
    }


    /**
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function temp_users_list(Request $request)
    {
        if (!Helper::has_permission('users','view')) {
            abort(403,'Access denied');
        }           
        $users = TempUser::query()->select(['temp_users.id','phone','c.country_name','created_at'])->join('country as c','country_id','=','c.id')->orderBy('temp_users.id','DESC');
        
        return  Datatables::eloquent($users)                           
                ->addColumn('action', function (TempUser $user) {
                    $parameter= Crypt::encrypt($user->id);
                    $html = '';
                    if(Helper::has_permission('users','delete')) {   
                    $html .='<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_user" user-id="'.$user->id.'"></a>
                        <form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-temp-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                    }
                    return $html;
                })->make(true);
    }

    /*
    * Edit a user details
    */
    public function edit(Request $request,$id)
    {
        if (!Helper::has_permission('users','edit')) {
            abort(403,'Access denied');
        }
        $user = User::where('id',$id)->first();
        if (!$user) {
            abort(403, 'Invalid User');
        }
        $userData = UserData::where('user_id',$id)->first();
        return view('user.editUser', ['user' => $user, 'userData' => $userData]);
    }

    /*
    * Update user
    */
    public function update(Request $request,$id)
    {
        if (!Helper::has_permission('users','edit')) {
            abort(403,'Access denied');
        }
        $user = User::where('id',$id)->first();
        if (!$user) {
            abort(403, 'Invalid User');
        }
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->name = $user->first_name.' '.$user->last_name;
        $user->status = $request->user_status;

        $userData = UserData::where('user_id',$id)->first();
        $call_settings = ($request->call_settings)?:[];
        $call_settings['accessnumber_support'] = isset($call_settings['accessnumber_support'])?1:0;
        $call_settings['callback_support'] = isset($call_settings['callback_support'])?1:0;
        $call_settings['wifi_support'] = isset($call_settings['wifi_support'])?1:0;
        $call_settings['conference_support'] = isset($call_settings['conference_support'])?1:0;
        $call_settings['bundle_o'] = isset($call_settings['bundle_o'])?1:0;

        $userData->call_settings = json_encode($call_settings);
        $userData->address = $request->address;
        $userData->city = $request->city;
        $userData->postal_code = $request->postal_code;

        DB::beginTransaction(); 
        try { 
            $user->save();
            $userData->save(); 
            DB::commit(); 
        } catch (\Exception $e) {
            DB::rollback();
            abort(403, 'Failed to update');
        }

        if($request->from_user_detail == 0)
            return redirect('/users');
        else if($request->from_user_detail == 1)
            return response()->json(['status' => "success", 'message' => 'User details updated successfully']); 
    }

     /**
    * Destroy User Data.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_temp_user(Request $request)
    {           
        if (!Helper::has_permission('users','delete')) {
            abort(403,'Access denied');
        }   
        
        $user_id = Crypt::decrypt($request->user_key);      
        TempUser::where('id', $user_id)->delete();
        return redirect()->back()->with('message', 'Data cleared successfully!');
    }

    /**
    * Destroy User Data.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_user(Request $request)
    {   
        // die();
        if (!Helper::has_permission('users','delete')) {
            abort(403,'Access denied');
        }   
        
        $user_id = Crypt::decrypt($request->user_key);        
        $user = User::where('id', $user_id)->first();
          
        DB::beginTransaction();
        try {                                            
            User::where('id', $user_id)->delete();
            UserData::where('user_id',$user_id)->delete();
            DB::table('trusted_numbers')->where('user_id',$user_id)->delete();
            DB::table('account_balance')->where('user_id',$user_id)->delete(); 
            AutoPlan::where('user_id',$user_id)->delete();
            AutoRecharge::where('user_id',$user_id)->delete();            
            DB::table('diddial')->where('user_id',$user_id)->delete();            
            DB::table('speeddial')->where('user_id',$user_id)->delete();
            DB::table('user_calls')->where('user_id',$user_id)->delete();
            DB::table('user_contacts')->where('user_id',$user_id)->delete();
            DB::table('user_credit_cards')->where('user_id',$user_id)->delete();
            DB::table('user_payments')->where('user_id',$user_id)->delete();
            DB::table('user_plans')->where('user_id',$user_id)->delete();
            DB::table('otp')->where('user_id',$user_id)->orWhere('phone',$user->phone)->delete();
            
            $temp = [];
            if(!is_null($user->i_account)) {
                $xml_data = SwitchHelper::switch_delete_account_xml($user->i_account);
                $temp =  SwitchHelper::call_switch_api($xml_data);
            }
            $description = json_encode(['user_id' => $user->id, 'name' => $user->name, 'phone' => $user->phone, 'email' => $user->email]);
            if(array_key_exists('fault',$temp)) {
                if($temp['fault']['value']['struct']['member'][1]['value']['string'] == 'Account not found'){
                    DB::commit();
                    DB::table('activity_log')->insert(['admin_id' => Auth::id(), 'description' => $description]);
                    return redirect()->back()->with('message', 'deleted successfully!');
                }
                DB::rollback();
                return redirect()->back()->with('error', 'Faild to delete Switch Data!'); 
            }else{
                DB::commit();
                DB::table('activity_log')->insert(['admin_id' => Auth::id(), 'description' => $description]);
                return redirect()->back()->with('message', 'User deleted successfully!');
            }            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Faild to delete Switch Data!');
        }        
    }

    /*
    * View details of a user
    * Input email , Phone number
    */
    public function user_detail($id = '',Request $request)
    {
        if (!Helper::has_permission('user_details','view')) {
            abort(403,'Access denied');
        }
        $user = $call_history = $user_plan = $user_payments = $simPurchase = $balance = $porting = $switch_plans = [];
        $child_users = $auto_recharge = $auto_plan = $credits = $credit_cards = $recent_otp = $parent_user =[];
        $filter_result = $provider = '';

        $filter =  ($request->filter)?:'';
        if($filter){
            $user = User::where('email', $filter)
                        ->orWhere('phone', 'like', '%'.ltrim($filter, '0'))->first();
            
            $filter_result = ($user)?'':'No User Found!..';
        } elseif($id != ''){
            $user_id = Crypt::decrypt($id);
            $user = User::where('id', $user_id)->join('country','country.id','=','country_id')->first();
        }

        if ($user) {
            $credits = DB::table('credits')->select('amount','default_amount','currency_symbol')
                        ->join('switch_template','switch_id','=','switch_template.id')
                        ->where('switch_id', $user->switch_id)
                        ->where('status','1')->get();

            $call_history = DB::table('user_calls')->select('connect_date','cli','cld','cost','duration','history_from')->where('user_id', $user->id)->whereBetween('connect_date', array(date('Y-m-d',strtotime('-1 day')).' 00:00:01', date('Y-m-d').' 23:59:59'))->orderBy('connect_date','DESC')->get();

            $user_plan = UserPlan::where('user_id',$user->id)->orderBy('created_at','DESC')->get();

            $user_payments = DB::table('user_payments')->whereIn('user_id',[$user->id,$user->parent_id])->orderBy('created_at','DESC')->get();
            
            $credit_cards = DB::table('user_credit_cards')->where('user_id',$user->id)->get();

            $simPurchase = SimList::whereIn('request_id', function($query) use ($user){
                $query->select('id')
                ->from('tbl_sim_request')
                ->where('user_id', $user->id);
            })->get();
            $provider = SimStock::where('id',$user->stock_id)->value('provider');           
            $child_users = User::where('parent_id',$user->id)->get();
            $parent_user = User::where('id',$user->parent_id)->get();
            $auto_plan = AutoPlan::where('user_id', $user->id)->get();

            $auto_recharge = AutoRecharge::where('user_id', $user->id)->first();

            $porting = TblPorting::where('stock_id', $user->stock_id)->first();

            $balance = DB::table('account_balance')->where('user_id', $user->id)->first();

            $recent_otp = DB::table('otp')->where('user_id', $user->id)->orderby('created_at', 'desc')->first();

            $switch_plans = DB::table('switch_plans')->where('switch_id', $user->switch_id)->where('status',1)->get();
        } 

        $interplan = DB::table('tbl_plans')->where('status',1)->where('category',5)->get();
        return view('user.user-detail',compact('user', 'filter', 'call_history', 'user_plan', 'user_payments', 'simPurchase', 'child_users', 'filter_result', 'auto_plan', 'auto_recharge', 'credits', 'credit_cards', 'balance', 'porting', 'recent_otp', 'interplan','parent_user', 'provider', 'switch_plans'));
    }

    /*
    * Function porting manage
    */
    public function user_porting(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'stock_id' => ['required'],             
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid Data!']);
        }

        SimList::where('stock_id', $request->stock_id)->update(['port' => $request->status]);
        // $user = DB::table('admins')->where('id', Auth::id())->first();
        $desc = json_encode(['PAC submitted by '.Auth::user()->first_name.' '.Auth::user()->last_name]);
        $port = TblPorting::where('stock_id',$request->stock_id)->first();
        $promocode = ($port && !is_null($port->promocode))?$port->promocode:Auth::user()->promocode;
        TblPorting::updateOrCreate(
            ['stock_id' => $request->stock_id],
            ['pac_number' => $request->pac, 'porting_to' => $request->port_to, 'status' => 0, 
            'promocode' => $promocode, 'provider' => $request->provider,'description' =>$desc]
        );
        return response()->json(['success' => 1]);
    }

    /**
    * Update auto plan status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_autoplan_status(Request $request)
    { 
        $mvno_key   = Helper::get_option('bundle_mvno_key');
        $planid     = $request->autoplan_id;
        $statustype = $request->status_type;
        $auto_plan  = AutoPlan::where('id',$planid)->first();
        $amount     = round(((floatval($auto_plan->total_amount) * 80)/100),2);
        // $amount = $auto_plan->total_amount;
        $user_id = $auto_plan->user_id;
        $user_list = explode(',', $auto_plan->user_list);
        if(count($user_list) ==1){
            $user_id = $user_list[0];
            $user = User::select('users.id','name','i_account','currency','stock_id')
                        ->join('country','country.id','=','country_id')
                        ->where('users.id', $user_id)->first();

            if ($auto_plan->plan_type == 'sim') { 
                $accountid = UserData::where('user_id',$user_id)->value('sim_account_id');
                //check for plan is active or not. 1 as active then go for disable
                if($auto_plan->status == 1){
                    switch ($statustype) {
                        case 0: //Admin Privilage to suspend
                            $changeon = Carbon::parse($auto_plan->next_renewal)->subDays(1)->format('Y-m-d');
                            $update['status_changeon'] = $changeon;
                            AutoPlan::where('id',$planid)->update($update);
                            return response()->json(['status' => "success", 'message' => 'Auto Plan disable request initiated and will be disable on before next renewal']);
                        break;

                        case 1: //disable now                        
                        $default_credit = UserCreditCard::where('id', $auto_plan->card_id)->first();
                        $cardexpiry = $default_credit->card_expiry;
                        if(Carbon::parse($cardexpiry)->lt(Carbon::now())){
                           return response()->json(['status' => "failure", 'message'=>'Card validity expired']); 
                        }

                        $credit_card_id = $default_credit->id;

                        $referenceid    = $default_credit->transaction_id;

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

                        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$amount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

                        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 
                        //$response = json_decode('{"AVSCODE":"D","CVV2MATCH":"X","TIMESTAMP":"2019-09-30T15:29:06Z","CORRELATIONID":"8e2aaed34cd35","ACK":"Success","VERSION":"86.0","BUILD":"53688488","TRANSACTIONID":"5JF5512330787674A","AMT":"7.99","CURRENCYCODE":"GBP"}',true);
                        $rsponse_status = strtoupper($response["ACK"]);
                        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {

                            $txn_id = $response['TRANSACTIONID'];

                            //suspend the account
                            $end_point   = '/core/accounts/suspend?MVNO='.$mvno_key;

                            $data = ['AccountId' => $accountid, 'SuspendScope' => "ACCOUNT", 'SuspendReason' => "FREEZE_CUSTREQUEST", 'channel' => "WEB", 'comments' => "" ];

                            $response = Helper::call_sim_process_api($end_point, json_encode($data));
                            //$response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [{}]}');
                            if($response->resultType == 'Ok' && $response->resultCode == 0){
                                NotificationLog::create(['user_id'=>$user_id,'message' => 'Account Suspended','description'=>'msg:'.json_encode($response).', admin:'.Auth::id(),'status'=>'1']); 
                                SimStock::where('id',$user->stock_id)->update(['suspended' => 1]);
                            }else{
                                $notify = NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Account Suspention','description'=>'msg:'.json_encode($response).', admin:'.Auth::id(),'status'=>'0']); 
                                $obj = (object) array(
                                    'notify_id' => $notify->id,
                                    'subscripton'=> $planid,
                                );  
                                FailureNotification::dispatch($obj)
                                    ->delay(now()->addMinutes(1)); 
                            }

                            $method = 'blockAccount'; 
                            $xml_data = SwitchHelper::switch_account_status_xml($method,$i_account);
                            $temp =  SwitchHelper::call_switch_api($xml_data);
                            if (array_key_exists("fault", $temp)) {
                                $notify = NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Switch Blocking','description'=>'msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']);
                                $obj = (object) array(
                                    'notify_id' => $notify->id,
                                    'subscripton'=> $planid,
                                );  
                                FailureNotification::dispatch($obj)
                                    ->delay(now()->addMinutes(1)); 
                            }

                            DB::table('user_credit_cards')->where('id', $credit_card_id)->update(array('transaction_id' => $txn_id));

                            $update['status'] = 0; //inactive
                            AutoPlan::where('id',$planid)->update($update);

                            $payment = ['transaction_id' => $txn_id, 'total_amount' => $amount, 'payment_method' => 'Paypal', 'payment_for' => 'AutoPlan disable instantly', 'user_id' => $user_id, 'amount' => $amount, 'tax_amount' => '0', 'description' => 'Accound suspension fee(card :'.$auto_plan->card_type.')', 'status' => '1']; 

                            $paymentid = UserPayment::insertGetId($payment);

                            return response()->json(['status' => "success", 'message' => 'Auto Plan Amount Deducted and Disabled successfully']);
                        }else{
                            $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0']]);

                            $notify = NotificationLog::create(['user_id'=>$user_id,'message' => 'Autoplan disable instantly Payment failure','description'=>'Plan ID:'.$planid.', card:'.'card :'.$auto_plan->card_type.', msg:'.$pay_error.', admin:'.Auth::id(),'status'=>'0']);
                            
                            $obj = (object) array(
                                'notify_id' => $notify->id,
                                'subscripton'=> $planid,
                            );  
                            FailureNotification::dispatch($obj)
                                ->delay(now()->addMinutes(1)); 

                            return response()->json(['status' => "failure", 'message' => 'Auto Plan Amount Deduction failed. Auto Plan not disabled']);
                        }
                        break;

                        case 2: //disable after contract period
                           $disable_on = Carbon::now()->addDays(29)->format('Y-m-d');
                           if($disable_on > $auto_plan->next_renewal){         
                                $disable_on = Carbon::parse($auto_plan->next_renewal)->addDays(29)->format('Y-m-d');
                           }else if($disable_on == $auto_plan->next_renewal){
                                $disable_on = Carbon::now()->addDays(28)->format('Y-m-d');    
                           }
                           $update['status_changeon'] = $disable_on;
                           AutoPlan::where('id',$planid)->update($update);
                           return response()->json(['status' => "success", 'message' => 'Auto Plan disable request initiated and will be disabled after the contract period ']);
                        break;
                    }
                }else{
                    
                    //resume the account
                    $end_point   = '/core/accounts/resume?MVNO='.$mvno_key;

                    $data = ['AccountId' => $accountid, 'ResumeScope' => 'ACCOUNT', 'channel' => 'WEB','comments' => ''];

                    $response = Helper::call_sim_process_api($end_point, json_encode($data));
                    //$response = json_decode('{"AccountId":"1050000000000000854","orderCode": "6722820963162652686", "resultType": "Ok","resultCode": "0","messages":[{}]}');
                    if($response->resultType == 'Ok' && $response->resultCode == 0){
                        NotificationLog::create(['user_id'=>$user_id,'message' => 'Account Resume','description'=>'msg:'.json_encode($response),'status'=>'1']); 
                        SimStock::where('id',$user->stock_id)->update(['suspended' => 0]);
                    }

                    $method = 'unblockAccount';  
                    $xml_data = SwitchHelper::switch_account_status_xml($method,$user->i_account);
                    $temp =  SwitchHelper::call_switch_api($xml_data);
                    if (array_key_exists("fault", $temp)) {
                        $notify = NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Switch Un-Blocking','description'=>'msg:'.json_encode($temp).', admin:0','status'=>'0']);

                        $obj = (object) array(
                            'notify_id' => $notify->id,
                            'subscripton'=> $planid,
                        );  
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1)); 
                    }
                    //make plan enable
                    $update['status'] = 1; 
                    $update = AutoPlan::where('id',$planid)->update($update);
                    if($update)
                        return response()->json(['status' => "success", 'message' => 'Auto Plan enabled successfully']);
                    else
                        return response()->json(['status' => "failure", 'message' => 'Auto Plan enable failure']);
                }
            }
        }else{
            return response()->json(['status' => "failure", 'message'=>'Package deactivate option not available']); 
        }      
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_autorecharge_status(Request $request)
    { 
        $recharge = AutoRecharge::where('id',$request->autorecharge_id)->first();

        if (!$recharge) {
            abort(403, 'Invalid Recharge');
        }
        $recharge->status = $recharge->status == 0 ? 1 : 0;
        DB::beginTransaction();

        try {

            $recharge->save();

            DB::commit();

            return response()->json(['status' => "success", 'message' => 'Auto recharge status updated successfully']);  

        } catch (\Exception $e) {
            DB::rollback();
            abort(403, 'Failed to update');
        }
    }


    public function change_user_status(Request $request) {
        $user_id = $request->user_id;

        $user = User::where('id',$user_id)->first();
        if (!$user) {
            abort(403, 'Invalid User');
        }
        $user->status = $user->status == 0 ? 1 : 0;

        DB::beginTransaction();

        try {

            $user->save();
            $sim_list = SimList::where('user_id',$user_id)->first();
            if($sim_list){
                $autoplanid = $sim_list->autoplan_id;
                PaymentCommission::where('autoplan_id', $autoplanid)
                            ->where('is_paid',0)
                            ->update(['user_active' => $user->status]); //disable &enable entry of user whose payment not given in commission
            }

            DB::commit();

            return response()->json(['status' => $user->status, 'message' => 'User status updated successfully']);  

        } catch (\Exception $e) {
            DB::rollback();
            abort(403, 'Failed to update');
        }
    }

    public function add_autorecharge(Request $request) {
        $user_id = $request->ar_user_id;

        $user = User::select('users.id','name','i_account','currency_symbol',
                    'tax','currency','short_code','email')
                    ->join('country','country.id','=','country_id')
                    ->where('users.id', $user_id)->first();
        if (!$user) {
            return response()->json(['status' => "failure", 'message' => 'Invalid User']); 
        }

        $credit_card = UserCreditCard::where('id', $request->auto_card)->first();
        
        $auto_amount = $request->amount;
        $auto_tax_amount = ($auto_amount * $user->tax)/100;
        $auto_total_amount = number_format($auto_amount+$auto_tax_amount,2,'.',"");


        DB::beginTransaction();

        try {

            AutoRecharge::updateOrCreate(
                ['user_id' => $user_id],
                ['transaction_id' => $credit_card->transaction_id, 'card_id' => $credit_card->id, 'amount' => $auto_amount, 
                 'tax' => $auto_tax_amount, 'total_amount' => $auto_total_amount, 
                 'card_expiry' => $credit_card->card_expiry, 'card_type' => $credit_card->card_type, 
                 'status' => '1']
            );

            DB::commit();

            return response()->json(['status' => "success", 'message' => 'Autorecharge amount added successfully']);  

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => "failure", 'message' => $e->getMessage()]);
        }
    }

    public function change_user_card(Request $request) {
        $user_id = $request->user_id;
        $modify_card_for = $request->modify_card_for;
        $credit_card_id = $request->credit_card;
        $is_refund = $request->is_refund;
        $user = User::select('users.id','name','i_account','currency_symbol',
                    'tax','currency','short_code','email')
                    ->join('country','country.id','=','country_id')
                    ->where('users.id', $user_id)->first();
        if (!$user) {
            return response()->json(['status' => "failure", 'message' => 'Invalid User']);  
        }

        $email = $user->email;
        $currency = $user->currency;
        $country_code = $user->short_code;
        $i_account = $user->i_account;
        //$promocode      = ($modify_card_for == 'credit') ? Auth::user()->promocode : "";

        if($credit_card_id == 'cash') {
            $amount = $request->cash_amount;
            $desc = $request->cash_desc;
            $cash_credit_to = $request->cash_credit_to;

            $tax_amount = ($amount * $user->tax)/100;
            $total_amount = number_format($amount, 2, '.', "");  

            $obj = new \stdClass();
            $obj->name = $user->name;
            $obj->amount = $user->currency_symbol.$total_amount;
            $obj->transaction_id = '';            
            $obj->subject = 'AVOOMobile Payment Confirmation';
            $obj->heading = 'Payment Status - Success';

            $payment['user_id'] = $user_id;             
            //$payment['transaction_id'] = ''; 
            $payment['amount'] = $amount;      
            $payment['tax_amount'] = $tax_amount;
            $payment['total_amount'] = $total_amount;
            $payment['payment_for'] = 'Credit Added';
            $payment['buy_price'] = $total_amount;
            $payment['payment_method'] = 'Cash Payment';  
            $payment['description'] = $desc.' by '.Auth::user()->first_name.' '.Auth::user()->last_name;;  
            
            $swithlog['description'] = $payment['payment_for'].' by '.Auth::user()->first_name.' '.Auth::user()->last_name;
            $swithlog['user_id']  = $user_id;

            if($cash_credit_to == "1") { //credit to app
                $method = 'accountCredit'; //accountAddFunds 
                $payment['category'] = 'switch';
                $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$amount,$currency,$desc);
                $temp =  SwitchHelper::call_switch_api($xml_data);
                if (array_key_exists("fault", $temp)) {
                    $payment['status'] = '2';                   
                    $payment_id = UserPayment::insertGetId($payment);
                    $payment_id = Crypt::encrypt($payment_id);
                    $obj->heading = 'Payment Status - Error';
                    $obj->error = 1;
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                        Mail::to($email)
                            // ->cc()
                            ->bcc('jijo.joseph@gencomtel.com')
                            ->send(new PaymentStatus($obj)); 
                    }
                    if($modify_card_for == 'credit'){
                        return response()->json(['status' => "failure", 'message' => "Payment failure"]);
                    }
                } else {
                    $swithlog['status'] = 1;
                    SwitchLog::insertGetId($swithlog);
                    $payment['status'] = '1';
                    //$payment['promocode'] = $promocode;
                    $payment_id = UserPayment::insertGetId($payment);
                    $payment_id = Crypt::encrypt($payment_id);  
                    $user_balance = Account::where('user_id',$user_id)->first();
                    $balance['balance_amount'] = $user_balance->balance_amount + $amount;
                    Account::where('user_id',$user_id)->update($balance); 
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)){                  
                        Mail::to($email)
                            // ->cc()
                            ->bcc('jijo.joseph@gencomtel.com')
                            ->send(new PaymentStatus($obj)); 
                    }
                    if($modify_card_for == 'credit'){
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                    }

                } 
            }else if($cash_credit_to == "2") {
                // credit the amount to sim
                $mvno_key   = Helper::get_option('bundle_mvno_key');
                $end_point  = '/superapi/topup?mvno='.$mvno_key;
                $msisdnDetails = User::find($user_id)->msisdn;
                if(!isset($msisdnDetails)) {
                    return response()->json(['status' => "failure", 'message' => "Somthing went wrong, Number not Found"]);
                }

                $payment['category'] = 'sim';
                $msisdn          = $msisdnDetails->phone_number;
                $amount_in_pence = $amount * 100;
                $sim_api_data = ["msisdn" => $msisdn, "amount" => $amount_in_pence];
                $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                // $response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [{}]}');
                if($response->resultType == 'SUCCESS' && $response->resultCode == 0){
                    $log_data = array(
                        'user_id' => $user_id,
                        'msisdn' => $msisdn,
                        'category' => 'EXTERNAL_TOPUP',
                        'reference_id' => property_exists($response, 'orderCode') ? $response->orderCode : 0
                    );
                    DB::table('avoo_sim_log')->insert($log_data);

                    $payment['status'] = '1';
                    //$payment['promocode'] = $promocode;
                    $payment_id = UserPayment::insertGetId($payment);
                    $payment_id = Crypt::encrypt($payment_id);  
                    //$user_balance = Account::where('user_id',$user_id)->first();
                    //$balance['balance_amount'] = $user_balance->balance_amount + $amount;
                    //Account::where('user_id',$user_id)->update($balance);   
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)){                  
                        Mail::to($email)
                            // ->cc()
                            ->bcc('jijo.joseph@gencomtel.com')
                            ->send(new PaymentStatus($obj));
                    }
                    if($modify_card_for == 'credit'){
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                    }
                }else{
                    $payment['status'] = '2';
                    $payment_id = UserPayment::insertGetId($payment);
                    $payment_id = Crypt::encrypt($payment_id);
                    $obj->heading = 'Payment Status - Error';
                    $obj->error = 1;
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                        Mail::to($email)
                            // ->cc()
                            ->bcc('jijo.joseph@gencomtel.com')
                            ->send(new PaymentStatus($obj));
                    }
                    if($modify_card_for == 'credit'){ 
                        return response()->json(['status' => "failure", 'message' => "Payment Failure"]);
                    }              
                }
            }
        }
        else if($credit_card_id == 'new') {
            $firstname = urlencode($request->first_name);
            $lastname = urlencode($request->last_name);
            $address = urlencode($request->address);
            $state = urlencode($request->state);
            $city = urlencode($request->city);
            $postal_code = urlencode($request->zip);
            $card_number = urlencode(str_replace("-", "", $request->card_number));
            $exp_month = str_pad($request->exp_month, 2, '0', STR_PAD_LEFT);     
            $exp_year = $request->exp_year;
            $cvv = $request->cvv;

            $amount = ($modify_card_for == 'credit') ? $request->topupamount : '0.10';
            $tax_amount = ($amount * $user->tax)/100;
            $total_amount = number_format($amount, 2, '.', "");  

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
            $method_name = 'DoDirectPayment';
            $payment_type = urlencode('Sale');

            // Add request-specific fields to the request string.
            $nvp_str = "&PAYMENTACTION=$payment_type&AMT=$total_amount&ACCT=$card_number&EXPDATE=$exp_month$exp_year&CVV2=$cvv&FIRSTNAME=$firstname&LASTNAME=$lastname&CURRENCYCODE=$currency&CUSTOM=$i_account&EMAIL=$email&COUNTRYCODE=$country_code&STATE=$state&CITY=$city&STREET=$address&ZIP=$postal_code";

            $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature$nvp_str";

            $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);
            $rsponse_status = strtoupper($response["ACK"]);
            if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                $txn_id = $response['TRANSACTIONID'];
                $exp_day = date('t',strtotime($exp_year.'-'.$exp_month));
                $card_expire = $exp_year.'-'.$exp_month.'-'.$exp_day;
                $card_type =  ucfirst($request->card_type).' ****'.substr($card_number, -4);

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
                    $affected = UserCreditCard::where('user_id', $user_id)->update(['is_default' => 0]);
                }

                $credit_card = UserCreditCard::updateOrCreate(['user_id' => $user_id, 'card_type' => $card_type, 'card_expiry' => $card_expire],['transaction_id' => $txn_id, 'is_default' => $card_is_default]);

                $credit_card_id = $credit_card->id;

                $obj = new \stdClass();
                $obj->name = $user->name;
                $obj->amount = $user->currency_symbol.$total_amount;
                $obj->transaction_id = $txn_id;            
                $obj->subject = 'AVOOMobile Payment Confirmation';
                $obj->heading = 'Payment Status - Success';

                $payment['user_id'] = $user_id;             
                $payment['transaction_id'] = $txn_id; 
                $payment['amount'] = $amount;      
                $payment['tax_amount'] = $tax_amount;
                $payment['total_amount'] = $total_amount;

                if($modify_card_for == 'credit'){
                    $payment['payment_for']     = 'Credit Added';
                    $payment['payment_method']  = 'Card Payment';  
                    $payment['description']     = 'Credit Added';
                }else{
                    $payment['payment_for'] = 'Credit Card Added';
                    $payment['payment_method'] = 'Card Payment';  
                    $payment['description'] = 'Add Credit Card';  
                }
                $swithlog['description'] = $payment['payment_for'].' by '.Auth::user()->first_name.' '.Auth::user()->last_name;
                $swithlog['user_id']  = $user_id;
                // credit the amount to app
                if($is_refund != "1") {

                    $credit_to      = $request->credit_to;
                    $promocode      = ($modify_card_for == 'credit') ? Auth::user()->promocode : "";
                    if($credit_to == "1") { //credit to app

                    $method = 'accountCredit'; //accountAddFunds 
                    $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$amount,$currency,$payment['description']);
                    $temp =  SwitchHelper::call_switch_api($xml_data);
                    //$temp = [];
                    if (array_key_exists("fault", $temp)) {
                        $swithlog['status'] = 2;
                        SwitchLog::insertGetId($swithlog);
                        $payment['status'] = '2';
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);
                        $obj->heading = 'Payment Status - Error';
                        $obj->error = 1;
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                            Mail::to($email)
                                // ->cc()
                                // ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                        if($modify_card_for == 'credit'){
                        return response()->json(['status' => "failure", 'message' => "Payment failure"]);
                        }
                    } else {
                        $swithlog['status'] = 1;
                        SwitchLog::insertGetId($swithlog);  
                        $payment['status'] = '1';
                        $payment['promocode'] = $promocode;
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);  
                        $user_balance = Account::where('user_id',$user_id)->first();
                        $balance['balance_amount'] = $user_balance->balance_amount + $amount;
                        Account::where('user_id',$user_id)->update($balance);  
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){                 
                            Mail::to($email)
                                // ->cc()
                                // ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj));
                        } 
                        if($modify_card_for == 'credit'){
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                        }

                    } 
                    }else if($credit_to == "2") {
                    // credit the amount to sim
                    $mvno_key   = Helper::get_option('bundle_mvno_key');
                    $end_point  = '/superapi/topup?mvno='.$mvno_key;
                    $msisdnDetails = User::find($user_id)->msisdn;
                    if(!isset($msisdnDetails)) {
                        return response()->json(['status' => "failure", 'message' => "Somthing went wrong, Number not Found"]);
                    }

                    $msisdn          = $msisdnDetails->phone_number;
                    $amount_in_pence = $amount * 100;
                    $sim_api_data = ["msisdn" => $msisdn, "amount" => $amount_in_pence];
                    $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                    // $response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [{}]}');
                    if($response->resultType == 'SUCCESS' && $response->resultCode == 0){
                        $log_data = array(
                            'user_id' => $user_id,
                            'msisdn' => $msisdn,
                            'category' => 'EXTERNAL_TOPUP',
                            'reference_id' => property_exists($response, 'orderCode') ? $response->orderCode : 0
                        );
                        DB::table('avoo_sim_log')->insert($log_data);

                        $payment['status'] = '1';
                        $payment['promocode'] = $promocode;
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);  
                        //$user_balance = Account::where('user_id',$user_id)->first();
                        //$balance['balance_amount'] = $user_balance->balance_amount + $amount;
                        //Account::where('user_id',$user_id)->update($balance);  
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){                   
                            Mail::to($email)
                                // ->cc()
                                // ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj));
                        }
                        if($modify_card_for == 'credit'){
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                        }
                    }else{
                        $payment['status'] = '2';
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);
                        $obj->heading = 'Payment Status - Error';
                        $obj->error = 1;
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                            Mail::to($email)
                                // ->cc()
                                // ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj));
                        }
                        if($modify_card_for == 'credit'){ 
                        return response()->json(['status' => "failure", 'message' => "Payment Failure"]);
                        }              
                    }
                    }
                } else {
                    $payment['user_id'] = $user_id;             
                    $payment['transaction_id'] = $txn_id; 
                    $payment['amount'] = $amount;      
                    $payment['tax_amount'] = $tax_amount;
                    $payment['total_amount'] = $total_amount;
                    $payment['payment_for'] = 'Credit Card Verfication Process';
                    $payment['payment_method'] = 'Paypal';  
                    $payment['description'] = 'Credit Card Added';
                    $payment['status'] = '1';
                    $payment_id = UserPayment::insertGetId($payment);

                    // refund the amount
                    $version = urlencode('94.0');
                    $method_name = 'RefundTransaction';

                    // Add request-specific fields to the request string.
                    $nvp_str = "&TRANSACTIONID=$txn_id&REFUNDTYPE=Full";

                    $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature$nvp_str";

                    $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);
                    $rsponse_status = strtoupper($response["ACK"]);
                    if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                        $refund_txn_id = $response['REFUNDTRANSACTIONID'];

                        $payment['user_id'] = $user_id;             
                        $payment['transaction_id'] = $refund_txn_id; 
                        $payment['amount'] = '-'.$response["TOTALREFUNDEDAMOUNT"];      
                        $payment['tax_amount'] = 0;
                        $payment['total_amount'] = '-'.$response["TOTALREFUNDEDAMOUNT"];
                        $payment['payment_for'] = 'Credit Card Added amount refunded';
                        $payment['payment_method'] = 'Card Payment - Refund';  
                        $payment['description'] = 'Credit Card Added amount refunded';
                        $payment['status'] = '1';
                        $payment_id = UserPayment::insertGetId($payment);
                    }
                }
                
            }   else {
                $error_msg = $user_id.''.json_encode($response).chr(10).chr(10);
                $fp = fopen('card_error.txt', 'a+');
                fwrite($fp, $error_msg.chr(10));
                fclose($fp);
                $payment['user_id'] = $user_id;             
                $payment['transaction_id'] = ''; 
                $payment['amount'] = $amount;      
                $payment['tax_amount'] = $tax_amount;
                $payment['total_amount'] = $total_amount;
                $payment['payment_for'] = 'Change Card';
                $payment['payment_method'] = 'Card Payment';  
                $payment['description'] = 'error : '.$response['L_LONGMESSAGE0']; 
                $payment['status'] = '0';
                DB::table('user_payments')->insert($payment);

                return response()->json(['status' => "failure", 'message' => $response['L_LONGMESSAGE0']]);
            }
        }else if(is_numeric($credit_card_id) && $modify_card_for == 'credit') {

            $card_data = UserCreditCard::where('id', $credit_card_id)->first();

            $promocode      = Auth::user()->promocode;
            $email          = $user->email;

            $credit_to      = $request->credit_to;

            $referenceid    = $card_data->transaction_id;
            $amount         = $request->topupamount;

            $tax_amount     = ($amount * $user->tax)/100;
            $billamount     = number_format($amount, 2, '.', "");

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

            $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$billamount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

            $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 
            $rsponse_status = strtoupper($response["ACK"]);
            if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                $txn_id = $response['TRANSACTIONID'];

                $payment['user_id']         = $user_id;             
                $payment['transaction_id']  = $txn_id; 
                $payment['amount']          = $amount;      
                $payment['tax_amount']      = $tax_amount;
                $payment['total_amount']    = $billamount;
                $payment['buy_price']       = $billamount;
                $payment['payment_for']     = 'Credit Added';
                $payment['payment_method']  = 'Card Payment';  
                $payment['description']     = 'Credit Added';

                $obj = new \stdClass();
                $obj->name = $user->name;
                $obj->amount = $user->currency_symbol.$billamount;
                $obj->transaction_id = $txn_id;            
                $obj->subject = 'AVOOMobile Payment Confirmation';
                $obj->heading = 'Payment Status - Success';

                DB::table('user_credit_cards')->where('id', $credit_card_id)->update(array('transaction_id' => $txn_id));

                $swithlog['description'] = $payment['payment_for'].' by '.Auth::user()->first_name.' '.Auth::user()->last_name;
                $swithlog['user_id']  = $user_id;
                // credit the amount to app
                if($credit_to == "1") {
                    $method   = 'accountCredit'; //accountAddFunds 
                    $payment['category']     = 'switch';
                    $xml_data = SwitchHelper::switch_account_bal_xml($method,$i_account,$amount,$currency,$payment['description']);
                    $temp =  SwitchHelper::call_switch_api($xml_data);
                    if (array_key_exists("fault", $temp)) {
                        $swithlog['status'] = 2;
                        SwitchLog::insertGetId($swithlog);
                        $payment['status'] = '2';
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);
                        $obj->heading = 'Payment Status - Error';
                        $obj->error = 1;
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                            Mail::to($email)
                                // ->cc()
                                ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                        return response()->json(['status' => "failure", 'message' => "Payment failure"]);
                    } else {
                        $swithlog['status'] = 1;
                        SwitchLog::insertGetId($swithlog);  
                        $payment['status'] = '1';
                        $payment['promocode'] = $promocode;
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);  
                        $user_balance = Account::where('user_id',$user_id)->first();
                        $balance['balance_amount'] = $user_balance->balance_amount + $amount;
                        Account::where('user_id',$user_id)->update($balance);  
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){                 
                            Mail::to($email)
                                // ->cc()
                                ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                    } 
                }else if($credit_to == "2") {
                    // credit the amount to sim
                    $payment['category']     = 'sim';
                    $mvno_key   = Helper::get_option('bundle_mvno_key');
                    $end_point  = '/superapi/topup?mvno='.$mvno_key;
                    $msisdnDetails = User::find($user_id)->msisdn;
                    if(!isset($msisdnDetails)) {
                        return response()->json(['status' => "failure", 'message' => "Somthing went wrong, Number not Found"]);
                    }

                    $msisdn          = $msisdnDetails->phone_number;
                    $amount_in_pence = $amount * 100;
                    $sim_api_data = ["msisdn" => $msisdn, "amount" => $amount_in_pence];
                    $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                    // $response = json_decode('{"AccountId": "1050000000000000854", "orderCode": "6722820963162652686", "resultType": "Ok", "resultCode": "0", "messages": [{}]}');
                    if($response->resultType == 'SUCCESS' && $response->resultCode == 0){
                        $log_data = array(
                            'user_id' => $user_id,
                            'msisdn' => $msisdn,
                            'category' => 'EXTERNAL_TOPUP',
                            'reference_id' => property_exists($response, 'orderCode') ? $response->orderCode : 0
                        );
                        DB::table('avoo_sim_log')->insert($log_data);

                        $payment['status'] = '1';
                        $payment['promocode'] = $promocode;
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);  
                        //$user_balance = Account::where('user_id',$user_id)->first();
                        //$balance['balance_amount'] = $user_balance->balance_amount + $amount;
                        //Account::where('user_id',$user_id)->update($balance);  
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){                   
                            Mail::to($email)
                                // ->cc()
                                ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                        return response()->json(['status' => "success", 'message' => "Payment Success"]);
                    }else{
                        $payment['status'] = '2';
                        $payment_id = UserPayment::insertGetId($payment);
                        $payment_id = Crypt::encrypt($payment_id);
                        $obj->heading = 'Payment Status - Error';
                        $obj->error = 1;
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                            Mail::to($email)
                                // ->cc()
                                ->bcc('jijo.joseph@gencomtel.com')
                                ->send(new PaymentStatus($obj)); 
                        }
                        return response()->json(['status' => "failure", 'message' => "Payment Failure"]);              
                    }
                }
            }else{
                $error_msg = $user_id.''.json_encode($response).chr(10).chr(10);
                $fp = fopen('card_error.txt', 'a+');
                fwrite($fp, $error_msg.chr(10));
                fclose($fp);
                $payment['user_id'] = $user_id;             
                $payment['transaction_id'] = ''; 
                $payment['amount'] = $amount;      
                $payment['tax_amount'] = $tax_amount;
                $payment['total_amount'] = $total_amount;
                $payment['payment_for'] = 'Change Card';
                $payment['payment_method'] = 'Card Payment';  
                $payment['description'] = 'error : '.$response['L_LONGMESSAGE0']; 
                $payment['status'] = '0';
                DB::table('user_payments')->insert($payment);

                return response()->json(['status' => "failure", 'message' => $response['L_LONGMESSAGE0']]);
            }
        }

        if($modify_card_for == 'plan') {
            $plan_id = $request->modify_id;

            $auto_plan = AutoPlan::where('id', $plan_id)->where('user_id', $user_id)->first();
            if (!$auto_plan) {
                return response()->json(['status' => "failure", 'message' => 'Invalid plan']);  
            }

            $card_data = UserCreditCard::where('id', $credit_card_id)->first();

            $auto_plan->card_id = $credit_card_id;
            $auto_plan->transaction_id = $card_data->transaction_id;
            $auto_plan->card_expiry = $card_data->card_expiry;
            $auto_plan->card_type = $card_data->card_type;

            DB::beginTransaction();

            try {

                $auto_plan->save();

                DB::commit();

                return response()->json(['status' => "success", 'message' => 'Card changed successfully', 
                    'data' => ['card_id' => $credit_card_id, 'card_type' => $card_data->card_type] ]);  

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => "failure", 'message' => 'Failed to update']);
            }
        }
        else if($modify_card_for == 'recharge') {
            $recharge_id = $request->modify_id;

            $auto_recharge = AutoRecharge::where('id', $recharge_id)->where('user_id', $user_id)->first();
            if (!$auto_recharge) {
                return response()->json(['status' => "failure", 'message' => 'Invalid Recharge']);  
            }

            $card_data = UserCreditCard::where('id', $credit_card_id)->first();

            $auto_recharge->card_id = $credit_card_id;
            $auto_recharge->transaction_id = $card_data->transaction_id;
            $auto_recharge->card_expiry = $card_data->card_expiry;
            $auto_recharge->card_type = $card_data->card_type;

            DB::beginTransaction();

            try {

                $auto_recharge->save();

                DB::commit();

                return response()->json(['status' => "success", 'message' => 'Card changed successfully', 
                    'data' => ['card_id' => $credit_card_id, 'card_type' => $card_data->card_type] ]);  

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => "failure", 'message' => 'Failed to update']);
            }
        }
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_sim_history(Request $request) 
    { 
        $user_id = $request->user_id;
        $mvno_key   = Helper::get_option('bundle_mvno_key');
        $user = User::where('id', $user_id)->first();        
        $from = DB::table('user_calls')
                        ->where('user_id', $user_id)->where('history_from', 2)
                        ->max('connect_date');
        if($from == ""){
            $from = $user->created_at;
        }
        if($from != ""){
            $fromdate = Carbon::parse($from)->format('Y-m-d H:i:s');
            $todate = Carbon::now()->format('Y-m-d H:i:s');
            $msisdn = $user->msisdn->phone_number;               
            $params = 'msisdn='.$msisdn.'&fromDate='.urlencode($fromdate).'&toDate='.urlencode($todate);
            $end_point = '/superapi/usage?'.$params.'&mvno='.$mvno_key;
            $response = Helper::call_sim_process_api($end_point,'','get');

            if($response->message == 'Success' && $response->statusCode == 0){ 
                if(!empty($response->usages)){                        
                    foreach($response->usages as $history){
                        $connect = Carbon::parse($history->date)->format('Y-m-d H:i:s');
                        if($history->serviceType == 'DATA' || $history->serviceType == 'SMS_MO'){                                
                            $usage_exist = DB::table('usage_history')
                                ->where('date', $connect)->where('user_id', $user_id)->exists();
                            if($usage_exist){
                                continue;
                            }
			    
			    if($history->to == '65075'){
                                NotificationLog::create(['user_id'=>$user_id,'message' => 'Port Out Requested -'.$connect,'description'=>'user:'.$user_id.', msg:port','status'=>'0']); 
                            }
			    	
                            $data_history = ['user_id'=> $user_id,'from_number'=>$history->from,'to_number'=>$history->to,'date'=>$connect,'duration'=>$history->duration,'amount'=> $history->amount,'service_type' => $history->serviceType ];
                            DB::table('usage_history')->insert($data_history);
                            continue;
                        }

                        $duration = $history->duration;
                        $disconnect = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));
                        $history_exist = DB::table('user_calls')
                            ->where('connect_date', $connect)->whereIn('history_from', [2,3])
                            ->where('user_id', $user_id)->exists();
                        if($history_exist){
                            continue;
                        }

                        if($history->serviceType !== 'VOICE_MO' || $history->serviceType !== 'VOICE_MT'){                           
                            continue;
                        }

                        $service_type = ($history->serviceType == 'VOICE_MO')?'1':'2';
                        if($history->to == '447973101233'){
                            $service_type = 3;
                        }
                        $i_cdr = time().mt_rand(1000, 9999);
                        $simhis_data = ['user_id' =>$user_id, 'connect_date' =>$connect, 'disconnect_date' => $disconnect, 'cli' => $history->from,   'cli_in' => $history->from, 'cld'=> $history->to, 'i_cdr' => $i_cdr, 'duration' => $duration, 'cost' => $history->amount, 'history_from' => 2, 'created_at'=> $todate, 'service_type'=> $service_type];                            
                        DB::table('user_calls')->insert($simhis_data);
                    }
                }
            }
        } 
        return 1;         
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_sim_info(Request $request) 
    {
        $user_id = $request->user_id;
        $mvno_key   = Helper::get_option('bundle_mvno_key');
        $user = User::where('id', $user_id)->first();
        $msisdn = $user->msisdn->phone_number;
        $end_point = '/superapi/accountInformation/'. $msisdn .'?mvno='. $mvno_key;
        $response = Helper::call_sim_process_api($end_point, '', 'get');

        $data['status'] = 'error';    
        if($response->resultType == 'SUCCESS' && $response->resultCode == '0'){
        	if(is_array($response->resources)){
	            $data_bundle = ($response->resources[3] < '134218')? number_format(($response->resources[3]->resourceValue/1024),2,'.',',').' MB': number_format(($response->resources[3]->resourceValue/(1024*1024)),2,'.',',').' GB';
	            $roaming_data = ($response->resources[4] < '134218')? number_format(($response->resources[4]->resourceValue/1024),2,'.',',').' MB': number_format(($response->resources[4]->resourceValue/(1024*1024)),2,'.',',').' GB';
	            $next_renewal = Carbon::parse($response->resources[1]->resourceExpirationDate)->format('Y-m-d H:i:s');
	            $now = Carbon::now();
	            $next_renewal = ($next_renewal >= $now)?$next_renewal:'No Active Plan';
	            $data['status'] = 'success';
	            $data['network_status'] = $response->{'card-network-status'};
	            $data['remaining_credit'] = '£'.($response->{'remaining-credit'}/100);
	            $data['sms_bundle'] = $response->resources[1]->resourceValue;
	            $data['voice_bundle'] = ($response->resources[2]->resourceValue/60).' Min';
	            $data['data_bundle'] = ($data_bundle > 1)? $data_bundle :number_format(($response->resources[3]->resourceValue/1024),2,'.',',');
	            $data['data_bundle_roam'] = $roaming_data;
	            $data['voice_bundle_roam'] = ($response->resources[5]->resourceValue/60).' Min';
	            $data['sms_bundle_roam'] = $response->resources[6]->resourceValue;
	            $data['next_renewal'] = $next_renewal;
	        }
        }
        return response()->json($data);
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function subscription_renewal_popup(Request $request) 
    {
        $autoPlan = AutoPlan::where('id',$request->plan_id)->first(); 
        $plans = [];
        if ($autoPlan->plan_type == 'sim') {           
            if ($autoPlan->bundle_id == 0) {
                $provider = $autoPlan->plan->provider;
                $category = ['EE'=>[1],'O2'=>[2],'VUK'=>[3]];
                $plans = DB::table('tbl_plans')->where('status', 1)->where('provider',$provider)
                            ->whereIn('category', $category[$provider])->get();
                //->whereIn('category', ['1','0'])
            }else{

                $plans = DB::table('tbl_bundles')->where(['status'=>1,'sim_count'=>$autoPlan->getplan->sim_count])->whereIn('category', ['1','0'])->get();
            }
        }
        $currency = Helper::get_option('currency_symbol');
        return view('user.subscription-renew',compact('autoPlan','plans','currency'))->render();
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_subscription_renewal(Request $request) 
    {
        $fp = fopen('custom_subscrition.txt', 'a+');
        $curr_day = Carbon::now()->format('Y-m-d');
        $next_renewal = Carbon::now()->addDays(30)->format('Y-m-d');
        $customer_id = Helper::get_option('mvno_customer_id');
        $mvno_key = Helper::get_option('bundle_mvno_key');
        $tax = Helper::get_option('country_tax');
        $plans = AutoPlan::where('id',$request->autoplan_id)
                        ->first();

        if($plans){ 
            $user = DB::table('users')->select('name','email','phone','i_account',
                        'currency','currency_symbol')
                        ->join('country','country_id','=','country.id')
                        ->where('users.id', $plans->user_id)->where('users.status', '1')
                        ->first(); //taking user details from users table against the user
            
            $blocked = Helper::check_fraudster($plans->user_id); //check whether user is in fraud list
            if(!$user || $blocked){  //if in fraud or user doen't active escape
                $response['status'] = '0';
                $response['message'] = 'Account is in blocked list';
                return $response;
            }
            
            $currency       = $user->currency;
            $i_account      = $user->i_account; 
            $plan_id = isset($request->plan_id)?$request->plan_id:0;
            $bundle_id = isset($request->bundle_id)?$request->bundle_id:0;
            if($bundle_id != 0){
                $bundle = DB::table('tbl_bundles')->where('id', $bundle_id)->first();
                $buy_price = $bundle->buy_price;
                $total_amount = $bundle->sell_price;
                $plan_id = $bundle->plan_id;
            }else{
                $plan = DB::table('tbl_plans')->where('id', $plan_id)->first();
                $buy_price = $plan->buy_price;
                $total_amount = $plan->sell_price;
            }
            if($plan_id != $plans->plan_id ||  $bundle_id != $plans->bundle_id){
                $tax_amount = ($total_amount * $tax)/100;
                $amount = ($total_amount - $tax_amount);

                $plans = AutoPlan::updateOrCreate(['id' => $request->autoplan_id], ['plan_id' => $plan_id, 'bundle_id' => $bundle_id, 'amount' => $amount, 'tax' => $tax_amount, 'total_amount' => $total_amount, 'status'=>1]);
            }
            
            if($request->renewal_type == 1){                
                if($request->payment_mode == 1) {
                    if($plans->card_expiry < $curr_day){
                        $response['status'] = '0';
                        $response['message'] = 'Invalid Card Details';
                        return $response;        
                    } 
                    $payment_flag = 0;
                    $billamount = $plans->total_amount;
                    $environment    = Helper::get_option('paypal_nvp_mode');
                    $api_endpoint   = 'https://api-3t.paypal.com/nvp';
                    $api_user       = Helper::get_option('paypal_nvp_username');
                    $api_password   = Helper::get_option('paypal_nvp_password');
                    $api_signature  = Helper::get_option('paypal_nvp_signature');
                    $version        = urlencode('86.0');  
                    $method_name    = 'DoReferenceTransaction';
                    $payment_type   = urlencode('Sale');
                    $referenceid    = $plans->transaction_id;                    

                    $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$billamount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

                    $response = Helper::call_nvp_payment($api_endpoint, $nvp_req);  
                    
                    $rsponse_status = strtoupper($response["ACK"]);
                    if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
                        $payment_flag = 1;
                        $txn_id = $response['TRANSACTIONID'];
                        $payment = ['transaction_id' => $txn_id, 'total_amount' => $billamount, 'payment_method' => 'Paypal', 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $plans->user_id, 'amount' => $plans->amount, 'tax_amount' => $plans->tax, 'description' => 'Monthly Subscription (users: '.$plans->user_list.', card:'.$plans->card_type.')', 'status' => '1', 'category' => 'sim', 'buy_price' => $buy_price];
                        $paymentid = UserPayment::insertGetId($payment);
                    }else {
                        $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0']]);
                        
                        $notify = NotificationLog::create(['user_id'=>$plans->user_id,'message' => 'Manual Subscription Payment Failed','description'=>'Sub ID:'.$plans->id.', card:'.$plans->card_type.', msg:'.$pay_error.', admin:'.Auth::id(),'status'=>'0']);
                        $obj = (object) array(
                            'notify_id' => $notify->id,
                            'subscripton'=> $plans->id,
                        );  
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1)); 

                        $payment = ['transaction_id' => '', 'total_amount' => $billamount, 'payment_method' => 'Paypal', 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $plans->user_id, 'amount' => $plans->amount, 'tax_amount' => $plans->tax, 'description' => 'Subscription Error :'.$response['L_LONGMESSAGE0'].', '.$notify->id, 'status' => '0', 'buy_price' => '0'];
                        $paymentid = UserPayment::insertGetId($payment);
                        fwrite($fp, 'subscription payment error: auto_id-'.$plans->id.' User-'.$plans->user_id.chr(10).json_encode($response).chr(10));
                        $data['status'] = '0';
                        $data['message'] = 'Payment Failed. '.$response['L_LONGMESSAGE0'];
                        return $data;
                    }
                } 
                if($request->payment_mode == 2){
                    $payment_method = $request->payment_method;
                    $payment = ['total_amount' => $plans->total_amount, 'payment_method' => $payment_method, 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $plans->user_id, 'amount' => $plans->amount, 'tax_amount' => $plans->tax, 'status' => '1', 'category' => 'sim', 'buy_price' => $buy_price];
                    
                    $payment['transaction_id'] = $request->custom_txn_id;
                    $payment['description'] = $request->custom_description.' By '.Auth::user()->first_name.' '.Auth::user()->last_name;

                    $paymentid = UserPayment::insertGetId($payment);
                    $payment_flag = 1;
                }
                if($payment_flag == 1){
                    if( $plans->plan_type === 'switch' ){   
                        $plandetail = $plans->plan;
                        $i_billplan = '203'; //$plandetail->plan_id;
                        $in_call_limit = '1000'; //$plandetail->minutes;   
                    }else if( $plans->plan_type === 'sim'){ 
                        $plandetail = $plans->getplan;                            
                        $i_billplan = '203'; //$plans->plan->switch_billing_plan;
                        $in_call_limit = '1000'; // $plandetail or $plans->plan->in_call_limit;
                        $simbillplan   = $plans->plan->sim_billing_plan;
                    }
                    
                    $childlist = explode(',', $plans->user_list); //child list for the user
                    foreach ($childlist as $child) {
                        $userdata = User::find($child); //child user details

                        $defult_plan = DB::table('switch_template')->where('id', $userdata->switch_id)->value('billing_plan');
                        $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
                        $temp     = SwitchHelper::call_switch_api($xml_data);

                        $i_account = $userdata->i_account;
                        
                        if($plans->plan_type === 'sim'){
                            $msisdn = $userdata->msisdn->phone_number;
                            if($plans->plan->provider == 'EE'){
                                $subscription_id = $userdata->userDetail->sim_subscription_id;
                                $end_point = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim                                
                                $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$simbillplan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];

                                $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                            }else{                
                                $next_renewal = date('Y-m-t', strtotime(Carbon::tomorrow()));
                                $response = json_decode('{"orderCode":"O2customorder","resultType":"Ok","resultCode":"0"}');
                            }
                            
                            if($response->resultType == 'Ok' && $response->resultCode == 0){
                                fwrite($fp, 'subscription success: auto_id-'.$plans->id.' User-'.$child.chr(10).json_encode($response).chr(10));
                                $log_data = array(
                                    'user_id' => $userdata->id,
                                    'stock_id' => $userdata->stock_id,
                                    'msisdn' => $msisdn,
                                    'category' => 'RECURRING',
                                    'plan_id' => $plans->plan_id,
                                    'value' => $plandetail->buy_price,
                                    'staff_id' => Auth::id(),
                                    'reference_id' => $response->orderCode
                                );
                                DB::table('avoo_sim_log')->insert($log_data);

                                $autoplan['next_renewal'] = $next_renewal;
                                AutoPlan::where('id', $plans->id)->update($autoplan);
                            }else{  
                                $notification = NotificationLog::create(['user_id'=>$child,'message' => 'Manual Subscription EE Subscription Renewal Failed','description'=>'Sub ID:'.$plans->id.', msg:'.json_encode($response).', admin:'.Auth::id(),'status'=>'0']);   
                                $data['status'] = '0';
                                $data['message'] = 'EE Subscription Failed';
                                $obj = (object) array(
                                    'notify_id' => $notification->id,
                                    'subscripton'=> $plans->id,
                                );  
                                FailureNotification::dispatch($obj)
                                    ->delay(now()->addMinutes(1)); 
                                return $data;                                 
                                fwrite($fp, 'subscription EE error: auto_id-'.$plans->id.' User-'.$child.chr(10).json_encode($response).chr(10));
                            }
                        }
                        
                        $method = 'accountCredit';
                        $note = 'Monthly Subscription (users: '.$plans->user_list.', card:'.$plans->card_type.')';
                        $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, '0.01', $currency, $note); 
                        $temp =  SwitchHelper::call_switch_api($credit_xml);
                        if (array_key_exists("fault", $temp)) {
                            $notification = NotificationLog::create(['user_id'=>$child, 'message' => 'Manual Subscription Switch AddCredit Failed','description'=>'Sub ID:'.$plans->id.', msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']);
                            $obj = (object) array(
                                'notify_id' => $notification->id,
                                'subscripton'=> $plans->id,
                            );  
                            FailureNotification::dispatch($obj)
                                ->delay(now()->addMinutes(1)); 
                        }

                        $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $i_billplan);
                        $temp     = SwitchHelper::call_switch_api($xml_data);

                        if (array_key_exists("fault", $temp)) {
                            $notification = NotificationLog::create(['user_id'=>$child,'message' => 'Manual Subscription Switch Plan Update Failed','description'=>'sub id:'.$plans->id.', msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']); 
                            $data['status'] = '0';
                            $data['message'] = 'Switch Plan Update Failed';
                            $obj = (object) array(
                                'notify_id' => $notification->id,
                                'subscripton'=> $plans->id,
                            );  
                            FailureNotification::dispatch($obj)
                                ->delay(now()->addMinutes(1)); 
                            return $data;
                        }else{
                            $balance['balance_minutes'] = $in_call_limit;
                            Account::where('user_id',$child)->update($balance);
                            $where['user_id']       = $child; 
                            $where['plan_type']     = $plans->plan_type;
                            $update['status']       = 0; 
                            UserPlan::where($where)->update($update);   
                            $usage['plan_id']       = $plans->plan_id;  
                            $usage['user_id']       = $child;
                            $usage['payment_id']    = $paymentid; 
                            $usage['status']        = 1; 
                            $usage['plan_type']     = $plans->plan_type;
                            UserPlan::create($usage);   
                        }
                    }
                }
            }

            $data['status'] = '1';
            $data['message'] = 'subscription Renewed Successfully';
            return $data;
        }
        $response['status'] = '0';
        $response['message'] = 'Invalid Card Details';
        return $response;
        fclose($fp);
        echo 1;
    }
     /**
    * Internation plan purchase
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function inter_plan(Request $request) 
    {
        $id     = explode('-',Crypt::decrypt($request->id));
        $userid = $id[0];
        $planid = $id[1];
        $credit_cards = DB::table('user_credit_cards')->where('user_id',$userid)->get();
        $plan = DB::table('tbl_plans')
                    ->select('id','plan_name as product_name','sell_price','period','in_call_limit')
                    ->where('status', 1)->where('id', $planid)->first();
        $currency       = Helper::get_option('currency_symbol');
        $user = User::where('id',$userid )->first();
        return view('user.inter-plan',compact('credit_cards','plan','currency','user'));
    }
    /**
    * save International plan purchase
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_interplan(Request $request) 
    {
        $rules = array('plan_id' => 'required','credit_card'=>'required');
        $messages = array(
        'plan_id.required' => 'The Plan required.',
        'credit_card.required' => 'Credit Card required.',
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails())
        {
            return redirect()->back()->withInput()->with('error',implode('\n',$validator->errors()->all()));
        }else{

            $planid   = Crypt::decrypt($request->plan_id);
            $creditid = $request->credit_card; 
            $userid   = Crypt::decrypt($request->user_id);
            $default_credit = UserCreditCard::where('user_id', $userid)
                            ->where('id', $creditid)->first();

            $cardexpiry = $default_credit->card_expiry;
 
            if(Carbon::parse($cardexpiry)->lt(Carbon::now())){
                return redirect()->back()->with('error','Card validity expired');
            }

            $currday     = Carbon::now()->format('Y-m-d');
            $paymentonce = UserPayment::select('id')
                           ->where('user_id',$userid)
                           ->where('status',1)
                           ->whereDate('created_at',$currday)->first();

            if(isset($paymentonce)){
                return redirect()->back()->with('error','Payment Deducted Once. Please contact Support');
            }

                $user = User::select('users.id','name','i_account','currency_symbol',
                        'tax','currency','short_code','email')
                        ->join('country','country.id','=','country_id')
                        ->where('users.id', $userid)->first();

                $plans = DB::table('tbl_plans')->where('id', $planid)->first();
                $tax            = Helper::get_option('country_tax');
                $total_amount   = $plans->sell_price;
                $tax_amount     = ($total_amount * $tax)/100;

                $referenceid    = $default_credit->transaction_id;

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

                $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$total_amount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

                $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 

                //$response = ['ACK'=>'Failed','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
                // $response = ['ACK'=>'SUCCESS','TRANSACTIONID'=>'123456','L_ERRORCODE0'=>'15005','L_SHORTMESSAGE0'=>'Processor Decline','L_LONGMESSAGE0'=>'This transaction cannot be processed.'];
                $rsponse_status = strtoupper($response["ACK"]);
                if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {

                   $txn_id = $response['TRANSACTIONID'];

                   DB::table('user_credit_cards')->where('id', $creditid)->update(array('transaction_id' => $txn_id));

                    $payment = ['transaction_id' => $txn_id, 'total_amount' => $total_amount, 'payment_method' => 'Paypal', 'payment_for' => 'International Plan Subscription', 'user_id' => $userid, 'amount' => $plans->sell_price, 'tax_amount' => $tax_amount, 'description' => 'International Plan Subscription', 'status' => '1', 'category' => 'sim', 'buy_price' => $plans->buy_price]; 

                    $paymentid = UserPayment::insertGetId($payment);

                    $userdata = User::find($userid);
                    $subscription_id = $userdata->userDetail->sim_subscription_id;                                    
                    $msisdn = $userdata->msisdn->phone_number;
                    $customer_id    = Helper::get_option('mvno_customer_id');
                    $mvno_key       = Helper::get_option('bundle_mvno_key');
                    
                    $simbillplan   = $plans->sim_billing_plan;

                    $end_point = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim
                    $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$simbillplan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];

                    $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                    // $response = json_decode('{"CustomerOrderId":"1050091279","Subscription":{"CustomerId":"1050077251","SubscriptionId":"1050077253","AccountId":"1065000000000000747","StartDate":"2019-08-15T15:48:33.3795171+02:00","EndDate":{},"Status":"Active","StatusReason":{},"Promotions":{},"Services":[{"ServiceId":"1050063957","Category":"CORESERVERCHARGING","Status":"active","ExternalId":{},"StartDate":"2019-08-15T15:48:32.9435134+02:00","EndDate":{},"ServiceCharacteristics":[null]}],"Products":[{"ProductId":"1003000280","ProductOfferingId":"1019000245","SubscriptionProductAssnId":"1050000000000006675","ProductChargePurchaseId":"1006000221","ProductCharacteristics":[null],"StartDate":"2019-08-15T15:48:32.9454952+02:00","EndDate":{},"RecurringAmount":{"Amount":"0","Currency":"GBP"},"NonRecurringAmount":{"Amount":"0","Currency":"GBP"},"UnbilledBalanceAmount":{"Amount":"0","Currency":"GBP"}}],"DeliveryAddress":{"Address":"unknown","HouseExtension":{},"HouseNo":"unknown","City":"unknown","ZipCode":"unknown","State":"unknown","CountryId":"76","ExternalAddressId":{}}},"orderCode":"6725390130994741271","resultType":"Ok","resultCode":"0","messages":["Order submitted"]}');

                    if($response->resultType == 'Ok' && $response->resultCode == 0){                                    
                        $log_data = array(
                            'user_id' => $userid,
                            'stock_id' => $userdata->stock_id,
                            'msisdn' => $msisdn,
                            'category' => 'CREATION',
                            'plan_id' => $planid,
                            'staff_id' => Auth::id(),
                            'reference_id' => $response->orderCode
                        );
                        $res = DB::table('avoo_sim_log')->insert($log_data);

                        return redirect()->back()->with('message','Payment Success. Plan Subscriped');
                    }else{

                        $notification = NotificationLog::create(['user_id'=>$userid,'message' => 'International Plan Subscription Payment Success,Update to SIM Failed','description'=>'Sub ID:'.$planid.',TRANSACTIONID: '.$txn_id.', admin:'.Auth::id(),'status'=>'0']);
                        $obj = (object) array(
                            'notify_id' => $notification->id,
                            'subscripton'=> $planid,
                        );  
                        FailureNotification::dispatch($obj)
                            ->delay(now()->addMinutes(1)); 
                        return redirect()->back()->with('error','Payment Success. Plan Subscription Update to SIM Failed. Please contact Technical team..');
                    }

                }else{

                    $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0']]);

                    $payment = ['transaction_id' => '', 'total_amount' => $total_amount, 'payment_method' => 'Paypal', 'payment_for' => 'International Plan Subscription', 'user_id' => $userid, 'amount' => $plans->sell_price, 'tax_amount' => $tax_amount, 'description' => 'International Plan Subscription,msg:'.$pay_error.')', 'status' => '0', 'buy_price' => $plans->buy_price];

                    $paymentid = UserPayment::insertGetId($payment);

                    $notification = NotificationLog::create(['user_id'=>$userid,'message' => 'International Plan Subscription Payment Failed','description'=>'Sub ID:'.$plans->id.', msg:'.$pay_error.', admin:'.Auth::id(),'status'=>'0']);
                    $obj = (object) array(
                        'notify_id' => $notification->id,
                        'subscripton'=> $plans->id,
                    );  
                    FailureNotification::dispatch($obj)
                        ->delay(now()->addMinutes(1));                    
                    return redirect()->back()->with('error','Payment Failed. Please try again..');

                }
        }

    }
    
    /**
    * Add Child Member
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_child_user($id)
    {
        $parent_id = ($id) ? Crypt::decrypt($id) : 0;        
        return view('user.create', compact('parent_id'));        
    }

    /**
    * Add Child Member
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_child_user(Request $request)
    {
        $dialcode = Helper::get_option('dial_code');
        $password = Helper::unique_code(8);
        $phone = $dialcode.ltrim($request->phone,'0');
        $country = Country::whereId(238)->first();

        $user = TempUser::create([
            'first_name' => ucfirst($request->first_name),
            'last_name' => ucfirst($request->last_name),
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($password),                        
            'country_id' => 238,
            'parent_id' => $request->parent_id,            
            'status' => 0
        ]);
        $parent = Crypt::encrypt($request->parent_id);
        return redirect('/show-user/'.$parent);
    }
    
    /**
    * Add Child Member
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function split_child_user(Request $request)
    {
        // $user_id = $request->user_id;
        // $user = User::find($user_id);
        // $user
        // User::where('id', $user_id)->update(['parent_id' => 0]);
        // //AutoPlan::where('id', $user_id)->update($autoplan);

        // user_list
    }

    public function reset_otp_try(Request $request)
    {
        $phone = $request->phone;
        $user_id = $request->user_id;
        DB::table('otp')->where('phone',$phone)->orWhere('user_id',$user_id)->decrement('no_try');
        return;
    }

    /**
    * Add Child Member
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function download_usage($user_id)
    {
        $user_id = Crypt::decrypt($user_id);
        $users = User::whereNotNull('i_account')->where('id',$user_id)->get(); //
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
                    $usage['activated'] = $activated;
                    $usage['usage'] = $data_usage;
                    $usage['call_duration'] = round($call_duration/60);                    
                    //net=100/(100+vatRate)*input;
                    //vat=input-net;                    
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
                $usage['activated'] = $activated;
                $usage['usage'] = '';
                $usage['call_duration'] = round($call_duration/60);                
                $usage['extra'] ='';
                $data[$i] = $usage;
                $i++;
            }   
        } 
        // print_R($data);

        array_unshift($data,array('ID','Name','Phone','Parent/Child','Plan', 'Activated','Data Usage','Call Duration','Extra'));
        return Excel::download(new CustomExport($data), 'usage.csv');
    }

    /**
    * Resend Password
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function resend_password(Request $request)
    {           
        $user_id = $request->user_id; 
        $user = User::find($user_id);
        if($user){
            $password = Helper::unique_code(8);
            $hash = Hash::make($password);   
            User::where('id', $user_id)->update(['password'=> $hash]);            
            
            $obj = new \stdClass();
            $obj->name = ($user->name)?:'User';
            $obj->subject = 'Welcome to AVOOMobile';
            $obj->heading  = 'Welcome To AVOOMobile';
            $obj->email = $user->email;
            $obj->username = $user->username;
            $obj->password = $password;

            if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){
                if(is_null($user->email_verified_at)){
                    $verify_token = sha1(time());
                    VerifyUser::updateOrCreate(
                        ['email' => $user->email],
                        ['token' => $verify_token]
                    );
                    $obj->token = $verify_token; 
                }
                
                $bcc_emails = Helper::get_option('bcc_emails');
                $bcc_emails = explode(',', $bcc_emails);
                Mail::to($user->email)
                    ->bcc($bcc_emails)
                    ->send(new Registration($obj));
            }
        }
        return 1;
    }

    /**
    * Resend Password
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function send_payment_link(Request $request)
    {           
        $user_id = $request->user_id; 
        $user = User::find($user_id);
        if($user){
            $obj = new \stdClass();
            $obj->name = ($user->name)?:'User';
            $obj->link = 'verify-card/'.Crypt::encrypt($user_id); 
            $obj->subject = 'AVOOMobile Payment Link';    
            $obj->heading = 'Self Payment Link';
            $obj->content = 'Please find below the link to verify your card';

            if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){
                Mail::to('jijojoseph001@gmail.com') //$user->email
                    ->bcc('jijo.joseph@gencomtel.com')
                    ->send(new UserSelfPaymentLink($obj));
            }
        }
        return 1;
    }

    /**
    * Custom Debit
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_custom_debit(Request $request)
    {   
        $card_id = Crypt::decrypt($request->credit_card);        
        $user_id = $request->user_id;        
        $total_amount = $request->debit_amount;
        $description = $request->debit_description; 
        $credit_card = UserCreditCard::where('user_id', $user_id)
                            ->where('id', $card_id)->first();

        $cardexpiry = $credit_card->card_expiry;

        if(Carbon::parse($cardexpiry)->lt(Carbon::now())){
            $response['status'] = '0';
            $response['message'] = 'Card validity expired';
            return $response;
        }

        $user = User::select('users.id','name','i_account','currency_symbol',
                        'tax','currency','short_code','email')
                        ->join('country','country.id','=','country_id')
                        ->where('users.id', $user_id)->first();

        $net_amount = 100/(100+$user->tax) * $total_amount;
        $vat_amount = $total_amount - $net_amount;                
        $net_amount = number_format($net_amount,2,'.','');
        $vat_amount = number_format($vat_amount,2,'.','');
        $total_amount = number_format($total_amount,2,'.','');
        $currency       = $user->currency;
        $i_account      = $user->i_account;
        $referenceid    = $credit_card->transaction_id;
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

        $nvp_req = "METHOD=$method_name&VERSION=$version&PWD=$api_password&USER=$api_user&SIGNATURE=$api_signature&PAYMENTACTION=$payment_type&AMT=$total_amount&REFERENCEID=$referenceid&CURRENCYCODE=$currency&CUSTOM=$i_account";

        $response = Helper::call_nvp_payment($api_endpoint, $nvp_req); 

        $rsponse_status = strtoupper($response["ACK"]);
        if($rsponse_status == 'SUCCESS'|| $rsponse_status == 'SUCCESSWITHWARNING') {
            $txn_id = $response['TRANSACTIONID'];
            DB::table('user_credit_cards')->where('id', $card_id)->update(['transaction_id' => $txn_id]);

            $payment = ['transaction_id' => $txn_id, 'total_amount' => $total_amount, 'payment_method' => 'Paypal', 'payment_for' => 'Service Charge', 'user_id' => $user_id, 'amount' => $net_amount, 'tax_amount' => $vat_amount, 'description' => $description.' by '.Auth::user()->first_name.' '.Auth::user()->last_name, 'status' => '1', 'buy_price' => 0]; 

            $paymentid = UserPayment::insertGetId($payment);
            $response['status'] = '1';
            $response['message'] = 'Payment Success!...';
            return $response;
        }else{
            $pay_error = json_encode(['code'=>$response['L_ERRORCODE0'],'smsg'=>$response['L_SHORTMESSAGE0'],'lmsg'=>$response['L_LONGMESSAGE0'].','.$description]);

            $payment = ['transaction_id' => '', 'total_amount' => $total_amount, 'payment_method' => 'Paypal', 'payment_for' => 'Service Charge', 'user_id' => $user_id, 'amount' => $net_amount, 'tax_amount' => $vat_amount, 'description' => 'Service Charge,msg:'.$pay_error.')', 'status' => '0', 'buy_price' => 0];

            $paymentid = UserPayment::insertGetId($payment);

            $notification = NotificationLog::create(['user_id'=>$user_id,'message' => 'Service Charging Failed','description'=> 'msg:'.$pay_error.', admin:'.Auth::id(),'status'=>'0']);

            $response['status'] = '0';
            $response['message'] = 'Payment Failed. Please try again..';
            return $response;
        }
    }

    /**
    * Switch Plan Renewal
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function renew_switch_plan(Request $request)
    {           
        $user_id = $request->user_id; 
        $switch_id = $request->switch_id;
        $switch_plan = DB::table('switch_plans')->where('id', $switch_id)->where('status',1)->first();
        $user = User::select('name','first_name','email','phone','i_account','currency','currency_symbol')
                ->join('country','country_id','=','country.id')->where('users.id', $user_id)
                ->where('users.status', '1')->first(); 

        if($user && $switch_plan){
            $i_account = $user->i_account;
            $currency = $user->currency;
            $defult_plan = DB::table('switch_template')->where('id', $user->switch_id)->value('billing_plan');
            $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
            $temp     = SwitchHelper::call_switch_api($xml_data);

            $method = 'accountCredit';
            $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, $switch_plan->buy_price, $currency); 
            $temp =  SwitchHelper::call_switch_api($credit_xml);
            if (array_key_exists("fault", $temp)) {
                $response['status'] = '0';
                $response['message'] = 'Plan renewal failed, please try again!..';
                return $response;
            }

            $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $switch_plan->switch_billing_plan);
            $temp     = SwitchHelper::call_switch_api($xml_data);

            if (array_key_exists("fault", $temp)) {
                $response['status'] = '0';
                $response['message'] = 'Plan renewal failed, please try again!..';
                return $response;
            }else{
                $balance['balance_minutes'] = $switch_plan->minutes;
                Account::where('user_id', $user_id)->update($balance);  
                $response['status'] = '1';
                $response['message'] = 'Plan renewed successfully!..';
                return $response;                 
            }
        }else{
            $response['status'] = '0';
            $response['message'] = 'Renewal failed, Invalid data!..';
            return $response;
        }
    }
}
