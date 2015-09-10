<?php

namespace Sixbyte\Perchecker;

class Perchecker
{
    protected $role_model       = config('perchecker.role_model');
    protected $route_model      = config('perchecker.route_model');
    protected $permission_model = config('perchecker.permission_model');

    public function getRoleModel()
    {
        $model = $this->$role_model;
        return new $model;
    }

    public function getRouteModel()
    {
        $model = $this->route_model;
        return new $model;
    }

    public function getPermissionModel()
    {
        $model = $this->permission_model;
        return new $model;
    }

}
