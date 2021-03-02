<?php

namespace App\Jobs\Alerts;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Helper;
use Carbon;

use App\Models\UserInvoice;
use App\Models\User;

use App\Notifications\Payment\SubscriptionPaymentNotification;

class SubscriptionPaymentAlertsJob //implements ShouldQueue
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
        $invoice = UserInvoice::whereId($this->inv_id)->where('status','<>',1)->first();
        if($invoice){
            $account_sid    = Helper::get_option('twilio_account_sid');
            $auth_token     = Helper::get_option('twilio_auth_token');
            $twilio_number  = Helper::get_option('twilio_number'); 

            $user           = User::find($invoice->user_id);
            $phone_number   = '+'.$user->msisdn->phone_number;
            $phone          = str_replace($user->country->dial_code,'0',$phone_number);

            $msg = 'Subscription Payment for '.Carbon::parse($invoice->date)->format('F Y').'  '.$user->country->currency_symbol.$invoice->amount_due.' - Nex Mobile';

            // try {             
            //     $client = new Client($account_sid, $auth_token);
            //     $client->messages->create(
            //         $phone_number,
            //         array(
            //             'from' => $twilio_number,
            //             'body' => $msg
            //         )
            //     );                                    
            // } catch (RestException $exception) {                  
                
            // }
            if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){   

                $dataObj = (object)[
                    'subject' => config('settings.app_name').' Payment Alert',
                    'heading' => 'Payment Alert',
                    'invoice' => $invoice
                ];

               $user->notify(new SubscriptionPaymentNotification($dataObj));                     
            }
              
        }
        dd($invoice);
    }
}
