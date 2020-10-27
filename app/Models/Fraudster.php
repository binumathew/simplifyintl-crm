<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fraudster extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'created_at']; 
    public $timestamps = false;
}
