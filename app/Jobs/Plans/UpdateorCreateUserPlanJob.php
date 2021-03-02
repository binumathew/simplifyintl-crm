<?php

namespace App\Jobs\Plans;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use DB;

use App\Models\UserPlan;

class UpdateorCreateUserPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    protected $plan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$plan_id)
    {
        $this->user_id = $user_id;
        $this->plan_id = $plan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $prev_start = '2020-09-01';//Carbon::now()->subMonth()->startofMonth()->format('Y-m-d');
        $prev_end   = '2020-09-30';//Carbon::now()->subMonth()->endofMonth()->format('Y-m-d');
        $curr_start = '2020-10-01';//Carbon::now()->startofMonth()->format('Y-m-d');
        $curr_end   = '2020-10-31';//Carbon::now()->endofMonth()->format('Y-m-d');

        DB::beginTransaction();
            UserPlan::where('user_id',$this->user_id)
                        ->where('plan_type','sim')
                        ->whereDate('created_at','>=',$prev_start)
                        ->whereDate('created_at','<=',$prev_end)
                        ->update(['status'=>0]);
                        
            $plan = UserPlan::where('user_id',$this->user_id)->whereDate('created_at','>=',$curr_start)->whereDate('created_at','<=',$curr_end)->first();
            if($plan){
                    UserPlan::where('id',$plan->id)
                            ->update(['status'=>1,'plan_id'=>$this->plan_id,'plan_type'=>'sim']);
            }else{
                      
                UserPlan::insertGetId([
                    'user_id'=>$this->user_id,
                    'plan_id'=>$this->plan_id,
                    'payment_id'=>0,
                    'plan_type'=>'sim',
                    'status'=>1,
                    'created_at'=>'2020-10-01 15:04:44'
                ]);
                
            }
        DB::commit();
    }
}
