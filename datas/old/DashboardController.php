<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Excel;
use Crypt;
use Carbon;
use Helper;
use DataTables;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\UserPayment;
use App\Exports\CustomExport;
use Illuminate\Http\Request; 
use \App\Models\RolePermissions;
use \App\Models\SimList;
use \App\Models\SimRequest;
use \App\Models\Credit;
use \App\Models\AutoPlan;
use \App\Models\AutoRecharge;
use \App\Models\Country;
use \App\Models\DiscountCoupon;
use \App\Models\DealerPayHistory;
use Illuminate\Support\Str; 

class DashboardController extends Controller
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
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index(Request $request)
    {
        $admin_id =  Auth::user()->id;
        $promocode = Auth::user()->promocode;
        $cur_list = ['GBP'=>'£','USD'=>'$','EUR'=>'€'];
        $filter = isset($request->filter)?$request->filter:'today';
        $duration = isset($request->duration)?$request->duration:'month';
        $currency = isset($request->currency)?$request->currency:Helper::get_option('currency');        
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('id','promocode')->toArray();        
        array_push($where, $admin_id);
        $where_promo = array_keys($where);
        array_push($where_promo, $promocode);
        $transaction = $orders = $chart = $elements = [];
        $ordered = $activated = $revenue = $recurring = 0;
        $act_percent = $ord_percent = $rev_percent = $rec_percent = 0;
        $prev_order = $prev_activated = $prev_revenue = $prev_recurring = 0;

        switch($filter){
            case 'today':
                $date_range = [date('Y-m-d')." 00:00:01", date('Y-m-d')." 23:59:59"];
                $prev_range = [date('Y-m-d',strtotime("-1 days"))." 00:00:01", $date_range[0]];   
            break;
            case 'this_week':
                $date = date('Y-m-d 00:00:01', strtotime('-'.date('w').' days'));
                $date_range = [$date, date('Y-m-d')." 23:59:59"];
                $prev_range = [date('Y-m-d', strtotime('-7 day', strtotime($date)))." 00:00:01", $date];
            break;
            case 'last_week':
                $date = date('Y-m-d 00:00:01', strtotime('-'.date('w').' days'));
                $date_range = [date('Y-m-d', strtotime('-7 day', strtotime($date)))." 00:00:01", $date];
                $prev_range = [date('Y-m-d', strtotime('-14 day', strtotime($date)))." 00:00:01", $date_range[0]];
            break;
            case 'this_month':
                $date_range = [date('Y-m-01')." 00:00:01", date('Y-m-d')." 23:59:59"];
                $prev_range = [date('Y-m-01',strtotime("-1 months"))." 00:00:01", date('Y-m-t',strtotime("-1 months"))." 23:59:59"];
            break;
            case 'last_month':
                $date_range = [date('Y-m-01',strtotime("-1 months"))." 00:00:01", date('Y-m-t',strtotime("-1 months"))." 23:59:59"];
                $prev_range = [date('Y-m-1',strtotime("-2 months"))." 00:00:01", date('Y-m-t',strtotime("-2 months"))." 23:59:59"];
            break;
            case 'this_year':
                $date_range = [date('Y-01-01')." 00:00:01", date('Y-m-d')." 23:59:59"];
                $prev_range = [date('Y-01-01',strtotime("-1 year"))." 00:00:01", $date_range[0]];
            break;
            case 'last_year':
                $year = date('Y')-1;
                $date_range = [date("$year-01-01")." 00:00:01", date("$year-12-31")." 23:59:59"];
                $prev_range = [date('Y-01-01',strtotime("-2 years"))." 00:00:01", $date_range[0]];
            break;                                        
        }
        $trans_query = UserPayment::select('user_payments.*','name')->join('users as u','user_id','=','u.id');
        if (Helper::has_permission('payment_history')){
            $transaction = $trans_query->take(5)->orderby('id','desc')->get();
        }elseif(Helper::has_permission('payment_history','view_own')) {
            $transaction = $trans_query->whereIn('dealer_id', $where)->take(5)->orderby('id','desc')->get();
        }
        $order_query = SimRequest::select('*');
        $order_cur_qry = SimRequest::whereBetween('created_at', $date_range);
        $order_prev_qry = SimRequest::whereBetween('created_at', $prev_range);
        $cur_act_qry = DB::table('avoo_sim_log')->join('users as u','user_id','=','u.id')
                            ->whereBetween('avoo_sim_log.created_at', $date_range)
                            ->where('category','BUNDLE_SUBSCRIPTION');
        $prev_act_qry = DB::table('avoo_sim_log')->join('users as u','user_id','=','u.id')
                            ->whereBetween('avoo_sim_log.created_at', $prev_range)
                            ->where('category','BUNDLE_SUBSCRIPTION');
        $revenue_qry = UserPayment::join('users as u','user_id','=','u.id')->where('currency', $currency)
                            ->whereBetween('user_payments.created_at', $date_range);
        $revenue_prev_qry = UserPayment::join('users as u','user_id','=','u.id')->where('currency', $currency)
                            ->whereBetween('user_payments.created_at', $prev_range);
        $recurring_qry = DB::table('avoo_sim_log')->join('users as u','user_id','=','u.id')
                            ->whereBetween('avoo_sim_log.created_at', $date_range)->where('category','RECURRING');
        $recurring_prev_qry = DB::table('avoo_sim_log')->join('users as u','user_id','=','u.id')
                            ->whereBetween('avoo_sim_log.created_at', $date_range)->where('category','RECURRING');
        if (Helper::has_permission('orders')){
            $ordered = $order_cur_qry->count();
            $prev_order = $order_prev_qry->count();  
            $activated = $cur_act_qry->count();
            $prev_activated = $prev_act_qry->count();
            $revenue = $revenue_qry->sum('total_amount'); 
            $prev_revenue = $revenue_prev_qry->sum('total_amount');     
            $recurring = $recurring_qry->count(); 
            $prev_recurring = $recurring_prev_qry->count();    
            $orders = $order_query->take(5)->orderby('id','desc')->get();
        }elseif(Helper::has_permission('orders','view_own')) {
            $ordered = $order_cur_qry->whereIn('promocode', $where_promo)->count();
            $prev_order = $order_prev_qry->whereIn('promocode', $where_promo)->count();
            $activated = $cur_act_qry->whereIn('promocode', $where_promo)->count();
            $prev_activated = $prev_act_qry->whereIn('promocode', $where_promo)->count(); 
            $revenue = $revenue_qry->whereIn('dealer_id', $where)->sum('total_amount');
            $prev_revenue = $revenue_prev_qry->whereIn('dealer_id', $where)->sum('total_amount'); 
            $recurring = $recurring_qry->whereIn('dealer_id', $where)->count();
            $prev_recurring = $recurring_prev_qry->whereIn('dealer_id', $where)->count(); 
            $orders = $order_query->whereIn('promocode', $where_promo)->take(5)->orderby('id','desc')->get();
        }
        // $ordered = 2;
        // $prev_order = 20;
        if(($ordered != 0)){
            $ord_percent = number_format((($ordered - $prev_order)/$ordered)*100,2,'.','');
        }
        // $activated = 300;
        // $prev_activated = 7;
        if(($activated != 0)){
            $act_percent = number_format((($activated - $prev_activated)/$activated)*100,2,'.','');
        }
        // $revenue =10;
        // $prev_revenue = 1500;
        if(($revenue != 0)){
            $rev_percent = number_format((($revenue - $prev_revenue)/$revenue)*100,2,'.','');
        }
        // $recurring = 150;
        // $prev_recurring = 1;
        if(($recurring != 0)){
            $rec_percent = number_format((($recurring - $prev_recurring)/$recurring)*100,2,'.','');
        }

        $listed_plan = DB::table('tbl_plans')->select('id','sell_price','plan_name')->where('listed', 1)->get();
        switch($duration){
            case 'day':
                $chart_rage = [date('Y-m-d',strtotime("-6 days"))." 00:00:01", date('Y-m-d')." 23:59:59"];
                $created = DB::raw('DATE_FORMAT(DATE(created_at), "%Y-%m-%d") as created');
                $groupBy = DB::raw('DATE(created_at)');
                $interval = new DatePeriod(new DateTime($chart_rage[0]), new DateInterval('P1D'), new DateTime($chart_rage[1]));
                $format = 'Y-m-d';
            break;
            case 'month':
                $chart_rage = [date('Y-m-01',strtotime("-4 months")), date('Y-m-t')];
                $created = DB::raw('MONTHNAME(created_at) as created');
                $groupBy = DB::raw('MONTH(created_at)');
                $interval = new DatePeriod(new DateTime($chart_rage[0]), new DateInterval('P1M'), new DateTime($chart_rage[1]));
                $format = 'F';
            break;
            case 'year':
                $chart_rage = [date('Y-01-01',strtotime("-4 years")), date('Y-m-t')];
                $created = DB::raw('YEAR(created_at) as created');
                $groupBy = DB::raw('YEAR(created_at)');
                $interval = new DatePeriod(new DateTime($chart_rage[0]), new DateInterval('P1Y'), new DateTime($chart_rage[1]));
                $format = 'Y';
            break;
        }
        $details = ['x']; 
        foreach ($interval as $value) {
            array_push($details, $value->format($format));  
            array_push($elements, $value->format($format));      
        }
        $chart[] = $details;
        foreach($listed_plan as $plan){
            $activation = DB::table('avoo_sim_log')->select(DB::raw('count(*) as count'), $created)->whereBetween('created_at', $chart_rage)->where('plan_id',$plan->id)
                        ->orderBy('created_at', 'desc')->groupBy($groupBy)->pluck('count','created')->toArray();
            $details = [$plan->plan_name]; 
            foreach ($elements as $element) {
                $count = (isset($activation[$element]))?$activation[$element]:0;                
                array_push($details, $count);
                //number_format(($count*$plan->sell_price),2,'.','')
            }
            $chart[] = $details;              
        }
        $earnings['weekly'] =  UserPayment::select(DB::raw('sum(total_amount) as total_amount'), DB::raw('DATE(created_at) as created'))->whereBetween('created_at', [date('Y-m-d',strtotime('-'.date('w').' days'))." 00:00:01", date('Y-m-d')." 23:59:59"])->where('currency',$currency)->where('status',1)->groupBy(DB::raw('DATE(created_at)'))->pluck('total_amount','created')->toArray();        
        $earnings['monthly'] = UserPayment::select(DB::raw('sum(total_amount) as total_amount'),DB::raw('DATE(created_at) as created'))->whereBetween('created_at', [date('Y-m-01')." 00:00:01", date('Y-m-d')." 23:59:59"])->where('currency',$currency)->where('status',1)->groupBy(DB::raw('DATE(created_at)'))->pluck('total_amount','created')->toArray();
        $earnings['yearly'] = UserPayment::select(DB::raw('sum(total_amount) as total_amount'), DB::raw('DATE(created_at) as created'))->whereBetween('created_at', [date('Y-01-01')." 00:00:01", date('Y-m-d')." 23:59:59"])->where('currency',$currency)->where('status',1)->groupBy(DB::raw('DATE(created_at)'))->pluck('total_amount','created')->toArray();
        return view('dashboard',compact('transaction','orders','ordered','activated','chart','earnings','currency','cur_list','duration','revenue','recurring','filter','act_percent','ord_percent','rev_percent','rec_percent'));                
    } 

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function order_check()
    {  
        $last_min = Carbon::now()->subMinutes(1)->toDateTimeString();
        $lastmin_order = DB::table('tbl_sim_request')->where('delivery_status',0)->count();
        //->where('created_at', '>=', $last_min)
        echo $lastmin_order;
    }

    /**
    * Show the fraudsters list.
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
        return view('fraudsters-list', compact('fraudsters'));
    }
    
    /**
    * Function delete throttle.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_fraudsters(Request $request)
    {         
        if (!Helper::has_permission('fraudsters','delete')) {
            abort(403,'Access denied');
        }
        DB::table('fraudsters')->where('id',$request->id)->delete();
        return[
                'error' => false,
                'message' => 'User removed sucessfully',
            ];
    } 

    /**
    * Show the did pool numbers.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_did_numbers()
    { 
        if (!Helper::has_permission('did_pool')) {
            abort(403,'Access denied');
        }
        $dids = DB::table('didlist')->get();
        return view('did-pool', compact('dids'));
    }

    /**
    * Show the sim stock.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_sim_stock()
    { 
        if (!Helper::has_permission('sim_management')) {
            abort(403,'Access denied');
        }
        return view('sim-management');
    }

    /**
    * Show the order report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_order()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        $promocode = DB::table('admins')->select('promocode')->get();

        $plans = DB::table('tbl_plans')->select('id','plan_name')->get();
        return view('report.report-order',compact('plans','promocode'));
    }

    /**
    * Show the order report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_order(Request $request)
    { 
        $users   = SimList::query()
            ->select(['tbl_sim_list.autoplan_id', 'sr.order_id', 'sr.promocode','sr.delivery_status', 'sr.created_at', 'us.name as username',  'usr.name as username', 'ap.next_renewal', 'ss.phone_number', 'up.total_amount',  'up.status'
            ])
            ->join('tbl_sim_request as sr', 'sr.id', '=', 'tbl_sim_list.request_id')
            ->leftJoin('users as us', 'us.id', '=', 'tbl_sim_list.user_id')
            ->leftJoin('users as usr', 'usr.id', '=', 'sr.user_id')
            ->join('tbl_sim_stock as ss','ss.id','=','tbl_sim_list.stock_id')
            ->join('auto_plan as ap','ap.id','=','tbl_sim_list.autoplan_id')
            ->join('user_payments as up','up.id','=','sr.payment_id');

        if ($request->has('promocode') && $request->promocode != -1) {
            $users->where('sr.promocode',$request->promocode);
        }
        if ($request->has('plan') && $request->plan != -1) {
            $users->where('ap.plan_id', $request->plan);
        }
        if ($request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $users->whereDate('tbl_sim_list.created_at', '>=', $from)
                          ->whereDate('tbl_sim_list.created_at', '<=', $to);
            }else{
                $users->whereDate('tbl_sim_list.created_at', $from);
            }
        }

        $result = Datatables::
                eloquent($users)
                ->addColumn('plan', function (SimList $user) {
                    return $user->auto_plan->plan ? $user->auto_plan->plan->plan_name : '';
                })
                ->editColumn('next_renewal', function ($date) {
                return $date->next_renewal ? with(new Carbon($date->next_renewal))->format('d-m-Y') : '';
                })
                ->editColumn('created_at', function ($date) {
                return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                })
                ->editColumn('delivery_status', function ($user) {
                    $delistat = "";
                    switch ($user->delivery_status) {
                        case 0:
                            $delistat = "Received";
                            break;
                        case 1:
                            $delistat = "Shipped";
                            break;
                        case 2:
                            $delistat = "Activated";
                            break;
                        default:
                            $delistat = "";
                            break;
                    }
                return $delistat;
                })
                ->editColumn('status', function ($user) {
                    $stat = "";
                    switch ($user->status) {
                        case 0:
                            $stat = "Failure";
                            break;
                        case 1:
                            $stat = "Success";
                            break;
                        case 2:
                            $stat = "Success With Error";
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
                return collect($item)->except(['autoplan_id']);
             });
            $datas->prepend(array('ORDER ID','PROMOCODE','DELIVERY STATUS','CREATED','USERNAME','NEXT RENEWAL','PHONE NUMBER','TOTAL AMOUNT','STATUS','PLAN'));
            return Excel::download(new CustomExport($datas->toArray()), 'order.csv');
        }else{
        return $result;
        }

    }

    /**
    * Show the port report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_port()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }

        $promocode = DB::table('admins')->select('promocode')->get();
        return view('report.report-port', compact('promocode'));
    }

    /**
    * Show the Port report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_port(Request $request)
    { 
        $users = DB::table('tbl_porting as tp')->select(['tp.id', DB::raw('LPAD(TRIM(LEADING "44" from phone_number),11,0) as phone_number'), DB::raw('LPAD(TRIM(LEADING "44" from porting_to),11,0) as porting_to'), 'pac_number', 'provider', 'reference_id', 'expected_date','tp.created_at', 'promocode', 'tp.status', DB::raw('LPAD(TRIM(LEADING "44" from temp_number),11,0) as temp_number')])
                    ->join('tbl_sim_stock as ss','ss.id','=','stock_id');

        if ($request->status != -1) {
            $users->where('tp.status',$request->status);
        }

        if ($request->promocode != -1) {
            $users->where('tp.promocode',$request->promocode);
        }

        if ($request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $users->whereDate('tp.created_at', '>=', $from)
                          ->whereDate('tp.created_at', '<=', $to);
            }else{
                $users->whereDate('tp.created_at', $from);
            }
        }

        $users = $users->get();
        $result = Datatables::of($users)
                ->editColumn('status', function ($user) {
                    $stat = "";
                    switch ($user->status) {
                        case 0:
                            $stat = "Received";
                            break;
                        case 1:
                            $stat = "Initiated";
                            break;
                        case 2:
                            $stat = "Confirmed";
                            break;
                        case 3:
                            $stat = "Processed";
                            break;
                        case 4:
                            $stat = "Completed";
                            break;
                        case 5:
                            $stat = "Canceled";
                            break;
                        default:
                            $stat = "";
                            break;
                    }
                return $stat;
                })
                ->editColumn('phone_number', function ($user) {
                    return ($user->status == 4)? $user->temp_number:$user->phone_number;
                })
                ->editColumn('created_at', function ($user) {
                return $user->created_at ? with(new Carbon($user->created_at))->format('d-m-Y') : '';
                })
                ->make(true);

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->except(['id','temp_number']);
             });
            $datas->prepend(array('TEMP NUMBER','PORTING TO','PAC NUMBER','PROVIDER','REFERENCE ID','EXPECTED DATE','CREATED AT','PROMOCODE','STATUS'));

            return Excel::download(new CustomExport($datas->toArray()), 'porting.csv');
        }else{
        return $result;
        }
    }

    /**
    * Show the EE Log report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_eelog()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        return view('report.report-eelog');
    }

    /**
    * Show the EE Log report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_eelog(Request $request)
    { 
        $users = DB::table('avoo_sim_log as sl')->select(['sl.id', 'usr.name','usr.email', 'msisdn', 'category', 'value', 'reference_id', 'sl.created_at'])
                    ->join('users as usr','usr.id','=','user_id');
        return Datatables::of($users)
            ->filter(function ($query) use ($request) {
                if ($request->has('category') && $request->get('category') != "") {
                    $query->where('category', 'like', "{$request->get('category')}%");
                }

                if ($request->has('name') && $request->get('name') != "") {
                    $query->where('usr.name', 'like', "{$request->get('name')}%");
                }
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at ? with(new Carbon($user->created_at))->format('d-m-Y') : '';
                })
            ->make(true);
    }

    /**
    * Show the Auto plan report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_autoplan()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        return view('report.report-autoplan');
    }
    /**
    * Show the Autoplan report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_autoplan(Request $request)
    { 
        $querydatas = AutoPlan::query()
            ->select('us.name','auto_plan.*','ss.phone_number','sl.stock_id',DB::raw("GROUP_CONCAT(ssl.stock_id) as child"))
            ->join('users as us', 'us.id', '=', 'auto_plan.user_id')
            ->join('tbl_sim_list as sl', 'sl.user_id', '=', 'auto_plan.user_id')
            ->join('tbl_sim_stock as ss', 'ss.id', '=', 'sl.stock_id')
            ->leftjoin("tbl_sim_list as ssl",DB::raw("FIND_IN_SET(ssl.user_id, auto_plan.user_list)"),">",\DB::raw("'0'"))->groupBy('auto_plan.id');

        if ($request->has('number') && $request->get('number') != "") {
            $querydatas->where('ss.phone_number', 'like', "{$request->get('number')}%");
        }  
        if ($request->has('from') && $request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to != ""){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $querydatas->where('auto_plan.next_renewal', '>=', $from)
                          ->where('auto_plan.next_renewal', '<=', $to);
            }else{
                $querydatas->whereRaw("date_format(auto_plan.next_renewal, '%Y-%m-%d')  = '$from'");
            }
        } 
        $result = Datatables::
                eloquent($querydatas)
                ->addColumn('plan', function (AutoPlan $user) {
                    return $user->plan->plan_name;
                })
                ->addColumn('provider', function (AutoPlan $user) {
                    return $user->plan->provider;
                })
                ->editColumn('child', function (AutoPlan $user) {
                    $child = $user->getChild();
                    return implode('|', $child);

                })
                ->editColumn('next_renewal', function ($date) {
                return $date->next_renewal ? with(new Carbon($date->next_renewal))->format('d-m-Y') : '';
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
                return collect($item)->only(['name','phone_number','child','plan','status','amount','next_renewal','created_at']);
             });
            $datas->prepend(array('NAME','NEXT RENEWAL','AMOUNT','STATUS','CREATED','PHONE NUMBER','CHILD','PLAN','PROVIDER'));
            return Excel::download(new CustomExport($datas->toArray()), 'autoplan.csv');
    }else{
        return $result;
    }

    }
    /**
    * Show the Auto recharge report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_autorecharge()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        return view('report.report-autorecharge');
    }
    /**
    * Show the Autorecharge report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_autorecharge(Request $request)
    { 
        $querydatas = AutoRecharge::query()
            ->select('us.name','auto_recharge.*','ss.phone_number')
            ->join('users as us', 'us.id', '=', 'auto_recharge.user_id')
            ->join('tbl_sim_list as sl', 'sl.user_id', '=', 'auto_recharge.user_id')
            ->join('tbl_sim_stock as ss', 'ss.id', '=', 'sl.stock_id');

        if ($request->has('number') && $request->number != "") {
            $querydatas->where('ss.phone_number',$request->number);
        }
        if ($request->has('from') && $request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to != ""){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $querydatas->where('auto_recharge.card_expiry', '>=', $from)
                          ->where('auto_recharge.card_expiry', '<=', $to);
            }else{
                $querydatas->whereRaw("date_format(auto_recharge.card_expiry, '%Y-%m-%d')  = '$from'");
            }
        }
        $result = Datatables::
                eloquent($querydatas)
                ->editColumn('card_expiry', function ($date) {
                return $date->card_expiry ? with(new Carbon($date->card_expiry))->format('d-m-Y') : '';
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
                return collect($item)->only(['name','phone_number','card_expiry','status','amount','created_at']);
             });
            $datas->prepend(array('NAME','AMOUNT','CARD EXPIRY','STATUS','CREATED','PHONE NUMBER'));
            return Excel::download(new CustomExport($datas->toArray()), 'autorecharge.csv');
        }else{
            return $result;
        }
    }
    /**
    * Show the subscription report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_subscription()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        $plans = DB::table('tbl_plans')->select('id','plan_name')->get();
        return view('report.report-subscription',compact('plans'));
    }
    /**
    * Show the subscription report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_subscription(Request $request)
    { 
        $querydatas = DB::table('avoo_sim_log as asl')->select('asl.*','tp.plan_name')
                      ->leftJoin('tbl_plans as tp', 'tp.id', '=', 'asl.plan_id')
                      ->orderBy('asl.created_at','asc')
                      ->orderBy('asl.user_id');

        if (!empty($request->number)) {
            $querydatas->where('asl.msisdn', 'like', "{$request->number}%");
        }
        if (!empty($request->from)) {

            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
                $to   = Carbon::parse($request->to)->format('Y-m-d');
                $querydatas->where('asl.created_at', '>=', $from)
                          ->where('asl.created_at', '<=', $to);
            }else{
                $querydatas->whereRaw("date_format(asl.created_at, '%Y-%m-%d')  = '$from'");
            }
        }

        $querydatas = $querydatas->get();
        $subScip    = collect();

        foreach ($querydatas as $data) {

           $userid      = $data->user_id;

           // if (!isset($subScip[$userid])) {
           //  $subScip[$userid] = [];
           // }
           $subdate           = [];
           $subdate['number'] = $data->msisdn;
           $from  = Carbon::parse($data->created_at);
           $from_format = $from->format('d-m-Y');
           $to    = Carbon::parse($data->created_at)->lastOfMonth();
           $daysIn = Carbon::parse($data->created_at)->daysInMonth;
           $incDays = 1;//($daysIn == 3)?2:1; 
           $to_format = $to->format('d-m-Y');     
           $note = ['SIM New Activation Fee','SIM Monthly Connection Fee','Sim shipment and fulfillment - Unbranded'];
           if($data->category == 'CREATION'){

             for($i = 0; $i < 3; $i++){
                switch ($i) {
                    case 0:
                        $price = 0.5;
                        $total = 0.5;
                        break;
                    case 1:
                        $price = 0.42;
                        $days = ($from->toDateString() == $to->toDateString())?1:$to->diffInDays($from)+$incDays; //to get days including the dates;
                        $total = round(($price/30)*$days,2);
                        break;
                    case 2:
                        $price = 1;
                        $total = 1;
                        break;
                    default:
                        $price = 0;
                        $total = 0;
                        break;
                }
                $subdate['name'] = $note[$i];
                $subdate['date']   = $from_format;
                $subdate['enddate']   = $to_format;
                $subdate['units'] = 1;
                $subdate['price'] = $price;
                $subdate['total'] = $total;
                $subScip->push($subdate); 
             }
           }
           if( $data->category == 'BUNDLE_SUBSCRIPTION' ){
                $subdate['name']  = $data->plan_name;
                $subdate['date']   = $from_format;
                $subdate['enddate']   = $to_format;
                $subdate['units'] = 1;
                $subdate['price'] = $data->value;
                $subdate['total'] = $data->value;
                $subdate['planid'] = $data->plan_id;
                $subScip->push($subdate); 
           }
           if( $data->category == 'RECURRING' ){
                for($i = 0; $i < 2; $i++){
                    if( $i == 0 ){
                        $subdate['name']    =  $data->plan_name;                        
                        $subdate['date']   = $from_format;
                        $subdate['enddate']   = $to_format;
                        $subdate['units'] = 1;                        
                        $subdate['price'] = $data->value;
                        $subdate['total'] = $data->value;
                        $plan_id  = $data->plan_id;
                    }else{
                        $subdate['name']     =  $note[1];
                        $subdate['date']   = $from_format;
                        $subdate['enddate']   = $to_format;
                        $subdate['units'] = 1;  
                        $price = 0.42;
                        $days = ($from->toDateString() == $to->toDateString())?1:$to->diffInDays($from)+$incDays; //to get days including the dates;
                        $total = round(($price/30)*$days,2);

                        $subdate['price'] = $price;
                        $subdate['total'] = $total;
                        $plan_id  = "";
                    }                                    
                    $subdate['planid'] = $plan_id;
                    $subScip->push($subdate);
               }
            }
        }
        if ($request->plan != -1) {
            $subScip = $subScip->where('planid',$request->plan); 
        }
        if ($request->package != -1) {
            $subScip = $subScip->where('name',$request->package); 
        }
        if($request->exportdata){
             $datas = $subScip->map(function ($item) {
                return array_except($item, ['planid']);
            });
            $datas->prepend(array('Usage Identifier','Product','Start Date','End Date','Units','Price','Total Amount'));
            return Excel::download(new CustomExport($datas->toArray()), 'subscription.csv');
        }else{
           return $result = DataTables::of($subScip)
                ->make(true);
        }
    }
    /*
    * List sim pagination
    */
    public function list_sim_pagination()
    {
        $sims = DB::table('tbl_sim_stock as ts')->select('ts.id','phone_number','category','ts.status','box_no','price', DB::raw('CONCAT(a.first_name," ",a.last_name) as fullname'))->join('admins as a','dealer_id','=','a.id');

        return DataTables::queryBuilder($sims)->toJson();
    }

    /**
    * Show the user call history.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_callhistory()
    { 
        $calls = DB::table('user_calls')->join('users','users.id','=','user_id')->get();
        return view('user-calls', compact('calls'));
    }

    /**
    * Show the sim stock.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_countries()
    { 
        if (!Helper::has_permission('countries')) {
            abort(403,'Access denied');
        }
        $countries = DB::table('country')->get();
        return view('country-list', compact('countries'));
    }

    /**
    * Edit country
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_countries($id)
    { 
        if (!Helper::has_permission('countries')) {
            abort(403,'Access denied');
        }

        $country_id = Crypt::decrypt($id);

        $country = DB::table('country')->whereId($country_id)->first();
        $switch_template = DB::table('switch_template')->select('id', 'currency')->get();

        return view('edit-country', compact('country', 'switch_template'));
    }

    /**
    * Update credits.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_country(Request $request, $id)
    { 
        if (!Helper::has_permission('countries')) {
            abort(403,'Access denied');
        }

        $country = Country::where('id',$id)->first();
        if (!$country) {
            abort(403, 'Invalid Country');
        }


        DB::beginTransaction();

        try {

            $country->country_name = $request->country_name;
            $country->country_code = $request->country_code;
            $country->short_code = $request->short_code;
            $country->dial_code = $request->dial_code;
            $country->otp_type = $request->otp_type;
            $country->switch_id = $request->switch;
            $country->access_number = $request->access_number;
            $country->accessnumber_support = $request->access_number_support;
            $country->callback_support = $request->callback_support;
            $country->wifi_support = $request->wifi_support;
            $country->conference_support = $request->conference_support;
            $country->popular = $request->popular;
            $country->land_price = ($request->land_price)?:'-';
            $country->mob_price = ($request->mob_price)?:'-';
            $country->currency = $request->currency;
            $country->currency_symbol = $request->currency_symbol;          
            $country->tax = $request->tax;
            $country->status = $request->status;

            $country->save();

            // $rates = DB::table('tbl_rates')->where(['country_id' => $id, 'rate_type' => 1])->first();

            // print_r($rates);
            // die();

            // DB::table('tbl_rates')->where(['country_id' => $id, 'rate_type' => 1])->update(['app_price' => $request->land_price]);
            // $rates = DB::table('tbl_rates')
            //             ->where(['country_id' => $id, 'rate_type' => 2])->first();
            // DB::table('tbl_rates')->where('id',$rates->id)->update(['app_price' => $request->mob_price]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['Faild to upate country details']);
        }
        return redirect('/countries');
    }

    /**
    * Show the sim stock.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_credits()
    { 
        if (!Helper::has_permission('credits')) {
            abort(403,'Access denied');
        }
        $credits = Credit::get();
        return view('credits', compact('credits'));
    }

    /**
    * Edit credit
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_credits($id)
    { 
        if (!Helper::has_permission('credits')) {
            abort(403,'Access denied');
        }

        $credit_id = Crypt::decrypt($id);

        $credits = DB::table('credits')->whereId($credit_id)->first();
        $switch_template = DB::table('switch_template')->select('id', 'currency')->get();

        return view('edit-credits', compact('credits', 'switch_template'));
    }

    /**
    * Update credits.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_credits(Request $request, $id)
    { 
        if (!Helper::has_permission('credits')) {
            abort(403,'Access denied');
        }

        $credit = Credit::where('id',$id)->first();
        if (!$credit) {
            abort(403, 'Invalid Credit');
        }

        if($request->is_default_amount == 0) {
            $default_credit = Credit::where('default_amount', 1)->first();
            if($default_credit->id == $id) {
                return redirect()->back()->withErrors(['Can\'t update. This is already the default amount']);
            }
        }


        DB::beginTransaction();

        try {

            if($request->is_default_amount == 1) {
                $affected = DB::table('credits')->where('default_amount', '=', 1)->update(array('default_amount' => 0));
            }

            $credit->amount = $request->amount;
            $credit->switch_id = $request->switch;
            $credit->default_amount = $request->is_default_amount;
            $credit->status = $request->credit_status;

            $credit->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            abort(403, 'Failed to update');
        }
        return redirect('/credits');
    }

    /**
    * List the discount coupons
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_discount_coupons()
    { 
        if (!Helper::has_permission('discount_coupons')) {
            abort(403,'Access denied');
        }
        $discount_coupon = DiscountCoupon::get();
        return view('discount-coupon', compact('discount_coupon'));
    }

    /**
    * Interface to add discount coupons
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function add_discount_coupon()
    { 
        if (!Helper::has_permission('discount_coupons')) {
            abort(403,'Access denied');
        }

        return view('create-discount-coupon');
    }

    /**
    * Edit discount coupon
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_discount_coupon($id)
    { 
        if (!Helper::has_permission('discount_coupons')) {
            abort(403,'Access denied');
        }

        $coupon_id = Crypt::decrypt($id);

        $coupons = DB::table('discount_coupons')->whereId($coupon_id)->first();

        return view('edit-discount-coupon', compact('coupons'));
    }

    /**
    * Save discount coupon.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_discount_coupon(Request $request)
    { 
        if (!Helper::has_permission('discount_coupons')) {
            abort(403,'Access denied');
        }

        if(isset($request->coupon_id)) {
            $coupon = DiscountCoupon::where('id',$request->coupon_id)->first();
            if (!$coupon) {
                abort(403, 'Invalid Discount Coupon');
            }
        }
        else {
            $coupon = new DiscountCoupon();
        }       

        $today = new Carbon;
        if($today >= $request->expiry_date) {
            abort(403, 'Expiry Date is already expired');
        }

        DB::beginTransaction();

        try {

            $coupon->coupon_code = $request->coupon_code;
            $coupon->is_fixed = $request->coupon_type;
            $coupon->discount_value = $request->discount_value;
            $coupon->expiry_date = $request->expiry_date;
            $coupon->status = $request->coupon_status;

            $coupon->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            abort(403, 'Failed to Update');
        }
        return redirect('/discount-coupon');
    }

    /**
    * Show the Role Management.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_role()
    {
        if (!Helper::has_permission('roles')) {
            abort(403,'Access denied');
        }
        $roles = DB::table('tbl_roles')->get();
        return view('role-list', compact('roles'));
    }

    /**
    * Show the permission management.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function edit_permission($id)
    { 
        if (!Helper::has_permission('roles','edit')) {
            abort(403,'Access denied');
        }
        $id = Crypt::decrypt($id);

        $permissions = DB::table('tbl_permissions')->select('tbl_permissions.id as permission_id','name','shortname','can_view','can_create','can_edit','can_delete')
        ->leftJoin('tbl_role_permissions', function($join) use ($id){
            $join->on('tbl_permissions.id', '=', 'tbl_role_permissions.permission_id')
            ->where('tbl_role_permissions.role_id', $id);
        })->get();
        
        $role = DB::table('tbl_roles')->where('id', $id)->first();

        return view('permission', compact('permissions','role'));
    }

    /**
    * Update the permissions.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function update_permission(Request $request)
    { 
        if (!Helper::has_permission('roles','edit')) {
            abort(403,'Access denied');
        }
        $role_id = $request->role_id;
        $short_code = $request->short_code;
        DB::table('tbl_roles')->where('id', $role_id)
        ->update(['name' => $request->name, 'short_code' => $short_code]);

        $permission_id = $request->permission_id;
        $permission_view = $request->permission_view;
        $permission_create = $request->permission_create;
        $permission_edit = $request->permission_edit;
        $permission_delete = $request->permission_delete;

        foreach($permission_id as $key => $value){
            $data['can_view'] = isset($permission_view[$value])?:0;
            $data['can_create'] = isset($permission_create[$value])?:0;
            $data['can_edit'] = isset($permission_edit[$value])?:0;
            $data['can_delete'] = isset($permission_delete[$value])?:0;

            RolePermissions::updateOrCreate(
                ['permission_id' => $value, 'role_id' => $role_id], $data
            );
        }
        DB::table('admins')->where('role', $role_id)
        ->update(['force_logout' => 1]);
        return redirect('/roles');
    }

    /*
    * Add new role
    */
    public function add_role(Request $request)
    {
        if (!Helper::has_permission('roles','create')) {
            abort(403,'Access denied');
        }
        $role_name = $request->role_name;
        $short_code = $request->short_code;

        $role_with_name = DB::table('tbl_roles')->where('name', $role_name)->orWhere('short_code', $short_code)->count();
        if ($role_with_name > 0) {
            return[
                'error' => true,
                'message' => 'This role already exist',
            ];
        }
        DB::table('tbl_roles')->insert(
            ['name' => $role_name, 'short_code' => $short_code]
        );
        return[
            'error' => false
        ];
    }

    /*
    * Delete a new role
    */
    public function delete_role(Request $request)
    {
        if (!Helper::has_permission('roles','delete')) {
            abort(403,'Access denied');
        }
        $id = Crypt::decrypt($request->id);
        $options = DB::table('options')->get();
    }

    /**
    * Show the sim stock.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function setttings()
    { 
        if (!Helper::has_permission('settings')) {
            abort(403,'Access denied');
        }
        $options = DB::table('options')->get();
        return view('settings', compact('options'));
    }
    /**
    * show settings.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function addsettings(Request $request)
    { 
        if (!Helper::has_permission('settings')) {
            abort(403,'Access denied');
        }
        $details = [];
        if(!empty($request->id) || isset($request->id)){

            $id         = Crypt::decrypt($request->id);
            $details    = DB::table('options')->where('id',$id)->first();
        }
        return view('addsettings', compact('details'));
    }
    /**
    * save settings.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_settings(Request $request)
    { 

        if (!Helper::has_permission('settings')) {
            abort(403,'Access denied');
        }
        $msg = "Settings Created successfully!";
        if(isset($request->id)) {
            $data = $request->all();
            unset($data['_token']);

            $response = DB::table('options')->whereId($data['id'])->update($data);
            $msg = "Settings Updated successfully!";
            if($response) {
                $id = Crypt::encrypt($data['id']);
                return redirect('/addsettings/'.$id)->with('message', $msg);
            } else {
                return redirect('/addsettings')->with('error', 'Failed to update this, please try again');
            }
        }
        $data = $request->all();
        unset($data['_token']);
        $id = DB::table('options')->insertGetId($data);

        if($id){
            $id = Crypt::encrypt($id);
            return redirect('/addsettings/'.$id)->with('message',$msg);
        }
        else{
            return redirect()->back()->with('error', 'Failed to add, Please try again..');
        }
    }
    /**
    * Function delete settings.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_settings(Request $request)
    { 
        $id     = Crypt::decrypt($request->id);
        $delete = DB::table('options')
            ->where('id',$id)->delete();
        if($delete){
           return response()->json(['status' => "success", 'message' => 'Settings Deleted']); 
        }else{
            return response()->json(['status' => "failure", 'message' => 'Technical error']); 
        }
    }
    /**
    * Show the whitelisted IP.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function firewall()
    { 
        if (!Helper::has_permission('firewall')) {
            abort(403,'Access denied');
        }

        $firewall = DB::table('tbl_whitelist')->get();
        return view('firewall', compact('firewall'));
    }

    /**
    * Function Add/Update whitelisted IP.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function save_firewall(Request $request)
    { 
        if(isset($request->id)) {
            if (!Helper::has_permission('firewall', 'edit')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);

            $response = DB::table('tbl_whitelist')->whereId($request->id)->update($data);  
            if($response) {
                return redirect('/firewall')->with('message', 'IP List updated successfully!');
            } else {
                return redirect('/firewall')->with('error', 'Failed to update this, please try again');
            }
        } else {
            if (!Helper::has_permission('firewall')) {
                abort(403,'Access denied');
            }

            $data = $request->all();
            unset($data['_token']);
            $id = DB::table('tbl_whitelist')->insertGetId($data);
            if($id)
                return redirect('/firewall')->with('message','IP added successfully!');
            else
                return redirect('/firewall')->with('error', 'Failed to add this ip, please try again');
        } 
    }

    /**
    * Function delete whitelisted IP.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_firewall(Request $request)
    { 
        if (!Helper::has_permission('firewall', 'delete')) {
            abort(403,'Access denied');
        }
        DB::table('tbl_whitelist')->where('id',$request->id)->delete();
        return redirect('/firewall')->with('message','IP Removed successfully!');
    }   

    public function throttles()
    { 
        if (!Helper::has_permission('fraudsters')) {
            abort(403,'Access denied');
        }
        return view('throttles');
    } 

    /**
    * Show the throttles.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function throttle_list()
    { 
        if (!Helper::has_permission('fraudsters')) {
            abort(403,'Access denied');
        }

        $throttle = DB::table('throttles');
        return DataTables::queryBuilder($throttle)->toJson();
    }

    /**
    * Function delete throttle.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function delete_throttle(Request $request)
    { 
        if (!Helper::has_permission('fraudsters','delete')) {
            abort(403,'Access denied');
        }
        DB::table('throttles')->whereIn('id',$request->selected)->delete();
        return[
                'error' => false,
                'message' => 'Failed to update status',
            ];
    }    

    public function get_cdr_summary($value='')
    {
        $data = json_decode(file_get_contents(url('/').'/cdr.txt'));
        // print_r($data);
        // foreach ($data->usages as $key => $value) {
        //     //print_r($value); echo "<br>";
        //     DB::table('cdr')->insert(
        //         ['call_from' => $value->from,'call_to' => $value->to, 'date' => $value->date,'duration' => $value->duration,'amount' => $value->amount,'serviceType' => $value->serviceType]
        //     );
        // }

        $datausage =0;
        array_walk( $data->usages, function (&$value, $key) use(&$arrayReindexed ,&$datausage){

                if($value->serviceType == 'DATA'){
                    $datausage += $value->duration;
                    $arrayReindexed['duration'][] = $value->duration;                 
                }
            }
        );

        print_r($datausage);
    }
    /**
    * Show the staff commission report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_staff_comm()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        $promocode = DB::table('admins')->select('promocode')->get();
        return view('report.report-staff-comm',compact('promocode'));
    }
    /**
    * Show the staff commission report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_staff_comm(Request $request)
    {
        $firstday   = Carbon::now()->firstofmonth()->format('Y-m-d');
        $lastday    = Carbon::now()->lastofmonth()->format('Y-m-d');

        $querydatas = DB::table('staffcommission_payments as sp')
                    ->select('sp.comm_staff',DB::raw('sum(sp.pay_amount) as payamount'),DB::raw('sum(sp.paid_amount) as paidamount'), 'a.promocode as promocode',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS staff_name"))
                    ->join('admins as a', 'a.id', '=', 'sp.comm_staff')
                    ->groupBy('sp.comm_staff')->orderBy('sp.comm_staff');
                    

        if ($request->promocode != -1) {
            $querydatas->where("a.promocode",$request->promocode);
        }
        $datas      = $querydatas->get();
        $monthdata  = $querydatas->WhereBetween('sp.payment_date', [$firstday, $lastday])
                        ->get();

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
            $sub['monthnotpaid']    = $monthpay;
            $sub['topay']           = $topay;
            array_push($commData, $sub); 
        }
        $result = Datatables::of($commData)->make(true);
        if($request->exportdata){
             $commData = collect($commData);
             $datas = $commData->map(function ($item) {
                return array_except($item, ['userid']);
            });
            $datas->prepend(array('NAME','PROMOCODE', 'TOTAL COMMISSION','TOTAL PAID COMMISSION','ADVANCE BALANCE',date('F Y').' COMMISSION',date('F Y').' NOT PAID', 'AMOUNT TO PAY'));
            return Excel::download(new CustomExport($datas->toArray()), 'staffcommission.csv');
        }else{
           return $result;
        }
    }
    /**
    * Show the Dealer commission report.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_dealer_comm()
    { 
        if (!Helper::has_permission('reports')) {
            abort(403,'Access denied');
        }
        $promocode = DB::table('admins as a')->select('promocode')
                     ->join('tbl_roles as r', 'r.id', '=', 'a.role')
                     ->where('r.short_code','!=','DEALER')->get();
        return view('report.report-dealer-comm',compact('promocode'));
    }
    /**
    * Show the staff commission report pagination.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function list_report_dealer_comm(Request $request)
    {
        $firstday   = Carbon::now()->firstofmonth()->format('Y-m-d');
        $lastday    = Carbon::now()->lastofmonth()->format('Y-m-d');
        $users      = DB::table('commission_payments as cp')
                        ->select('cp.comm_user',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'), 'a.promocode as promocode',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"))
                        ->join('admins as a', 'a.id', '=', 'cp.comm_user')
                        ->where('cp.user_active',1);

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
        $result = Datatables::of($commData)->make(true);
        if($request->exportdata){
             $commData = collect($commData);
             $datas = $commData->map(function ($item) {
                return array_except($item, ['userid']);
            });
            $datas->prepend(array('NAME','PROMOCODE', 'TOTAL COMMISSION','TOTAL PAID COMMISSION','ADVANCE BALANCE',date('F Y').' COMMISSION',date('F Y').' NOT PAID', 'AMOUNT TO PAY'));
            return Excel::download(new CustomExport($datas->toArray()), 'dealercommission.csv');
        }else{
           return $result;
        }
    }
    /**
    * Show the commission report based on dealer.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_dealer_details(Request $request)
    {
        $userid = $request->user_id;
        $dealer = DB::table('admins')->select('id','first_name','last_name')
                 ->where('role','!=',1)->get();
        return view('report.report-dealer-details', compact('userid','dealer'));
    }
    /**
    * Show the commission report based on dealer full.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function report_dealer_full(Request $request)
    {
        $users   = DB::table('commission_payments as cp')
            ->select('cp.autoplan_id',DB::raw("CONCAT(a.first_name, ' ',a.last_name) AS dealer_name"),'cp.created_at',DB::raw('sum(cp.pay_amount) as payamount'),DB::raw('sum(cp.amount_paid) as paidamount'),'cp.comm_user','cp.payment_date')
            ->join('admins as a', 'a.id', '=', 'cp.comm_user')
            ->where('cp.user_active',1)
            ->groupBy('cp.id')
            ->orderBy('cp.comm_user','asc')
            ->orderBy('payment_date', 'asc');

        if ($request->dealer != -1) {
            $users->where('cp.comm_user', $request->dealer);
        }

        if ($request->from != "") {
            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
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
            $data->total_amount = $totalamount;
            $data->paid_amount  =  $paidamount;
            $data->pay_amount   = $payamount;
            $data->paydate      =  $data->payment_date;
            $data->date         = $data->created_at;
            array_push($userDetails, $data);
        }
        $result = Datatables::of($userDetails)
                ->editColumn('date', function ($date) {
                    return $date->created_at ? with(new Carbon($date->created_at))->format('d-m-Y') : '';
                    })
                ->editColumn('paydate', function ($date) {
                    return $date->paydate ? with(new Carbon($date->paydate))->format('d-m-Y') : '';
                })
                ->make(true);

        if($request->exportdata){
            $result = collect($result->getData()->data);
            $datas = $result->map(function ($item,$key) {
                return collect($item)->only(['dealer_name','planname','pay_amount','paid_amount','total_amount','paydate','date']);
             });
            $datas->prepend(array('NAME','PLAN','COMMISSION','COMMISSION PAID','COMMISSION TO PAY','PAYMENT DATE','DATE'));
            return Excel::download(new CustomExport($datas->toArray()), 'dealercommdetails.csv');
        }else{
            return $result;
        }
    }

}
