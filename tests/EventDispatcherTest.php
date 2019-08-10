<?php

use Suricate\EventDispatcher;

require_once 'stubs/Event.php';

/**
 * @SuppressWarnings("StaticAccess")
 */
class EventDispatcherTest extends \PHPUnit\Framework\TestCase
{
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
}
