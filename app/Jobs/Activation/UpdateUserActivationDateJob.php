<?php

namespace App\Jobs\Activation;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use DwpHelper;
use Log;
use App\Models\SimList;
use App\Models\UserPlan;
use App\Models\AutoPlan;
use App\Models\User;
class UpdateUserActivationDateJob implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user_id;
    private $plan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$plan_id)
    {
        $this->user_id      = $user_id;
        $this->plan_id      = $plan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            $user = User::find($this->user_id);
            $list = SimList::where('stock_id',$user->stock_id)->where('user_id',$this->user_id)->first();
            if(!empty($list)){
                if($list->provision_id != 0){
                    $data['order_id'] =  $list->provision_id;
                    $search_xml = DwpHelper::dwp_order_search($data);
                    $response  = DwpHelper::dwp_process_api($search_xml);
                    $response  = json_decode(DwpHelper::dwp_response_handler($response));
                    if($response->children[0]->no == 0){
                        $result = DwpHelper::dwp_response($response->children);
                        if(is_array($result) && is_array($result['orders']) && isset($result['orders']['block']['components']['block']['completion-date']) && $result['orders']['block']['components']['block']['completion-date'] != ""){
                            $comp_date = $result['orders']['block']['components']['block']['completion-date'];
                            UserPlan::where('id',$this->plan_id)
                                        ->where('user_id',$this->user_id)
                                        //->where('status',1)
                                        ->limit(1)
                                        ->update(['created_at'=>$comp_date,'act_check'=>1]);
                        }else{
                            Log::info('DwpHelperError',[
                                'result' => $result,
                                'user_id'=>$this->user_id,
                                'user_planid'=>$this->plan_id,
                            ]);  
                        }
                    }
                }
            }
    }

}
