<?php
namespace Suricate;

class Collection implements  \Iterator, \Countable, \ArrayAccess, Interfaces\ICollection
{
    
    protected $items            = array();
    protected $mapping          = array(); // to be deprecated ?

    private $sortField;                     // to be deprecated
    private $sortOrder;                     // to be deprecated

    protected $itemOffset        = 0;
    protected $iteratorPosition  = 0;

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    

    public function purgeItems()
    {
        $this->items        = array();
        $this->mapping      = array();
        $this->itemOffset   = 0;
    }

// To be deprecated
/*
    public function sort($field, $order)
    {
        $this->sortField   = $field;
        $this->sortOrder   = $order;

        uasort($this->items, array($this, 'sortFunction'));

        return $this;
    }
*/
    public function getPossibleValuesFor($args, $withMapping = true)
    {
        if (!is_array($args)) {
            $args = array('format' => '%s', 'data' => array($args));
        }

        if ($withMapping) {
            $class = $this::ITEM_TYPE;
            $dummyItem = new $class();
            $mappingKey = $dummyItem::TABLE_INDEX;
        }
        $values = array();
        foreach ($this->items as $key => $item) {
            $itemValues = array();
            foreach ($args['data'] as $arg) {
                $itemValues[] = $item->$arg;
            }
            $arrayKey = ( $withMapping ) ? $item->$mappingKey: $key;
            $values[$arrayKey] = vsprintf($args['format'], $itemValues);
        }

        return $values;
    }

    public function getValuesFor($name)
    {
        $values = array();
        foreach ($this->items as $item) {
            $values[] = $item->$name;
        }

        return $values;
    }

    public function getItems()
    {
        return $this->items;
    }


    private function sortFunction($a, $b)
    {
        $first  = $this->cleanStr($a->{$this->sortField});
        $second = $this->cleanStr($b->{$this->sortField});

        if ($first === $second) {
            return 0;
        }

        if ($this->sortOrder == self::SORT_ASC) {
            return ($first < $second) ? -1 : +1;
        } else {
            return ($first < $second) ? +1 : -1;
        }
    }

    public function addItemLink($linkId)
    {
        $this->items[$this->itemOffset] = $linkId;
        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $linkId;

        $this->itemOffset++;
    }

    

    public function getItemFromKey($key)
    {
        $invertedMapping = array_flip($this->mapping);
        if (isset($invertedMapping[$key])) {
            return $this->items[$invertedMapping[$key]];
        }
    }


    // Implementation of Countable Interface
    public function count()
    {
        return count($this->items);
    }

    // Implementation of Iterator Interface
    public function current()
    {
        return $this->offsetGet($this->iteratorPosition);
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function key()
    {
        return $this->iteratorPosition;
    }

    public function valid()
    {
        return isset($this->items[$this->iteratorPosition]);
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    // Implementation of ArrayAccess Interface
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        $item =isset($this->items[$offset]) ? $this->items[$offset] : null;
        if (gettype($item) == 'object' || $item == null) {
            return $item;
        } else {
            // Lazy load
            $itemType = $this::ITEM_TYPE;
            $itemToLoad = new $itemType;
            $itemToLoad->load($this->items[$offset]);

            $this->items[$offset] = $itemToLoad;

            return $this->items[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    private function cleanStr($str)
    {

        $str = mb_strtolower($str, 'utf-8');
        $str = strtr(
            $str,
            array(
                'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'a'=>'a', 'a'=>'a', 'a'=>'a', 'ç'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'd'=>'d', 'd'=>'d', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'g'=>'g', 'g'=>'g', 'g'=>'g', 'h'=>'h', 'h'=>'h', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', '?'=>'i', 'j'=>'j', 'k'=>'k', '?'=>'k', 'l'=>'l', 'l'=>'l', 'l'=>'l', '?'=>'l', 'l'=>'l', 'ñ'=>'n', 'n'=>'n', 'n'=>'n', 'n'=>'n', '?'=>'n', '?'=>'n', 'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'o'=>'o', 'o'=>'o', 'o'=>'o', 'œ'=>'o', 'ø'=>'o', 'r'=>'r', 'r'=>'r', 's'=>'s', 's'=>'s', 's'=>'s', 'š'=>'s', '?'=>'s', 't'=>'t', 't'=>'t', 't'=>'t', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'w'=>'w', 'ý'=>'y', 'ÿ'=>'y', 'y'=>'y', 'z'=>'z', 'z'=>'z', 'ž'=>'z'
            )
        );

        return $str;
    }

    // to be deprecated
    public function getSlice($start, $nbItems = null)
    {
        return array_slice($this->items, $start, $nbItems, true);
    }

    // to be deprecated
    public function getFirstItem()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    // to be deprecated
    public function getRandom($nb = 1)
    {
        $keys = (array) array_rand($this->items, $nb);
        $result = array();
        foreach ($keys as $currentKey) {
            $result[$currentKey] = $this->items[$currentKey];
        }

        return $result;
    }

    // Helpers
    public function first()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    public function last()
    {
        if (count($this->items)) {
            return end($this->items);
        } else {
            return null;
        }
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    public function random($nbItems = 1)
    {
        if ($this->isEmpty()) {
            return null;
        }

        $keys = array_rand($this->items, $nbItems);

        if (is_array($keys)) {
            return array_intersect_key($this->items, array_flip($keys));
        } else {
            return $this->items[$keys];
        }
    }

    public function shuffle()
    {
        shuffle($this->items);

        return $this;
    }

    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    public function unique()
    {
        return new static(array_unique($this->items));
    }

    public function sort(\Closure $closure)
    {
        uasort($this->items, $closure);

        return $this;
    }

    public function filter(\Closure $closure)
    {
        return new static(array_filter($this->items, $callback));
    }

    public function has($key)
    {

    }

    public function prepend($item)
    {
        array_unshift($this->items, $item);

        return $this;
    }

    public function push($item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function pop()
    {
        return array_pop($this->items);
    }

    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }
}
