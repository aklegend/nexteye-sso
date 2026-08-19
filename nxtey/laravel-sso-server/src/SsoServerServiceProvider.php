<?php

namespace Nxtey\SsoServer;

use Illuminate\Support\ServiceProvider;
use Nxtey\SsoServer\Console\InstallCommand;

class SsoServerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso-server.php', 'sso-server');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
            
            $this->publishes([
                __DIR__ . '/../config/sso-server.php' => config_path('sso-server.php'),
            ], 'sso-server-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'sso-server-migrations');
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
    }
}