<?php
use \Suricate\Registry;

class RegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'fallback');

        $retVal = Registry::exists('index');
        $this->assertFalse($retVal);

        Registry::set('index', 'myNewVal');
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'myNewVal');

        $retVal = Registry::exists('index');
        $this->assertTrue($retVal);

        Registry::clean();
        $retVal = Registry::exists('index');
        $this->assertFalse($retVal);
    }

    public function testGetProperty()
    {
        $retVal = Registry::getProperty('index', 'property', 'fallback');
        $this->assertEquals($retVal, 'fallback');

        $testObj = new \stdClass();
        $testObj->property = 'testing';

        Registry::set('index', $testObj);

        $retVal = Registry::getProperty('index', 'property', 'fallback');
        $this->assertEquals($retVal, 'testing');
    }

    public function testContext()
    {
        Registry::set('index', 'myNewVal');
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'myNewVal');

        Registry::setContext("test-context");
        $this->assertEquals("test-context", Registry::getContext());
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'fallback');

        Registry::set('index', 'myNewValInContext');
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'myNewValInContext');

        Registry::setContext(null);
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'myNewVal');
        
        Registry::setContext("test-context");
        Registry::clean();
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'fallback');
    }
}