<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Admins;
use Mail;
use Carbon;

use App\Mail\ActivationNotify;

class NotifyActivation implements ShouldQueue
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
       //\Log::info(json_encode($this->details));
        foreach ($this->details as $dkey => $list) {
            $hierarchy = Admins::find($dkey)->ascendings()->toArray();
            array_push($hierarchy,$dkey);
            $emails    = Admins::select('id','email')->whereIn('id',$hierarchy)->get()->keyBy('id')->toArray();
            $to        = $emails[$dkey]['email'];
            unset($emails[$dkey]);
            $cc = join(',',array_column($emails, 'email'));

                $obj            = new \stdClass();
                $obj->name      = 'Dealer';
                $obj->subject   = 'Activation Completed';
                $obj->heading   = 'Activation Completed';
                $obj->userlist  = $list;
                $obj->date      = Carbon::now()->format('d-M-Y');

                Mail::to($to)
                    //->cc($cc)
                    ->send(new ActivationNotify($obj));
       }
    }
}
