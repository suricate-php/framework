<?php
class RegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $retVal = \Suricate\Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'fallback');

        $retVal = \Suricate\Registry::exists('index');
        $this->assertFalse($retVal);
        \Suricate\Registry::set('index', 'myNewVal');
        $retVal = \Suricate\Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'myNewVal');

        $retVal = \Suricate\Registry::exists('index');
        $this->assertTrue($retVal);

        \Suricate\Registry::clean();
        $retVal = \Suricate\Registry::exists('index');
        $this->assertFalse($retVal);
    }
}