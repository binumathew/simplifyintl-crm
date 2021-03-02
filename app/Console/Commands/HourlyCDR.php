<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Cron\CronExpression;

use Storage;
use Helper;
use Carbon;
use DB;
use Mail;
use App\Models\ScheduledTask;

use App\Mail\CronFailure;
use App\Jobs\CDR\HourlyCdrUpdateJob;
use App\Jobs\CDR\SimUsageSummaryJob;
class HourlyCDR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourly:cdr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hourly CDR of DWP portal';

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
        $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
        if($task){
            $start_time = microtime(true);

            $currday    = Carbon::now();
            $year       = $currday->year;
            $month      = $currday->month;
            $remotePath = '/Daily/'.$year.'/'.$month.'/';

            try {
                $getfile    = $this->getfileFTP($remotePath);

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

            } catch (\Exception $e) {

                $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
                $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $e->getMessage()];
                Mail::to('jijo.joseph@gencomtel.com')
                    ->bcc(['arun.raj610@gmail.com'])
                    ->send(new CronFailure($obj));
            }

        }
    }

    private function getfileFTP($remotePath){
        $ftp            = Storage::disk('ftp');
        try {
            $allFiles       = $ftp->allFiles($remotePath);
            $lastrun        = strtotime(Helper::get_option('cdr_lastrun'));
            $triggersummary = false; 
            $cdr_files      = [];
            foreach($allFiles as $key => $file){

                $type = $ftp->mimeType($file);
                $ext  = explode('/',$type)[1];

                if($ftp->lastModified($file) > $lastrun && $ext == 'csv'){                                                           
                    $filename = explode('/',$file)[3];
                    $getFile = $ftp->get($file);
                    $modified = date('Y-m-d H:i:s', $ftp->lastModified($file));
                    Storage::disk('calllogs')->put($filename, $getFile);
                    DB::table('options')
                        ->where('name','cdr_lastrun')
                        ->update(['value' => $modified]);

                    $cdr_files[] = $filename;
                }
            }
            $ftp->getDriver()->getAdapter()->disconnect();
        } catch (\Exception $e) {
            $ftp->getDriver()->getAdapter()->disconnect();
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => 'Connection error'.$e->getMessage()];
            Mail::to('jijo.joseph@gencomtel.com')
                // ->bcc(['arun.raj610@gmail.com'])
                ->send(new CronFailure($obj));
        }
        
        if(!empty($cdr_files)){
            foreach($cdr_files as $file){
                try {  

                    HourlyCdrUpdateJob::dispatch($file);
                } catch (\Exception $e) {

                    $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
                    $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $filename.' Failed to retrieve and update '.$e->getMessage()];

                    Mail::to('jijo.joseph@gencomtel.com')
                        // ->bcc(['arun.raj610@gmail.com'])
                        ->send(new CronFailure($obj));
                }                
            }
            SimUsageSummaryJob::dispatch();
        }        
        return true;
    }
}
