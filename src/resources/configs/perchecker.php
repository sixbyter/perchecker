<?php

/**
 * Perchecker - Laravel 5.1 Package
 * Author: liu.sixbyte@gmail.com.
 */
return [

    'role_model'         => \Sixbyte\Perchecker\Models\Role::class,

    'permission_model'   => \Sixbyte\Perchecker\Models\Permission::class,

    'route_model'        => \Sixbyte\Perchecker\Models\Route::class,

    /*
     * Forbidden callback
     */
    'forbidden_callback' => function () {
        header('HTTP/1.0 403 You don\'t have permission to do it!');
        exit('You don\'t have permission to do it!');
    },

    /*
     * Use template helpers
     */
    // 'template_helpers'   => true,

    /*
     * Super User role name
     */
    'superuser_role'     => 'superuser',

];
