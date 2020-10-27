<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Excel;
use Crypt;
use Carbon;
use Helper;
use Session;
use DataTables;
use SwitchHelper;
use App\Models\Country;
use App\Models\Conference;
use App\Models\BridgeServer;
use App\Exports\CustomExport;
use Illuminate\Http\Request;


use App\Models\SimStock;

class ConferenceController extends Controller
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
    * Show the Conference List Page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index(Request $request)
    {
        if(!Helper::has_permission('conference')){
            abort(403,'Access Denied');
        }

        $bridgeips = BridgeServer::all();
        return view('conference.conference-list',compact('bridgeips'));             
    } 

    /**
    * Show the Conference List details.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conference_list(Request $request)
    {
        if(!Helper::has_permission('conference')){
            abort(403,'Access Denied');
        }
        
        $adv_port = 0;
        $now = Carbon::now()->format('Y-m-d H:i:s');        
        $conference = Conference::select('conference_create.*',DB::raw('JSON_LENGTH(msisdnlist) as ports'),'u.name','u.phone')
                        ->leftJoin('users as u','u.id','=','user_id');

        if($request->sendcli)
            $conference = $conference->where('sendcli', 'like', '%'.ltrim($request->sendcli, '0'));
        
        if($request->conf_name)
            $conference = $conference->where('confname', 'like', '%'.$request->conf_name.'%');                
        
        if($request->country_id)
            $conference = $conference->where('conference_create.country_id', $request->country_id);
        
        if($request->bridge_id)
            $conference = $conference->where('bridge_id', $request->bridge_id);
        
        if($request->serviceno)
            $conference = $conference->where('serviceno', $request->serviceno);
        
        if($request->conf_status){
            switch($request->conf_status){
                case 1:
                    $conference = $conference->where('startdatetime', '<=', $now)->where('enddatetime', '>=', $now);                    
                break;
                case 2:
                    $conference = $conference->where('startdatetime', '>', $now);
                break;
                case 3:
                    $conference = $conference->where('enddatetime', '<', $now);
                break;
                case 'custom':
                    if($request->from_date){
                        $from_date = Carbon::parse($request->from_date)->startOfDay();
                        $conference = $conference->where('startdatetime', '>=', $from_date);
                    }
                    if($request->to_date){
                        $to_date = Carbon::parse($request->to_date)->endOfDay();
                        $conference = $conference->where('enddatetime', '<=', $to_date);
                    }                                    
                break;                           
            }
        }

        $total = Conference::count();
        $upcoming = Conference::where('startdatetime', '>', $now)->count();
        $finised = Conference::where('enddatetime', '<=', $now)->count();
        $activeList = Conference::select(DB::raw('count(*) as count'), DB::raw('SUM(JSON_LENGTH(msisdnlist)) as ports'))
                        ->where('startdatetime', '<=', $now)->where('enddatetime', '>=', $now)->where('audioconfid','!=',0)->first(); 
        $active = $activeList->count;
        $active_port = ($activeList->ports)? ($activeList->ports+$active):0; 
        // echo Carbon::parse($now)->addHours(1);
        $adv_port =  Conference::where('startdatetime', '>', $now)->where('enddatetime', '<=', Carbon::parse($now)->addHours(1))
                            ->where('audioconfid','!=',0)->sum(DB::raw('JSON_LENGTH(msisdnlist)')); //->get();//
        // print_R($adv_port);
        return Datatables::eloquent($conference)
            ->addColumn('business_name', function (Conference $conference) {
                if($conference->user)
                    return $conference->user->userDetail->business_name;                
            })->addColumn('name', function (Conference $conference) {
                if($conference->user)
                    return $conference->user->name;                
            })->editColumn('ports', function (Conference $conference) {
                return $conference->ports + 1;
            })->editColumn('startdatetime', function (Conference $conference) {
                return Helper::date_format($conference->startdatetime);
            })->editColumn('enddatetime', function (Conference $conference) {
                return Helper::date_format($conference->enddatetime);
            })->editColumn('created_at', function (Conference $conference) {
                return Helper::date_format($conference->created_at);
            })->addColumn('action', function (Conference $conference) {
                $html = '';
                if (Helper::has_permission('conference','edit')) {
                    if($conference->audioconfid != 0){
                        $html .='<a data-toggle="tooltip" title="Login" href="http://149.36.7.16/iCallMateAEC1/faces/audioConfLive.xhtml?audioconfid='.$conference->audioconfid.'&serviceno='.$conference->serviceno.'" target="_blank" class="m-r-10"><i class="mdi mdi-login mdi-24px"></i></a>';
                    }
                        
                    $html .='<a title="View Details" class="show_conference_details text-muted m-r-10" data-id="'.$conference->id.'"><i class="mdi mdi-eye mdi-24px"></i></a> <a title="View Details" class="show_user_data text-muted m-r-10" data-id="'.$conference->id.'"><i class="mdi mdi-account mdi-24px"></i></a>';
                }
                if(Helper::has_permission('conference','delete')) {   
                    $html .='<a href="javascript:void(0);" class="text-danger delete_conference" data-id="'.Crypt::encrypt($conference->id).'"  data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="mdi mdi-delete mdi-24px"></i></a>';
                }
                if (Helper::has_permission('conference','edit')) {
                    if($conference->user)
                    $html .='<form id="show_user_'.$conference->id.'" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$conference->phone.'"></form>';
                }

                return $html;
            })
            ->with(['total' => $total, 'active' => $active, 'active_port' => $active_port, 'upcoming' => $upcoming, 'adv_port' => $adv_port, 'finised' => $finised])
        ->make(true);
    }


    /**
    * Show the Conference details view.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conference_details(Request $request)
    {
        $conf_id = $request->conference_id;        
        $conference = Conference::where('id', $conf_id)->first();                    
        if(!$conference){
            return response()->json(['error'=> true, 'message' => 'Invalid conference detail']);
        }
        $html = view('conference.modal-popup',compact('conference'))->render();
        return response()->json(['error'=> false, 'html' => $html]);
    }

    /**
    * Show the Conference details view.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_conference(Request $request)
    {        
        $conf_id = Crypt::decrypt($request->conference_id);        
        $conference = Conference::where('id', $conf_id)->first();                    
        if(!$conference){
            return response()->json(['error'=> true, 'message' => 'Invalid conference detail']);
        }
        if($conference->audioconfid != 0){
            $conf_key = Helper::get_option('conference_key');
            $end_point = '/DeleteAudioConf?';
            $apidata = ['ukey' => $conf_key, 'audioconfid' => $conference->audioconfid];
            $data = SwitchHelper::conference_bridge($end_point, json_encode($apidata), $conference->bridge_id);
        } else {
            $data['status'] = 'success';
        } 

        if($data['status']=='success'){
            $conference = Conference::where('id', $conf_id)->delete();
            $response['message'] = 'Conference Deleted Successfully';
            return response()->json(['success' => true, $response]);
        }else{
            $response['message'] = 'Network error, please try again';
            $response['data'] = $data['value'];
            return response()->json(['error'=> true, $response]);  
        } 
    }

    /**
    * Show the Conference User List.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function user_list(Request $request)
    {
        if(!Helper::has_permission('conference')){
            abort(403,'Access Denied');
        }
        
        return view('conference.user-list');             
    }

    /**
    * Show the application user details pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conference_user_list(Request $request){
        $users = DB::table('users as us')->select('first_name','last_name','email','phone','us.id','ud.user_platform as platform','us.status','us.created_at')->leftJoin('user_data as ud', 'ud.user_id', '=', 'us.id')->where('ud.user_platform','AVC');

        return DataTables::queryBuilder($users)
                ->addIndexColumn()
                ->addColumn('actions', function($user){
                    $parameter= Crypt::encrypt($user->id);
                    $html = '';
                    if (Helper::has_permission('users','edit')) {
                        $html .='<a data-toggle="tooltip" title="Login" href="'.config('app.suburl').'admin-authenticate/'.$parameter.'" target="_blank" class="m-r-10"><i class="mdi mdi-login mdi-24px"></i></a> <a title="View Details" class="show_user_data text-muted  m-r-10" user-id="'.$user->id.'"><i class="mdi mdi-eye mdi-24px"></i></a>';

                        if($user->status == 0){
                            $html .='<a href="javascript:void(0)" class="edit btn btn-primary btn-sm userAction m-r-10" data-status="'.$user->status.'" data-user="'.$user->id.'" data-tag="Enable">Enable</a>';
                        }else if($user->status == 1){
                            $html .='<a href="javascript:void(0)" class="edit btn btn-danger btn-sm userAction m-r-10" data-status="'.$user->status.'" data-user="'.$user->id.'" data-tag="Disable">Disable</a>';
                        }                        
                    }
                    if(Helper::has_permission('users','delete')) {   
                        $html .='<a href="javascript:void(0);" class="text-danger delete_user m-r-10"  user-id="'.$user->id.'" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="mdi mdi-delete mdi-24px"></i></a><form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                    }
                    if (Helper::has_permission('users','edit')) {
                        $html .='<form id="show_user_'.$user->id.'" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$user->phone.'"></form>';
                    }
                    return $html;
                })
                ->editColumn('name', function ($user){
                    return $user->first_name.' '.$user->last_name;
                })
                ->editColumn('status', function ($user){
                    if($user->status == 0){
                        $status='<span class="badge badge-danger">In Active</span>';
                    }else if($user->status == 1){
                        $status='<span class="badge badge-success">Active</span>';
                    }
                    return $status;
                })
                ->editColumn('created_at', function ($user) {
                    return Helper::date_format($user->created_at);
                })
                ->rawColumns(['actions','platform','status'])
                ->make(true);


        /*
        // $pageSize = ($request->length) ?  $request->length: 10;
        // $start    = ($request->start) ?   $request->start : 0;
        $search   = $request->input('search.value');

        $userQuery = DB::table('users as us')
                    ->leftJoin('temp_users as tu', 'tu.phone', '=', 'us.phone')
                    ->leftJoin('user_data as ud', 'ud.user_id', '=', 'us.id');
        $userQuery->where('ud.user_platform','AVC');

        $itemCounter = $userQuery->get();
        $count_total = $itemCounter->count();

        $count_filter = 0;
        if($search != ''){
            if(is_numeric($search)){
                $userQuery->where( 'us.phone' , 'LIKE' , '%'.$search.'%');
            }else{
            $userQuery->where( 'us.first_name' , 'LIKE' , ''.$search.'%')
                    ->orWhere( 'us.last_name' , 'LIKE' , ''.$search.'%')
                    ->orWhere( 'us.email' , 'LIKE' , ''.$search.'%');
            }
            $count_filter = $userQuery->count();
        }
            
        $userQuery->select('us.first_name as firstname','us.last_name as lastname','us.email as email','us.phone','us.id as user_id','ud.user_platform as platform','us.status','us.created_at as created_at','tu.id as tempid')->orderBy('us.created_at', 'DESC');

        // $userQuery->skip($start)->take($pageSize);
        $users  = $userQuery->get();

        if($count_filter == 0){
            $count_filter = $count_total;
        }
        return Datatables::of($users)
                    ->addIndexColumn()
                    ->with([
                    "recordsTotal" => $count_total,
                    "recordsFiltered" => $count_filter,
                    ])
                    ->addColumn('actions', function($user){
                        $parameter= Crypt::encrypt($user->id);
                        $html = '';
                        if (Helper::has_permission('users','edit')) {
                        $html .='<a data-toggle="tooltip" title="Login" href="'.config('app.liveurl').'admin-authenticate/'.$parameter.'" target="_blank"><i class="mdi mdi-login mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a title="View Details" class="show_user_data text-muted" user-id="'.$user->id.'"><i class="mdi mdi-eye mdi-24px"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        if(Helper::has_permission('users','delete')) {   
                        $html .='<a href="javascript:void(0);" class="text-danger delete_user"  user-id="'.$user->id.'" data-toggle="tooltip" data-placement="top" data-original-title="Delete"><i class="mdi mdi-delete mdi-24px"></i></a><form id="delete_user_'.$user->id.'" method="post" action="'.url('/delete-user').'">'.csrf_field().'<input type="hidden" name="user_key" value="'.$parameter.'"></form>';
                        }
                        if (Helper::has_permission('users','edit')) {
                            $html .='<form id="show_user_'.$user->id.'" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$user->phone.'"></form>';
                        }

                        return $html;

                        $actionbtn= "";

                        if($data->tempid == "" && $data->status == 0){
                            $actionbtn='&nbsp&nbsp&nbsp&nbsp<a href="javascript:void(0)" class="edit btn btn-primary btn-sm userAction" data-status="'.$data->status.'" data-user="'.$data->user_id.'" data-tag="Enable">Enable</a>';
                        }else if($data->status == 1){
                            $actionbtn='&nbsp&nbsp&nbsp&nbsp<a href="javascript:void(0)" class="edit btn btn-danger btn-sm userAction" data-status="'.$data->status.'" data-user="'.$data->user_id.'" data-tag="Disable">Disable</a>';
                        }
                           $btn = '<a href="javascript:void(0);" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-18"></i></a> <a href="javascript:void(0);" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"><i class="mdi mdi-eye font-18"></i></a> <a href="javascript:void(0);" class="text-muted" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-close font-18"></i></a> '.$actionbtn;
                            return $btn;
                    })
                    ->editColumn('name', function ($data){
                        return $data->firstname.' '.$data->lastname;
                    })
                    ->addColumn('confstatus', function ($data){
                        if( $data->tempid != "" && $data->status == 0 ){
                            $status='<span class="badge badge-danger">In Complete</span>';
                        }else if($data->status == 0){
                            $status='<span class="badge badge-danger">In Active</span>';
                        }else if($data->status == 1){
                            $status='<span class="badge badge-success">Active</span>';
                        }
                        return $status;
                    })
                    ->editColumn('created_at', function ($data) {
                        return date('d-m-Y H:i:s',strtotime($data->created_at));
                    })
                    ->rawColumns(['actions','platform','confstatus'])
                    ->skipPaging()
                    ->make(true);

        */
    }


    /**
    * User Enable / Disable
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function conf_user_status(Request $request){
        $userid  = $request->userid;
        $getuser = DB::table('users')->where('id',$userid)->first();
        if(empty($getuser)){
            return response()->json(['status'=>422,'msg'=>'User details not found']);
        }
        $status     = $getuser->status;
        $i_account  = $getuser->i_account;
        $email      = $getuser->email;
        switch ($status) {
            //Make Disable
            case 1:
                $changestatus = 0;
                $tagname      = 'Disabled';
                $method       = 'blockAccount';
                break;
            //Make Enable
            case 0:
                $changestatus = 1;
                $tagname      = 'Enabled';
                $method       = 'unblockAccount';
                break;
            default:
                break;
        }
        $xml_data    = SwitchHelper::switch_account_status_xml($method,$i_account);
        $temp        = SwitchHelper::call_switch_api($xml_data);

        if (!array_key_exists("fault", $temp)) {
            $userupdate = DB::table('users')->where('id',$userid)->update(['status'=>$changestatus]);
            if($changestatus == 1){
                $obj = new \stdClass();
                $obj->name = $getuser->first_name.' '.$getuser->last_name;
                $obj->subject = config('settings.app_name').'Conference Activation';
                $obj->heading = config('settings.app_name').'Conference Activation - Success';
                //Mail::to($email)
                    // ->cc()
                    // ->bcc('jijo.joseph@gencomtel.com')
                    //->send(new UserActivation($obj));
            }

            if($userupdate){
                return response()->json(['status'=>200,'msg'=>'User '.$tagname.' Successfully!!']);
            }else{
                return response()->json(['status'=>422,'msg'=>'User status changed failed']);
            }
        }else{
            return response()->json(['status'=>422,'msg'=>'User status changed failed']);
        }
    }
}
