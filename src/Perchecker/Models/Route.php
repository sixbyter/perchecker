<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;
use Perchecker;

class Route extends Model
{
    protected $table = 'routes';

    protected $fillable = ['name', 'readable_name', 'permission_id', 'route_key'];

    public $timestamps = false;

    public function permission()
    {
        return $this->belongsTo(config('perchecker.permission_model'));
    }

    public function getValidateRules()
    {
        $permissions_table = Perchecker::getPermissionModel()->getTable();
        return $rules      = [
            'permission_id' => 'required|exists:' . $permissions_table . ',id',
            'readable_name' => 'required',
        ];
    }

}
