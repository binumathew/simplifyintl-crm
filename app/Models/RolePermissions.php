<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
    protected $table = 'tbl_role_permissions';

    protected $fillable = ['role_id', 'permission_id', 'can_view_own', 'can_view', 'can_create', 'can_edit','can_delete'];

    public $timestamps = false;
}
