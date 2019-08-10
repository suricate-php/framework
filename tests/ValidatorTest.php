<?php

/**
 * @SuppressWarnings("TooManyPublicMethods")
 **/
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

        $test = 1.0;
        $validator = new \Suricate\Validator($test);
        $validator->type('int', "Not an int");
        $this->assertTrue($validator->fails());

        $test = 1;
        $validator = new \Suricate\Validator($test);
        $validator->type('float', "Not a float");
        $this->assertTrue($validator->fails());

        $test = 1;
        $validator = new \Suricate\Validator($test);
        $validator->type('numeric', "Not a numeric");
        $this->assertTrue($validator->pass());

        $test = 1.2;
        $validator = new \Suricate\Validator($test);
        $validator->type('numeric', "Not a numeric");
        $this->assertTrue($validator->pass());

        $test = new stdClass();
        $validator = new \Suricate\Validator($test);
        $validator->type('object', "Not an object ");
        $this->assertTrue($validator->pass());

        $test = "tt";
        $validator = new \Suricate\Validator($test);
        $validator->type('string', "Not a string ");
        $this->assertTrue($validator->pass());
    }

    public function testEmail()
    {
        $test = 'mathieu@lesniak.fr';
        $validator = new \Suricate\Validator($test);
        $validator->email("Not an email");
        $this->assertTrue($validator->pass());

        $test = 'mathieu@lesniak';
        $validator = new \Suricate\Validator($test);
        $validator->email("Not an email");
        $this->assertFalse($validator->pass());

        $test = 'mathieu@lesniak.co.uk';
        $validator = new \Suricate\Validator($test);
        $validator->email("Not an email");
        $this->assertTrue($validator->pass());
    }

    public function testUrl()
    {
        $test = 'https://www.google.com/search?index=1';
        $validator = new \Suricate\Validator($test);
        $validator->url("Not an URL");
        $this->assertTrue($validator->pass());

        $test = 'www.google.com/search?index=1';
        $validator = new \Suricate\Validator($test);
        $validator->url("Not an URL");
        $this->assertTrue($validator->fails());

        $test = 'gopher://www.google.com/search?index=1';
        $validator = new \Suricate\Validator($test);
        $validator->url("Not an URL");
        $this->assertTrue($validator->pass());
    }

    public function testIP()
    {
        $test = '8.8.8.8';
        $validator = new \Suricate\Validator($test);
        $validator->ip("Not an IP");
        $this->assertTrue($validator->pass());

        $test = '2001:0db8:0000:85a3:0000:0000:ac1f:8001';
        $validator = new \Suricate\Validator($test);
        $validator->ip("Not an IP");
        $this->assertTrue($validator->pass());
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
