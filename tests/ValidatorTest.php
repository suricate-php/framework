<?php
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateTrue()
    {
        $test = true;

        $validator = new \Suricate\Validator($test);
        $validator->true("Not true");
        $this->assertSame(0, count($validator->getErrors()));
        $this->assertTrue($validator->pass());
        $this->assertFalse($validator->fails());

    }

    public function testValidateFalse()
    {
        $test = true;

        $validator = new \Suricate\Validator($test);
        $validator->false("Not false");
        $this->assertSame(1, count($validator->getErrors()));
        $this->assertFalse($validator->pass());
        $this->assertTrue($validator->fails());
        $this->assertSame("Not false", $validator->getErrors()[0]);
    }

    public function testValidateEqualTo()
    {
        $test = true;

        $validator = new \Suricate\Validator($test);
        $validator->equalTo(true, "Not equal to 1");
        $this->assertTrue($validator->pass());
    }

    public function testValidateIdenticalTo()
    {
        $test = true;

        $validator = new \Suricate\Validator($test);
        $validator->identicalTo("1", "Not identical to 1");
        $this->assertTrue($validator->fails());

        $validator = new \Suricate\Validator($test);
        $validator->identicalTo(1, "Not identical to 1");
        $this->assertTrue($validator->fails());
    }

    public function testValidateLessThan()
    {
        $test = 10;

        $validator = new \Suricate\Validator($test);
        $validator->lessThan(1, "Not less than 1");
        $this->assertTrue($validator->fails());
    }

    public function testValidateLessThanOrEqual()
    {
        $test = 10;

        $validator = new \Suricate\Validator($test);
        $validator->lessThanOrEqual(10, "Not <= than 10");
        $this->assertTrue($validator->pass());
    }

    public function testValidateGreaterThan()
    {
        $test = 10;

        $validator = new \Suricate\Validator($test);
        $validator->greaterThan(11, "Not greater than 11");
        $this->assertTrue($validator->fails());
    }

    public function testValidateGreaterThanOrEqual()
    {
        $test = 10;

        $validator = new \Suricate\Validator($test);
        $validator->greaterThanOrEqual(10, "Not >= than 10");
        $this->assertTrue($validator->pass());
    }

    public function testBlank()
    {
        $test = '';

        $validator = new \Suricate\Validator($test);
        $validator->blank("Not blank");
        $this->assertTrue($validator->pass());
    }

    public function testNull()
    {
        $test = null;

        $validator = new \Suricate\Validator($test);
        $validator->null("Not null");
        $this->assertTrue($validator->pass());

        $test = 0;

        $validator = new \Suricate\Validator($test);
        $validator->null("Not null");
        $this->assertFalse($validator->pass());
    }

    public function testType()
    {
        $test = [1, 2, 3];
        $validator = new \Suricate\Validator($test);
        $validator->type('array', "Not an array");
        $this->assertTrue($validator->pass());

        $test = true;
        $validator = new \Suricate\Validator($test);
        $validator->type('bool', "Not a bool");
        $this->assertTrue($validator->pass());

        $test = 1;
        $validator = new \Suricate\Validator($test);
        $validator->type('bool', "Not a bool");
        $this->assertTrue($validator->fails());
    }

    public function testAlnum()
    {
        $test = '!aa';
        $validator = new \Suricate\Validator($test);
        $validator->alnum('Not alphanumeric');
        $this->assertTrue($validator->fails());

        $test = 'aa123z';
        $validator = new \Suricate\Validator($test);
        $validator->alnum('Not alphanumeric');
        $this->assertTrue($validator->pass());
    }
}