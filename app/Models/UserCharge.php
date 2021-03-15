<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCharge extends Model
{
	protected $table = 'user_charges';

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');        
    }
}
