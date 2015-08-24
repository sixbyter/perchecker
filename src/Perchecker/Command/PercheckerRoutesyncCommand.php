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
    protected $description = 'sync routes to db~';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $routes    = $this->getRoutes();
        $db_routes = $this->getDbRoutes();

        $routes_uri = [];

        foreach ($routes as $key => $route) {
            $routes_uri[$key] = $route['uri'];
        }

        $db_routes_uri = $db_routes->lists('uri')->toArray();

        $routeModel = Perchecker::getRouteModel();

        // 同步 路由
        foreach ($routes as $route) {
            if (in_array($route['uri'], $db_routes_uri)) {
                $data = [
                    'name'   => $route['name'],
                    'status' => 'sync',
                ];
                $routeModel::where('uri', $route['uri'])->update($data);
                $this->info($route['uri'] . '  update');
            } else {
                $data = [
                    'name'   => $route['name'],
                    'status' => 'sync',
                    'uri'    => $route['uri'],
                ];
                $routeModel::insert($data);
                $this->comment($route['uri'] . '  insert');
            }
        }

        // 检查没有同步的路由
        foreach ($db_routes as $db_route) {
            if (!in_array($db_route['uri'], $routes_uri)) {
                $db_route->status = 'missing';
                $db_route->save();
                $this->question($db_route['uri'] . '  missing');
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
        return $route;
    }

    protected function getDbRoutes()
    {
        $routeModel = Perchecker::getRouteModel();
        return $routeModel::get();
    }

}
