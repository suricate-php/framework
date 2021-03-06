<?php

declare(strict_types=1);

namespace Suricate\Cache;

use Suricate;
use Predis\Client;
use \Exception;
use \BadMethodCallException;

/**
 * Redis extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $host           redis host (default: localhost)
 * @property int    $port           redis port (default: 6379)
 * @property int    $defaultExpiry  Key default expiry
 */

class Redis extends Suricate\Cache
{
    protected $parametersList = ['host', 'port', 'defaultExpiry'];

    /** @var Client|false $handler */
    private $handler;

    public function __construct()
    {
        parent::__construct();

        $this->handler = false;
        $this->host = 'localhost';
        $this->port = 6379;
        $this->defaultExpiry = 3600;
    }

    /**
     * Get redis host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set Redis host
     *
     * @param string $host redis hostname/ip
     *
     * @return Redis
     */
    public function setHost(string $host): Redis
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get Redis port
     *
     * @return integer
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set Redis port
     *
     * @param int $port Redis port
     *
     * @return Redis
     */
    public function setPort(int $port): Redis
    {
        $this->port = intval($port);

        return $this;
    }

    /**
     * Get default cache expiration duration
     *
     * @return integer
     */
    public function getDefaultExpiry(): int
    {
        return $this->defaultExpiry;
    }

    /**
     * Set default cache expiration duration
     *
     * @param integer $expiry
     *
     * @return Redis
     */
    public function setDefaultExpiry(int $expiry): Redis
    {
        $this->defaultExpiry = $expiry;

        return $this;
    }

    /**
     * Connect to Redis host
     *
     * @throws Exception
     * @throws BadMethodCallException
     *
     * @return Redis
     */
    private function connect(): Redis
    {
        if ($this->handler === false) {
            if (class_exists('\Predis\Client')) {
                $redisHandler = new Client([
                    'scheme' => 'tcp',
                    'host' => $this->host,
                    'port' => $this->port
                ]);
                if ($redisHandler->connect() === false) {
                    throw new Exception('Can\'t connect to redis server');
                }
                $this->handler = $redisHandler;

                return $this;
            }

            throw new BadMethodCallException('Can\'t find Redis extension');
        }

        return $this;
    }

    /**
     * Put a value into redis
     * @param string $keyname  Key name
     * @param mixed  $value    Value
     * @param int    $expiry   Cache expiry
     * @throws Exception
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function set(string $keyname, $value, $expiry = null)
    {
        $this->connect();
        if ($this->handler === false) {
            return false;
        }

        if ($expiry === null) {
            $expiry = $this->defaultExpiry;
        }

        $retVal = $this->handler->set($keyname, $value);
        $expireRetVal = true;
        if ($expiry !== -1) {
            $expireRetVal = $this->handler->expire($keyname, $expiry);
        }

        return $retVal && $expireRetVal;
    }

    /**
     * Get a cached value from keyname
     *
     * @param string $keyname
     * @throws Exception
     * @throws BadMethodCallException
     *
     * @return string|null
     */
    public function get(string $keyname): ?string
    {
        $this->connect();
        if ($this->handler) {
            return $this->handler->get($keyname);
        }

        return null;
    }

    /**
     * Delete a variable from redis
     *
     * @param string $keyname
     * @throws Exception
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function delete(string $keyname): bool
    {
        $this->connect();
        if ($this->handler) {
            return (bool) $this->handler->del([$keyname]);
        }

        return false;
    }
}
