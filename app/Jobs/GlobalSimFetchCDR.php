<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Helper;
use Carbon;
use DB;

use App\Models\UserCall;

class GlobalSimFetchCDR //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->model = new UserCall();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $from               = Carbon::now()->format('Y-m-d').'T00:00:00';
        $to                 = Carbon::now()->format('Y-m-d').'T23:59:59';
        
        $users              = $this->model->globalsim_users();
        if($users->isNotEmpty()){
            foreach($users as $key => $list){
                $user_id = $list->user_id;
                try{
                    $this->model->globalsim_fetchcdr($user_id,$from,$to);
                }catch(\Exception $exception){
                    //\Log::info('error');
                    return false;
                }
           }
        }
    }
    public function failed(\Exception $exception)
    {
         //\Log::info('error'); 
    }
}
