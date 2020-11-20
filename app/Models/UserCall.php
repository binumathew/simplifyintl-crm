<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use GlobalSim;
use Carbon;
use Helper;

class UserCall extends Model
{
    protected $table = 'user_calls_copy';
    public $timestamps = true;

    function globalsim_users(){
        return $getusers = DB::table('users as u')
                    ->select('u.id as user_id','tss.phone_number as msisdn')
                    ->join('tbl_sim_stock as tss','u.stock_id','=','tss.id')
                    ->get();
    }
    function globalsim_fetchcrd($user_id,$from,$to){
        $seller_margin      = Helper::get_option('seller_percent');
        $reseller_margin    = Helper::get_option('reseller_percent');
        $user               = User::find($user_id);
        $msisdn             = '447872286827'; //$user->msisdn->phone_number;
        $user_id            = 1;//$user->user_id;
        $country_name       = $user->country->country_name;
        $call_log           = GlobalSim::getCalls($msisdn,$from,$to);
        $data_log           = GlobalSim::getDataHistory($msisdn,$from,$to);

        if($call_log != false){
            if($call_log['@attributes']['status'] == 'success'){
                if(!empty($call_log['Calls'])){
                    foreach($call_log['Calls']['Call'] as $key => $log){
                        $connect    = Carbon::parse($log['ConnectTime'])->format('Y-m-d H:i:s');
                        $disconnect = Carbon::parse($log['EndTime'])->format('Y-m-d H:i:s');
                        $to_number  = strlen(ltrim($log['DialledNumber'])) > 12 ? ltrim($log['DialledNumber'],0) : ltrim($log['DialledNumber']);
                        $duration   = Carbon::parse($log['ConnectTime'])->diffInSeconds(Carbon::parse($log['EndTime']));
                        $basecost = (float)trim($log['CallCost']);
                        $resellercost  = $endusercost = 0;
                        if($basecost != 0){
                            $resellercost = round(($basecost + ($basecost*($seller_margin/100))),4);
                            $endusercost  = round(($resellercost + ($resellercost*($reseller_margin/100))),4);
                        }
                        
                        $callid         = $log['CallId'];
                        $i_cdr          = $callid;
                        $call_type      = trim($log['CallType']);

                        if($call_type == 'SMS Relay'){
                            $smsdata = ['user_id'=> $user_id, 'from_number' => $msisdn, 'to_number'=> $to_number, 'date' => $connect, 'call_id'=> $callid, 'duration' => $duration, 'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO'];
                            $data_history[] = $smsdata; 
                        }else{
                            $service_type   = 1;
                            if($call_type == 'Incoming Call'){
                                $service_type   = 2;
                            }
                            $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $msisdn, 'cli_in' => $msisdn, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'call_id' => $callid,  'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $endusercost,'base_cost'=>$basecost,'reseller_cost'=>$resellercost, 'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name];
                            $call_data[]    = $simhis_data;
                        }
                    }
                }
            }
        }
        if($data_log != false){
            if($data_log['@attributes']['status'] == 'success'){
                if(!empty($data_log['Calls'])){
                    foreach($data_log['Calls']['Call'] as $key => $log){
                        $connect    = Carbon::parse($log['connecttime'])->format('Y-m-d H:i:s');
                        $duration   = (float)trim($log['actualbytes']);
                        $basecost   = (float)trim($log['cost']);
                        $resellercost  = $endusercost = 0;
                        if($basecost != 0){
                            $resellercost = round(($basecost + ($basecost*($seller_margin/100))),4);
                            $endusercost  = round(($resellercost + ($resellercost*($reseller_margin/100))),4);
                        }
                        $callid         = $log['callid'];

                        $data = ['user_id' => $user_id, 'from_number' => $msisdn, 'to_number'=> "", 'date' => $connect,'call_id'=> $callid, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost,'service_type' => 'DATA'];
                        $data_history[] = $data; 
                    }
                }
            }
        }
        
        if(!empty($data_history)){
            DB::table('usage_history_copy')->insert($data_history);
        }
        
        if(!empty($call_data)){
            DB::table('user_calls_copy')->insert($call_data);
        }
    }
}