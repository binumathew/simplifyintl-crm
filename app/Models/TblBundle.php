<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblBundle extends Model
{
    protected $table = 'tbl_bundles';

    public function sim_provider() {
        return $this->hasOne('App\Models\Provider','short_code','provider');
    }
}
