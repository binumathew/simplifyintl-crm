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

class SoftDelivery //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request_id)
    {
        $this->request_id = $request_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request_id = $this->request_id;
        $sim_list = SimList::where('request_id',$request_id)->first();
        try {
            $user    = $sim_list->sim_request->user;
            if($user){
                $qrdata = $sim_list->stock->stockcode->qr_code;
                if($qrdata){

                    $obj            = new \stdClass();
                    $obj->name      = ($user->name)?:'User';
                    $obj->subject   = config('settings.app_name').' Sim';
                    $obj->heading   = config('settings.app_name').' Sim';
                    $obj->qrcode    = Utils::qrcode($qrdata,true);
                    $obj->date      = Carbon::now()->format('d M Y');

                    Mail::to($user->email)
                        ->bcc(['jijo.joseph@gencomtel.com','arun@gencomtel.com'])
                        ->send(new SimSoftDelivery($obj));
                }
            }
        } catch (\Exception $e) {
            Log::error('SimSoftDelivery',[
                'error' =>  $e->getMessage(),
                'request_id'=> $request_id,
                'order'=>$sim_list->sim_request->order_id
            ]);
        }
    }
}
