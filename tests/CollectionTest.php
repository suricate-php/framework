<?php
class CollectionTest extends \PHPUnit\Framework\TestCase
{
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
        $arr = array(1, 2, 1, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals([0 => 1, 1 => 2, 3 => 3], $collection->unique()->getItems());
    }

    public function testCount()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(3, $collection->count());
    }

    public function testGetIterator()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(new \ArrayIterator($arr), $collection->getIterator());
    }

    public function testPaginate()
    {
        $arr = array(1, 2, 3, 4, 5);
        $collection = new \Suricate\Collection($arr);
        

        $collection->paginate(1, 3);
        $this->assertEquals(['page' => 3, 'nbItems' => 5, 'nbPages' => 5], $collection->pagination);
        $this->assertEquals([3], $collection->getItems());
    }

    public function testFirst()
    {
        $arr = array(4, 5, 6);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(4, $collection->first());
    }

    public function testLast()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(3, $collection->last());


        $collection = new \Suricate\Collection();
        $this->assertEquals(null, $collection->last());
    }

    public function testSum()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(6, $collection->sum());

        $arr = [
            ['id' => 10, 'name' => 'azerty'],
            ['id' => 2, 'name' => 'qsdfg'],
            ['id' => 3, 'name' => 'wxcvbn']
        ];
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(15, $collection->sum('id'));

    }

    public function testHas()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(true, $collection->has('b'));
        $this->assertEquals(false, $collection->has('d'));
    }

    public function testKeys()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
    }

    public function testPrepend()
    {
        $arr = [4, 5, 6];
        $collection = new \Suricate\Collection($arr);
        $collection->prepend(99);
        $this->assertEquals([99, 4, 5, 6], $collection->getItems());
    }
    
    public function testPut()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $collection->put('z', 99);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'z' => 99], $collection->getItems());
    }

    public function testShift()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $shifted = $collection->shift();
        $this->assertEquals(1, $shifted);
        $this->assertEquals(['b' => 2, 'c' => 3], $collection->getItems());
    }

    public function testPop()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $popped = $collection->pop();
        $this->assertEquals(3, $popped);
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->getItems());
    }

    public function testReverse()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $reversed = $collection->reverse();
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $reversed->getItems());
    }

    public function testReduce()
    {
        // Basic reduce
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection = new \Suricate\Collection($arr);
        $callback = function ($carry, $item) {
            $carry += $item;

            return $carry;
        };
        $reduced = $collection->reduce($callback);
    
        $this->assertEquals(6, $reduced);

        // reduce with initial value
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection = new \Suricate\Collection($arr);
        $callback = function ($carry, $item) {
            $carry += $item;

            return $carry;
        };
        $reduced = $collection->reduce($callback, 100);
    
        $this->assertEquals(106, $reduced);
    }

    public function testSlice()
    {
        // Basic slice
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $collection = new \Suricate\Collection($arr);
        $sliced = $collection->slice(1, 2);
    
        $this->assertEquals(['b' => 2, 'c' => 3], $sliced->getItems());

        // Slice with numeric keys
        $arr = [1, 2, 3];
        $collection = new \Suricate\Collection($arr);
        $sliced = $collection->slice(1, 2);
    
        $this->assertEquals([2, 3], $sliced->getItems());

        // Slice preserve keys
        $arr = [1, 2, 3];
        $collection = new \Suricate\Collection($arr);
        $sliced = $collection->slice(1, 2, true);
    
        $this->assertEquals([1 => 2, 2 => 3], $sliced->getItems());
    }
    
    public function testTake()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $taken = $collection->take(2);
        $this->assertEquals(['a' => 1, 'b' => 2], $taken->getItems());
    }

    public function testFilter()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(
            ['b' => 2],
            $collection->filter(function ($value) {
                return ($value % 2) === 0;
            })->getItems()
        );
    }

    public function testPush()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals([1, 2, 3, 4], $collection->push(4)->getItems());
    }

    public function testGetValuesFor()
    {
        $arr = [
            ['id' => 1, 'name' => 'azerty'],
            ['id' => 2, 'name' => 'qsdfg'],
            ['id' => 3, 'name' => 'wxcvbn']
        ];
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals([
            'azerty',
            'qsdfg',
            'wxcvbn'],
            $collection->getValuesFor('name')
        );
    }

    public function testSearch()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals('c', $collection->search('3'));
        $this->assertEquals('c', $collection->search(3, true));
        $this->assertFalse($collection->search(22));
        $this->assertFalse($collection->search('3', true));
    }
}
