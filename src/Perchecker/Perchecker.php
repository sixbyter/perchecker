<?php

namespace Sixbyte\Perchecker;

use Request;

class Perchecker
{
    protected $role_model;
    protected $route_model;
    protected $permission_model;
    protected $all_permissions_cache;

    public function getRoleModel()
    {
        $model = config('perchecker.role_model');
        return new $model;
    }

    public function getRouteModel()
    {
        $model = config('perchecker.route_model');
        return new $model;
    }

    public function getPermissionModel()
    {
        $model = config('perchecker.permission_model');
        return new $model;
    }

    public function getAllPermissions()
    {
        if (is_null($this->all_permissions_cache)) {
            $this->all_permissions_cache = $this->getPermissionModel()
                ->limit(config('perchecker.permissions_count', 300))
                ->get();
        }
        return $this->all_permissions_cache;
    }

    public function getAuthUser()
    {
        return Request::user();
    }

    /**
     * 检查当前验证登录的用户是否有某个权限
     * @param  [int|string]  $p [权限的id | 权限的name]
     * @return boolean    [description]
     */
    public function hasPermission($p)
    {
        $user = $this->getAuthUser();
        return $user->hasPermission($p);
    }
    /**
     * 检查当前验证登录的用户是否有某个角色
     * @param  [int|string]  $r [角色的id | 角色的name]
     * @return boolean    [description]
     */
    public function hasRole($r)
    {
        $user = $this->getAuthUser();
        return $user->hasRole($r);
    }

}
