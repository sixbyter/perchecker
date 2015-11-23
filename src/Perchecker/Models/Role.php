<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;
use Perchecker;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'readable_name'];

    public $timestamps = false;

    protected $permissionsCache = null;

    public function permissions()
    {
        return $this->belongsToMany(config('perchecker.permission_model'), 'role_permission');
    }

    public function users()
    {
        return $this->belongsToMany(config('perchecker.user_model'), 'user_role');
    }

    /**
     * 这个角色是否拥有某个权限
     * @param  [int|string]  $p  [权限的name或者id]
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
        $permissions = $this->getPermissions();

        $permissions_type = array_column($permissions, 'can', $type);
        return $permissions_type[$p];
    }

    /**
     * 获取权限列表
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getPermissions()
    {
        if (is_null($this->permissionsCache)) {
            $permissions       = [];
            $permissions_model = Perchecker::getAllPermissions();
            $role_permissions  = $this['permissions'];

            foreach ($permissions_model as $permission_model) {
                $permission = [
                    'id'                => $permission_model['id'],
                    'name'              => $permission_model['name'],
                    'readable_name'     => $permission_model['readable_name'],
                    'pre_permission_id' => $permission_model['pre_permission_id'],
                    'can'               => false,
                ];
                if ($this->name == config('perchecker.superuser_role')) {
                    $permission['can'] = true;
                } else {
                    // 设置深度为 10 避开递归
                    $flag = false;
                    for ($i = 0; $i < 10; $i++) {
                        if ($role_permissions->where('id', $permission_model['id'])->first()) {
                            if ($permission_model['pre_permission_id'] === 0) {
                                $flag = true;
                                break;
                            } else {
                                $permission_model = $permissions_model->where('id', $permission_model['pre_permission_id'])->first();
                            }
                        } else {
                            $flag = false;
                            break;
                        }
                    }
                    $permission['can'] = $flag;
                }

                $permissions[] = $permission;
            }
            $this->permissionsCache = $permissions;
        }

        return $this->permissionsCache;
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
