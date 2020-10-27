<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoPlan extends Model
{
    protected $table = 'auto_plan';

    protected $fillable = ['user_id', 'transaction_id', 'plan_id', 'next_renewal', 'amount', 'tax', 'total_amount', 'bundle_id', 'card_expiry', 'card_type', 'card_id', 'gateway', 'adv_pay', 'status'];

    public function plan()
    {
    	if ($this->plan_type == 'sim') {
    		return $this->hasOne('App\Models\TblPlan','id','plan_id');
    	} else if ($this->plan_type == 'switch') {
    		return $this->hasOne('App\Models\Plan','id','plan_id');
    	} else if ($this->plan_type == 'bridge') {
            return $this->hasOne('App\Models\ConferencePlan','id','plan_id');
        }
        
    }

    public function sim()
    {
        return $this->hasMany('App\Models\SimList','autoplan_id','id');
    }

    public function getChild(){
        $phone = [];
        if($this->child){
            $stockid = explode(',', $this->child);
            $diff    = array_diff( $stockid, [$this->stock_id] ); //to remove parent stock id
            $simList = SimStock::whereIn('id', $diff)->get();
            foreach ($simList as $list) {
               $phone[] = $list->phone_number;
            }
        }
        return $phone;
    }

    public function getplan()
    {
        if ($this->bundle_id == 0) {
            return $this->hasOne('App\Models\TblPlan','id','plan_id');
        } else if ($this->bundle_id != 0) {
            return $this->hasOne('App\Models\TblBundle','id','bundle_id');
        }
        
    } 
}
