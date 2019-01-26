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
        
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK !");

        $this->assertEquals(1, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK2 !");
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK3 !");
        $this->assertEquals(2, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_SUCCESS)));
        $this->assertEquals(0, count(Flash::getMessages(Flash::TYPE_INFO)));

        $this->expectException(\InvalidArgumentException::class);
        Flash::writeMessage('unknown type', "OK 1");
    }

    public function testRenderMessages()
    {
        new \Suricate\Suricate([], './tests/stubs/session.ini');
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK !");

        $this->assertEquals('<div class="alert alert-success">OK !</div>', Flash::renderMessages());
        $this->assertEquals('', Flash::renderMessages());
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK 1");
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK 2");
        $this->assertEquals('<div class="alert alert-success">OK 1<br/>OK 2</div>', Flash::renderMessages());
        Flash::writeMessage(Flash::TYPE_SUCCESS, "OK 1");
        Flash::writeMessage(Flash::TYPE_INFO, "INFO 2");
        $this->assertEquals('<div class="alert alert-success">OK 1</div><div class="alert alert-info">INFO 2</div>', Flash::renderMessages());
    }

    public function testData()
    {
        new \Suricate\Suricate([], './tests/stubs/session.ini');
        $myObj = new \stdClass();
        $myObj->property = 1;
        $myObj->otherProperty = "1";

        $this->assertNull(Flash::getData('myKey'));

        Flash::writeData('myKey', $myObj);
        $this->assertEquals($myObj, Flash::getData('myKey'));
    }
}
