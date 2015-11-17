<?php

namespace Sixbyte\Perchecker;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
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
     * 注册Perchecker 提供者
     *
     * 注册 Perchecker 映射
     * 注册路由同步命令
     * 注册Blade模板扩展
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

        $this->registerBladeExtensions();

    }

    protected function registerBladeExtensions()
    {
        if (false === $this->app['config']->get('perchecker.template_helpers', true)) {
            return;
        }
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            // @percheckcan
            $bladeCompiler->directive('percheckcan', function ($expression) {
                return "<?php if(app('Perchecker')->hasPermission{$expression}): ?>";
            });
            $bladeCompiler->directive('endpercheckcan', function ($expression) {
                return '<?php endif; ?>';
            });
            // @percheckis
            $bladeCompiler->directive('percheckis', function ($expression) {
                return "<?php if(app('Perchecker')->hasRole{$expression}): ?>";
            });
            $bladeCompiler->directive('endpercheckis', function ($expression) {
                return '<?php endif; ?>';
            });
        });
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
