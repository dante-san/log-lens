<?php

namespace Laxmidhar\LogLens;

use Illuminate\Support\ServiceProvider;

class LogLensServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/loglens.php',
            'loglens'
        );
    }

    public function boot()
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'loglens'
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/loglens'),
            ], 'loglens-assets');

            $this->publishes([
                __DIR__ . '/config/loglens.php' => config_path('loglens.php'),
            ], 'loglens-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/loglens'),
            ], 'loglens-views');
        }
    }
}
