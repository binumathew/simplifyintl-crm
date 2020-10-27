<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Throttle extends Model
{
    protected $fillable = ['identifier', 'ip_address', 'attempted_at']; 
    public $timestamps = false;
}
