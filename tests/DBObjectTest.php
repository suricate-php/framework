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

    public function testGetTableName()
    {
        $testName = 'my_sql_table';

        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'tableName', $testName);
        $this->assertEquals($testName, $testDBO->getTableName());
    }

    public function testGetTableIndex()
    {
        $testIndex = 'id';

        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'tableIndex', $testIndex);
        $this->assertEquals($testIndex, $testDBO->getTableIndex());
    }

    public function testGetDBConfig()
    {
        $testConfigName = 'my_config';

        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'DBConfig', $testConfigName);
        $this->assertEquals($testConfigName, $testDBO->getDBConfig());
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

    public function testIsset()
    {
        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'dbVariables', ['id', 'name', 'not_loaded_var']);
        self::mockProperty($testDBO, 'dbValues', ['id' => 1, 'name' => 'test name']);

        $this->assertTrue(isset($testDBO->id));
        $this->assertFalse(isset($testDBO->undefVar));
    }

    public function testIsLoaded()
    {
        $testIndex = 'id';

        $testDBO = new \Suricate\DBObject();
        self::mockProperty($testDBO, 'tableIndex', $testIndex);
        self::mockProperty($testDBO, 'dbVariables', [$testIndex, 'name', 'not_loaded_var']);
        $this->assertFalse($testDBO->isLoaded());

        self::mockProperty($testDBO, 'dbValues', [$testIndex => 1, 'name' => 'test name']);
        $this->assertTrue($testDBO->isLoaded());
    }

    public function testHydrate()
    {
        $testDBO = new \Suricate\DBObject();
        $testDBO->realProperty = '';

        self::mockProperty($testDBO, 'dbVariables', ['id', 'name']);
        $testDBO->hydrate([
            'id' => 1,
            'name' => 'test record',
            'add_column' => 'test value',
            'realProperty' => 'my string',
        ]);

        $this->assertEquals($testDBO->realProperty, 'my string');

        $reflector = new ReflectionClass(get_class($testDBO));
        $property = $reflector->getProperty('dbValues');
        $property->setAccessible(true);
        $this->assertEquals([
            'id' => 1,
            'name' => 'test record',
        ], $property->getValue($testDBO));

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
