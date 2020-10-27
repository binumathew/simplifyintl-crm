<?php

namespace App\Console\Commands;

use DB;
use Carbon;
use Helper;
// use SwitchHelper;
// use App\User;
// use App\Models\AutoPlan;
// use App\Models\AutoRecharge;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\User as ModalUser;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

class CardExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'card:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User get notification, if card expire on next month';

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
            $next_month = Carbon::now()->addMonth();
            $planusers  = DB::table('auto_plan')->select('user_id')
                            ->whereDate('card_expiry', $next_month)->get();

            $rechargeusers  = DB::table('auto_recharge')->select('user_id')
                            ->whereDate('card_expiry', $next_month)->get();

            $planusers = collect($planusers)
                            ->map(function($users){ return $users->user_id; })->toArray(); 

            $rechargeusers = collect($rechargeusers)
                            ->map(function($users){ return $users->user_id; })->toArray();

            $getuser = array_unique(array_merge($planusers,$rechargeusers));

            if(!empty($getuser)){
                $users = ModalUser::select('id','first_name','phone')
                            ->whereIn('id', $getuser)
                            ->where('status',1)->get();
                if($users->isNotEmpty()){
                    foreach ($users as $details) {
                        $msg = 'Dear '. $details->first_name.', Your '.config('settings.app_name').' registered card expires in one month. Thankyou, '.config('settings.app_name').' Customer Service';
                        $account_sid = Helper::get_option('twilio_account_sid');
                        $auth_token =  Helper::get_option('twilio_auth_token');
                        $twilio_number = Helper::get_option('twilio_number');

                        $client = new Client($account_sid, $auth_token);
                        try {                  
                            $client->messages->create(
                                $details->phone,
                                array(
                                    'from' => $twilio_number,
                                    'body' => $msg
                                )
                            );
                        } catch (RestException $exception) {                   
                            if ($exception->getCode() === 21211) {
                                $response['message'] = $exception->getMessage();
                                NotificationLog::create(['user_id'=>$details->id,'message' => 'Card Expiry Notification Failed','description'=>'msg:'.json_encode($response),'status'=>'0']);     
                            }
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
