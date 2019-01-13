<?php
class PageTest extends PHPUnit_Framework_TestCase
{
    public function testLanguage()
    {
        $page = new \Suricate\Page();
        $page->setLanguage('fr_FR');
        $this->assertAttributeEquals('fr_FR', 'language', $page);
    }

    public function testEncoding()
    {
        $page = new \Suricate\Page();
        $page->setEncoding('iso-8859-1');
        $this->assertAttributeEquals('iso-8859-1', 'encoding', $page);
    }

    public function testTitle()
    {
        $page = new \Suricate\Page();
        $page->setTitle('My great webpage');
        $this->assertAttributeEquals('My great webpage', 'title', $page);
    }

    public function testAddStylesheet()
    {
        $page = new \Suricate\Page();
        $page->addStylesheet('stylesheet-ref', '/my.css');

        $reflector = new ReflectionClass(get_class($page));
        $property = $reflector->getProperty('stylesheets');
        $stylesheets = $property->setAccessible(true);

        $this->assertEquals(
            $property->getValue($page),
            [
                'stylesheet-ref' => ['url' => '/my.css', 'media' => 'all'],
            ]
        );
        $this->assertNotEquals(
            $property->getValue($page),
            [
                'another-stylesheet' => ['url' => '/my-2.css', 'media' => 'all'],
            ]
        );
    }
}
