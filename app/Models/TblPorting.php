<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblPorting extends Model
{
	protected $table = 'tbl_porting';

	protected $fillable = ['stock_id', 'pac_number', 'porting_to', 'reference_id', 'expected_date', 'status', 'description', 'promocode', 'provider', 'staff_id'];

    public function stock()
    {
        return $this->hasOne('App\Models\SimStock','id','stock_id');
    }

    public function list() 
    {
        return $this->hasOne('App\Models\SimList','stock_id','stock_id');
    }
}
