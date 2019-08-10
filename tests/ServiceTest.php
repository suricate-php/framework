<?php
class ServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testGetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new \Suricate\Service();
        $service->undefVar;
    }

    public function testSetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $service = new \Suricate\Service();
        $service->undefVar = "123";
    }

    public function testGet()
    {
        $testService = new \Suricate\Service();

        self::mockProperty($testService, 'parametersList', [
            'param_1',
            'param_2'
        ]);
        $this->assertNull($testService->param_1);
        self::mockProperty($testService, 'parametersValues', [
            'param_1' => 'value1',
            'param_2' => 'value2'
        ]);
        $this->assertNotNull($testService->param_1);
        $this->assertEquals($testService->param_1, 'value1');
    }

    public function testSet()
    {
        $testService = new \Suricate\Service();

        self::mockProperty($testService, 'parametersList', [
            'param_1',
            'param_2'
        ]);
        $this->assertNull($testService->param_1);
        $testService->param_1 = 'new_value';
        $this->assertEquals($testService->param_1, 'new_value');
    }

    public function testConfigure()
    {
        $testService = new \Suricate\Service();

        self::mockProperty($testService, 'parametersList', [
            'param_1',
            'param_2'
        ]);
        $testService->configure(['param_1' => 'value1', 'param_2' => 'value2']);

        $this->assertEquals($testService->param_1, 'value1');
        $this->assertEquals($testService->param_2, 'value2');

        $this->expectException(InvalidArgumentException::class);
        $testService->configure(['param_3' => 'value3']);
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
