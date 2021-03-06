<?php

namespace Sixbyte\Perchecker;

use Perchecker;

trait HasPermissionTrait
{

    /**
     * 权限的缓存,保证一次请求只需要求一次用户的权限列表,减少查询数据库的次数.
     * @var null
     */
    protected $permissions_cache = null;

    protected $roles_cache = null;

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

        $permissions      = $this->getPermissions();
        $permissions_type = array_column($permissions, 'can', $type);
        if (isset($permissions_type[$p])) {
            return $permissions_type[$p];
        }
        return false;
    }

    public function getPermissions()
    {
        if (is_null($this->permissions_cache)) {
            $roles_permissions = [];
            // 合并
            $roles = $this->getRoles();
            // 有角色
            if ($roles->count() > 0) {
                foreach ($roles as $role) {
                    $role_permissions = $role->getPermissions();
                    foreach ($role_permissions as $key => $role_permission) {
                        if (isset($roles_permissions[$key])) {
                            if ($role_permission['can'] === true) {
                                $roles_permissions[$key] = $role_permission;
                            }
                        } else {
                            $roles_permissions[$key] = $role_permission;
                        }
                        $roles_permissions[$key]['from'] = 'role';
                    }
                }
                $private_permissions = $this['permissions'];
                foreach ($roles_permissions as $key => $roles_permission) {
                    $private_permission = $private_permissions->where('id', $roles_permission['id'])->first();
                    if ($private_permission) {
                        $roles_permissions[$key]['can']  = true;
                        $roles_permissions[$key]['from'] = 'private';
                    }
                }
            } else {
                $private_permissions = $this['permissions'];
                foreach ($private_permissions as $key => $private_permission) {
                    $roles_permissions[] = [
                        'id'                => $private_permission['id'],
                        'name'              => $private_permission['name'],
                        'readable_name'     => $private_permission['readable_name'],
                        'pre_permission_id' => $private_permission['pre_permission_id'],
                        'can'               => true,
                    ];
                }
            }

            // 递归检查
            foreach ($roles_permissions as $key => $roles_permission) {
                $roles_permissions[$key]['can'] = $this->checkPermission($roles_permission, $roles_permissions);
            }
            $this->permissions_cache = $roles_permissions;
        }

        return $this->permissions_cache;
    }

    /**
     * 递归检查父级权限是否为true
     * @param  [array] $roles_permission  [description]
     * @param  [array] $roles_permissions [description]
     * @return [boolen]                    [description]
     */
    protected function checkPermission($roles_permission, $roles_permissions)
    {
        // 这里的递归避不开, 有大牛可以避开请一定要告诉
        if ($roles_permission['pre_permission_id'] === 0) {
            return $roles_permission['can'];
        } else {
            $pre_roles_permission = array_filter($roles_permissions, function ($var) use ($roles_permission) {
                return ($var['id'] == $roles_permission['pre_permission_id']);
            });
            $pre_roles_permission = array_pop($pre_roles_permission);
            if ($this->checkPermission($pre_roles_permission, $roles_permissions)) {
                return $roles_permission['can'];
            }
            return false;
        }
    }

    /**
     * 这个用户是否有某个角色
     * @param  [type]  $r 角色name或者id
     * @return boolean
     */
    public function hasRole($r)
    {

        if (is_string($r)) {
            $type = 'name';
        }
        if (is_integer($r)) {
            $type = 'id';
        }
        if (!isset($type)) {
            throw new \Exception("invalid argument", 1);
        }

        $type = $type;

        $roles = $this->getRoles();
        if (empty($roles)) {
            return false;
        }
        $role = $roles->where($type, $r)->first();
        if (empty($role)) {
            return false;
        }
        return true;
    }

    public function getRoles()
    {
        if ($this->roles_cache === null) {
            $this->roles_cache = $this->roles()->get();
        }
        return $this->roles_cache;
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
        // 路由没有设置权限, 默认为允许访问
        if (empty($route['permission_id'])) {
            return true;
        }
        if (!$this->hasPermission($route['permission_id'])) {
            return false;
        }
        return true;
    }

}
