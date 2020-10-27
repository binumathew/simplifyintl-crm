<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartList extends Model
{
    protected $table = 'tbl_cart_details';

    protected $fillable = ['cart_id', 'stock_id', 'credit', 'port'];

    public $timestamps = false;

    public function stock()
    {
        return $this->hasOne('App\Models\SimStock','id','stock_id');
    }

}
