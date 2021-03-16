<?php

namespace App\Jobs\Invoice;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

use Helper;
use Log;
use Carbon;
use DB;

use App\Models\AutoPlan;
use App\Models\User;
use App\Models\UserCharge;
use App\Models\UserPlan;
use App\Models\UserInvoice;
use App\Models\UserInvoiceItem;
use App\Models\UserPayment;

use App\Jobs\Alerts\SubscriptionPaymentAlertsJob;

class InvoiceGenerationJob implements ShouldQueue
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
        $this->autoplan_id      = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $autoplan_id    = $this->autoplan_id;
        $autoplan       = AutoPlan::whereId($autoplan_id)->first();
        if(in_array($autoplan->plan->provider,['E_SIM'])){
            $invoiceDate    = Carbon::parse($autoplan->next_renewal)->toDateString();
            $prev_start     = Carbon::parse($autoplan->next_renewal)->subDays($autoplan->plan->period)->toDateString();
            $prev_end       = Carbon::parse($autoplan->next_renewal)->subDay()->toDateString();
            $next_renewal   = Carbon::parse($invoiceDate)->addDays($autoplan->plan->period)->toDateString();
            $next_end_date = Carbon::parse($invoiceDate)->addDays($autoplan->plan->period)->format('d-m-Y');
            $diff_in_months = 1;
        }else{
            $invoiceDate    = Carbon::now()->startOfMonth()->toDateString();
            $prev_start     = Carbon::parse($invoiceDate)->subMonth()->startOfMonth()->toDateString();
            $prev_end       = Carbon::parse($invoiceDate)->subMonth()->endOfMonth()->toDateString();
            $next_renewal   = Carbon::parse($invoiceDate)->addMonthNoOverflow()->startOfMonth()->toDateString();
            $next_end_date  = Carbon::parse($invoiceDate)->endOfMonth()->format('d-m-Y');
            $diff_in_months = Carbon::parse($autoplan->start_date)->diffInMonths(Carbon::parse($invoiceDate));
        }

        if(!empty($autoplan)){
            $user    = User::whereId($autoplan->user_list)->where('status',1)->first();
            $blocked = Helper::check_fraudster($autoplan->user_id);
        }
        if($user && !$blocked){
            $net_out_charge = $billamount = $billtotal = $planamount = $balancecredit = $amountdue = $creditapplied = $prepaid_credit = 0;

            $credits         = $user->credits;

            if($diff_in_months >= 1){
                $getamount   = Helper::taxCalculation($autoplan->plan->sell_price,$user->country);
                $planamount += $getamount->amount;
                $plan_desc  = Carbon::parse($invoiceDate)->format('d-m-Y').' - '.$next_end_date;
                if(in_array($autoplan->plan->provider,['E_SIM'])){
                    $prepaid_credit = $user->userDetail->prepaid_credit;
                }
            }else{
                $numberofdays   = Carbon::parse($autoplan->start_date)->endOfMonth()->diffInDays(Carbon::parse($autoplan->start_date)) + 1;
                $days           = Carbon::parse($autoplan->start_date)->daysInMonth;
                $prorata_extra  = ($autoplan->plan->sell_price/$days) * $numberofdays;
                $prorata        = Helper::taxCalculation($prorata_extra,$user->country);
                $planamount     += $prorata->amount;
                $plan_desc  = Carbon::parse($autoplan->start_date)->format('d-m-Y').' - '.Carbon::parse($autoplan->start_date)->endOfMonth()->format('d-m-Y');
                $credits = $planamount;
            }
            $add_charge_list = UserCharge::where('user_id',$user->id)->where('state',0)
                                    ->whereDate('created_at', '>=', $prev_start)
                                    ->whereDate('created_at', '<=', $prev_end)
                                    ->get();

            $add_charges     = $add_charge_list->sum('amount');

            $out_charge_list = UserPlan::where('user_id',$user->id);

            if(in_array($autoplan->plan->provider,['E_SIM'])){
                $out_charge_list = $out_charge_list->where('status',1)->first();
            }else{
                $out_charge_list =  $out_charge_list->whereDate('created_at', '>=', $prev_start)
                                    ->whereDate('created_at', '<=', $prev_end)
                                    ->first();
                if($out_charge_list){
                    $net_out_charge = $out_charge_list->service_total;
                }
            } 

            $billamount     = ($planamount + $add_charges + $net_out_charge + $prepaid_credit);

            $billtotal      = ($credits >= $billamount) ? 0 : $billamount - $credits;
            $creditapplied  = ($credits >= $billamount) ?  $billamount : $credits;
            $balancecredit  = ($credits >= $billamount) ? $credits - $billamount : 0; 

            $getbill        = Helper::vataddCalculation($billtotal,$user->country->tax);
            $amountdue      = $getbill->total_amount;
            $status         = ($amountdue == 0) ? 1 : 0;
            $paid_at        = ($amountdue == 0) ? $autoplan->start_date : null;

            $getinv =   UserInvoice::updateOrCreate(
                            ['date' => $invoiceDate,'user_id'=>$user->id],
                            [
                                'user_id'=>$user->id,
                                'subscription_id'=>$autoplan_id,
                                'status'=>$status,
                                'date'=> $invoiceDate,
                                'sub_total'=>$getbill->amount,
                                'tax'=>$getbill->tax_amount,
                                'total'=>$getbill->total_amount,
                                'credits_applied'=>$creditapplied,
                                'amount_due'=>$amountdue,
                                'currency_code'=>$user->country->currency,
                                'paid_at'=>$paid_at
                            ]
                        );

            $lineitems[] = [
                'invoice_id' => $getinv->id,
                'plan_id'=>$autoplan->plan->id,
                'description'=> $plan_desc,
                'quantity'=>1,
                'price'=>$planamount

            ];
            if($prepaid_credit != 0){
                $lineitems[] = [
                    'invoice_id' => $getinv->id,
                    'plan_id'=>null,
                    'description'=> 'Prepaid Credit',
                    'quantity'=>1,
                    'price'=>$prepaid_credit
                ];
            }
            if($out_charge_list && $net_out_charge != 0){
                if($out_charge_list->service_total > 0){
                    $outparms = collect(['sms','call','data']);
                    $outparms->each(function ($item, $key) use(&$lineitems,$getinv,$out_charge_list){
                        if($out_charge_list[$item.'_cost'] > 0){
                            $lineitems[] = [
                                'invoice_id' => $getinv->id,
                                'plan_id'=>null,
                                'description'=> $item.' charge - '.Carbon::parse($out_charge_list->created_at)->format('d-m-Y').' - '.Carbon::parse($out_charge_list->created_at)->endOfMonth()->format('d-m-Y'),
                                'quantity'=>1,
                                'price'=>$out_charge_list[$item.'_cost']
                
                            ];  
                        }
                    });
                }
            }
            if($add_charge_list->isNotEmpty()){

                $add_charge_list->each(function ($item, $key) use(&$lineitems,$getinv){
                    $lineitems[] = [
                        'invoice_id' => $getinv->id,
                        'plan_id'=>null,
                        'description' => $item->comments.' - '.Carbon::parse($item->created_at)->format('d-m-Y'),
                        'quantity'=>1,
                        'price'=>$item->amount

                    ];
                });
            }
            DB::beginTransaction();
                UserInvoiceItem::insert($lineitems);
                User::where('id', $user->id)->limit(1)->update([
                    'credits' => $balancecredit
                ]);
                UserCharge::where('user_id',$user->id)
                            ->whereDate('created_at', '>=', $prev_start)
                            ->whereDate('created_at', '<=', $prev_end)
                            ->update(['state'=>1]);
                UserPlan::where('user_id',$user->id)
                            ->whereDate('created_at', '>=', $prev_start)
                            ->whereDate('created_at', '<=', $prev_end)
                            ->limit(1)->update(['out_pay_status'=>1]);
                AutoPlan::whereId($autoplan->id)->limit(1)->update([
                    'next_renewal' => $next_renewal
                ]);
            DB::commit();
            if(in_array($autoplan->plan->provider,['E_SIM'])){
                DB::beginTransaction();
                    UserPlan::where('user_id',$user->id)
                            ->where('plan_type','sim')
                            ->whereDate('created_at','>=',$prev_start)
                            ->whereDate('created_at','<=',$prev_end)
                            ->update(['status'=>0]);
                    if(UserPlan::where('user_id',$user->id)->whereDate('created_at','>=',$invoiceDate)->whereDate('created_at','<=',$next_renewal)->doesntExist()){
                        UserPlan::insertGetId([
                            'user_id'=>$user->id,
                            'plan_id'=>$autoplan->plan_id,
                            'payment_id'=>0,
                            'plan_type'=>'sim',
                            'status'=>1,
                            'created_at'=>$invoiceDate
                        ]);
                    }
                DB::commit();
            }
        }
    }
}
