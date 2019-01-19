<?php

use PHPUnit\Framework\TestCase;


class DatabaseTest extends TestCase
{
    protected $className = '\Suricate\Database';
    protected $tableName = 'users';

    protected function setupData()
    {
        $pdo = new PDO('sqlite:/tmp/test.db');
        $pdo->exec("DROP TABLE IF EXISTS `" . $this->tableName ."`");
        $pdo->exec("CREATE TABLE `" .$this->tableName. "` (`id` INTEGER PRIMARY KEY,`name` varchar(50) DEFAULT NULL,`date_added` datetime NOT NULL)");
        $stmt = $pdo->prepare("INSERT INTO `" . $this->tableName . "` (name, date_added) VALUES (:name, :date)");
        $values = [
            ['John', '2019-01-10 00:00:00'],
            ['Paul', '2019-01-11 00:00:00'],
            ['Robert', '2019-01-12 00:00:00']
        ];
        foreach ($values as $value) {
            $res = $stmt->execute(['name' => $value[0], 'date' => $value[1]]);
        }
    }

    public function setUp()
    {
        $this->setupData();
    }

    public function testContructor()
    {
        $className = $this->className;
        $database = new $className();

        $this->assertNull($database->getConfig());
        $reflection = new \ReflectionClass(get_class($database));
        $property = $reflection->getProperty('handler');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($database), false);
    }

    public function testGetSetConfig()
    {
        $className = $this->className;
        $configName = "testConfig";

        $database = new $className();
        $this->assertNull($database->getConfig());
        $retVar = $database->setConfig($configName);

        $this->assertInstanceOf($className, $retVar);
        $this->assertEquals($configName, $database->getConfig());
    }

    public function testConnect()
    {
        $className = $this->className;
        $database = new $className();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db',
        ]);
        $database->query("SELECT * FROM users");
        $reflection = new \ReflectionClass(get_class($database));
        $property = $reflection->getProperty('handler');
        $property->setAccessible(true);

        $this->assertInstanceOf('\PDO', $property->getValue($database));
    }

    public function testUnsupportedHandler()
    {
        $className = $this->className;
        $database = new $className();
        $database->configure([
            'type' => 'my-pdo-handler',
        ]);
        $this->expectException(\Exception::class);
        $database->query("SELECT * FROM users");
    }

    public function testFetchAll()
    {
        $className = $this->className;
        $database = new $className();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db',
        ]);
        $queryResult = $database->query("SELECT * FROM `" . $this->tableName . "`");
        $this->assertEquals($queryResult->fetchAll(), [
            ['id' => '1', 'name' => 'John', 'date_added' => '2019-01-10 00:00:00'],
            ['id' => '2', 'name' => 'Paul', 'date_added' => '2019-01-11 00:00:00'],
            ['id' => '3', 'name' => 'Robert', 'date_added' => '2019-01-12 00:00:00'],
        ]);
    }

    protected function tearDown()
    {
        self::$pdo = null;
        $this->conn = null;
        if (is_file('/tmp/test.db')) {
            unlink('/tmp/test.db');
        }
    }
}
