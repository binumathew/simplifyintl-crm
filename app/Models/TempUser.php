<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempUser extends Model
{
    protected $table = 'temp_users';
    
    protected $fillable = ['first_name', 'last_name', 'phone', 'email', 'password', 'country_id', 'parent_id'];

    public $timestamps = true;
}
