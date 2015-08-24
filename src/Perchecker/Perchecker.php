<?php

namespace Sixbyte\Perchecker;

class Perchecker
{
    protected $role_model       = \Sixbyte\Perchecker\Models\Role::class;
    protected $route_model      = \Sixbyte\Perchecker\Models\Route::class;
    protected $permission_model = \Sixbyte\Perchecker\Models\Permission::class;

    public function getRoleModel()
    {
        return $this->$role_model;
    }

    public function getRouteModel()
    {
        return $this->route_model;
    }

    public function getPermissionModel()
    {
        return $this->permission_model;
    }

}
