<?php

use Suricate\Container;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testContainerExists()
    {
        $testContainer = new Container([
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ]);

        $this->assertTrue($testContainer->offsetExists('a'));
        $this->assertTrue($testContainer->offsetExists(5));
        $this->assertFalse($testContainer->offsetExists('c'));
        $this->assertFalse($testContainer->offsetExists(6));

        $this->assertTrue(isset($testContainer['a']));
        $this->assertTrue(isset($testContainer[5]));
        $this->assertFalse(isset($testContainer[99]));
    }

    public function testContainerGet()
    {
        $testContainer = new Container([
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ]);

        $this->assertEquals(1, $testContainer['a']);
        $this->assertEquals('z', $testContainer[5]);
        $this->assertEquals(null, $testContainer['unknownValue']);
    }

    public function testContainerUnset()
    {
        $payload = [
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ];

        $testContainer = new Container($payload);
        $this->assertSame(1, $testContainer['a']);
        $this->assertSame(3, $testContainer['b']);
        $this->assertSame('z', $testContainer['5']);
        unset($testContainer['b']);
        $this->assertFalse($testContainer->offsetExists('b'));
    }

    public function testContainerSet()
    {
        $testContainer = new Container();
        $this->assertEquals(null, $testContainer['myValue']);
        $testContainer['myValue'] = 42;
        $this->assertEquals(42, $testContainer['myValue']);
    }
}
