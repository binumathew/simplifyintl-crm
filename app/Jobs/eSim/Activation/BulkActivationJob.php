<?php

namespace App\Jobs\eSim\Activation;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\TblPlan;
use App\Models\AutoPlan;
use App\Models\SimList;
use App\Models\UserPlan;
use DB;
use Carbon;
use TelnaService;
use Log;

class BulkActivationJob //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $autoplan_id;
    protected $user_id;
    protected $plan_id;
    protected $simlist_id;
    protected $sim_number;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($autoplan_id,$user_id,$plan_id,$simlist_id,$sim_number)
    {
        $this->autoplan_id = $autoplan_id;
        $this->user_id = $user_id;
        $this->plan_id = $plan_id;
        $this->simlist_id = $simlist_id;
        $this->sim_number = $sim_number;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $plan       = TblPlan::whereId($this->plan_id)->first();
        $activation = self::sim_activation($this->sim_number,$plan->sim_billing_plan);
        if($activation){
            DB::beginTransaction();
            try{
                AutoPlan::whereId($this->autoplan_id)->limit(1)
                          ->update(['status'=>1,'start_date'=>now(),'next_renewal'=>Carbon::now()->addDays($plan->period)->toDateString()]);

                UserPlan::insertGetId([
                    'user_id'=>$this->user_id,
                    'plan_id'=>$plan->id,
                    'package_id'=> $activation['package_id'],
                    'payment_id'=>0,
                    'plan_type'=>'sim',
                    'status'=>1
                ]);

                SimList::where(['autoplan_id'=> $this->autoplan_id])->limit(1)
                        ->update(['reg_status'=>1,'provision'=>4]);

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
                Log::error('web:activation-failed',[
                    'error' =>   $e->getMessage(),
                    'user_id'=>$this->user_id,
                    'iccid'=> $this->sim_number,
                ]);
            }
        }
    }
    private function sim_activation($iccid,$billing_plan){
       try{
            $TelnaService = new TelnaService;
            $setdrain     = $TelnaService->set_sim_balance_drain(
                            $iccid,
                            ['drainFromParent' =>false]
                        );
            if($setdrain){
                $activate = $TelnaService->sim_activate(
                            $iccid,
                            ['packageTypeId' =>$billing_plan,
                            'packageStatus'=>'ACTIVE']
                        );
                if($activate){
                    $setActivate = $TelnaService->set_sim_activate(
                                    $activate->packageId,
                                    ['packageStatus'=>'ACTIVE']
                                );
                    if($setActivate){
                        return $response = [
                            'package_id'=> $activate->packageId
                        ];
                    }
                }
            }
            return false;
        }catch(\Exception $e){
            Log::error('web:sim-activation',['iccid'=> $iccid,'error'=>$e->getMessage()]);
            return false;
        } 
    }
}
