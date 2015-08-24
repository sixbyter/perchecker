<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name'];
    public $timestamps  = false;

    public function permissions()
    {
        return $this->belongsToMany('Sixbyte\Perchecker\Models\Permission', 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_role');
    }

}
