<?php

namespace DrawMyAttention\ExpAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ExpressionEngineUserServiceProvider extends ServiceProvider{

    public function boot()
    {
        Auth::provider('ExpressionEngineAuth', function($app, array $config)
        {
            return new ExpressionEngineUserProvider($this->app['hash'], $config['model']);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
    }
}
