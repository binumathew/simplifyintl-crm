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

use App\Mail\SimDataNotification;

class UsageAlertsJob implements ShouldQueue
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
        $now_sms        = Carbon::now()->format('d-m-y');

        $account_sid    = Helper::get_option('twilio_account_sid');
        $auth_token     = Helper::get_option('twilio_auth_token');
        $twilio_number  = Helper::get_option('twilio_number'); 

        $user           = User::find($this->details->user_id);
        $phone_number   = '+'.$user->msisdn->phone_number;
        $phone          = str_replace($user->country->dial_code,'0',$phone_number);

        $msg = 'Important: You have used '.$this->details->usage.'% of your data on '.$now_sms.', you may need to add more data till new bundle is active. Pls contact '.config('general.settings.sms_alert_email').' - Nex Mobile';

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
            $obj->subject   = 'Data Usage Alerts';
            $obj->heading   = 'Data Usage Status: Important Information';
            $obj->limit     = $this->details->limit;
            $obj->phone     = $phone;
            $obj->date      = $now;
            $obj->usage     = $this->details->usage;

            Mail::to($user->email)
                ->bcc(config('general.settings.bcc_emails'))
                ->send(new SimDataNotification($obj));
                                
        }
        $update = DB::table('user_plans')
                ->whereId($this->details->planid)
                ->where('plan_type','sim')
                ->update(['usage_notify' => $this->details->category]);
    }
}
