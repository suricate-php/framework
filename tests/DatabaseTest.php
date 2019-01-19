<?php
class DatabaseTest extends \PHPUnit\Framework\TestCase
{

    protected $className = '\Suricate\Database';
    public function testContructor()
    {
        $className = $this->className;
        $database = new $className();
        
        $this->assertNull($database->getConfig());
        $reflection = new \ReflectionClass(get_class($database));
        $property = $reflection->getProperty('handler');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($database), false);

    }

}
