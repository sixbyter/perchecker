<?php

namespace Sixbyte\Perchecker\Command;

use Illuminate\Console\Command;
use Perchecker;
use Route;

class PercheckerRoutesyncCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'perchecker:routesync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync routes to db';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $routes    = $this->getRoutes();
        $db_routes = $this->getDbRoutes();

        $routes_key = [];

        foreach ($routes as $key => $route) {
            $routes_key[$key] = $this->getRouteKey($route);
        }
        $_db_routes_key = $db_routes->lists('route_key');
        // 兼容5.0和5.1 的collection
        $db_routes_key = [];
        foreach ($_db_routes_key as $_db_routes_key) {
            $db_routes_key[] = $_db_routes_key;
        }

        $routeModel = Perchecker::getRouteModel();

        // 同步 路由
        foreach ($routes as $route) {
            $route_key = $this->getRouteKey($route);
            if (in_array($route_key, (array) $db_routes_key)) {
                $data = [
                    'status' => 'sync',
                    'name'   => $route['name'],
                ];
                $routeModel->where('route_key', $route_key)->update($data);
                $this->info($route_key . '  update');
            } else {
                $data = [
                    'name'      => $route['name'],
                    'status'    => 'sync',
                    'route_key' => $route_key,
                ];
                $routeModel->insert($data);
                $this->comment($route_key . '  insert');
            }
        }

        // 检查没有同步的路由
        foreach ($db_routes as $db_route) {
            if (!in_array($db_route['route_key'], $routes_key)) {
                $db_route->status = 'missing';
                $db_route->save();
                $this->question($db_route['route_key'] . '  missing');
            }
        }

    }

    protected function getRouteKey($route)
    {
        return $route['host'] . ':' . $route['method'] . ':' . $route['uri'];
    }

    protected function getRoutes()
    {
        $routes  = Route::getRoutes();
        $results = [];

        foreach ($routes as $route) {
            if (null !== $r = $this->getRouteInformation($route)) {
                $results[] = $r;
            }
        }

        return $results;
    }

    protected function getRouteInformation($route)
    {
        return $this->filterRoute([
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $route->middleware(),
            'host'       => $route->domain(),
            'method'     => implode('|', $route->methods()),
        ]);
    }

    protected function filterRoute(array $route)
    {
        $fileter_route_function = config('perchecker.filter_route');
        return $fileter_route_function($route);
    }

    protected function getDbRoutes()
    {
        $routeModel = Perchecker::getRouteModel();
        return $routeModel->get();
    }

}
