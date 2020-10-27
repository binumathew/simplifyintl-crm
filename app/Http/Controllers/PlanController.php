<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Helper;

use DB;

use DataTables;

use Carbon;

use Crypt;

use Validator;

use Auth;

use \App\Models\Admins;

class PlanController extends Controller
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
    public function sim_plans()
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }
        $dealer   = DB::table('admins')->select('id','first_name','last_name','promocode')
                    ->where('status',1);
        if(Helper::has_permission('plan_management')){
            
        }else if(Helper::has_permission('plan_management','view_own')){
            $dealer = $dealer->where('admins.id', Auth::id())
                            ->orWhere('admins.parent_id', Auth::id()); 
        }
        $dealer     = $dealer->get();
        return view('plan.sim-plans',compact('dealer'));
    }
    /**
    * Show the Sim plans list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_plans_list(Request $request)
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }

        $querydatas = DB::table('tbl_plans');

        if(Helper::has_permission('plan_management')){
            //$querydatas = $querydatas->where('tbl_plans.dealer_id', 0);
        }else if(Helper::has_permission('plan_management','view_own')){
            $querydatas = $querydatas->where('tbl_plans.dealer_id', Auth::id());  
        }

        if ($request->has('plan_status') && $request->get('plan_status') != "") {
                $querydatas->where('status', $request->get('plan_status'));
        }
        if ($request->has('dealer_id') && $request->get('dealer_id') != "") {
            $dealer_id  = Crypt::decrypt($request->get('dealer_id'));
            $admins     = Admins::find($dealer_id);
            $role       = $admins->roles->short_code;
            $dealer_id = ($role == 'DEALER') ? $dealer_id : 0;
            $querydatas->where('tbl_plans.dealer_id', $dealer_id);
        }
        $result = Datatables::of($querydatas)
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
                    ->addColumn('actions', function ($data) {
                    $edit = $delete = "";
                    if (Helper::has_permission('plan_management','edit')) {
                        $edit = '<a href="'.url('/sim-plans-manage/'.Crypt::encrypt($data->id)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a> ';
                    } 
                    if (Helper::has_permission('plan_management','delete')) {
                            $delete = '<a href="javascript:void(0);" class="text-muted delete_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('sim_plan').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                    }      
                     $btn = $edit.' <a href="javascript:void(0);" class="text-muted view_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('sim_plan').'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a>'.$delete;
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * Sim Plans Manage
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_plans_manage($id = '')
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }
        $plan  = [];
        if($id){
            $id       = Crypt::decrypt($id);
            $getplan  = DB::table('tbl_plans')->whereId($id)->first();
            if(!empty($getplan)){
                $plan = $getplan;
            }
        }
        $provider = DB::table('tbl_providers')->where('status',1)->get();
        $dealer   = DB::table('admins')->select('id','first_name','last_name','promocode')
                    ->where('status',1);
        if(Helper::has_permission('plan_management')){
            
        }else if(Helper::has_permission('plan_management','view_own')){
            $dealer = $dealer->where('admins.id', Auth::id())
                            ->orWhere('admins.parent_id', Auth::id()); 
        }
        $dealer     = $dealer->get();
        return view('plan.sim-plans-manage',compact('plan','provider','dealer'));
    }
    /**
    * Sim Plans Actions
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function sim_plans_actions(Request $request)
    { 
        
    $rules = array('plan_name' => 'required|max:50','switch_billing_plan' => 'required|max:10','sim_billing_plan' => 'required|max:10','description' => 'required|max:150','buy_price'=>'required|max:8','sell_price'=>'required|max:8','data_limit'=>'required|max:25','call_limit'=>'required|max:25','in_call_limit'=>'required|max:25','msg_limit'=>'required|max:25','status'=>'required','period' => 'required|max:8','provider' => 'required');
      $messages = array(
        'plan_name.required' => 'Please provide plan name',
        'plan_name.max' => 'Plan name maximum character exceeded',
        'switch_billing_plan.required' => 'Please provide switch billing plan',
        'switch_billing_plan.max' => 'Switch billing plan maximum character exceeded',
        'sim_billing_plan.required' => 'Please provide sim billing plan',
        'sim_billing_plan.max' => 'Sim billing plan maximum character exceeded',
        'description.required' => 'Please provide description',
        'description.max' => 'Description maximum character exceeded',
        'buy_price.required' => 'Please provide buy price',
        'buy_price.max' => 'Buy price maximum digits exceeded',
        'sell_price.required' => 'Please provide sell price',
        'sell_price.max' => 'Sell price maximum character exceeded',
        'data_limit.required' => 'Please provide data limit',
        'data_limit.max' => 'Data limit maximum character exceeded',
        'call_limit.required' => 'Please provide call limit',
        'call_limit.max' => 'Call limit maximum character exceeded',
        'in_call_limit.required' => 'Please provide international call limit',
        'in_call_limit.max' => 'International call limit maximum character exceeded',
        'msg_limit.required' => 'Please provide message limit',
        'msg_limit.max' => 'Message limit maximum character exceeded',
        'status.required' => 'Please choose status',
        'period.required' => 'Please provide period',
        'period.max' => 'Period limit maximum character exceeded',
        'provider.required' => 'Please provide provider',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $edit_id    = $request->edit_id;
            $data       = $request->except(['_token','edit_id']);
            $dealer_id  = Crypt::decrypt($request->dealer_id);
            $admins     = Admins::find($dealer_id);
            if(!$admins){
                return response()->json(['status'=>422,'msg'=>['Dealer not exists']]);  
            }
            $role       = $admins->roles->short_code;
            $data['dealer_id'] = ($role == 'DEALER') ? $dealer_id : 0;

            if($edit_id != ""){
                if (!Helper::has_permission('plan_management','edit')) {
                    abort(403,'Access denied');
                }
                $edit_id = Crypt::decrypt($edit_id);
                DB::table('tbl_plans')->where('id',$edit_id)->update($data);
                return response()->json(['status'=>200,'msg'=>'plan updated..','redirect'=>'/sim-plans']);
            }else{
                if (!Helper::has_permission('plan_management','create')) {
                    abort(403,'Access denied');
                }
                DB::table('tbl_plans')->insert($data);
                return response()->json(['status'=>200,'msg'=>'plan added successfully..','redirect'=>'/sim-plans']);
            }
        }

    }
    /**
    * Delete the plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function plan_delete(Request $request)
    { 
        if (!Helper::has_permission('plan_management','delete')) {
            abort(403,'Access denied');
        }
        $id   = Crypt::decrypt($request->id);
        $type = base64_decode($request->type);
        switch ($type) {
            case 'sim_plan':
                $table   = 'tbl_plans';
                $tableid = 'simPlans-table';
                break;
            case 'switch_plan':
                $table   = 'plans';
                $tableid = 'switchPlans-table';
                break;
            case 'conf_plan':
                $table   = 'conference_plans';
                $tableid = 'confPlans-table';
                break;
            
            default:
                # code...
                break;
        }
        $delete = DB::table($table)->where('id', $id)->delete();
        return response()->json(['status'=>200,'msg'=>'plan deleted successfully..','table'=>$tableid]);
    }
    /**
    * View the plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function plan_view(Request $request)
    { 
        
        $id   = Crypt::decrypt($request->id);
        $type = base64_decode($request->type);
        switch ($type) {
            case 'sim_plan':

            if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
                        abort(403,'Access denied');
            }
                $simplan = [];
                $getplan = DB::table('tbl_plans')->whereId($id)->first();
                if(!empty($getplan)){
                    $simplan = $getplan;
                }
                $view     = view('plan.create-data',compact('simplan'))->render();
                break;
            case 'switch_plan':
            if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
                abort(403,'Access denied');
            }
                $switchplan = [];
                $getplan = DB::table('plans')->whereId($id)->first();
                if(!empty($getplan)){
                    $switchplan = $getplan;
                }
                $view     = view('plan.create-data',compact('switchplan'))->render();
                break;
            case 'conf_plan':

            if ((!Helper::has_permission('conference_plan')) && (!Helper::has_permission('conference_plan','view_own'))){
                abort(403,'Access denied');
            }
                $confplan = [];
                $getplan = DB::table('conference_plans')->whereId($id)->first();
                if(!empty($getplan)){
                    $confplan = $getplan;
                }
                $view     = view('plan.create-data',compact('confplan'))->render();
                break;
            default:
                # code...
                break;
        }
        return response()->json(['status'=>200,'page'=>$view]);  
    }
    /**
    * Show the Switch plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function switch_plans()
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }
        $dealer   = DB::table('admins')->select('id','first_name','last_name','promocode')
                    ->where('status',1);
        if(Helper::has_permission('plan_management')){
            
        }else if(Helper::has_permission('plan_management','view_own')){
            $dealer = $dealer->where('admins.id', Auth::id())
                            ->orWhere('admins.parent_id', Auth::id()); 
        }
        $dealer     = $dealer->get();
        return view('plan.switch-plans',compact('dealer'));
    }
    /**
    * Show the Switch plans list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function switch_plans_list(Request $request)
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }

        $querydatas = DB::table('plans');

        if(Helper::has_permission('plan_management')){
            //$querydatas = $querydatas->where('plans.dealer_id', 0);
        }else if(Helper::has_permission('plan_management','view_own')){
            $querydatas = $querydatas->where('plans.dealer_id', Auth::id());  
        }

        if ($request->has('dealer_id') && $request->get('dealer_id') != "") {
            $dealer_id  = Crypt::decrypt($request->get('dealer_id'));
            $admins     = Admins::find($dealer_id);
            $role       = $admins->roles->short_code;
            $dealer_id = ($role == 'DEALER') ? $dealer_id : 0;
            $querydatas->where('plans.dealer_id', $dealer_id);
        }

        $result = Datatables::of($querydatas)
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                    ->addColumn('actions', function ($data) {
                    $edit = $delete = "";
                    if (Helper::has_permission('plan_management','edit')) {
                        $edit = '<a href="'.url('/switch-plans-manage/'.Crypt::encrypt($data->id)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a> ';
                    } 
                    if (Helper::has_permission('plan_management','delete')) {
                        $delete = '<a href="javascript:void(0);" class="text-muted delete_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('switch_plan').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                    }     
                    $btn = $edit.' <a href="javascript:void(0);" class="text-muted view_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('switch_plan').'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a> '.$delete;
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * Switch Plans Manage
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function switch_plans_manage($id = '')
    { 
        if ((!Helper::has_permission('plan_management')) && (!Helper::has_permission('plan_management','view_own'))){
            abort(403,'Access denied');
        }
        $plan  = [];
        if($id){
            $id       = Crypt::decrypt($id);
            $getplan  = DB::table('plans')->whereId($id)->first();
            if(!empty($getplan)){
                $plan = $getplan;
            }
        }
        $dealer   = DB::table('admins')->select('id','first_name','last_name','promocode')
                    ->where('status',1);
        if(Helper::has_permission('plan_management')){
            
        }else if(Helper::has_permission('plan_management','view_own')){
            $dealer = $dealer->where('admins.id', Auth::id())
                            ->orWhere('admins.parent_id', Auth::id()); 
        }
        $dealer     = $dealer->get();
        return view('plan.switch-plans-manage',compact('plan','dealer'));
    }
    /**
    * Switch Plans Actions
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function switch_plans_actions(Request $request)
    { 
        
    $rules = array('plan_name' => 'required|max:50','switch_billing_plan' => 'required|max:10','description' => 'required|max:150','buy_price'=>'required|max:8','sell_price'=>'required|max:8','minutes'=>'required|max:12','period'=>'required|max:8','flag'=>'required|max:25','switch_id'=>'required|max:8','in_app_id'=>'required|max:25','plan_type'=>'required|max:5','rate_minutes'=>'required|max:10');
      $messages = array(
        'plan_name.required' => 'Please provide plan name',
        'plan_name.max' => 'Plan name maximum character exceeded',
        'switch_billing_plan.required' => 'Please provide switch billing plan',
        'switch_billing_plan.max' => 'Switch billing plan maximum character exceeded',
        'description.required' => 'Please provide description',
        'description.max' => 'Description maximum character exceeded',
        'buy_price.required' => 'Please provide buy price',
        'buy_price.max' => 'Buy price maximum digits exceeded',
        'sell_price.required' => 'Please provide sell price',
        'sell_price.max' => 'Sell price maximum character exceeded',
        'minutes.required' => 'Please provide minutes',
        'minutes.max' => 'Minutes maximum character exceeded',
        'period.required' => 'Please provide period',
        'period.max' => 'Period maximum character exceeded',
        'flag.required' => 'Please provide flag',
        'flag.max' => 'Flag maximum character exceeded',
        'switch_id.required' => 'Please provide switch id',
        'switch_id.max' => 'Switch id maximum character exceeded',
        'in_app_id.required' => 'Please provide app id',
        'in_app_id.max' => 'App id maximum character exceeded',
        'plan_type.required' => 'Please provide plan type',
        'plan_type.max' => 'Plan type maximum character exceeded',
        'rate_minutes.required' => 'Please provide rate minutes',
        'rate_minutes.max' => 'Rate minutes maximum character exceeded',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $edit_id    = $request->edit_id;
            $data       = $request->except(['_token','edit_id']);
            $dealer_id  = Crypt::decrypt($request->dealer_id);
            $admins     = Admins::find($dealer_id);
            if(!$admins){
                return response()->json(['status'=>422,'msg'=>['Dealer not exists']]);  
            }
            $role       = $admins->roles->short_code;
            $data['dealer_id'] = ($role == 'DEALER') ? $dealer_id : 0;

            if($edit_id != ""){
                if (!Helper::has_permission('plan_management','edit')) {
                    abort(403,'Access denied');
                }
                $edit_id = Crypt::decrypt($edit_id);
                DB::table('plans')->where('id',$edit_id)->update($data);
                return response()->json(['status'=>200,'msg'=>'plan updated..','redirect'=>'/switch-plans']);
            }else{
                if (!Helper::has_permission('plan_management','create')) {
                    abort(403,'Access denied');
                }
                DB::table('plans')->insert($data);
                return response()->json(['status'=>200,'msg'=>'plan added successfully..','redirect'=>'/switch-plans']);
            }
        }

    }
    /**
    * Show the Conference plans.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conf_plans()
    { 
        if ((!Helper::has_permission('conference_plan')) && (!Helper::has_permission('conference_plan','view_own'))){
            abort(403,'Access denied');
        }
        return view('plan.conf-plans');
    }
    /**
    * Show the conference plans list.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conf_plans_list(Request $request)
    { 
        if ((!Helper::has_permission('conference_plan')) && (!Helper::has_permission('conference_plan','view_own'))){
            abort(403,'Access denied');
        }
        $querydatas = DB::table('conference_plans as cp')
        			 ->select('cp.*','st.currency')
        			 ->join('switch_template as st', 'st.id', '=', 'cp.switch_id')
        			 ->orderBy('cp.switch_id');
        			 
        if ($request->has('plan_status') && $request->get('plan_status') != "") {
                $querydatas->where('status', $request->get('plan_status'));
        }

        $result = Datatables::of($querydatas)
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
                    ->addColumn('actions', function ($data) {
                     $btn = '<a href="'.url('/conf-plans-manage/'.Crypt::encrypt($data->id)).'" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a> <a href="javascript:void(0);" class="text-muted view_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('conf_plan').'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a> <a href="javascript:void(0);" class="text-muted delete_plan" data-id="'.Crypt::encrypt($data->id).'" data-type="'.base64_encode('conf_plan').'" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a>';
                        return $btn;
                    })
                ->rawColumns(['actions'])
                ->make(true);
        return $result;
    }
    /**
    * Conf Plans Manage
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conf_plans_manage($id = '')
    { 
        if ((!Helper::has_permission('conference_plan')) && (!Helper::has_permission('conference_plan','view_own'))){
            abort(403,'Access denied');
        }
        $plan           = [];
        $switchtemplate = [];
        if($id){
            $id       = Crypt::decrypt($id);
            $getplan  = DB::table('conference_plans')->whereId($id)->first();
            if(!empty($getplan)){
                $plan = $getplan;
            }
        }
        $gettemplate = DB::table('switch_template')->select('id','currency')->get();
        if($gettemplate->isNotEmpty()){
            $switchtemplate = $gettemplate;
        }
        return view('plan.conf-plans-manage',compact('plan','switchtemplate'));
    }
    /**
    * Conf Plans Actions
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conf_plans_actions(Request $request)
    { 
    
    $rules = array('plan_name' => 'required|max:50','switch_billing_plan' => 'required|max:10','description' => 'required|max:150','buy_price'=>'required|max:8','sell_price'=>'required|max:8','minutes'=>'required|max:12','period'=>'required|max:8','flag'=>'required|max:25','switch_id'=>'required|max:8','in_app_id'=>'required|max:25','provider'=>'required|max:25','participant_limit'=>'required|max:5','plan_term_annual'=>'required|max:10','status'=>'required');
      $messages = array(
        'plan_name.required' => 'Please provide plan name',
        'plan_name.max' => 'Plan name maximum character exceeded',
        'switch_billing_plan.required' => 'Please provide switch billing plan',
        'switch_billing_plan.max' => 'Switch billing plan maximum character exceeded',
        'description.required' => 'Please provide description',
        'description.max' => 'Description maximum character exceeded',
        'buy_price.required' => 'Please provide buy price',
        'buy_price.max' => 'Buy price maximum digits exceeded',
        'sell_price.required' => 'Please provide sell price',
        'sell_price.max' => 'Sell price maximum character exceeded',
        'minutes.required' => 'Please provide minutes',
        'minutes.max' => 'Minutes maximum character exceeded',
        'period.required' => 'Please provide period',
        'period.max' => 'Period maximum character exceeded',
        'flag.required' => 'Please provide flag',
        'flag.max' => 'Flag maximum character exceeded',
        'switch_id.required' => 'Please provide switch id',
        'switch_id.max' => 'Switch id maximum character exceeded',
        'in_app_id.required' => 'Please provide app id',
        'in_app_id.max' => 'App id maximum character exceeded',
        'provider.required' => 'Please provide provider',
        'provider.max' => 'Provider name maximum character exceeded',
        'participant_limit.required' => 'Please provide participant limit',
        'participant_limit.max' => 'Participant limit maximum character exceeded',
        'plan_term_annual.required' => 'Please provide plan term annual',
        'plan_term_annual.max' => 'Plan term annual maximum character exceeded',
        'status.required' => 'Please provide status',
        );
        $validator = Validator::make($request->all(), $rules,$messages);

        // Validate the input and return correct response
        if ($validator->fails()){
          return response()->json(['status'=>422,'msg'=>$validator->errors()->all()]);
        }else{
            $edit_id = $request->edit_id;
            $request = $request->except(['_token','edit_id']);
            if($edit_id != ""){
                if (!Helper::has_permission('conference_plan','edit')) {
                    abort(403,'Access denied');
                }
                $edit_id = Crypt::decrypt($edit_id);
                DB::table('conference_plans')->where('id',$edit_id)->update($request);
                return response()->json(['status'=>200,'msg'=>'plan updated..','redirect'=>'/conf-plans']);
            }else{ 
                if (!Helper::has_permission('conference_plan','create')) {
                    abort(403,'Access denied');
                }
                DB::table('conference_plans')->insert($request);
                return response()->json(['status'=>200,'msg'=>'plan added successfully..','redirect'=>'/conf-plans']);
            }
        }

    }
}
