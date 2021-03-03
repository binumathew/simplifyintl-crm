<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Helper;
use DB;
use Validator;
use DataTables;
use Crypt;
use Carbon;
use Auth;

use \App\Models\PlanCommission;
use \App\Models\UserCommission;
use \App\Models\Admins;
use \App\Models\DealerRevenue;
use \App\Models\Clawback;
use \App\Models\DealerPayHistory;
use \App\Models\AutoPlan;
use \App\Models\User;
use App\Models\PaymentCommission;
use App\Models\ClawbackPayments;

class CommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
    * Plan commission listing
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_plan(Request $request){

    	if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }
        return view('commission.planlist');
    }
    /**
    * Show the plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_plan_list(Request $request)
    { 
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

        $querydatas = PlanCommission::query()
	                  ->groupBy('plan_type','plan_id');

        if ($request->has('plan_type') && $request->get('plan_type') != "") {
                $querydatas->where('plan_type', $request->get('plan_type'));
        }
        if ($request->has('plan_id') && $request->get('plan_id') != "") {
                $querydatas->where('plan_id', $request->get('plan_id'));
        }
        
        $result = Datatables::of($querydatas)
        			->editColumn('plan_type', function ($data) {
                     return $data->plan_type == 1 ? 'Plan' : 'Bundle';
                    })
                    ->addColumn('plan_name', function (PlanCommission $pl) {
                        $plan = $pl->plan;
                        $planname = isset($plan->plan_name) ? $plan->plan_name : "";
                        $simcount = isset($plan->sim_count) ? $plan->sim_count : "";
                        return $planname.' '.$simcount;
                    })
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                    ->addColumn('actions', function ($data) {
                        $edit = $delete = "";
                        if (Helper::has_permission('commission','edit')) {
                            $edit = '<a href="'.url('/commplan-manage/'.Crypt::encrypt($data->plan_id.'-'.$data->plan_type)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a>';
                        } 
                        if (Helper::has_permission('commission','delete')) {
                            $delete = '<a href="javascript:void(0);" class="text-muted delete_comm" data-id="'.Crypt::encrypt($data->plan_type.'-'.$data->plan_id).'"  data-type="'.base64_encode('plan_comm').'"   data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                        }    
                    $btn = $edit.'<a href="javascript:void(0);" class="text-muted view_comm" data-id="'.Crypt::encrypt($data->plan_type.'-'.$data->plan_id).'" data-type="'.base64_encode('plan_comm').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a>'.$delete;
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * Create the plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function commplan_manage($id = "")
    { 
        if (!Helper::has_permission('commission','create')) {
            abort(403,'Access denied');
        }
        $duration = DB::table('comm_durations')->get();
        $plan           = [];
        if($id){
        	$req           = explode("-", Crypt::decrypt($id));
            $plan_id       = $req[0];
            $plan_type     = $req[1];

            $getplan  = PlanCommission::where(['plan_id'=>$plan_id,'plan_type'=>$plan_type])->get();
            if($getplan->isNotEmpty()){
                $plan = $getplan;
            }
        }  
        return view('commission.plan-manage',compact('duration','plan'));
    }
    /**
    * get plans from the plan type provided
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_getplan(Request $request)
    {
        $type     = $request->type;
        switch ($type) {
            case 1:
                //$dealerid = isset($request->dealer) ? Crypt::decrypt($request->dealer) : 1001;
                $plans = DB::table('tbl_plans')->select(['id','plan_name'])->get();
                break;
            case 2:
                $plans = DB::table('tbl_bundles')->select(['id','plan_name','sim_count'])->get();
                break;
            default:
                //$dealerid = isset($request->dealer) ? Crypt::decrypt($request->dealer) : 1001;
                $plans = DB::table('tbl_plans')->select(['id','plan_name'])->get();
                break;
        }
        return json_encode($plans);
    }
    /**
    * Function to add & Update plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_plancommission(Request $request)
    { 
      	$rules = array('plan_type' => 'required','plan_id' => 'required','comm_rate.*' => 'required','comm_type.*' => 'required','comm_duration.*'=>'required','status.*'=>'required');
      	$messages = array(
	        'plan_type.required' => 'Please provide plan type',
	        'plan_id.required' => 'Please provide plan name',
	        'comm_rate.*.required' => 'Please provide commission rate',
	        'comm_type.*.required' => 'Please provide commission type',
	        'comm_duration.*.required' => 'Please provide commission duration',
	        'status.*.required' => 'Please provide commission status',
	        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $edit_id 	= $request->edit_id;
            $data 		= $request->except(['_token','edit_id']);

            if($edit_id != ""){
            	if (!Helper::has_permission('commission', 'edit')) {
                	abort(403,'Access denied');
            	}
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
	            	return response()->json(['status'=>200,'msg'=>'Plan commission updated successfully!','redirect'=>'/comm-plan']);
	            } else {
	            	return response()->json(['status'=>422,'msg'=>['Failed to update this plan commission, please try again']]);
	            }
            }else{
                if (!Helper::has_permission('commission','create')) {
                    abort(403,'Access denied');
                }

                foreach ($data['comm_rate'] as $key => $val) {
		            $dataval = array();
		            $dataval['plan_type'] = $data['plan_type'];
		            $dataval['plan_id']   = $data['plan_id'];
		            $dataval['comm_rate'] = $data['comm_rate'][$key];
		            $dataval['comm_type'] = $data['comm_type'][$key];
		            $dataval['comm_duration'] = $data['comm_duration'][$key];
		            $dataval['status']        = $data['status'][$key];  

		            $id 	=  PlanCommission::firstOrCreate(['plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
            	}
            	if($id->wasRecentlyCreated){
            		return response()->json(['status'=>200,'msg'=>'plan commission created successfully..','redirect'=>'/comm-plan']);
                }else{
                	return response()->json(['status'=>422,'msg'=>['Plan commission already exists']]);
                }
                
            }
        }
    }
    /**
    * Delete the plan commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_comm(Request $request)
    { 
        if (!Helper::has_permission('commission','delete')) {
            abort(403,'Access denied');
        }
        $req      = explode("-",Crypt::decrypt($request->id));
        $type     = base64_decode($request->type);
        switch ($type) {
            case 'plan_comm':
                $delete = PlanCommission::where(['plan_type'=>$req[0],'plan_id'=>$req[1]])->delete();
                $tableid = 'commPlan-table';
                break;
            case 'dealer_comm':
                $delete = UserCommission::where(['user_id'=>$req[0],'plan_type'=>$req[1],'plan_id'=>$req[2]])->delete();
                $tableid = 'commDealer-table';
                break;
            default:
                # code...
                break;
        }
        return response()->json(['status'=>200,'msg'=>'Commission deleted successfully..','table'=>$tableid]);
    }
    /**
    * Dealer Plan commission listing
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_plan_dealer(Request $request){

        $querydatas   = DB::table('admins')->select('id','first_name','last_name','promocode');
        $dealer  = $querydatas;
        $revenue = $querydatas;
        if(Helper::has_permission('commission')){

            $dealer  = $dealer->where('role','<>',1)->get();
            $revenue = $revenue->where('role','<>',1)->get();

        }else if(Helper::has_permission('commission','view_own')){
            $dealer = $dealer->where(function ($query) {
                            $query->where('id', Auth::id())
                                  ->orWhere('parent_id', Auth::id());
                        })->get();

            $revenue = $revenue->where(function ($query) {
                            $query->where('parent_id', Auth::id());
                        })->get();
        }else{
           abort(403,'Access denied'); 
        }
        $haschild    = false;
        if(DB::table('admins')->where('parent_id',Auth::id())->exists()){
            $haschild = true;
        }
        return view('commission.dealer-planlist',compact('dealer','revenue','haschild'));
    }
    /**
    * ADD & EDIT the Dealer commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function commdealer_manage($id = "")
    { 
        if (!Helper::has_permission('commission','create')) {
                abort(403,'Access denied');
        }
        $duration = DB::table('comm_durations')->get();
        $plan           = [];
        $dealer         = [];
        if($id){
            $req           = explode("-", Crypt::decrypt($id));
            $user_id       = $req[0];
            $plan_type     = $req[1];
            $plan_id       = $req[2];
            
            $getplan  = UserCommission::where(['user_id'=>$user_id,'plan_id'=>$plan_id,'plan_type'=>$plan_type])->get();
            if($getplan->isNotEmpty()){
                $plan = $getplan;
            }
        }
        $dealer     = DB::table('admins')->select('id','first_name','promocode');
        if(Helper::has_permission('commission')){
            $dealer = $dealer->where('role','<>',1)->get();
        }else if(Helper::has_permission('commission','view_own')){
            $dealer = $dealer->where(function ($query) {
                            $query->where('parent_id', Auth::id());
                        })->get();
        }else{
           abort(403,'Access denied'); 
        }
        return view('commission.dealer-manage',compact('duration','plan','dealer'));
    }
    /**
    * Function to add & Update dealer commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_dealercommission(Request $request)
    { 
        
        $rules = array('dealer_id' => 'required','plan_type' => 'required','plan_id' => 'required','comm_rate.*' => 'required','comm_type.*' => 'required','comm_duration.*'=>'required','status.*'=>'required');
        $messages = array(
            'dealer_id.required' => 'Please provide dealer',
            'plan_type.required' => 'Please provide plan type',
            'plan_id.required' => 'Please provide plan name',
            'comm_rate.*.required' => 'Please provide commission rate',
            'comm_type.*.required' => 'Please provide commission type',
            'comm_duration.*.required' => 'Please provide commission duration',
            'status.*.required' => 'Please provide commission status',
            );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            if (!Helper::has_permission('commission','edit')) {
                abort(403,'Access denied');
            }
            $edit_id    = $request->edit_id;
            $data       = $request->except(['_token','edit_id']);
            $dealer_id  = Crypt::decrypt($data['dealer_id']);
            if($edit_id != ""){
                if (!Helper::has_permission('commission', 'edit')) {
                    abort(403,'Access denied');
                }
                UserCommission::where('user_id',$dealer_id)
                    ->where('plan_type',$data['plan_type'])
                    ->where('plan_id',$data['plan_id'])->delete();

                foreach ($data['comm_rate'] as $key => $val) {
                    $dataval = array();
                    $dataval['user_id']   = $dealer_id;
                    $dataval['plan_type'] = $data['plan_type'];
                    $dataval['plan_id']   = $data['plan_id'];
                    $dataval['comm_rate'] = $data['comm_rate'][$key];
                    $dataval['comm_type'] = $data['comm_type'][$key];
                    $dataval['comm_duration'] = $data['comm_duration'][$key];
                    $dataval['status']        = $data['status'][$key];

                    $id =  UserCommission::updateOrCreate(['user_id' => $dealer_id,'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                }  
                if($id) {
                    return response()->json(['status'=>200,'msg'=>'Dealer commission updated successfully!','redirect'=>'/comm-plan-dealer']);
                } else {
                    return response()->json(['status'=>422,'msg'=>['Failed to update this dealer commission, please try again']]);
                }
            }else{
                if (!Helper::has_permission('commission','create')) {
                    abort(403,'Access denied');
                }
                foreach ($data['comm_rate'] as $key => $val) {
                    $dataval = array();
                    $dataval['user_id']   = $dealer_id;
                    $dataval['plan_type'] = $data['plan_type'];
                    $dataval['plan_id']   = $data['plan_id'];
                    $dataval['comm_rate'] = $data['comm_rate'][$key];
                    $dataval['comm_type'] = $data['comm_type'][$key];
                    $dataval['comm_duration'] = $data['comm_duration'][$key];
                    $dataval['status']        = $data['status'][$key];  

                    $id =  UserCommission::firstOrCreate(['user_id' => $dealer_id,'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id'],'comm_duration' => $data['comm_duration'][$key]], $dataval);
                }
                if($id->wasRecentlyCreated){
                    return response()->json(['status'=>200,'msg'=>'Dealer commission created successfully..','redirect'=>'/comm-plan-dealer']);
                }else{
                    return response()->json(['status'=>422,'msg'=>['Dealer commission already exists']]);
                }
                
            }
        }
    }
    /**
    * Show the dealer commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_dealer_list(Request $request)
    { 
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

        $admins     = Admins::find(Auth::id());
        $role       = $admins->roles->short_code;

        $querydatas = UserCommission::query()
                ->select('userplan_commissions.*','ad.id as userid','ad.first_name','ad.promocode')
                ->join('admins as ad','ad.id','=','userplan_commissions.user_id');

        if(Helper::has_permission('commission')){

        }else if(Helper::has_permission('commission','view_own')){
            $querydatas = $querydatas->where('ad.id', Auth::id())
                            ->orWhere('ad.parent_id', Auth::id());  
        }
        $querydatas = $querydatas->groupBy('userplan_commissions.user_id','userplan_commissions.plan_id');

        if ($request->has('plan_type') && $request->get('plan_type') != "") {
                $querydatas->where('plan_type', $request->get('plan_type'));
        }
        if ($request->has('plan_id') && $request->get('plan_id') != "") {
                $querydatas->where('plan_id', $request->get('plan_id'));
        }
        if ($request->has('dealer_id') && $request->get('dealer_id') != "") {
                $querydatas->where('user_id', crypt::decrypt($request->get('dealer_id')));
        }
        
        $result = Datatables::of($querydatas)
                    ->editColumn('plan_type', function ($data) {
                     return $data->plan_type == 1 ? 'Plan' : 'Bundle';
                    })
                    ->addColumn('plan_name', function (UserCommission $pl) {
                        return $pl->plan->plan_name.' '.$pl->plan->sim_count;
                    })
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                    ->addColumn('actions', function ($data) {
                     $edit = $delete = "";
                     if (Helper::has_permission('commission','edit')) {
                            $edit = '<a href="'.url('/commdealer-manage/'.Crypt::encrypt($data->user_id.'-'.$data->plan_type.'-'.$data->plan_id)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a> ';
                    } 
                    if (Helper::has_permission('commission','delete')) {
                            $delete = '<a href="javascript:void(0);" class="text-muted delete_comm" data-id="'.Crypt::encrypt($data->user_id.'-'.$data->plan_type.'-'.$data->plan_id).'"  data-type="'.base64_encode('dealer_comm').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                    }    

                    $btn = $edit.'<a href="javascript:void(0);" class="text-muted view_comm" data-id="'.Crypt::encrypt($data->user_id.'-'.$data->plan_type.'-'.$data->plan_id).'" data-type="'.base64_encode('dealer_comm').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a>'.$delete;
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * View the commissions.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_view(Request $request)
    { 
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

        $req        = explode("-",Crypt::decrypt($request->id));
        $type       = base64_decode($request->type);

        switch ($type) {
            case 'dealer_comm':
                $user_id    = $req[0];
                $plan_type  = $req[1];
                $plan_id    = $req[2];

                $dealer     = [];
                $getcomm    = DB::table('userplan_commissions as upc')->select('upc.*','cd.id as durationid','cd.days','cd.name','ad.id as userid','ad.first_name','ad.promocode')
                        ->join('comm_durations as cd','cd.id','=','upc.comm_duration')
                        ->join('admins as ad','ad.id','=','upc.user_id')
                        ->where(['upc.user_id'=>$user_id,'upc.plan_type'=>$plan_type,'upc.plan_id'=>$plan_id])->get();
                if($getcomm->isNotEmpty()){
                    $plandetails = UserCommission::find($getcomm[0]->id)->plan;
                    $getcomm[0]->plan_name  = $plandetails->plan_name.' '.$plandetails->sim_count;
                    $planprice              = $plandetails->sell_price;
                    $getcomm[0]->plan_price = $planprice;
                    $dealer = $getcomm;
                    $totalcomm = 0;
                    foreach ($dealer as $key => $val) {
                        $totalcomm += ($val->comm_type == 1) ? $val->comm_rate : (($planprice *$val->comm_rate)/100);
                    }
                    $getcomm[0]->totalcomm = $totalcomm;
                }
                $view     = view('commission.create-data',compact('dealer'))->render();
                break;
            case 'plan_comm':
                $plan_type  = $req[0];
                $plan_id    = $req[1];

                $plan = [];
                $getcomm = DB::table('plan_commissions as pc')->select('pc.*','cd.id as durationid','cd.days','cd.name')
                         ->join('comm_durations as cd','cd.id','=','pc.comm_duration')
                         ->where(['pc.plan_type'=>$plan_type,'pc.plan_id'=>$plan_id])->get();

                if($getcomm->isNotEmpty()){
                    $plandetails = PlanCommission::find($getcomm[0]->id)->plan;
                    $getcomm[0]->plan_name  = $plandetails->plan_name.'  '.$plandetails->sim_count;
                    $planprice              = $plandetails->sell_price;
                    $getcomm[0]->plan_price = $planprice;
                    $plan = $getcomm;
                    $totalcomm = 0;
                    foreach ($plan as $key => $val) {
                        $totalcomm += ($val->comm_type == 1) ? $val->comm_rate : (($planprice *$val->comm_rate)/100);
                    }
                    $getcomm[0]->totalcomm = $totalcomm;
                }

                $view     = view('commission.create-data',compact('plan'))->render();
                break;
            default:
                # code...
                break;
        }
        return response()->json(['status'=>200,'page'=>$view]);  
    }
    /**
    * Function get revenue commission.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_revenue(Request $request)
    { 
        $dealer_id = Crypt::decrypt($request->dealer);
        $get = DealerRevenue::select('amount','expiry_at')->where('dealer_id',$dealer_id)->first();
        if(isset($get))
        return response()->json(['status' => 200, 'message' => $get]);
    }
    /**
    * Function to add & Update dealer Revenue.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_revenue(Request $request)
    { 
        if (!Helper::has_permission('commission','create')) {
                abort(403,'Access denied');
        }

        $rules = array('addrev_dealer' => 'required','revenue_amount' => 'required|max:10','expiry_at' => 'required|date_format:d-m-Y');
        $messages = array(
            'addrev_dealer.required' => 'Please provide dealer',
            'revenue_amount.required' => 'Please provide amount',
            'revenue_amount.max' => 'Maximum character limit exceeded',
            'expiry_at.required' => 'Please provide expiry date',
            );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $dealer_id = Crypt::decrypt($request->addrev_dealer);
            if(Auth::id() == $dealer_id){
              return response()->json(['status'=>422,'msg'=>['Cannot assign revenue to yourself']]);  
            }
            $revenue['dealer_id']   = $dealer_id;
            $revenue['amount']      = $request->revenue_amount;
            $revenue['expiry_at']   = Carbon::parse($request->expiry_at)->format('Y-m-d');
            $save = DealerRevenue::updateOrCreate(['dealer_id' => $dealer_id],$revenue);
        if($save->wasRecentlyCreated)
            return response()->json(['status'=>200,'msg'=>'Dealer Revenue Added']);
        else
            return response()->json(['status'=>200,'msg'=>'Dealer Revenue updated']);
        }
    }
    /**
    * Clawback plan 
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function clawback_plan(Request $request){

        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }
        return view('commission.clawback-planlist');
    }
    /**
    * Show the clawback plan.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function clawback_list(Request $request)
    { 
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

        $querydatas = Clawback::query();
        $type       = $request->data_type;

        if($type == "plan"){
            $querydatas = $querydatas->where('admin_id',0); 
        }
        if($type == "dealer"){
            $querydatas->join('admins as ad','ad.id','=','clawback.admin_id');

            if(Helper::has_permission('commission')){
                $querydatas = $querydatas->where('admin_id',"<>",0);

            }else if(Helper::has_permission('commission','view_own')){
                $querydatas = $querydatas->where('ad.id', Auth::id())
                            ->orWhere('ad.parent_id', Auth::id());
            }else{
               abort(403,'Access denied'); 
            }
            $querydatas->select('clawback.*','ad.first_name','ad.promocode');
        }

        

        if ($request->has('plan_type') && $request->get('plan_type') != "") {
                $querydatas->where('plan_type', $request->get('plan_type'));
        }
        if ($request->has('plan_id') && $request->get('plan_id') != "") {
                $querydatas->where('plan_id', $request->get('plan_id'));
        }
        if ($request->has('dealer_id') && $request->get('dealer_id') != "") {
                $querydatas->where('admin_id', Crypt::decrypt($request->get('dealer_id')));
        }
        
        $result = Datatables::of($querydatas)
                    ->editColumn('plan_type', function ($data) {
                     return $data->plan_type == 1 ? 'Plan' : 'Bundle';
                    })
                    ->addColumn('plan_name', function (Clawback $pl) {
                        return $pl->plan->plan_name.' '.$pl->plan->sim_count;
                    })
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                    ->editColumn('status', function ($user) {
                        $stat = "";
                        switch ($user->status) {
                            case 0:
                                $stat = "InActive";
                                break;
                            case 1:
                                $stat = "Active";
                                break;
                        }
                        return $stat;
                    })
                    ->addColumn('actions', function ($data) use($type) {
                     $edit = $delete = "";
                     if(Helper::has_permission('commission','edit')){
                        $edit = '<a href="'.url('/clawback-manage/'.$type.'-'.Crypt::encrypt($data->id)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a>';
                     }
                     if(Helper::has_permission('commission','delete')){
                        $delete = '<a href="javascript:void(0);" class="text-muted delete_clawback" data-id="'.Crypt::encrypt($data->id).'"  data-type="'.base64_encode($type.'_clawback').'"   data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                     }

                     $btn = $edit.$delete;
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * Create & update clawback .
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function clawback_manage($id = "")
    { 
        if (!Helper::has_permission('commission','create')) {
            abort(403,'Access denied');
        }
        $admins     = Admins::find(Auth::id());
        $role       = $admins->roles->short_code;

        $plan         = [];
        $dealer       = [];
        $id           = explode("-", $id);
        switch ($id[0]) {
            case 'plan':
                $type = "Plan";
                break;

            case 'dealer':
                $type = "Dealer";
                $dealer     = DB::table('admins')->select('id','first_name','promocode');

                if(Helper::has_permission('commission')){

                    $dealer = $dealer->where('role','<>',1)->get();

                }else if(Helper::has_permission('commission','view_own')){
                    $dealer = $dealer->where(function ($query) {
                            $query->where('id', Auth::id())
                                  ->orWhere('parent_id', Auth::id());
                        })->get();
                }
                break;
            
            default:
                # code...
                break;
        }
        if(isset($id[1])){

            $clawid         = Crypt::decrypt($id[1]);
            $getplan        = Clawback::whereId($clawid)->first();
            if(!empty($getplan)){
                $plan = $getplan;
            }
        }  
        return view('commission.clawback-manage',compact('plan','type','dealer'));
    }
    /**
    * Function to add & Update clawback.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_clawback(Request $request)
    { 
        $rules = array('plan_type' => 'required','plan_id' => 'required','period' => 'required','status'=>'required','dealer_id'=> 'required_if:type,==,Dealer');
        $messages = array(
            'plan_type.required' => 'Please provide plan type',
            'plan_id.required' => 'Please provide plan name',
            'period.required' => 'Please provide period',
            'status.required' => 'Please provide status',
            'dealer_id.required_if' => 'Please provide dealer',
            );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $edit_id    = $request->edit_id;
            $data       = $request->except(['_token','edit_id']);
            $type       = $data['type'];
            $dealerid   = ($type == 'Plan') ? 0 : Crypt::decrypt($data['dealer_id']);
            $dataval    = [];

            $dataval['admin_id']   = $dealerid;
            $dataval['plan_type'] = $data['plan_type'];
            $dataval['plan_id']   = $data['plan_id'];
            $dataval['period']    = $data['period'];
            $dataval['status']    = $data['status'];

            if($edit_id != ""){
                if (!Helper::has_permission('commission', 'edit')) {
                    abort(403,'Access denied');
                }
                $id =  Clawback::updateOrCreate(['admin_id' => $dealerid,'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id']], $dataval); 
                if($id) {
                    return response()->json(['status'=>200,'msg'=>$type.' clawback updated successfully!','redirect'=>'/clawback-'.strtolower($type)]);
                } else {
                    return response()->json(['status'=>422,'msg'=>['Failed to update, please try again']]);
                }
            }else{
                if (!Helper::has_permission('commission', 'create')) {
                    abort(403,'Access denied');
                }
                $id   = Clawback::firstOrCreate(['admin_id' => $dealerid,'plan_type' => $data['plan_type'],'plan_id' => $data['plan_id']], $dataval);
                if($id->wasRecentlyCreated){
                    return response()->json(['status'=>200,'msg'=>$type.' clawback created successfully..','redirect'=>'/clawback-'.strtolower($type)]);
                }else{
                    return response()->json(['status'=>422,'msg'=>[$type.' clawback already exists']]);
                }
                
            }
        }
    }
    /**
    * Dealer clawback listing
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function clawback_dealer(Request $request){

        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

        $dealer     = DB::table('admins')->select('id','first_name','promocode');

        if(Helper::has_permission('commission')){
            $dealer = $dealer->where('role','<>',1)->get();

        }else if(Helper::has_permission('commission','view_own')){
            $dealer = $dealer->where(function ($query) {
                            $query->where('id', Auth::id())
                                  ->orWhere('parent_id', Auth::id());
                        })->get();
        }else{
           abort(403,'Access denied'); 
        } 

        return view('commission.clawback-dealerlist',compact('dealer'));
    }
    /**
    * Delete the clawback.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_clawback(Request $request)
    { 
        if (!Helper::has_permission('commission','delete')) {
            abort(403,'Access denied');
        }
        $req      = Crypt::decrypt($request->id);
        $type     = base64_decode($request->type);
        switch ($type) {
            case 'plan_clawback':
                $tableid = 'clawbackPlan-table';
                break;
            case 'dealer_clawback':
                $tableid = 'clawbackDealer-table';
                break;
            default:
                # code...
                break;
        }
        $delete = Clawback::whereId($req)->delete();
        return response()->json(['status'=>200,'msg'=>'Clawback deleted successfully..','table'=>$tableid]);
    }
    /**
    * commission payment
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_payment(Request $request){

        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }
        $querydatas       = Admins::select('id','first_name','promocode')->where('status', 1);
        $dealer  = $querydatas;
        $paycomm = $querydatas;
        if(Helper::has_permission('commission')){
            $dealer  = $dealer->where('role','<>',1)->get();
            $paycomm = $paycomm->where('role','<>',1)->get();

        }else if(Helper::has_permission('commission','view_own')){
            $dealer = $dealer->where(function ($query) {
                            $query->where('id', Auth::id())
                                  ->orWhere('parent_id', Auth::id());
                        })->get();
            $paycomm = $paycomm->where(function ($query) {
                            $query->where('parent_id', Auth::id());
                        })->get();
        }else{
           abort(403,'Access denied'); 
        }
        $haschild    = false;
        if(DB::table('admins')->where('parent_id',Auth::id())->exists()){
            $haschild = true;
        }
        return view('commission.payment-report', compact('dealer','paycomm','haschild'));
    }
    /**
    * commission payment list
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function comm_payment_list(Request $request){

        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

       $currsymbol  = Helper::get_option('currency_symbol');
       $pageSize    = ($request->length) ?  $request->length: 10;
       $start       = ($request->start) ?   $request->start : 0;

       $firstday    = Carbon::now()->firstofmonth()->format('Y-m-d');
       $lastday     = Carbon::now()->lastofmonth()->format('Y-m-d');

       $userQuery   = DB::table('commission_payments as cp')
                    ->select('cp.comm_user',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'), 'a.promocode as promocode',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"))
                    ->join('admins as a', 'a.id', '=', 'cp.comm_user');

        $userQuery   = $userQuery->groupBy('cp.comm_user')->orderBy('cp.comm_user');
        $itemCounter = $userQuery->get();
        $count_total = $itemCounter->count();

        $count_filter = 0;

        if(Helper::has_permission('commission')){

        }else if(Helper::has_permission('commission','view_own')){
            $userQuery = $userQuery->where('a.id', Auth::id())
                        ->orWhere('a.parent_id', Auth::id());
            $count_filter = $userQuery->count();

        }

        if ($request->has('promocode') && $request->get('promocode') != "") {
            $userQuery->where('a.id', Crypt::decrypt($request->get('promocode')));
            $count_filter = $userQuery->count();
        }
        $userQuery = $userQuery->skip($start)->take($pageSize);
        $userData  = $userQuery->get()->keyBy('comm_user');

        // $monthdata = $userQuery->WhereBetween('cp.payment_date', [$firstday, $lastday])->get()->keyBy('comm_user');

        $tilldata  = DB::table('dealer_pay_history')->select('dealer_id',DB::raw('sum(amount) as paidamount'),DB::raw('sum(balance_amount) as balance'))
                    ->whereDate('paid_on', '<=', Carbon::now())
                    ->groupBy('dealer_id')->orderBy('dealer_id')->get()->keyBy('dealer_id');

        $getclawback = DB::table('clawback_payments')->selectRaw('SUM(clawback_amount) as clawbackamount,SUM(comm_reversal) as commreversal,comm_user')->groupBy('comm_user');

        $clawbackdata      = $getclawback->get()->keyBy('comm_user');
        $clawbackmonthdata = $getclawback->WhereBetween('clawback_date', [$firstday, $lastday])->get()->keyBy('comm_user');

        $dealerpayhis      = DB::table('dealer_pay_history')->select(DB::raw('sum(amount) as paidamount'),DB::raw('sum(balance_amount) as balance'),'dealer_id')->groupBy('dealer_id');

        $payhistory        = $dealerpayhis->get()->keyBy('dealer_id');

        $commData = [];
        foreach ($userData as $key => $data) {
            $sub            = [];
            $payamount  = $paidamount = $commreversal = $totalcomm = $balance = $paidcomm = $advancebal =  $topay = 0;

            $commreversal   = isset($clawbackdata[$key]) ? $clawbackdata[$key]->commreversal : 0;
            $paidcomm       = isset($payhistory[$key]) ? $payhistory[$key]->paidamount : 0;

            $payamount      = $data->payamount;
            $paidamount     = $data->paidamount;
           

            $totalcomm      = (($payamount + $paidamount) - $commreversal);
            $advancebal     = ($paidcomm - $totalcomm); 
            $advancebal     = ($advancebal > 0) ? $advancebal : 0; 
            $topay          = ($totalcomm - $paidcomm);
            $topay          = ($topay > 0) ? $topay : 0;


            $sub['userid']          = Crypt::encrypt($data->comm_user);
            $sub['name']            = $data->dealer_name;
            $sub['promocode']       = $data->promocode;
            $sub['total']           = $currsymbol.Helper::number_format($totalcomm);
            $sub['totalpaid']       = $currsymbol.Helper::number_format($paidcomm);
            $sub['advancebal']      = $currsymbol.Helper::number_format($advancebal);
            // $sub['monthcomm']       = $monthcomm;
            // $sub['monthnotpaid']    = $monthnotpaid;
            $sub['topay']           = $currsymbol.Helper::number_format($topay);
            array_push($commData, $sub); 
        }
        if($count_filter == 0){
            $count_filter = $count_total;
        }
        return Datatables::of($commData)
                ->with([
                    "recordsTotal" => $count_total,
                    "recordsFiltered" => $count_filter,
                    ])
                ->skipPaging()->make(true);
    }
    /**
    * Show the commission report based on user.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_commission(Request $request)
    {
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }
        $userid = $request->user_id;
        return view('commission.user-commission', compact('userid'));
    }
    /**
    * Show the commission report based on user.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_commission_list(Request $request)
    {
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }
        $currsymbol  = Helper::get_option('currency_symbol');

        $user_id = Crypt::decrypt($request->userid);

        $users   = DB::table('commission_payments as cp')
                ->select('cp.autoplan_id',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"),'cp.created_at',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'),'cp.comm_user')
                ->join('admins as a', 'a.id', '=', 'cp.comm_user')
                ->where('cp.comm_user',$user_id)
                ->groupBy('cp.autoplan_id')
                ->orderBy('created_at', 'DESC');

        if ($request->from && !empty($request->from)) {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if(!empty($request->to)){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $users->whereDate('cp.created_at', '>=', $from)
                          ->whereDate('cp.created_at', '<=', $to);
            }else{
            $users->whereDate('cp.created_at', '=', $from);
            }
        }
        $users = $users->get()->keyBy('autoplan_id');
        $getclawback = DB::table('clawback_payments')->selectRaw('clawback_amount as clawbackamount,comm_reversal as commreversal,comm_user,autoplan_id') ->where('comm_user',$user_id);

        $clawbackdata      = $getclawback->get()->keyBy('autoplan_id');
        $userDetails = [];

        foreach ($users as $key => $data) {
            $payamount = $paidamount = $totalamount = $gainedcomm = 0;
            $payamount             = $data->payamount;
            $paidamount            = $data->paidamount;
            $totalamount           = $payamount + $paidamount;
            $commreversal          = isset($clawbackdata[$key]) ? $clawbackdata[$key]->commreversal : 0;
            $clawback_amount       = isset($clawbackdata[$key]) ? $clawbackdata[$key]->clawbackamount : 0;
            $gainedcomm     = ($totalamount - $commreversal);

            $autoplan           = $data->autoplan_id;
            $getplan            = AutoPlan::find($autoplan);
            $data->planname     = (!empty($getplan)) ? $getplan->plan->plan_name:"";
            $data->gained_comm  = $currsymbol.Helper::number_format($gainedcomm);
            $data->paid_comm    = $currsymbol.Helper::number_format($paidamount);
            $data->clawback_amount     = $currsymbol.Helper::number_format($clawback_amount);
            $data->total_amount = $currsymbol.Helper::number_format($totalamount);
            array_push($userDetails, $data);
        }
        return Datatables::of($userDetails)
                ->editColumn('created_at', function ($date) {
                        return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                        })
                ->editColumn('action', function ($data) {
                        $userdata = Crypt::encrypt($data->comm_user.'-'.$data->autoplan_id);
                        return '<button type="button" data-id="'.$userdata.'" class="btn btn-default btn-xs comm-breakdown" title="View Details"><i class="mdi mdi-eye font-18"></i></button>';
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
        if ((!Helper::has_permission('commission')) && (!Helper::has_permission('commission','view_own'))){
            abort(403,'Access denied');
        }

       $val     = Crypt::decrypt($request->datas);
       $val     = explode('-', $val);
       $userid  =  $val[0];
       $planid  =  $val[1];
       $breakdown    = [];
       $getbreakdown = DB::table('commission_payments')
                        ->select('pay_amount','amount_paid','payment_date','user_active')
                        ->where('comm_user',$userid)
                        ->where('autoplan_id',$planid)
                        ->orderBy('payment_date', 'asc')
                        ->get();

        if($getbreakdown->isNotEmpty()){
            $breakdown = $getbreakdown;
        }
        $view  = view('commission.create-data',compact('breakdown'))->render();
        return response()->json(['status'=>200,'page'=>$view]);

    }
    /**
    * Pay Commission to dealer
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function pay_commission(Request $request)
    { 
        if (!Helper::has_permission('commission','create')){
            abort(403,'Access denied');
        }

        $rules = array('pay_dealer' => 'required','pay_amount' => 'required|max:10','type' => 'required');
        $messages = array(
            'pay_dealer.required' => 'Please provide dealer',
            'pay_amount.required' => 'Please provide amount',
            'pay_amount.max' => 'Maximum character limit exceeded',
            'type.required' => 'Technical error',
            );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $dealer_id  = Crypt::decrypt($request->pay_dealer);
            if(Auth::id() == $dealer_id){
              return response()->json(['status' => 422, 'msg' => ['Not possible to add payment for yourself']]);  
            }
            $dealer     = Admins::where('id',$dealer_id)->where('status', 1)->first();
            if(!$dealer) {
                return response()->json(['status' => 422, 'msg' => ['Invalid Dealer']]);
            }
            if(!PaymentCommission::where('comm_user', $dealer->id)->exists()){
              return response()->json(['status' => 422, 'msg' => ['No commission earned till now']]);  
            }
            $type               = $request->type;
            $loggedin_user      = Auth::user();
            $dealer_pay_history = DealerPayHistory::where('dealer_id', $dealer->id)
                                    ->latest('paid_on')->first();
            $previous_balance = 0;
            if($dealer_pay_history)
                $previous_balance = $dealer_pay_history->balance_amount;

            $payment_amount = $request->pay_amount;
            $balance_amount = $request->pay_amount + $previous_balance;

            DB::beginTransaction();

            try {
                if($type == 'dealer') {
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
                $new_payment            = new DealerPayHistory();
                $new_payment->dealer_id = $dealer->id;
                $new_payment->amount    = $payment_amount;
                $new_payment->balance_amount = $balance_amount;
                $new_payment->paid_on   = Carbon::now();
                $new_payment->who_paid  = $loggedin_user->id;
                $new_payment->save();

                DB::commit();

                return response()->json(['status' => 200, 'msg' => 'Commission paid successfully']);  
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => 422, 'msg' => ['Failed to update']]);
            }
        }
    }
    
}
