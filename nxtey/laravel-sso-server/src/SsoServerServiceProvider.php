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
        // 1. Enforce strict, short-lived cryptographic tokens
        Passport::tokensExpireIn(now()->addHour());          // Shortened from 1 day to 1 hour for security
        Passport::refreshTokensExpireIn(now()->addDays(14)); // Shortened from 30 days to 14 days
        Passport::personalAccessTokensExpireIn(now()->addDays(7));

        // 2. Enforce strict PKCE (Proof Key for Code Exchange) for your subdomains
        // This stops authorization code interception attacks on subdomains
        Passport::enablePkceProtection();
        
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
            
            $this->publishes([
                __DIR__ . '/../config/sso-server.php' => config_path('sso-server.php'),
            ], 'sso-server-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'sso-server-migrations');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/sso-server'),
            ], 'sso-server-views');
        }

        // Load views from the package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sso-server');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
    }
}
