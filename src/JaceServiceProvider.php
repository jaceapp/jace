<?php

namespace JaceApp\Jace;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use JaceApp\Jace\Console\Commands\InstallCommand;
use JaceApp\Jace\Console\Commands\RunSeederCommand;
use JaceApp\Jace\Providers\EventServiceProvider;
use Illuminate\Routing\Router;
use JaceApp\Jace\Middleware\AccessRestrictedForBanned;
use JaceApp\Jace\Middleware\ChannelVisibility;

class JaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register middlewares
        $this->registerMiddleware($this->app->router);

        // Load routes
        if (config('jace.api')) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        } else {
            $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        }

        // Load commands
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Register all commands
        $this->commands([
            InstallCommand::class,
            RunSeederCommand::class,
        ]);
    }

    public function register()
    {
        $this->app->register(PermissionServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register all the middlewares necessary
     *
     * @param Router $router
     * @return void
     */
    private function registerMiddleware(Router $router)
    {
        $router->aliasMiddleware('check.banned', AccessRestrictedForBanned::class);
        $router->aliasMiddleware('check.channel.visibility', ChannelVisibility::class);
    }
}
