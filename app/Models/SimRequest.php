<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimRequest extends Model
{
    protected $table = 'tbl_sim_request';
    
    public $timestamps = true;

    public function list()
    {
        return $this->hasMany('App\Models\SimList', 'request_id','id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function getSimList()
    {
        $list = "";
        $sim = $this->list()->get();
        if ($sim) {
            foreach ($sim as $simList) {
                if($simList->stock->verified == 0){ 
                //$simList->reg_status!=2 && ($simList->stock->provider=='O2' || $simList->stock->provider=='VUK')
                    $list .= '0759xxxxxxx'.' , ';
                }else{
                    $list .= '0'.ltrim($simList->stock->phone_number,'44').' , ';
                }                
            }
        }
        return trim($list,' , ');
    }

    public function getShippingAddress()
    {
        $address = json_decode($this->shipping_address);
        return $address->street.' , '.$address->city.' , '.$address->country.' , '.$address->postal_code;
    }

    public function payment()
    {
        return $this->hasOne('App\Models\UserPayment','id','payment_id');
    }
}
