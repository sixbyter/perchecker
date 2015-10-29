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

        $routes_name = [];

        foreach ($routes as $key => $route) {
            if (empty($route['name'])) {
                unset($routes[$key]);
                continue;
            }
            $routes_name[$key] = $route['name'];
        }

        $db_routes_name = $db_routes->lists('name');

        $routeModel = Perchecker::getRouteModel();

        // 同步 路由
        foreach ($routes as $route) {
            if (in_array($route['name'], (array) $db_routes_name)) {
                $data = [
                    'uri'    => $route['uri'],
                    'status' => 'sync',
                ];
                $routeModel->where('name', $route['name'])->update($data);
                $this->info($route['name'] . '  update');
            } else {
                $data = [
                    'name'   => $route['name'],
                    'status' => 'sync',
                    'uri'    => $route['uri'],
                ];
                $routeModel->insert($data);
                $this->comment($route['name'] . '  insert');
            }
        }

        // 检查没有同步的路由
        foreach ($db_routes as $db_route) {
            if (!in_array($db_route['name'], $routes_name)) {
                $db_route->status = 'missing';
                $db_route->save();
                $this->question($db_route['name'] . '  missing');
            }
        }

    }

    protected function getRoutes()
    {
        $routes  = Route::getRoutes();
        $results = [];

        foreach ($routes as $route) {
            $results[] = $this->getRouteInformation($route);
        }

        return $results;
    }

    protected function getRouteInformation($route)
    {
        return $this->filterRoute([
            'uri'  => $route->uri(),
            'name' => $route->getName(),
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
