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
            if(date('t') == date('d')){
                $currday    = Carbon::now()->addMonthsNoOverflow();
            }
            
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
                $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').' '.Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $e->getMessage()];
                Mail::to(config('general.settings.technical_support'))
                    ->send(new CronFailure($obj));
            }

        }
    }

    private function getfileFTP($remotePath){
        $ftp            = Storage::disk('sftp');
        try {
            $allFiles       = $ftp->listContents($remotePath);
            dd($allFiles);
            $lastrun        = strtotime(Helper::get_option('cdr_lastrun'));
            $triggersummary = false; 
            $cdr_files      = [];
            $modified       = 0;
            foreach($allFiles as $key => $file){

                if($file['timestamp'] > $lastrun && $file['extension'] == 'csv'){
                    $modified = ($file['timestamp']  > $modified) ? $file['timestamp']: $modified;
                    $getFile  = $ftp->readStream($file['path']);
                    self::processUsage($getFile);  
                }
            }
            $ftp->getDriver()->getAdapter()->disconnect();
        } catch (\Exception $e) {
            $ftp->getDriver()->getAdapter()->disconnect();
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').' '.Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => 'Connection error'.$e->getMessage()];
            Mail::to(config('general.settings.technical_support'))
                ->send(new CronFailure($obj));
        }
        if($modified > 0 ){
            DB::table('options')
                        ->where('name','cdr_lastrun')
                        ->update(['value' => date('Y-m-d H:i:s',$modified)]);
            SimUsageSummaryJob::dispatch();
        }     
        return true;
    }
    private function processUsage($file){
        $cdrData    = [];
        $skipheader = true;
        while ($csvLine = fgetcsv($file, 1000, ",")) {
            if($skipheader){ $skipheader = false; continue;}
            else{
                $cdrData[] = $csvLine;
            }
        }
        if(!empty($cdrData)){
            foreach (array_chunk($cdrData,1000) as $key => $list) {
               HourlyCdrUpdateJob::dispatch($list);
            }
        }
    }
}
