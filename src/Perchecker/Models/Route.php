<?php

namespace Sixbyte\Perchecker\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'routes';

    protected $fillable = ['name', 'readable_name', 'permission_id', 'uri'];

    public $timestamps = false;

    public function permission()
    {
        return $this->belongsTo(config('perchecker.permission_model'));
    }

}
