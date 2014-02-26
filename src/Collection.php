<?php
namespace Fwk;

class Collection implements  \Iterator, \Countable, \ArrayAccess, Interfaces\ICollection
{
    const TABLE_NAME            = '';           // Name of SQL table containing items
    const ITEM_TYPE             = '';           // Class of items in collection
    const PARENT_ID_NAME        = 'parent_id';  // Name of the field referencing to parent_id
    const PARENT_OBJECT_TYPE    = '';           // Parent object type

    protected $parentId;                       // Id of the parent
    protected $parentFilterName;                // Name of field used for filtering
    protected $parentFilterType;                // Value of filter

    protected $items            = array();
    protected $mapping          = array();

    private $sortField;
    private $sortOrder;

    private $itemOffset        = 0;
    private $iteratorPosition  = 0;

    /**
     * Load entire table into collection
     * @return Collection Loaded collection
     */
    public static function loadAll()
    {
        $calledClass    = get_called_class();
        $collection     = new $calledClass;

        $sql            = '';
        $sqlParams      = array();

        $sql  = "SELECT *";
        $sql .= "   FROM `" . $collection::TABLE_NAME . "`";

        if ($collection->parentFilterType !== '' && $collection->parentFilterType != null) {
            $sql .= "WHERE " . $collection->parentFilterName . "=:type";
            $sqlParams['type'] = $collection->parentFilterType;
        }

        $results = Fwk::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $collection::ITEM_TYPE;
                $collection->addItem($itemName::buildFromArray($currentResult));
            }
        }

        return $collection;
    }

    /**
     * Static wrapper for loadFromSql
     * @param  string     $sql       SQL Statement
     * @param  array      $sqlParams SQL Parameters
     * @return Collection Loaded collection
     */
    public static function buildFromSql($sql, $sqlParams = array())
    {
        $calledClass = get_called_class();
        $collection = new $calledClass;

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    public function loadFromSql($sql, $sqlParams = array())
    {
        $results = Fwk::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $this::ITEM_TYPE;
                $this->addItem($itemName::buildFromArray($currentResult));
            }
        }

        return $this;
    }

    public function lazyLoadFromSql($sql, $sqlParams = array())
    {
        $results = Fwk::Database()
            ->query($sql, $sqlParams)
            ->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $this->addItemLink(current($currentResult));
            }
        }

        return $this;
    }

    public static function loadForParentId($parentId)
    {
        $calledClass   = get_called_class();
        $collection     = new $calledClass;

        if ($parentId != '') {
            $sqlParams     = array();
            $dbHandler     = Fwk::Database(true);

            $sql  = "SELECT *";
            $sql .= " FROM `" . $collection::TABLE_NAME . "`";
            $sql .= " WHERE";
            $sql .= "   " . $collection::PARENT_ID_NAME . "=:parent_id";

            if ($collection->parentFilterType !== null) {
                $sql .= "   AND " . $collection->parentFilterName . "=:parent_type";
                $sqlParams['parent_type'] = $collection->parentFilterType;
            }

            $sqlParams['parent_id'] = $parentId;
            $results = $dbHandler->query($sql, $sqlParams)->fetchAll();

            if ($results !== false) {
                foreach ($results as $currentResult) {
                    $itemName = $collection::ITEM_TYPE;
                    $collection->addItem($itemName::buildFromArray($currentResult));
                }
            }

            $collection->parentId = $parentId;
        }

        return $collection;
    }

    public function setParentIdForAll($parentId)
    {
        $this->parentId = $parentId;
        foreach ($this->items as $key => $currentItem) {
            $this->items[$key]->{static::PARENT_ID_NAME} = $parentId;
        }
    }

    /**
     * Load Parent Item, if item type is defined
     * @return item type Parent Object
     */
    public function loadParent()
    {
        if (static::PARENT_OBJECT_TYPE != '' && $this->parentId != '') {
            $parentObjectType = static::PARENT_OBJECT_TYPE;
            $parent = new $parentObjectType();
            if (method_exists($parent, 'load')) {
                $parent->load($this->parentId);

                return $parent;
            } else {
                throw new \BadMethodCallException("Parent object does not have a load(\$id) method");
            }
        } else {
            throw new \BadMethodCallException("PARENT_OBJECT_TYPE is not defined");
        }
    }

    public function craftItem($itemData)
    {
        $itemName = static::ITEM_TYPE;

        foreach ($itemData as $data) {
            $newItem = new $itemName();
            $newItem->{static::PARENT_ID_NAME} = $this->parentId;
            $hasData = false;
            foreach ($data as $field => $value) {
                $newItem->$field = $value;
                if ($value != '') {
                    $hasData = true;
                }
            }

            // Only add item if there's data inside
            if ($hasData) {
                $this->addItem($newItem);
            }
        }
    }

    public function save()
    {
        // 1st step : delete all records for current parentId
        $sql  = "DELETE FROM `" . static::TABLE_NAME . "`";
        if (static::PARENT_ID_NAME != '') {
            $sql .= " WHERE";
            $sql .= "   " . static::PARENT_ID_NAME . "=:parent_id";

            $sqlParams = array('parent_id' => $this->parentId);
        } else {
            $sqlParams = array();
        }

        Fwk::Database()->query($sql, $sqlParams);

        // 2nd step : save all current items
        foreach ($this->items as $currentItem) {
            $currentItem->save(true); // Force insert
        }
    }

    public function purgeItems()
    {
        $this->items        = array();
        $this->mapping      = array();
        $this->itemOffset   = 0;
    }


    public function sort($field, $order)
    {
        $this->sortField   = $field;
        $this->sortOrder   = $order;

        uasort($this->items, array($this, 'sortFunction'));

        return $this;
    }

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
            $array_key = ( $withMapping ) ? $item->$mappingKey: $key;
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

    public function __toString()
    {
        $output = '<ul>'."\n";
        foreach ($this->items as $key => $item) {
            $output .= '<li>' . $key .' => ' . $item . "</li>\n";
        }
        $output .= '</ul>'."\n";

        return $output;
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

    public function addItem(DBObject $item)
    {
        $key = $item::TABLE_INDEX;
        // Add item to items pool
        $this->items[$this->itemOffset] = $item;

        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $item->$key;

        $this->itemOffset++;
    }

    public function getItemsType()
    {
        return static::ITEM_TYPE;
    }

    public function getParentIdName()
    {
        return static::PARENT_ID_NAME;
    }

    public function getParentId()
    {
        return $this->parentId;
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

    public function getSlice($start, $nbItems = null)
    {
        return array_slice($this->items, $start, $nbItems, true);
    }

    public function getFirstItem()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    public function getRandom($nb = 1)
    {
        $keys = (array) array_rand($this->items, $nb);
        $result = array();
        foreach ($keys as $currentKey) {
            $result[$currentKey] = $this->items[$currentKey];
        }

        return $result;
    }
}
