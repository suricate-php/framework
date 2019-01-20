<?php
require_once 'stubs/Category.php';

class DBObjectTest extends \PHPUnit\Framework\TestCase
{
    protected $tableName = 'users';
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

    public function testInstanciate()
    {
        $testDBO = Category::instanciate([
            'id' => 1,
            'name' => 'test record',
        ]);

        $reflector = new ReflectionClass(Category::class);
        $property = $reflector->getProperty('dbValues');
        $property->setAccessible(true);
        $this->assertEquals([
            'id' => 1,
            'name' => 'test record',
        ], $property->getValue($testDBO));
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

    public function testWakeup()
    {
        $mock = $this->getMockBuilder(\Suricate\DBObject::class)
            ->setMethods(['setRelations'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('setRelations');
        
        $mock->__wakeup();
    }

    public function testRelations()
    {
        $relations = [
            'category' => [
                'type' => \Suricate\DBObject::RELATION_ONE_ONE,
                'source' => 'category_id',
                'target' => 'Category'
            ]
        ];
        // Prepare database
        $this->setupData();
        $mock = $this->getMockBuilder(\Suricate\DBObject::class)
            ->setMethods(['setRelations', 'getRelation'])
            ->getMock();

        // Prepare setup DBObject
        $testDBO = $this->getDBOject();
        $reflector = new ReflectionClass($mock);
        $property = $reflector->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($testDBO, $relations);

        // get relation values
        $reflector = new ReflectionClass($testDBO);
        $relationValuesRef = $reflector->getProperty('relationValues');
        $relationValuesRef->setAccessible(true);

        $loadedRelationsRef = $reflector->getProperty('loadedRelations');
        $loadedRelationsRef->setAccessible(true);

        // Load
        $testDBO->load(1);
        $relationsValues = $relationValuesRef->getValue($testDBO);
        $loadedRelations = $loadedRelationsRef->getValue($testDBO);

        // No relation values at first
        $this->assertSame([], $relationsValues);
        $this->assertSame([], $loadedRelations);
        $this->assertEquals('Admin', $testDBO->category->name);
        $this->assertInstanceOf('\Suricate\DBObject', $testDBO->category);


        $relationsValues = $relationValuesRef->getValue($testDBO);
        $loadedRelations = $loadedRelationsRef->getValue($testDBO);

        // Check relation cache has been set
        $this->assertArrayHasKey('category', $relationsValues);

        // Check relation loaded flag has been set
        $this->assertArrayHasKey('category', $loadedRelations);

        // Check return type of relation
        $this->assertInstanceOf('\Suricate\DBObject', $relationsValues['category']);

        // Load new object
        $testDBO = $this->getDBOject();
        $reflector = new ReflectionClass($mock);
        $property = $reflector->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($testDBO, $relations);
        $testDBO->load(2);
        // get relation values
        $reflector = new ReflectionClass($testDBO);
        $relationValuesRef = $reflector->getProperty('relationValues');
        $relationValuesRef->setAccessible(true);

        $loadedRelationsRef = $reflector->getProperty('loadedRelations');
        $loadedRelationsRef->setAccessible(true);

        $relationsValues = $relationValuesRef->getValue($testDBO);
        $loadedRelations = $loadedRelationsRef->getValue($testDBO);

        // No relation values at first
        $this->assertSame([], $relationsValues);
        $this->assertSame([], $loadedRelations);

        // Isset implicit load relation, check that's been loaded
        $this->assertTrue(isset($testDBO->category));
    }

    public function testLoad()
    {
        // Prepare database
        $this->setupData();

        // Inject database handler
        $testDBO = $this->getDBOject();

        $this->assertFalse($testDBO->isLoaded());
        $retVal = $testDBO->load(1);
        $this->assertTrue($testDBO->isLoaded());
        $this->assertEquals(1, $testDBO->id);

        $this->assertEquals('John', $testDBO->name);
        
        $this->assertInstanceOf('\Suricate\DBObject', $retVal);
    }

    public function testToArray()
    {
        // Prepare database
        $this->setupData();

        // Inject database handler
        $testDBO = $this->getDBOject();
        $testDBO->load(2);
        
        $this->assertSame([
            'id' => '2',
            'category_id' => '100',
            'name' => 'Paul',
            'date_added' => '2019-01-11 00:00:00',
            ],
            $testDBO->toArray()
        );

        $testDBO = $this->getDBOject();
        $testDBO->load(2);
        self::mockProperty($testDBO, 'exportedVariables', [
            'id' => 'id', 
            'category_id' => 'category_id,type:integer',
            'name' => 'name',
            'date_added' => '-']
        );

        $this->assertSame([
            'id' => '2',
            'category_id' => 100,
            'name' => 'Paul',
            ],
            $testDBO->toArray()
        );
    }

    public function testToJson()
    {
        // Prepare database
        $this->setupData();

        // Inject database handler
        $testDBO = $this->getDBOject();
        $testDBO->load(2);

        $this->assertSame(
            '{"id":"2","category_id":"100","name":"Paul","date_added":"2019-01-11 00:00:00"}',
            $testDBO->toJson()
        );
    }

    public static function mockProperty($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }

    protected function setupData()
    {
        $pdo = new PDO('sqlite:/tmp/test.db');
        $pdo->exec("DROP TABLE IF EXISTS `users`");
        $pdo->exec("DROP TABLE IF EXISTS `categories`");
        $pdo->exec("CREATE TABLE `users` (`id` INTEGER PRIMARY KEY,`category_id` INTEGER, `name` varchar(50) DEFAULT NULL,`date_added` datetime NOT NULL)");
        $pdo->exec("CREATE TABLE `categories` (`id` INTEGER PRIMARY KEY, `name` varchar(50) DEFAULT NULL)");
        
        $stmt = $pdo->prepare("INSERT INTO `users` (name, category_id, date_added) VALUES (:name, :categoryid, :date)");
        $values = [
            ['John', 100, '2019-01-10 00:00:00'],
            ['Paul', 100, '2019-01-11 00:00:00'],
            ['Robert', 101, '2019-01-12 00:00:00']
        ];
        foreach ($values as $value) {
            $stmt->execute(['name' => $value[0], 'categoryid' => $value[1], 'date' => $value[2]]);
        }

        $stmt = $pdo->prepare("INSERT INTO `categories` (id, name) VALUES (:id, :name)");
        $values = [
            [100, 'Admin'],
            [101, 'Employee']
        ];
        foreach ($values as $value) {
            $stmt->execute(['id' => $value[0], 'name' => $value[1]]);
        }
    }

    protected function getDatabase()
    {
        $database = new \Suricate\Database();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db',
        ]);

        return $database;
    }

    protected function getDBOject()
    {
        $dbLink = $this->getDatabase();
        // Inject database handler
        $testDBO = new \Suricate\DBObject();


        $reflector = new ReflectionClass(get_class($testDBO));
        $property = $reflector->getProperty('dbLink');
        $property->setAccessible(true);
        $property->setValue($testDBO, $dbLink);

        self::mockProperty($testDBO, 'tableName', $this->tableName);
        self::mockProperty($testDBO, 'tableIndex', 'id');
        self::mockProperty($testDBO, 'dbVariables', ['id', 'category_id', 'name', 'date_added']);

        return $testDBO;
    }
}
