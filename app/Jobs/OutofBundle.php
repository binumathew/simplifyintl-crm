<?php

namespace App\Jobs;

use DB, Mail, Helper, Carbon;
use App\Models\User;
use App\Models\TrustedNumber;
use App\Mail\OutofBundleNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

class OutofBundle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
     /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

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
        $fp   = fopen('sim_data_alerts.txt', 'a+');
        foreach ($this->details as $list) {
            $user_id = $list->user_id;
            $planid = $list->planid;
            $user = User::find($user_id);
            $phone = $user->msisdn->phone_number;
            // $trusted = TrustedNumber::where('user_id',$user_id)
            //             ->where('default_number',1)->where('verified',1)->first();
            // if(!empty($trusted)){
                // $phone = str_replace("+","", $trusted->trusted_number);
                $msg = 'Dear Customer, Its seems you have used out of bundle Services, for details please check your usage. Thank you';
                try {             
                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create(
                        '+'.$phone,
                        array(
                            'from' => $twilio_number,
                            'body' => $msg
                        )
                    );                    
                } catch (RestException $exception) {                  
                    
                }
            // }

            if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){
                $obj            = new \stdClass();
                $obj->name      = ($user->name)?:'User';
                $obj->subject   = 'OutofBundle Alerts';
                $obj->heading   = 'OutofBundle Usage';
                $obj->phone     = $phone;
                $obj->date      = $now;

                Mail::to($user->email)
                    ->bcc(['jijo.joseph@gencomtel.com','support@avoomobile.com'])
                    ->send(new OutofBundleNotification($obj));                            
            }
            $update = DB::table('user_plans')->whereId($planid)
                        ->where('plan_type','sim')->update(['outofbundle_notify' => 1]);
        } 
        fclose($fp); 
    }
}
