# Perchecker

基于 laravel5.* 的权限管理包

## 特性
参考了 [artesaos/Defender](https://github.com/artesaos/defender) 和其他权限管理包的一些特性,但也有一些跟他们不一样的地方

### 不一样
1. 路由->用户->角色->权限 单链查询
2. 路由只和权限绑定, 存储在数据库
3. 权限有父级权限的树状结构
5. 简单的管理操作

## 安装

```shell
composer require "sixbyte/perchecker:dev-master"
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

#### 在需要权限检查的路由或者控制器下使用中间件
```php=
$this->middleware('auth'); // 用户登录验证是前提
$this->middleware('perchecker');
```

```php
// 用户登录验证是前提
Route::get('/test', ['middleware' => ['auth', 'perchecker'], 'as' => 'test', function () {
    echo "i have permission";
}]);
```

#### 注册所有 `有路由名` 的路由
```shell
php artisan perchecker:routesync
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

return [

    'role_model'         => 'Sixbyte\Perchecker\Models\Role', // 5.0的写法

    'permission_model'   => \Sixbyte\Perchecker\Models\Permission::class, // 5.1的写法

    'route_model'        => \Sixbyte\Perchecker\Models\Route::class,

    /*
     * 没有权限的时候的回调函数
     */
    'forbidden_callback' => function () {
        header('HTTP/1.0 403 You don\'t have permission to do it!');
        exit('You don\'t have permission to do it!');
    },

    /*
     * 超级角色
     */
    'superuser_role'     => 'superuser',

];
```

#### 是否有此权限, 及权限的父权限 pre_permission_id

: hasPermission($p,$type='id')
$p 权限的id属性值或者名字属性值
$type 权限属性的类型,id或者name

```php=
$user = Auth::user();
$user->hasPermission(1);
$user->hasPermission('user.create','name');
```
权限的验证方式:
1. 查找用户的所以角色
2. 求出这些角色的权限并集
3. 检查 权限 是否在并集里存在,存在 `true`, 不存在 `false`

#### 是否有此角色

: hasRole($r)
$p 角色的id属性值或者名字属性值

```php=
$user = Auth::user();
$user->hasRole(1);
$user->hasRole('admin');
```

## 扩展

1. 继承 `Model` 类,修改配置文件

2. 重新编写 中间件, ServiceProvider, 修改注册文件

3. 替换HasPermissionTrait.php