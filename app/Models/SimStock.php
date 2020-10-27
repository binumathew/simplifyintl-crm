<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimStock extends Model
{
    protected $table = 'tbl_sim_stock';

    protected $fillable = ['phone_number', 'imsi_number', 'sim_number', 'category', 'box_no','provider'];
    
    public $timestamps = true;

    public function list()
    {
        return $this->hasOne('App\Models\SimList','stock_id','id');
    }

    public function network()
    {
        return $this->hasOne('App\Models\Provider','short_code','provider');
    }
}
