<?php
class PageTest extends PHPUnit_Framework_TestCase
{
    public function testLanguage()
    {
        $page = new \Suricate\Page();
        $page->setLanguage('fr_FR');
        $this->assertAttributeEquals('fr_FR', 'language', $page);
    }
}