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
use App\Jobs\CDR\SimUsageSummaryJob;
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
            $getfile = $this->getfileFTP($remotefile);
        }
    }
    //Ftp connection
    private function getfileFTP($remotefile){
        $ftp    = Storage::disk('sftp');
        try {
            $getFile       = $ftp->readStream($remotefile);
            //$getFile = Storage::disk('gcs')->readStream($remotefile);
            Log::info('calls-monthly-summary-get-file',[
                'params'=>$remotefile
            ]);
            self::processUsage($getFile);
            //$ftp->getDriver()->getAdapter()->disconnect();
            // Log::info('calls-monthly-summary',[
            //     'params'=>$remotefile
            // ]);
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
    private function processUsage($file){

        //Remove existing old CDR from DB
        $start_month = Carbon::now()->subMonth(1)->format('Y-m-01');
        $end_month   = Carbon::parse($start_month)->endOfMonth()->format('Y-m-d');

        DB::table('usage_history')->whereIn('provider', ['O2','VF'])
                                  ->whereDate('date', '>=',$start_month)
                                  ->whereDate('date', '<=',$end_month)
                                  ->delete();

        DB::table('user_calls')->whereIn('provider', ['O2','VF'])
                                    ->whereDate('connect_date', '>=',$start_month)
                                    ->whereDate('connect_date', '<=',$end_month)
                                    ->delete();
        $cdrData    = [];
        $skipheader = true;
        while ($csvLine = fgetcsv($file, 1000, ",")) {
            if($skipheader){ $skipheader = false; continue;}
            else{
                $cdrData[] = $csvLine;
                if(count($cdrData) == 1000){
                    MonthlyCdrUpdateJob::dispatch($cdrData);
                    $cdrData = [];
                }
            }
        }
        MonthlyCdrUpdateJob::dispatch($cdrData); 

        Log::info('calls-monthly-summary-process-complete',[
                'start_month'=>$start_month,
                'end_month'=>$end_month
            ]);

        SimUsageSummaryJob::dispatch($start_month, $end_month);
    }
}
