<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
	protected $table = 'notification_log';

    protected $fillable = ['user_id', 'message', 'description', 'status','payload'];
    
    public $timestamps = true;
}
