<?php

namespace App\Console\Commands;

use DB,Mail,Helper,Carbon,Storage;
use Illuminate\Console\Command;
use Cron\CronExpression;

use App\Mail\CronFailure;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

use App\Jobs\CDR\SimUsageSummaryJob;
class CallsMonthlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:monthlysummary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sim call and data logs monthly summary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 10000);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
        if($task){
            $currday = Carbon::now()->subMonth(); //Carbon::parse('2020-09-01')->subMonth();//
            $year = $currday->year;
            $month = $currday->month;
            $now = $currday->format('Mpy');
            $ftpusername = config('services.dwp.simftp_username');
            $remotefile = '/Monthly/'.$year.'/Mobile_'.strtoupper($now).'_'.$ftpusername.'_Calls.CSV';

            $localfile  = strtoupper($now).'.csv';
            $exists = Storage::disk('calllogs')->exists($localfile);

            if($exists){
                $upcall = $this->updateCalllogs($localfile);
            }else{
                $getfile = $this->getfileFTP($remotefile,$localfile);
                if($getfile){
                    $upcall = $this->updateCalllogs($localfile);
                }
            }
        }
    }
    //Fetched call logs from FTP to db
    private function updateCalllogs($localfile){
        try{
            $start_time = microtime(true);
            $cdr_list = $call_data = $data_history = [];
            $seller_margin   = Helper::get_option('seller_percent');
            $reseller_margin = Helper::get_option('reseller_percent');
            $getfile = storage_path('/app/calllogs/'.$localfile);
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

            $start_month = Carbon::now()->subMonth()->format('Y-m-01'); //Carbon::parse('2020-09-01')->subMonth(1)->format('Y-m-02');
            $end_month   = Carbon::parse($start_month)->endOfMonth()->format('Y-m-d');
            //$end_month = Carbon::parse('2020-09-01')->format('Y-m-d');
            DB::table('usage_history')->whereIn('provider', ['O2','VF'])->whereDate('date', '>=',$start_month)
                    ->whereDate('date', '<=',$end_month)->delete();
            DB::table('user_calls')->whereIn('provider', ['O2','VF'])->whereDate('connect_date', '>=',$start_month)
                    ->whereDate('connect_date', '<=',$end_month)->delete();
            $i_cdr =  Carbon::now()->subMonth()->format('ym').str_pad(1, 9, '0', STR_PAD_LEFT);
            if(DB::table('user_calls')->where('i_cdr', $i_cdr)->exists()){
                return true;
            }

            $users = DB::table('trusted_numbers')->select('user_id','trusted_number')->get()->keyBy('trusted_number');

            foreach ($cdr_list as $lkey => $cdr) {
                $from = strval(ltrim(trim($cdr[0]),0));
                $country_code = trim($cdr[9]);
                $dial_code = isset($country[$country_code]) ? $country[$country_code]->dial_code : '';
                //$dial_code = $country[$country_code]->dial_code;
                $user = $users->get($dial_code.$from);
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
                    $data = ['user_id' => $user_id, 'from_number' => $from_number, 'to_number'=> "", 'date' => $connect, 'duration' => $duration, 'amount' => $endusercost, 'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'DATA', 'provider' => $provider];
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

                        $smsdata = ['user_id'=> $user_id, 'from_number' => $from_number, 'to_number'=> $to_number, 'date' => $connect, 'duration' => $sms_duration,  'amount' => $endusercost,'base_amount'=>$basecost,'reseller_amount'=>$resellercost, 'service_type' => 'SMS_MO', 'provider' => $provider];
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

                $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $from_number, 'cli_in' => $from_number, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $endusercost,'base_cost'=>$basecost,'reseller_cost'=>$resellercost,'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name,'provider' => $provider];
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
        } catch (\Exception $e) {
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $e->getMessage()];
            Mail::to('arun.raj610@gmail.com')
                ->send(new CronFailure($obj));
        }

        // $exists    = Storage::disk('calllogs')->exists($localfile);
        // if($exists){
        //    $delete = Storage::disk('calllogs')->delete($localfile);
        // }

        $end_time = microtime(true);
        $exec_time = round(($end_time - $start_time), 5);
        $next_run = '';
        collect($this->schedule->events())->map(function ($event) use(&$next_run) {
            if(strpos($event->command, $this->signature)){
                $next = CronExpression::factory($event->expression)->getNextRunDate();
                $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
            }
        });

        ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time, 'next_run' => $next_run, 'executed' => 0]);
        return true;
    }
    //Ftp connection
    private function getfileFTP($remotefile,$localfile){
        $ftp    = Storage::disk('ftp');
        try {
            $file = $ftp->get($remotefile);
            Storage::disk('calllogs')->put($localfile, $file);
            $ftp->getDriver()->getAdapter()->disconnect();
        } catch (\Exception $e) {
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $e->getMessage()];
            Mail::to('arun.raj610@gmail.com')
                ->send(new CronFailure($obj));
            $ftp->getDriver()->getAdapter()->disconnect();
            return false;
        }
        return true;
    }
}
