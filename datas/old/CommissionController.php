<?php

namespace App\Http\Controllers;

use DB;
use DataTables;
use Carbon;
use Helper;
use Crypt;
use Auth;
use Excel;
use App\Exports\CustomExport;
use Illuminate\Http\Request;
use \App\Models\PlanCommission;
use \App\Models\UserCommission;
use \App\Models\PaymentCommission;
use \App\Models\StaffCommission;
use \App\Models\StaffCommissionPayment;
use \App\Models\DealerPayHistory;
use \App\Models\Admins;
use \App\Models\AutoPlan;
use \App\Models\StaffPortCommission;
use \App\Models\StaffPaycreditCommission;
use \App\Models\DealerRevenue;

class CommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
    * Show the Sim plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index()
    { 
        if (!Helper::has_permission('commission')) {
            abort(403,'Access denied');
        }  
        abort(403,'COMMING SOON. This Page is under construction!...');
    }
    /**
    * Show the plans commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_planlist()
    { 
        if (!Helper::has_permission('commission')) {
            abort(403,'Access denied');
        }
        $plans = DB::table('plan_commissions as pc')->select('pc.*','cd.id as durationid','cd.days','cd.name')
                ->join('comm_durations as cd','cd.id','=','pc.comm_duration')
                ->orderBy('pc.plan_id','ASC')
                ->orderBy('pc.plan_type', 'ASC')
                ->orderBy('pc.comm_duration', 'ASC')->get();
        $plandetails = array();
        foreach ($plans as $plan) {
            $data = PlanCommission::find($plan->id)->plan;
            $plan->plan_name = $data->plan_name;
            array_push($plandetails, $plan);
        }
        return view('commission.list', compact('plandetails'));
    }
    /**
    * Show the plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_commplan()
    { 
        if (!Helper::has_permission('commission','create')) {
            abort(403,'Access denied');
        }
        $duration = DB::table('comm_durations')->get();  
        return view('commission.create',compact('duration'));
    }
    /**
    * Show the Edit Plan Commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_commplan($id)
    { 
        if (!Helper::has_permission('commission', 'edit')) {
            abort(403,'Access denied');
        }
        $id = Crypt::decrypt($id);
        $id = explode('-', $id);
        $plantype = $id[0];
        $planid   = $id[1];
        $plans = DB::table('plan_commissions as pc')->select('pc.*','cd.id as durationid','cd.days','cd.name')
                ->join('comm_durations as cd','cd.id','=','pc.comm_duration')
                ->where('pc.plan_type', $plantype)->where('pc.plan_id', $planid)->get();
        $plandetails = $plans;
        $duration = DB::table('comm_durations')->get();
        return view('commission.edit', compact('plandetails','duration'));
    }
    
    /**
    * Show the list the plan.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_getplan(Request $request)
    {
        $type = $request->type;
        switch ($type) {
            case 1:
                $plans = DB::table('tbl_plans')->select(['id','plan_name'])->get();
                break;
            case 2:
                $plans = DB::table('tbl_bundles')->select(['id','plan_name','sim_count'])->get();
                break;
            default:
                $plans = DB::table('tbl_plans')->select(['id','plan_name'])->get();
                break;
        }
        return json_encode($plans);
    }
    /**
    * Function Update plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_plancommission(Request $request)
    { 
        if(isset($request->id)) {
            if (!Helper::has_permission('commission', 'edit')) {
                abort(403,'Access denied');
            }
            $data = $request->all();
            unset($data['_token']);
            DB::table('plan_commissions')
            ->where('plan_type',$data['plan_type'])
            ->where('plan_id',$data['plan_id'])->delete();

            foreach ($data['comm_rate'] as $key => $val) {
            $dataval = array();
            $dataval['plan_type'] = $data['plan_type'];
            $dataval['plan_id']   = $data['plan_id'];
            $dataval['comm_rate'] = $data['comm_rate'][$key];
            $dataval['comm_type'] = $data['comm_type'][$key];
            $dataval['comm_duration'] = $data['comm_duration'][$key];
            $dataval['status']        = $data['status'][$key];

            $id =  PlanCommission::updateOrCreate(['plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                
            }  
            if($id) {
                return redirect()->back()->with('message', 'Plan commission update successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update this plan commission, please try again');
            }
        }else{
             if (!Helper::has_permission('commission')) {
                abort(403,'Access denied');
            }
            $data = $request->all();
            unset($data['_token']);
            foreach ($data['comm_rate'] as $key => $val) {
            $dataval = array();
            $dataval['plan_type'] = $data['plan_type'];
            $dataval['plan_id']   = $data['plan_id'];
            $dataval['comm_rate'] = $data['comm_rate'][$key];
            $dataval['comm_type'] = $data['comm_type'][$key];
            $dataval['comm_duration'] = $data['comm_duration'][$key];
            $dataval['status']        = $data['status'][$key];

            $id =  PlanCommission::firstOrCreate(['plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                
            }
            //DB::table('plan_commissions')->insertGetId($data);
                if($id->wasRecentlyCreated)
                    return redirect('/edit-commplan/'.Crypt::encrypt($data['plan_type'].'-'.$data['plan_id']))->with('message','Plan commission Created successfully!');
                else
                    return redirect()->back()->with('error', 'Plan commission already exists');
        }
    }
    /**
    * Show the user commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_userlist()
    { 
        if (!Helper::has_permission('commission')) {
            abort(403,'Access denied');
        }
        $plan = DB::table('userplan_commissions as upc')->select('upc.*','cd.id as durationid','cd.days','cd.name','ad.id as userid','ad.first_name','ad.promocode')
                ->join('comm_durations as cd','cd.id','=','upc.comm_duration')
                ->join('admins as ad','ad.id','=','upc.user_id');
                if(Auth::user()->role == 5){
                  $plan = $plan->where('ad.id', Auth::id())
                            ->orWhere('ad.parent_id', Auth::id());  
                }
        $plans = $plan->orderBy('ad.id','ASC')
                ->orderBy('upc.plan_id','ASC')
                ->orderBy('upc.plan_type', 'ASC')
                ->orderBy('upc.comm_duration', 'ASC')->get();
        $plandetails = array();
        foreach ($plans as $plan) {
            $data = UserCommission::find($plan->id)->plan;
            $plan->plan_name = $data->plan_name;
            array_push($plandetails, $plan);
        }
        $dealers = Admins::select('admins.id','first_name','first_name','promocode')
                    ->join('tbl_roles as tr','tr.id','=','admins.role')
                    ->where('tr.short_code','DEALER')->where('status', 1)->get();
        return view('commission.userlist', compact('plandetails','dealers'));
    }
    /**
    * Show the user plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function create_commplanuser()
    { 
        if (!Helper::has_permission('commission','create')) {
            abort(403,'Access denied');
        }
        $duration = DB::table('comm_durations')->get();  
        $user     = DB::table('admins')->select('id','first_name','promocode');
                    if(Auth::user()->role == 5){
                      $user = $user->where('parent_id', Auth::id());  
                    }
        $user = $user->where('role','<>',1)->get();
        return view('commission.usercreate',compact('duration','user'));
    }
    /**
    * Function Update plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_userplancommission(Request $request)
    { 
        if(isset($request->id)) {
            if (!Helper::has_permission('commission', 'edit')) {
                abort(403,'Access denied');
            }
            $data = $request->all();
            unset($data['_token']);

            DB::table('userplan_commissions')
            ->where('user_id',$data['user_id'])
            ->where('plan_type',$data['plan_type'])
            ->where('plan_id',$data['plan_id'])->delete();

            foreach ($data['comm_rate'] as $key => $val) {
            $dataval = array();
            $dataval['user_id']     = $data['user_id'];
            $dataval['plan_type']   = $data['plan_type'];
            $dataval['plan_id']     = $data['plan_id'];
            $dataval['comm_rate']   = $data['comm_rate'][$key];
            $dataval['comm_type']   = $data['comm_type'][$key];
            $dataval['comm_duration'] = $data['comm_duration'][$key];
            $dataval['status']        = $data['status'][$key];

            $id =  UserCommission::updateOrCreate(['user_id' => $data['user_id'],'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                
            }
            if(isset($data['add_revenue']) && $data['add_revenue'] == 1){
                $revenue['amount'] = $data['revenue_amount'];
                DB::table('dealer_revenue')->where('dealer_id', $data['user_id'])->update($revenue);
            }
            if($id) {
                return redirect()->back()->with('message', 'User Plan commission update successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update this User plan commission, please try again');
            }
        }else{
             if (!Helper::has_permission('commission')) {
                abort(403,'Access denied');
            }
            $data = $request->all();
            unset($data['_token']);
            foreach ($data['comm_rate'] as $key => $val) {
            $dataval = array();
            $dataval['user_id']     = $data['user_id'];
            $dataval['plan_type']   = $data['plan_type'];
            $dataval['plan_id']     = $data['plan_id'];
            $dataval['comm_rate']   = $data['comm_rate'][$key];
            $dataval['comm_type']   = $data['comm_type'][$key];
            $dataval['comm_duration'] = $data['comm_duration'][$key];
            $dataval['status']        = $data['status'][$key];

            $id =  UserCommission::firstOrCreate(['user_id' => $data['user_id'],'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                
            }
             // $id =  UserCommission::firstOrCreate(['user_id' => $data['user_id'],'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration']], $data);
            //$id = DB::table('userplan_commissions')->insertGetId($data);
                if($id->wasRecentlyCreated)
                    return redirect('/edit-commplanuser/'.Crypt::encrypt($data['plan_type'].'-'.$data['plan_id'].'-'.$data['user_id']))->with('message','User Plan commission Created successfully!');
                else
                    return redirect()->back()->with('error', 'User Plan Commission already exists');
        }
    }
    /**
    * Function add revenue commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_revenue(Request $request)
    { 
        $revenue['dealer_id']= $request->dealer;
        $revenue['amount'] = $request->revenue_amount;
        $revenue['expiry_at'] = Carbon::parse($request->expiry_at)->format('Y-m-d');
        $save = DealerRevenue::updateOrCreate(['dealer_id' => $request->dealer],$revenue);
        if($save->wasRecentlyCreated)
        return response()->json(['status' => "success", 'message' => 'Dealer Revenue Added']);
        else
            return response()->json(['status' => "success", 'message' => 'Dealer Revenue updated']);
    }
    /**
    * Function get revenue commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_revenue(Request $request)
    { 
        $revenue['dealer_id']= $request->dealer;
        $get = DealerRevenue::select('amount','expiry_at')->where('dealer_id',$request->dealer)->first();
        if(isset($get))
        return response()->json(['status' => "success", 'message' => $get]);
    }

    /**
    * Show the Edit user Plan Commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_commplanuser($id)
    { 
        if (!Helper::has_permission('commission', 'edit')) {
            abort(403,'Access denied');
        }
        $id = Crypt::decrypt($id);
        $id = explode('-', $id);
        $plantype = $id[0];
        $planid   = $id[1];
        $user_id  = $id[2];
        $plans = DB::table('userplan_commissions as pc')->select('pc.*','cd.id as durationid','cd.days','cd.name','ad.id as userid','ad.first_name','ad.promocode')
                ->join('comm_durations as cd','cd.id','=','pc.comm_duration')
                ->join('admins as ad','ad.id','=','pc.user_id')
                ->where('pc.user_id', $user_id)
                ->where('pc.plan_type', $plantype)
                ->where('pc.plan_id', $planid)->get();
        $plandetails = $plans;
        $duration = DB::table('comm_durations')->get();
        $user     = DB::table('admins')->select('id','first_name','promocode')
                    ->where('role','<>',1)->get();
        return view('commission.useredit', compact('plandetails','duration','user'));
    }

    /**
    * Show the commission report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_commission_payment_report(Request $request)
    { 
        $loggedin_user = Auth::user();
        $role_details = DB::table('tbl_roles')->where('id', $loggedin_user->role)->first();
        $parent_id = 0;
        if($role_details->short_code == 'ADMIN') {
            $parent_id = 0;
        }
        else {
            $parent_id = $loggedin_user->id;
        }
        $dealers = Admins::where('status', 1)->where('parent_id', $parent_id)->get();
        return view('commission.comm-payment-report', compact('dealers'));
    }

    /**
    * Show the commission report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function commission_payment_report(Request $request)
    { 
        $firstday   = Carbon::now()->firstofmonth()->format('Y-m-d');
        $lastday    = Carbon::now()->lastofmonth()->format('Y-m-d');
        $users      = DB::table('commission_payments as cp')
                        ->select('cp.comm_user',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'), 'a.promocode as promocode',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"))
                        ->join('admins as a', 'a.id', '=', 'cp.comm_user')
                        ->where('cp.user_active',1);

        $loggedin_user = Auth::user();
        $role_details = DB::table('tbl_roles')->where('id', $loggedin_user->role)->first();
        if($role_details->short_code == 'DEALER') {
            $users->where('a.parent_id', $loggedin_user->id);
        }

        $users->groupBy('cp.comm_user')->orderBy('cp.comm_user');

        if($request->promocode != -1){
            $users->where('a.promocode',$request->promocode);
        }
        $datas     = $users->get();
        $monthdata = $users->WhereBetween('cp.payment_date', [$firstday, $lastday])->get();

        if(!$monthdata->isEmpty()){
            $montharray = [];
            foreach ($monthdata as $key => $val) {

                $montharray[$val->comm_user] = $val;
            }
        }

        $tilldata  = DB::table('dealer_pay_history as dh')->select('dh.dealer_id',DB::raw('sum(dh.amount) as paidamount'),DB::raw('sum(dh.balance_amount) as balance'))
                    ->where('dh.paid_on', '<=', Carbon::now())
                    ->groupBy('dh.dealer_id')->orderBy('dh.dealer_id')->get();

        if(!$tilldata->isEmpty()){
            $tillarray = [];
            foreach ($tilldata as $key => $val) {

                $tillarray[$val->dealer_id] = $val;
            }
        }
        $commData = [];
        foreach ($datas as $key => $data) {

            $sub            = [];
            $payamount  = $paidamount = $totalamount = $remainbal = $advancebal = $monthcomm = $topay = $totalpaid = $prevbal =  0;

            $monthpay   = (isset($montharray[$data->comm_user])) ? $montharray[$data->comm_user]->payamount : 0;
            $monthpaid  = (isset($montharray[$data->comm_user])) ? $montharray[$data->comm_user]->paidamount : 0;

            $monthcomm      = $monthpay + $monthpaid;

            $payamount      = $data->payamount;
            $paidamount     = $data->paidamount;
            $totalamount    = $payamount + $paidamount;

            $totpaid     = DB::table('dealer_pay_history')
                            ->select(DB::raw('sum(amount) as totalpaid'))
                            ->where('dealer_id',$data->comm_user)
                            ->groupBy('dealer_id')->first();

            $dealer_pay_history = DealerPayHistory::where('dealer_id', $data->comm_user)
                                  ->latest('paid_on')->first();

            if(isset($totpaid)){

                $totalpaid = intval($totpaid->totalpaid);
            }

            if(isset($dealer_pay_history)){
               $advancebal = $dealer_pay_history->balance_amount;
            }


            $remainbal    = (($totalamount - $totalpaid) + $advancebal);

            $prevbal      = $remainbal - $monthcomm; //prev month commission balance

            $topay        = ($monthcomm  - $advancebal) + $prevbal;

            $sub['userid']          = $data->comm_user;
            $sub['name']            = $data->dealer_name;
            $sub['promocode']       = $data->promocode;
            $sub['total']           = $totalamount;
            $sub['totalpaid']       = $totalpaid;
            $sub['advancebal']      = $advancebal;
            $sub['monthcomm']       = $monthcomm;
            $sub['monthnotpaid']    = $monthpay;
            $sub['topay']           = $topay;
            array_push($commData, $sub); 
        }

        //print_r($commData);
        //die();
        return Datatables::of($commData)->make(true);
    }
    /**
    * Show the commission report based on user.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_commission(Request $request)
    {
        $userid = $request->user_id;
        return view('commission.commission', compact('userid'));
    }
    /**
    * Show the commission report based on user.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_commission_details(Request $request)
    {
        $users   = DB::table('commission_payments as cp')
            ->select('cp.autoplan_id',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"),'cp.created_at',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'),'cp.comm_user')
            ->join('admins as a', 'a.id', '=', 'cp.comm_user')
            ->where('cp.comm_user',$request->userid)
            ->where('cp.user_active',1)
            ->groupBy('cp.autoplan_id')
            ->orderBy('payment_date', 'asc');

        if ($request->from && !empty($request->from)) {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if(!empty($request->to)){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $users->where('cp.payment_date', '>=', $from)
                          ->where('cp.payment_date', '<=', $to);
            }else{
            $users->where('cp.payment_date', '=', $from);
            }
        }
        $users = $users->get();
        $userDetails = [];
        foreach ($users as $data) {
            $payamount  = $paidamount = $totalamount = 0;
            $payamount  = $data->payamount;
            $paidamount = $data->paidamount;
            $totalamount = $payamount + $paidamount;
            $autoplan = $data->autoplan_id;
            $getplan  = AutoPlan::find($autoplan)->plan->plan_name;
            $data->planname     = $getplan;
            $data->pay_amount   = $payamount;
            $data->paid_amount  =  $paidamount;
            $data->total_amount = $totalamount;
            array_push($userDetails, $data);
        }
        return Datatables::of($userDetails)
                ->editColumn('created_at', function ($date) {
                        return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                        })
                ->editColumn('action', function ($data) {
                        $userdata = Crypt::encrypt($data->comm_user.'-'.$data->autoplan_id);
                        return '<button type="submit" data-id="'.$userdata.'" class="btn btn-success btn-xs comm-breakdown" title="View Details"><i class="fa fa-eye"></i></button>';
                        })
                ->make(true);
    }
    /**
    * Show the commission breakdown.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_breakdown(Request $request)
    {
       $val = Crypt::decrypt($request->datas);
       $val = explode('-', $val);
       $userid =  $val[0];
       $planid =  $val[1];
       $details   = DB::table('commission_payments')
            ->select('pay_amount','amount_paid','payment_date')
            ->where('comm_user',$userid)
            ->where('autoplan_id',$planid)
            ->where('user_active',1)
            ->orderBy('payment_date', 'asc')
            ->get();
        return view('commission.breakdown',compact('details'));
       
    }


    /**
    * Pay Commission to dealer
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function pay_commission(Request $request)
    { 
        $dealer = Admins::where('id',$request->dealer)->where('status', 1)->first();

        if (!$dealer) {
            return response()->json(['status' => "failure", 'message' => 'Invalid Dealer']);
        }

        $dealer_type = $request->dealer_type;
        $loggedin_user = Auth::user();

        $dealer_pay_history = DealerPayHistory::where('dealer_id', $dealer->id)
            ->latest('paid_on')->first();

        $previous_balance = 0;
        if($dealer_pay_history)
            $previous_balance = $dealer_pay_history->balance_amount;

        $payment_amount = $request->pay_amount;
        $balance_amount = $request->pay_amount + $previous_balance;

        DB::beginTransaction();

        try {

            if($dealer_type == 'dealer') {
                $pending_payments   = PaymentCommission::where('comm_user', $dealer->id)
                    ->where('is_paid', 0)
                    ->where('user_active', 1)
                    ->orderBy('payment_date', 'asc')->get();

                foreach ($pending_payments as $record) {
                    $payment = PaymentCommission::where('id', $record->id)->first();
                    $amount = $record->pay_amount;
                    if($balance_amount > 0) {
                        if($balance_amount >= $amount){
                            $record->pay_amount = 0;
                            $record->amount_paid = $record->amount_paid + $amount;
                            $record->is_paid = 1;
                            $balance_amount -= $amount;
                        }
                        else {
                            $record->pay_amount = $amount - $balance_amount;
                            $record->amount_paid = $record->amount_paid + $balance_amount;
                            $balance_amount = 0;
                        }

                        $record->paid_on = Carbon::now();
                        $record->save();
                    }
                    else {
                        break;
                    }
                }
            }
            else if($dealer_type == 'staff') {
                $pending_payments   = StaffCommissionPayment::where('comm_staff', $dealer->id)
                    ->where('is_paid', 0)
                    ->orderBy('payment_date', 'asc')->get();

                foreach ($pending_payments as $record) {
                    $payment = StaffCommissionPayment::where('id', $record->id)->first();
                    $amount = $record->pay_amount;
                    if($balance_amount > 0) {
                        if($balance_amount >= $amount){
                            $record->pay_amount = 0;
                            $record->paid_amount = $record->paid_amount + $amount;
                            $record->is_paid = 1;
                            $balance_amount -= $amount;
                        }
                        else {
                            $record->pay_amount = $amount - $balance_amount;
                            $record->paid_amount = $record->paid_amount + $balance_amount;
                            $balance_amount = 0;
                        }

                        $record->paid_on = Carbon::now();
                        $record->save();
                    }
                    else {
                        break;
                    }
                }
            }
            

            $new_payment = new DealerPayHistory();
            $new_payment->dealer_id = $dealer->id;
            $new_payment->amount = $payment_amount;
            $new_payment->balance_amount = $balance_amount;
            $new_payment->paid_on = Carbon::now();
            $new_payment->who_paid = $loggedin_user->id;
            $new_payment->save();


            DB::commit();

            return response()->json(['status' => "success", 'message' => 'Commission paid successfully']);  

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => "failure", 'message' => 'Failed to update']);
        }
        
    }
    /**
    * Pay Commission to staff
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_staff_list(Request $request)
    { 
        $dealers = DB::table('admins as a')
            ->join('tbl_roles as r', 'a.role', '=', 'r.id')
            ->select('a.id as user_id', 'a.first_name', 'a.last_name', 'a.promocode')
            ->where('a.status', 1)
            ->where(function($q) {
                $q->where('r.short_code', 'RI_STAFF')
                    ->orWhere('r.short_code', 'AVOO_STAFF');
            })
            ->get();
        return view('commission.staffcommission', compact('dealers'));
    }
    /**
    * Pay Commission to generate
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_staff_generate(Request $request)
    { 
        $firstday   = Carbon::now()->firstofmonth()->format('Y-m-d');
        $lastday    = Carbon::now()->lastofmonth()->format('Y-m-d');
        // $firstday   = Carbon::parse('2019-09-01')->firstofmonth()->format('Y-m-d');
        // $lastday    = Carbon::parse('2019-09-01')->lastofmonth()->format('Y-m-d');
        // General commission
        $getdata    = DB::table('tbl_sim_request as sr')
                      ->select('sl.stock_id','ad.id as staffid','ad.role')
                      ->join('tbl_sim_list as sl', 'sl.request_id', '=', 'sr.id')
                      ->join('admins as ad','ad.promocode','=','sr.promocode')
                      ->join('tbl_roles as r', 'ad.role', '=', 'r.id')
                      ->whereNotNull('sr.promocode')
                      ->where(function($q) {
                            $q->where('r.short_code', 'RI_STAFF')
                                ->orWhere('r.short_code', 'AVOO_STAFF');
                        })
                      ->WhereBetween('sr.created_at', [$firstday, $lastday])
                      ->orderBy('sr.created_at','asc')->get();
        $grouped    = [];             
        foreach ($getdata as $key => $value) {
            if(!isset($grouped[$value->staffid]))
                $grouped[$value->staffid] = [];
            array_push($grouped[$value->staffid],  $value);
        }
        foreach ($grouped as $key =>$details) {
                $target = 1;
                $check  = true;

                foreach ($details as $comm) {
                    $getcommission = StaffCommission::where('role_id',$comm->role)
                                    ->where(function ($query) use ($target) {
                                        $query->where('target_from', '<=', $target);
                                        $query->where('target_to', '>=', $target);
                                    })->first();

                    if(!isset($getcommission->commission)){
                        if($check){
                            $getrange = DB::table('staff_commission')->where('role_id',$comm->role)
                                        ->where('target_from',$target)->first();
                            if( isset($getrange) ){
                                if($getrange->target_to == 0 || is_null($getrange->target_to)){
                                  $commission =  $getrange->commission; 
                                  $check      =  false;
                                }
                            }else{
                               $commission  =  0; 
                               $check       =  true; 
                            }
                        }
                    }else{
                        $commission    = $getcommission->commission;
                }

                if($commission != 0){
                  $paymentDate                  = Carbon::now()->format('Y-m-d');
                  $commArray['stock_id']        = $comm->stock_id;
                  $commArray['comm_staff']      = $comm->staffid;
                  $commArray['payment_date']    = $paymentDate;
                  $commArray['pay_amount']      = $commission;
                  $commArray['comm_rate']       = $commission;
                  $commArray['comm_for']        = 1; // General commission
                  StaffCommissionPayment::firstOrCreate(['stock_id' => $comm->stock_id,'comm_staff' => $comm->staffid,'comm_for' => 1], $commArray);
                }
            $target++;
            }
        }

         //porting commission
        $portdata   = DB::table('tbl_porting as tp')
                      ->select('stock_id','ad.id as staffid','ad.role')
                      ->join('admins as ad','ad.promocode','=','tp.promocode')
                      ->join('tbl_roles as r', 'ad.role', '=', 'r.id')
                      ->whereNotNull('tp.promocode')
                      ->where(function($q) {
                            $q->where('r.short_code', 'RI_STAFF')
                                ->orWhere('r.short_code', 'AVOO_STAFF');
                        })
                      ->WhereBetween('tp.created_at', [$firstday, $lastday])
                      ->Where('tp.status',4)
                      ->orderBy('tp.created_at','asc')->get();
        $ported    = [];             
        foreach ($portdata as $key => $value) {
            if(!isset($ported[$value->staffid]))
                $ported[$value->staffid] = [];
            array_push($ported[$value->staffid],  $value);
        }

        foreach ($ported as $key =>$details) {
                $target = 1;
                $check  = true;

                foreach ($details as $comm) {
                    $getcommission = StaffPortCommission::where('role_id',$comm->role)
                                    ->where(function ($query) use ($target) {
                                        $query->where('target_from', '<=', $target);
                                        $query->where('target_to', '>=', $target);
                                    })->first();

                    if(!isset($getcommission->commission)){
                        if($check){
                            $getrange = DB::table('staff_porting_commission')
                                        ->where('role_id',$comm->role)
                                        ->where('target_from',$target)->first();
                            if( isset($getrange) ){
                                if($getrange->target_to == 0 || is_null($getrange->target_to)){
                                  $commission =  $getrange->commission; 
                                  $check      =  false;
                                }
                            }else{
                               $commission  =  0; 
                               $check       =  true; 
                            }
                        }
                    }else{
                        $commission    = $getcommission->commission;
                }
                if($commission != 0){
                  $paymentDate                  = Carbon::now()->format('Y-m-d');
                  $commArray['stock_id']        = $comm->stock_id;
                  $commArray['comm_staff']      = $comm->staffid;
                  $commArray['payment_date']    = $paymentDate;
                  $commArray['pay_amount']      = $commission;
                  $commArray['comm_rate']       = $commission;
                  $commArray['comm_for']        = 2; // porting commission
                  StaffCommissionPayment::firstOrCreate(['stock_id' => $comm->stock_id,'comm_staff' => $comm->staffid,'comm_for' => 2], $commArray);
                }
            $target++;
            }
        }

        //pay credit commission
        $paydata    = DB::table('user_payments as up')
                      ->select(DB::raw('sum(up.amount) as payamount'),'ad.id as staffid','ad.role')
                      ->join('admins as ad','ad.promocode','=','up.promocode')
                      ->join('tbl_roles as r', 'ad.role', '=', 'r.id')
                      ->whereNotNull('up.promocode')
                      ->where(function($q) {
                            $q->where('r.short_code', 'RI_STAFF')
                            ->orWhere('r.short_code', 'AVOO_STAFF');
                        })
                      ->WhereBetween('up.created_at', [$firstday, $lastday])
                      ->Where('up.status',1) //include only success details
                      ->groupBy('up.promocode')->get();

        foreach ($paydata as $key =>$details) {
            $totalpay       = 0;
            $totalpay       = round($details->payamount);
            $getcommission  = StaffPaycreditCommission::where('role_id',$details->role)
                                ->where(function ($query) use ($totalpay) {
                                    $query->where('target_from', '<=', $totalpay);
                                    $query->where('target_to', '>=', $totalpay);
                                })->first();
            if(!isset($getcommission->commission)){
                $getrange = DB::table('staff_paycredit_commission')
                                    ->where('role_id',$details->role)
                                    ->where('target_from','<=',$totalpay)->first();
                        if( isset($getrange) ){
                            if($getrange->target_to == 0 || is_null($getrange->target_to)){
                              $commission =  $getrange->commission; 
                            }
                        }else{
                           $commission  =  0; 
                        }

                }else{
                    $commission    = $getcommission->commission;
                }
            if($commission != 0){
                  $paymentDate                  = Carbon::now()->format('Y-m-d');
                  $commArray['stock_id']        = 0;
                  $commArray['comm_staff']      = $details->staffid;
                  $commArray['payment_date']    = $paymentDate;
                  $commArray['pay_amount']      = $commission;
                  $commArray['comm_rate']       = $commission;
                  $commArray['comm_for']        = 3; // paycredit commission
                  StaffCommissionPayment::firstOrCreate(['comm_staff' => $details->staffid,'comm_for' => 3], $commArray);
            }
        }

        return response()->json(['status' => "success", 'message' => 'Commission generated']);
    }
    /**
    * Show the staff commission report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_staffpayment_report(Request $request)
    { 
        $firstday   = Carbon::now()->firstofmonth()->format('Y-m-d');
        $lastday    = Carbon::now()->lastofmonth()->format('Y-m-d');
        $users      = DB::table('staffcommission_payments as sp')
            ->select('sp.comm_staff',DB::raw('sum(sp.pay_amount) as payamount'),DB::raw('sum(sp.paid_amount) as paidamount'), 'a.promocode as promocode',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS staff_name"))
            ->join('admins as a', 'a.id', '=', 'sp.comm_staff')
            ->groupBy('sp.comm_staff')->orderBy('sp.comm_staff');

        if($request->promocode != -1){

            $users->where('a.promocode',$request->promocode);
        }
        $datas     = $users->get();
        $monthdata = $users->WhereBetween('sp.payment_date', [$firstday, $lastday])->get();

        if(!$monthdata->isEmpty()){
            $montharray = [];
            foreach ($monthdata as $key => $val) {

                $montharray[$val->comm_staff] = $val;
            }
        }

        $commData = [];
        foreach ($datas as $key => $data) {

            $sub            = [];
            $payamount  = $paidamount = $totalamount = $remainbal = $advancebal = $monthcomm = $topay = $totalpaid = $prevbal =  0;

            $monthpay   = (isset($montharray[$data->comm_staff])) ? $montharray[$data->comm_staff]->payamount : 0;
            $monthpaid  = (isset($montharray[$data->comm_staff])) ? $montharray[$data->comm_staff]->paidamount : 0;

            $monthcomm      = $monthpay + $monthpaid;

            $payamount      = $data->payamount;
            $paidamount     = $data->paidamount;
            $totalamount    = $payamount + $paidamount;

            $totpaid     = DB::table('dealer_pay_history')
                            ->select(DB::raw('sum(amount) as totalpaid'))
                            ->where('dealer_id',$data->comm_staff)
                            ->groupBy('dealer_id')->first();

            $dealer_pay_history = DealerPayHistory::where('dealer_id', $data->comm_staff)
                                  ->latest('paid_on')->first();

            if(isset($totpaid)){

                $totalpaid = intval($totpaid->totalpaid);
            }

            if(isset($dealer_pay_history)){
               $advancebal = $dealer_pay_history->balance_amount;
            }


            $remainbal    = (($totalamount - $totalpaid) + $advancebal);

            $prevbal      = $remainbal - $monthcomm; //prev month commission balance

            $topay        = ($monthcomm  - $advancebal) + $prevbal;

            $sub['userid']          = $data->comm_staff;
            $sub['name']            = $data->staff_name;
            $sub['promocode']       = $data->promocode;
            $sub['total']           = $totalamount;
            $sub['totalpaid']       = $totalpaid;
            $sub['advancebal']      = $advancebal;
            $sub['monthcomm']       = $monthcomm;
            $sub['monthnotpaid']    = $prevbal;
            $sub['topay']           = $topay;
            array_push($commData, $sub); 
        }
        return Datatables::of($commData)->make(true);
    }
    /**
    * Show the commission report based on staff.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function staff_commission(Request $request)
    {
        $userid = $request->user_id;
        $staff = DB::table('admins')->select('id','first_name','last_name')
                 ->where('role','!=',1)->get();
        return view('commission.staff-comm-detail', compact('userid','staff'));
    }
    /**
    * Show the commission report based on staff.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function staff_commission_details(Request $request)
    {
        $users   = DB::table('staffcommission_payments as scp')
            ->select('ss.phone_number',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS staff_name"),'scp.created_at',DB::raw('sum(scp.pay_amount) as payamount'),DB::raw('sum(scp.paid_amount) as paidamount'),'scp.comm_staff','scp.comm_for')
            ->join('admins as a', 'a.id', '=', 'scp.comm_staff')
            ->leftJoin('tbl_sim_stock as ss', 'ss.id', '=', 'scp.stock_id')
            ->groupBy('scp.id')
            ->orderBy('scp.id', 'asc');
            // ->orderBy('scp.comm_for', 'asc')
            // ->orderBy('payment_date', 'asc');

        if ($request->staff != -1) {
            $users->where('scp.comm_staff', $request->staff);            
        }else{
            if(isset($request->userid) && $request->userid != ""){
                $users->where('scp.comm_staff',$request->userid);
            }
        }
        if ($request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $users->where('scp.payment_date', '>=', $from)
                          ->where('scp.payment_date', '<=', $to);
            }else{
                $users->whereRaw("date_format(scp.payment_date, '%Y-%m-%d')  = '$from'");
            }
        }
        
        $users = $users->get();
        $userDetails = [];
        if(!empty($users[0]->staff_name)){
        foreach ($users as $data) {
            $payamount  = $paidamount = $totalamount = 0;
            $payamount  = $data->payamount;
            $paidamount = $data->paidamount;
            $totalamount = $payamount + $paidamount;
            $data->phonenumber  = $data->phone_number;
            $data->total_amount = $totalamount;
            $data->paid_amount  =  $paidamount;
            $data->pay_amount   = $payamount;
            $data->commfor      = $data->comm_for;
            $data->date         = $data->created_at;
            array_push($userDetails, $data);
        }
        }
        $result =  Datatables::of($userDetails)
                ->editColumn('date', function ($date) {
                        return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                        })
                ->editColumn('comm_for', function ($data) {
                    $stat = "";
                    switch ($data->comm_for) {
                        case 1:
                            $stat = "General";
                            break;
                        case 2:
                            $stat = "Porting";
                            break;
                        case 3:
                            $stat = "Pay Credit";
                            break;
                        default:
                            $stat = "";
                            break;
                    }
                return $stat;
                })
                ->make(true);
        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['staff_name','phonenumber','pay_amount','paid_amount','total_amount','comm_for','date']);
             });
            $datas->prepend(array('NAME','TYPE','PHONE','COMMISSION','COMMISSION PAID','COMMISSION TO PAY','DATE'));
            return Excel::download(new CustomExport($datas->toArray()), 'staffcommdetails.csv');
        }else{
            return $result;
        }
    }
    
    /**
    * Porting Commission to staff
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_staff_portlist(Request $request)
    { 
        $roles = DB::table('tbl_roles')->get();
        return view('commission.staffportcommission', compact('roles'));
    }
    /**
    * Porting Commission list to staff
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function staff_port_commission_list(Request $request)
    {

        $role_commission = DB::table('tbl_roles as r')
            ->join('staff_porting_commission as spc', 'spc.role_id', '=', 'r.id');

        if($request->role && !empty($request->role)) {
            $role_commission->where('spc.role_id', $request->role);
        }

        $role_commission = $role_commission->get();

        return Datatables::of($role_commission)
                ->editColumn('action', function ($data) {
                return '<a data-toggle="tooltip" title="Edit"  href="add-staff-port-commission/'.Crypt::encrypt($data->role_id).'" class="fa fa-pencil-square-o editbtn"></a>';
                })->make(true);
    }
    /**
    * Interface to add staff port commission
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_staff_port_commission(Request $request)
    {
        $details = [];
        if(!empty($request->id) || isset($request->id)){

            $roleid     = Crypt::decrypt($request->id);
            $details    = DB::table('staff_porting_commission')->where('role_id',$roleid)->get();
        }
        $roles = DB::table('tbl_roles')->get();
        return view('commission.add-staff-port-comm', compact('roles','details'));
    }

    /**
    * Function Update plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_staff_port_commission(Request $request)
    { 

            if (!Helper::has_permission('commission')) {
                abort(403,'Access denied');
            }
            $msg = "Staff porting commission Created successfully!";
            if(isset($request->id)) {
                DB::table('staff_porting_commission')->where('role_id', $request->id)->delete();
                $msg = "Staff porting commission Updated successfully!";
            }
            $data = $request->all();
            unset($data['_token']);
            foreach ($data['commission_rate'] as $key => $val) {
            $dataval = array();
            $dataval['role_id']       = $data['role'];
            $dataval['target_from']   = $data['target_from'][$key];
            $dataval['target_to']     = $data['target_to'][$key] != "" ? $data['target_to'][$key] : 0;
            $dataval['commission'] = $data['commission_rate'][$key];

            $id =  StaffPortCommission::updateOrCreate(['role_id' => $data['role'],'target_from' => $data['target_from'][$key]], $dataval);
                
            }

            if($id->wasRecentlyCreated){
                $roleid = Crypt::encrypt($data['role']);
                return redirect('/add-staff-port-commission/'.$roleid)->with('message',$msg);
            }
            else{
                return redirect()->back()->with('error', 'Staff porting Commission already exists');
            }
    }
    /**
    * pay credit Commission to staff
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_staff_paycreditlist(Request $request)
    { 
        $roles = DB::table('tbl_roles')->get();
        return view('commission.staffpaycreditcomm', compact('roles'));
    }
    /**
    * pay credit Commission list to staff
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function staff_paycredit_commission_list(Request $request)
    {

        $role_commission = DB::table('tbl_roles as r')
            ->join('staff_paycredit_commission as spc', 'spc.role_id', '=', 'r.id');

        if($request->role && !empty($request->role)) {
            $role_commission->where('spc.role_id', $request->role);
        }

        $role_commission = $role_commission->get();

        return Datatables::of($role_commission)
                ->editColumn('action', function ($data) {
                return '<a data-toggle="tooltip" title="Edit"  href="add-staff-paycredit-commission/'.Crypt::encrypt($data->role_id).'" class="fa fa-pencil-square-o editbtn"></a>';
                })->make(true);
    }
    /**
    * Interface to add staff paycredit commission
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_staff_paycredit_commission(Request $request)
    {
        $details = [];
        if(!empty($request->id) || isset($request->id)){

            $roleid     = Crypt::decrypt($request->id);
            $details    = DB::table('staff_paycredit_commission')->where('role_id',$roleid)->get();
        }
        $roles = DB::table('tbl_roles')->get();
        return view('commission.add-staff-paycredit-comm', compact('roles','details'));
    }
    /**
    * Function Update plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_staff_paycredit_commission(Request $request)
    { 

            if (!Helper::has_permission('commission')) {
                abort(403,'Access denied');
            }
            $msg = "Staff paycredit commission Created successfully!";
            if(isset($request->id)) {
                DB::table('staff_paycredit_commission')->where('role_id', $request->id)->delete();
                $msg = "Staff paycredit commission Updated successfully!";
            }
            $data = $request->all();
            unset($data['_token']);
            foreach ($data['commission_rate'] as $key => $val) {
            $dataval = array();
            $dataval['role_id']       = $data['role'];
            $dataval['target_from']   = $data['target_from'][$key];
            $dataval['target_to']     = $data['target_to'][$key];
            $dataval['commission'] = $data['commission_rate'][$key];

            $id =  StaffPaycreditCommission::updateOrCreate(['role_id' => $data['role'],'target_from' => $data['target_from'][$key]], $dataval);
                
            }

            if($id->wasRecentlyCreated){
                $roleid = Crypt::encrypt($data['role']);
                return redirect('/add-staff-paycredit-commission/'.$roleid)->with('message',$msg);
            }
            else{
                return redirect()->back()->with('error', 'Staff paycredit Commission already exists');
            }
    }
    /**
    * Function delete plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_plan_comm(Request $request)
    { 
        $planid = $request->id;
        $delete = DB::table('plan_commissions')
            ->where('id',$planid)->delete();
        if($delete){
           return response()->json(['status' => "success", 'message' => 'Plan Deleted']); 
        }else{
            return response()->json(['status' => "failure", 'message' => 'Technical error']); 
        }
    }
    /**
    * Function delete user plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_userplan_comm(Request $request)
    { 
        $planid = $request->id;
        $delete = DB::table('userplan_commissions')
            ->where('id',$planid)->delete();
        if($delete){
           return response()->json(['status' => "success", 'message' => 'User Plan Deleted']); 
        }else{
            return response()->json(['status' => "failure", 'message' => 'Technical error']); 
        }
    }
}
