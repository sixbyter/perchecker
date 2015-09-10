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
        return $this->belongsToMany(config('perchecker.role_model'), 'role_permission');
    }

    public function routes()
    {
        return $this->hasMany(config('perchecker.route_model'));
    }

    public function scopeGroup($query)
    {

        return $query->groupBy('pre_permission_id');
    }

}
