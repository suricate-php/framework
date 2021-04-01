<?php
declare(strict_types=1);
class FileTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $cacheFile = new \Suricate\Cache\File();
        $this->assertEquals(3600, $cacheFile->defaultExpiry);
    }

    public function testGetSetExpiry()
    {
        $cacheFile = new \Suricate\Cache\File();

        $this->assertEquals(3600, $cacheFile->getDefaultExpiry());
        $cacheFile->setDefaultExpiry(60);
        $this->assertEquals(60, $cacheFile->getDefaultExpiry());
    }
}
