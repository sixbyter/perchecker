<?php

namespace Sixbyte\Perchecker;

use Perchecker;

trait HasPermissionTrait
{

    public function roles()
    {
        return $this->belongsToMany(config('perchecker.role_model'), 'user_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(config('perchecker.permission_model'), 'user_permission');
    }

    /**
     * 这个用户是否有某个权限
     * @param  [string|int]  $p 权限name或者id
     * @return boolean
     */
    public function hasPermission($p)
    {
        if ($this->hasRole(config('perchecker.superuser_role'))) {
            return true;
        }
        if (is_string($p)) {
            $type = 'name';
        }
        if (is_integer($p)) {
            $type = 'id';
        }
        if (!isset($type)) {
            throw new \Exception("invalid argument", 1);
        }
        $roles = $this->roles()->with('permissions')->get();
        if (empty($roles)) {
            return false;
        }
        $all_permissions = Perchecker::getPermissionModel()->get(['id', 'name', 'pre_permission_id']);
        $permissions     = [];
        foreach ($roles as $role) {
            if (!empty($role['permissions'])) {
                foreach ($role['permissions'] as $role_permission) {
                    $pre_permission                       = $all_permissions->where('id', $role_permission['pre_permission_id'])->first();
                    $permissions[$role_permission[$type]] = $pre_permission[$type];
                }
            }
        }

        // 检查私有权限
        $private_permissions = $this->permissions;
        foreach ($private_permissions as $private_permission) {
            $pre_permission                          = $all_permissions->where('id', $private_permission['pre_permission_id'])->first();
            $permissions[$private_permission[$type]] = $pre_permission[$type];
        }

        // 递归检查是否拥有父权限
        $checker = function ($need, $permissions) use (&$checker) {
            if (array_key_exists($need, $permissions)) {
                if ($permissions[$need] !== null) {
                    return $checker($permissions[$need], $permissions);
                } else {
                    return true;
                }
            }
            return false;
        };
        return $checker($p, $permissions);
    }

    public function IsPrivatePermission($p)
    {
        $permission_table = Perchecker::getPermissionModel()->getTable();
        if (is_string($p)) {
            $type = $permission_table . '.name';
        }
        if (is_integer($p)) {
            $type = $permission_table . '.id';
        }
        if (!isset($type)) {
            throw new \Exception("invalid argument", 1);
        }

        $permission = $this->permissions()->where($type, $p)->first();

        if (empty($permission)) {
            return false;
        }
        return true;
    }

    public function permissionFrom($p)
    {
        $permission_table = Perchecker::getPermissionModel()->getTable();
        if (is_string($p)) {
            $type = $permission_table . '.name';
        }
        if (is_integer($p)) {
            $type = $permission_table . '.id';
        }
        if (!isset($type)) {
            throw new \Exception("invalid argument", 1);
        }

        $role_flag = false;
        $roles     = $this->roles()->with('permissions')->get();
        if (empty($roles)) {
            $role_flag = false;
        } else {
            foreach ($roles as $role) {
                if ($role->hasPermission($p)) {
                    $role_flag = true;
                    break;
                }
            }
        }

        if ($role_flag === true) {
            return 'role';
        } else {
            $permission = $this->permissions()->where($type, $p)->first();

            if (empty($permission)) {
                return false;
            }
            return 'private';
        }

    }

    /**
     * 这个用户是否有某个角色
     * @param  [type]  $r 角色name或者id
     * @return boolean
     */
    public function hasRole($r)
    {
        $role_table = Perchecker::getRoleModel()->getTable();
        if (is_string($r)) {
            $type = $role_table . '.name';
        }
        if (is_integer($r)) {
            $type = $role_table . '.id';
        }
        if (!isset($type)) {
            throw new \Exception("invalid argument", 1);
        }
        $roles = $this->roles()->where($type, $r)->first();
        if (empty($roles)) {
            return false;
        }
        return true;
    }

    /**
     * 这个用户是否可以访问某个路由
     * @param  [type] $r 路由名
     * @return boolean
     */
    public function canRoute($r)
    {
        if ($this->hasRole(config('perchecker.superuser_role'))) {
            return true;
        }
        $routeModel = Perchecker::getRouteModel();
        $route      = $routeModel->where('route_key', $r)->first();
        if (empty($route)) {
            return false;
        }
        if (!$this->hasPermission($route['permission_id'])) {
            return false;
        }
        return true;
    }

}
