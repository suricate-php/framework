<?php

/**
 * @SuppressWarnings("StaticAccess")
 */
class CurlTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $handler = \Suricate\Suricate::Curl();
        $reflection = new \ReflectionClass(get_class($handler));
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        $this->assertInstanceOf(
            \Suricate\Request::class,
            $property->getValue($handler)
        );

        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        $this->assertInstanceOf(
            \Suricate\Request::class,
            $property->getValue($handler)
        );
    }

    public function testGetSetUrl()
    {
        $handler = \Suricate\Suricate::Curl();
        $this->assertSame(null, $handler->getUrl());
        $handler->setUrl('https://www.google.com');
        $this->assertSame('https://www.google.com', $handler->getUrl());
    }

    public function testMethod()
    {
        $handler = \Suricate\Suricate::Curl();
        $handler->setMethod('DELETE');
        $reflection = new \ReflectionClass(get_class($handler));
        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        $request = $property->getValue($handler);
        $this->assertSame('DELETE', $request->getMethod());
    }

    public function testSetUA()
    {
        $handler = \Suricate\Suricate::Curl();
        $handler->setUserAgent('GoogleBot');

        $this->assertSame('GoogleBot', $handler->userAgent);
    }

    public function testAddHeader()
    {
        $handler = \Suricate\Suricate::Curl();

        $this->assertSame([], $handler->headers);
        $handler->addHeader('Content-type: application/json');
        $this->assertSame(
            ['Content-type: application/json'],
            $handler->headers
        );
        $handler->addHeader('Content-length: 42');
        $this->assertSame(
            ['Content-type: application/json', 'Content-length: 42'],
            $handler->headers
        );
    }
}
