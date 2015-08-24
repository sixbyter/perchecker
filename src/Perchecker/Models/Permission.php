<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'pre_permission_id'];

    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany('Sixbyte\Perchecker\Models\Role', 'role_permission');
    }

    public function rotes()
    {
        return $this->hasMany('Sixbyte\Perchecker\Models\Route');
    }

    public function scopeGroup($query)
    {

        return $query->groupBy('pre_permission_id');
    }

}
