<?php

use \Suricate\FormItem;

/**
 * @SuppressWarnings("StaticAccess")
 */
class FormItemTest extends \PHPUnit\Framework\TestCase
{
    public function testInput()
    {
        $res = FormItem::input('text', 'myInput', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="myInput">Mon input</label><input type="text" name="myInput" id="myInput" value="accentu&eacute;"/>',
            $res
        );

        $res = FormItem::input('text', 'myInput', null, 'Mon input');
        $this->assertEquals(
            '<label for="myInput">Mon input</label><input type="text" name="myInput" id="myInput"/>',
            $res
        );

        $res = FormItem::input('text', 'myInput', "testval");
        $this->assertEquals(
            '<input type="text" name="myInput" value="testval"/>',
            $res
        );
    }
}
