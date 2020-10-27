<?php

namespace App\Console\Commands;

use DB; 
use Carbon;
use App\Models\User;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\ApiLog;
use App\Models\UserData;
use App\Models\UserPlan;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class AddonUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addon:usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '0870 Usage Limit alert';

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
            $message = '<html><body>';
            $message .= '<h3>O870 Usage Limit!</h3>';
            
            $user_data = UserData::select('user_id')->where('call_settings->bundle_o', '1')->get();
            foreach($user_data as $item){
                $user = User::find($item->user_id);
                $user_plan = UserPlan::where(['user_id'=>$user->id,'status'=>1])->first();
                
         
                $total_calls = $total_min = 0;
                $activated = '';
                if($user_plan){
                    $activated = date('Y-m-01');
                    $total_calls = DB::table('free_calls')->where('user_id', $user->id)
                                ->whereDate('connect_date','>=',$activated)->count();
                    $total_min = DB::table('free_calls')->where('user_id', $user->id)
                                ->whereDate('connect_date','>=',$activated)->sum('duration');
                    $activated = $activated;
                } 
                if($total_min > 250)
                    $message .= '<p style="color:red;">';
            
                $message .= $user->name.'-'.$user->phone.'-'.$total_calls.'-'.$total_min.chr(10);
                if($total_min > 250)
                    $message .= '</p>';
                
            } 
            $message .= '</body></html>';      
            if($message != ''){
                $to = "support@gencomtel.com";
                $subject = "0870 Usage -".date('d-m-Y h A');            
                $headers = "From: ".strtolower(config('settings.support_email')) . "\r\n" .
                    "CC: jijo.joseph@gencomtel.com,shine@gencomtel.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                mail($to,$subject,$message,$headers);
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
            ScheduledTask::where('command', $this->signature)->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}
