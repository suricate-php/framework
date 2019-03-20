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
        $testContainer['zz'];
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

        $reflector = new ReflectionClass(get_class($testContainer));
        $property = $reflector->getProperty('warehouse');
        $property->setAccessible(true);

        $this->assertEquals([], $property->getValue($testContainer));

        $property = $reflector->getProperty('content');
        $property->setAccessible(true);
        $this->assertEquals($payload, $property->getValue($testContainer));

        $testContainer['new_index'] = 'ttt';
        $this->assertEquals($payload, $property->getValue($testContainer));

        $testContainer->setWarehouse($warehouse);
        $property = $reflector->getProperty('warehouse');
        $property->setAccessible(true);

        $this->assertEquals($warehouse, $property->getValue($testContainer));


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
