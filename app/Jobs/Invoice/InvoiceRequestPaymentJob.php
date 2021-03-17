<?php

namespace App\Jobs\Invoice;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB;
use Carbon;
use Stripepayments;
use Helper;
use ServiceHelper;
use Log;

use App\Models\User;
use App\Models\UserInvoice;
use App\Models\UserInvoiceTransaction;
use App\Models\UserInvoiceTransactionMeta;
use App\Models\UserCharge;
use App\Models\UserInvoiceItem;
use App\Models\AutoPlan;

use App\Jobs\Credit\Sim\EsimCreditJob;
use App\Jobs\Payments\stripeDirectDebitPaymentJob;

use App\Notifications\Payment\SubscriptionPaymentSuccessNotification;
use App\Notifications\Payment\SubscriptionPaymentFailedNotification;

class InvoiceRequestPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $inv_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inv_id)
    {
        $this->inv_id = $inv_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $invoice = UserInvoice::whereId($this->inv_id)->first();
        $user    = User::whereId($invoice->user_id)->first();
        if($invoice){
            if($invoice->amount_due > 0 ){

                if($invoice->subscription->gateway == 'Stripe'){

                    if(in_array($invoice->subscription->plan->provider,['E_SIM'])){
                        $plan_desc = 'Subscription ('.Carbon::parse($invoice->date)->format('d-m-Y').' - '.Carbon::parse($invoice->date)->addDays($invoice->subscription->plan->period)->format('d-m-Y').')';
                    }else{
                        $plan_desc = 'Subscription ('.Carbon::parse($invoice->date)->format('d-m-Y').' - '.Carbon::parse($invoice->date)->endOfMonth()->format('d-m-Y').') Other charges ('.Carbon::parse($invoice->date)->subMonth()->format('d-m-Y').' - '.Carbon::parse($invoice->date)->subMonth()->endOfMonth()->format('d-m-Y').')'; 
                    }

                    $inv_txn =  UserInvoiceTransaction::insertGetId([
                                'invoice_id' => $this->inv_id,
                                'payment_method_id'=>$invoice->subscription->card_id,
                                'date' => Carbon::now()->toDateString(),
                                'amount'=> $invoice->amount_due,
                                'currency_code'=>$invoice->currency_code,
                                'description'=> $plan_desc,
                            ]);

                    $metadata    = [
                        'user_id' => $invoice->user_id,
                        'autoplan_id'=> $invoice->subscription_id,
                        'invoice_id' => $this->inv_id,
                        'invoice_txn_id'=>$inv_txn,
                        'payment_for'=>'subscription',
                        'description'=> config('settings.app_name').$plan_desc,
                    ];

                    if($invoice->subscription->gateway == 'Stripe'){
                        $gateway = DB::table('payment_gateway')->select('id')->where('gateway',$invoice->subscription->gateway)->first();

                        $paymentdata = [
                            'user_id'   => $invoice->user_id,
                            'card_id'   => $invoice->subscription->card_id,
                            'total_amount'=> $invoice->amount_due,
                            'payment_for' => config('settings.app_name').$plan_desc,
                            'gateway_id'=> $gateway->id
                        ];
                        
                        $result  = Stripepayments::stripeCardPayment($paymentdata,$metadata);

                        if($result->status){

                            DB::beginTransaction();
                                UserInvoiceTransaction::whereId($inv_txn)->limit(1)
                                                    ->update(['status'=>1,'transaction_id'=>$result->transaction_id]);
                                UserInvoice::whereId($this->inv_id)->limit(1)->update([
                                    'status'=>1
                                ]);
                                AutoPlan::whereId($invoice->subscription_id)->limit(1)->update([
                                    'transaction_id'=>$result->payment_method
                                ]);
                            DB::commit();

                            if(in_array($invoice->subscription->plan->provider,['E_SIM'])){
                                EsimCreditJob::dispatch($invoice->user_id,$invoice->subscription_id);
                            }
                            
                            $user->notify(new SubscriptionPaymentSuccessNotification($invoice,1));

                        }else{
                            $attemptcount = $invoice->failed_attempt+1;

                            $failed_fee = 0;

                            if($attemptcount > 1){

                                $failed_fee = Config('general.settings.stripe_failed_fee');
                                $lineitems = [
                                'invoice_id' => $invoice->id,
                                'plan_id'=>null,
                                'description' => 'Stripe failed fee -'.Carbon::now()->format('d-m-Y'),
                                'quantity'=>1,
                                'price'=>$failed_fee
            
                                ];
                            }

                            $attemptcharge = Helper::vataddCalculation($failed_fee,$user->country->tax);

                            $next_retry = Carbon::now()->addDays(config('general.settings.instant_failed_retry_days'))->toDateString();

                            DB::beginTransaction();

                                if($attemptcount > 1){

                                    UserCharge::insertGetId([
                                        'user_id'=>$user->id,
                                        'amount'=> $failed_fee,
                                        'comments'=>'Stripe failed fee -'.Carbon::now()->format('d-m-Y'),
                                        'state'=>1
                                    ]);
                                    UserInvoiceItem::insert($lineitems);
                                }

                                UserInvoiceTransaction::whereId($inv_txn)->limit(1)
                                                    ->update(['status'=>8]);

                                UserInvoice::whereId($this->inv_id)->limit(1)->update([
                                    'failed_attempt' => DB::raw('failed_attempt + 1'),
                                    'sub_total' => DB::raw('sub_total +'.$attemptcharge->amount),
                                    'tax'=>DB::raw('tax +'.$attemptcharge->tax_amount),
                                    'total'=>DB::raw('total +'.$attemptcharge->total_amount),
                                    'amount_due'=>DB::raw('amount_due +'.$attemptcharge->total_amount),
                                    'next_retry_at' => $next_retry,
                                    'paid_at' => Carbon::now()->toDateString(),
                                    'status'=>8
                                ]);
                                UserInvoiceTransactionMeta::insert([
                                    'inv_txn_id' => $inv_txn,
                                    'meta_data' => $result->error
                                ]);
                            DB::commit();
                            $user->notify(new SubscriptionPaymentFailedNotification($invoice,$attemptcount));
                        }
                    }
                }
            }
        }
    }
}
