<?php
class PageTest extends \PHPUnit\Framework\TestCase
{
    public function testLanguage()
    {
        $language = 'fr_FR';
        $page = new \Suricate\Page();
        $page->setLanguage($language);
        $this->assertSame($language, $page->getLanguage());
    }

    public function testEncoding()
    {
        $encoding = 'iso-8859-1';

        $page = new \Suricate\Page();
        $page->setEncoding($encoding);
        $this->assertSame($encoding, $page->getEncoding());
    }

    public function testTitle()
    {
        $title = 'My great webpage';

        $page = new \Suricate\Page();
        $page->setTitle($title);
        $this->assertSame($title, $page->getTitle());
    }

    public function testAddStylesheet()
    {
        $page = new \Suricate\Page();
        $page->addStylesheet('stylesheet-ref', '/my.css');

        $reflector = new ReflectionClass(get_class($page));
        $property = $reflector->getProperty('stylesheets');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($page), [
            'stylesheet-ref' => ['url' => '/my.css', 'media' => 'all']
        ]);
        $this->assertNotEquals($property->getValue($page), [
            'another-stylesheet' => ['url' => '/my-2.css', 'media' => 'all']
        ]);
    }

    public function testRender()
    {
        $page = new \Suricate\Page();
        $this->assertEquals(
            '<!DOCTYPE html>
<html lang="en">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
</body>
</html>',
            $page->render()
        );

        $page->setTitle('My Pageé');
        $this->assertEquals(
            '<!DOCTYPE html>
<html lang="en">
<head>
<title>My Page&eacute;</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
</body>
</html>',
            $page->render()
        );

        $page->addHtmlClass('class1');
        $page->addHtmlClass('class2');

        $this->assertEquals(
            '<!DOCTYPE html>
<html lang="en" class="class1 class2">
<head>
<title>My Page&eacute;</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
</body>
</html>',
            $page->render()
        );

        $page->addScript('script-id', 'http://scripturl.com');
        $page->addRss('rss-id', 'http://rssurl.com', 'RSS is not dead !');
        $page->addStylesheet('css-id', 'http://cssurl.com');
        $page->addMeta('metaname', 'metacontent');
        $page->addMetaProperty('metapropertyname', 'metapropertycontent');
        $page->addMetaLink('metalinkname', 'metalinktype', 'metalinkhref');

        $this->assertEquals(
            '<!DOCTYPE html>
<html lang="en" class="class1 class2">
<head>
<title>My Page&eacute;</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="metaname" content="metacontent"/>
<meta property="metapropertyname" content="metapropertycontent"/>
<link rel="metalinktype" href="metalinkhref"/>
<link rel="stylesheet" id="css-id" href="http://cssurl.com" type="text/css" media="all"/>
<script type="text/javascript" src="http://scripturl.com"></script>
<link rel="alternate" id="rss-id" href="http://rssurl.com" type="application/rss+xml" media="RSS is not dead !"/>
</head>
<body>
</body>
</html>',
            $page->render()
        );
    }
}
