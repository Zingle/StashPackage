<?php

namespace ZingleCom\Stash\Driver;

use Predis\Client;
use Stash\Driver\AbstractDriver;

/**
 * Class Predis
 *
 * For support for Illuminate redis database
 */
class Predis extends AbstractDriver
{
    /**
     * The Redis drivers.
     *
     * @var Client
     */
    protected $client;

    /**
     * The cache of indexed keys.
     *
     * @var array
     */
    protected $keyCache = array();


    /**
     * The options array should contain an array of servers,
     *
     * The "server" option expects an array of servers, with each server being represented by an associative array. Each
     * redis config must have either a "socket" or a "server" value, and optional "port" and "ttl" values (with the ttl
     * representing server timeout, not cache expiration).
     *
     * The "database" option lets developers specific which specific database to use.
     *
     * The "password" option is used for clusters which required authentication.
     *
     * @param array $options
     */
    public function setOptions(array $options = array())
    {
        $options += $this->getDefaultOptions();

        // Normalize Server Options
        if (isset($options['servers'])) {
            $unprocessedServers = (is_array($options['servers']))
                ? $options['servers']
                : array($options['servers']);
            unset($options['servers']);

            $servers = array();
            foreach ($unprocessedServers as $server) {
                $server = array_merge([
                    'scheme' => 'tcp',
                    'port'   => '6379',
                    'host'   => '127.0.0.1',
                ], $server);

                if ('tcp' === $server['scheme']) {
                    $servers[] = sprintf('tcp://%s:%s', $server['host'], $server['port']);
                } elseif ('unix' === $server['scheme']) {
                    $servers[] = sprintf('unix:/%s', $server['path']);
                } else {
                    throw new \RuntimeException(sprintf('Predis driver doesn\'t support "%s" currently', $server['scheme']));
                }
            }
        } else {
            $servers = ['tcp://127.0.0.1'];
        }

        $this->client = new Client(1 === count($servers) ? array_pop($servers) : $servers);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key)
    {
        return unserialize($this->client->get($this->makeKeyString($key)));
    }

    /**
     * {@inheritdoc}
     */
    public function storeData($key, $data, $expiration)
    {
        $store = serialize(array('data' => $data, 'expiration' => $expiration));
        if (is_null($expiration)) {
            return $this->client->set($this->makeKeyString($key), $store);
        } else {
            $ttl = $expiration - time();

            // Prevent us from even passing a negative ttl'd item to redis,
            // since it will just round up to zero and cache forever.
            if ($ttl < 1) {
                return true;
            }

            return $this->client->setex($this->makeKeyString($key), $ttl, $store);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key = null)
    {
        if (is_null($key)) {
            $this->client->flushdb();

            return true;
        }

        $keyString = $this->makeKeyString($key, true);
        $keyReal = $this->makeKeyString($key);
        $this->client->incr($keyString); // increment index for children items
        $this->client->del([$keyReal]); // remove direct item.
        $this->keyCache = array();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function isAvailable()
    {
        return class_exists('Predis\Client', false);
    }

    /**
     * {@inheritdoc}
     */
    public function isPersistent()
    {
        return true;
    }

    /**
     * Turns a key array into a key string. This includes running the indexing functions used to manage the Redis
     * hierarchical storage.
     *
     * When requested the actual path, rather than a normalized value, is returned.
     *
     * @param  array  $key
     * @param  bool   $path
     * @return string
     */
    protected function makeKeyString($key, $path = false)
    {
        $key = \Stash\Utilities::normalizeKeys($key);

        $keyString = 'cache:::';
        $pathKey = ':pathdb::';
        foreach ($key as $name) {
            //a. cache:::name
            //b. cache:::name0:::sub
            $keyString .= $name;

            //a. :pathdb::cache:::name
            //b. :pathdb::cache:::name0:::sub
            $pathKey = ':pathdb::' . $keyString;
            $pathKey = md5($pathKey);

            if (isset($this->keyCache[$pathKey])) {
                $index = $this->keyCache[$pathKey];
            } else {
                $index = $this->client->get($pathKey);
                $this->keyCache[$pathKey] = $index;
            }

            //a. cache:::name0:::
            //b. cache:::name0:::sub1:::
            $keyString .= '_' . $index . ':::';
        }

        return $path ? $pathKey : md5($keyString);
    }
}
