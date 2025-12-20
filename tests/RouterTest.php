<?php
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test constructor
     *
     * @return void
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function testConstruct()
    {
        $router = new \Suricate\Router();
        $this->assertEquals([], $router->getRoutes());
        $this->assertEquals(
            \Suricate\Suricate::Response(),
            $router->getResponse()
        );
    }
}
