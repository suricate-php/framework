<?php

use Suricate\Event\EventDispatcher;

require_once 'stubs/TestEvent.php';
require_once 'stubs/EventListener1.php';
require_once 'stubs/EventListener2.php';
require_once 'stubs/EventListener3.php';

/**
 * @SuppressWarnings("StaticAccess")
 */
class EventDispatcherTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigure()
    {
        $parameters = [
            [
                'event' => 'my.event',
                'listeners' => ['listener1', 'listener2', 'listener3|50']
            ]
        ];
        $dispatcher = new EventDispatcher();
        $dispatcher->configure($parameters);

        $reflector = new ReflectionClass(get_class($dispatcher));
        $property = $reflector->getProperty('listeners');
        $property->setAccessible(true);

        $result = [
            'my.event' => [0 => ['listener1', 'listener2'], 50 => ['listener3']]
        ];
        $this->assertSame($result, $property->getValue($dispatcher));
    }

    public function testAddListener()
    {
        $dispatcher = new EventDispatcher();

        $reflector = new ReflectionClass(get_class($dispatcher));
        $property = $reflector->getProperty('listeners');
        $property->setAccessible(true);
        $this->assertSame([], $property->getValue($dispatcher));

        $dispatcher->addListener('dummy.event', '\MyProject\MyListener', 12);
        $result = ['dummy.event' => [12 => ['\MyProject\MyListener']]];
        $this->assertSame($result, $property->getValue($dispatcher));

        $dispatcher->addListener(
            'dummy.event',
            '\MyProject\MySecondListener',
            10
        );
        $result = [
            'dummy.event' => [
                12 => ['\MyProject\MyListener'],
                10 => ['\MyProject\MySecondListener']
            ]
        ];
        $this->assertSame($result, $property->getValue($dispatcher));

        $dispatcher->addListener('TestEvent', '\MyProject\MyThirdListener', 12);
        $result = [
            'dummy.event' => [
                12 => ['\MyProject\MyListener'],
                10 => ['\MyProject\MySecondListener']
            ],
            'my.test.event' => [12 => ['\MyProject\MyThirdListener']]
        ];
        $this->assertSame($result, $property->getValue($dispatcher));
    }

    public function testFire()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('test.event', 'EventListener1');
        $dispatcher->addListener('test.event', 'EventListener2');
        $dispatcher->fire('test.event', 'payload_string');
        $this->expectOutputString(
            "payload for listerner1 is : payload_string\npayload for listerner2 is : payload_string\n"
        );
    }

    public function testFireObjet()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('my.test.event', 'EventListener3');
        $dispatcher->addListener('test.event', 'EventListener2');
        $dispatcher->fire(new TestEvent());
        $this->expectOutputString("payload for listerner3 is : lorem ipsum\n");
    }

    public function testFireException()
    {
        $dispatcher = new EventDispatcher();
        $this->expectException('InvalidArgumentException');

        $dispatcher->fire(
            /** @scrutinizer ignore-type */
            new \Stdclass()
        );
    }

    public function testGetImpactedListeners()
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener('dummy.event', '\MyProject\MyListener', 12);
        $dispatcher->addListener('dummy.event', '\MyProject\MyListener2', 10);
        $dispatcher->addListener('another.event', '\MyProject\MyListener3', 12);

        $reflection = new \ReflectionClass(get_class($dispatcher));
        $method = $reflection->getMethod('getImpactedListeners');
        $method->setAccessible(true);
        $methodResult = $method->invoke($dispatcher, 'dummy.event');

        $this->assertSame(
            ['\MyProject\MyListener2', '\MyProject\MyListener'],
            $methodResult
        );

        $methodResult = $method->invoke($dispatcher, 'dummy.event');
        $this->assertSame(
            ['\MyProject\MyListener2', '\MyProject\MyListener'],
            $methodResult
        );
    }

    public function testSortListeners()
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener('dummy.event', '\MyProject\MyListener', 12);
        $dispatcher->addListener('dummy.event', '\MyProject\MyListener2', 10);
        $dispatcher->addListener('another.event', '\MyProject\MyListener3', 12);

        $reflection = new \ReflectionClass(get_class($dispatcher));
        $method = $reflection->getMethod('sortListeners');
        $method->setAccessible(true);
        $method->invoke($dispatcher, 'dummy.event');

        $property = $reflection->getProperty('sortedListeners');
        $property->setAccessible(true);

        $result = [
            'dummy.event' => ['\MyProject\MyListener2', '\MyProject\MyListener']
        ];
        $this->assertEquals($result, $property->getValue($dispatcher));

        $method->invoke($dispatcher, 'unknown.event');
        $this->assertEquals($result, $property->getValue($dispatcher));
    }
}
