<?php
class MemcacheTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $cacheFile = new \Suricate\Cache\Memcache();
        $this->assertEquals('localhost', $cacheFile->host);
    }

    public function testGetSetPort()
    {
        $cacheFile = new \Suricate\Cache\Memcache();
        
        $this->assertEquals(11211, $cacheFile->getPort());
        $cacheFile->setPort(11221);
        $this->assertEquals(11221, $cacheFile->getPort());
    }

    public function testGetSetHost()
    {
        $cacheFile = new \Suricate\Cache\Memcache();
        
        $this->assertEquals('localhost', $cacheFile->getHost());
        $cacheFile->setHost('127.0.0.1');
        $this->assertEquals('127.0.0.1', $cacheFile->getHost());
    }

    public function testGetSetExpiry()
    {
        $cacheFile = new \Suricate\Cache\Memcache();
        
        $this->assertEquals(3600, $cacheFile->getDefaultExpiry());
        $cacheFile->setDefaultExpiry(60);
        $this->assertEquals(60, $cacheFile->getDefaultExpiry());
    }

    public function testGetSetCompression()
    {
        $cacheFile = new \Suricate\Cache\Memcache();
        
        $this->assertFalse($cacheFile->getUseCompression());
        $this->assertFalse($cacheFile->useCompression);
        $cacheFile->setUseCompression(true);
        $this->assertTrue($cacheFile->getUseCompression());
        $this->assertTrue($cacheFile->useCompression);
    }
}
    