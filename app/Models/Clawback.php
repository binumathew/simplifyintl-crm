<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clawback extends Model
{
	protected $table = 'clawback';
	protected $fillable = ['admin_id', 'plan_type', 'plan_id', 'period','status'];

    public function plan()
    {
    	if ($this->plan_type == 1) {
    		return $this->hasOne('App\Models\TblPlan','id','plan_id');
    	} else if ($this->plan_type == 2) {
    		return $this->hasOne('App\Models\TblBundle','id','plan_id');
    	}
        
    }
}