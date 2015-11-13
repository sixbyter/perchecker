<?php

namespace Sixbyte\Perchecker;

use Closure;
use Perchecker;

/**
 * 权限验证的中间件,请放在auth中间件后面使用
 * 验证用户有没有访问此路由的权限
 * 路由名->路由权限->用户角色是否拥有此权限
 */
class PercheckerMiddleware
{

    public function handle($request, Closure $next)
    {
        $role_key = $this->getRouteKey($request->route());
        if (!$request->user()->canRoute($role_key)) {
            call_user_func(config('perchecker.forbidden_callback'));
        }
        return $next($request);
    }

    protected function getRouteKey($route)
    {
        return $route->domain() . ':' . implode('|', $route->methods()) . ':' . $route->uri();
    }

}
