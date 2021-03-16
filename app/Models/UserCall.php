<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use Carbon;
use Helper;

class UserCall extends Model
{
    protected $table = 'user_calls';
    public $timestamps = true;
}
