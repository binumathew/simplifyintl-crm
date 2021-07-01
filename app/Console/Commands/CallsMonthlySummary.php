<?php

namespace App\Console\Commands;

use DB,Mail,Helper,Carbon,Storage;
use Illuminate\Console\Command;
use Cron\CronExpression;
use Log;

use App\Mail\CronFailure;
use App\Models\ScheduledTask;
use Illuminate\Console\Scheduling\Schedule;

use App\Jobs\CDR\MonthlyCdrUpdateJob;
class CallsMonthlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:monthlysummary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sim call and data logs monthly summary';

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
            $currday = Carbon::now()->subMonth(); //Carbon::parse('2020-09-01')->subMonth();//
            $year = $currday->year;
            $month = $currday->month;
            $now = $currday->format('Mpy');
            $ftpusername = config('services.dwp.simftp_username');
            $remotefile = '/Monthly/'.$year.'/Mobile_'.strtoupper($now).'_'.$ftpusername.'_Calls.CSV';

            $localfile  = strtoupper($now).'.csv';
            $exists = Storage::disk('calllogs')->exists($localfile);

            if($exists){
                MonthlyCdrUpdateJob::dispatch($localfile);
            }else{
                $getfile = $this->getfileFTP($remotefile,$localfile);
                if($getfile){
                    MonthlyCdrUpdateJob::dispatch($localfile);
                }
            }
        }
    }
    //Ftp connection
    private function getfileFTP($remotefile,$localfile){
        $ftp    = Storage::disk('ftp');
        try {
            $file = $ftp->get($remotefile);
            Storage::disk('calllogs')->put($localfile, $file);
            $ftp->getDriver()->getAdapter()->disconnect();
        } catch (\Exception $e) {
            $task = ScheduledTask::where(['command'=>$this->signature,'status'=>1])->first();
            $obj = (object)['subject' => 'Cron Failure '.config('settings.app_name').Carbon::now()->format('Y-m-d'), 'heading' => 'Cron Failure '.config('settings.app_name'), 'cron' => $task->description, 'error' => $e->getMessage()];
            Mail::to(config('general.settings.technical_support'))
                ->send(new CronFailure($obj));
            $ftp->getDriver()->getAdapter()->disconnect();
            return false;
        }
        return true;
    }
}
