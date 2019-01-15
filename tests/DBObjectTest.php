<?php
class DBObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testContructor()
    {
        $classname = '\Suricate\DBObject';

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->setMethods(array('setRelations'))
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setRelations');

        // now call the constructor
        $reflectedClass = new ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock);
    }

    public function testUndefinedGet()
    {
        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'dbVariables', ['id', 'name', 'last_update']);
        $this->expectException(\InvalidArgumentException::class);
        
        $testDBO->undefinedVar;
    }

    public function testDBProperty()
    {
        $testDBO = new \Suricate\DBObject();
        $testDBO->regularProperty = 42;
        self::mockProperty($testDBO, 'dbVariables', ['id', 'name', 'not_loaded_var']);
        self::mockProperty($testDBO, 'dbValues', ['id' => 1, 'name' => 'test name']);
        $this->assertEquals($testDBO->id, 1);
        $this->assertNotEquals($testDBO->name, 'test name edited');
        $this->assertNull($testDBO->not_loaded_var);

        $this->assertTrue($testDBO->isDBVariable('id'));
        $this->assertFalse($testDBO->isDBVariable('regularProperty'));

        $this->assertTrue($testDBO->propertyExists('regularProperty'));
        $this->assertTrue($testDBO->propertyExists('id'));
        $this->assertFalse($testDBO->propertyExists('unknownProperty'));
    }
    

    public static function mockProperty($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}
