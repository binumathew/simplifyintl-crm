<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Excel;
use Helper;
use DataTables;
use Carbon;
use Utils;
use Log;
use Stripepayments;

use App\Models\SimList;
use App\Models\SimRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Jobs\Delivery\SoftDelivery;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * Shows list of purchased sim which are ready for delivery
    */
    public function order_delivery()
    {   
        if (!Helper::has_permission('delivery') && !Helper::has_permission('delivery','view_own')) {            
            abort(403,'Access denied');
        }

        return view('orders.delivery-list');
    }

    /*
    * List sim request
    * pagination function for data table
    */
    public function delivery_list(Request $request)
    { 
        $admin_id = Auth::id();
        $promocode =  Auth::user()->promocode;     
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();        
        array_push($where, $promocode);

        $delivery_list = DB::table('tbl_sim_request as rq')->select('rq.id', 'order_id', 'name', 'shipping_address', 'delivery_status', 'rq.created_at', DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"), 'print_status', DB::raw("(SELECT short_code FROM tbl_roles as rl join admins as ad on ad.role = rl.id WHERE rq.promocode = ad.promocode) as short_code"),DB::raw("(SELECT GROUP_CONCAT(qrcode_scanned SEPARATOR ',') as qrcode_scanned FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as qrcode_scanned"),'up.total_amount')->join('users as usr', 'usr.id', '=', 'rq.user_id')->leftJoin('user_payments as up', 'up.id', '=', 'rq.payment_id');

        if ($request->filter_type == 1) { 
            $delivery_list = $delivery_list->where('delivery_status','0');
        } else if($request->filter_type == 2) { 
            $delivery_list = $delivery_list->where('delivery_status','1');
        } else if($request->filter_type == 4) { 
            $delivery_list = $delivery_list->where('delivery_status','4');
        }else if($request->filter_type == 5) { 
            $delivery_list = $delivery_list->where('delivery_status','5');
        }
        
        if(Helper::has_permission('delivery')){
        }elseif(Helper::has_permission('delivery','view_own')){
            $delivery_list = $delivery_list->whereIn('rq.promocode', $where);
        }
        
        return DataTables::queryBuilder($delivery_list)->toJson();
    }

    /*
    * Show Status of every sim request
    */
    public function order_status(Request $request)
    {
        if (!Helper::has_permission('delivery') && !Helper::has_permission('delivery','view_own')) {            
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }

        $admin_id = Auth::id();
        $promocode =  Auth::user()->promocode;     
        $where = DB::table('admins')->where('parent_id', $admin_id)->pluck('promocode')->toArray();        
        array_push($where, $admin_id);

        $sim_request = DB::table('tbl_sim_request')->where('id', $request->id);
        if(Helper::has_permission('delivery')){
            $sim_request = $sim_request->first();
        }elseif(Helper::has_permission('delivery','view_own')){
            $sim_request = $sim_request->whereIn('promocode', $where)->first();
        }

        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Sim request doesnot exist']);
        }

        $order_status = DB::table('delivery_history')->where('sim_request_id', $sim_request->id)->orderBy('time','ASC')->get();
        $view = view("modal-popup",compact('order_status','sim_request'))->with('req_id',$sim_request->id)->render();
        return response()->json(['error' => false, 'html' => $view]);
    }
    
    /*
    * Bulk shipment process
    */
    public function order_shipment(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }
        $admin = Auth::user();
        $request_id = $request->selected;
        $sim_request = SimRequest::whereIn('id',$request_id)->get();
        if ($sim_request->isNotEmpty()) {
            $note = 'Packed by '.$admin->first_name.' '.$admin->last_name.' and Shipped via '. $request->agent .' on ';
            $agent = $request->agent;
            foreach ($sim_request as $request) {
                if ($request->delivery_status == 0) {
                    $simlist = SimList::where('request_id',$request->id)->get();
                    if($simlist->isNotEmpty()){
                        foreach($simlist as $key => $list){
                            if($list->stock->provider == 'E_SIM' && $agent == 'Soft Delivery' && $list->stock->is_esim){
                                try {
                                    DB::table('tbl_sim_list')->where('id', $list->id)->limit(1)->update(['web_request' => 1]);
                                    SoftDelivery::dispatch($list->id);
                                } catch (\Exception $e) {
                                    Log::error('SoftDelivery',[
                                        'error' =>   $e->getMessage()
                                    ]);
                                }   
                            }
                        }
                    }
                    DB::beginTransaction();
                    try {
                        DB::table('tbl_sim_request')->where('id', $request->id)->update(['delivery_status' => 1]);
                        DB::table('delivery_history')->insert(['sim_request_id'=>$request->id, 'type'=>1, 'proceed_by'=>$admin->id, 'note'=>$note]);
                        DB::commit();                        
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(['error' => true, 'message' => 'Failed to update status']);
                    }
                }
            }
            return response()->json(['error' => false, 'message' => 'Updated Successfully']);
        } else {
            return response()->json(['error' => true, 'message' => 'Order details doesnot exist!.']);
        }
    }

    /*
    * View Enquiry history
    */
    public function enquiry_history(Request $request)
    {
        if(!Helper::has_permission('users')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }
        $sim_request = DB::table('tbl_sim_request')->where('id', $request->id)->first();
        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Sim request doesnot exist']);
        }
        $enquiry_history = DB::table('enquiry_history')->where('sim_req_id', $request->id)->orderBy('created_at','ASC')->get();
        $view = view("modal-popup",compact('enquiry_history'))->with('req_id',$sim_request->id)->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /*
    * Save Call Enquiry
    */
    public function save_enquiry(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        $sim_request = DB::table('tbl_sim_request')->where('id', $id)->first();
        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Invalid Order Details']);
        }

        $handledBy = Auth::user()->first_name.' '.Auth::user()->last_name;
        $response = DB::table('enquiry_history')->insert(['user_id' => $sim_request->user_id, 'sim_req_id' => $sim_request->id,'handled_user_id' => Auth::user()->id,'handled_by' => $handledBy,'note' => $request->note]);
        if ($response) {
            return response()->json(['error' =>  false]);
        } else {
            return response()->json(['error' => true, 'message' => 'Failed to save enquiry history']);
        }
    }

    /*
    * List details of requested sim
    */
    public function sim_details(Request $request)
    {
        if (!Helper::has_permission('delivery') && !Helper::has_permission('delivery','view_own')) {            
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }

        $sim_request = DB::table('tbl_sim_request')->where('id', $request->id)->first();
        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Order details doesnot exist!.']);
        }

        $sim_list = SimList::where('request_id', $request->id)->groupBy('autoplan_id')->get();
        if (!$sim_list) {
            return response()->json(['error' => true, 'message' => 'Order doesnot exist!.']);
        }
        $address = $sim_request->shipping_address;
        $view = view("modal-popup",compact('sim_list','address'))->render();
        return response()->json(['error' => false, 'html' => $view]);

        return[
            'error' => false,
            'html' => view("simDetailList",['sim_list' => $sim_list,'address' => $sim_request->shipping_address])->render(),
        ];

    }

    /*
    * Update/Change phone number
    */
    public function update_order(Request $request)
    {
        $phone =  $request->phone;
        $simList = SimList::whereId($request->sim_id)->first();
        $simReqst = DB::table('tbl_sim_request')->whereId($simList->request_id)
                        ->whereIn('delivery_status', [0,1])->first();
        if($simReqst){
            $stock_id = $simList->stock_id;
            $stock = DB::table('tbl_sim_stock')->where('status', 1)
                        ->where(function ($query) use ($phone) {
                            $query->where('phone_number', $phone)
                                  ->orWhere('sim_number', $phone);
                                  //->orWhere('sim_number', '894411'.$phone);
                        })->first();
         
            // if($stock && $stock->provider == $simList->auto_plan->getplan->provider){
            if($stock && (($stock->provider == $simList->auto_plan->getplan->provider) || ($stock->provider == 'O2' && $simList->auto_plan->getplan->provider == 'EE_O2'))){
                DB::beginTransaction();
                try {
                    DB::table('tbl_sim_list')->whereId($simList->id)->update(['stock_id' => $stock->id]);
                    DB::table('tbl_sim_stock')->where('id', $stock->id)->update(['status' => 0]);
                    DB::table('tbl_sim_request')->whereId($simList->request_id)->update(['delivery_status' => 0]);
                    DB::table('tbl_porting')->where('stock_id',$stock_id)->update(['stock_id' => $stock->id]);
                    DB::table('tbl_sim_stock')->where('id', $stock_id)->update(['status' => 1]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['error' => true, 'message' => 'Failed to update order']);
                }
            }else{
                return response()->json(['error' => true, 'message' => 'Phone Number doesn\'t available or Provider mismatch']);
            }
            return response()->json(['error' => false]);
        } 
        return response()->json(['error' => true, 'message' => 'Phone number doesn\'t exist']); 
    }

    /*
    * Process Re-Order 
    */
    public function process_reorder(Request $request)
    {
        $delivery = (Auth::user()->role == 1)?[0,1,2,3,4]:[0,1];
        $simReqst = DB::table('tbl_sim_request')->whereId($request->order_id)
                        ->whereIn('delivery_status', $delivery)->first();
        if($simReqst){
            DB::beginTransaction();
            try {
                DB::table('tbl_sim_request')->whereId($simReqst->id)->update(['delivery_status' => 0]);
                $note = 'Re Ordered by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on ';
                DB::table('delivery_history')->insert(['sim_request_id' => $simReqst->id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => true, 'message' => 'Couldn\'t process the reorder, please contact admin']);
            }        
            return response()->json(['error' => false]);
        } 
        return response()->json(['error' => true, 'message' => 'Couldn\'t process the reorder, please contact admin']); 
    }

    /*
    * Postcode finder return address
    */
    public function find_address(Request $request)
    {
        $decoded = Helper::get_postal_address($request->postal_code,$request->house_no);
        if (isset($decoded->Message)) {
           return response()->json(['error' => true, 'type' => 2, 'message' => 'Invalid postcode address', 'address' => '']);
        }   

        /*
        if($request->house_no != ''){
            $decoded =  json_decode('{"postcode":"EN6 2BW","latitude":51.6919290000000017926140571944415569305419921875,"longitude":-0.200651999999999997026378650843980722129344940185546875,"addresses":[{"formatted_address":["26 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"26","line_1":"26 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"}]}');
        }else{

            $decoded =  json_decode('{"postcode":"EN6 2BW","latitude":51.6919290000000017926140571944415569305419921875,"longitude":-0.200651999999999997026378650843980722129344940185546875,"addresses":[{"formatted_address":["2 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"2","line_1":"2 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["4 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"4","line_1":"4 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["6 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"6","line_1":"6 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["8 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"8","line_1":"8 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["10 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"10","line_1":"10 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["12 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"12","line_1":"12 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["14 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"14","line_1":"14 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["16 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"16","line_1":"16 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["18 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"18","line_1":"18 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["20 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"20","line_1":"20 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["22 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"22","line_1":"22 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["24 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"24","line_1":"24 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["26 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"26","line_1":"26 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["28 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"28","line_1":"28 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["30 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"30","line_1":"30 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["32 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"32","line_1":"32 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["34 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"34","line_1":"34 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["36 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"36","line_1":"36 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["38 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"38","line_1":"38 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["40 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"40","line_1":"40 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["42 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"42","line_1":"42 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["44 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"44","line_1":"44 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["46 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"46","line_1":"46 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["48 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"48","line_1":"48 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["50 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"50","line_1":"50 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["52 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"52","line_1":"52 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["54 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"54","line_1":"54 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["56 The Shrublands","","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"","sub_building_number":"","building_number":"56","line_1":"56 The Shrublands","line_2":"","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["Commstech Ltd","28 The Shrublands","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"Commstech Ltd","sub_building_number":"","building_number":"28","line_1":"Commstech Ltd","line_2":"28 The Shrublands","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"},{"formatted_address":["Tanalytics Ltd","8 The Shrublands","","Potters Bar","Hertfordshire"],"thoroughfare":"The Shrublands","building_name":"","sub_building_name":"Tanalytics Ltd","sub_building_number":"","building_number":"8","line_1":"Tanalytics Ltd","line_2":"8 The Shrublands","line_3":"","line_4":"","locality":"","town_or_city":"Potters Bar","county":"Hertfordshire","district":"Hertsmere","country":"England"}]}');
        }
        */
        $option = '<option value="">---Select---</option>';
        $address = $decoded->addresses[0]->town_or_city.' , '.$decoded->addresses[0]->country.' , '.$decoded->postcode; 
        if (count($decoded->addresses) > 1) {
            foreach ($decoded->addresses as $key => $addresses) {
                $lineAddress = $addresses->line_1;
                if (empty($addresses->line_2)) {
                    $address_line = $addresses->line_1;
                } else {
                    $address_line = $addresses->line_1.' '.$addresses->line_2;
                }
                $option .= '<option value="'.$address_line.' , '.$address.'">'.$address_line.' , '.$address.'</option>';
            }
            return response()->json(['error' => true, 'type' =>  1, 'message' => 'Please confirm your address line', 'list' => $option]);
        } else {
            if (empty($decoded->addresses[0]->line_1)) {
                return response()->json(['error' => true, 'type' =>  2,'message' => 'Please enter your address line.']);
            } else {
                $address_line = $decoded->addresses[0]->line_1." ".$decoded->addresses[0]->line_2;
                return response()->json(['error' => false, 'address' => $address_line.' , '.$address]);
            }
        }
    }

    /*
    * Update shipping address
    */
    public function update_shipping_address(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }
        
        $sim_list = SimList::where('id', $request->id)->first();
        if (!$sim_list) {
            return response()->json(['error' => true, 'message' => 'Order details doesn\'t exist']);            
        }
        $sim_request = SimRequest::where('id', $sim_list->request_id)->first();
        if ($sim_request) {
            parse_str($request->address, $address);
            $shippingAddress['first_name'] = $address['first_name'];
            $shippingAddress['last_name'] = $address['last_name'];
            $shippingAddress['postal_code'] = $address['postal_code'];
            $shippingAddress['street'] = $address['shipping_street'];
            $shippingAddress['city'] = $address['shipping_city'];
            $shippingAddress['country'] = $address['shipping_country'];
            $note = 'Shipping address updated by '.Auth::user()->first_name.' '.Auth::user()->last_name.' on ';
            $sim_count = DB::table('tbl_sim_list')->where('request_id', $sim_list->request_id)->count();
            if ($sim_count > 1) {
                $copy = $sim_request->replicate();
                $copy->shipping_address = json_encode($shippingAddress);
                $copy->created_at = $sim_request->created_at;
                $copy->print_status = 0;
                if ($copy->save()) {
                    $sim_list->request_id = $copy->id;
                    if ($sim_list->save()) {
                        DB::table('delivery_history')->insert(['sim_request_id' => $copy->id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
                        return response()->json(['error' => false]);
                    } else {
                        $copy->delete();
                        return response()->json(['error' => true, 'message' => 'Failes to update shipping address']);                               
                    }
                } else {
                    return response()->json(['error' => true, 'message' => 'Failes to update shipping address']);
                }
            } else {
                $sim_request->shipping_address = json_encode($shippingAddress);
                $sim_request->print_status = 0;
                if ($sim_request->save()) {                    
                    DB::table('delivery_history')->insert(['sim_request_id' => $sim_request->id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
                    return response()->json(['error' => false]);
                } else {
                    return response()->json(['error' => true, 'message' => 'Failes to update shipping address']);
                }
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Order details doesn\'t exist']);
        }
    }

    /*
    * Print welocome letter
    */
    public function print_welcome_letter(Request $request)
    {
        $request_id = $request->selected;
        if (empty($request_id)) {
            return response()->json(['error' => true, 'message' => 'Please select any order']);
        }

        DB::table('tbl_sim_request')->whereIn('id', $request_id)->update(['print_status' => 1]);
        $sim_request = SimRequest::whereIn('id', $request_id)->get();
        if ($sim_request->isEmpty()) {
            return response()->json(['error' => true, 'message' => 'Order details doesn\'t exist']);
        }
        foreach($sim_request as $key => $sim_req){
            $sim_list   = SimList::where('request_id',$sim_req->id)->get();
            $qrcode_val = [];
            foreach($sim_list as $skey => $list){
                if($list->stock->is_esim){
                    $qrdata = $list->stock->stockcode->qr_code;
                    $qrcode = Utils::qrcode($qrdata);
                    $qrcode_val[] = $qrcode;
                }
            }
            $sim_req->qrcode  = $qrcode_val;
            $sim_req->simlist = $sim_list;
        }
        $view = view('welcome-letter', compact('sim_request'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /*
    * Order Cancellation process
    */
    public function order_cancellation(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }

        $sim_request = DB::table('tbl_sim_request')->where('id', $request->id)->first();
        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Order details doesn\'t exist']); 
        }

        DB::beginTransaction();
        try {
            DB::table('tbl_sim_request')->where('id', $request->id)->update(['delivery_status' => 4]);
            $note = 'Canceled by '.Auth::user()->first_name.' '.Auth::user()->last_name.', for '.$request->reason.' on ';
            DB::table('delivery_history')->insert(['sim_request_id' => $request->id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => true, 'message' => 'Failed to change status']);
        }
        return response()->json(['error' => false]);
    }
        /*
    * Order Cancellation process and refund
    */
    public function order_cancel_refund(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return response()->json(['error' => true, 'message' => 'Access denied']);
        }

        $sim_request = SimRequest::where('id', $request->id)->first();
        if (!$sim_request) {
            return response()->json(['error' => true, 'message' => 'Order details doesn\'t exist']); 
        }
        if($request->refundamount > $sim_request->payment->total_amount){
            return response()->json(['error' => true, 'message' => 'Refund amount is greater than order amount']); 
        }
        $data = [
            'transaction_id'=>$sim_request->payment->transaction_id,
            'amount'=> $request->refundamount,
            'reason'=>$request->reason
        ];
        $metadata = [
            'order_id'=>$sim_request->order_id,
            'user_id'=>$sim_request->user_id
        ];
        if($sim_request->payment->payment_method == 'Stripe'){
            $refund  = Stripepayments::stripeCardRefund($data,$metadata);
        }else{
            return response()->json(['error' => true, 'message' => 'Payment gateway not found']);  
        }
        
        $payment = ['user_id'=>$sim_request->user_id,'payment_method'=>$sim_request->payment->payment_method,'payment_for'=>'Order Cancel and Refund','currency'=>$sim_request->payment->currency,'card_type'=>$sim_request->payment->card_type,'category'=>$sim_request->payment->category,'amount'=>$request->refundamount,'total_amount'=>$request->refundamount];

        if($refund->status){
            $payment['transaction_id']  = $refund->transaction_id;
            $payment['description']     = 'Order Cancel and Refund';
            $payment['status']          = 1;
            DB::beginTransaction();
            try {
                DB::table('tbl_sim_request')->where('id', $request->id)->update(['delivery_status' => 5]);
                $note = 'Canceled and Refund '.$request->refundamount.' by '.Auth::user()->first_name.' '.Auth::user()->last_name.', for '.$request->reason.' on ';
                DB::table('delivery_history')->insert(['sim_request_id' => $request->id, 'type' => 1, 'proceed_by' => Auth::user()->id, 'note' => $note]);
                DB::table('user_payments')->insert($payment);
                DB::commit();
                return response()->json(['error' => false, 'message' => 'Refund initiated, order cancelled']);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => true, 'message' => 'Refund initiated, Failed to cancel the order']);
            }
        }else{
            $payment['transaction_id']  = '';
            $payment['description']     = 'Order Cancel and Refund -'.$refund->error;
            $payment['status'] = 0;
            DB::table('user_payments')->insert($payment);
            return response()->json(['error' => true, 'message' => 'Refund failed, Failed to cancel the order']);
        }
    }
}
