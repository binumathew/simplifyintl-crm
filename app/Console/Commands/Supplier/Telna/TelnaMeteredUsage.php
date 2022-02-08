<?php

namespace App\Console\Commands\Supplier\Telna;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use DB;
use Carbon;
use Log;
use Storage;
use Mail;

use Cron\CronExpression;
use App\Models\ScheduledTask;
use App\Models\Provider;

use App\Mail\CronFailure;

use App\Jobs\Supplier\Telna\Metered\TelnaMeteredUsageJob;
use App\Jobs\Supplier\Telna\Metered\TelnaUsageSummaryJob;
class TelnaMeteredUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telna:meteredusage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Telna Metered Usage';

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
            $remotePath = '/data/cdr';
            try {

                $getfile    = $this->getfileSFTP($remotePath);

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
    private function getfileSFTP($remotePath){
        $sftp               = Storage::disk('Telna-SFTP');
        $modified           = 0;
        try {
            $allFiles       = $sftp->listContents($remotePath);
            $getprovider    = Provider::where(['short_code'=>config('telna.short_code')])->first();
            $lastrun        = $getprovider->cdr_lastrun;
            $triggersummary = false; 
            $cdr_files      = [];
            foreach($allFiles as $key => $file){
                if($file['timestamp'] > $lastrun && $file['extension'] == 'csv'){
                    $modified = ($file['timestamp']  > $modified) ? $file['timestamp']: $modified;
                    $getFile  = $sftp->readStream($file['path']);
                    self::processUsage($getFile);  
                }
            }
            $sftp->getDriver()->getAdapter()->disconnect();
        } catch (\Exception $e) {
            $sftp->getDriver()->getAdapter()->disconnect();
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').' '.Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => 'Connection error'.$e->getMessage()];
            Mail::to(config('general.settings.technical_support'))
                ->send(new CronFailure($obj));
        }
        if($modified > 0 ){
            DB::table('tbl_providers')->whereId($getprovider->id)
                        ->update(['cdr_lastrun' => date('Y-m-d H:i:s',$modified)]);
            TelnaUsageSummaryJob::dispatch();
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
                if(count($cdrData) == 1000){
                    TelnaMeteredUsageJob::dispatch($cdrData);
                    $cdrData = [];
                }
            }
        }
        if(!empty($cdrData)){
            TelnaMeteredUsageJob::dispatch($cdrData);
        }
    }
}
