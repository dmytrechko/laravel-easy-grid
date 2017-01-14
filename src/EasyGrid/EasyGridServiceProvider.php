<?php

namespace EasyGrid\EasyGrid;

use Illuminate\Support\ServiceProvider;

class EasyGridServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/easygrid.php' => config_path('easygrid.php'),
        ]);
    }
    
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/easygrid.php', 'easygrid'
        );
        
        $this->app->bind('easygrid', function ($app) {
            return new Grid();
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'easygrid',
        ];
    }
}
