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
        $object = new \Suricate\Suricate([], './tests/stubs/my-single-config.ini');
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(['./tests/stubs/my-single-config.ini'], $configFiles);

        // multiple config file
        $object = new \Suricate\Suricate([], ['./tests/stubs/my-single-config.ini', './tests/stubs/another-config.ini']);
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(['./tests/stubs/my-single-config.ini', './tests/stubs/another-config.ini'], $configFiles);

        // non existent file
        $object = new \Suricate\Suricate([], ['./tests/stubs/my-single-config-unknown.ini', './tests/stubs/another-config.ini']);
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty('configFile');
        $property->setAccessible(true);
        $configFiles = $property->getValue($object);

        $this->assertEquals(['./tests/stubs/another-config.ini'], $configFiles);
    }

}
