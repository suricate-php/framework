<?php
class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $router = new \Suricate\Router();
        $this->assertAttributeEquals(array(), 'routes', $router);
        $this->assertEquals(\Suricate\Suricate::Response(), $router->getResponse());
    }
}