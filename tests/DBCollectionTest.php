<?php

require_once 'stubs/Category.php';

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

    
    public static function mockProperty($object, string $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}