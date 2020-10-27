<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Options extends Model
{
	public $timestamps = false;
    protected $table = 'options';
    protected $fillable = ['name', 'category', 'value'];
}
