<?php

namespace App\Console\Commands;

use DB;
use Carbon;
use Helper;
use SwitchHelper;
use App\Models\User;
use App\Models\AutoPlan;
use App\Models\SimStock;
use App\Models\ScheduledTask;
use App\Models\NotificationLog;

use Cron\CronExpression;
use Illuminate\Console\Command;

// use SwitchHelper;
// use App\Models\AutoRecharge;

use Illuminate\Console\Scheduling\Schedule;

class AutoPlanDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoplan:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable autoplan as per the contract period disable request';

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
            $curr_day = Carbon::now()->format('Y-m-d');
            $getplan  = AutoPlan::whereDate('status_changeon', $curr_day)->where('status', 1)->get();

            if(isset($getplan)){
                $mvno_key   = Helper::get_option('bundle_mvno_key');
                $end_point   = '/core/accounts/suspend?MVNO='.$mvno_key;
                foreach ($getplan as $value) {

                   $status['status'] = 0;
                   //$status['status_changeon'] = null; 
                   DB::table('auto_plan')->where('id', $value->id)->update($status);

                   $user_list = explode(',', $value->user_list);
                   foreach ($user_list as $user_id) {
                        $accountid = DB::table('user_data')->where('user_id',$user_id)->value('sim_account_id');
                        $user = User::where('id', $user_id)->first();
                        $stock_id = $user->stock_id;
                        $i_account = $user->i_account;
                        $data = ['AccountId' => $accountid, 'SuspendScope' => "ACCOUNT", 'SuspendReason' => "FREEZE_CUSTREQUEST", 'channel' => "WEB", 'comments' => "" ]; 

                        $response = Helper::call_sim_process_api($end_point, json_encode($data));

                        if($response->resultType == 'Ok' && $response->resultCode == 0){
                            NotificationLog::create(['user_id'=>$user_id,'message' => 'Account Suspended','description'=>'msg:'.json_encode($response).', admin:0','status'=>'1']); 
                            SimStock::where('id', $stock_id)->update(['suspended' => 1]);
                        }else{
                            NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Account Suspention','description'=>'msg:'.json_encode($response).', admin:0','status'=>'0']); 
                        }

                        $method = 'blockAccount'; 
                        $xml_data = SwitchHelper::switch_account_status_xml($method,$i_account);
                        $temp =  SwitchHelper::call_switch_api($xml_data);

                        if (array_key_exists("fault", $temp)) {
                            NotificationLog::create(['user_id'=>$user_id,'message' => 'Error While Switch Blocking','description'=>'msg:'.json_encode($temp).', admin:0','status'=>'0']);
                        }
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
            ScheduledTask::where('command', $this->signature)
                ->update(['run_time' => $exec_time,'next_run' => $next_run]); 
        }
    }
}
