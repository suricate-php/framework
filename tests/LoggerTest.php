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

        /** @scrutinizer ignore-call */
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

        /** @scrutinizer ignore-call */
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

        /** @scrutinizer ignore-call */
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

        /** @scrutinizer ignore-call */
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

        /** @scrutinizer ignore-call */
        $mock->debug($message);
    }
}
