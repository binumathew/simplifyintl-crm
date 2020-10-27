<?php

namespace App\Http\Controllers;

use DB;
use Excel;
use Carbon;
use Helper;
use DataTables;
use App\Exports\CustomExport;
use Illuminate\Http\Request;

use DwpHelper;


class CallHistoryController extends Controller
{
    public function __construct()
    {
        ini_set('memory_limit', '-1');
        $this->middleware('auth');
    }

    /**
    * Display a listing of call.
    *
    * @return \Illuminate\Http\Response
    */
    public function list_call_history()
    {
        if (!Helper::has_permission('call_history') && !Helper::has_permission('call_history','view_own')) {
            abort(403,'Access denied');
        }

        $api_user = Helper::get_option('dwp_auth_username');
        $api_pwd  = Helper::get_option('dwp_auth_password');

        // $xml = '<?xml version="1.0" ? >
        // <Request module="dwapi" call="mobile_compatibilities" id="e21aa535fde298bd50f5ece8e8b5b2e0" version="1.0">
        //   <block name="auth">
        //     <a name="username" format="text">'. $api_user .'</a>
        //     <a name="password" format="password">'. $api_pwd .'</a>
        //     <a name="client-id" format="text">1</a>
        //   </block>          
        //   <a name="mobile-number" format="phone">07766742689</a>
        // </Request>'; //07847359440

        // $response  = DwpHelper::dwp_process_api($xml);
        // $response  = json_decode(DwpHelper::dwp_response_handler($response));
        // print_r($response);

        // $data['phone'] = '07547412616';
        // $data['order_id'] = 'AVO15317262';

        //o2
        // $data['bars_on'] = 'M,sb=0003,w,ab';
        // $data['bars_off'] = '';
        //vuk
        // $data['bars_on'] = 'M,sb=0003,w,ab';
        // $data['bars_off'] = '';
        // $xml = DwpHelper::dwp_set_bar($data);

        // $data['phone'] = '07547412616';
        // $data['order_id'] = 'AVO15317262';

        //o2
        // $data['services'] = ['mm' => 0];
        //vuk 
        // $xml = DwpHelper::dwp_set_services($data);

        // $data['phone'] = '07547412616';
        // $data['order_id'] = 'AVO15317262';
        // //o2
        // $data['apns'] = ['985' => 1,'986' => 1];
        // //vuk
        // $xml = DwpHelper::dwp_set_apns($data);

        // // echo $xml;

        // $response  = DwpHelper::dwp_process_api($xml);
        // $response  = json_decode(DwpHelper::dwp_response_handler($response));
        // print_r($response);        
        // die();

        return view('call-history');
    }

    /**
    * Display a listing of call details.
    *
    * @return \Illuminate\Http\Response
    */
    public function call_history_list(Request $request)
    {
        $call_history = DB::table('user_calls')->select('user_calls.id','u.name as username','cli','cld','duration','cost','connect_date','history_from')->join('users as u','u.id','=','user_id');

        if ($request->user_cli) {
            $call_history->where('u.phone', 'like', '%'.$request->user_cli.'%');
        }

        $from_date =($request->from_date)?Carbon::parse($request->from_date)->startOfDay():Carbon::now()->startOfDay();
        $to_date = ($request->to_date)? Carbon::parse($request->to_date)->endOfDay():Carbon::parse($from_date)->endOfDay();
        $call_history->where('connect_date', '>=', $from_date)->where('connect_date', '<=', $to_date);

        if($request->channel){
            $call_history->where('history_from', $request->channel);
        }

        if($request->call_type){
            $operator = ($request->call_type == 1)?'=':(($request->call_type == 2)?'>':'>=');
            $call_history->where('cost', $operator, 0);            
        }

        $call_history = $call_history->get();
        if($request->exportdata){
            $call_history->prepend(['ID','Name','CLI','CLD','Duration','Cost','Connect Date','1-App/2-Sim']);
            return Excel::download(new CustomExport($call_history->toArray()), 'callhistory.csv');
        }else{
            return $result = DataTables::of($call_history)->make(true);
        }
    }
}
