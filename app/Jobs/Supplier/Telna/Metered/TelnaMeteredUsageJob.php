<?php

namespace App\Jobs\Supplier\Telna\Metered;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use DB;
use Carbon;

use App\Models\Provider;

class TelnaMeteredUsageJob //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $cdr_data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cdr_data)
    {
        $this->cdr_data  = $cdr_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->cdr_data)){
            $getprovider     = Provider::where(['short_code'=>config('telna.short_code')])->first();
            $seller_margin   = $getprovider->seller_percent;
            $reseller_margin = $getprovider->reseller_percent;

            $data_history = $call_data = [];

            $users = DB::table('users as us')->select('us.id as user_id','us.phone','ss.sim_number')
                        ->join('tbl_sim_stock as ss','ss.id','=','us.stock_id')
                        ->where(['ss.provider'=>config('telna.short_code')])
                        ->get()->keyBy('sim_number');

            if($users->isNotEmpty()){
                foreach ($this->cdr_data as $lkey => $cdr) {
                    $from    = strval(trim($cdr[1]));
                    $user    = $users->get($from);
                    if(!is_null($user)){
                        $user_id = $user->user_id;
                        $from_number = str_replace("+","", $user->phone);
                    }else{ continue;}
                    $i_cdr = trim($cdr[0]);

                    $connect_time  = Carbon::parse(trim($cdr[3]));
                    $disconnect_time = Carbon::parse(trim($cdr[4]));
 
                    if(strval(trim($cdr[2])) == "DATA"){
                        $data = [
                            'user_id' => $user_id, 
                            'from_number' => $from_number, 
                            'to_number'=> "", 
                            'date' =>$connect_time->setTimezone('Europe/London')->toDateTimeString(), 
                            'disconnect_date'=>$disconnect_time->setTimezone('Europe/London')->toDateTimeString(),
                            'call_id'=>$i_cdr, 
                            'duration' => strval(trim($cdr[5])), 
                            'amount' => 0, 
                            'base_amount'=>0,
                            'reseller_amount'=>0, 
                            'service_type' => 'DATA', 
                            'provider' => $getprovider->short_code,
                            'direction' => strval(trim($cdr[6])),
                            'country_code'=>strval(trim($cdr[9])),
                            'country'=>strval(trim($cdr[10])),
                            'mcc'=>strval(trim($cdr[11])),
                            'mnc'=>strval(trim($cdr[12])),
                            'package_id'=>strval(trim($cdr[17]))
                        ];
                        $data_history[] = $data; 
                        //continue;
                    }
                    if(strval(trim($cdr[2])) == "SMS"){
                        $smsdata = [
                            'user_id'=> $user_id, 
                            'from_number' => strval(trim($cdr[8])), 
                            'to_number'=> strval(trim($cdr[7])), 
                            'date' =>$connect_time->setTimezone('Europe/London')->toDateTimeString(), 
                            'disconnect_date'=>$disconnect_time->setTimezone('Europe/London')->toDateTimeString(), 
                            'call_id'=>$i_cdr, 
                            'duration' => strval(trim($cdr[5])), 
                            'amount' => 0, 
                            'base_amount'=>0,
                            'reseller_amount'=>0, 
                            'service_type' => 'SMS', 
                            'provider' => $getprovider->short_code,
                            'direction' => strval(trim($cdr[6])),
                            'country_code'=>strval(trim($cdr[9])),
                            'country'=>strval(trim($cdr[10])),
                            'mcc'=>strval(trim($cdr[11])),
                            'mnc'=>strval(trim($cdr[12])),
                            'package_id'=>strval(trim($cdr[17]))
                        ];
                        $data_history[] = $smsdata; 
                        //continue;
                    }
                    if(strval(trim($cdr[2])) == "CALL"){
                        $simhis_data = [
                            'user_id' => $user_id, 
                            'connect_date' => $connect_time->setTimezone('Europe/London')->toDateTimeString(), 
                            'disconnect_date' => $disconnect_time->setTimezone('Europe/London')->toDateTimeString(), 
                            'cli' => strval(trim($cdr[8])), 
                            'cli_in' => strval(trim($cdr[8])), 
                            'cld'=> strval(trim($cdr[7])),
                            'call_id' => $i_cdr, 
                            'duration' => strval(trim($cdr[5])), 
                            'billed' => ceil(trim($cdr[5])/60), 
                            'cost' => 0,
                            'base_cost'=>0,
                            'reseller_cost'=>0,
                            'history_from' => 2,
                            'provider' => $getprovider->short_code, 
                            'direction' => strval(trim($cdr[6])), 
                            'country_code'=>strval(trim($cdr[9])),
                            'country'=>strval(trim($cdr[10])),
                            'mcc'=>strval(trim($cdr[11])),
                            'mnc'=>strval(trim($cdr[12])),
                            'package_id'=>strval(trim($cdr[17]))
                        ];
                        $call_data[] = $simhis_data;
                    }
                }
                dd($data_history);
                if(!empty($data_history)){
                    foreach (array_chunk($data_history,1000) as $history){
                        DB::table('usage_history')->insert($history);
                    }                    
                } 
                if(!empty($call_data)){ 
                    foreach (array_chunk($call_data,1000) as $calls){ 
                        DB::table('user_calls')->insert($calls);                  
                    }
                }
            }
        }
    }
}
