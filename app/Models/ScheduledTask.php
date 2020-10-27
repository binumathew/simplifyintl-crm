<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    protected $fillable = ['description', 'command', 'next_run', 'run_time', 'status'];
}
