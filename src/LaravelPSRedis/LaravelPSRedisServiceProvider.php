<?php

namespace LaravelPSRedis;

use Illuminate\Config\Repository;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider;

class LaravelPSRedisServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    public function boot()
    {
        $this->app->configure('redis');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'redis',
            function () {
                /** @var Repository $config */
                $config = $this->app->make('config');
                $driver = new Driver($config->get('redis'));

                return new RedisManager('predis', $driver->getConfig());
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['redis'];
    }

}
