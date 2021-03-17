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
use DwpHelper;
use DataTables;
use SwitchHelper;
use GlobalSim;
use Log;
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
use App\Models\PaymentGateway;
use App\Models\UserCreditCard;
use App\Models\UserPayment;
use App\Mail\PaymentStatus;
use App\Mail\Registration;
use App\Models\TblPorting;
use App\Models\PaymentCommission;
use App\Models\SwitchLog;
use App\Models\NotificationLog;
use App\Models\Credit;
use App\Models\UserBank;
use App\Models\OptedService;
use App\Models\UserInvoice;

use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Exports\CustomExport;
use App\Jobs\FailureNotification;
use App\Mail\UserSelfPaymentLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Models\ClawbackPayments;
use \App\Models\Clawback;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

class UserController extends Controller
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
        if (!Helper::has_permission('users') && !Helper::has_permission('users','view_own')) {
            abort(403,'Access denied');
        }
        
        return view('user.users');
    }

    /**
    * Show the user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_users(Request $request)
    {          
        $admin_id =  Auth::user()->id;     
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('id')->toArray();        
        array_push($where, $admin_id);
        $users_qry = User::query()->select(['users.id','name','phone','email','users.created_at','ud.user_platform','users.status'])->join('user_data as ud','users.id','=','user_id');

        $total_user = User::all();
        $active_user = User::where('status', 1);
        $inactive_user = User::where('status', 0);
        $weekly_usr = User::where('created_at','>',date('Y-m-d 00:00:01', strtotime('-'.date('w').' days')));
        if(isset($request->order)){
            $orderBy = $request->columns[$request->order[0]['column']]['name'];
            $dir = $request->order[0]['dir'];           
        }
        if (Helper::has_permission('users')){
            $total = $total_user->count();
            $active = $active_user->count();
            $inactive = $inactive_user->count();
            $weekly = $weekly_usr->count();
            $users = $users_qry->orderBy($orderBy,$dir);
        }elseif(Helper::has_permission('users','view_own')) {
            $total = $total_user->whereIn('dealer_id', $where)->count();
            $active = $active_user->whereIn('dealer_id', $where)->count();
            $inactive = $inactive_user->whereIn('dealer_id', $where)->count();
            $weekly = $weekly_usr->whereIn('dealer_id', $where)->count();
            $users = $users_qry->whereIn('dealer_id', $where)->orderBy($orderBy,$dir);
        }else{
            $total = $active = $inactive = $weekly = 0;
            $users = $users_qry->where('users.id',0);
        }

        return Datatables::eloquent($users)
                ->editColumn('user_platform', function (User $user) {
                    return $user->userDetail->user_platform;
                }) 
                ->addColumn('action', function (User $user) {
                    $parameter= Crypt::encrypt($user->id);
                    $html = '';
                    if (Helper::has_permission('users','edit')) {
                    $html .='<a data-toggle="tooltip" title="Login" href="https://geokallmobile.co.uk/admin-authenticate/'.$parameter.'" target="_blank"><i class="mdi mdi-login mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a title="View Details" class="show_user_data text-muted" user-id="'.$user->id.'"><i class="mdi mdi-eye mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    if(Helper::has_permission('users','delete')) {   
                    $html .='<a href="javascript:void(0);" class="text-danger delete_user"  user-id="'.$user->id.'" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="mdi mdi-delete mdi-24px"></i></a><form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                    }
                    if (Helper::has_permission('users','edit')) {
                        $html .='<form id="show_user_'.$user->id.'" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$user->phone.'"></form>';
                    }

                    return $html;
                })
                ->with(['total'=>$total,'active'=>$active,'inactive'=>$inactive,'weekly' => $weekly])
            ->make(true);
    }

    /**
    * Show the user overview.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_detail(Request $request)
    {  
        if (!Helper::has_permission('user_details')) {
            abort(403,'Access denied');
        }
        $user = '';
        $search = $notes = [];
        if($request->identifier){
            $search['identifier'] = $request->identifier;
            $user = User::where('email', $request->identifier)
                        ->orWhere('phone', 'like', '%'.ltrim($request->identifier, '0'))->first();
        }else if($request->order_id){
            $search['order_id'] = $request->order_id;
            $order = SimRequest::where('order_id', $request->order_id)->first();
            if($order)
                $user = User::where('id', $order->user_id)->first(); 
        }else if($request->customer_id){
            $search['customer_id'] = $request->customer_id;
            $user_id = filter_var($request->customer_id, FILTER_SANITIZE_NUMBER_INT);
            $platform = preg_replace('/[0-9]/', '', $request->customer_id);
            $user = User::where('id', $user_id)->first();
            if($user && $user->userDetail->user_platform != strtoupper($platform)){
               $user = ''; 
            }
        }else if($request->user_name){
            $search['user_name'] = $request->user_name;
            $user_data = UserData::where('auth_name', $request->user_name)->first();                        
            if($user_data){
               $user = User::where('id', $user_data->user_id)->first();
            }
        }

        if($user){
            $reports = [];            
            if(Helper::has_permission('report')){
                $half_year = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->submonths(6))->where('status', 1)->get();
                $half_return = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->submonths(6))->where('status', 3)->sum('total_amount');
                $yearly = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYear())->where('status', 1)->get();
                $yearly_return = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYear())->where('status', 3)->sum('total_amount');
                $last_two  = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYears(2))->where('status', 1)->get();
                $last_two_return  = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYears(2))->where('status', 3)->sum('total_amount');
                $half_total = $half_year->sum('total_amount'); $half_expence = $half_year->sum('buy_price'); $half_vat = $half_year->sum('tax_amount');
                $half_fee = 0; $half_profit = ($half_total - $half_expence - $half_vat - $half_return);
                $yearly_total = $yearly->sum('total_amount'); $yearly_expence = $yearly->sum('buy_price'); $yearly_vat = $yearly->sum('tax_amount');
                $yearly_fee = 0; $yearly_profit = ($yearly_total - $yearly_expence - $yearly_vat - $yearly_return);
                $last_two_total = $last_two->sum('total_amount'); $last_two_expence = $last_two->sum('buy_price'); $last_two_vat = $last_two->sum('tax_amount');
                $last_two_fee = 0; $last_two_profit = ($last_two_total - $last_two_expence - $last_two_vat - $last_two_return - $last_two_fee);
                $reports = ['6' => ['revenue' => $half_total, 'expense'=> $half_expence, 'refund'=> $half_return, 'fee' => $half_fee, 'vat'=> $half_vat, 'profit' => $half_profit],
                    '12'=>['revenue' => $yearly_total, 'expense'=> $yearly_expence, 'refund'=> $yearly_return, 'fee' => $yearly_fee, 'vat'=> $yearly_vat, 'profit' => $yearly_profit],
                    '24'=>['revenue' => $last_two_total, 'expense'=> $last_two_expence, 'refund'=> $last_two_return, 'fee' => $last_two_fee, 'vat'=> $last_two_vat, 'profit' => $last_two_profit]];
            }
            $currency = $user->country->currency_symbol;
            $notes = DB::table('enquiry_history')->where('user_id', $user->id)->get();
            $plan = UserPlan::where('user_id', $user->id)->where('plan_type', 'sim')->where('status', 1)->first();
            $children = User::where('parent_id',$user->id)->get();
            $parent = User::where('id',$user->parent_id)->first();
            $parent = ($parent)?:$user;
            return view('user.user-details', compact('user','currency','parent','children','plan','reports','search','notes'));
        }
        return view('user.user-details', compact('user','search'));
    }

    /**
    * Show the user detail view.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_data(Request $request)
    {  
        if (!Helper::has_permission('user_details')) {
            abort(403,'Access denied');
        }

        $user_id = Crypt::decrypt($request->user_id);
        $user = User::where('id', $user_id)->first();
        if($user){
            $currency = $user->country->currency_symbol;
            switch ($request->page) {
                case 'overview':
                    $reports = [];
                    if(Helper::has_permission('report')){
                        $half_year = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->submonths(6))->where('status', 1)->get();
                        $half_return = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->submonths(6))->where('status', 3)->sum('total_amount');
                        $yearly = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYear())->where('status', 1)->get();
                        $yearly_return = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYear())->where('status', 3)->sum('total_amount');
                        $last_two  = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYears(2))->where('status', 1)->get();
                        $last_two_return  = UserPayment::where('user_id', $user->id)->whereDate('created_at', '>=', Carbon::now()->subYears(2))->where('status', 3)->sum('total_amount');
                        $half_total = $half_year->sum('total_amount'); $half_expence = $half_year->sum('buy_price'); $half_vat = $half_year->sum('tax_amount');
                        $half_fee = 0; $half_profit = ($half_total - $half_expence - $half_vat - $half_return);
                        $yearly_total = $yearly->sum('total_amount'); $yearly_expence = $yearly->sum('buy_price'); $yearly_vat = $yearly->sum('tax_amount');
                        $yearly_fee = 0; $yearly_profit = ($yearly_total - $yearly_expence - $yearly_vat - $yearly_return);
                        $last_two_total = $last_two->sum('total_amount'); $last_two_expence = $last_two->sum('buy_price'); $last_two_vat = $last_two->sum('tax_amount');
                        $last_two_fee = 0; $last_two_profit = ($last_two_total - $last_two_expence - $last_two_vat - $last_two_return - $last_two_fee);
                        $reports = ['6' => ['revenue' => $half_total, 'expense'=> $half_expence, 'refund'=> $half_return, 'fee' => $half_fee, 'vat'=> $half_vat, 'profit' => $half_profit],
                            '12'=>['revenue' => $yearly_total, 'expense'=> $yearly_expence, 'refund'=> $yearly_return, 'fee' => $yearly_fee, 'vat'=> $yearly_vat, 'profit' => $yearly_profit],
                            '24'=>['revenue' => $last_two_total, 'expense'=> $last_two_expence, 'refund'=> $last_two_return, 'fee' => $last_two_fee, 'vat'=> $last_two_vat, 'profit' => $last_two_profit]];
                    }

                    $notes = DB::table('enquiry_history')->where('user_id', $user->id)->get();
                    $plan = UserPlan::where('user_id', $user->id)->where('plan_type', 'sim')->where('status', 1)->first();
                    $children = User::where('parent_id',$user->id)->get();
                    $parent = User::where('id',$user->parent_id)->first();
                    $parent = ($parent)?:$user;
                    $html = view('user.overview', compact('user','currency','parent','children','plan','reports','notes'))->render();
                break;
                case 'basic_details':
                    $otp = DB::table('otp')->where('user_id', $user->id)->orderby('created_at', 'desc')->first();
                    $html = view('user.basic-details', compact('user','otp','currency'))->render();
                break;
                case 'auto_subscription':
                    $plans = AutoPlan::where('user_id', $user->id)->where('status', 1)->get();
                    $disabled_plans = AutoPlan::where('user_id', $user->id)->where('status', 0)->get();
                    $html = view('user.subscription', compact('user','plans','disabled_plans','currency'))->render();
                break; 
                case 'credit_debit':
                    $gateways = PaymentGateway::where('status', 1)->get();
                    foreach($gateways as $gateway){
                        $gateway->cards = UserCreditCard::where(['user_id' => $user->id,'gateway'=>$gateway->id])->get();
                    }
                    $credit_amount = Credit::where('switch_id', $user->switch_id)->get();
                    $html = view('user.credit', compact('user','gateways','credit_amount'))->render();
                break; 
                case 'plan_history':
                    $plans = UserPlan::where('user_id', $user->id)->orderby('created_at', 'desc')->get();
                    // $html = view('user.plans', compact('user','plans','currency'))->render();
                    // $plans = AutoPlan::where('user_id', $user->id)->get();
                    $html = view('user.plans', compact('user','plans','currency'))->render();
                break; 
                case 'call_history':
                    $user_plan = UserPlan::where('user_id', $user->id)->orderBy('created_at','desc')->first();
                    $start_date = ($user_plan)?$user_plan->created_at: Carbon::now()->subDays(30);
                    $calls = DB::table('user_calls')->select('id','connect_date','cli','cld','cost','duration','history_from')->where('user_id', $user->id)
                                ->whereBetween('connect_date', array(date('Y-m-d',strtotime($start_date)).' 00:00:01', date('Y-m-d').' 23:59:59'))
                                ->orderBy('connect_date','desc')->get();
                    $html = view('user.calls', compact('user','calls','currency'))->render();
                break; 
                case 'transaction':
                    $payments = DB::table('user_payments')->whereIn('user_id', [$user->id, $user->parent_id])->orderBy('created_at','desc')->get();
                    $html = view('user.transaction', compact('user','payments','currency'))->render();
                break; 
                case 'settings':
                    $html = view('user.settings', compact('user'))->render();
                break;
                case 'services':
                    $simstatus = 0;  $html= '';
                    $bars = (!is_null($user->stock_id)) ? DB::table('provider_services')
                    ->where('short_code',$user->msisdn->provider)->where('status',1)->get() : collect([]);
                    $user_services = DB::table('user_services')->where('user_id',$user->id)->get()->keyBy('service_id')->toArray();
                    $recentlyopted = DB::table('opted_services')
                                    ->where('user_id',$user->id)
                                    ->where('status',1)
                                    ->where('updated_at','>=',Carbon::now()->subMinute(15)->format('Y-m-d H:i:s'))->get()->keyBy('service_id')->toArray();

                    if($bars->isNotEmpty()){
                        //$service_info = collect([]);
                        $provider     = $user->msisdn->provider;
                        $phone_number = ($provider == 'E_SIM') ? ltrim($user->msisdn->phone_number) : '0'.ltrim($user->msisdn->phone_number,$user->country->dial_code);
                        $service_info   = [];
                        $network_info   = [];

                        if(in_array($provider,['O2','EE_O2','VUK'])){
                            // $simdata['network']     = $user->msisdn->network->provider;
                            // $simdata['sim_number']  = $user->msisdn->sim_number;
                            // $sim_xml        = DwpHelper::dwp_check_sim_xml($simdata);
                            // $response       = DwpHelper::dwp_process_api($sim_xml);
                            // $response       = json_decode(DwpHelper::dwp_response_handler($response));
                            // if($response->children[0]->no == 0){
                            //     $simstatus  = $response->children[1]->html;
                            // }

                            $data['cli']    = $phone_number;//07766742689;
                            $bars_xml       = DwpHelper::dwp_check_mobile_bars_xml($data);
                            $response       = DwpHelper::dwp_process_api($bars_xml);
                            $response       = json_decode(DwpHelper::dwp_response_handler($response));

                            if($response->children[0]->no == 0){
                                $servicereq = $response->children[1]->children;

                                $service_info = DwpHelper::dwp_service_response($servicereq);

                                $data['active_bars'] = join(',',array_keys($service_info, 1));
                                $compact_xml  = DwpHelper::dwp_service_compact($data);
                                $response     = DwpHelper::dwp_process_api($compact_xml);
                                $response     = json_decode(DwpHelper::dwp_response_handler($response));

                                if($response->children[0]->no == 0){
                                    $compact   = [];
                                    if(isset($response->children[1]->children)){
                                        $activereq = $response->children[1]->children;
                                        foreach($activereq as $key => $list){
                                            array_push($compact,$list->children[0]->html);
                                        }
                                        foreach($service_info as $key => $alist){
                                            if($alist == 1)
                                            array_push($compact,$key);
                                        }
                                        $service_info = array_intersect_key($service_info, array_flip($compact));
                                    }
                                }
                            }

                            $services_xml   = DwpHelper::dwp_check_mobile_service_xml($data);
                            $response       = DwpHelper::dwp_process_api($services_xml);
                            $response       = json_decode(DwpHelper::dwp_response_handler($response));

                            if($response->children[0]->no == 0){

                                $simstatus  = 1;
                                $servicereq = $response->children[1]->children;
                                $network_info = DwpHelper::dwp_service_response($servicereq);
                            }
                            
                            $html = view('user.services', compact('provider','user','bars','service_info','simstatus','network_info','user_services','recentlyopted'))->render();
                        }elseif(in_array($provider,['E_SIM'])){
                            try {
                                $network_info = GlobalSim::GetSimStatus($phone_number);
                                if($network_info == false || $network_info['@attributes']['status'] == 'fail'){
                                    Log::error('GetSimStatus',[
                                        'user_id' => $user->id,
                                        'error' =>   $network_info
                                    ]);
                                    return response()->json(['error' => true, 'message' => 'sim services request failed']);
                                }
                                if($network_info['SimStatus'] == 'Enabled'){
                                    $simstatus  = 1;
                                }
                                $html = view('user.services', compact('provider','user','bars','service_info','simstatus','network_info','user_services','recentlyopted'))->render(); 
                            } catch (\Exception $e) {
                                Log::error('GetSimStatus',[
                                    'user_id' => $user->id,
                                    'error' =>   $e->getMessage()
                                ]);
                                return response()->json(['error' => true, 'message' => 'Fetching sim services failed'.$e->getMessage()]);
                            }
                        }
                    }  
                break;          
                case 'invoice':
                    $invoice = UserInvoice::where('user_id',$user->id)->orderBy('date','DESC')->get();
                    $html    = view('user.invoice', compact('user','invoice'))->render();
                break;
                default:
                    $html = 'error';
            }  
        } 

        return response()->json(['html' => $html]);
    }


     /**
    * Show the user detail view.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_user(Request $request)
    {
        if (!Helper::has_permission('users', 'edit') || !Helper::has_permission('user_details', 'edit')) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        $id = Crypt::decrypt($request->user_id);
        $user = User::where('id',$id)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid User']);
        }
        $user->first_name = ucfirst(strtolower($request->first_name));
        $user->last_name = ucfirst(strtolower($request->last_name));
        $user->email = strtolower($request->email);
        $user->name = $user->first_name.' '.$user->last_name;
        $user->alt_phone = $request->alt_phone;
        $userData = UserData::where('user_id', $id)->first();
        $userData->business_name = $request->business_name;
        $userData->house_no = $request->house_number;
        $userData->address = $request->address;
        $userData->city = $request->city;
        $userData->state = $request->state;
        $userData->postal_code = $request->postal_code;
        $billing_address = ['first_name' => $user->first_name, 'last_name' => $user->last_name, 'postal_code' => $request->postal_code, 'street' => $request->address, 'city' => $request->city, 'country' => $request->state];
        $userData->billing_address =  json_encode($billing_address);
        DB::beginTransaction(); 
        try { 
            $user->save();
            $userData->save(); 
            DB::commit(); 
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to update the user details']);
        }
        return response()->json(['success' => true, 'message' => 'User details updated successfully']); 
    }
    /**
    * Show the In Complete sign up.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function temp_users(Request $request)
    {   
        if (!Helper::has_permission('users')) {
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
        if (!Helper::has_permission('users')) {
            abort(403,'Access denied');
        }           
        $users = TempUser::query()->select(['temp_users.id','phone','c.country_name','created_at'])
                    ->join('country as c','country_id','=','c.id')->orderBy('temp_users.id','desc');
        
        return  Datatables::eloquent($users)                           
                ->addColumn('action', function (TempUser $user) {
                    $parameter= Crypt::encrypt($user->id);
                    $html = '';
                    if(Helper::has_permission('users','delete')) {   
                        $html .='<a data-toggle="tooltip" title="" href="javascript:void(0);" class="delete_user text-danger" user-id="'.$user->id.'"><i class="mdi mdi-delete mdi-24px"></i></a> <form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-temp-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                    }
                    return $html;
                })->make(true);
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
    * Show the blocked user list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_fraudsters()
    {   
        if (!Helper::has_permission('fraudsters')) {
            abort(403,'Access denied');
        } 
        $fraudsters = DB::table('fraudsters')->select('fraudsters.*','u.name','u.email','u.phone')
                        ->join('users as u','user_id','=','u.id')->get();
        return view('user.fraudsters-list', compact('fraudsters'));
    }

    /*
    * Check Customer   
    * return status
    */
    public function search_user(Request $request)
    {        
        $country = Country::where('id', $request->country)->first();
        if (substr($request->phone, 0, 2) == preg_replace('/[^0-9]/', '', $country->dial_code)) {
            $phone = '+'.$request->phone;
        } else if (substr($request->phone, 0, 3) == $country->dial_code) {
            $phone = $request->phone;
        } else if (substr($request->phone, 0, 1) == '0') {
            $phone = $country->dial_code.substr($request->phone, 1);
        } else {
            $phone = $country->dial_code.$request->phone;
        }        

        $user = User::where('phone', $phone);
        if(filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            $user = $user->orWhere('email', $request->email);
        }
        $user = $user->first();
        
        if($user){
            // $sendotp = $this->send_otp($user->id,$user->country_id,$user->phone);
            // if($sendotp['success']){
            //     $response['reference'] = $user->id;
            //     $response['phone_number'] = $user->phone;
            //     $response['status'] = 1;
            // }else{
            //     $response['status'] = 2;
            //     $response['message'] = $sendotp['message'];
            // }
            $response['html'] = view('orders.billing-address',compact('user'))->render();
            $response['user_exist'] = 1;
            $response['email'] = $request->email;
            $response['phone'] = $phone;
        }else{
            $response['status']   = 0;
            $response['user_exist'] = 0;
        }

        return response()->json($response);
    }

    /*
    * Save Call Enquiry
    */
    public function save_note(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        $handledBy = Auth::user()->first_name.' '.Auth::user()->last_name;
        $response = DB::table('enquiry_history')->insert(['user_id' => $user_id, 'sim_req_id' => 0, 'handled_user_id' => Auth::id(),'handled_by' => $handledBy,'note' => $request->note]);
        if ($response) {
            return response()->json(['success' =>  true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to save the note']);
        }
    }

    public function change_user_status(Request $request) {

        DB::beginTransaction();
        try {
            $user_id = Crypt::decrypt($request->user_id);
            $user = User::where('id',$user_id)->first();
            if (!$user) {
               return response()->json(['success' => false, 'message' => 'Invalid user details given!']); 
            }
            $method = ($user->status == 0) ? 'unblockAccount' : 'blockAccount';
            $status = ($user->status == 0) ? 'Un Blocking' : 'Blocking';
            $user->status = ($user->status == 0) ? 1 : 0;
            $user->save();
            if(!is_null($user->i_account)){                
                $xml_data = SwitchHelper::switch_account_status_xml($method, $user->i_account);
                $temp =  SwitchHelper::call_switch_api($xml_data);
                if (array_key_exists("fault", $temp)) {
                    $notify = NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Switch '.$status,'description'=>'msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']);
                    $obj = (object) array(
                        'notify_id' => $notify->id,
                        'subscripton'=> 0,
                    );  
                    FailureNotification::dispatch($obj)
                        ->delay(now()->addMinutes(1)); 
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => $notify->message]); 
                } 
            }

            $sim_list = SimList::where('user_id',$user_id)->first();
            if($sim_list){
                $autoplanid = $sim_list->autoplan_id;

                /* disable &enable entry of user whose payment not given in commission */
                $c = $this->user_status_trigger($user_id,$autoplanid,$user->status);
            }

            DB::commit();
            return response()->json(['success' => true, 'status' => $user->status, 'message' => 'User status updated successfully']);  
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage().'Failed to update the status']);
        }
    }

    /**
    * Manage Subscription Status.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function manage_subscription(Request $request)
    {
        $subscription = AutoPlan::where('id', $request->renew_id)->first();
        if($subscription){
            if($subscription->status){
                $amount = Helper::number_format(($subscription->total_amount*80)/100);
                $disable_on = Carbon::parse($subscription->next_renewal)->addDays(29)->format('Y-m-d');
                if($subscription->plan_type == 'sim' && $subscription->plan->network->service_type == 2){
                    $disable_on = Carbon::now()->addDays(30)->format('Y-m-d');
                }
                $options['1'] = 'Disable Now ( '.$amount.' will be deducted )';
                $options['2'] = 'After Contract Period (30 Days) (Disable on '. $disable_on .')';
                if(Auth::user()->role == 1){
                    $options['3'] = "Subscription cancel after current service. (Disable on ".Carbon::parse($subscription->next_renewal)->subDays(1)->format('Y-m-d').")";
                    $options['4'] = "Disable now without any payment!.";
                }
                $html = view('modal-popup', compact('subscription','options'))->render();
                return response()->json(['success' => true, 'status' => 1, 'html' =>  $html]);
            }else{
                if($subscription->plan_type == 'sim'){
                    return response()->json(['success' => false, 'message' => 'Couldn\'t update the status!']);
                }elseif($subscription->plan_type == 'switch' || $subscription->plan_type == 'bridge'){
                    $description = (!is_null($subscription->description))? json_decode($subscription->description):[];
                    $desc = ['proceed_by' => 'Renewal enabled by '.Auth::user()->first_name.' '.Auth::user()->last_name, 'proceed_at' => Carbon::now()->format('Y-m-d H:i:s')];
                    array_push($description, $desc);
                    $update = ['status' => 1, 'status_changeon' => NULL, 'description' => json_encode($description)];
                    if($subscription->next_renewal <= Carbon::now()->format('Y-m-d')){
                        $update['next_renewal'] = Carbon::tomorrow()->format('Y-m-d');
                    }                    
                    AutoPlan::where('id', $subscription->id)->update($update);
                    return response()->json(['success' => true,  'status' => 2, 'data' => $update, 'message' => 'Subscription status updated successfully!']);
                }
            }
        }
        return response()->json(['success' => false, 'message' => 'Couldn\'t update the status, invalid details given!']);
    }


    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function subscription_renewal_list(Request $request) 
    {
        $auto_plan = AutoPlan::where('id', $request->renew_id)->first();
        $user = User::find($auto_plan->user_id);
        $plans = [];
        $renewal_list = 1;
        if ($auto_plan->plan_type == 'sim') {
            if ($auto_plan->bundle_id == 0) {
                $provider = $auto_plan->plan->network->provider;
                $category = ['EE'=>[1],'O2'=>[2],'VUK'=>[3],'EE_O2'=>[1]];  //->where('status', 1)
                $plans = DB::table('tbl_plans')->where('provider', $provider)->get();
                            // ->whereIn('category', $category[$provider])->get();
            }else{
                $plans = DB::table('tbl_bundles')->where('sim_count', $auto_plan->getplan->sim_count)->whereIn('category', ['1','0'])->get();
            }
        }elseif($auto_plan->plan_type == 'bridge'){
            $plans = DB::table('conference_plans')->where('switch_id', $user->switch_id)
                        ->where('status', 1)->get();
        }elseif($auto_plan->plan_type == 'switch'){
            $plans = DB::table('plans')->where('switch_id', $user->switch_id)->get();
        }
        $currency = Helper::get_option('currency_symbol');        
        $html = view('modal-popup',compact('renewal_list','auto_plan','plans','currency'))->render();

        return response()->json(['success' => true, 'html' =>  $html]);
    }

    /**
    * Update auto recharge status
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_subscription_renewal(Request $request) 
    {        
        $curr_day = Carbon::now()->format('Y-m-d');
        $next_renewal = Carbon::now()->addDays(30)->format('Y-m-d');
        $plans = AutoPlan::where('id',$request->autoplan_id)->first();
        if($plans->plan_type == 'sim' && $plans->plan->network->service_type == 2){
            $next_renewal = Carbon::now()->addMonth()->firstOfMonth()->format('Y-m-d');
        }
        $user = User::find($plans->user_id);        
        $blocked = Helper::check_fraudster($plans->user_id); //check whether user is in fraud list
        if(!$user || $blocked){  //if in fraud or user doen't active
            return response()->json(['success' => false, 'message' => 'Account is in inactive list!']);
        }
        $tax = $user->country->tax;
        $i_account = $user->i_account; 
        $currency = $user->country->currency;       
        $plan_id = isset($request->plan_id)?$request->plan_id:0;
        $bundle_id = isset($request->bundle_id)?$request->bundle_id:0;

        if($plans->plan_type == 'sim'){
            $plan = DB::table('tbl_plans')->where('id', $plan_id)->first();
            if($bundle_id != 0){
                $bundle = DB::table('tbl_bundles')->where('id', $bundle_id)->first();
                $buy_price = $bundle->buy_price;
                $total_amount = $bundle->sell_price;
                $plan_id = $bundle->plan_id;
                $plan = DB::table('tbl_plans')->where('id', $plan_id)->first();
            }else{                
                $buy_price = $plan->buy_price;
                $total_amount = $plan->sell_price;
            }                        
            $i_billplan = $plans->switch_billing_plan;
            $in_call_limit = $plans->plan->in_call_limit;
            $sim_bill_plan= $plans->plan->sim_billing_plan;
        }elseif($plans->plan_type ='switch'){
            $plan = DB::table('plans')->where('id', $plan_id)->first();
            $i_billplan = $plan->switch_billing_plan;
            $in_call_limit = $plan->minutes;
            $buy_price = $plan->buy_price;
            $total_amount = $plan->sell_price;
        }elseif($plans->plan_type ='bridge'){
            $plan = DB::table('conference_plans')->where('id', $plan_id)->first();
            $i_billplan = $plan->switch_billing_plan;
            $in_call_limit = $plan->minutes;
            $buy_price = $plan->buy_price;
            $total_amount = $plan->sell_price;
        }
  
        if($plan_id != $plans->plan_id ||  $bundle_id != $plans->bundle_id){
            $tax_amount = ($total_amount * $tax)/100;
            $amount = ($total_amount - $tax_amount);

            $plans = AutoPlan::updateOrCreate(['id' => $request->autoplan_id], ['plan_id' => $plan_id, 'bundle_id' => $bundle_id, 'amount' => $amount, 'tax' => $tax_amount, 'total_amount' => $total_amount, 'switch_billing_plan' => $plan->switch_billing_plan, 'status' => 1]);
        }

        if($request->renewal_type == 1){                
            if($request->payment_mode == 1) {
                if($plans->card_expiry < $curr_day){
                    return response()->json(['success' => false, 'message' => 'Current card details expired, please update a new card!']);      
                } 
                $payment_flag = 0;
                $bill_amount = $plans->total_amount;
                $data['card_id'] = $plans->card_id;
                $data['user_id'] = $user->id;
                $data['i_account'] = $i_account;
                $data['currency'] = $currency;
                $net_amount = 100/(100+$tax) * $bill_amount;
                $vat_amount = $bill_amount - $net_amount;                
                $data['net_amount'] = Helper::number_format($net_amount);
                $data['vat_amount'] = Helper::number_format($vat_amount);
                $data['total_amount'] = Helper::number_format($bill_amount);
                $data['buy_price'] = Helper::number_format($buy_price);
                $data['discount_amount'] = 0;
                $data['discount_coupon'] = '';
                $data['payment_for'] = 'Plan Monthly Subscription';
                $data['description'] = 'Monthly Subscription (users: '.$plans->user_list.') - processed by '. Auth::user()->first_name.' '.Auth::user()->last_name;
                $data['category'] = $plans->plan_type;

                if($plans->gateway == 'Paypal'){            
                    $response = Helper::paypal_payment_process($data);
                }elseif($plans->gateway == 'Braintree'){
                    $response = Helper::braintree_payment_process($data);
                }
                
                if($response['status']){
                    $payment_flag = 1;
                    $txn_id = $response['transaction_id'];
                    $payment_id = $response['payment_id'];      
                    if($plans->gateway == 'Paypal'){
                        $auto_plan_data['transaction_id'] = $txn_id;
                        DB::table('user_credit_cards')->where('id', $plans->card_id)->update(['transaction_id' => $txn_id]);
                    }
                    // $auto_plan_data['next_renewal'] = $next_renewal;          
                    // AutoPlan::where('id', $plans->id)->update($auto_plan_data);    
                }else{
                    return response()->json(['success' => false, 'message' => $response['message']]);
                }
            }else if($request->payment_mode == 2){
                $payment_method = $request->payment_method;
                $payment = ['total_amount' => $plans->total_amount, 'currency' => $currency,  'payment_method' => $payment_method, 'payment_for' => 'Plan Monthly Subscription', 'user_id' => $plans->user_id, 'amount' => $plans->amount, 'tax_amount' => $plans->tax, 'status' => '1', 'category' => $plans->plan_type, 'buy_price' => $buy_price];                
                $payment['transaction_id'] = $request->custom_txn_id;
                $payment['description'] = $request->custom_description.' - processed by '.Auth::user()->first_name.' '.Auth::user()->last_name;
                $payment_id = UserPayment::insertGetId($payment);
                $payment_flag = 1;
            }

            if($payment_flag == 1){
                $child_list = explode(',', $plans->user_list); //child list for the user
                $error_flag = count($child_list);
                foreach ($child_list as $child) {
                    $userdata = User::find($child); //child user details
                    $i_account = $userdata->i_account;
                    $defult_plan = DB::table('switch_template')->where('id', $userdata->switch_id)->value('billing_plan');
                    $xml_data = SwitchHelper::switch_update_plan_xml($i_account, $defult_plan);
                    $temp     = SwitchHelper::call_switch_api($xml_data);                                                                    
                    if($plans->plan_type === 'sim'){
                        $msisdn = $userdata->msisdn->phone_number;
                        if($plans->plan->provider == 'EE'){
                            $customer_id = Helper::get_option('mvno_customer_id');
                            $mvno_key = Helper::get_option('bundle_mvno_key');

                            $subscription_id = $userdata->userDetail->sim_subscription_id;
                            $end_point = '/core/subscriptions/offerings?MVNO='.$mvno_key; //api for sim                                
                            $sim_api_data = ["CustomerId" => (int)$customer_id,"SubscriptionId"=>(int)$subscription_id,"Offerings"=>array(["ProductOfferingId"=> (int)$sim_bill_plan,"OrderedProductCharacteristics"=>array(["Name"=> "MSISDN","Value"=>$msisdn])]),"Channel"=>"Web"];

                            $response = SIMHelper::call_sim_process_api($end_point, json_encode($sim_api_data));
                        }else{                
                            $response = json_decode('{"orderCode":"O2customorder","resultType":"Ok","resultCode":"0"}');
                        }
                        
                        if($response->resultType == 'Ok' && $response->resultCode == 0){                            
                            $log_data = array(
                                'user_id' => $userdata->id,
                                'stock_id' => $userdata->stock_id,
                                'msisdn' => $msisdn,
                                'category' => 'RECURRING',
                                'plan_id' => $plans->plan_id,
                                'value' => $plan->buy_price,
                                'staff_id' => Auth::id(),
                                'reference_id' => $response->orderCode
                            );
                            DB::table('avoo_sim_log')->insert($log_data);
                            $autoplan['next_renewal'] = $next_renewal;
                            AutoPlan::where('id', $plans->id)->update($autoplan);
                            // $pl_cnt = UserPlan::where(['user_id'=>$child,'plan_type'=>'sim'])->count();
                            // $i_billplan = ($pl_cnt >= 2)? $plans->switch_billing_plan:'203';
                            // $in_call_limit = ($pl_cnt >= 2)? $plans->plan->in_call_limit:'1000';
                        }else{                                  
                            $notification = NotificationLog::create(['user_id'=>$child,'message' => 'Manual Subscription EE Subscription Renewal Failed','description'=>'Sub ID:'.$plans->id.', msg:'.json_encode($response).', admin:'.Auth::id(),'request'=>json_encode($sim_api_data),'status'=>'0']);   
                            
                            $obj = (object) array(
                                'notify_id' => $notification->id,
                                'subscripton'=> $plans->id,
                            );  
                            FailureNotification::dispatch($obj)
                                ->delay(now()->addMinutes(1));
                            continue;
                        }
                    }
                    
                    $method = 'accountCredit';
                    $note = 'Monthly Subscription';
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
                        continue;
                    }

                    if($i_billplan != 0 || !is_null($i_billplan)){
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
                            continue;
                        }else{
                            $balance['balance_minutes'] = $in_call_limit;
                            Account::where('user_id', $child)->update($balance);
                            $where = ['user_id' => $child, 'plan_type' => $plans->plan_type]; 
                            UserPlan::where($where)->update(['status' => 0]);   
                            $usage = ['plan_id' => $plans->plan_id, 'user_id' => $child, 'payment_id' => $payment_id, 'status' => 1, 'plan_type' => $plans->plan_type];
                            UserPlan::create($usage);
                            $error_flag--;   
                        }
                    }else{
                        $where = ['user_id' => $child, 'plan_type' => $plans->plan_type]; 
                        UserPlan::where($where)->update(['status' => 0]);  
                        $usage = ['plan_id' => $plans->plan_id, 'user_id' => $child, 'payment_id' => $payment_id, 'status' => 1, 'plan_type' => $plans->plan_type];
                        UserPlan::create($usage);
                    }
                }
            }
        }else{
            return response()->json(['success' => true, 'message' => 'Subscription successfully updated!']);
        }

        if($error_flag){
            return response()->json(['success' => false, 'message' => 'Subscription renewal failed!']);
        }
        return response()->json(['success' => true, 'message' => 'Subscription renewed successfully!']);
    }    

    /**
    * Manage Subscription Status.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_card_list(Request $request)
    {   
        $card_list = 1;
        $renew_id = Crypt::decrypt($request->renew_id);
        $plan = AutoPlan::where('id', $renew_id)->first();
        $card_id = $plan->card_id;
        $cards = UserCreditCard::where('user_id', $plan->user_id)->get();        
        $html = view('modal-popup', compact('card_list','cards','card_id', 'renew_id'))->render();
        return response()->json(['success' => true, 'html' =>  $html]);
    }

    /**
    * Manage Subscription Status.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function change_subscription_card(Request $request)
    {  
        $renew_id = $request->renew_id;
        $card_id = Crypt::decrypt($request->card_id);
        $card = UserCreditCard::where('id', $card_id)->first();
        if($card){
            if($card->card_expiry > Carbon::now()){
                $gateway = PaymentGateway::where('id', $card->gateway)->value('gateway');
                $update = ['card_id' => $card_id, 'transaction_id' => $card->transaction_id, 'card_expiry' => $card->card_expiry, 'card_type' =>  $card->card_type, 'gateway' => $gateway];
                AutoPlan::where('id', $renew_id)->update($update);
                return response()->json(['success' => true, 'message' => 'Card changed successfully']);
            }else{
                return response()->json(['success' => false, 'message' => 'Card details expired, please select another card']);
            }
        } 
        return response()->json(['success' => false, 'html' =>   'Invalid card details given']);       
    }

    /**
    * Update Call Settings.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_call_settings(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        $settings = $request->all();
        unset($settings['user_id']);
        UserData::where('user_id', $user_id)->update(['call_settings' => json_encode($settings)]);
        return response()->json(['success' => true, 'message' => 'Call settings updated successfully']);  
    }

     /**
    * Update Call Settings.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function remove_user_card(Request $request)
    {
        $card_id = Crypt::decrypt($request->card_id);
        $plans = AutoPlan::where('card_id', $card_id)->get();
        if($plans->isEmpty()){
            UserCreditCard::where('id', $card_id)->delete();
            return response()->json(['success' => true, 'card_id' => $card_id, 'message' => 'Card reference removed successfully']); 
        }
        $count = $plans->count();
        return response()->json(['success' => false, 'message' => 'Card reference used in '.$count.' subscription, please update and try!']);  
    }

    /**
    * Reset Daily Otp Limit.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function reset_otp_try(Request $request)
    {
        $phone = $request->phone;
        $user_id = $request->user_id;        
        DB::table('otp')->where('phone', $phone)->orWhere('user_id', $user_id)->update(['no_try' => DB::raw('GREATEST(no_try - 1, 0)')]);
        return;
    }

    /**
    * Destroy User Data.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_user(Request $request)
    {   
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
            DB::table('user_calls')->where('user_id',$user_id)->delete();
            DB::table('user_contacts')->where('user_id',$user_id)->delete();
            DB::table('user_credit_cards')->where('user_id',$user_id)->delete();
            DB::table('user_payments')->where('user_id',$user_id)->delete();
            DB::table('user_plans')->where('user_id',$user_id)->delete();
            DB::table('conference_create')->where('user_id',$user_id)->delete();
            DB::table('tbl_cart')->where('user_id',$user_id)->delete(); 
            DB::table('otp')->where('user_id',$user_id)->orWhere('phone',$user->phone)->delete();
            
            $temp = [];
            if(!is_null($user->i_account)) {
                $xml_data = SwitchHelper::switch_delete_account_xml($user->i_account);
                $temp =  SwitchHelper::call_switch_api($xml_data);
            }
            $description = json_encode(['user_id' => $user->id, 'name' => $user->name, 'phone' => $user->phone, 'email' => $user->email,'i_account' => $user->i_account]);
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

    /**
    * Credit / Debit Manage
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function credit_debit_manage(Request $request)
    {

        $user_id        = Crypt::decrypt($request->user_id);
        $user           = User::find($user_id);        
        $blocked        = Helper::check_fraudster($user_id); //check whether user is in fraud list
        if(!$user || $blocked){  //if in fraud or user doen't active
            return response()->json(['status' => 422, 'message' => 'Account is in inactive list!']);
        }
        $i_account      = $user->i_account;
        $currency       = $user->country->currency;
        $tax            = $user->country->tax;
        $country_code   = $user->country->short_code;

        $getamount      = $this->credit_debit_amount($request,$tax);

        $gateway        = $request->gateway;
        $paymentType    = $request->payment_for;

        $paymentypes  = ['addcredit'=>'Credit Added','debit'=>'Amount Debit','deduct'=>'Amount Deduct','addcard'=>'New Card'];

        $payment_for            = $paymentypes[$paymentType];

        $data['user_id']        = $user_id;
        $data['i_account']      = $i_account;
        $data['currency']       = $currency;              
        $data['net_amount']     = $getamount->amount;
        $data['vat_amount']     = $getamount->tax_amount;
        $data['total_amount']   = $getamount->total_amount;
        $data['buy_price']      = 0;
        $data['payment_for']    = ucwords($payment_for);
        $data['description']    = ucwords($request->custom_description).' - processed by '. Auth::user()->first_name.' '.Auth::user()->last_name;
        $data['category']        = 'switch';
        $data['discount_amount'] = "";
        $data['discount_coupon'] = "";
        
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

        if($paymentType == 'addcredit' || $paymentType == 'debit' || $paymentType == 'addcard'){

            if($gateway == 'Stripe'){
                $data['card_id']         = ($request->credit_card != 'new') ? Crypt::decrypt($request->credit_card) : '';
                $data['stripeToken'] = $request->stripeToken;
                $response = Helper::stripe_payment_process($data);

            }else if($gateway == 'Braintree'){
                $response = Helper::braintree_payment_process($data);
            }else if($gateway == 'Paypal'){
                $response = Helper::paypal_payment_process($data);
            }
            else if($gateway == 'Cash'){
                $payment_method = $request->payment_method;
                $payment = ['total_amount' => $data['total_amount'], 'currency' => $data['currency'],  'payment_method' => $payment_method, 'payment_for' => $payment_for, 'user_id' => $user_id, 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'category' => $data['category'], 'buy_price' => 0];
                $payment['transaction_id'] = $request->custom_txn_id;
                $payment['description']    = $data['description'];

                $response['status']  = 1;
            }
        }else if($paymentType == 'deduct'){

            $user_balance = Account::where('user_id',$user_id)->first();

            if($user_balance->balance_amount < $data['total_amount']){
             return response()->json(['status' => 422, 'message' => 'Amount trying to deduct is greater than the account balance']);  
            }else{
                $method = 'accountDebit';
                $debit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, $data['net_amount'], $data['currency']);
                $temp = SwitchHelper::call_switch_api($debit_xml); 
                if (array_key_exists("fault", $temp)) {

                    $notification = NotificationLog::create(['user_id'=>$user_id, 'message' => ' Amount Deduct Failed','description'=>'msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']);
                    return response()->json(['status' => 422, 'message' => 'Deduct payment failed.']);
                }else{
                    $payment = ['total_amount' => $data['total_amount'], 'currency' => $data['currency'],  'payment_method' => $paymentType, 'payment_for' => $payment_for, 'user_id' => $user_id, 'amount' => $data['net_amount'], 'tax_amount' => $data['vat_amount'], 'category' => $data['category'], 'buy_price' => 0,'description'=>$data['description'],'status'=>4];

                    $payment_id = UserPayment::insertGetId($payment);

                    $user_balance = Account::where('user_id',$user_id)->first();
                    $balance['balance_amount'] = $user_balance->balance_amount - $data['total_amount'];
                    Account::where('user_id', $user_id)->update($balance);
                    return response()->json(['status' => 200, 'message' => 'Amount deducted successfully']);
                } 
            }
        }

        /* Payment Success */
        if($response['status']){

            if($paymentType == 'addcredit'){

                $method     = 'accountCredit';
                $credit_xml = SwitchHelper::switch_account_bal_xml($method, $i_account, $data['net_amount'], $data['currency']);
                $temp       = SwitchHelper::call_switch_api($credit_xml);

                if (array_key_exists("fault", $temp)) {
                    if($gateway == 'Cash'){
                        $payment['status'] = 2;
                        $payment_id = UserPayment::insertGetId($payment);
                    }else{  
                       $payment_id =  $response['payment_id'];
                    }
                    $notification = NotificationLog::create(['user_id'=>$user_id, 'message' => $gateway.' AddCredit Failed','description'=>'Payment Id:'.$payment_id.', msg:'.json_encode($temp).', admin:'.Auth::id(),'status'=>'0']);
                    return response()->json(['status' => 422, 'message' => 'Payment added successfully. Account Credit to switch failed']);
                }else{
                    if($gateway == 'Cash'){
                        $payment['status'] = 1;
                        $payment_id = UserPayment::insertGetId($payment);
                    }
                    $user_balance = Account::where('user_id',$user_id)->first();
                    $balance['balance_amount'] = $user_balance->balance_amount + $data['net_amount'];
                    Account::where('user_id', $user_id)->update($balance);
                    return response()->json(['status' => 200, 'message' => 'Credit added successfully']);
                } 

            }else if($paymentType == 'debit'){
                if($gateway == 'Cash'){
                    $payment['status'] = 1;
                    $payment_id = UserPayment::insertGetId($payment);
                }
                if(isset($request->notify) && $request->notify == 1){
                    $msg  = "Info from ".config('settings.app_name')." Mobile.".$request->custom_message;
                    $phone = $user->phone;
                    $this->send_sms($msg,$phone);
                }
                return response()->json(['status' => 200, 'message' => 'Amount debited successfully']);

            }else if($paymentType == 'addcard'){
                $payment = UserPayment::where('id', $response['payment_id'])->first();
                if($payment){
                    switch($payment->payment_method){
                        case 'Paypal':
                            $refund = Helper::paypal_refund_process($payment, $data['total_amount'], $data['description']);
                        break;
                        case 'Braintree':
                            $refund = Helper::braintree_refund_process($payment, $data['total_amount'], $data['description']);
                        break;
                        case 'Stripe':
                            $refund = Helper::stripe_refund_process($payment, $data['total_amount'], $data['description']);
                        break;
                        default:
                            $refund = ['status' => 422, 'message' => 'Refund process doesn\'t support for this transaction'];
                        break;
                    }
                    if($refund['error']){
                        NotificationLog::create(['user_id' => $user_id, 'message' => 'Card change refund amount '.$data['total_amount'].' failed', 'description' => 'Payment id:'. $response['payment_id'] .' ', 'status' => '0']);
                        return response()->json(['status' => 422, 'message' => 'New card added successfully. Failed to initate refund.']);  
                    }else{
                       return response()->json(['status' => 200, 'message' => 'New card added successfully. Refund initiated']);  
                    }
                }
            }
        }else{
          return response()->json(['status' => 422, 'message' => 'Payment failed.']);   
        }
    }
    private function credit_debit_amount($request,$tax){

        if($request->custom_amount_check == 1){
            $amount  = $request->custom_amount;
        }else{
            if($request->payment_for == 'addcard'){
                $request->tax_type = 3; /*No tax calculation */
                if($request->gateway == 'Stripe'){
                    $amount = (float)0.10;
                }else{
                    $amount = (float)0.10;
                } 
            }else{
                $creditid       = Crypt::decrypt($request->credit_amount);
                $getcredit      = DB::table('credits')->whereId($creditid)->first();
                $amount         = $getcredit->amount;
            }
        }
        if($request->tax_type == 1){           /* Tax Included */
            $getamount     = Helper::vatreduceCalculation($amount,$tax);
        }else if($request->tax_type == 2){    /* Tax Excluded */
            $getamount     = Helper::vataddCalculation($amount,$tax);
        }else if($request->tax_type == 3){   /* No tax */
            $getamount = new \stdClass;
            $getamount->amount       = $amount;
            $getamount->tax_amount   = 0;
            $getamount->total_amount = $amount;
        }
        return $getamount;
    }
    private function send_sms($msg,$phone){

        $account_sid    = Helper::get_option('twilio_account_sid');
        $auth_token     =  Helper::get_option('twilio_auth_token');
        $twilio_number  = Helper::get_option('twilio_number');                            
        try {             
            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $phone,
                array(
                    'from' => $twilio_number,
                    'body' => $msg
                )
            );
        } catch (RestException $exception) {                  
            
        }
    }
    /**
    * calculate credit debit amount.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function cal_credit_debit(Request $request)
    {
        $user_id        = Crypt::decrypt($request->user_id);
        $user           = User::find($user_id); 
        $tax            = $user->country->tax;

        $getamount      = $this->credit_debit_amount($request,$tax);
        $html = view('user.credit-debit-dynamic', compact('request','getamount','user',''))->render();
        return response()->json(['status'=>200,'page'=>$html]);
    }
    /**
    * credit debit payment gateways.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function credit_debit_gateway(Request $request)
    {
        $gateway_id = ($request->gateway == 'Cash') ? 'Cash' : $request->gateway;
        if($gateway_id != 'Cash'){
            $gateway    = PaymentGateway::where('id', $gateway_id)->first();
        }else{
          $gateway = new \stdClass();  
          $gateway->gateway    = 'Cash';  
        }
        $gateways    = PaymentGateway::get();

        $user_id    = Crypt::decrypt($request->user_id);
        if($user_id)
        $user = User::find($user_id);
        $cards = UserCreditCard::where(['user_id' => $user_id, 'gateway' => $gateway_id])->get();

        $html = view('user.credit-debit-dynamic', compact('gateway', 'cards','user','gateways'))->render();
        return response()->json(['error' => false, 'html' => $html]);
    }
    /**
    * Clawback and commission payment stop when user is inactive.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_status_trigger($user_id,$auto_plan_id,$status)
    {
        $changedate = Carbon::now();
        $user       = User::where('id',$user_id)->first();

        $useron     = Carbon::parse($user->created_at);

        $autoplan   = AutoPlan::whereId($auto_plan_id)->first();

        $planType   = ($autoplan->bundle_id === 0) ? 1 : 2;  
        $planId     = ($planType === 2) ? $autoplan->bundle_id : $autoplan->plan_id;

        $commission = PaymentCommission::selectRaw('sum(pay_amount) as payamount, sum(amount_paid) as paidamount,comm_user')
                      ->where('autoplan_id',$auto_plan_id)->first();

        if(!empty($commission)){
            $dealer_id      = $commission->comm_user;
            $topay          = $commission->payamount;
            $paidamount     = $commission->paidamount;
            $totcommission  = round(($topay + $paidamount),2);

            if($status === 0){  // User inactive case

                $dealerclawback = Clawback::select('period')
                                  ->where(['admin_id'=>$dealer_id,'plan_type'=>$planType,'plan_id'=>$planId,'status'=>1])->first();

                if(!empty($dealerclawback)){
                    $period = $dealerclawback->period;
                }else{
                    $clawback   = Clawback::select('period')
                            ->where(['plan_type'=>$planType,'plan_id'=>$planId,'status'=>1])->first(); 

                    $period = (!empty($clawback)) ? $clawback->period : 0;
                }
                if($period !== 0){

                    $useractiveperiod = round(abs(Carbon::parse($useron)->floatDiffInMonths($changedate)));
                    $useractiveperiod = ($useractiveperiod == 0) ? 1 : $useractiveperiod;

                    if($useractiveperiod < $period){
                        $comm_amount     = round((($totcommission / $period ) * $useractiveperiod),2);
                        $clawback_amount = ($comm_amount - $paidamount);

                        $comm_reversal   = $totcommission - $comm_amount;

                        $clawbackArray   = [];

                        $clawbackArray['autoplan_id']     = $auto_plan_id;
                        $clawbackArray['comm_user']       = $dealer_id;
                        $clawbackArray['comm_total']      = $totcommission;
                        $clawbackArray['comm_gained']     = $comm_amount;
                        $clawbackArray['comm_reversal']   = $comm_reversal;
                        $clawbackArray['clawback_amount'] = $clawback_amount;
                        $clawbackArray['clawback_date']   = $changedate->format('Y-m-d');
                        ClawbackPayments::updateOrCreate(['autoplan_id'=>$auto_plan_id],$clawbackArray);
                        PaymentCommission::where(['autoplan_id'=>$auto_plan_id,'is_paid'=> 0])->update(['user_active'=> 0]);
                        return true;
                    }

                }
                PaymentCommission::where(['autoplan_id'=>$auto_plan_id,'is_paid'=> 0])->update(['user_active'=> 0]);
                return true;
            }else{  //User active case
                $getpaused = PaymentCommission::select('id','payment_date')->where(['autoplan_id'=>$auto_plan_id,'user_active'=>0])->get();
                if($getpaused->isNotEmpty()){
                    foreach ($getpaused as $gkey => $glist) {
                        if($gkey == 0){
                            if(Carbon::parse($glist->payment_date)->lessThanOrEqualTo(Carbon::now())){
                                $paymentDate  = Carbon::now()->format("Y-m-d");
                            }else{
                               $paymentDate  = Carbon::parse($glist->payment_date)->format("Y-m-d"); 
                            }
                        }else{
                           $paymentDate  = Carbon::parse($paymentDate)->addMonth()->format("Y-m-d"); 
                        }
                        $paymentEnd   = Carbon::parse($paymentDate)->addDays(1)->format("Y-m-d");
                        PaymentCommission::whereId($glist->id)->update(['payment_date'=>$paymentDate,'payment_end'=>$paymentEnd,'user_active'=>1]);
                    }  
                }
                ClawbackPayments::where(['autoplan_id'=>$auto_plan_id,'comm_user'=>$dealer_id])->update(['comm_gained'=>$totcommission,'comm_reversal'=>0,'clawback_amount'=>0,'clawback_date'=>$changedate->format('Y-m-d')]); 
                return true;
            }
        }
    }
    /**
    * Direct Debit.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function direct_debit($user_id)
    {
        $user_id  = Crypt::decrypt($user_id);
        $user     = User::find($user_id);
        $blocked  = Helper::check_fraudster($user_id); //check whether user is in fraud list
        if(!$user || $blocked){  //if in fraud or user doen't active
            return redirect('user-details')->with('Account is in inactive list!');
        }
        $step = 0;
        $customer = view('user.direct-debit-dynamic', compact('user','step'))->render();
        return view('user.direct-debit',compact('customer'));
    }
    /**
    * Direct Debit Manage.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function direct_debit_manage(Request $request)
    {
        $user_id  = Crypt::decrypt($request->user_id);
        $user     = User::find($user_id);
        $blocked  = Helper::check_fraudster($user_id); //check whether user is in fraud list
        if(!$user || $blocked){  //if in fraud or user doen't active
            return response()->json(['status' => 422, 'message' => 'Account is in inactive list!']);
        }

        if($request->step == 1){
            $validator = Validator::make($request->all(), 
                ['first_name'=>'required|max:25|regex:/^[a-zA-Z0-9 ]+$/',
                'last_name'  => 'required|max:20|regex:/^[a-zA-Z0-9 ]+$/',
                'country_id' => 'required|regex:/^[a-zA-Z0-9- ]+$/',
                'email'      =>'required|email', 
                'address'    =>'required',
                'city'       =>'required', 
                'postal_code'  =>'required',        
              ], ['first_name.required' => 'firstname required',
                'last_name.required' => 'lastname required',
                'country_id.required' => 'country required',
                'email.required' => 'Email required',
                'address.required' => 'Address required',
                'city.required' => 'City required',
                'postal_code.required' => 'Postal code required',
              ]
            );
            if ($validator->fails()){
                return response()->json(['status'=>422,'msg'=>implode(',', $validator->errors()->all())]);
            } 
        }
        if($request->step == 2){
            $validator = Validator::make($request->all(), 
                ['first_name'=>'required|max:25|regex:/^[a-zA-Z0-9 ]+$/',
                'last_name'  => 'required|max:20|regex:/^[a-zA-Z0-9 ]+$/',
                'account_no' =>'required_without:iban', 
                'branch_code' =>'required_without:iban',
                'iban'        =>'required_without:account_no',        
              ], ['first_name.required' => 'firstname required',
                'last_name.required' => 'lastname required',
                'account_no.required_without' => 'Account no required',
                'branch_code.required_without' => 'Branch code required',
                'iban.required_without' => 'Iban required',
              ]
            );
            if ($validator->fails()){
                return response()->json(['status'=>422,'msg'=>implode(',', $validator->errors()->all())]);
            }
        }
        $country    = Country::find($request->country_id);
        $gocardless = Helper::initiate_gocardless();

        switch ($request->step) {
            case 1:
                $customerData = [
                            "params" => ["email" => $request->email,
                                   "given_name" => $request->first_name,
                                   "family_name" => $request->last_name,
                                   "address_line1"=>$request->address,
                                   "city"=>$request->city,
                                   "country_code" => $country->short_code,
                                    "postal_code"=>$request->postal_code]
                            ];
                if(is_null($request->customer_edit)){
                    $customer  = Helper::trigger_gocardless('addcustomer',$customerData);
                    $message   = 'added';
                }else{
                    $customer  = Helper::trigger_gocardless('editcustomer',$customerData,$request->customer_edit);
                    $message   = 'updated';
                }           
                if($customer->status == 200){
                    DB::table('user_data')->where('user_id',$user_id)
                                        ->update(['gocardless_customer'=>$customer->response->id,'postal_code'=>$request->postal_code,'city'=>$request->city,'address'=>$request->address]);

                    DB::table('users')->where('id',$user_id)
                                        ->update(['country_id'=>$request->country_id]);

                    $existbank = UserBank::where('user_id',$user_id)->get();
                    $message = '';
                    $step    = 1;
                    $bank = view('user.direct-debit-dynamic', compact('user','step','existbank'))->render();
                    return response()->json(['status'=>200,'msg'=>'Customer details '.$message.' to Direct debit','page'=>$bank]);
                }else{
                   return response()->json(['status'=>422,'msg'=>$customer->response[0]->message]); 
                }
                break;
            case 2: //Bank details

            $getbank        = [];
            $updatebankdata = [];
            if(isset($request->bank_list)){
                $getbank  = UserBank::where('user_id',$user_id)
                            ->where('id',Crypt::decrypt($request->bank_list))->first();
            }
            
            $bankdata =  [
                    "params" => [
                               "account_holder_name" => $user->first_name.' '.$user->last_name,
                               "country_code" => $user->country->short_code,
                               "links" => ["customer" => $user->userDetail->gocardless_customer]]
                    ];
            if(isset($request->account_no)){
                $bankdata['params']['account_number'] = $request->account_no;
                $bankdata['params']['branch_code'] = $request->branch_code;
                $updatebankdata = ['account_number'=> $request->account_no,'branch_code'=>$request->branch_code];
            }elseif(isset($request->iban)){
              $bankdata['params']['iban'] = $request->iban; 
              $updatebankdata = ['iban'=>$request->iban];
            }else{
               return response()->json(['status'=>422,'msg'=>'Account details missing']);  
            }
            if(empty($getbank)){

                $addbank  = Helper::trigger_gocardless('addbank',$bankdata);
                if($addbank->status == 200){
                    $user_bank = UserBank::updateOrCreate(['user_id' => $user_id,'bank_id'=>$addbank->response->id, 'first_name' => $request->first_name, 'last_name' => $request->last_name, 'account_no' => $request->account_no, 'branch_code' => $request->branch_code,'country_id'=>$user->country->id,'iban'=>$request->iban,'status'=>1]);
                    $message = 'added';
                }else{
                    $respmsg = '';
                   foreach ($addbank->response as $key => $value) {
                       $respmsg .= $value->field.' '.$value->message.',';
                   }
                   return response()->json(['status'=>422,'msg'=>$respmsg]); 
                }
            }else{
                $user_bank    = $getbank;
                $updatedata   = [
                                  "params" => ["metadata" => $updatebankdata]
                                ];
                $updatebank   = Helper::trigger_gocardless('updatebank',$updatedata,$user_bank->bank_id);
                $message = 'updated';
            }

            $step    = 2;
            $mandate = view('user.direct-debit-dynamic', compact('user','user_bank','step'))->render();
            return response()->json(['status'=>200,'msg'=>'Bank details '.$message.' to Direct debit','page'=>$mandate]);

            break;

            case 3: //Mandate details

            if(is_null($request->bank_id) || $request->bank_id == ''){
              return response()->json(['status'=>422,'msg'=>'Technical error..']);  
            }
            $bank_id     = Crypt::decrypt($request->bank_id);
            $getbank     = UserBank::whereId($bank_id)->first();
            if($getbank->card_id == 0){
                $mandatedata = [
                        "params" => ["scheme" => "bacs",
                           "metadata" => ["appname" => config('settings.app_name')],
                           "links" => ["customer_bank_account" => $getbank->bank_id]]
                        ];
                $addmandate  = Helper::trigger_gocardless('addmandate',$mandatedata);
                if($addmandate->status == 200){
                    $card_expire = Carbon::now()->addYear(50)->format('Y-m-d');
                    $card_data   = UserCreditCard::updateOrCreate(['user_id' => $user_id,'card_expiry' => $card_expire,'card_type'=>'', 'transaction_id' => $addmandate->response->id, 'gateway' => 4]);

                    DB::table('auto_plan')->where('user_id',$user_id)->update(['transaction_id'=>$addmandate->response->id,'card_id'=>$card_data->id,'card_expiry' => $card_expire,'card_type'=>'','gateway'=>'Gocardless']);

                    UserBank::where('bank_id',$bank_id)->update(['card_id'=>$card_data->id]);

                    $success = true;
                }else{
                  $success = false;  
                }
            }else{
                $getmandate = UserCreditCard::whereId($getbank->card_id)->first();
                $updatedata = [
                              "params" => ["metadata" => ["customer_bank_account" => $getbank->bank_id]]
                            ];
                $updatemandate   = Helper::trigger_gocardless('updatemandate',$updatedata,$getmandate->transaction_id);
                $success = true;
            }
            $mandate = 1;
            $result  = view('user.direct-debit-dynamic', compact('mandate','success'))->render();
            return response()->json(['status'=>200,'msg'=>'','page'=>$result]); 
            break;
            default:
                # code...
                break;
        }
    }
        /**
    * Show the application user opted feature.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function opted_services(){
        // if (!Helper::has_permission('reports')) {
        //     abort(403,'Access denied');
        // }
        return view('user.opted-services');
    }
    /**
    * Show the application user opted services.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_services(Request $request){

        $optedservices   = DB::table('opted_services as os')
                        ->select('os.*','us.name','us.phone','us.email','ps.short_code','ps.service_name','ps.provider','ps.type','os.status as opted_status',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS doneby"))
                        ->join('users as us', 'us.id', '=', 'os.user_id')
                        ->join('provider_services as ps', 'ps.id', '=', 'os.service_id')
                        ->leftJoin('admins as a', 'a.id', '=', 'os.done_by')
                        ->orderBy('created_at', 'DESC');

        if ($request->has('number') && $request->get('number') != "") {
                $optedservices->where('us.phone', 'like', "+{$request->get('number')}%");
        }
        if ($request->has('opted_status') && $request->get('opted_status') != "") {
                $optedservices->where('os.status',$request->get('opted_status'));
                
        }             
        $result = Datatables::of($optedservices)
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y H:i:s') : '';
                    })
                    ->editColumn('opted_status', function ($user) {
                        $stat = "";
                        switch ($user->status) {
                            case 0:
                                $stat = "Request received";
                                break;
                            case 1:
                                $stat = "Processed";
                                break;
                            case 2:
                                $stat = "Failed";
                                break;
                        }
                        return $stat;
                    })
                    ->editColumn('action', function ($data) {
                        if($data->opted_status != 1){
                            $opteddetails = Crypt::encrypt($data->id);
                            $user_id      = Crypt::encrypt($data->user_id);
                            $btn_type = ($data->opted_key == 'Yes' || $data->opted_key == 1) ? 'btn-info' : 'btn-warning';
                            return '<a href="javascript:void(0)" class="btn '.$btn_type.' btn-sm service_actions" data-opted="'.$opteddetails.'" data-tag="'.$data->opted_value.'" data-user="'.$user_id.'" data-type="'.$data->type.'">'.$data->opted_value.'</a>';
                        }else { return '';}
                    })
                    ->make(true);
        return $result;
    }
    /**
    * Application user opted services api call.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function services_change(Request $request){
        $user       = User::find(Crypt::decrypt($request->user_id));
        $provider   = $user->msisdn->provider;
        $requesttype = $request->requesttype; 
        if(isset($request->dataid)){
            $dataid  = Crypt::decrypt($request->dataid);
        }else{
            if(in_array($provider,['O2','EE_O2','VUK','E_SIM'])){
                $bars       = json_decode($request->bars,TRUE);
                if(empty($bars)){
                    return response()->json(['status' => 422, 'message' => 'No changes applied']); 
                }
                $service_id = array_column($bars,'bar_id');
                $services   = DB::table('provider_services')->whereIn('id',$service_id)->get();

                if($services->isNotEmpty()){
                    foreach($services as $key => $list){
                        $servicevalue = json_decode($list->service_value,TRUE);
                        $k           = array_search($list->id, array_column($bars, 'bar_id'));
                        $opted_key   = $bars[$k]['status'];
                        $opted_value = $servicevalue[$opted_key];
                        $servicedata = ['user_id'=>$user->id,'service_id'=>$list->id,'opted_key'=>$opted_key,'opted_value'=>$opted_value,'request_from'=>'WEB'];

                        $opted = OptedService::updateOrCreate(['user_id' => $user->id, 'service_id' => $list->id,'status'=>0],$servicedata);
                        $dataid[] = $opted->id;
                    }
                }
            }
        }
        $dataid  = is_array($dataid) ? $dataid : [$dataid];
        $getdata = DB::table('opted_services as os')
                        ->select('os.*','ps.short_code','ps.service_key','ps.service_short_code','ps.service_name')
                        ->join('provider_services as ps', 'ps.id', '=', 'os.service_id')
                        ->whereIn('os.id', $dataid)->get();

        if(in_array($provider,['O2','EE_O2','VUK'])){
            
            $datalist['order_id'] = isset($user->order) ? $user->order->order_id : $user->parent->order->order_id;
            $datalist['phone']    = '0'.ltrim($user->msisdn->phone_number,$user->country->dial_code);

            if($requesttype ==1){
                $reqtype   = "Bars";
                $baroption = ['bars_off','bars_on'];
                $bararray  = [];
                foreach($getdata as $key => $list){
                    $bararray[$baroption[$list->opted_key]][] = $list->service_key;
                }
                $datalist[$baroption[0]] = isset($bararray[$baroption[0]]) ? join(',',$bararray[$baroption[0]]) : '';
                $datalist[$baroption[1]] = isset($bararray[$baroption[1]]) ? join(',',$bararray[$baroption[1]]) : '';

                $xml_data = DwpHelper::dwp_set_bar($datalist);
            }else if($requesttype == 2){
                $reqtype      = "Services";
                $networkarray = [];
                foreach($getdata as $key => $list){
                    $networkarray[$list->service_key] = $list->opted_key;
                }
                $datalist['services'] = $networkarray;
                $xml_data = DwpHelper::dwp_set_services($datalist);
            }
            $response    = DwpHelper::dwp_process_api($xml_data);
            $response    = json_decode(DwpHelper::dwp_response_handler($response));
            if($response->children[0]->no == 0){
                $servstatus      = 1;
                foreach($getdata as $key => $list){
                    $userservice = DB::table('user_services')
                                ->updateOrInsert(['user_id' => $user->id, 'service_id' => $list->service_id],['service_status' => $list->opted_key]);
                }
                $update = DB::table('opted_services')->whereIn('id',$dataid)->update(['status'=>$servstatus,'description'=>'','done_by'=>Auth::id()]);
                return response()->json(['status' => 200, 'message' => $reqtype.' changed Successfully']);
            }else{
                $servstatus      = 2;
                $errormsg        = isset($response->children[0]->text) ? $response->children[0]->text: 'Failed to change services';
                $update = DB::table('opted_services')->whereIn('id',$dataid)->update(['status'=>$servstatus,'description'=>$errormsg,'done_by'=>Auth::id()]);
                return response()->json(['status' => 422, 'message' => $errormsg]);
            }
        }elseif(in_array($provider,['E_SIM'])){
            if($requesttype == 2){
                $reqtype      = "Services";
                $networkarray = [];
                foreach($getdata as $key => $list){
                    $networkarray[$list->service_key] = $list->opted_value;
                }
                $setservice = GlobalSim::SetSimStatus($user->msisdn->phone_number,$networkarray);
                if($setservice == false || $setservice['@attributes']['status'] == 'fail'){
                    Log::error('GetSimStatus',[
                        'user_id' => $user->id,
                        'error' =>   $setservice
                    ]);
                    $update = DB::table('opted_services')->whereIn('id',$dataid)->update(['status'=>2,'description'=>'','done_by'=>Auth::id()]);
                    return response()->json(['status' => 422, 'message' =>'sim services request failed']);
                }
                foreach($getdata as $key => $list){
                    $userservice = DB::table('user_services')
                                ->updateOrInsert(['user_id' => $user->id, 'service_id' => $list->service_id],['service_status' => $list->opted_key]);
                }
                $update = DB::table('opted_services')->whereIn('id',$dataid)->update(['status'=>1,'description'=>'','done_by'=>Auth::id()]);
                return response()->json(['status' => 200, 'message' =>'Service changed Successfully']);
            }
        }
    }
}
