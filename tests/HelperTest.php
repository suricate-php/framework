<?php
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
        $this->expectOutputString("<pre>\n1\n</pre><pre>\nArray\n(\n    [a] => 1\n)\n\n</pre>");
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

        $value = function() {
            return 2;
        };
        $this->assertSame(2, value($value));
    }

    function testCamelCase()
    {
        $str = 'this_is_a_test';
        $this->assertSame('ThisIsATest', camelCase($str));

    }
}
