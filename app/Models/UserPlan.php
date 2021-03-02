<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    protected $fillable = ['plan_id', 'user_id', 'status','created_at'];

    protected $table = 'user_plans';

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
}
