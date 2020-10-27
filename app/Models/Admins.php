<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admins extends Model
{
    protected $table = 'admins';

    protected $fillable = ['first_name', 'last_name', 'email','parent_id'];

    public function parent()
	{
        return $this->belongsTo(Admins::class, 'parent_id')->with('parent');
	}
	public function ascendings()
    {
        $ascendings = collect();

        $user = $this;

        while($user->parent) {
            $ascendings->push($user->parent->id);

            if ($user->parent) {
                $user = $user->parent;
            }
        }

        return $ascendings;
    }
    public $timestamps = true;

    public function roles()
    {
        return $this->hasOne('App\Models\Roles','id','role');
    }
}
