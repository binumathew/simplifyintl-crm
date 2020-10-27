<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{
	protected $table = 'user_payments';

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');        
    }
}
