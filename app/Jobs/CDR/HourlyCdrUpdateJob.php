<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Helper;
use DB;

class HourlyCdrUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cdr_filename;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cdr_filename)
    {
        $this->cdr_filename  = $cdr_filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cdr_list = $call_data = $data_history = [];

        $seller_margin   = Helper::get_option('seller_percent');
        $reseller_margin = Helper::get_option('reseller_percent');

        $getfile    = storage_path('/app/calllogs/'.$this->cdr_filename);
        $readfile   = fopen($getfile, "r");
        $skipheader = true;
        while ($csvLine = fgetcsv($readfile, 1000, ",")) {
            if($skipheader){ $skipheader = false; continue;}
            else{
              array_push($cdr_list, $csvLine);
            }
        }
        array_multisort(array_column($cdr_list, 0), SORT_ASC, $cdr_list);  

        $prevcheck = null;
        $country_list = DB::table('country')->select('country_code','dial_code','country_name')->get();
        foreach ($country_list as $c_list) {
            $country[$c_list->country_code] = $c_list;
        }

        $users = DB::table('trusted_numbers')->select('user_id','trusted_number')->get()->keyBy('trusted_number');

        foreach ($cdr_list as $lkey => $cdr) {
            $from         = strval(ltrim(trim($cdr[0]),0));
            $country_code = trim($cdr[9]);
            $dial_code    = isset($country[$country_code]) ? $country[$country_code]->dial_code : '';
            $country_name = isset($country[$country_code]) ? $country[$country_code]->country_name : '';
            $user         = $users->get($dial_code.$from);
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
                // if($data_max >= $connect){                                   
                //     continue;
                // }
                $duration = (double)trim($cdr[7]);
                $duration = $duration*1024;
                $data = ['user_id' => $user_id, 'from_number' => $from_number, 'to_number'=> "", 'date' => $connect, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'DATA', 'provider' => $provider];
                $data_history[] = $data; 
                continue;
            }
            $service    = strval(trim($cdr[18]));
            $duration   = (trim($cdr[6]) != "") ? trim($cdr[6]): 0;
            $to_number  = strval(ltrim(trim($cdr[5]),0));

            if(strval(trim($cdr[4])) == "G"){
                // if($data_max >= $connect){                                   
                //     continue;
                // }
                if(preg_match('/SMS/', $service) || preg_match('/MMS/', $service)){

                    $sms_duration   = (trim($cdr[6]) != "") ? trim($cdr[6]): 1;

                    $smsdata = ['user_id'=> $user_id, 'from_number' => $from_number, 'to_number'=> $to_number, 'date' => $connect, 'duration' => $sms_duration,  'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO', 'provider' => $provider];
                    $data_history[] = $smsdata; 
                    continue;
                }
            }            
            // if($call_max >= $connect){                                   
            //     continue;
            // }
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
            
            $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $from_number, 'cli_in' => $from_number, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $endusercost,'base_cost'=>$basecost,'reseller_cost'=>$resellercost,'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name,'provider' => $provider];
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
    }
}
