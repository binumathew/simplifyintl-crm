<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblPlan extends Model
{
    protected $table = 'tbl_plans';

    public function sim_provider() {
        return $this->hasOne('App\Models\Provider','short_code','provider');
    }

    public function network()
    {
        return $this->hasOne('App\Models\Provider','short_code','provider');
    }
}
