<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB,Mail,Helper,Carbon,Storage;
use App\Jobs\CDR\SimUsageSummaryJob;

class MonthlyCdrUpdateJob implements ShouldQueue
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
        $start_time = microtime(true);
        $cdr_list = $call_data = $data_history = [];
        $seller_margin   = Helper::get_option('seller_percent');
        $reseller_margin = Helper::get_option('reseller_percent');
        $getfile = storage_path('/app/calllogs/'.$this->cdr_filename);
        $readfile = fopen($getfile, "r");
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

        $start_month = Carbon::now()->subMonth(1)->format('Y-m-01'); //Carbon::parse('2020-09-01')->subMonth(1)->format('Y-m-02');
        $end_month   = Carbon::parse($start_month)->endOfMonth()->format('Y-m-d');
        //$end_month = Carbon::parse('2020-09-01')->format('Y-m-d');
        DB::table('usage_history')->whereIn('provider', ['O2','VF'])->whereDate('date', '>=',$start_month)
                ->whereDate('date', '<=',$end_month)->delete();
        DB::table('user_calls')->whereIn('provider', ['O2','VF'])->whereDate('connect_date', '>=',$start_month)
                ->whereDate('connect_date', '<=',$end_month)->delete();
        $i_cdr =  Carbon::now()->subMonth(1)->format('ym').str_pad(1, 9, '0', STR_PAD_LEFT);
        if(DB::table('user_calls')->where('i_cdr', $i_cdr)->exists()){
            return true;
        }

        $users = DB::table('trusted_numbers as tn')->select('tn.user_id','tn.trusted_number',DB::raw('TRIM(LEADING c.dial_code FROM trusted_number) as trusted'))
                ->leftJoin('users as us','us.id','=','tn.user_id')
                ->leftJoin('country as c','c.id','=','us.country_id')
                ->get()->keyBy('trusted');

        foreach ($cdr_list as $lkey => $cdr) {
            $from = strval(ltrim(trim($cdr[0]),0));
            $country_code = trim($cdr[9]);
            $dial_code = isset($country[$country_code]) ? $country[$country_code]->dial_code : '';
            //$dial_code = $country[$country_code]->dial_code;
                $charge_code  = trim($cdr[18]);
            $user = $users->get($from);
            if(!is_null($user)){
                $user_id = $user->user_id;
                $from_number = str_replace("+","", $user->trusted_number);
            }else{ continue;}

            $basecost = (float)trim($cdr[8]);
            $resellercost  = $endusercost = 0;
            if($basecost != 0){
                $resellercost = round(($basecost + ($basecost*($seller_margin/100))),4);
                $endusercost  = round(($resellercost + ($resellercost*($reseller_margin/100))),4);
            }
            $provider = strval(trim($cdr[10]));
            $provider = ($provider == 'O2')? 'O2':'VF';
            $country_name = isset($country[$country_code]) ? $country[$country_code]->country_name : '';
            $connect = date("Y-m-d H:i:s",strtotime($cdr[1].' '.$cdr[2]));
            if(strval(trim($cdr[4])) == "D"){
                // if($data_max >= $connect){
                //     continue;
                // }
                $duration = (double)trim($cdr[7]);
                $duration = $duration*1024;
                    $data = ['user_id' => $user_id, 'from_number' => $from_number, 'to_number'=> "", 'date' => $connect, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'DATA', 'provider' => $provider,'charge_code'=>$charge_code];
                $data_history[] = $data;
                continue;
            }
            $service = strval(trim($cdr[18]));
            $duration = (trim($cdr[6]) != "") ? trim($cdr[6]): 0;
            $to_number = strval(ltrim(trim($cdr[5]),0));
            if(strval(trim($cdr[4])) == "G"){
                // if($data_max >= $connect){
                //     continue;
                // }
                if(preg_match('/SMS/', $service) || preg_match('/MMS/', $service)){
                    $sms_duration   = (trim($cdr[6]) != "") ? trim($cdr[6]): 1;

                        $smsdata = ['user_id'=> $user_id, 'from_number' => $from_number, 'to_number'=> $to_number, 'date' => $connect, 'duration' => $sms_duration,  'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO', 'provider' => $provider,'charge_code'=>$charge_code];
                    $data_history[] = $smsdata;
                    continue;
                }
            }
            // if($call_max >= $connect){
            //     continue;
            // }
            $disconnect = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));
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
            $call_data[] = $simhis_data;
            $i_cdr++;
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

        SimUsageSummaryJob::dispatch($start_month, $end_month);
    }
}
