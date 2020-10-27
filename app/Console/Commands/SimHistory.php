<?php

namespace App\Console\Commands;

use DB;
use Helper;
use Carbon;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\User as ModalUser;
use Illuminate\Console\Command;
use App\Models\NotificationLog;
use Illuminate\Console\Scheduling\Schedule;

class SimHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Sim history update';

    /**
     * The schedule instance.
     *
     * @var \Illuminate\Console\Scheduling\Schedule
     */
    protected $schedule;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        ini_set('memory_limit', '-1');
        // ini_set('max_execution_time', 5000);
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
          $executed = $no_users = $i = 0;          
          $start_time = microtime(true);
          $mvno_key   = Helper::get_option('bundle_mvno_key');
          // $no_users = ModalUser::whereNotNull('stock_id')->count(); 
          // $limit = 50; /*50*/ $executed = $task->executed;   
          // if($no_users > 2400){   //24*6 = 144 for ten min run users limit = 144*50
          //   $limit = $no_users/48;
          // }
          
          // $users = ModalUser::whereNotNull('stock_id')->skip($executed)->take($limit)->get();
          $fp = fopen('call_his_exec.txt', 'a+');
          $users = ModalUser::whereNotNull('stock_id')->get();
          foreach($users as $user){
            ++$i;
            $user_start_time = microtime(true);
            if($user->msisdn->provider != 'EE'){
              continue;
            }              
            $user_id = $user->id;
            $from = DB::table('user_calls')
                        ->where('user_id', $user_id)->where('history_from', 2)
                        ->max('connect_date');
            if($from == ""){
                $from = $user->created_at;
            }
            if($from != ""){
              $fromdate = Carbon::parse($from)->format('Y-m-d H:i:s');
              $todate = Carbon::now()->format('Y-m-d H:i:s');
              $msisdn = $user->msisdn->phone_number;         
              $params = 'msisdn='.$msisdn.'&fromDate='.urlencode($fromdate).'&toDate='.urlencode($todate);
              $end_point = '/superapi/usage?'.$params.'&mvno='.$mvno_key;
              $response = Helper::call_sim_process_api($end_point,'','get');
              if($response->message == 'Success' && $response->statusCode == 0){ 
                if(!empty($response->usages)){
                  $data_max = DB::table('usage_history')->where('user_id', $user_id)->max('date');       
                  $call_data = $data_history = []; 
                  $j = 0;                
                  foreach($response->usages as $history){
                    ++$j;
                    $connect = Carbon::parse($history->date)->format('Y-m-d H:i:s');
                    if($history->serviceType == 'DATA' || $history->serviceType == 'SMS_MO'){                            
                        if($data_max >= $connect){                                   
                            continue;
                        }
                        if($history->to == '65075'){
                            NotificationLog::create(['user_id'=>$user_id,'message' => 'Port Out Requested -'.$connect,'description'=>'user:'.$user_id.', msg:port','status'=>'0']); 
                        }
                        $data = ['user_id'=> $user_id,'from_number'=>$history->from,'to_number'=>$history->to,'date'=>$connect,'duration'=>$history->duration,'amount'=> $history->amount,'service_type' => $history->serviceType ];
                        $data_history[] = $data;                                 
                        continue;
                    }
                    $duration = $history->duration;
                    $disconnect = date("Y-m-d H:i:s", (strtotime(date($connect)) + $duration));                        
                    if($history->serviceType == 'VOICE_MO' || $history->serviceType == 'VOICE_MT'){
                      $service_type = ($history->serviceType == 'VOICE_MO')?'1':'2';
                      if($history->to == '447973101233'){
                          $service_type = 3;
                      }
                      $i_cdr = $i.$j.time();
                      $simhis_data = ['user_id' =>$user_id, 'connect_date' =>$connect, 'disconnect_date' => $disconnect, 'cli' => $history->from, 'cli_in' => $history->from, 'cld'=> $history->to, 'i_cdr' => $i_cdr, 'duration' => $duration, 'billed' => ceil($duration/60), 'cost' => $history->amount, 'history_from' => 2, 'created_at'=> $todate, 'service_type'=> $service_type];
                      $call_data[] = $simhis_data;
                    }
                  }

                  if(!empty($call_data)){                    
                    DB::table('user_calls')->insert($call_data);
                  }

                  if(!empty($data_history)){
                    DB::table('usage_history')->insert($data_history);
                  } 
                }
              }
            }
            $user_end_time = microtime(true); 
            $user_exec_time = round(($user_end_time - $user_start_time), 5);            
            fwrite($fp, $user_id.'--'.$user_exec_time.chr(10));           
          }
           fclose($fp); 
          $end_time = microtime(true);
          $exec_time = round(($end_time - $start_time), 5);
          $next_run = '';
          collect($this->schedule->events())->map(function ($event) use(&$next_run) {
            if(strpos($event->command, $this->signature)){
              $next = CronExpression::factory($event->expression)->getNextRunDate();
              $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
            }
          });
          $executed += count($users);
          $executed = ($executed != $no_users)? $executed : 0;
          ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time,'next_run' => $next_run,'executed' => $executed]);
      }
    }
}
