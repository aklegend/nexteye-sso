<?php

namespace Nxtey\SsoClient;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class SsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nxtey-sso.php', 'nxtey-sso'
        );
    }

    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/nxtey-sso.php' => config_path('nxtey-sso.php'),
        ], 'nxtey-sso-config');

        // Register Custom Socialite Driver
        Socialite::extend('nxtey', function ($app) {
            $config = $app['config']['nxtey-sso'];
            
            return Socialite::buildProvider(
                NxteySocialiteProvider::class,
                [
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect' => $config['redirect_uri'],
                ]
            );
        });
        if ($this->app->runningInConsole()) {
        $this->commands([\Nxtey\SsoClient\Console\InstallCommand::class]);
        }
        // Load Package Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
