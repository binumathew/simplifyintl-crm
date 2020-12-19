<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    protected $table = 'users';
    
    protected $fillable = ['name', 'first_name', 'last_name','username', 'phone', 'email', 'password', 'country_id', 'parent_id', 'stock_id', 'switch_id', 'time_zone', 'status'];

    public $timestamps = true;

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    public function country()
    {
        return $this->hasOne('App\Models\Country','id','country_id');
    }

    public function balance()
    {
        return $this->hasOne('App\Models\Account','user_id','id');
    }

    public function trusted()
    {
        return $this->hasMany('App\Models\TrustedNumber','user_id','id');
    }

    public function cards($gateway = '')
    {
        if($gateway)
            return $this->hasMany('App\Models\UserCreditCard','user_id','id')->where('gateway', '=', $gateway);
        else
           return $this->hasMany('App\Models\UserCreditCard','user_id','id');
    }

    public function msisdn()
    {
        return $this->hasOne('App\Models\SimStock','id','stock_id');
    }
    
    public function userDetail()
    {
        return $this->hasOne('App\Models\UserData','user_id','id');
    }

    public function userPlan()
    {
        $userPlan = UserPlan::where('status',1)->where('user_id',$this->id)->first();
        if (!$userPlan) {
            return[
                'plan' => '-',
                'expiry' => '-'
            ];
            
        } else {
            return[
                'plan' => $userPlan->plan->plan_name,
                'expiry' => date('d-M-Y',strtotime($userPlan->created_at."+30 days"))
            ];
        }
    }

    public function order()
    {
        return $this->hasOne('App\Models\SimRequest','user_id','id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function recharge()
    {
        return $this->hasMany('App\Models\AutoRecharge','user_id','id');
    }
}
