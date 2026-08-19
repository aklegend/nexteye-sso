<?php

namespace Nxtey\SsoClient;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Nxtey\SsoClient\Console\InstallCommand;

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
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/nxtey-sso.php' => config_path('nxtey-sso.php'),
        ], 'nxtey-sso-config');

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

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}