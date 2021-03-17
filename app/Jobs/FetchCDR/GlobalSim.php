<?php

namespace App\Jobs\FetchCDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use Log;
use Utils;
use DB;

use App\Models\User;
use App\Models\UserCall;
use App\Models\UserHistory;
use App\Helpers\GlobalSim as SimHelper;

class GlobalSim implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id){
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $from               = Carbon::now()->subHours(220)->toDateTimeLocalString();//.'T00:00:00';//'2021-03-01T00:00:00';//
        $to                 = Carbon::now()->subHours(218)->toDateTimeLocalString();//'2021-03-16T00:00:00';//Carbon::now()->format('Y-m-d').'T23:59:59';
        $user_id            = $this->user_id;
        $seller_margin      = Utils::settings('seller_percent');
        $reseller_margin    = Utils::settings('reseller_percent');
        $user               = User::find($user_id);
        $msisdn             = $user->msisdn->phone_number;
        $country_name       = $user->country->country_name;
        $call_log           = SimHelper::getCalls($msisdn,$from,$to);
        $data_log           = SimHelper::getDataHistory($msisdn,$from,$to);
        Log::info('GLOBALSIMCDRDETAILS',[
            'user_id'=>$user_id,
            'calls'=> $call_log,
            'data'=>$data_log
        ]);
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
                            
                            $smsdata = ['user_id'=> $user_id, 'from_number' => $msisdn, 'to_number'=> $to_number, 'date' => $connect, 'call_id'=> $callid, 'duration' => 1, 'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO','provider'=>'E_SIM'];
                            $data_history[] = $smsdata;
                        }else{
                            $service_type   = 1;
                            if($call_type == 'Incoming Call'){
                                $service_type   = 2;
                            }
                            $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $msisdn, 'cli_in' => $msisdn, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'call_id' => $callid,  'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $endusercost,'base_cost'=>$basecost,'reseller_cost'=>$resellercost, 'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name,'provider'=>'E_SIM'];
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

                        $data = ['user_id' => $user_id, 'from_number' => $msisdn, 'to_number'=> "", 'date' => $connect,'call_id'=> $callid, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost,'service_type' => 'DATA','provider'=>'E_SIM'];
                        $data_history[] = $data;
                    }
                }
            }
        }
        if(!empty($data_history)){
            UserHistory::insert($data_history);
        }
        if(!empty($call_data)){
           UserCall::insert($call_data);
        }
    }
    public function failed(\Exception $exception){

        //  Log::error('GlobalSimFetchCDR',[
        //      'user_id' => $this->user_id,
        //      'from' =>   $from,
        //      'to'   =>  $to
        //  ]);
    }
}
