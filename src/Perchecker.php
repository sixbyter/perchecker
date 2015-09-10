<?php

namespace Sixbyte\Perchecker;

class Perchecker
{
    protected $role_model       = config('perchecker.role_model');
    protected $route_model      = config('perchecker.route_model');
    protected $permission_model = config('perchecker.permission_model');

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
