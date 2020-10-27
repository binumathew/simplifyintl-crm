<?php

namespace App\Console\Commands;

use DB; 
use Carbon;
use SwitchHelper;
use App\Models\Account; 
use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\ApiLog;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class CallHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User call history update';

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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(ScheduledTask::where(['command'=> $this->signature,'status'=>1])->exists()){
            $start_time = microtime(true);
            $xml_data = SwitchHelper::switch_call_status_xml();        
            $temp =  SwitchHelper::call_switch_api($xml_data);
            if (array_key_exists("fault",$temp)) {
                //'error', 'call details could not be updated'; 
            } else {
                $calls = [];
                $datas = ($temp['params']['param']['value']['struct']['member'][0]['value']['array']['data'])?:[];
                $cdr_lst = (isset($datas['value']))?$datas['value']:[];
                foreach($cdr_lst as $key => $details) {
                    if(!isset($details['struct'])){
                        $members = $details['member'];
                    }else{
                        $members = $details['struct']['member'];
                    }                
                    
                    foreach($members as $i => $member) {
                        $array_key = array_keys($member['value'])[0];
                        if(empty($member['value'][$array_key])) {
                            $calls[$key][$member['name']]  = NULL;
                        } else {
                            $calls[$key][$member['name']]  = $member['value'][$array_key];
                        }
                    }
                }

                foreach($calls as $call){ 
                    $balance = [];
                    if(DB::table('user_calls')->where('i_cdr', $call['i_cdr'])->exists()){
                        continue;
                    }
                    $user = DB::table('users')->select('id')
                                ->where('i_account',$call['i_account'])->first();
                    if($user){                
                        $account = Account::where('user_id', $user->id)->first();
                        if($call['cost'] > 0){
                            $balance['balance_minutes'] = $account->balance_minutes -($call['plan_duration']/60);
                            $balance['balance_amount'] = $account->balance_amount - $call['cost'];
                        } else {
                            if($account->balance_minutes > 0){                        
                                $minutes = ceil($call['billed_duration']/60);
                                if($account->balance_minutes < $minutes){                            
                                    $tarif_rate = ($minutes - $account->balance_minutes) * $call['price_n'];
                                    $balance['balance_amount'] = $account->balance_amount - $tarif_rate;
                                    $balance['balance_minutes'] = 0;
                                }else{
                                    $balance['balance_minutes'] = $account->balance_minutes - $minutes;
                                }
                            } else {
                                $balance['balance_amount'] = $account->balance_amount - $call['cost'];
                            }
                        }
                        
                        Account::where('user_id', $user->id)->update($balance);                  
                        $data = array(
                            'user_id' => $user->id,
                            'cli' => $call['cli'],
                            'cli_in' => $call['cli_in'],
                            'cld' => $call['cld'],
                            'i_cdr' => $call['i_cdr'],
                            'i_call' => $call['i_call'],
                            'call_id' => $call['call_id'],
                            'duration' => $call['duration'],
                            'billed' => $call['billed_duration']/60,
                            'cost' => $call['cost'],
                            'country' => $call['country'],
                        );
                        $c_time = substr($call['connect_time'], 0, 8);
                        $d_time = substr($call['disconnect_time'], 0, 8);
                        $connect = substr($call['connect_time'], 17).' '.$c_time;
                        $disconnect = substr($call['disconnect_time'], 17).' '.$d_time;
                        $data['connect_date'] = Carbon::parse($connect)->format('Y-m-d H:i:s');
                        $data['disconnect_date']=Carbon::parse($disconnect)->format('Y-m-d H:i:s');
                        DB::table('user_calls')->insert($data);                    
                    }
                }  
            }
            
            $i_account = '20101';
            $xml_data = SwitchHelper::switch_call_status_xml($i_account);        
            $temp = SwitchHelper::call_switch_api($xml_data, '35'); 

            if (array_key_exists("fault",$temp)) {
                //'error', 'call details could not be updated'; 
            } else {
                $calls = [];
                $datas = ($temp['params']['param']['value']['struct']['member'][0]['value']['array']['data'])?:[];
                $cdr_lst = (isset($datas['value']))?$datas['value']:[];
                foreach($cdr_lst as $key => $details) {
                    if(!isset($details['struct'])){
                        $members = $details['member'];
                    }else{
                        $members = $details['struct']['member'];
                    }                
                    
                    foreach($members as $i => $member) {
                        $array_key = array_keys($member['value'])[0];
                        if(empty($member['value'][$array_key])) {
                            $calls[$key][$member['name']]  = NULL;
                        } else {
                            $calls[$key][$member['name']]  = $member['value'][$array_key];
                        }
                    }
                }

                foreach($calls as $call){ 
                    if(DB::table('free_calls')->where('i_cdr', $call['i_cdr'])->exists()){
                        continue;
                    }

                    $user_id = DB::table('trusted_numbers')
                                ->where('trusted_number', $call['cli'])
                                ->value('user_id');
                    if($user_id){                                        
                        $data = array(
                            'user_id' => $user_id,
                            'cli' => $call['cli'],
                            'cld' => $call['cld'],
                            'i_cdr' => $call['i_cdr'],                            
                            'duration' => ceil($call['duration']/60),
                            'cost' => $call['cost'],
                            'country' => $call['country'],
                        );
                        $c_time = substr($call['connect_time'], 0, 8);
                        $d_time = substr($call['disconnect_time'], 0, 8);
                        $connect = substr($call['connect_time'], 17).' '.$c_time;
                        $disconnect = substr($call['disconnect_time'], 17).' '.$d_time;
                        $data['connect_date'] = Carbon::parse($connect)->format('Y-m-d H:i:s');
                        $data['disconnect_date']=Carbon::parse($disconnect)->format('Y-m-d H:i:s');
                        DB::table('free_calls')->insert($data);                    
                    }
                }  
            } 

            $end_time = microtime(true);
            $exec_time = round(($end_time - $start_time), 5);
            $next_run = '';
            collect($this->schedule->events())->map(function ($event) use(&$next_run) {
              if(strpos($event->command, $this->signature)){
                $next = CronExpression::factory($event->expression)->getNextRunDate();
                $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
              }
            });

            $log_time =  Carbon::now()->subDays(5)->format('Y-m-d H:i:s');
            DB::table('api_log')->where('created_at', '<', $log_time)->delete();
            ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}
