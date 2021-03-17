<?php

namespace App\Notifications\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SubscriptionPaymentSuccessNotification extends Notification
{
    use Queueable;
    private $details;
    private $status;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details,$status)
    {
        $this->details = $details;
        $this->status  = $status;
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
        return (new MailMessage)
                ->subject(config('settings.app_name').' alert')
                ->view('emails.subscription_payment_success', ['data' => $this->details,'user'=>$notifiable,'status'=>$this->status]);
    }
    /**
     * Get the twilio representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toTwilio($notifiable)
    { 
        if($this->status == 1){
            $content = 'Thanks. We have successfully processed your '.config('settings.app_name').' monthly charges. '.config('settings.app_name').' Team';
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
