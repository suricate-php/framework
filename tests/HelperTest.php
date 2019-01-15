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

}
