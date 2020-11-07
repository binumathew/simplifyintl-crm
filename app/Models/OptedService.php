<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptedService extends Model
{
	public $timestamps = false;
    protected $table = 'opted_services';
    protected $fillable = ['user_id', 'service_id', 'opted_key','opted_value','status','description','request_from','done_by'];
}