<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimList extends Model
{
    protected $table = 'tbl_sim_list';
    
    public $timestamps = true;

    public function sim_request()
    {
        return $this->hasOne('App\Models\SimRequest','id','request_id');
    }

    public function stock()
    {
        return $this->hasOne('App\Models\SimStock','id','stock_id');
    }

    public function auto_recharge()
    {
        return $this->hasOne('App\Models\AutoRecharge','user_id','user_id');
    }

    public function auto_plan()
    {
        return $this->hasOne('App\Models\AutoPlan','id','autoplan_id');
    }

    public function getSimDetails()
    {
        $flag = 'Active';
        $provision = '0';
        $phone_number = '';
        $stockId = []; 
        $simList = SimList::where('autoplan_id',$this->autoplan_id)->get();
        if ($simList) { 
            foreach ($simList as $list) {
                if ($list->reg_status == 0) {
                    $flag = 'Not Active';
                }else if($list->reg_status == 1) {
                    $flag = 'Processed';
                }
                if($list->stock->verified == 0){                 
                    $phone_number .= '0759xxxxxxx'.' , ';
                }else{
                    $phone_number .= '0'.ltrim($list->stock->phone_number,'44').' , ';
                }
                $provision = $list->provision; 
                $stockId[] = $list->stock_id;
            }
        }
        return [
            'provision' => $provision,
            'status' => $flag,
            'phone_number' => rtrim($phone_number,' , '),
            'stockId' => serialize($stockId),
            'idetifier' => $list->id
        ];
    }

    public function getSim()
    {
        $simList = SimList::where('autoplan_id',$this->autoplan_id)->where('request_id',$this->request_id)->get();
        return[
            'simList' => $simList,
            'count' => count($simList),
        ];
    }
}
