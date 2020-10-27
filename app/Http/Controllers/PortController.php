<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Excel;
use Helper;
use DataTables;
use App\Models\SimList;
use App\Models\TblPorting;
use App\Models\SimRequest;
use Illuminate\Http\Request;
use DwpHelper;
class PortController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * Shows list of port request
    */
    public function port_list(Request $request)
    {   
        if (!Helper::has_permission('porting')) {            
            abort(403,'Access denied');
        }
        // $api_user = Helper::get_option('dwp_auth_username');
        // $api_pwd  = Helper::get_option('dwp_auth_password');

        // $sim_list = SimList::where('id', 1207)->first();

        // $type = ($sim_list->port)?'port':'new';
        // $product_id = $sim_list->auto_plan->plan->sim_billing_plan;
        // $data['type'] = $type;
        // $data['product_id'] = $product_id;
        // $data['order_id'] = 'AVO14596735';
        // $data['user_name'] = $sim_list->sim_request->order_id;
        // if($sim_list->port){
        //     $data['port_cli'] = $sim_list->porting_to;
        //     $data['pac_code'] = $sim_list->pac_no; 
        //     $data['port_date'] = '2020-08-14';
        // }else{
        //     $data['activation'] = '2020-08-14';
        // }
        // $data['sim_number'] = $sim_list->stock->sim_number;
      //   $data['network'] = $sim_list->stock->network->provider;

      //  // $xml_data = '<?xml version="1.0"? >
      //   <Request module="dwapi" call="mobile_order_add_product" id="'.Helper::unique_code(32).'" version="1.0">
      //     <block name="auth">
      //       <a name="username" format="text">'. $api_user .'</a>
      //       <a name="password" format="password">'. $api_pwd .'</a>
      //       <a name="client-id" format="text">1</a>
      //     </block>
      //     <block name="mobile">
      //       <a name="acquisition-method" format="text">'.$data['type'].'</a>
      //     <a name="bill-limit" format="counting">10.00</a>
      //       <a name="is-sim-required" format="boolean">1</a>            
      //       <a name="order-id" format="counting">'.$data['order_id'].'</a>
      //     <a name="sim-buffer-serial" format="text">'.$data['sim_number'].'</a>
      //     <a name="sim-is-buffer" format="text">1</a>
      //     <a name="sim-type" format="text">triple</a>
      //     <a name="use-billing-address" format="boolean">1</a>
      //     <a name="user-name" format="text">'.$data['user_name'].'</a>
      //     ';
      // if($data['type'] == 'port'){
      //   $xml_data .= '<a name="mobile-number" format="phone">'.$data['port_cli'].'</a>
      //       <a name="pac" format="text">'.$data['pac_code'].'</a>
      //     <a name="port-date" format="text">'.$data['port_date'].'</a>
      //     ';          
      //     // <a name="port-date" format="text">dog</a>
      // }else{
      //   $xml_data .= '<a name="activation-date" format="date">'.$data['activation'].'</a>
      //   ';
      // }
      // if($data['network'] == 'O2'){
      //   $xml_data .= '<a name="M" format="boolean">1</a>
      //     <a name="L" format="boolean">1</a>
      //     <a name="I" format="boolean">1</a>
      //     <a name="sb=0001" format="boolean">1</a>
      //     <a name="sb=0003" format="boolean">1</a>
      //     <a name="w" format="boolean">1</a>
      //     <a name="ab" format="boolean">1</a>
      //     ';
      // }elseif($data['network'] == 'Vodafone'){
      //   $xml_data .= '<a name="INTPREMIUM" format="boolean">1</a>
      //     <a name="PREMIUM" format="boolean">1</a>
      //     <a name="ROAMING" format="boolean">1</a>
      //     <a name="INTERNATIONAL" format="boolean">1</a>
      //     <a name="INWHENROAM" format="boolean">1</a>
      //     <a name="PRMSG" format="boolean">1</a>
      //     <a name="GPRSROAM" format="boolean">1</a>
      //     ';
      // }
      //   $xml_data .= '<a name="product-id" format="counting">'.$data['product_id'].'</a>
      //       <a name="wwcap-enabled" format="boolean">1</a>
      //     </block>
      //     <a name="order-id" format="counting">'.$data['order_id'].'</a>          
      //     <a name="product-type" format="text">voice</a>
      //   </Request>';

      //   echo $xml_data;
      //   die();
        //// $xml = '<?xml version="1.0"? >
        // <Request module="dwapi" call="mobile_product_list" id="'.Helper::unique_code(32).'" version="1.0">
        //   <block name="auth">
        //     <a name="username" format="text">'. $api_user .'</a>
        //     <a name="password" format="password">'. $api_pwd .'</a>
        //     <a name="client-id" format="text">1</a>
        //   </block>
        //   <a name="sub-account" format="text">GZR38415</a>
        // </Request>';

        // $response  = DwpHelper::dwp_process_api($xml);
        // echo  $response;
        // $response  = json_decode(DwpHelper::dwp_response_handler($response));
        // print_r($response);
        // die();

        $data = [];
        // $data = TblPorting::orderBy('created_at','desc');
        // $data = $data->get();
        return view('porting-list', compact('data'));
    }

    /**
    * Shows list of port request
    */
    public function porting_list(Request $request)
    {   
        // $porting_list = TblPorting::select('tbl_porting.*', DB::raw("(SELECT name FROM users as u WHERE u.stock_id = tbl_porting.stock_id ) as name"), DB::raw("(SELECT email FROM users as u WHERE u.stock_id = tbl_porting.stock_id) as email"))->join('users as u', 'u.stock_id', '=', 'tbl_porting.stock_id')->join('tbl_sim_stock as s', 's.id', '=', 'tbl_porting.stock_id');

        $porting_list = TblPorting::select('tbl_porting.*', 'u.name', 'u.email')->join('users as u', 'u.stock_id', '=', 'tbl_porting.stock_id')->join('tbl_sim_stock as s', 's.id', '=', 'tbl_porting.stock_id');
        
        if ($request->email != '') { 
            $porting_list = $porting_list->where('u.email', $request->email);
        }                   
        if ($request->reference_id != '') { 
            $porting_list = $porting_list->where('reference_id', $request->reference_id);
        }
        if($request->porting_to != '') {
            $porting_list = $porting_list->where('porting_to', $request->porting_to);
        }
        if($request->port_status != '') { 
            $porting_list = $porting_list->where('tbl_porting.status', $request->port_status);
        }
        if($request->from_date != '') {
            $porting_list = $porting_list->whereDate('tbl_porting.created_at', '>=', $request->from_date);
        }
        if($request->to_date != '') { 
            $porting_list = $porting_list->whereDate('tbl_porting.created_at', '<=', $request->to_date);
        }

        return DataTables::eloquent($porting_list)
            ->editColumn('name', function ($port) {
                if(!$port->name){
                    return $port->list->sim_request->user->name.' (P)';
                }else{
                    return $port->name;
                }
            })->editColumn('email', function ($port) {
                if(!$port->email){
                    return $port->list->sim_request->user->email.' (P)';
                }else{
                    return $port->email;
                }
            })->addColumn('temporary_no', function ($port) {
                if($port->status==4){
                    return is_null($port->stock->temp_number)?'44759xxxxxxx':$port->stock->temp_number;
                }else{
                    return ($port->stock->verified)?$port->stock->phone_number:'44759xxxxxxx';
                }
            })->addColumn('action', function ($port) {
                $html = '';
                $html .= '<a data-toggle="tooltip" title="View Details" href="javascript:void(0);" class="view_port_details text-muted" data-id="'. $port->id .'"><i class="mdi mdi-eye mdi-24px"></i></a>';
                if($port->list->reg_status != 0 && $port->status != 4){
                    $html .= '&nbsp;&nbsp;&nbsp;<a data-toggle="tooltip" title="Edit"  href="'. url('/edi-port', $port->id) .'" class="text-muted"><i class="mdi mdi-pencil mdi-24px"></i></a>';
                }                
                return $html;
            })->toJson();
    }

    /*
    * Show porting request description/details
    */
    public function port_description(Request $request)
    {
        $port_details = TblPorting::where('id', $request->port_id)->first();
        if (!$port_details) {
            return response()->json(['error' => true, 'message' => 'Porting details not exist']);
        }

        $view = view("modal-popup",compact('port_details'))->render();
        return response()->json(['error' => false, 'html' => $view]);
    }

    /*
    * Show details of port request
    */
    public function edit_port_request($id)
    {
        if (!Helper::has_permission('porting', 'edit')) {            
            abort(403,'Access denied');
        }
               
        $port = TblPorting::where('id', $id)->first();
        return view('porting-edit', compact('port'));
    }

    /*
    * Function Update Porting status
    */
    public function update_port_request(Request $request)
    {
        parse_str($request->data, $params);     
        $port = TblPorting::where('id', $params['id'])->first(); 
        $data['pac_number'] = $params['pac_number'];
        $data['porting_to'] = $params['porting_to'];
        $data['provider'] =  ($params['provider'] != 'other') ? $params['provider'] : $params['other_provider'];
        $data['reference_id'] = $params['reference_id'];            
        $data['expected_date'] = $params['expected_date'];
        $data['staff_id'] = Auth::id();
        $user = DB::table('admins')->where('id', Auth::id())->first();

        $description = ($port->description)?json_decode($port->description):[]; 
        $steps = ['Requested','Initiated','Confirmed'];
        $desc = $steps[$request->step].' by '.Auth::user()->first_name.' '.Auth::user()->last_name; 
        array_push($description, $desc);        
        $data['description'] = json_encode($description);
        $step = $request->step+1;
        $data['status'] = $step;
        $respo = TblPorting::where('id', $params['id'])->update($data);

        $port = TblPorting::where('id', $params['id'])->first();

        $html = view('porting-wizard', compact('port'))->render();

        return [ 'html' => $html ];         
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
            foreach ($sim_request as $request) {
                if ($request->delivery_status == 0) {
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
         
            if($stock && $stock->provider == $simList->auto_plan->getplan->provider){
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
    * Postcode finder return address
    */
    public function find_address(Request $request)
    {
        $decoded = Helper::get_postal_address($request->postal_code,$request->house_no);
        if (isset($decoded->Message)) {
           return response()->json(['error' => true, 'type' => 2, 'message' => 'Invalid postcode address', 'address' => '']);
        }    
         
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
            $sim_count = DB::table('tbl_sim_list')->where('request_id', $sim_list->request_id)->count();
            if ($sim_count > 1) {
                $copy = $sim_request->replicate();
                $copy->shipping_address = json_encode($shippingAddress);
                $copy->created_at = $sim_request->created_at;
                $copy->print_status = 0;
                if ($copy->save()) {
                    $sim_list->request_id = $copy->id;
                    if ($sim_list->save()) {
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
}
