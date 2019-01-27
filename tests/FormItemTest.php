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

    public function testInputText()
    {
        $res = FormItem::text('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="text" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputPassword()
    {
        $res = FormItem::password('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="password" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputNumber()
    {
        $res = FormItem::number('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="number" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputHidden()
    {
        $res = FormItem::hidden('my-input-name', "accentué");
        $this->assertEquals(
            '<input type="hidden" name="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputSubmit()
    {
        $res = FormItem::submit('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="submit" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputTel()
    {
        $res = FormItem::tel('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="tel" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputUrl()
    {
        $res = FormItem::url('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="url" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputEmail()
    {
        $res = FormItem::email('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="email" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputSearch()
    {
        $res = FormItem::search('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="search" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputDate()
    {
        $res = FormItem::date('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="date" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputDateTime()
    {
        $res = FormItem::datetime('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="datetime" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputTime()
    {
        $res = FormItem::time('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="time" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }
}
