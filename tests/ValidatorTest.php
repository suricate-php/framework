<?php
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateTrueFalse()
    {
        $test = true;
        $validator = new \Suricate\Validator($test);

        $validator->true("Not true");
        $this->assertSame(0, count($validator->getErrors()));
        $this->assertTrue($validator->pass());
        $this->assertFalse($validator->fails());

        $validator->false("Not false");
        $this->assertSame(1, count($validator->getErrors()));
        $this->assertFalse($validator->pass());
        $this->assertTrue($validator->fails());
        $this->assertSame("Not false", $validator->getErrors()[0]);
        
    }
}