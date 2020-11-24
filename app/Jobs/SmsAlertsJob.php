<?php

namespace App\Jobs;

use Mail;
use Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;


class SmsAlertsJob implements ShouldQueue
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
        $account_sid = Helper::get_option('twilio_account_sid');
        $auth_token =  Helper::get_option('twilio_auth_token');
        $twilio_number = Helper::get_option('twilio_number');
        try {              
            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $this->details->phone,
                array(
                    'from' => $twilio_number,
                    'body' => $this->details->msg
                )
            );
        } catch (RestException $exception) {                  
            
        }
    }
}
