<?php
class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testIsDebug()
    {
        $app = new \Suricate\App();
        $app->mode = \Suricate\App::DEBUG_MODE;

        $this->assertTrue($app->isDebug());
    }

    public function testIsDevelopment()
    {
        $app = new \Suricate\App();
        $app->mode = \Suricate\App::DEVELOPMENT_MODE;

        $this->assertTrue($app->isDevelopment());
    }

    public function testIsPrelive()
    {
        $app = new \Suricate\App();
        $app->mode = \Suricate\App::PRELIVE_MODE;

        $this->assertTrue($app->isPrelive());
    }

    public function testIsProduction()
    {
        $app = new \Suricate\App();
        $app->mode = \Suricate\App::PRODUCTION_MODE;

        $this->assertTrue($app->isProduction());
    }
}
