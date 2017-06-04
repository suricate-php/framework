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

}
