<?php

namespace Sixbyte\Perchecker;

use Closure;
use Perchecker;

class PercheckerMiddleware
{

    public function handle($request, Closure $next)
    {
        if ($request->user()->hasRole(config('perchecker.superuser_role'), 'name')) {
            return $next($request);
        }
        $rolename   = $request->route()->getName();
        $routeModel = Perchecker::getRouteModel();
        $route      = $routeModel->where('name', $rolename)->first();
        if (empty($route)) {
            call_user_func(config('perchecker.forbidden_callback'));
        }

        if (!$request->user()->hasPermission($route['permission_id'])) {
            call_user_func(config('perchecker.forbidden_callback'));
        }
        return $next($request);
    }

}
