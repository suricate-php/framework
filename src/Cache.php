<?php
namespace Fwk;

/**
 * Cache
 * 
 * @package Fwk
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 */

class Cache extends Service implements Interfaces\ICache
{
    protected $parametersList = array('type');
    public static $container;

    protected function init()
    {
        if (static::$container === null) {
            switch ($this->type) {
                case 'memcache':
                    static::$container = Fwk::CacheMemcache(true);
                    break;
                case 'apc':
                    static::$container = Fwk::CacheApc(true);
                    break;
                default:
                    throw new \Exception("Unknown cache type " . $this->type);
                    break;
            }
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
    public function set($variable, $value, $expiry = null)
    {
        $this->init();
        return static::$container->set($variable, $value, $expiry);
    }

    /**
     * Get a variable from cache
     * @param  string $variable The key to fetch
     * @return mixed           Data fetched from cache, false if not found
     */
    public function get($variable)
    {
        $this->init();
        return static::$container->get($variable);
    }
}
