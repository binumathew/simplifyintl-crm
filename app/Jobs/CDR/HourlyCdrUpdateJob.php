<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Helper;
use DB;
use Carbon;

class HourlyCdrUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cdr_data;
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

            $cdr_list  = $call_data = $data_history = $prev_call_data = $prev_data_history = [];
            $prev_date = Carbon::now()->subMonthsNoOverflow()->endOfMonth()->format('Y-m-d').' 23:59:59';

            $seller_margin   = Helper::get_option('seller_percent');
            $reseller_margin = Helper::get_option('reseller_percent');

            $prevcheck = null;
            $country_list = DB::table('country')->select('country_code','dial_code','country_name')->get();
            foreach ($country_list as $c_list) {
                $country[$c_list->country_code] = $c_list;
            }

            $users = DB::table('trusted_numbers as tn')->select('tn.user_id','tn.trusted_number',DB::raw('TRIM(LEADING c.dial_code FROM trusted_number) as trusted'))
                        ->leftJoin('users as us','us.id','=','tn.user_id')
                        ->leftJoin('country as c','c.id','=','us.country_id')
                        ->get()->keyBy('trusted');

            foreach ($this->cdr_data as $lkey => $cdr) {
                $from         = strval(ltrim(trim($cdr[0]),0));
                $country_code = trim($cdr[9]);
                $dial_code    = isset($country[$country_code]) ? $country[$country_code]->dial_code : '';
                $country_name = isset($country[$country_code]) ? $country[$country_code]->country_name : '';
                $charge_code  = trim($cdr[18]);
                $user         = $users->get($from);
                if(!is_null($user)){
                    $user_id = $user->user_id;
                    $from_number = str_replace("+","", $user->trusted_number);
                }else{ continue;}
                $i_cdr = $user_id.strtotime(trim($cdr[1]).' '.trim($cdr[2]));

                $basecost       = (float)trim($cdr[8]);
                $resellercost   = $endusercost = 0;
                if($basecost != 0){
                    $resellercost = round(($basecost + ($basecost*($seller_margin/100))),4);
                    $endusercost  = round(($resellercost + ($resellercost*($reseller_margin/100))),4);
                }

                $prevcheck  = $from;
                $provider   = strval(trim($cdr[10]));
                $provider   = ($provider == 'O2')? 'O2':'VF';
                $connect    = date("Y-m-d H:i:s",strtotime($cdr[1].' '.$cdr[2]));    

                if(strval(trim($cdr[4])) == "D"){
                    $duration = (double)trim($cdr[7]);
                    $duration = $duration*1024;

                    $data = ['user_id' => $user_id, 'from_number' => $from_number, 'to_number'=> "", 'date' => $connect, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'DATA', 'provider' => $provider,'charge_code'=>$charge_code];

                    if($connect <= $prev_date){  
                        $prev_data_history[] = $data;
                        continue;
                    }
                    $data_history[] = $data; 
                    continue;
                }
                $service    = strval(trim($cdr[18]));
                $duration   = (trim($cdr[6]) != "") ? trim($cdr[6]): 0;
                $to_number  = strval(ltrim(trim($cdr[5]),0));

                if(strval(trim($cdr[4])) == "G"){
                    if(preg_match('/SMS/', $service) || preg_match('/MMS/', $service)){

                        $sms_duration   = ($provider == "VF") ? 1 : trim($cdr[6]);

                        $smsdata = ['user_id'=> $user_id, 'from_number' => $from_number, 'to_number'=> $to_number, 'date' => $connect, 'duration' => $sms_duration,  'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO', 'provider' => $provider,'charge_code'=>$charge_code];

                        if($connect <= $prev_date){  
                            $prev_data_history[] = $smsdata;
                            continue;
                        }
                        $data_history[] = $smsdata; 
                        continue;
                    }
                }            
                $disconnect     = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));
                if(strlen($to_number) <= 10){
                    $to_number  = str_replace("+","", $dial_code.$to_number);
                }else{
                    $to_number  = str_replace("+","", $to_number);  
                }

                $service_type = 1;
                if(preg_match('/VML/', $service)){
                    $service_type = 3;
                }

                $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $from_number, 'cli_in' => $from_number, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $endusercost,'base_cost'=>$basecost,'reseller_cost'=>$resellercost,'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name,'provider' => $provider,'charge_code'=>$charge_code];

                if($connect <= $prev_date){  
                    $prev_call_data[] = $simhis_data;
                    continue;
                }

                $call_data[] = $simhis_data;
            }
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
            if(!empty($prev_data_history)){
                foreach (array_chunk($prev_data_history,1000) as $prev_history){
                DB::table('usage_history_prev')->insert($prev_history);
                }                    
            } 
            if(!empty($prev_call_data)){ 
                foreach (array_chunk($prev_call_data,1000) as $prev_calls){ 
                    DB::table('user_calls_prev')->insert($prev_calls);                  
                }
            }
        }
    }
}
