<?php
namespace Fwk\Cache;

use Fwk;

class Apc extends Fkw\Cache
{
    public function __construct()
    {

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

    }

    public function set($variable, $value, $expiry = null)
    {
        $this->connect();

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
