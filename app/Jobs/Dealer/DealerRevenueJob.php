<?php

namespace App\Jobs\Dealer;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;

use App\Models\Admins;
use App\Models\AutoPlan;
use App\Models\DealerRevenue;
use App\Models\PaymentCommission;

class DealerRevenueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $autoplan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($autoplan_id)
    {
        $this->autoplan_id  = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $autoplan = AutoPlan::whereId($this->autoplan_id)->first();
        if(!empty($autoplan)){
            $promocode   = $autoplan->sim[0]->sim_request->promocode;
            if(!is_null($promocode)){
                $childcount  = count(explode(',', $autoplan->user_list));
                $curr_day    = Carbon::now()->format('Y-m-d');
                $dealerWhere = ['promocode' => $promocode, 'short_code' => 'DEALER'];     
                $dealer      = Admins::select('admins.id as dealer_id')
                                ->leftJoin('tbl_roles as r', 'role', '=', 'r.id')
                                ->where($dealerWhere)->first();
                if(!is_null($dealer)){                 
                    $dealer_id  = $dealer->dealer_id;
                    $revenue    = DealerRevenue::select('amount')
                                    ->where('dealer_id',$dealer_id)
                                    ->where('expiry_at','>=', $curr_day)
                                    ->where('status',1)->first(); 
                    if($revenue){
                        $amount         = $revenue->amount;
                        $revenuecomm    = $amount * $childcount;                        
                        $maxend         = PaymentCommission::selectRaw('MAX(payment_end) AS max_end')
                                                    ->where('comm_user', $dealer_id)
                                                    ->where('autoplan_id',$autoplan->id)->value('max_end'); 
                        //to check dealer commission ended to start revenue commission    
                        if(!$maxend || Carbon::parse($maxend)->lessThanOrEqualTo($curr_day)){
                            $commArray['autoplan_id']     = $autoplan->id;
                            $commArray['comm_user']       = $dealer_id;
                            $commArray['payment_date']    = $curr_day;
                            $commArray['payment_end']     = Carbon::tomorrow();
                            $commArray['pay_amount']      = $revenuecomm;
                            $commArray['comm_rate']       = $amount;
                            $commArray['comm_type']       = 1;
                            PaymentCommission::firstOrCreate(['autoplan_id' => $autoplan->id,'comm_user' => $dealer_id,'payment_date' => $curr_day], $commArray);
                        }                                    
                    }
                }
            }
        }
    }
}
