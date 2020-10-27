<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCommission extends Model
{
	protected $table = 'userplan_commissions';
	protected $fillable = ['user_id','plan_type', 'plan_id', 'comm_duration','comm_type','comm_rate','status'];
    
    public function plan()
    {
        if ($this->plan_type == 1) {
            return $this->hasOne('App\Models\TblPlan','id','plan_id');
        } else if ($this->plan_type == 2) {
            return $this->hasOne('App\Models\TblBundle','id','plan_id');
        }
        
    }
}