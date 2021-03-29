<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;
use Log;
use App\Models\ScheduledTask;
use App\Models\AutoPlan;

use App\Jobs\Dealer\DealerRevenueJob;

class DealerRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dealer:revenue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Dealer Revenue';

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
            try {
                $start_time      = microtime(true);
                $currDay         = Carbon::now()->startOfMonth()->toDateString();
                $getsubscription =  AutoPlan::select('auto_plan.id')
                                    ->where('auto_plan.status', 1)
                                    ->whereDate('auto_plan.start_date', '<', $currDay)
                                    ->whereNotNull('auto_plan.user_list')
                                    ->orderBy('auto_plan.id')
                                    ->get();

                $getsubscription->each(function ($item, $key){
                    try {
                        DealerRevenueJob::dispatch($item->id);
                    } catch (\Exception $e) {
                        Log::error('DealerRevenueJob',[
                            'params' =>   $item,
                            'error'=>$e->getMessage()
                        ]);
                    }
                    
                });

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
            } catch (\Exception $e) {
                Log::error('InvoiceGenerationJob',[
                    'error'=>$e->getMessage()
                ]);
            }
        }
    }
}
