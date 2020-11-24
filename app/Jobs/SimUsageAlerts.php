<?php

namespace App\Jobs;

use Carbon;
use Helper;
use DB;
use Mail;
use App\Mail\SimDataNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

use App\Models\User;
use App\Models\TrustedNumber;

class SimUsageAlerts implements ShouldQueue
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
        $now     = Carbon::now()->format('d-M-Y H:i');

        $account_sid    = Helper::get_option('twilio_account_sid');
        $auth_token     = Helper::get_option('twilio_auth_token');
        $twilio_number  = Helper::get_option('twilio_number'); 
        $fp   = fopen('sim_data_alerts.txt', 'a+');
        foreach ($this->details as $dkey => $list) {
            $user_id = $list->user_id;
            $planid  = $list->planid;

            $user    = User::find($user_id);
            $getphone   = TrustedNumber::where('user_id',$user_id)
                        ->where('default_number',1)->where('verified',1)->first();
            if(!empty($getphone)){
                $phone = str_replace("+","", $getphone->trusted_number);
                $msg = 'You have used '.$list->usage.'% of your '.$list->limit.' GB on your '.$phone.' as of '.$now.' Hrs';

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
                
                if(filter_var($user->email, FILTER_VALIDATE_EMAIL)){                    
                    $obj            = new \stdClass();
                    $obj->name      = ($user->name)?:'User';
                    $obj->subject   = 'Data Usage Alerts';
                    $obj->heading   = 'Data Usage Status: Important Information';
                    $obj->limit     = $list->limit;
                    $obj->phone     = $phone;
                    $obj->date      = $now;
                    $obj->usage     = $list->usage;

                    Mail::to($user->email)
                        ->bcc(['jijo.joseph@gencomtel.com','support@avoomobile.com'])
                        ->send(new SimDataNotification($obj));
                }

                $update = DB::table('user_plans')->whereId($planid)
                    ->where('plan_type','sim')->update(['usage_notify' => $list->category]);
            }
        } 
        fclose($fp);
    }
}
