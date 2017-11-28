<?php

namespace ZingleCom\Stash;

use Tedivm\StashBundle\Factory\DriverFactory;
use Tedivm\StashBundle\Service\CacheService;
use Tedivm\StashBundle\Service\CacheTracker;
use ZingleCom\Stash\Exception\CacheNotFoundException;

/**
 * Class CacheManager
 */
class CacheManager
{
    /**
     * @var array
     */
    private $cacheConfig;

    /**
     * @var array
     */
    private $driverConfig;

    /**
     * @var bool
     */
    private $tracking;

    /**
     * @var bool
     */
    private $trackingValues;

    /**
     * @var CacheService[]
     */
    private $caches = [];

    /**
     * @var string
     */
    private $defaultCache;


    /**
     * DriverManager constructor.
     *
     * @param array  $cacheConfig
     * @param array  $driverConfig
     * @param bool   $tracking
     * @param bool   $trackingValues
     * @param string $defaultCache
     */
    public function __construct(
        array $cacheConfig,
        array $driverConfig,
        $tracking = false,
        $trackingValues = false,
        $defaultCache = 'default'
    ) {
        $this->cacheConfig    = $cacheConfig;
        $this->driverConfig   = $driverConfig;
        $this->tracking       = $tracking;
        $this->trackingValues = $trackingValues;
        $this->defaultCache   = $defaultCache;
    }

    /**
     * @param string $name
     * @return CacheService
     * @throws CacheNotFoundException
     */
    public function getCache($name = null)
    {
        if (null === $name) {
            $name = $this->defaultCache;
        }

        if (!isset($this->cacheConfig[$name])) {
            throw new CacheNotFoundException($name);
        }

        if (!isset($this->caches[$name])) {
            $this->caches[$name] = $this->buildCache($name);
        }

        return $this->caches[$name];
    }

    /**
     * @param string $name
     * @return CacheService
     */
    private function buildCache($name)
    {
        $config = $this->cacheConfig[$name];
        $drivers = $config['drivers'];

        $driver = DriverFactory::createDriver(
            $drivers,
            $this->driverConfig
        );

        $tracker = new CacheTracker($name);
        $tracker->enableQueryLogging($this->tracking);
        $tracker->enableQueryValueLogging($this->trackingValues);

        return new CacheService($name, $driver, $tracker);
    }
}
