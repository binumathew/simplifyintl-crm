<?php

namespace App\Jobs\Delivery;

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
use App\Mail\SimSoftDelivery;

class SoftDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $list_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($list_id)
    {
        $this->list_id = $list_id;
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
                    $obj->subject   = config('settings.app_name').' Sim';
                    $obj->heading   = config('settings.app_name').' Sim';
                    //$obj->qrcode    = Utils::qrcode($qrdata,true);
                    $obj->date      = Carbon::now()->format('d M Y');
                    $obj->plan_name = $sim_list->auto_plan->plan->plan_name;
                    $obj->token_link = config('app.front_endurl').'qrcode-link?token='.$token;
                    Mail::to($user->email)
                        ->bcc(['alphalabsllp.arun@gmail.com'])
                        ->send(new SimSoftDelivery($obj));
                        
                }
            }
        } catch (\Exception $e) {
            Log::error('SimSoftDelivery',[
                'error' =>  $e->getMessage(),
                'request_id'=> $this->list_id,
                'order'=>$sim_list->sim_request->order_id
            ]);
        }
    }
}
