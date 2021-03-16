<?php

declare(strict_types=1);

namespace Suricate;

use Exception;

/**
 * Cache
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $type
 */

class Cache extends Service implements Interfaces\ICache
{
    protected $parametersList = ['type'];
    public static $container;
    protected $cacheTypes = [
        'memcache' => 'Suricate\Suricate::CacheMemcache',
        'memcached' => 'Suricate\Suricate::CacheMemcached',
        'apc' => 'Suricate\Suricate::CacheApc',
        'file' => 'Suricate\Suricate::CacheFile',
        'redis' => 'Suricate\Suricate::CacheRedis'
    ];

    /**
     * Init cache handler
     *
     * @return mixed
     * @throws Exceptions
     */
    protected function init()
    {
        if (static::$container === null) {
            if (isset($this->cacheTypes[$this->type])) {
                static::$container = $this->cacheTypes[$this->type](true);
                return;
            }
            throw new Exception("Unknown cache type " . $this->type);
        }
    }

    public function getInstance()
    {
        $this->init();
        return static::$container;
    }

    /**
     * Setter
     * Put a variable in cache
     * @param string $variable The key that will be associated with the item.
     * @param mixed $value    The variable to store.
     * @param int $expiry   Expiration time of the item. If it's equal to zero, the item will never expire.
     *                      You can also use Unix timestamp or a number of seconds starting from current time,
     *                      but in the latter case the number of seconds may not exceed 2592000 (30 days).
     */
    public function set(string $variable, $value, $expiry = null)
    {
        $this->init();
        return static::$container->set($variable, $value, $expiry);
    }

    /**
     * Get a variable from cache
     * @param  string $variable The key to fetch
     * @return mixed           Data fetched from cache, false if not found
     */
    public function get(string $variable)
    {
        $this->init();
        return static::$container->get($variable);
    }

    /**
     * Delete a variable from cache
     *
     * @param string $variable The key to delete
     * @return boolean
     */
    public function delete(string $variable)
    {
        $this->init();
        return static::$container->delete($variable);
    }
}
