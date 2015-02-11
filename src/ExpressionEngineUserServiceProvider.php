<?php

namespace DrawMyAttention\ExpAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ExpressionEngineUserServiceProvider extends ServiceProvider{

    public function boot()
    {
        Auth::extend('ExpressionEngineAuth', function($app)
        {
            $model = $this->app['config']['auth.model'];
            return new ExpressionEngineUserProvider($this->app['hash'], $model);
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
