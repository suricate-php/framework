<?php

use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 **/
class DatabaseTest extends TestCase
{
    protected $className = '\Suricate\Database';
    protected $tableName = 'users';

    protected function setupData()
    {
        $pdo = new PDO('sqlite:/tmp/test.db');
        $pdo->exec("DROP TABLE IF EXISTS `" . $this->tableName . "`");
        $pdo->exec(
            "CREATE TABLE `" .
                $this->tableName .
                "` (`id` INTEGER PRIMARY KEY,`name` varchar(50) DEFAULT NULL,`date_added` datetime NOT NULL)"
        );
        $stmt = $pdo->prepare(
            "INSERT INTO `" .
                $this->tableName .
                "` (name, date_added) VALUES (:name, :date)"
        );
        $values = [
            ['John', '2019-01-10 00:00:00'],
            ['Paul', '2019-01-11 00:00:00'],
            ['Robert', '2019-01-12 00:00:00']
        ];
        foreach ($values as $value) {
            $stmt->execute(['name' => $value[0], 'date' => $value[1]]);
        }
    }

    protected function getDatabase()
    {
        $className = $this->className;
        $database = new $className();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db'
        ]);

        return $database;
    }

    public function setUp(): void
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

    public function testGetSetConfigs()
    {
        $className = $this->className;
        $configs = [
            'test1' => ['type' => 'sqlite', 'memory' => true],
            'test2' => ['type' => 'mysql']
        ];

        $database = new $className();
        $this->assertSame([], $database->getConfigs());
        $retVar = $database->setConfigs($configs);

        $this->assertInstanceOf($className, $retVar);
        $this->assertEquals($configs, $database->getConfigs());
    }

    public function testConnect()
    {
        $database = $this->getDatabase();
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
            'type' => 'my-pdo-handler'
        ]);
        $this->expectException(\Exception::class);
        $database->query("SELECT * FROM users");
    }

    public function testFetchAll()
    {
        $database = $this->getDatabase();
        $queryResult = $database->query(
            "SELECT * FROM `" . $this->tableName . "`"
        );
        $this->assertEquals($queryResult->fetchAll(), [
            [
                'id' => '1',
                'name' => 'John',
                'date_added' => '2019-01-10 00:00:00'
            ],
            [
                'id' => '2',
                'name' => 'Paul',
                'date_added' => '2019-01-11 00:00:00'
            ],
            [
                'id' => '3',
                'name' => 'Robert',
                'date_added' => '2019-01-12 00:00:00'
            ]
        ]);
    }

    public function testFetch()
    {
        $database = $this->getDatabase();
        $database->query("SELECT * FROM `" . $this->tableName . "`");
        // Record 1
        $this->assertSame(
            [
                'id' => 1,
                'name' => 'John',
                'date_added' => '2019-01-10 00:00:00'
            ],
            $database->fetch()
        );
        // Record 2
        $this->assertSame(
            [
                'id' => 2,
                'name' => 'Paul',
                'date_added' => '2019-01-11 00:00:00'
            ],
            $database->fetch()
        );
        // Record 3
        $this->assertSame(
            [
                'id' => 3,
                'name' => 'Robert',
                'date_added' => '2019-01-12 00:00:00'
            ],
            $database->fetch()
        );
        // No more records
        $this->assertFalse($database->fetch());
    }

    public function testFetchObject()
    {
        $database = $this->getDatabase();
        $database->query("SELECT * FROM `" . $this->tableName . "`");
        $result = $database->fetchObject();
        $expected = new \stdClass();
        $expected->id = 1;
        $expected->name = 'John';
        $expected->date_added = '2019-01-10 00:00:00';

        $this->assertEquals($expected, $result);
        $this->assertSame($expected->id, $result->id);
    }

    public function testFetchColumn()
    {
        $database = $this->getDatabase();
        $database->query("SELECT * FROM `" . $this->tableName . "` WHERE id=2");
        $this->assertSame(2, $database->fetchColumn());
        $database->query("SELECT * FROM `" . $this->tableName . "` WHERE id=2");
        $this->assertSame('Paul', $database->fetchColumn(1));
        $this->assertFalse($database->fetchColumn(1));
    }

    public function testLastInsertId()
    {
        $database = $this->getDatabase();
        $database->query(
            "INSERT INTO `" .
                $this->tableName .
                "` (name, date_added) VALUES ('Rodrigo', '2019-01-13 00:00:00')"
        );
        $this->assertSame(
            '4',
            $database->lastInsertId(),
            'Test last inserted id'
        );
    }

    public function testGetColumnCount()
    {
        $database = $this->getDatabase();
        $database->query("SELECT * FROM `" . $this->tableName . "`");
        $this->assertEquals(3, $database->getColumnCount());
    }

    protected function tearDown(): void
    {
        if (is_file('/tmp/test.db')) {
            unlink('/tmp/test.db');
        }
    }
}
