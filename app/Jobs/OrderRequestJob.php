<?php

namespace App\Jobs;

use Mail;
use Helper;
use App\Mail\OrderRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OrderRequestJob implements ShouldQueue
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
        $when = now()->addMinutes(5);
        $email_bcc = json_decode(Helper::get_option('email_bcc'),TRUE);
        $email_bcc = (!empty($email_bcc)) ? $email_bcc : [];
        Mail::to($this->details->email)
            ->bcc($email_bcc)
            ->send(new OrderRequest($this->details));
    }
}
