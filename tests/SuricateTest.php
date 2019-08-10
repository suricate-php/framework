<?php
class SuricateTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigFile()
    {
        // Empty config files list at the beginning
        $object = new \Suricate\Suricate();
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals([], $configFiles);

        // single config file
        $object = new \Suricate\Suricate(
            [],
            './tests/stubs/my-single-config.ini'
        );
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(
            ['./tests/stubs/my-single-config.ini'],
            $configFiles
        );

        // multiple config file
        $object = new \Suricate\Suricate(
            [],
            [
                './tests/stubs/my-single-config.ini',
                './tests/stubs/another-config.ini'
            ]
        );
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(
            [
                './tests/stubs/my-single-config.ini',
                './tests/stubs/another-config.ini'
            ],
            $configFiles
        );

        // non existent file
        $object = new \Suricate\Suricate(
            [],
            [
                './tests/stubs/my-single-config-unknown.ini',
                './tests/stubs/another-config.ini'
            ]
        );
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(['./tests/stubs/another-config.ini'], $configFiles);
    }

    public function testDefaultConfig()
    {
        $suricate = new Suricate\Suricate();
        $this->assertTrue($suricate->hasService('Logger'));
        $this->assertTrue($suricate->hasService('Error'));
        $this->assertFalse($suricate->hasService('Session'));

        $this->assertEquals(
            [
                'Router' => [],
                'Logger' => [
                    'enabled' => true,
                    'level' => \Suricate\Logger::LOGLEVEL_WARN,
                    'logfile' => 'php://stdout'
                ],
                'App' => ['base_uri' => '/'],
                'Error' => [
                    'report' => true,
                    'dumpContext' => true
                ]
            ],
            $suricate->getConfig()
        );
    }

    public function testConfigConst()
    {
        $object = new \Suricate\Suricate([], './tests/stubs/const.ini');
        $this->assertTrue(defined('MY_TEST_CONST'));
        $this->assertFalse(defined('my_test_const'));
        $this->assertSame(1, MY_TEST_CONST);
        $this->assertSame("my string", ANOTHER_CONST);
        $this->assertTrue(ITS_TRUE);
        $this->assertSame(true, ITS_TRUE);
    }
}
