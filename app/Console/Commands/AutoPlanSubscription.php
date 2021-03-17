<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;
use Carbon;
use Log;
use App\Models\ScheduledTask;
use App\Models\UserInvoice;

use App\Jobs\Invoice\InvoiceRequestPaymentJob;

class AutoPlanSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plan will subscribe every month';

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
                $start_time = microtime(true);
                $invoiceDay         = Carbon::now()->toDateString();
                $getsubscription    = UserInvoice::select('id')
                                        ->where(function($query) use ($invoiceDay){
                                            $query->whereDate('due_date','=',$invoiceDay);
                                            $query->orWhereDate('next_retry_at','=',$invoiceDay);
                                        })
                                        ->where('deleted', 0)
                                        ->whereIn('status',[0,2,3,8])
                                        ->where('amount_due','>',0)
                                        ->where('failed_attempt','<',2)
                                        ->orderBy('id')
                                        ->get();

                $getsubscription->each(function ($item, $key){
                    InvoiceRequestPaymentJob::dispatch($item->id);
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
                Log::error('AutoPlanSubscription',[
                    'error'=>$e->getMessage()
                ]);
            }
        }
    }
}
