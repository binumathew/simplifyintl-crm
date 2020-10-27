<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoPlanHistory extends Model
{
	protected $table = 'auto_plan_history';

    protected $fillable = ['auto_plan_id', 'payment_id', 'renewal_date', 'description', 'status'];

    public $timestamps = false;
}
