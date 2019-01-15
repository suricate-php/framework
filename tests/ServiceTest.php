<?php
class ServiceTest extends \PHPUnit\Framework\TestCase
{
    
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
