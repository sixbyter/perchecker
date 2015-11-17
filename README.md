# Perchecker

基于 laravel5.* 的权限管理包

## 特性
参考了 [artesaos/Defender](https://github.com/artesaos/defender) 和其他权限管理包的一些特性,但也有一些跟他们不一样的地方

### 不一样
1. 路由只和权限绑定, 存储在数据库
2. 权限有父级权限的树状结构
3. 简单的管理操作

## 安装

```shell
composer require "sixbyte/perchecker:0.2.*"
```

## 配置

在 `app/config/app.php` 的 `$providers` 和 `$aliases` 数组下分别添加
providers
```php=
'Sixbyte\Perchecker\PercheckerServiceProvider',
```

aliases
```php=
'Perchecker' => 'Sixbyte\Perchecker\PercheckerFacade',
```

设置资源
```shell
php artisan vendor:publish
```

数据迁移
```shell
php artisan migrate
```
路由入库
```shell
php artisan perchecker:routesync
```

Trait 在 app/User.php 添加
```php=
use Sixbyte\Perchecker\HasPermissionTrait;
```

```php=
use ... HasPermissionTrait
```
配置完成



## 使用

#### 在 app\Http\Kernel.php 的 `$routeMiddleware` 数组 注册中间件
```
'perchecker'    => 'Sixbyte\Perchecker\PercheckerMiddleware',
```

#### 在需要权限检查的路由下使用中间件

```php
// 用户登录验证是前提
Route::get('/test', ['middleware' => ['auth', 'perchecker'], 'as' => 'test', function () {
    echo "i have permission";
}]);
```


#### 为用户 `1` 绑定新角色
```php=
# 创建新角色
$role_model = Perchecker::getRoleModel();
$role_id = $role_model->insert(['name'=>'admin','readable_name'=>'超级管理员']);

# 用户绑定新角色 多对多的关系
$user = User::find(1);
$user->roles()->attach($role_id);
```

#### 为角色 `1` 绑定权限
```php=
# 创建权限
$permission_model = Perchecker::getPermissionModel();
$permission_id = $permission_model->insert(['name'=>'user.create']);

# 权限绑定角色 多对多关系
$role_model = Perchecker::getRoleModel();
$role = $role_model->find(1);
$role->permissions()->attach($permission_id);

```

#### 为路由 `1` 绑定权限 同上


#### 配置文件
```php

/**
 * Perchecker - Laravel 5.1 Package
 * Author: liu.sixbyte@gmail.com.
 */
return [

    'role_model'         => 'Sixbyte\Perchecker\Models\Role', // 5.0 style

    'permission_model'   => \Sixbyte\Perchecker\Models\Permission::class,

    'route_model'        => \Sixbyte\Perchecker\Models\Route::class,

    /*
     * Forbidden callback
     */
    'forbidden_callback' => function () {
        header('HTTP/1.0 403 You don\'t have permission to do it!');
        exit('You don\'t have permission to do it!');
    },
    /**
     * route filter function
     */
    'filter_route'       => function ($route) {
        if (in_array('perchecker', $route['middleware'])) {
            return $route;
        }
        return null;
    },
    /*
     * Use template helpers
     */
    'template_helpers'   => true,

    /*
     * Super User role name
     */
    'superuser_role'     => 'superuser',

];
```

#### 是否有此权限, 及权限的父权限 pre_permission_id

: hasPermission($p)
$p 权限的id属性值或者名字属性值

```php=
$user = Auth::user();
$user->hasPermission(1);
$user->hasPermission('user.create');
```
权限的验证方式:
1. 查找用户的所以角色
2. 求出这些角色的权限并集
3. 查找私有权限,和角色权限求并集
3. 检查 权限 是否在并集里存在,存在 `true`, 不存在 `false`

#### 是否有此角色

: hasRole($r)
$p 角色的id属性值或者名字属性值

```php=
$user = Auth::user();
$user->hasRole(1);
$user->hasRole('admin');
```

#### 获取所有权限
用户
```php
$user = Auth::user();
$user->getPermissions();
```

角色
```php
$role_model = Perchecker::getRoleModel();
$role = $role_model->find(1);
$role->permissions()->attach($permission_id);
$role->getPermissions();
```

## 扩展

1. 继承 `Model` 类,修改配置文件

2. 重新编写 中间件, ServiceProvider, 修改注册文件

3. 替换HasPermissionTrait.php