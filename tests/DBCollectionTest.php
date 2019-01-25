<?php

require_once 'stubs/Category.php';
require_once 'stubs/CategoriesList.php';

class DBCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTableName()
    {
        $testName = 'categories';

        $testCollection = new \Suricate\DBCollection();
        self::mockProperty($testCollection, 'tableName', $testName);
        $this->assertEquals($testName, $testCollection->getTableName());
    }

    public function testGetItemsType()
    {
        $testName = Category::class;

        $testCollection = new \Suricate\DBCollection();
        self::mockProperty($testCollection, 'itemsType', $testName);
        $this->assertEquals($testName, $testCollection->getItemsType());
    }

    public function testGetDBConfig()
    {
        $testConfigName = 'my_config';

        $testCollection = new \Suricate\DBCollection();
        self::mockProperty($testCollection, 'DBConfig', $testConfigName);
        $this->assertEquals($testConfigName, $testCollection->getDBConfig());
    }

    public function testGetParentIdField()
    {
        $testName = 'parent_id';

        $testCollection = new \Suricate\DBCollection();
        self::mockProperty($testCollection, 'parentIdField', $testName);
        $this->assertEquals($testName, $testCollection->getParentIdField());
    }

    public function testGetParentId()
    {
        $testId = 100;

        $testCollection = new \Suricate\DBCollection();
        $this->assertNull($testCollection->getParentId());
        self::mockProperty($testCollection, 'parentId', $testId);
        $this->assertSame($testId, $testCollection->getParentId());
    }

    public function testGetSetLazyLoad()
    {
        $testCollection = new \Suricate\DBCollection();
        $this->assertFalse($testCollection->getLazyLoad());
        $retVal = $testCollection->setLazyLoad(true);
        $this->assertInstanceOf(\Suricate\DBCollection::class, $retVal);
        $this->assertTrue($testCollection->getLazyLoad());
    }

    public function testLoadFromSql()
    {
        $this->setupData();
        $testDBCollection = $this->getDBCollection();

        $sql = "SELECT * FROM categories WHERE id>:id";
        $sqlParams = ['id' => 0];

        $retVal = $testDBCollection->loadFromSql($sql, $sqlParams);
        
        $this->assertInstanceOf(\Suricate\DBCollection::class, $retVal);
        $this->assertSame(2, $testDBCollection->count());
        $this->assertInstanceOf(Category::class, $testDBCollection[0]);
    }

    public function testBuildFromSql()
    {
        $this->setupData();

        $sql = "SELECT * FROM categories WHERE id>:id";
        $sqlParams = ['id' => 0];

        $retVal = CategoriesList::buildFromSql($sql, $sqlParams);
        
        $this->assertInstanceOf(\Suricate\DBCollection::class, $retVal);
        $this->assertSame(2, $retVal->count());
        $this->assertInstanceOf(Category::class, $retVal[0]);
    }

    public function testLoadAll()
    {
        $this->setupData();
        $retVal = CategoriesList::loadAll();

        $this->assertInstanceOf(\Suricate\DBCollection::class, $retVal);
        $this->assertSame(2, $retVal->count());
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

    protected function getDBCollection()
    {
        $dbLink = $this->getDatabase();
        // Inject database handler
        $testDBCollection = new \Suricate\DBCollection();


        $reflector = new ReflectionClass(get_class($testDBCollection));
        $property = $reflector->getProperty('dbLink');
        $property->setAccessible(true);
        $property->setValue($testDBCollection, $dbLink);

        self::mockProperty($testDBCollection, 'tableName', 'categories');
        self::mockProperty($testDBCollection, 'itemsType', Category::class);
        self::mockProperty($testDBCollection, 'parentIdField', 'parent_id');
        
        return $testDBCollection;
    }

    protected function setupData()
    {
        $pdo = new PDO('sqlite:/tmp/test.db');
        $pdo->exec("DROP TABLE IF EXISTS `users`");
        $pdo->exec("DROP TABLE IF EXISTS `categories`");
        $pdo->exec("CREATE TABLE `users` (`id` INTEGER PRIMARY KEY,`category_id` INTEGER, `name` varchar(50) DEFAULT NULL,`date_added` datetime NOT NULL)");
        $pdo->exec("CREATE TABLE `categories` (`id` INTEGER PRIMARY KEY, `name` varchar(50) DEFAULT NULL, `parent_id` INTEGER DEFAULT NULL)");
        
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

    public static function mockProperty($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}