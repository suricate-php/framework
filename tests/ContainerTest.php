<?php
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testContainerExists()
    {
        $testContainer = new \Suricate\Container([
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
        $testContainer = new \Suricate\Container([
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ]);        
        
        $this->assertEquals($testContainer['a'], 1);
        $this->assertEquals($testContainer[5], 'z');

        $this->expectException(\InvalidArgumentException::class);
        $tmp = $testContainer['zz'];
    }

    public function testContainerSet()
    {
        $payload = [
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ];

        $warehouse = ['zz' => 'my_value'];
        
        $testContainer = new \Suricate\Container($payload);
        $this->assertAttributeEquals([], 'warehouse', $testContainer);
        $this->assertAttributeEquals($payload, 'content', $testContainer);

        $testContainer->setWarehouse($warehouse);
        $this->assertAttributeEquals($warehouse, 'warehouse', $testContainer);


    }

    public function testContainerUnset()
    {
        $payload = [
            'a' => 1,
            'b' => 3,
            5 => 'z'
        ];

        $testContainer = new \Suricate\Container($payload);
        $this->assertAttributeEquals($payload, 'content', $testContainer);
        unset($testContainer['b']);
        $this->assertAttributeEquals( [
            'a' => 1,
            5 => 'z'
        ], 'content', $testContainer);
    }

    public function testContainerWarehouse()
    {
        $warehouse = ['test' => 'stdClass'];

        $testContainer = new \Suricate\Container([]);
        $testContainer->setWarehouse($warehouse);
        $this->assertEquals($testContainer['test'], new \stdClass);
    }
}
