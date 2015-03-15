<?php
class HelperTest extends PHPUnit_Framework_TestCase {
    public function testDataGetKeyIsNull()
    {
        $dataArr = array('test' => 42);

        $this->assertEquals($dataArr, dataGet($dataArr, null));
    }

}
