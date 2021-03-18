<?php

use Suricate\FormItem;

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

    public function testIsset()
    {
        $handler = new FormItem(['src' => 'http://']);
        $this->assertTrue(isset($handler->src));
        $this->assertFalse(isset($handler->value));
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

    public function testInputButton()
    {
        $res = FormItem::button('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="button" name="my-input-name" id="my-input-name" value="accentu&eacute;"/>',
            $res
        );
    }

    public function testInputCheckbox()
    {
        $res = FormItem::checkbox('my-input-name', 2, true, 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="checkbox" name="my-input-name" id="my-input-name" value="2" checked="checked"/>',
            $res
        );

        $res = FormItem::checkbox('my-input-name', 2, false, 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="checkbox" name="my-input-name" id="my-input-name" value="2"/>',
            $res
        );
    }

    public function testInputRadio()
    {
        $res = FormItem::radio('my-input-name', [1, 2, 3], 2, 'Mon input');
        $this->assertEquals(
            '<label>Mon input</label><div class="radio-list"><div class="radio-item"><label for="my-input-name-0">1</label><input type="radio" name="my-input-name" id="my-input-name-0" value="0"/></div><div class="radio-item"><label for="my-input-name-1">2</label><input type="radio" name="my-input-name" id="my-input-name-1" value="1"/></div><div class="radio-item"><label for="my-input-name-2">3</label><input type="radio" name="my-input-name" id="my-input-name-2" value="2" checked="checked"/></div></div>',
            $res
        );
    }

    public function testInputReset()
    {
        $res = FormItem::reset('val');
        $this->assertEquals('<input type="reset" value="val"/>', $res);
    }

    public function testInputSelect()
    {
        $res = FormItem::select('my-input-name', [1, 2, 3], 2, 'Mon input');
        $this->assertEquals(
            '<label>Mon input</label><select name="my-input-name"><option value="0">1</option><option value="1">2</option><option value="2" selected>3</option></select>',
            $res
        );

        $res = FormItem::select(
            'my-input-name',
            ["é\"" => ['a', 'b'], 2, 3 => ['c', 'd']],
            2,
            'Mon input'
        );
        $this->assertEquals(
            '<label>Mon input</label><select name="my-input-name"><optgroup label="&eacute;&quot;"><option value="0">a</option><option value="1">b</option></optgroup><option value="0">2</option><optgroup label="3"><option value="0">c</option><option value="1">d</option></optgroup></select>',
            $res
        );

        $res = FormItem::select(
            'my-input-name',
            [
                "é\"" => ['a', 'b'],
                2,
                3 => ['c' => 'option c', 'd' => 'option d']
            ],
            'c',
            'Mon input'
        );
        $this->assertEquals(
            '<label>Mon input</label><select name="my-input-name"><optgroup label="&eacute;&quot;"><option value="0">a</option><option value="1">b</option></optgroup><option value="0">2</option><optgroup label="3"><option value="c" selected>option c</option><option value="d">option d</option></optgroup></select>',
            $res
        );

        $res = FormItem::select(
            'my-input-name',
            [1, 2, 3],
            [2, 1],
            'Mon input'
        );
        $this->assertEquals(
            '<label>Mon input</label><select name="my-input-name"><option value="0">1</option><option value="1" selected>2</option><option value="2" selected>3</option></select>',
            $res
        );
    }

    public function testInputFile()
    {
        $res = FormItem::file('my-input-name', "Mon input");
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><input type="file" name="my-input-name" id="my-input-name"/>',
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

    public function testInputImage()
    {
        $res = FormItem::image(
            'my-input-name',
            "https://gsuite.google.com/img/icons/product-lockup.png"
        );
        $this->assertEquals(
            '<input type="image" name="my-input-name" src="https://gsuite.google.com/img/icons/product-lockup.png"/>',
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

    public function testInputTextarea()
    {
        $res = FormItem::textarea('my-input-name', "accentué", 'Mon input');
        $this->assertEquals(
            '<label for="my-input-name">Mon input</label><textarea name="my-input-name" id="my-input-name">accentué</textarea>',
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
