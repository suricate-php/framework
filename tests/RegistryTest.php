<?php

use Suricate\Registry;

/**
 * @SuppressWarnings("StaticAccess") */
class RegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        // test fallback return
        $retVal = Registry::get('index', 'fallback');
        $this->assertEquals($retVal, 'fallback');

        // test non existent value
        $retVal = Registry::exists('index');
        $this->assertFalse($retVal);

        // test existing value, no fallback
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
