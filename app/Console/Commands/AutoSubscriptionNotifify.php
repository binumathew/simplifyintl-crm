<?php

namespace App\Console\Commands;

use DB;
use Carbon;
use Helper;
use App\Models\AutoPlan;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use App\Models\User as ModalUser;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
use Illuminate\Console\Scheduling\Schedule;

class AutoSubscriptionNotifify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription Renewal Notification';

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
        if(ScheduledTask::where(['command' => $this->signature, 'status' => 1])->exists()){
            $start_time = microtime(true);
            $next_day = Carbon::now()->addDays(2)->format('Y-m-d');
            $auto_plan = AutoPlan::select('user_id', DB::raw("GROUP_CONCAT(id) as id"))
                        ->where('next_renewal', $next_day)->where('adv_pay', 0)
                        ->where('status', 1)->groupBy('user_id','card_id')->get();

            foreach($auto_plan as $plan){ //in 48 hours
                $user = ModalUser::select('id','first_name','phone')
                            ->where(['id' => $plan->user_id, 'status'=> 1])->first();
                if($user) {
                    $msg = 'Dear '. $user->first_name.', Your '.config('settings.app_name').' Plan is due for renewal in 48 hours and will be charged to your registered card. Thankyou, '.config('settings.app_name').' Customer Service';

                    $account_sid = Helper::get_option('twilio_account_sid');
                    $auth_token =  Helper::get_option('twilio_auth_token');
                    $twilio_number = Helper::get_option('twilio_number');

                    $client = new Client($account_sid, $auth_token);
                    try {                  
                        $client->messages->create(
                            $user->phone,
                            array(
                                'from' => $twilio_number,
                                'body' => $msg
                            )
                        );
                    } catch (RestException $exception) {                   
                        if ($exception->getCode() === 21211) {
                            $response['message'] = $exception->getMessage();
                            NotificationLog::create(['user_id'=>$user->id,'message' => 'Auto Subscription Notification Failed','description'=>'Sub ID:'.$plan->id.', msg:'.json_encode($response),'status'=>'0']);     
                        }
                    }
                }                 
            }  

            /*$did_msg ="International Calls Made Easy\nGet a Free UK number for your international Number more info login your portal for Direct Dial";
            $last_week = Carbon::now()->subDays(7)->format('Y-m-d');
            $users = ModalUser::select('id','first_name','phone')
                        ->whereDate('created_at', $last_week)->where('status', 1)->get();

            $account_sid = Helper::get_option('twilio_account_sid');
            $auth_token =  Helper::get_option('twilio_auth_token');
            $twilio_number = Helper::get_option('twilio_number');

            foreach($users as $user){                
                $client = new Client($account_sid, $auth_token);
                try {                  
                    $client->messages->create(
                        $user->phone,
                        array(
                            'from' => $twilio_number,
                            'body' => $did_msg
                        )
                    );
                } catch (RestException $exception) {                   
                    if ($exception->getCode() === 21211) {
                        $response['message'] = $exception->getMessage();
                        NotificationLog::create(['user_id'=>$user->id,'message' => 'Direct Dial Feature Notification Failed','description'=>'user:'.$user->id.', msg:'.json_encode($response),'status'=>'0']);     
                    }
                }
            }*/
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
