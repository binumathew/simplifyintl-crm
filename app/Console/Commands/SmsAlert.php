<?php

namespace App\Console\Commands;

use DB;
use Carbon;
use App\Models\User;
use Cron\CronExpression;
use App\Jobs\SmsAlertsJob;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class SmsAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SMS alert for app users';

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
        if(ScheduledTask::where(['command'=> $this->signature,'status'=>1])->whereDate('updated_at', '!=', Carbon::now())->exists()){
            $start_time = microtime(true);
            $two_days = Carbon::now()->subDays(3)->format('Y-m-d');
            $ten_days = Carbon::now()->subDays(11)->format('Y-m-d');
            $recent =  DB::table('users as u')->select('phone','app_minute', DB::raw("(SELECT sum(billed) FROM user_calls as c WHERE c.user_id = u.id) as duration"))->join('switch_template as t','t.id','=','u.switch_id')->join('user_data as d','d.user_id','=','u.id')->whereDate('u.created_at', $two_days)->where('user_platform', 'APP')->where('u.status', 1)->get();

            $dormant = DB::table('users as u')->select('phone','app_minute',DB::raw("(SELECT sum(billed) FROM user_calls as c WHERE c.user_id = u.id) as duration"),DB::raw("(SELECT count(*) FROM user_plans as p WHERE p.user_id = u.id) as history"))->join('switch_template as t','t.id','=','u.switch_id')->join('user_data as d','d.user_id','=','u.id')->whereDate('u.created_at', $ten_days)->where('user_platform', 'APP')->where('u.status', 1)->get();

            // $recent =  User::select('phone','app_minute', DB::raw("(SELECT sum(billed) FROM user_calls as c WHERE c.user_id = users.id) as duration"))->join('switch_template as t','t.id','=','users.switch_id')->join('user_data as d','d.user_id','=','users.id')->whereDate('users.created_at', $two_days)->where('user_platform', 'APP')->where('users.status', 1)->get();
            // $dormant = User::select('phone','app_minute',DB::raw("(SELECT sum(billed) FROM user_calls as c WHERE c.user_id = users.id) as duration"),DB::raw("(SELECT count(*) FROM user_plans as p WHERE p.user_id = users.id) as history"))->join('switch_template as t','t.id','=','users.switch_id')->join('user_data as d','d.user_id','=','users.id')->whereDate('users.created_at', $ten_days)->where('user_platform', 'APP')->where('users.status', 1)->get();

            if($recent->isNotEmpty()){
                foreach($recent as $user){
                    $msg = '';
                    if($user->duration == 0){
                        $msg='Your available free minutes will expire soon, so make the best ASAP.  Thanks '.config('settings.app_name').' APP';
                    }else if($user->duration >= $user->app_minute){
                        $msg='Thankyou for using '.config('settings.app_name').' Free Minutes, For more call you can Top Up here.  https://'.json_decode(config('settings.company_details'))->company_website.'/freecalls';
                    }
                    if($msg != ''){
                        $obj = (object)['phone' => $user->phone,'msg' => $msg];
                        SmsAlertsJob::dispatch($obj)
                            ->delay(now()->addMinutes(1));
                    }                
                }
            }

            if($dormant->isNotEmpty()){
                foreach($dormant as $user){
                    $msg = '';
                    if($user->duration >= $user->app_minute){
                        $msg='Thankyou for using '.config('settings.app_name').' Free Minutes, For more call you can Top Up here.  https://'.json_decode(config('settings.company_details'))->company_website.'/freecalls';
                    }elseif($user->history == 0){
                        $msg='Hello, keep your international calls down, with affordable '.config('settings.app_name').' App.  https://'.json_decode(config('settings.company_details'))->company_website.'/freecalls Thankyou.';
                    }
                    if($msg != ''){
                        $obj = (object)['phone' => $user->phone,'msg' => $msg];
                        SmsAlertsJob::dispatch($obj)
                            ->delay(now()->addMinutes(1));
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
