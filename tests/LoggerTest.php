<?php

use Suricate\Logger;

/**
 * @SuppressWarnings("StaticAccess")
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testFatal()
    {
        $message = 'log message';
        $mock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('log')
            ->with($message, Logger::LOGLEVEL_FATAL);
        $mock->fatal($message);
    }

    public function testError()
    {
        $message = 'log message';
        $mock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('log')
            ->with($message, Logger::LOGLEVEL_ERROR);
        $mock->error($message);
    }

    public function testWarn()
    {
        $message = 'log message';
        $mock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('log')
            ->with($message, Logger::LOGLEVEL_WARN);
        $mock->warn($message);
    }

    public function testInfo()
    {
        $message = 'log message';
        $mock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('log')
            ->with($message, Logger::LOGLEVEL_INFO);
        $mock->info($message);
    }

    public function testDebug()
    {
        $message = 'log message';
        $mock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('log')
            ->with($message, Logger::LOGLEVEL_DEBUG);
        $mock->debug($message);
    }
}
