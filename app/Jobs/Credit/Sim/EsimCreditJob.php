<?php

namespace App\Jobs\Credit\Sim;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use GlobalSim;
use DB;
use Log;
use Notification as Notify;
use App\Models\User;
use App\Models\AutoPlan;
use App\Models\Account;

use App\Notifications\Payment\PrepaidCreditFailedNotification;
class EsimCreditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $autoplan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$autoplan_id)
    {
        $this->user_id     = $user_id;
        $this->autoplan_id = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user     = User::find($this->user_id);
        $autoplan = AutoPlan::find($this->autoplan_id);

        if($autoplan->plan->reset_prepaid_credit){
            /* function to reset credit */
        }
        if($user->userDetail->prepaid_credit != 0){
            try {
                $addcreditreq = GlobalSim::AddPrePaidCredit($user->userDetail->esim_customer,$user->userDetail->prepaid_credit);
                if($addcreditreq == false || $addcreditreq['@attributes']['status'] == 'fail'){
                    Log::error('AddPrePaidCredit',[
                        'esim_customer' => $user->userDetail->esim_customer,
                        'error' =>   $addcreditreq
                    ]);
                    Notify::route('mail' , config('general.settings.support_email'))
                            ->notify(new PrepaidCreditFailedNotification($user));
                }else{
                    Account::where('user_id',$user->id)->limit(1)->update([
                        'balance_amount' => DB::raw('balance_amount +'.$user->userDetail->prepaid_credit)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('AddPrePaidCredit',[
                    'esim_customer' => $user->userDetail->esim_customer,
                    'error' =>   $e->getMessage()
                ]);
                Notify::route('mail' , config('general.settings.support_email'))
                ->notify(new PrepaidCreditFailedNotification($user));
            }
        }
    }
}
