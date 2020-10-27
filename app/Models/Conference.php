<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    protected $table = 'conference_create';
    
    protected $fillable = ['user_id', 'country_id', 'inc_id', 'serviceno', 'sendcli', 'bridge_id', 'audioconfid', 'confname', 'contact_type', 'group_id', 'conf_settings', 'notifyvalidityinmin', 'description', 'maxmembers', 'schdtype', 'schdreminmincsv', 'filetype', 'msisdnlist', 'msisdn_data', 'est_amount', 'startdatetime', 'enddatetime', 'selectedweekdays', 'sendnow', 'chairperson_pin', 'participant_pin', 'identifier','status'];

    public $timestamps = true;

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }
    
}