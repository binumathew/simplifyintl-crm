<?php

namespace App\Notifications\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SubscriptionPaymentFailedNotification extends Notification
{
    use Queueable;
    private $details;
    private $attempt;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details,$attempt)
    {
        $this->details = $details;
        $this->attempt = $attempt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail',TwilioChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email_bcc = config('general.settings.bcc_emails');
        return (new MailMessage)
                ->subject(config('settings.app_name').' Payment Notification')
                ->view('emails.subscription_payment_failed', ['data' => $this->details,'user'=>$notifiable,'attempt'=>$this->attempt]);
                // ->bcc($email_bcc);
    }
    /**
     * Get the twilio representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toTwilio($notifiable)
    { 
        if($this->attempt == 1){
            $content = 'Hello, Our attempt to process your subscription payment failed. We will attempt again within 24 Hrs. To avoid service disruption, we request you to kindly contact our customer support ASAP. Best regards, '.config('settings.app_name').' Team';
        }elseif($this->attempt >= 2){
            $content = 'Important Message from '.config('settings.app_name').'.  Our final subscription payment attempt failed, and system will trigger account cancellation within seven days.  Kindly contact Customer Support to make payment and reinstate your account fully. Thanks, '.config('settings.app_name').' Team';
        }
        return (new TwilioSmsMessage())
                        ->content($content);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
