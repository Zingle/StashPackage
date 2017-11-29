<?php

namespace ZingleCom\Stash;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Stash\DriverList;
use Stash\Interfaces\PoolInterface;
use ZingleCom\Stash\Driver\Predis;

/**
 * Class StashServiceProvider
 */
class StashServiceProvider extends ServiceProvider
{
    /**
     * Boot
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/stash.php' => config_path('stash.php'),
        ], 'config');
    }

    /**
     * Register container bindings
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/stash.php';
        $this->mergeConfigFrom($configPath, 'stash');

        $this
            ->registerAdditionalDrivers()
            ->registerCacheManager()
        ;
    }

    /**
     * @return $this
     */
    private function registerAdditionalDrivers()
    {
        DriverList::registerDriver('Predis', Predis::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerCacheManager()
    {
        $this->app->singleton('stash.cache_manager', function (Container $container) {
            $config = $container->make('config');

            return new CacheManager(
                $config->get('stash.caches'),
                $config->get('stash.drivers'),
                $config->get('stash.tracking'),
                $config->get('stash.tracking_values'),
                $config->get('stash.default_cache')
            );
        });
        $this->app->alias('stash.cache_manager', CacheManager::class);

        $this->app->bind('stash.cache', function (Container $container) {
            return $container->make('stash.cache_manager')->getCache();
        });
        $this->app->alias('stash.cache', PoolInterface::class);

        return $this;
    }
}
