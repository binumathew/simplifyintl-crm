<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Helper;
use DataTables;
use App\Models\Cart;
use App\Models\Plan;
use App\Models\Boltons;
use App\Models\TblPlan;
use App\Models\Provider;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /*
    * List all orders which are shipped 
    * Items which are activated and non activated are listed
    */
    public function create_order()
    {
        if (!Helper::has_permission('orders','create')) {
            abort(403,'Access denied');
        }
        $users = Cart::select('user_id')->whereNotNull('user_id')->groupBy('user_id')->take(5)->orderBy('id','desc')->get();
        $data = [];
        foreach($users as $user){
            $data[] = Cart::where('user_id', $user->user_id)->get();
        }
        return view('new-order', compact('data'));
    }

    public function select_plan()
    {
        $providers = Provider::where('status',1)->get();
        $plans = [];
        foreach ($providers as $provider) {
            $plans[$provider->short_code] = TblPlan::where('provider', $provider->short_code)
                                                ->where('status',1)->get();
        }        
        return view('plan-list',compact('providers','plans'));
    }

    public function select_bolt_ons()
    {
        $providers = Provider::where('status',1)->get();
        $plans = [];
        foreach ($providers as $provider) {
            $plans[$provider->short_code] = Boltons::where('provider', $provider->id)
                                                ->where('status',1)->get();
        }
        $plans['APP'] = Plan::whereIn('plan_type',[2,3])->where('switch_id', 1)->get();        
        return view('bolt-ons',compact('providers','plans'));
    }


    /*
    * List all orders which are shipped 
    * Items which are activated and non activated are listed
    */
    public function list_orders()
    {
    	if (!Helper::has_permission('orders')) {
    		abort(403,'Access denied');
    	}

    	return view('orders.list');
    }

    /*
    * Pagination and filter
    */
    public function pagination(Request $request)
    {
    	if ($request->filter_type == 1) { 

            // Filter which are ready to activate

    		$order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"),DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` =rq.id LIMIT 1)) as sim_number"))->join('users as usr','usr.id','=','rq.user_id')->where('delivery_status','1')->orderBy('rq.created_at','DESC');

    	} else if($request->filter_type == 2) { 

            // Filter which are activated but CallBack required

    		$order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"),DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` =rq.id LIMIT 1)) as sim_number"))->join('users as usr','usr.id','=','rq.user_id')->where('delivery_status','2')->orderBy('rq.created_at','DESC');
        } else if($request->filter_type == 3) { 

            // Filter which are not packed. just order received

            $order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"),DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` =rq.id LIMIT 1)) as sim_number"))->join('users as usr','usr.id','=','rq.user_id')->where('delivery_status','0')->orderBy('rq.created_at','DESC');
    	} else if($request->filter_type == 4) {

            // List all packed, active call back required, just order received and actived
    		$order_list = DB::table('tbl_sim_request as rq')->select('rq.id','rq.order_id','rq.promocode','usr.name','delivery_status','rq.created_at as date','usr.phone',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),DB::raw("(SELECT time FROM delivery_history WHERE sim_request_id = rq.id AND type = 1 LIMIT 1) as ship_date"),DB::raw("(SELECT `sim_number` FROM `tbl_sim_stock` WHERE tbl_sim_stock.id =( SELECT `stock_id` FROM `tbl_sim_list` WHERE `request_id` =rq.id LIMIT 1)) as sim_number"))
    		->join('users as usr','usr.id','=','rq.user_id')
    		->orderBy('rq.created_at','DESC');
    	}
        $role       = Auth::user()->role;
        $promocode  = Auth::user()->promocode;
	    $role_details = DB::table('tbl_roles')->where('id', $role)->first();
        if($role_details->short_code == 'DEALER' || $role_details->short_code == 'AAB_STAFF') {
            $promocodes = DB::table('admins')->where('parent_id', Auth::id())->get()->pluck('promocode')->toArray(); 
            array_push($promocodes, $promocode);
            $order_list = $order_list->whereIn('promocode', $promocodes);
        }
    	return DataTables::queryBuilder($order_list)->toJson();
    }  

    public function update_order_promocode(Request $request)
    {
        $promocode = strtoupper($request->promocode);
        DB::table('tbl_sim_request')->where('id', $request->order_id)->update(['promocode' => $promocode]);
    }
}
