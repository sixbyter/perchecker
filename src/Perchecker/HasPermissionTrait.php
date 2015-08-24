<?php

namespace Sixbyte\Perchecker;

use Perchecker;

trait HasPermissionTrait
{

    public function roles()
    {
        return $this->belongsToMany('Sixbyte\Perchecker\Models\Role', 'user_role');
    }

    public function hasPermission($p, $type = 'id')
    {
        if ($type === 'id' || $type === 'name') {
            $permissions = [];
            $roles       = $this->roles()->with('permissions')->get();
            if (empty($roles)) {
                return false;
            }
            foreach ($roles as $role) {
                if (!empty($role['permissions'])) {
                    foreach ($role['permissions'] as $permission) {
                        $permissions[$permission[$type]] = $permission['pre_permission_id'];
                    }
                }
            }
            if (isset($permissions[$p])) {
                if ($permissions[$p] !== 0) {
                    if (isset($permissions[$permissions[$p]])) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
            return false;
        } else {
            throw new \Exception("value type 非法", 1);

        }
    }

    public function hasRole($r, $type = 'id')
    {
        if ($type === 'id' || $type === 'name') {
            $roles = $this->roles()->where($type, $r)->first();
            if (empty($roles)) {
                return false;
            }
            return true;
        } else {
            throw new \Exception("value type 非法", 1);

        }
    }

}
