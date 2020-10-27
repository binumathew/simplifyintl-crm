<?php

namespace App\Http\Controllers;

use DB;
use File;
use Helper;
use DataTables;
use Carbon;
use Excel;
use App\Exports\CustomExport;
use Illuminate\Http\Request;

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
    public function list_callhistory()
    {
        if (!Helper::has_permission('call_history')) {
            abort(403,'Access denied');
        }
        $users = DB::table('users')->select('id','name','phone')->get();
        return view('call-history.list',compact('users'));
    }

    public function call_history_pagination(Request $request)
    {

    	$calls = DB::table('user_calls')->select('user_calls.id','usr.name as username','cli','cld','duration','cost','connect_date','history_from')->join('users as usr','usr.id','=','user_id')->orderBy('connect_date','DESC');

        if ($request->users != -1) {
            $calls->where('usr.id',$request->users);
        }
        if (!empty($request->from)) {

            $from = Carbon::parse($request->from)->format('Y-m-d');
            if($request->to){
            $to   = Carbon::parse($request->to)->format('Y-m-d');
                $calls->where('connect_date', '>=', $from)
                          ->where('connect_date', '<=', $to);
            }else{
                $calls->whereRaw("date_format(connect_date, '%Y-%m-%d')  = '$from'");
            }
        }
        if(!empty($request->platform)){
            $calls->where('history_from', $request->platform);
        }

        if(!empty($request->call_type)){
            $operator = ($request->call_type == 1)?'=':(($request->call_type == 2)?'>':'>=');
            $calls->where('cost', $operator, 0);            
        }

        $calls = $calls->orderBy('connect_date','desc')->get();
        if($request->exportdata){
            $calls->prepend(array('ID','Name','CLI','CLD','Duration','Cost','Connect Date','1-App/2-Sim'));

            return Excel::download(new CustomExport($calls->toArray()), 'callhistory.csv');
        }else{
             return $result = DataTables::of($calls)
                ->make(true);
        }
        //return DataTables::queryBuilder($calls)->toJson();
    }

    public function import_cdr()
    {        
        return view('import-cdr'); 
    }

    public function import_cdr_data(Request $request)
    {        
        $collection = Excel::toCollection(new \stdClass(), request()->file('file'));

        $i = 0; $msisdn = [];
        foreach($collection as $datas){            
            foreach($datas as $data){
                if($i == 0){
                    $i++;    
                    continue;
                }

                $phone = trim('44'.ltrim($data[0],0));
                $stock = DB::table('tbl_sim_stock')->where('phone_number',$phone)->first();
                if($stock){
                    $user = DB::table('users')->where('stock_id', $stock->id)->first();
                    $country = DB::table('country')->where('country_code',$data[9])->first();
                    $connect = date('Y-m-d H:i:s', strtotime($data[1].' '.$data[2]));
                    $category = trim($data[4]);
                    if($category == 'G'){
                        $disconnect = strtotime($connect) + (int)$data[6];
                        $i_cdr = time().mt_rand(10000, 99999);
                        $cld = str_replace('+','',$country->dial_code.ltrim($data[5],0));
                        $call['user_id'] = $user->id;
                        $call['connect_date'] = $connect;
                        $call['disconnect_date'] = date('Y-m-d H:i:s', $disconnect);
                        $call['cli'] = '+'.$phone;
                        $call['cli_in'] = '+'.$phone;
                        $call['cld']  = trim($cld);
                        $call['i_cdr'] = $i_cdr;                    
                        $call['duration'] = trim($data[6]);
                        $call['cost'] = trim($data[8]);
                        $call['country'] = $country->country_name;
                        $call['history_from'] = '2';
                        $history_exist = DB::table('user_calls')
                                            ->where('connect_date', $connect)
                                            ->whereIn('history_from', [2,3])
                                            ->where('user_id', $user->id)->exists();
                        if($history_exist){
                            continue;
                        }
                        DB::table('user_calls')->insert($call);
                        print_r($call);
                    } else if($category == 'D'){
                        $usage_exist = DB::table('usage_history')->where('date', $connect)
                                        ->where('user_id', $user->id)->exists();
                        if($usage_exist){
                            continue;
                        }
                        $usage = ['user_id' => $user->id, 'from_number' => $phone, 'to_number' => '', 'date' => $connect, 'duration' => $data[7], 'amount' 
                             => $data[8], 'service_type' => 'DATA'];
                        DB::table('usage_history')->insert($usage);
                        print_r($usage);
                    }else{
                        var_dump($category);
                    }
                }else{
                    echo $phone.chr(10);
                }
            }
        }
    }
}
