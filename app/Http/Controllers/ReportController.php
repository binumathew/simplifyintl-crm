<?php



namespace App\Http\Controllers;



use DB;

use Auth;

use Crypt;

use Excel;

use Carbon;

use Helper;

use DataTables;

use App\Models\UserPayment;

use App\Exports\CustomExport;

use \App\Models\AutoPlan;

use \App\Models\UserPlan;

use App\Models\User;

use App\Models\UserInvoice;

use App\Models\UserInvoiceTransaction;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;


class ReportController extends Controller

{

    public function __construct()

    {

        $this->middleware('auth');

    }

    /**

     * Display a listing of payments.

     *

     * @return \Illuminate\Http\Response

     */

    public function index()

    {

        return view('report.index');

    }

    /**
    * Show the application autoplan report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_autoplan(){

        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }

        $tblplans = DB::table('tbl_plans')
                 ->select('id','plan_name','provider');

        $plans = DB::table('conference_plans')
                    ->select('id','plan_name','provider')
                    ->union($tblplans)
                    ->where('switch_id',1)->get();

        $providers = $tblplans->select('provider')->distinct()->get();

        return view('report.report-autoplan',compact('plans','providers'));
    }
    /**
    * Show the Autoplan report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_autoplan(Request $request)
    { 
        $querydatas = AutoPlan::query()
                        ->select('us.name','auto_plan.*','ss.phone_number','sl.stock_id',DB::raw("GROUP_CONCAT(ssl.stock_id) as child"),'us.phone','ct.currency_symbol')
                        ->join('users as us', 'us.id', '=', 'auto_plan.user_id')
                        ->join('country as ct', 'ct.id', '=', 'us.country_id')
                        ->leftjoin('tbl_sim_list as sl', 'sl.user_id', '=', 'auto_plan.user_id')
                        ->leftjoin('tbl_sim_stock as ss', 'ss.id', '=', 'sl.stock_id')
                        ->leftjoin("tbl_sim_list as ssl",DB::raw("FIND_IN_SET(ssl.user_id, auto_plan.user_list)"),">",\DB::raw("'0'"))->groupBy('auto_plan.id');

            if ($request->has('number') && $request->get('number') != "") {
                    $querydatas->where('ss.phone_number', 'like', "{$request->get('number')}%");
                    $querydatas->orWhere('us.phone', 'like', "+{$request->get('number')}%");
            }

            if ($request->has('from') && $request->from != "") {

                $from = Carbon::parse($request->from)->format('Y-m-d');

                if($request->to != ""){
                    $to   = Carbon::parse($request->to)->format('Y-m-d');
                }else{
                    $to   = Carbon::now()->format('Y-m-d');
                }

                $querydatas->whereDate('auto_plan.next_renewal', '>=', $from)
                              ->whereDate('auto_plan.next_renewal', '<=', $to);
            } 

            if ($request->has('status') && $request->get('status') != "") {
                    $querydatas->where('auto_plan.status', $request->get('status'));
            }

            if ($request->has('provider') && $request->get('provider') != "") {

                $tblplans = DB::table('tbl_plans')
                            ->select('id','provider')
                            ->distinct()
                            ->where('provider', 'like', "{$request->get('provider')}%");

                $getplan  = DB::table('conference_plans')
                            ->select('id','provider')
                            ->union($tblplans)
                            ->where('provider', 'like', "{$request->get('provider')}%")->get();

                $plans   = $getplan->toArray();
                $planids = array_values(array_column($plans, 'id'));

                if($request->get('provider') == 'Bridge'){

                 $querydatas->whereIn('auto_plan.plan_id', $planids)->where('plan_type','bridge');   
                }else{
                   $querydatas->whereIn('auto_plan.plan_id', $planids)->where('plan_type', '!=' , 'bridge'); 
                }
            }
            if ($request->has('plans') && $request->get('plans') != "") {

                    $ty     = explode("-", $request->get('plans'));
                    $type   = $ty[0];
                    $plan   = $ty[1];

                    if($type == 'conf'){
                        $getplan = DB::table('conference_plans')->where('plan_name', 'like', "{$plan}%")->get();
                        $plans   = $getplan->toArray();
                        $planids = array_values(array_column($plans, 'id'));
                        $querydatas->whereIn('auto_plan.plan_id', $planids)->where('plan_type','bridge');
                    }else{
                       $querydatas->where('auto_plan.plan_id', $plan)->where('plan_type', '!=' , 'bridge'); 
                    }
                    
            }                     
            $result = Datatables::eloquent($querydatas)
                        ->addColumn('plan', function (AutoPlan $user) {
                            return $user->plan->plan_name;
                        })
                        ->addColumn('provider', function (AutoPlan $user) {
                            return $user->plan->provider;
                        })
                        ->editColumn('phone_number', function ($user) {
                            return ($user->phone_number !="") ? $user->phone_number: str_replace("+", "", $user->phone); 
                        })
                        ->editColumn('child', function (AutoPlan $user) {
                            $child = $user->getChild();
                            return implode(' | ', $child);
                        })
                        ->editColumn('amount', function ($user) {
                            return $user->currency_symbol.' '.$user->amount;
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
                        ->editColumn('next_renewal', function ($date) {
                            return $date->next_renewal ? with(new Carbon($date->next_renewal))->format('d-m-Y') : '';
                        })
                        ->editColumn('created_at', function ($date) {
                         return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                        })
                        ->make(true);

            if($request->exportdata){
                    $result = collect($result->getData()->data);
                    $datas = $result->map(function ($item,$key) {
                        return collect($item)->only(['name','next_renewal','plan_type','amount','phone_number','child','plan','gateway','status','created_at','provider']);
                     });
                    $datas->prepend(array('NAME','NEXT RENEWAL','PLAN TYPE','AMOUNT','GATEWAY','STATUS','CREATED','PHONE NUMBER','CHILD','PLAN','PROVIDER'));
                    return Excel::download(new CustomExport($datas->toArray()), 'autoplan.csv');
                }else{
                    return $result;
                }
    }
    /**
    * Show the application credit card expiry report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_cardexpiry(){
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        $gateway = DB::table('auto_plan')->select('gateway')->distinct()->get();
        return view('report.report-cardexpiry',compact('gateway'));
    }
    /**
    * Show the card expiry report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_cardexpiry(Request $request)
    { 
        $querydatas = AutoPlan::query()
                    ->select('us.name','us.phone','us.email','uc.card_expiry','uc.card_type','auto_plan.gateway')
                    ->distinct()
                    ->join('users as us', 'us.id', '=', 'auto_plan.user_id')
                    ->join('user_credit_cards as uc', 'uc.id', '=', 'auto_plan.card_id');

        if ($request->has('card_expiry') && $request->get('card_expiry') != "") {
            $expiry = $request->get('card_expiry');
            $now = Carbon::now();
            switch ($expiry) {
                case 1:
                    $from = $now->startOfWeek()->format('Y-m-d');
                    $to   = $now->endOfWeek()->format('Y-m-d');
                    break;
                case 2:
                    $from = $now->firstOfMonth()->format('Y-m-d');
                    $to   = $now->endOfMonth()->format('Y-m-d');
                    break;
                case 3:
                    $from = $now->firstOfMonth()->addMonth()->format('Y-m-d');
                    $to   = $now->endOfMonth()->format('Y-m-d');
                    break;
                default:
                    break;
            }
            $querydatas->whereDate('uc.card_expiry', '>=', $from)
                          ->whereDate('uc.card_expiry', '<=', $to);
        }

        if ($request->has('card_gateway') && $request->get('card_gateway') != "") {
                $querydatas->where('auto_plan.gateway', 'like', "{$request->get('card_gateway')}%");
        }

        $result = Datatables::eloquent($querydatas)
                    ->editColumn('card_expiry', function ($date) {
                     return $date->card_expiry ? with(new Carbon($date->card_expiry))->format('d-m-Y') : '';
                    })
                ->make(true);

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['name','phone','email','card_expiry','card_type','gateway']);
             });
            $datas->prepend(array('NAME','PHONE','EMAIL','CARD EXPIRY (DD-MM-YYYY)','CARD TYPE','GATEWAY'));
            return Excel::download(new CustomExport($datas->toArray()), 'cardexpiry.csv');
        }else{
            return $result;
        }
    }
    /**
    * Show the application user report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_user(){
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        return view('report.report-user');
    }

    /**
    * Show the application user report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_user(Request $request){

        $querydatas = User::query()
                    ->select('name','phone','email','status','created_at')->orderBy('created_at','DESC'); 

        if ($request->has('number') && $request->get('number') != "") {
                $querydatas->where('phone', 'like', "+{$request->get('number')}%");
        }
        if ($request->has('user_status') && $request->get('user_status') != "") {
                $querydatas->where('status',$request->get('user_status'));
                
        }             
        $result = Datatables::eloquent($querydatas)
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
                    ->make(true);

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['name','phone','email','status','created_at']);
             });
            $datas->prepend(array('NAME','PHONE','EMAIL','STATUS','CREATED AT'));
            return Excel::download(new CustomExport($datas->toArray()), 'user.csv');
        }else{
            return $result;
        }
    }
    /**
    * Show the application usage report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_usage(){

        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        return view('report.report-usage');
    }
    /**
    * Show the usage report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_usage(Request $request)
    { 
        $querydatas = UserPlan::query()
                    ->select('us.name','us.phone','us.email','user_plans.*','ct.currency_symbol','us.country_id','us.id as user_id')
                    ->join('users as us', 'us.id', '=', 'user_plans.user_id')
                    ->join('country as ct', 'ct.id', '=', 'us.country_id')
                    ->where('user_plans.plan_type','sim');
                    //->where('user_plans.status',1);

        if ($request->has('number') && $request->get('number') != "") {

            $querydatas->where('us.phone', 'like', "+{$request->get('number')}%");
        }
        if ($request->has('usage_month') && $request->get('usage_month') != "") {
            $month = $request->usage_month;

            if($request->usage_year != ""){
                $year   = $request->usage_year;
            }else{
                $year   = Carbon::now()->format('Y');
            }
            $fromdate = '01-'.$month.'-'.$year;
            $from     = Carbon::parse($fromdate)->format('Y-m-d');
            $to       = Carbon::parse($from)->endOfMonth()->format('Y-m-d');

            $querydatas->whereDate('user_plans.created_at', '>=', $from)
                          ->whereDate('user_plans.created_at', '<=', $to);
        }
        // if ($request->has('usage_status') && $request->get('usage_status') != "") {

        //     $querydatas->where('user_plans.status',$request->get('usage_status'));
        // }
        $result = Datatables::eloquent($querydatas)
                    ->addColumn('voice', function ($data) {
                        return $data->call_cost;
                    })
                    ->addColumn('sms', function ($data) {
                        return $data->sms_cost;
                    })
                    ->addColumn('data', function ($data) {
                        return $data->data_cost;
                    })
                    ->addColumn('plan', function (UserPlan $user) {
                        return $user->plan->plan_name;
                    })
                    ->editColumn('call_usage', function ($data) {
                        return Helper::secToHR($data->call_usage);
                    })
                    ->editColumn('data_usage', function ($data) {
                        return Helper::bytesToGB($data->data_usage);
                    })
                    ->editColumn('service_total', function ($user) {
                        return $user->currency_symbol.$user->service_total;
                    })
                    ->addColumn('total', function ($data) {
                        $country        = Helper::getCountry($data->country_id)[0];
                        $getoutof       = Helper::vataddCalculation($data->service_total,$country->tax);
                        return $data->currency_symbol.$getoutof->total_amount;
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
                    ->addColumn('from', function () use($from){
                        return $from;
                    })
                    ->addColumn('to', function () use($to){
                        return $to;
                    })
                    ->editColumn('created_at', function ($date) {
                     return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                ->make(true);
        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['name','phone','email','plan','plan_type','data_usage','call_usage','sms_count','service_total','created_at','plan']);
             });
            $datas->prepend(array('NAME','PHONE','EMAIL','PLAN TYPE','DATA USAGE','CALL USAGE','SMS','TOTAL','CREATED AT','PLAN'));
            return Excel::download(new CustomExport($datas->toArray()), 'usage.csv');
        }else{
            return $result;
        }
    }
    /**
    * Show the usage report details pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function get_cdr_records(Request $request)
    { 
        $user = User::find($request->user);
        if($request->param == 'voice'){
            $getcdr = DB::table('user_calls')
                      ->where('user_id',$request->user)
                      ->whereDate('connect_date', '>=', $request->from)
                      ->whereDate('connect_date', '<=', $request->to)
                      ->orderBy('connect_date','DESC')
                      ->get();
        }
        if($request->param == 'sms'){
            $getcdr = DB::table('usage_history')
                      ->where('user_id',$request->user)
                      ->where('service_type','SMS_MO')
                      ->whereDate('date', '>=', $request->from)
                      ->whereDate('date', '<=', $request->to)
                      ->orderBy('date','DESC')
                      ->get();
        }
        if($request->param == 'data'){
            $getcdr = DB::table('usage_history')
                      ->where('user_id',$request->user)
                      ->where('service_type','DATA')
                      ->whereDate('date', '>=', $request->from)
                      ->whereDate('date', '<=', $request->to)
                      ->orderBy('date','DESC')
                      ->get();
        }
        return response()->json(['status'=>200,'response' => json_encode($getcdr),'currency_symbol'=>$user->country->currency_symbol]);
    }

        /*
    * List all orders which are shipped
    * Items which are activated and non activated are listed
    */
    public function report_order()
    {
        if (!Helper::has_permission('orders') && !Helper::has_permission('orders','view_own')) {
            abort(403,'Access denied');
        }
        $dealers = DB::table('admins')->select(DB::raw('concat(first_name," ",last_name) as dealer'),'promocode')->where('status', 1)->get();
        return view('report.report-order',compact('dealers'));
    }
    /**
    * Show the order pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_order(Request $request)
    { 
        $admin_id   = Auth::user()->id;
        $promocode  = Auth::user()->promocode;
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();
        array_push($where, $promocode);

        $order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone', DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"), DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"), DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` = rq.id LIMIT 1)) as sim_number"))->join('users as usr','usr.id','=','rq.user_id');

        if($request->sim_number != ''){
            $order_list = $order_list->join('tbl_sim_list as sl','request_id','=','rq.id')
            ->join('tbl_sim_stock as sk','sk.id','=','sl.stock_id');
        }

        if(Helper::has_permission('orders')) {
        }elseif(Helper::has_permission('orders','view_own')) {
            $order_list = $order_list->whereIn('rq.promocode', $where);
        }else{
            $order_list = $order_list->where('id', 0);
        }

        if ($request->delivery_status == 1) {
            $order_list = $order_list->where('delivery_status','1');
        } else if($request->delivery_status == 2) {
            $order_list = $order_list->where('delivery_status','2');
        } else if($request->delivery_status == 3) {
            $order_list = $order_list->where('delivery_status','0');
        }

        if($request->order_id){
            $order_list = $order_list->where('rq.order_id', $request->order_id);
        }
        if($request->user_phone){
            $order_list = $order_list->where('usr.phone', $request->user_phone);
        }
        if($request->from_date){
            $order_list = $order_list->whereDate('rq.created_at','>=', $request->from_date);
        }
        if($request->to_date){
            $order_list = $order_list->whereDate('rq.created_at','<=', $request->to_date);
        }
        if($request->sim_number){
            $order_list = $order_list->where('sk.sim_number', $request->sim_number);
        }
        $result = Datatables::queryBuilder($order_list)
                        ->editColumn('promocode', function ($order) {
                            return (($order->promocode)?$order->promocode:'SJ100');
                        })->editColumn('date', function ($order) {
                            return $order->date ? with(new Carbon($order->date))->format('d-m-Y') : '';
                        })->editColumn('ship_date', function ($order) {
                        if($order->ship_date)
                        return $order->ship_date ? with(new Carbon($order->ship_date))->format('d-m-Y') : '';
                        })
                        ->editColumn('delivery_status', function ($order) {
                            $stat = "";
                        switch ($order->delivery_status) {
                            case 0:
                                $stat = "Order Received";
                                break;
                            case 1:
                                $stat = "Ready To Activate";
                                break;
                            case 2:
                                $stat = "CallBack Pending";
                                break;
                            case 3:
                                $stat = "Activated";
                                break;
                        }
                        return $stat;
                        })
                        ->rawColumns(['promocode'])
                        ->make(true);

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['order_id','promocode','name','delivery_status','date','phone','sim_count','ship_date','sim_number']);
             });
            $datas->prepend(array('ORDER ID','PROMOCODE','CUSTOMER','DELIVERY STATUS','DATE','CUSTOMER NUMBER','SIM','SHIPPING DATE','SIM NUMBER'));
            return Excel::download(new CustomExport($datas->toArray()), 'order.csv');
        }else{
            return $result;
        }
    }
    /*
    * List report statistics
    * Items which are activated and non activated are listed
    */
    public function report_dashboard(Request $request)
    {
        $data      = [];
        $from = $to = "";

        if($request->timeperiod){
            $timeperiod = $request->timeperiod;
            $now        = Carbon::now();
            switch ($timeperiod) {
                case 1:
                    $from = $now->startOfWeek()->format('Y-m-d');
                    $to   = $now->endOfWeek()->format('Y-m-d');
                    break;
                case 2:
                    $from = $now->firstOfMonth()->format('Y-m-d');
                    $to   = $now->endOfMonth()->format('Y-m-d');
                    break;
                case 3:
                    $from = $now->subMonth(1)->firstOfMonth()->format('Y-m-d');
                    $to   = $now->endOfMonth()->format('Y-m-d');
                    break;
                default:
                    # code...
                    break;
            }
        }
        /* Users Details */
        $userdata           = $this->getUsers($from,$to);
        $data['user_data']  = json_encode($userdata);
        /* End of user details */

         /* Payment details */
        $paydata            = $this->getPayment($from,$to);
        $data['user_pay']   = json_encode(array_values($paydata));
        /* End of Payment details */

        /* Autoplan plan type */
        $plandata           = $this->getAutoplantype($from,$to);
        $data['plan_type']  = json_encode(array_values($plandata));
        /* End Autoplan plan type */

        /* Order based on delivery status */
        $orderdata             = $this->getOrderstatus($from,$to);
        $data['order_status']  = json_encode(array_keys($orderdata['orderdata']));
        $data['order_status_series']  = json_encode($orderdata['orderdataseries']);

        /* End Order based on delivery status */

        /* Order based on promocode */
        $promodata            = $this->getorderPromo($from,$to);
        $data['order_promo']  = json_encode($promodata);
        /* End Order based on promocode */

        /* Sim plan renewed */
        $simplandata            = $this->getsimplanrenew($from,$to);
        $data['simplan_renew']  = json_encode($simplandata);
        /* End Sim plan renewed */

        /* porting based */
        $porting                 = $this->getportingBased($from,$to);
        $data['porting_status']  = json_encode($porting['portingdata']);
        $data['porting_vals']    = json_encode($porting['portingvals']);

        /* End porting based */

        /* Autoplan plan based */
        $planstatus           = $this->getplanStatus($from,$to);
        $data['plan_status']  = json_encode(array_keys($planstatus['plandata']));
        $data['plan_status_series']  = json_encode($planstatus['planseries']);
        /* End Autoplan plan based */

        // print_r($data);
        // die();
        if($request->ajax()){
            return response()->json(['status'=>200,'data' => $data]);
        }else{
            
            return view('report.report-statistics',$data);  
        }
    }
    private function getUsers($from,$to){

        $userdata  = [];

        $users = DB::table('users')
                    ->select(DB::raw("COUNT(id) as usercount"),'status')
                    ->groupBy('status')->orderBy('status','DESC');
        $platform = DB::table('user_data')
                    ->select(DB::raw("COUNT(user_id) as usercount"),'user_platform')
                    ->groupBy('user_platform');

        if($from != ""){
            $users = $users->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        if($from != ""){
            $platform = $platform->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        $users       = $users->get();
        $platform    = $platform->get();

        $userdata['label'] = ['Active','InActive'];
        $userdata['data']  = [0,0];

        if($users->isNotEmpty()){
            foreach($users as $row) {
                $status = ($row->status == 1) ? 'Active' : 'InActive';
                $key    = array_keys($userdata['label'],$status); 
                if(!empty($key)){
                    $userdata['data'][$key[0]] = (int) $row->usercount;
                    $userdata['all'][]   = $row;
                    $userdata['dyn'][$status] = [(int) $row->usercount];
                }
            }  
        }
        if($platform->isNotEmpty()){
            foreach($platform as $row) {
                $type = ($row->user_platform != "") ? ucfirst(strtolower($row->user_platform)) : "Others";
                $userdata['label'][]    = $type; 
                $userdata['data'][]     = (int) $row->usercount;
                $userdata['all'][]      = $row;
                $userdata['dyn'][$type] = [(int) $row->usercount];
            }
        }
        return $userdata;  
    }
    private function getPayment($from,$to){
        $paydata = [];

        $payment = DB::table('user_payments')
                    ->select(DB::raw("COUNT(id) as paycount"),DB::raw('replace(payment_method," ", "") as payment_method' ),'status')
                    ->groupBy('payment_method','status');

        if($from != ""){
            $payment = $payment->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        $payment     = $payment->get();

        if($payment->isNotEmpty()){
            $keyval = ['fail','succ','suwerr','refund','cancel'];
            foreach($payment as $row) {
            
                if(isset($paydata[$row->payment_method])){
                    $paydata[$row->payment_method][$keyval[$row->status]] = $row->paycount;

                }else{
                    $paydata[$row->payment_method] = ['y'=>$row->payment_method,$keyval[$row->status]=>$row->paycount];
                }
            }  
        }
        return $paydata;
    }
    private function getAutoplantype($from,$to){
        $plandata = [];
        $plantype = DB::table('auto_plan')
                    ->select(DB::raw("COUNT(id) as plancount"),'plan_type')
                    ->groupBy('plan_type');
        if($from != ""){

            $plantype = $plantype->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        $plantype     = $plantype->get();

        if($plantype->isNotEmpty()){
            foreach ($plantype as $pkey => $plist) {
                array_push($plandata,['label'=>ucfirst(strtolower($plist->plan_type)),'value'=>$plist->plancount]);
            }
        }
        return $plandata;
    }
    private function getOrderstatus($from,$to){
        $orderdata = [];
        if($from == ""){
            $from      = Carbon::now()->subMonth(11)->format('Y-m-d');
            $to        = Carbon::now()->format('Y-m-d');
        }

        $orderstat = DB::table('tbl_sim_request')
                     ->select(DB::raw("COUNT(id) as ordercount"),DB::raw("DATE_FORMAT(created_at, '%M') month"),'delivery_status')  
                     ->whereDate('created_at','>=',$from)
                     ->whereDate('created_at','<=',$to)
                     ->groupBy('delivery_status','month')
                     ->orderBy('created_at', 'ASC')
                     ->get();

        if($orderstat->isNotEmpty()){
            $keyval = ['or_rec','redto_act','call_pen','actived','cancelled'];
            foreach($orderstat as $row) {
                
                if(isset($orderdata[$row->month])){
                    $orderdata[$row->month][$keyval[$row->delivery_status]] = $row->ordercount;
                }else{
                    $orderdata[$row->month] = ['or_rec'=>0,'redto_act'=>0,'call_pen'=>0,'actived'=>0,'y'=>$row->month];
                    $orderdata[$row->month][$keyval[$row->delivery_status]] = $row->ordercount; 
                }
            }  
        }
        $arr     = [];
        $arr1     = [];
        $arr2     = [];
        $arr3     = [];
        $arr4     = [];
        $result   = [];
        $j = 0;
        foreach ($orderdata as $okey => $olist) {
            if($j == 0){
                $arr['name']  = 'Order Received';
                $arr1['name'] = 'Ready to Activate';
                $arr2['name'] = 'Call Pending';
                $arr3['name'] = 'Activated';
                //$arr4['name'] = 'Cancelled';
                $j++;
            }
            $arr['data'][]  = $olist['or_rec'];
            $arr1['data'][] = $olist['redto_act'];
            $arr2['data'][] = $olist['call_pen'];
            $arr3['data'][] = $olist['actived'];
            //$arr4['data'][] = $olist['cancelled'];

        }
        array_push($result,$arr);
        array_push($result,$arr1);
        array_push($result,$arr2);
        array_push($result,$arr3);
        //array_push($result,$arr4);
        json_encode($result, JSON_NUMERIC_CHECK);
        $res['orderdata'] = $orderdata;
        $res['orderdataseries'] = $result;
        return $res;
    }
    private function getorderPromo($from,$to){
        $promodata  = [];
        $orderpromo = DB::table('tbl_sim_request')
                      ->select(DB::raw("COUNT(id) as ordercount"),'promocode')
                      ->groupBy('promocode')->orderBy('promocode','ASC');
        if($from != ""){
            
            $orderpromo = $orderpromo->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        $orderpromo     = $orderpromo->get();

        if($orderpromo->isNotEmpty()){
            foreach($orderpromo as $row) {
                array_push($promodata,['name'=>$row->promocode,'y'=>(int) $row->ordercount]);
            }  
        }
        return $promodata;
    }
    private function getsimplanrenew($from,$to){
        $simplandata = [];

        $simplan = DB::table('avoo_sim_log as asl')
                   ->select(DB::raw("COUNT(asl.plan_id) as plancount"),'tp.plan_name')
                   ->join('tbl_plans as tp', 'tp.id', '=', 'asl.plan_id')
                   ->groupBy('asl.plan_id');
        if($from != ""){
            $simplan = $simplan->whereDate('asl.created_at','>=',$from)
                           ->whereDate('asl.created_at','<=',$to);
        }
        $simplan     = $simplan->get();

        if($simplan->isNotEmpty()){
            foreach($simplan as $row) {
                array_push($simplandata,['label'=>$row->plan_name,'data'=>(int) $row->plancount]);
            }
        }
        return $simplandata;
    }
    private function getportingBased($from,$to){
        $portingdata  = [];
        $portingvals  = [];
        $porting      = DB::table('tbl_porting')
                        ->select(DB::raw("COUNT(id) as portcount"),'status')
                        ->groupBy('status');
        if($from != ""){
            $porting = $porting->whereDate('created_at','>=',$from)
                           ->whereDate('created_at','<=',$to);
        }
        $porting     = $porting->get();
        $keyval = ['Request Received','Request Initiated','Confirmed','Processed','Completed','Cancelled'];
        if($porting->isNotEmpty()){

            foreach($porting as $row) {
                $portingdata['label'][]    = $keyval[$row->status]; 
                $portingdata['data'][]     = (int) $row->portcount;
                array_push($portingvals,['label'=>$keyval[$row->status],'data'=>(int) $row->portcount]);
            }
        }else{
            foreach ($keyval as $kkey => $kval) {
                $portingdata['label'][]    = $kval; 
                $portingdata['data'][]     = (int) 0;
            }
        }
        $res['portingdata'] = $portingdata;
        $res['portingvals'] = $portingvals;
        return $res;
    }
    private function getplanStatus($from,$to){
        $planstatusdata  = [];
        $simplanstat  = DB::table('auto_plan as ap')
                        ->select(DB::raw("COUNT(ap.plan_id) as plancount"),'ap.status','tp.plan_name')
                        ->join('tbl_plans as tp', 'tp.id', '=', 'ap.plan_id')
                        ->whereIn('ap.plan_type',['sim','switch'])
                        ->groupBy('ap.plan_id','ap.status');
        if($from != ""){
            $simplanstat = $simplanstat->whereDate('ap.created_at','>=',$from)
                           ->whereDate('ap.created_at','<=',$to);
        }
        $simplanstat     = $simplanstat->get();

        if($simplanstat->isNotEmpty()){
            $keyval = ['InActive','Active'];
            foreach($simplanstat as $row) {
                $planname = 'Sim -'.$row->plan_name;
                if(isset($planstatusdata[$planname])){
                    $planstatusdata[$planname][$keyval[$row->status]] = $row->plancount;
                }else{
                    $planstatusdata[$planname] = ['InActive'=>0,'Active'=>0,'y'=>$planname];
                    $planstatusdata[$planname][$keyval[$row->status]] = $row->plancount; 
                }
            }  
        }
        $confplanstat  = DB::table('auto_plan as ap')
                        ->select(DB::raw("COUNT(ap.plan_id) as plancount"),'ap.status','cp.plan_name')
                        ->join('conference_plans as cp', 'cp.id', '=', 'ap.plan_id')
                        ->where('ap.plan_type','bridge')
                        ->groupBy('ap.plan_id','ap.status');
        if($from != ""){
            $confplanstat = $confplanstat->whereDate('ap.created_at','>=',$from)
                           ->whereDate('ap.created_at','<=',$to);
        }
        $confplanstat     = $confplanstat->get();

        if($confplanstat->isNotEmpty()){
            $keyval = ['InActive','Active'];
            foreach($confplanstat as $row) {
                $planname = $row->plan_name;
                if(isset($planstatusdata[$planname])){
                    $planstatusdata[$planname][$keyval[$row->status]] = $row->plancount;
                }else{
                    $planstatusdata[$planname] = ['InActive'=>0,'Active'=>0,'y'=>$planname];
                    $planstatusdata[$planname][$keyval[$row->status]] = $row->plancount; 
                }
            }  
        }
        $arr     = [];
        $arr1     = [];
        $result   = [];
        $j = 0;
        foreach ($planstatusdata as $okey => $olist) {
            if($j == 0){
                $arr['name']  = 'Active';
                $arr1['name'] = 'InActive';
                $j++;
            }
            $arr['data'][]  = $olist['Active'];
            $arr1['data'][] = $olist['InActive'];
        }
        array_push($result,$arr);
        array_push($result,$arr1);
        json_encode($result, JSON_NUMERIC_CHECK);
        $res['plandata'] = $planstatusdata;
        $res['planseries'] = $result;
        return $res;
    }




    /**

     * Display a listing of payment details.

     *

     * @return \Illuminate\Http\Response

     */

    public function renewal_report(Request $request)

    {

        return view('report.renewal');

    }



    /**

     * Display a listing of payment details.

     *

     * @return \Illuminate\Http\Response

     */

    public function auto_recharge_report(Request $request)

    {

        return view('report.recharge');

    }



    /**

     * Display a listing of payment details.

     *

     * @return \Illuminate\Http\Response

     */

    public function card_expiry_report(Request $request)

    {

        return view('report.card_expiry');

    }



    /**

     * Display a listing of payment details.

     *

     * @return \Illuminate\Http\Response

     */

    public function cancellation_report(Request $request)

    {

        return view('report.cancellation');

    }

    /**
    * Show the application user invoice report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_invoice(){

        if (!Helper::has_permission('user_invoice')) {
            abort(403,'Access denied');
        }
        return view('report.report-invoice');
    }
    /**
    * Show the usage user invoice pagination
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_invoice(Request $request)
    { 
        $payments = UserInvoice::join('users as usr','usr.id','=','user_id')
                        ->join('country as c','usr.country_id','=','c.id')
                        ->leftJoin('tbl_sim_list as sl','sl.user_id','=','usr.id')
                        ->leftJoin('tbl_sim_request as rq','rq.id','=','sl.request_id')
                        //->where('user_invoices.currency_code',$request->input('currency','GBP'))
                        ->orderBy('user_invoices.date','DESC')
                        ->orderBy('user_invoices.user_id');
                        
        if ($request->order_id) {
            $payments->where('rq.order_id', $request->order_id);
        }
        if ($request->phone) {
            $phone = ltrim($request->phone, '0');
            $payments->where('usr.phone', 'like', '%'.$phone);
        }
        if (!empty($request->from)) {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            $payments->whereDate('user_invoices.date', '>=', $from);
            if($request->to){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $payments->whereDate('user_invoices.date', '<=', $to);
            }
        }else{
            // $from = Carbon::now()->startofMonth()->format('Y-m-d');
            // $payments->whereDate('user_invoices.date', $from);
        }
        if($request->payment_status != '') {
            $payments->where('user_invoices.status', $request->payment_status);
        }
        $payments = $payments->select(
            'rq.order_id',
            'user_invoices.id',
            'usr.id as user_id',
            'usr.name',
            'usr.phone',
            'usr.email',
            'user_invoices.currency_code',
            'sub_total',
            'user_invoices.tax as tax',
            'total',
            'credits_applied',
            'amount_due',
            'user_invoices.date',
            'paid_at',
            'user_invoices.status as status',
            'user_invoices.last_payment_request_at',
            'user_invoices.payment_requests_count'
            
        );               
        $result =  Datatables::eloquent($payments)
                ->editColumn('name', function ($payment) {
                    return $payment->name;
                })->editColumn('created_at', function ($payment) {
                    return Helper::date_format($payment->date);
                })->editColumn('last_payment_request_at', function ($payment) {
                    return Carbon::parse($payment->last_payment_request_at)->format('d-m-Y');
                })->editColumn('date', function ($payment) {
                    return Carbon::parse($payment->date)->format('d-m-Y');
                })->editColumn('paid_at', function ($payment) {
                    return ($payment->paid_at != '0000-00-00') ? Carbon::parse($payment->paid_at)->format('d-m-Y') : '';
                })
                ->addColumn('failed_desc', function ($payment){
                    $failed_desc = '';
                    if($payment->status == 8){
                        if($payment->transactions->isNotEmpty()){
                            foreach($payment->transactions as $key => $txn){
                                if($txn->txn_meta->isNotEmpty()){
                                    foreach($txn->txn_meta as $tkey => $tmeta){
                                      $failed_desc .= json_encode($tmeta->meta_data);
                                    }
                                }
                            }
                        }
                        return $failed_desc;
                    }
                })
                ->addColumn('downloadurl', function ($payment){
                    return Crypt::encrypt($payment->id);
                })
                ->editColumn('action', function ($payment) {
                    $paylink = '';
                    // if (Helper::has_permission('user_invoice','edit')) {
                        // if($payment->status == 8){
                        //     $paylink = '<a href="javascript:void(0);"><button class="btn btn-primary btn-sm pay_link"  title="Send Payment Link" data-id="'.Crypt::encrypt($payment->id).'">Pay Link</button></a>';
                        // }
                    // }
                    return $paylink;
                    // if(Helper::has_permission('payment_history', 'edit') && $payment->status == 1){
                    //     $refund = '<a href="javascript:void(0);" title="Refund" class="action_refund text-danger" data-id="'.Crypt::encrypt($payment->id).'" data-amount="'.$payment->total_amount.'" data-currency="'.$payment->currency_symbol.'"><i class="mdi mdi-undo-variant mdi-24px"></i></a>';
                    // }
                    //return '<form class="grid_form" method="post" action="'.url('/user-details').'">'.csrf_field().'<input type="hidden" name="identifier" value="'.$payment->phone.'"><a title="View Details"  href="javascript:void(0);"  class="show_user_data text-muted m-r-10"><i class="mdi mdi-eye mdi-24px"></i></a> '. $refund .'</form>';
                })
                ->rawColumns(['name','action'])
                ->make(true); 
        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                switch ($item->status) {
                    case 0:
                        $item->status = 'Not Processed';
                        break;
                    case 1:
                        $item->status = 'Paid';
                        break;
                    case 2:
                        $item->status = 'Partially paid';
                        break;
                    case 3:
                        $item->status = 'unpaid';
                        break;
                    case 4:
                        $item->status = 'cancel';
                        break;
                    case 5:
                        $item->status = 'Pending';
                        break;
                    case 6:
                        $item->status = 'Refund';
                        break;
                    case 7:
                        $item->status = 'Dispute';
                        break;
                    case 8:
                        $item->status = 'Failed';
                        break;
                    default:
                        $item->status = 'Draft';
                        break;
                }
                return collect($item)->only(['order_id','name','phone','email','status','date','sub_total','tax','total','credits_applied','amount_due','paid_at']);
                });
            $datas->prepend(array('Order ID','NAME','PHONE','EMAIL','SUB TOTAL','TAX','TOTAL','CREDITS','AMOUNT DUE','INVOICE DATE','PAYMENT DATE','STATUS'));
            return Excel::download(new CustomExport($datas->toArray()), 'invoice.csv');
        }else{
            return $result;
        }     
    }
    /**
    * Show the application user invoice report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_invoice_txn(){

        if (!Helper::has_permission('user_invoice')) {
            abort(403,'Access denied');
        }
        return view('report.report-invoice-txn');
    }
    /**
    * Show the usage user invoice pagination
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_invoice_txn(Request $request)
    { 
        $payments = UserInvoiceTransaction::join('user_invoices as usr_inv','usr_inv.id','=','invoice_id')
                        ->join('users as usr','usr.id','=','usr_inv.user_id')
                        ->join('country as c','usr.country_id','=','c.id')
                        ->leftJoin('user_invoice_transactions_meta as utm','utm.inv_txn_id','=','user_invoice_transactions.id')
                        ->join('tbl_sim_list as sl','sl.user_id','=','usr.id')
                        ->join('tbl_sim_request as rq','rq.id','=','sl.request_id')
                        ->orderBy('user_invoice_transactions.date','DESC')
                        ->orderBy('usr_inv.user_id');

        if ($request->order_id) {
            $payments->where('rq.order_id', $request->order_id);
        }
        if ($request->transaction_id) {
            $payments->where('user_invoice_transactions.transaction_id', $request->transaction_id);
        }
        if ($request->phone) {
            $phone = ltrim($request->phone, '0');
            $payments->where('usr.phone', 'like', '%'.$phone);
        }
        if (!empty($request->from)) {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            $payments->whereDate('user_invoice_transactions.date', '>=', $from);
            if($request->to){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $payments->whereDate('user_invoice_transactions.date', '<=', $to);
            }
        }
        if($request->payment_status != '') {
            $payments->where('user_invoice_transactions.status', $request->payment_status);
        }
        $payments = $payments->select(
            'rq.order_id',
            'user_invoice_transactions.id',
            'usr.id as user_id',
            'usr.name',
            'usr.phone',
            'usr.email',
            'user_invoice_transactions.date as inv_txn_date',
            'user_invoice_transactions.currency_code',
            'user_invoice_transactions.transaction_id',
            'utm.meta_data as meta_data',
            'amount',
            'user_invoice_transactions.status as status'
            
        );            
        $result =  Datatables::eloquent($payments)
                ->editColumn('name', function ($payment) {
                    return $payment->name;
                })->addColumn('date', function ($payment) {
                    return Carbon::parse($payment->inv_txn_date)->format('d-m-Y');
                })->addColumn('failed_desc', function ($payment) {
                    return strlen($payment->meta_data) >= 200 ? isset(json_decode($payment->meta_data)->error) ? json_decode($payment->meta_data)->error->message: json_decode($payment->meta_data)->message : '';
                })
                ->rawColumns(['name','action'])
                ->make(true);  

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                switch ($item->status) {
                    case 0:
                        $item->status = 'Not Processed';
                        break;
                    case 1:
                        $item->status = 'Paid';
                        break;
                    case 2:
                        $item->status = 'Partially paid';
                        break;
                    case 3:
                        $item->status = 'unpaid';
                        break;
                    case 4:
                        $item->status = 'cancel';
                        break;
                    case 5:
                        $item->status = 'Pending';
                        break;
                    case 6:
                        $item->status = 'Refund';
                        break;
                    case 7:
                        $item->status = 'Dispute';
                        break;
                    case 8:
                        $item->status = 'Failed';
                        break;
                    default:
                        $item->status = 'Draft';
                        break;
                }
                return collect($item)->only(['order_id','name','phone','email','status','date','transaction_id','amount']);
                });
            $datas->prepend(array('Order ID','NAME','PHONE','EMAIL','TRANSACTION','AMOUNT','STATUS','DATE'));
            return Excel::download(new CustomExport($datas->toArray()), 'invoice_txn.csv');
        }else{
            return $result;
        }  
    }
}

