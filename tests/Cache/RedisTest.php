<?php
declare(strict_types=1);
class RedisTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $cacheFile = new \Suricate\Cache\Redis();
        $this->assertEquals('localhost', $cacheFile->host);
        $this->assertEquals(6379, $cacheFile->port);
    }

    public function testGetSetPort()
    {
        $cacheFile = new \Suricate\Cache\Redis();

        $this->assertEquals(6379, $cacheFile->getPort());
        $cacheFile->setPort(6380);
        $this->assertEquals(6380, $cacheFile->getPort());
    }

    public function testGetSetHost()
    {
        $cacheFile = new \Suricate\Cache\Redis();

        $this->assertEquals('localhost', $cacheFile->getHost());
        $cacheFile->setHost('127.0.0.1');
        $this->assertEquals('127.0.0.1', $cacheFile->getHost());
    }

    public function testGetSetExpiry()
    {
        $cacheFile = new \Suricate\Cache\Redis();

        $this->assertEquals(3600, $cacheFile->getDefaultExpiry());
        $cacheFile->setDefaultExpiry(60);
        $this->assertEquals(60, $cacheFile->getDefaultExpiry());
    }
}
