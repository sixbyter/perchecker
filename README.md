# Perchecker

基于 laravel5.1 的权限管理包

## 安装

```shell
composer require "sixbyte/perchecker:dev-master"
```

## 配置

在 `app/config/app.php` 的 `$providers` 和 `$aliases` 数组下分别添加
providers
```php=
Sixbyte\Perchecker\PercheckerServiceProvider::class,
```

aliases
```php=
'Perchecker'        => Sixbyte\Perchecker\PercheckerFacade::class,
```

在 app\Http\Kernel.php 的 `$routeMiddleware` 数组 注册中间件
```
'perchecker'    => \Sixbyte\Perchecker\PercheckerMiddleware::class,
```

在需要权限检查的路由或者控制器下使用中间件
```php=
$this->middleware('perchecker');
```

设置资源
```shell
php artisan vendor:publish
```

数据迁移
```shell
php artisan migrate
```

配置完成