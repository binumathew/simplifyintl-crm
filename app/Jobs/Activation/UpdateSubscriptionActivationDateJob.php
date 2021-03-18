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
use App\Models\AutoPlan;
use App\Models\User;

class UpdateSubscriptionActivationDateJob implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $autoplan_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($autoplan_id)
    {
        $this->autoplan_id      = $autoplan_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $list = SimList::where('autoplan_id',$this->autoplan_id)->first();
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
                        $result = AutoPlan::where('id',$this->autoplan_id)
                                    ->limit(1)
                                    ->update(['start_date'=>$comp_date,'act_check'=>1]);
                    }else{
                        Log::info('DwpHelperError',[
                            'result' => $result,
                            'autoplan_id'=>$this->autoplan_id
                        ]);  
                    }
                }
            }
        }
    }
}
