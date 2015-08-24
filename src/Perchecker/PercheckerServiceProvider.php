<?php

namespace Sixbyte\Perchecker;

use Illuminate\Support\ServiceProvider;
use Sixbyte\Perchecker\Command\PercheckerRoutesyncCommand;

class PercheckerServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfiguration();
        $this->publishMigrations();
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Perchecker', function ($app) {
            return new Perchecker;
        });

        $this->app->singleton('command.perchecker.routesync', function ($app) {
            return new PercheckerRoutesyncCommand;
        });

        $this->commands('command.perchecker.routesync');

    }

    /**
     * Publish configuration file.
     */
    private function publishConfiguration()
    {
        $this->publishes([__DIR__ . '/../resources/configs/perchecker.php' => config_path('perchecker.php')], 'config');
        $this->mergeConfigFrom(__DIR__ . '/../resources/configs/perchecker.php', 'perchecker');
    }

    /**
     * Publish migration file.
     */
    private function publishMigrations()
    {
        $this->publishes([__DIR__ . '/../resources/migrations/' => base_path('database/migrations')], 'migrations');
    }

}
