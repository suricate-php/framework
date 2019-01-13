<?php
namespace Suricate\Cache;

use Suricate;

/**
 * Memcache extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $host           Memcache host (default: localhost)
 * @property string $port           Memcache port (default: 11211)
 * @property int    $defaultExpiry  Key default expiry
 */

class Memcached extends Suricate\Cache
{
    protected $parametersList = array(
                                    'host',
                                    'port',
                                    'defaultExpiry',
                                );
    private $handler;

    public function __construct()
    {
        parent::__construct();

        $this->handler          = false;
        $this->host             = 'localhost';
        $this->port             = '11211';
    }
    
    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
        
        return $this;
    }

    public function getDefaultExpiry()
    {
        return $this->defaultExpiry;
    }

    public function setDefaultExpiry($expiry)
    {
        $this->defaultExpiry = $expiry;

        return $this;
    }
    
    private function connect()
    {
        if ($this->handler === false) {
            if (class_exists('Memcached')) {
                try {
                    $this->handler = new \Memcached();
                    $this->handler->addServer($this->host, $this->port);
                } catch (\Exception $e) {
                    throw new \Exception('Can\'t connect to memcache server');
                }
            } else {
                throw new \BadMethodCallException('Can\'t find Memcached extension');
            }
        } else {
            return $this;
        }
    }

    /**
     * Put a value into memcache
     * @param string $variable Variable name
     * @param mixed $value    Value
     * @param int $expiry   Cache expiry
     *
     * @return bool
     */
    public function set(string $variable, $value, $expiry = null)
    {
        $this->connect();

        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }

        return $this->handler->set($variable, $value, $expiry);
    }

    public function get(string $variable)
    {
        $this->connect();
        return $this->handler->get($variable);
    }

    /**
     * Delete a variable from memcache
     *
     * @param string $variable
     *
     * @return bool
     */
    public function delete(string $variable)
    {
        $this->connect();
        return $this->handler->delete($variable);
    }
}
