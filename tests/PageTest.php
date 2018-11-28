<?php
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
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

        $stylesheets = Assert::readAttribute($page, 'stylesheets');
        

        $this->assertEquals(
            $stylesheets, 
            [
                'stylesheet-ref' => ['url' => '/my.css', 'media' => 'all'],
            ]);
        $this->assertNotEquals(
            $stylesheets, [
                'another-stylesheet' => ['url' => '/my-2.css', 'media' => 'all'],
            ]);
    }

}
