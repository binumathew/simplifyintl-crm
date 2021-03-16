<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon;
use Cron\CronExpression;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

use App\Jobs\FetchCDR\GlobalSim;
use App\Jobs\CDR\EsimUsageSummaryJob;
class FetchCDR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:cdr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch cdr';

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
            $users      = DB::table('users as u')
                        ->select('u.id as user_id')
                        ->join('tbl_sim_stock as tss','u.stock_id','=','tss.id')
                        ->where('tss.provider','E_SIM')
                        ->get();
            if($users->isNotEmpty()){
                // foreach($users as $key => $list){
                //     $user_id = $list->user_id;
                //     try{
                //         GlobalSim::dispatch($user_id);  
                //     }catch(\Exception $e){
                //         Log::error('GLOBALSIMCDR',[
                //             'user_id'=>$user_id,
                //             'error'=> $e->getMessage()
                //         ]);
                //     }
                // }
              EsimUsageSummaryJob::dispatch();  
            }
            $end_time   = microtime(true);
            $exec_time  = round(($end_time - $start_time), 5);
            $next_run   = '';
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
