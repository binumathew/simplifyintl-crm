<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
	protected $table = 'credits';

    protected $fillable = ['amount', 'switch_id', 'default_amount', 'status'];

    public $timestamps = false;

    public function switch()
    {
    	return $this->hasOne('App\Models\SwitchTemplate','id','switch_id');
        
    }
}
