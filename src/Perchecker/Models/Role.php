<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;
use Perchecker;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'readable_name'];

    public $timestamps = false;

    public function permissions()
    {
        return $this->belongsToMany(config('perchecker.permission_model'), 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_role');
    }

    /**
     * 这个角色是否拥有某个权限
     * @param  [int|string]  $p     权限的name或者id
     * @return boolean        [description]
     */
    public function hasPermission($p)
    {
        if ($this->name == config('perchecker.superuser_role')) {
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

        $permissions     = [];
        $all_permissions = Perchecker::getPermissionModel()->get(['id', 'name', 'pre_permission_id']);
        if (!empty($this['permissions'])) {
            foreach ($this['permissions'] as $role_permission) {
                $pre_permission                       = $all_permissions->where('id', $role_permission['pre_permission_id'])->first();
                $permissions[$role_permission[$type]] = $pre_permission[$type];
            }
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

    public function getValidateRules()
    {
        if (empty($this->id)) {
            return $rules = [
                'name'          => 'required|unique:' . $this->table . ',name',
                'readable_name' => 'required',
            ];
        } else {
            return $rules = [
                'name'          => 'required|unique:' . $this->table . ',name,' . $this->id,
                'readable_name' => 'required',
            ];
        }

    }

}
