<?php

namespace ITWOC\Providers;

use Illuminate\Support\ServiceProvider;
use ITWOC\ITWOC;

class ITWOCServiceProvider extends ServiceProvider{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind('itwoc', function () {
            return new ITWOC();
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['itwoc'];
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../config/itwoc.php' => config_path('itwoc.php'),
        ]);
    }
}
