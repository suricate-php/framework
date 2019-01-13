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
 * @property int    $port           Memcache port (default: 11211)
 * @property int    $defaultExpiry  Key default expiry
 * @property bool   $useCompression Use memcache compression (default: false)
 */

class Memcache extends Suricate\Cache
{
    protected $parametersList = array(
                                    'host',
                                    'port',
                                    'defaultExpiry',
                                    'useCompression'
                                );
    private $handler;

    public function __construct()
    {
        parent::__construct();

        $this->handler          = false;
        $this->host             = 'localhost';
        $this->port             = 11211;
        $this->useCompression   = false;
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
    
    public function getUseCompression()
    {
        return $this->useCompression;
    }

    public function setUseCompression($useCompression)
    {
        $this->useCompression = $useCompression;

        return $this;
    }

    private function connect()
    {
        if ($this->handler === false) {
            if (class_exists('Memcache')) {
                try {
                    $this->handler = new \Memcache();
                    $this->handler->connect($this->host, $this->port);
                } catch (\Exception $e) {
                    throw new \Exception('Can\'t connect to memcache server');
                }
            } else {
                throw new \BadMethodCallException('Can\'t find Memcache extension');
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
     */
    public function set(string $variable, $value, $expiry = null)
    {
        $this->connect();

        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }

        if ($this->useCompression !== false) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = null;
        }

        $this->handler->set($variable, $value, $flag, $expiry);
    }

    public function get(string $variable)
    {
        $this->connect();
        return $this->handler->get($variable);
    }

    public function delete(string $variable)
    {
        $this->connect();
        return $this->handler->delete($variable);
    }
}
