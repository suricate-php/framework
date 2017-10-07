<?php
class ServiceTest extends PHPUnit_Framework_TestCase {
    
    public function testGetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new \Suricate\Service();
        $service->undefVar;
    }

    public function testSetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new \Suricate\Service();
        $service->undefVar = "123";
    }

}
