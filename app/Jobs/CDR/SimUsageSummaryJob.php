<?php

namespace App\Jobs\CDR;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use DB;
use Log;

use App\Models\TblPlan;
use App\Models\UserPlan;

use App\Jobs\CDR\UpdateUsageSummaryJob;

class SimUsageSummaryJob implements ShouldQueue
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
        
        $start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_date   = Carbon::now()->endOfMonth()->format('Y-m-d').' 23:59:59';
        $userlist   = DB::table('users as u')->select('u.id','up.id as user_plan_id','plan_id','up.created_at','prorata')
                            ->join('user_plans as up','up.user_id','=','u.id')->where('plan_type', 'sim')
                            ->where('u.status', 1)
                            ->where('up.status', 1)
                            ->whereDate('up.created_at', '>=',$start_date)
                            ->whereDate('up.created_at', '<=',$end_date)
                            ->orderBy('plan_id')->get();
        

        if($userlist->isNotEmpty()){
            foreach ($userlist as $user) {
                UpdateUsageSummaryJob::dispatch($user->id,$user->user_plan_id,$user->plan_id);
            }
        }
        
    }
}
