<?php

use \Suricate\Flash;

/**
 * @SuppressWarnings("StaticAccess")
 */
class FlashTest extends \PHPUnit\Framework\TestCase
{
    public function testReadWithoutSession()
    {
        new \Suricate\Suricate();
        $this->expectException(\Exception::class);
        Flash::read();
    }
    
    public function testReadWithSession()
    {
        new \Suricate\Suricate([], './tests/stubs/session.ini');

        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_INFO)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_ERROR)));
        
        Flash::write(Flash::TYPE_SUCCESS, "OK !");

        $this->assertEquals(1, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        Flash::write(Flash::TYPE_SUCCESS, "OK2 !");
        Flash::write(Flash::TYPE_SUCCESS, "OK3 !");
        $this->assertEquals(2, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_INFO)));
    }

    public function testRenderMessages()
    {
        new \Suricate\Suricate([], './tests/stubs/session.ini');
        Flash::write(Flash::TYPE_SUCCESS, "OK !");

        $this->assertEquals('<div class="alert alert-success">OK !</div>', Flash::renderMessages());
        $this->assertEquals('', Flash::renderMessages());
        Flash::write(Flash::TYPE_SUCCESS, "OK 1");
        Flash::write(Flash::TYPE_SUCCESS, "OK 2");
        $this->assertEquals('<div class="alert alert-success">OK 1<br/>OK 2</div>', Flash::renderMessages());
        Flash::write(Flash::TYPE_SUCCESS, "OK 1");
        Flash::write(Flash::TYPE_INFO, "INFO 2");
        $this->assertEquals('<div class="alert alert-success">OK 1</div><div class="alert alert-info">INFO 2</div>', Flash::renderMessages());

    }
}
