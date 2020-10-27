<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Excel;
use Helper;
use DataTables;
use App\Models\SimList;
use App\Models\SimRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
    * Shows list of purchased sim which are ready for delivery
    */
    public function delivery_list()
    {   
        // if (!Helper::has_permission('delivery')) {
        //     abort(403,'Access denied');
        // }
        return view('delivery-management');
    }

    public function update_status(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }
        $postData = $request->all(); 
        $sim_request = DB::table('tbl_sim_request')->where('id', $postData['id'])->first();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }

        DB::beginTransaction();

        try {
            DB::table('tbl_sim_request')->where('id', $postData['id'])->update(['delivery_status' => 1]);

            $note = 'Packed by '.Auth::user()->first_name.' '.Auth::user()->last_name.' and Shipped via '.$postData['agent'].' on ';

            DB::table('delivery_history')->insert(
                ['sim_request_id' => $sim_request->id,'type' => 1,'proceed_by' => Auth::user()->id, 'note' => $note]
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return[
                'error' => true,
                'message' => 'Failed to change status'
            ];
        }

        return[
            'error' => false,
        ];
    }

    public function update_order(Request $request)
    {
        $sim_id = $request->sim_id;
        $phone =  $request->phone;
        $simList = SimList::whereId($sim_id)->first();
        $simReqst = DB::table('tbl_sim_request')->whereId($simList->request_id)
                        ->whereIn('delivery_status', [0,1])->first();

        if($simReqst){
            $stock_id = $simList->stock_id;

            $stock = DB::table('tbl_sim_stock')
                        ->where('status', 1)
                        ->where(function ($query) use ($phone) {
                            $query->where('phone_number', $phone)
                                  ->orWhere('sim_number', $phone);
                                  //->orWhere('sim_number', '894411'.$phone);
                        })
                        ->first();
         
            if($stock && $stock->provider == $simList->auto_plan->getplan->provider){
                DB::beginTransaction();
                try {
                    DB::table('tbl_sim_list')->whereId($sim_id)->update(['stock_id' => $stock->id]);
                    DB::table('tbl_sim_stock')->where('id', $stock->id)->update(['status' => 0]);
                    DB::table('tbl_sim_request')->whereId($simList->request_id)->update(['delivery_status' => 0]);
                    DB::table('tbl_porting')->where('stock_id',$stock_id)->update(['stock_id' => $stock->id]);
                    DB::table('tbl_sim_stock')->where('id', $stock_id)->update(['status' => 1]);
                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    return[
                        'error' => true,
                        'message' => 'Failed to update order'
                    ];
                }
            }else{
                return[
                    'error' => true,
                    'message' => 'Phone number doesn\'t available or provider mismatch error'
                ];
            }
            return[
                'error' => false,
            ];
        } 
        return[
            'error' => true,
            'message' => 'Phone number doesn\'t exist'  
        ];  
    }

    /*
    * List sim request
    * pagination function for data table
    */
    public function pagination(Request $request)
    { 
        if ($request->filter_type == 1) { 
            $delivery_list = DB::table('tbl_sim_request as rq')->select('rq.id','order_id','name','shipping_address','delivery_status','rq.created_at',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),'print_status',DB::raw("(SELECT short_code FROM tbl_roles as rl join admins as ad on ad.role = rl.id WHERE rq.promocode = ad.promocode) as role"))->join('users as usr','usr.id','=','rq.user_id')->where('delivery_status','0')->orderBy('rq.created_at','DESC');
        } else if($request->filter_type == 2) { 
            $delivery_list = DB::table('tbl_sim_request as rq')->select('rq.id','order_id','name','shipping_address','delivery_status','rq.created_at',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),'print_status',DB::raw("(SELECT short_code FROM tbl_roles as rl join admins as ad on ad.role = rl.id WHERE rq.promocode = ad.promocode) as role"))->join('users as usr','usr.id','=','rq.user_id')->where('delivery_status','1')->orderBy('rq.created_at','DESC');
        } else if($request->filter_type == 3) { 
            $delivery_list = DB::table('tbl_sim_request as rq')->select('rq.id','order_id','name','shipping_address','delivery_status','rq.created_at',DB::raw("(SELECT COUNT(*) FROM tbl_sim_list WHERE tbl_sim_list.request_id = rq.id) as sim_count"),'print_status',DB::raw("(SELECT short_code FROM tbl_roles as rl join admins as ad on ad.role = rl.id WHERE rq.promocode = ad.promocode) as role"))->join('users as usr','usr.id','=','rq.user_id')->orderBy('rq.created_at','DESC');
        }
        
        $role       = Auth::user()->role;
        $promocode  = Auth::user()->promocode;
        $role_details = DB::table('tbl_roles')->where('id', $role)->first();
        if($role_details->short_code == 'DEALER' || $role_details->short_code == 'AAB_STAFF') {
            $promocodes = DB::table('admins')->where('parent_id', Auth::id())->get()->pluck('promocode')->toArray(); 
            array_push($promocodes, $promocode);
            $delivery_list = $delivery_list->whereIn('promocode', $promocodes);
        }
        
        return DataTables::queryBuilder($delivery_list)->toJson();
    }

    /*
    * Show Status of every sim request
    */
    public function show_delivery_status(Request $request)
    {
        if (!Helper::has_permission('delivery','view')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }
        $count = 0;
        $postData = $request->all();
        $sim_request = DB::table('tbl_sim_request')->where('id', $postData['id'])->first();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }

        $html = '<ul class="progress-indicator">';

        $html .= '<li class="completed"><span class="bubble"></span>Request Recieved<br>'.date('d-M-Y H:i',strtotime($sim_request->created_at)).'</li>';

        $delivery_history = DB::table('delivery_history')->where('sim_request_id', $sim_request->id)->orderBy('time','ASC')->get();
        
        if ($delivery_history) {
            foreach ($delivery_history as $history) {
                $html .= '<li class="completed"><span class="bubble"></span>'.$history->note.'<br>'.date('d-M-Y H:i',strtotime($history->time)).'</li>';
                $count++;
            }
        }

        for ($i=$count; $i < 2; $i++) { 
            $html .= '<li><span class="bubble"></span></li>';
        }

        $html .= '</ul>';
        return[
            'error' => false,
            'html' => $html,
        ];
    }

    /*
    * View call history
    */
    public function enquiry_history(Request $request)
    {
        if (!Helper::has_permission('delivery')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }
        $postData = $request->all();
        $sim_request = DB::table('tbl_sim_request')->where('id', $postData['id'])->first();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }
        $enquiry_history = DB::table('enquiry_history')->where('sim_req_id', $sim_request->id)->orderBy('created_at','ASC')->get();
        $view = view("enquiryHistory",compact('enquiry_history'))->with('req_id',$sim_request->id)->render();
        return [
            'error' =>false,
            'html' => $view
        ];
    }

    /*
    * Save Call Enquiry
    */
    public function save_enquiry_detail(Request $request)
    {
        // if (!Helper::has_permission('delivery','create')) {
        //     return[
        //         'error' => true,
        //         'message' => 'Access denied'
        //     ];
        // }
        $postData = $request->all();
        $id = Crypt::decrypt($postData['id']);
        //print_r($postData); exit();
        $sim_request = DB::table('tbl_sim_request')->where('id', $id)->first();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }

        $handledBy = Auth::user()->first_name.' '.Auth::user()->last_name;

        $response = DB::table('enquiry_history')->insert(
            ['sim_req_id' => $sim_request->id,'handled_user_id' => Auth::user()->id,'handled_by' => $handledBy,'note' => $postData['note']]
        );
        if ($response) {
            return[
                'error' => false,
            ];
        } else {
            return[
                'error' => true,
                'message' => 'Failed to save enquiry history'
            ];
        }
    }

    /*
    * Load all address to print
    */
    public function print_address(Request $request)
    {
        $request_id = $request->selected;

        if ($request_id == "") {
            return[
                'error' => true,
                'message' => 'Please select atleast one address to print',
            ];
        }

        DB::table('tbl_sim_request')->whereIn('id', $request_id)->update(['print_status' => 1]);

        $sim_request = SimRequest::whereIn('id', $request_id)->get();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }
        $view = view("printView",compact('sim_request'))->render();

        return[
            'error' => false,
            'html' => $view
        ];
    }

    /*
    * Bulk ship 
    */
    public function bulk_ship(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }

        $flag = false;

        $request_id = $request->selected;
        $agent = $request->agent;

        if ($request_id == "") {
            return[
                'error' => true,
                'message' => 'Please select atleast one address to print',
            ];
        }

        $sim_request = SimRequest::whereIn('id',$request_id)->get();

        if ($sim_request) {

            $note = 'Packed by '.Auth::user()->first_name.' '.Auth::user()->last_name.' and Shipped via '.$agent.' on ';
            $proceed_by = Auth::user()->id;

            foreach ($sim_request as $request) {
                if ($request->delivery_status == 0) {

                    DB::beginTransaction();
                    try {

                        DB::table('tbl_sim_request')->where('id', $request->id)->update(['delivery_status' => 1]);

                        DB::table('delivery_history')->insert(
                            ['sim_request_id' => $request->id,'type' => 1,'proceed_by' => $proceed_by, 'note' => $note]
                        );

                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollback();
                        $flag = true;
                    }
                }
            }
        }

        if ($flag) {
            return[
                'error' => true,
                'message' => 'Failed to update status',
            ];
        }

        return[
            'error' => false,
        ];
    }

    /*
    * List details of requested sim
    */
    public function sim_details(Request $request)
    {
        if (!Helper::has_permission('delivery')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }
        $sim_request = DB::table('tbl_sim_request')->where('id', $request->id)->first();
        if (!$sim_request) {
            return[
                'error' => true,
                'message' => 'Sim request doesnot exist',
            ];
        }
        //$sim_list = DB::table('tbl_sim_list')->select('tbl_sim_list.id','phone_number','category','sim_number','box_no')->join('tbl_sim_stock','tbl_sim_stock.id','=','tbl_sim_list.stock_id')->where('request_id', $request->id)->get();

        $sim_list = SimList::where('request_id', $request->id)->groupBy('autoplan_id')->get();

        if (!$sim_list) {
            return[
                'error' => true,
                'message' => 'No SIM in list',
            ];
        }
        return[
            'error' => false,
            'html' => view("simDetailList",['sim_list' => $sim_list,'address' => $sim_request->shipping_address])->render(),
        ];

    }

    /*
    * Update shipping address
    */
    public function update_shipping_address(Request $request)
    {
        if (!Helper::has_permission('delivery','edit')) {
            return[
                'error' => true,
                'message' => 'Access denied'
            ];
        }
        $sim_list = SimList::where('id', $request->id)->first();
        if (!$sim_list) {
            return[
                'error' => true,
                'message' => 'Invalid Request',
            ];
        }

        $sim_request = SimRequest::where('id', $sim_list->request_id)->first();

        if ($sim_request) {

            $split_address = explode(' , ', $request->address);
            if (count($split_address) != 4) {
                return [
                    'error' => true,
                    'message' => 'Incorrect address format'
                ];
            }

            $shippingAddress['postal_code'] = $split_address[3];
            $shippingAddress['street'] = $split_address[0];
            $shippingAddress['city'] = $split_address[1];
            $shippingAddress['country'] = $split_address[2];

            $sim_count = DB::table('tbl_sim_list')->where('request_id', $sim_list->request_id)->count();

            if ($sim_count > 1) {
                $copy = $sim_request->replicate();

                $copy->shipping_address = json_encode($shippingAddress);
                $copy->created_at = $sim_request->created_at;
                $copy->print_status = 0;
                if ($copy->save()) {
                    $sim_list->request_id = $copy->id;

                    if ($sim_list->save()) {
                        return[
                            'error' => false,
                        ];
                    } else {
                        $copy->delete();
                        return [
                            'error' => true,
                            'message' => 'Failes to update shipping address'
                        ];
                    }
                } else {
                    return [
                        'error' => true,
                        'message' => 'Failes to update shipping address'
                    ];
                }
            } else {
                $sim_request->shipping_address = json_encode($shippingAddress);
                $sim_request->print_status = 0;
                if ($sim_request->save()) {
                    return[
                        'error' => false,
                    ];
                } else {
                    return [
                        'error' => true,
                        'message' => 'Failes to update shipping address'
                    ];
                }
            }

        } else {
            return [
                'error' => true,
                'message' => 'Sim request not available'
            ];
        }
    }

    /*
    * Return address
    */
    public function get_address(Request $request)
    {
        $decoded = Helper::get_postal_address($request->postal_code,$request->house_no);

        if (isset($decoded->Message)) {
            return [
                'error' => true,
                'type' => 2,
                'message' => 'Invalid Postcode',
                'address' => '',
            ];
        }
        
        $option = '<option value="">---Select---</option>';
        $address2 = $decoded->addresses[0]->town_or_city.' , '.$decoded->addresses[0]->country.' , '.$request->postal_code; 
        if (count($decoded->addresses) > 1) {
            foreach ($decoded->addresses as $key => $address) {
                $lineAddress = $address->line_1;
                if (empty($address->line_2)) {
                    $AdrsLine = $address->line_1;
                } else {
                    $AdrsLine = $address->line_1.' - '.$address->line_2;
                }
                $option .= '<option value="'.$AdrsLine.' , '.$address2.'">'.$AdrsLine.'</option>';
            }
            return [
                'error' => true,
                'type' => 1,
                'message' => 'Please confirm your Address Line 1.',
                'list' => $option,
            ];
        } else {
            if (empty($decoded->addresses[0]->line_1)) {
                return [
                    'error' => true,
                    'type' => 2,
                    'message' => 'We cannot find your Address Line 1. Please enter your Address Line 1.',
                ];
            } else {
                $line1 = $decoded->addresses[0]->line_1." ".$decoded->addresses[0]->line_2;
                return [
                    'error' => false,
                    'address' => $line1.' , '.$address2,
                ];
            }
        }
    }

    
}
