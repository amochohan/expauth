<?php

namespace DrawMyAttention\ExpAuth;

class ExpressionEngineHasherServiceProvider extends \Illuminate\Hashing\HashServiceProvider{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hash', function() { return new \DrawMyAttention\ExpAuth\ExpressionEngineHasher; });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('hash');
    }

}
