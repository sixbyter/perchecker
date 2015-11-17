<?php

namespace Sixbyte\Perchecker;

use Closure;
use Request;

class Perchecker
{
    protected $role_model;
    protected $route_model;
    protected $permission_model;

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

    /**
     * 递归对权限的建立树结构
     * @param  [type] $permissions [description]
     * @return [type]              [description]
     */
    public function treePermissions(Closure $callback)
    {

        $permissions = $this->getPermissionModel()->where('pre_permission_id', 0)->get();
        if (empty($permissions)) {
            return [];
        }

        // 匿名函数遍历
        $ergodicer = function ($permissions) use (&$ergodicer, $callback) {
            $tree = [];
            foreach ($permissions as $key => $permission) {
                $tree[$key] = call_user_func($callback, $permission);
                $sons       = $permission->sons;
                if (!empty($sons)) {
                    $nodes = $ergodicer($sons);
                    if (!empty($nodes)) {
                        $tree[$key]['nodes'] = $nodes;
                    }
                }
            }
            return $tree;
        };

        return $ergodicer($permissions);
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
