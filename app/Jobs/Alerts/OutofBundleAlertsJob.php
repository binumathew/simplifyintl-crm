<?php

namespace App\Jobs\Alerts;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use Helper;
use Mail;
use DB;

use App\Models\User;

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

use App\Mail\OutofBundleNotification;

class OutofBundleAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now            = Carbon::now()->format('d-M-Y H:i');
        $account_sid    = Helper::get_option('twilio_account_sid');
        $auth_token     = Helper::get_option('twilio_auth_token');
        $twilio_number  = Helper::get_option('twilio_number');

        $user           = User::find($this->details->user_id);
        $phone_number   = '+'.$user->msisdn->phone_number;
        $phone          = str_replace($user->country->dial_code,'0',$phone_number);

        $msg = 'Important note: It seems you have used some chargeable premium services on your '.$phone.'. Please contact us to resolve the same. Best - '.config('settings.app_name');
        
        try {             
            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $phone_number,
                array(
                    'from' => $twilio_number,
                    'body' => $msg
                )
            );                    
        } catch (RestException $exception) {                  
            
        }
        if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){
            $obj            = new \stdClass();
            $obj->name      = ($user->name)?:'User';
            $obj->subject   = 'OutofBundle Alerts';
            $obj->heading   = 'OutofBundle Usage';
            $obj->phone     = $phone;
            $obj->date      = $now;
            $obj->usage     = $this->details->usage;

            Mail::to($user->email)
                ->bcc(config('general.settings.bcc_emails'))
                ->send(new OutofBundleNotification($obj));                            
        }
        $update = DB::table('user_plans')
                ->whereId($this->details->planid)
                ->where('plan_type','sim')
                ->update(['outofbundle_notify' => $this->details->category]);
    }
}
