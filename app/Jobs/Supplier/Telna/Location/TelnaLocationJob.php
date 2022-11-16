<?php

namespace App\Jobs\Supplier\Telna\Location;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB;
use Carbon;

use App\Models\Provider;

class TelnaLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $loc_data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($loc_data)
    {
        $this->loc_data  = $loc_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->loc_data)){
            $getprovider     = Provider::where(['short_code'=>config('telna.short_code')])->first();

            $loc_history = [];

            $users = DB::table('users as us')->select('us.id as user_id','us.phone','ss.sim_number')
                        ->join('tbl_sim_stock as ss','ss.id','=','us.stock_id')
                        ->where(['ss.provider'=>config('telna.short_code')])
                        ->get()->keyBy('sim_number');

            if($users->isNotEmpty()){
                foreach ($this->loc_data as $lkey => $loc) {
                    $from    = strval(trim($loc[1]));
                    $user    = $users->get($from);
                    if(!is_null($user)){
                        $user_id = $user->user_id;
                        $from_number = str_replace("+","", $user->phone);
                    }else{ continue;}

                    $connect_time  = Carbon::parse(trim($loc[2]));
 
                    $data = [
                        'user_id' => $user_id, 
                        'location_id'=>trim($loc[0]),
                        'from_number' => $from_number, 
                        'connect_datetime' =>$connect_time->setTimezone('Europe/London')->toDateTimeString(), 
                        'code'=>strval(trim($loc[3])), 
                        'country_code' => strval(trim($loc[5])), 
                        'country' => strval(trim($loc[6])), 
                        'mcc'=>strval(trim($loc[7])),
                        'mnc'=>strval(trim($loc[8])),
                        'service_type'=>strval(trim($loc[4])),
                        'imei'=>strval(trim($loc[11])),
                        'provider' => $getprovider->short_code
                    ];
                    $loc_history[] = $data; 
                }
                if(!empty($loc_history)){
                    foreach (array_chunk($loc_history,1000) as $history){
                        DB::table('location_updates')->insert($history);
                    }                    
                } 
            }
        }
    }
}
