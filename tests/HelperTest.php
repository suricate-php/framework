<?php

/**
 * @SuppressWarnings("TooManyPublicMethods")
 **/
class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testDataGetKeyIsNull()
    {
        $dataArr = array('test' => 42);

        $this->assertEquals($dataArr, dataGet($dataArr, null));
        $this->assertEquals(42, dataGet($dataArr, 'test'));
        $this->assertEquals(43, dataGet($dataArr, 'test-unknown', 43));
    }

    public function testP()
    {
        $this->expectOutputString(
            "<pre>\n1\n</pre><pre>\nArray\n(\n    [a] => 1\n)\n\n</pre>"
        );
        _p(1);

        _p(['a' => 1]);
    }

    public function testHead()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->assertSame(1, head($arr));
    }

    public function testLast()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->assertSame(3, last($arr));
    }

    public function testFlatten()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => ['s1' => 3, 's2' => 4]];
        $this->assertSame([1, 2, 3, 4], flatten($arr));
    }

    public function testValue()
    {
        $value = 1;
        $this->assertSame(1, value($value));

        $value = function () {
            return 2;
        };
        $this->assertSame(2, value($value));
    }

    public function testCamelCase()
    {
        $str = 'this_is_a_test';
        $this->assertSame('ThisIsATest', camelCase($str));
    }

    public function testContains()
    {
        $haystack = 'this is not a long sentence';
        $this->assertTrue(contains($haystack, 'not'));

        $this->assertTrue(contains($haystack, ['these', 'short', 'sentence']));
        $this->assertFalse(contains($haystack, ['these', 'short', 'wordlist']));
    }

    public function testStartsWith()
    {
        $haystack = 'this is not a long sentence';
        $this->assertTrue(startsWith($haystack, 'this'));

        $this->assertTrue(
            startsWith($haystack, ['these', 'short', 'sentence', 'this'])
        );
        $this->assertFalse(
            startsWith($haystack, ['these', 'short', 'wordlist'])
        );
    }

    public function testEndsWith()
    {
        $haystack = 'this is not a long sentence';
        $this->assertTrue(endsWith($haystack, 'sentence'));

        $this->assertTrue(
            endsWith($haystack, ['these', 'short', 'sentence', 'this'])
        );
        $this->assertFalse(endsWith($haystack, ['these', 'short', 'wordlist']));
    }

    public function testWordLimit()
    {
        $haystack = 'this is not a long sentence';
        $this->assertEquals('this...', wordLimit($haystack, 4));
        $this->assertEquals('this...', wordLimit($haystack, 5));
        $this->assertEquals('this is...', wordLimit($haystack, 8));
        $this->assertEquals('this??', wordLimit($haystack, 4, '??'));
        $this->assertEquals(
            'this is not a long sentence',
            wordLimit($haystack, 100)
        );
    }

    public function testApp()
    {
        $this->assertInstanceOf(\Suricate\App::class, app());
    }

    public function testSlug()
    {
        $str = 'This is a long sentence in an URL';
        $this->assertEquals(slug($str), 'this-is-a-long-sentence-in-an-url');
        $str = 'Une chaîne accentuée_et_des underscores';
        $this->assertEquals(
            slug($str),
            'une-chaine-accentueeetdes-underscores'
        );
    }
    public function testNiceTime()
    {
        $time = time() - 45;
        $this->assertEquals(niceTime($time), 'il y a moins d\'une minute.');
        $time = time() - 115;
        $this->assertEquals(niceTime($time), 'il y a environ une minute.');
        $time = time() - 60 * 40;
        $this->assertEquals(niceTime($time), 'il y a 40 minutes.');
        $time = time() - 60 * 65;
        $this->assertEquals(niceTime($time), 'il y a environ une heure.');
        $time = time() - 60 * 60 * 14;
        $this->assertEquals(niceTime($time), 'il y a environ 14 heures.');
        $time = time() - 60 * 64 * 30;
        $this->assertEquals(niceTime($time), 'hier.');
        $time = time() - 60 * 60 * 24 * 17;
        $this->assertEquals(niceTime($time), 'il y a 17 jours.');
        $time = time() - 60 * 60 * 24 * 65;
        $this->assertEquals(niceTime($time), 'il y a 2 mois.');
        $time = time() - 60 * 60 * 24 * 375;
        $this->assertEquals(niceTime($time), 'il y a plus d\'un an.');
        $time = time() - 60 * 60 * 24 * 800;
        $this->assertEquals(niceTime($time), 'il y a plus de 2 ans.');
    }
}
