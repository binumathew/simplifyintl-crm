<?php

namespace App\Jobs\Delivery\Telna;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Carbon;
use Mail;
use Utils;
use Log;

use App\Models\User;
use App\Models\SimList;
use App\Mail\Telna\eSimSoftDeliveryMail;

class eSimSoftDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $list_id;
    protected array $credentials;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($list_id,$credentials= [])
    {
        $this->list_id = $list_id;
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sim_list = SimList::whereId($this->list_id)->first();
        try {
            $user    = $sim_list->sim_request->user;
            if($user){
                $token  = Utils::encodeHash($sim_list->id);
                if($token){

                    $obj            = new \stdClass();
                    $obj->name      = ($user->name)?:'User';
                    $obj->subject   = config('settings.app_name').' eSim';
                    $obj->heading   = config('settings.app_name').' eSim';
                    $obj->date      = Carbon::now()->format('d M Y');
                    $obj->token_link = config('app.front_endurl').'qrcode-link?token='.$token;
                    $obj->credentials = $this->credentials;
                    Mail::to($user->email)
                        ->bcc(config('general.settings.technical_support'))
                        ->send(new eSimSoftDeliveryMail($obj));
                        
                }
            }
        } catch (\Exception $e) {
            Log::error('TelnaSoftDelivery',[
                'error' =>  $e->getMessage(),
                'request_id'=> $this->list_id,
                'order'=>$sim_list->sim_request->order_id
            ]);
        }
    }
}
