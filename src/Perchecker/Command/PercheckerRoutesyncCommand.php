<?php

namespace Sixbyte\Perchecker\Command;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    public function __construct()
    {
        parent::__construct();

        $this->router = app('router');
        $this->routes = $this->router->getRoutes();
    }

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
            'middleware' => $this->getMiddleware($route),
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

    /**
     * Get before filters.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        $middlewares = array_values($route->middleware());

        $middlewares = array_unique(
            array_merge($middlewares, $this->getPatternFilters($route))
        );

        $actionName = $route->getActionName();

        if (!empty($actionName) && $actionName !== 'Closure') {
            $middlewares = array_merge($middlewares, $this->getControllerMiddleware($actionName));
        }
        return $middlewares;
    }

    /**
     * Get the middleware for the given Controller@action name.
     *
     * @param  string  $actionName
     * @return array
     */
    protected function getControllerMiddleware($actionName)
    {
        Controller::setRouter($this->laravel['router']);

        $segments = explode('@', $actionName);

        return $this->getControllerMiddlewareFromInstance(
            $this->laravel->make($segments[0]), $segments[1]
        );
    }

    /**
     * Get all of the pattern filters matching the route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getPatternFilters($route)
    {
        $patterns = [];

        foreach ($route->methods() as $method) {
            // For each method supported by the route we will need to gather up the patterned
            // filters for that method. We will then merge these in with the other filters
            // we have already gathered up then return them back out to these consumers.
            $inner = $this->getMethodPatterns($route->uri(), $method);

            $patterns = array_merge($patterns, array_keys($inner));
        }

        return $patterns;
    }

    /**
     * Get the pattern filters for a given URI and method.
     *
     * @param  string  $uri
     * @param  string  $method
     * @return array
     */
    protected function getMethodPatterns($uri, $method)
    {
        return $this->router->findPatternFilters(
            Request::create($uri, $method)
        );
    }

    /**
     * Get the middlewares for the given controller instance and method.
     *
     * @param  \Illuminate\Routing\Controller  $controller
     * @param  string  $method
     * @return array
     */
    protected function getControllerMiddlewareFromInstance($controller, $method)
    {
        $middleware = $this->router->getMiddleware();

        $results = [];

        foreach ($controller->getMiddleware() as $name => $options) {
            if (!$this->methodExcludedByOptions($method, $options)) {
                $results[] = Arr::get($middleware, $name, $name);
            }
        }

        return $results;
    }

    /**
     * Determine if the given options exclude a particular method.
     *
     * @param  string  $method
     * @param  array  $options
     * @return bool
     */
    protected function methodExcludedByOptions($method, array $options)
    {
        return (!empty($options['only']) && !in_array($method, (array) $options['only'])) ||
            (!empty($options['except']) && in_array($method, (array) $options['except']));
    }

}
