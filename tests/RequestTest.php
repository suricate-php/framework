<?php
class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testParse()
    {
        $_SERVER['REQUEST_URI'] = '/myDir/myPage.php?arg=1&argtwo=2';
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $request = new Suricate\Request();
        $request->parse();

        $this->assertEquals('/myDir/myPage.php?arg=1&argtwo=2', $request->getRequestUri());
        $this->assertEquals('/myDir/myPage.php', $request->getPath());
        $this->assertEquals('arg=1&argtwo=2', $request->getQuery());
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertNotEquals('GET', $request->getMethod());
    
        unset($_SERVER['REQUEST_METHOD']);
        $_POST['_method'] = 'DELETE';

        $request->parse();
        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertNotEquals('PUT', $request->getMethod());

    }

    public function testBody()
    {
        $request = new Suricate\Request();
        $request->setBody('<h1>Hello world</h1>');

        $this->assertEquals('<h1>Hello world</h1>', $request->getBody());
    }

    public function testParams()
    {
        $_POST['test']  = "myVar";
        $_GET['getVar'] = 1;

        $request = new Suricate\Request();
        $this->assertEquals('myVar', $request->getPostParam('test'));
        $this->assertNotEquals('tttt', $request->getPostParam('test'));
        $this->assertEquals('defaultValue', $request->getPostParam("unknown-var", 'defaultValue'));

        $this->assertTrue($request->hasParam('test'));
        $this->assertFalse($request->hasParam('unknown-var'));
        $this->assertTrue($request->hasParam('getVar'));

        $this->assertEquals(1, $request->getParam('getVar'));
        $this->assertEquals('myVar', $request->getParam('test'));
        $this->assertNotEquals('tttt', $request->getParam('test'));
        $this->assertEquals('defaultValue', $request->getParam("unknown-var", 'defaultValue'));
    }

    public function testHeaders()
    {
        $request = new Suricate\Request();
        $request->setHeaders(array('my-header' => 'myValue'));

        $this->assertEquals(array('my-header' => 'myValue'), $request->getHeaders());

        $request->addHeader('my-header', 'myNewValue');
        $this->assertEquals(array('my-header' => 'myNewValue'), $request->getHeaders());

        $request->setHeaders(array());
        $this->assertEquals(array(), $request->getHeaders());

        $request->setContentType('text/xml');
        $this->assertEquals(array('Content-type' =>'text/xml'), $request->getHeaders());

        $request->setContentType('text/xml', 'utf8');
        $this->assertEquals(array('Content-type' =>'text/xml; charset=utf8'), $request->getHeaders());
    }

    public function testMethod()
    {
        $request = new Suricate\Request();
        $request->setMethod('POST');

        $this->assertEquals('POST', $request->getMethod());
        try {
            $request->setMethod('POST2');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertEquals('Invalid HTTP Method POST2', $e->getMessage());
        }
    }

    public function testHttp()
    {
        $request = new Suricate\Request();
        
        $request->setHttpCode(200);
        $this->assertTrue($request->isOk());
        $this->assertFalse($request->isClientError());
        $this->assertFalse($request->isServerError());
        $this->assertFalse($request->isRedirect());

        $request->setHttpCode(302);
        $this->assertFalse($request->isOk());
        $this->assertFalse($request->isClientError());
        $this->assertFalse($request->isServerError());
        $this->assertTrue($request->isRedirect());

        $request->setHttpCode(404);
        $this->assertEquals(404, $request->getHttpCode());
        $this->assertFalse($request->isOk());
        $this->assertTrue($request->isClientError());
        $this->assertFalse($request->isServerError());
        $this->assertFalse($request->isRedirect());

        $request->setHttpCode(503);
        $this->assertFalse($request->isOk());
        $this->assertFalse($request->isClientError());
        $this->assertTrue($request->isServerError());
        $this->assertFalse($request->isRedirect());

        $request->setHttpCode(404);
        $reflection = new \ReflectionClass(get_class($request));
        $method = $reflection->getMethod('getStringForHttpCode');
        $method->setAccessible(true);
        $this->assertEquals('404 Not Found', $method->invoke($request));

        $request->setHttpCode(600);
        $reflection = new \ReflectionClass(get_class($request));
        $method = $reflection->getMethod('getStringForHttpCode');
        $method->setAccessible(true);
        $this->assertEquals(null, $method->invoke($request));
    }

    public function testUrl()
    {
        $request = new Suricate\Request();
        $request->setUrl('https://www.google.fr');
        $this->assertEquals('https://www.google.fr', $request->getUrl());
        $this->assertNotEquals('https://www.yahoo.fr', $request->getUrl());   
    }
}