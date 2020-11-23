<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;



class UserHistory extends Model
{

    protected $table = 'usage_history_copy';
    public $timestamps = true;
}
