<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Carbon;
use DwpHelper;
use DB;
use Helper;
use Cache;
use Log;
use GlobalSim;

use App\Models\ScheduledTask;
use App\Models\SimList;
use App\Models\SimStock;
use App\Models\NotificationLog;
use App\Jobs\NotifyActivation;
use Cron\CronExpression;
class ActivationNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:activation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify the dealers when an activation is completed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(ScheduledTask::where(['command' => $this->signature, 'status' => 1])->exists()){
            $start_time = microtime(true);
            $simlist = SimList::where('tbl_sim_list.provision',3)
            ->whereDate('tbl_sim_list.provision_date','>=',Carbon::now()->subDays(7)->format('Y-m-d'))
            ->whereDate('tbl_sim_list.provision_date','<=',Carbon::now()->format('Y-m-d'))
            ->get();
            if($simlist->isNotEmpty()){
            try {
                $notifyusers = [];
                $notifylog = [];
                foreach($simlist as $key => $list){

                    if(in_array($list->stock->provider,['O2','EE_O2','VUK'])){
                        
                        $data['order_id'] =  $list->provision_id;                 
                        $search_xml = DwpHelper::dwp_order_search($data);
                        $response  = DwpHelper::dwp_process_api($search_xml);
                        $response  = json_decode(DwpHelper::dwp_response_handler($response));
                        Log::info('activation-notify',[
                            'request'=> $search_xml,
                            'response' => $response
                        ]);
                        if($response->children[0]->no == 0){
                            $result = DwpHelper::dwp_response($response->children);                                                       
                            $status['state'] =  ucfirst($result['orders']['block']['components']['block']['state']);
                            $status['request_status'] = $result['orders']['block']['request-stage'];         
                            if($status['state'] == 'Completed' && $status['request_status'] == 'Complete'){
                                $user = $list->sim_request->user;
                                DB::table('tbl_sim_list')->whereId($list->id)->update(['provision' => 4]);
                                $sim_number = $result['orders']['block']['components']['block']['sim-serial'];
                                $mobile_number = $result['orders']['block']['components']['block']['mobile-number'];
                                $phone_number = '44'.ltrim($mobile_number,'0');
                                if($sim_number != $list->stock->sim_number){
                                    $payload = json_encode(['type'=>'msisdn_update','order_id'=>$list->sim_request->order_id]);
                                    NotificationLog::create(['user_id'=>$user->id,'message' => 'Mobile number update to stock Failed order '.$list->sim_request->order_id,'payload'=>$payload]); 
                                // $search_xml = DwpHelper::dwp_change_simnumber($data);
                                // $response = DwpHelper::dwp_process_api($search_xml);
                                }
                                try{
                                    SimStock::where('id',$list->stock_id)->update(['phone_number' => $phone_number, 'verified' => 1]);
                                }catch(\Exceptions $e){

                                }
                               
                                $obj = (object)['order_id'=>$list->sim_request->order_id,'user_id'=>$user->id,'name' => $user->first_name.' '.$user->last_name];
                                $payload = json_encode(['type'=>'order_activation','order_id'=>$list->sim_request->order_id]);
                                array_push($notifylog,['user_id'=>$user->id,'message' => 'Activation completed for the user order -'.$list->sim_request->order_id,'payload'=>$payload,'description'=>'Order:'.$list->sim_request->order_id.', name '.$user->name,'status'=>'0']);
                                $notifyusers[$list->stock->dealer_id][] = $obj;
                            }               
                        }
                    }elseif(in_array($list->stock->provider,['E_SIM'])){

                        try {
                            $user           = $list->sim_request->user;
                            $iccid          = $list->stock->sim_number;
                            $getsiminfo     = GlobalSim::getSimInfo($iccid);
                            if($getsiminfo != false){
                                if($getsiminfo['@attributes']['status'] == 'success'){
                                    if(gettype($getsiminfo['Sim']['ActiveProfileLastUsed']) == 'array'){
                                        continue;  
                                    }else{
                                        $msisdn         = $getsiminfo['Sim']['PublicNumber'];
                                        $esimuser       = $getsiminfo['Sim']['UserId'];
                                        $esimcustomer   = $getsiminfo['Sim']['CustomerId'];
                                    }
                                    SimStock::whereId($list->stock->id)->limit(1)->update(['phone_number'=>$msisdn,'verified'=>1]);
                                    SimList::whereId($list->id)->limit(1)->update(['esim_customer'=>$esimcustomer,'esim_user'=>$esimuser,'provision'=>4]);

                                    $obj = (object)['order_id'=>$list->sim_request->order_id,'user_id'=>$user->id,'name' => $user->first_name.' '.$user->last_name];
                                    $payload = json_encode(['type'=>'order_activation','order_id'=>$list->sim_request->order_id]);
                                    array_push($notifylog,['user_id'=>$user->id,'message' => 'Activation completed for the user order -'.$list->sim_request->order_id,'payload'=>$payload,'description'=>'Order:'.$list->sim_request->order_id.', name '.$user->name,'status'=>'0']);
                                    $notifyusers[$list->stock->dealer_id][] = $obj;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('ASSIGNMSISDN',[
                                'order' => $list->sim_request->order_id,
                                'simnumber' => $list->stock->sim_number,
                                'error' =>   $e->getMessage()
                            ]);
                        }
                    }
                }
                if(!empty($notifyusers)){
                    NotificationLog::insert($notifylog);
                    Cache::forget('notifications');
                    NotifyActivation::dispatch($notifyusers)
                    ->delay(Carbon::now()->addSeconds(10));  
                }
            } catch (\Exception $e) {
                Log::error('activation-notify',[
                    'error' => $e->getMessage()
                ]);  
            }
            }
            $end_time = microtime(true);
            $exec_time = round(($end_time - $start_time), 5);
            $next_run = '';
            collect($this->schedule->events())->map(function ($event) use(&$next_run) {
            if(strpos($event->command, $this->signature)){
                $next = CronExpression::factory($event->expression)->getNextRunDate();
                $next_run =  Carbon::parse($next)->format('Y-m-d H:i:s');
            }
            });

            ScheduledTask::where('command', $this->signature)
                    ->update(['run_time' => $exec_time,'next_run' => $next_run]);
        }
    }
}
