<?php
class CollectionTest extends PHPUnit_Framework_TestCase {
    public function testConstruct()
    {
        $arr = array(1,2,3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals($arr, $collection->getItems());
    }

    public function testIsEmpty()
    {
        $arr = array();
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(true, $collection->isEmpty());
    }

    public function testUnique()
    {
        
    }

    public function testHas()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(true, $collection->has('b'));
        $this->assertEquals(false, $collection->has('d'));
    }
}