<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'pre_permission_id', 'readable_name'];

    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany(config('perchecker.role_model'), 'role_permission');
    }

    public function routes()
    {
        return $this->hasMany(config('perchecker.route_model'));
    }

    public function prePermission()
    {
        return $this->belongsTo(config('perchecker.permission_model'));
    }

    public function sons()
    {
        return $this->hasMany(config('perchecker.permission_model'), 'pre_permission_id', 'id');
    }

    public function getValidateRules()
    {
        if (empty($this->id)) {
            return $rules = [
                'name'              => 'required|unique:' . $this->table . ',name',
                'readable_name'     => 'required',
                'pre_permission_id' => 'required',
            ];
        } else {
            return $rules = [
                'name'              => 'required|unique:' . $this->table . ',name,' . $this->id,
                'readable_name'     => 'required',
                'pre_permission_id' => 'required',
            ];
        }
    }

}
