<?php

namespace App\Console\Commands;

use DB,Mail,Helper,Carbon,Storage;
use Illuminate\Console\Command;
use Cron\CronExpression;

use App\Mail\CronFailure;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

class SimCallData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:calldata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sim call and data logs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
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
            $currday = Carbon::now();
            $year = $currday->year;
            $month = $currday->month;
            $now = $currday->format('Ymd');
            $ftpusername = config('app.simftp_username');
            $remotefile = '/Daily/'.$year.'/'.$month.'/Mobile_'.$now.'_'.$ftpusername.'_Calls.csv';
            $localfile  = $now.'.csv';
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
        $start_time = microtime(true);
        $cdr_list = $call_data = $data_history = [];
        // $getfile = Storage::disk('calllogs')->get($localfile);
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
       
        $j = 0;
        $prevcheck = null;
        $country_list = DB::table('country')->select('country_code','dial_code','country_name')->get();
        foreach ($country_list as $c_list) {
            $country[$c_list->country_code] = $c_list;
        }

        foreach ($cdr_list as $lkey => $cdr) {
            $j++;
            $from = strval(ltrim(trim($cdr[0]),0));
            $country_code = trim($cdr[9]);
            if($from != $prevcheck) {
                $user = DB::table('trusted_numbers')
                           ->where('trusted_number', 'like','%'.$from. '%')->first();
                if(!empty($user)){
                    $user_id = $user->user_id;
                    // $data_max = DB::table('usage_history')->where('user_id', $user_id)->max('date'); 
                    // $call_max = DB::table('user_calls')->where('user_id', $user_id)->where('history_from', 2)->max('connect_date');
                    $from_number = str_replace("+","", $user->trusted_number);
                }else{ continue;}
            }
            $cost = trim($cdr[8]);
            $prevcheck = $from;
            $dial_code = $country[$country_code]->dial_code;
            $country_name = $country[$country_code]->country_name;
            $connect = date("Y-m-d H:i:s",strtotime($cdr[1].' '.$cdr[2]));            
            if(strval(trim($cdr[4])) == "D"){
                // if($data_max >= $connect){                                   
                //     continue;
                // }
                $duration = (double)trim($cdr[7]);
                $duration = $duration*1024;
                $data = ['user_id' => $user_id, 'from_number' => $from_number, 'to_number'=> "", 'date' => $connect, 'duration' => $duration, 'amount' => $cost, 'service_type' => 'DATA','provider' => 'O2'];
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
                if($service == 'O2_SMS' || $service == 'O2_PREMSMS'){
                    $smsdata = ['user_id'=> $user_id, 'from_number' => $from_number, 'to_number'=> $to_number, 'date' => $connect, 'duration' => $duration, 'amount' => $cost, 'service_type' => 'SMS_MO', 'provider' => 'O2'];
                    $data_history[] = $smsdata; 
                    continue;
                }
            }            
            // if($call_max >= $connect){                                   
            //     continue;
            // }
            $disconnect = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));
            $to_number  = str_replace("+","", $dial_code.$to_number);
            $i_cdr = $user_id.$j.time();
            $service_type = 1;
            if($service == 'O2_VML' || $service == 'O2Dise_VML'){
                $service_type = 3;
            }
            
            $simhis_data = ['user_id' => $user_id, 'connect_date' => $connect, 'disconnect_date' => $disconnect, 'cli' => $from_number, 'cli_in' => $from_number, 'cld'=> $to_number, 'i_cdr' => $i_cdr, 'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $cost, 'history_from' => 2, 'service_type' => $service_type, 'country'=> $country_name];
            $call_data[] = $simhis_data;
        }

        if(!empty($data_history)){
            DB::table('usage_history')->insert($data_history);
        } 
        if(!empty($call_data)){                    
            DB::table('user_calls')->insert($call_data);
        }

        //$delete = Storage::disk('calllogs')->delete($localfile);

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
            Mail::to('jijo.joseph@gencomtel.com')
                ->send(new CronFailure($obj));
            $ftp->getDriver()->getAdapter()->disconnect(); 
            return false;
        }
        return true;
    }
}
