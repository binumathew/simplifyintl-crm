<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon;
use DB;
use App\Jobs\CDR\UpdateUsageSummaryJob;
class EsimUsageSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userlist   = DB::table('users as u')->select('u.id','up.id as user_plan_id','plan_id','up.created_at','prorata','tp.period as period')
                            ->join('user_plans as up','up.user_id','=','u.id')
                            ->join('tbl_plans as tp','tp.id','=','up.plan_id')
                            ->whereIn('tp.provider', ['E_SIM'])
                            ->where('up.plan_type', 'sim')
                            ->where('u.status', 1)
                            ->where('up.status', 1)
                            ->orderBy('up.plan_id')->get();
        if($userlist->isNotEmpty()){
            foreach ($userlist as $user) {
                $start_date  = Carbon::parse($user->created_at)->format('Y-m-d');
                $end_date    = Carbon::parse($user->created_at)->addDays($user->period)->format('Y-m-d');
                UpdateUsageSummaryJob::dispatch($user,$start_date,$end_date);
            }
        }
    }
}
