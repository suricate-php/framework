<?php
namespace Fwk\Cache;

use Fwk;

class Memcache extends Fwk\Cache
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
        $this->handler          = false;
        $this->host             = 'localhost';
        $this->port             = '11211';
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
                throw new \BadMethodCallException('Can\'t find Memcached extension');
            }
        } else {
            return $this;
        }
    }

    public function set($variable, $value, $expiry = null)
    {
        $this->connect();

        if ($expiry == null) {
            $expiry = $this->defaultExpiry;
        }

        if ($this->useCompression) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = null;
        }

        $this->handler->set($variable, $value, $flag, $expiry);
    }

    public function get($variable)
    {
        $this->connect();

        return $this->handler->get($variable);
    }

    public function delete($variable)
    {
        $this->connect();
        $this->handler->delete($variable);
    }
}
