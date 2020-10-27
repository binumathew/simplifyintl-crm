<?php

namespace App\Jobs;

use Mail;
use App\Models\User;
use App\Models\AutoPlan;
use App\Models\NotificationLog;
use App\Mail\PaymentFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FailureNotification implements ShouldQueue
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
        $notfication = NotificationLog::where('id',$this->details->notify_id)->first();
        $user = User::find($notfication->user_id);
        $plan = AutoPlan::find($this->details->subscripton);
        $this->details->subject = config('settings.app_name').' Failue Notification';
        $this->details->heading = $notfication->message;
        $this->details->name = $user->name;
        $this->details->phone = $user->phone;
        $this->details->email = $user->email;
        $this->details->plan = $plan->plan->plan_name;
        $this->details->sell_price = $plan->total_amount;
        $this->details->attempt = $notfication->created_at;
        $bcc_emails = Helper::get_option('bcc_emails');
        $bcc_emails = explode(',', $bcc_emails);
        Mail::to(strtolower(config('settings.support_email')))
            // ->cc()
            ->bcc($bcc_emails)
            ->send(new PaymentFailure($this->details));
    }
}
