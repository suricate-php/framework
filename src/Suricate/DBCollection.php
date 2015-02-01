<?php
namespace Suricate;

class DBCollection extends Collection
{
    const TABLE_NAME            = '';           // Name of SQL table containing items
    const ITEM_TYPE             = '';           // Class of items in collection
    const PARENT_ID_NAME        = 'parent_id';  // Name of the field referencing to parent_id
    const PARENT_OBJECT_TYPE    = '';           // Parent object type

    protected $lazyLoad = false;
    protected $parentId;                       // Id of the parent
    protected $parentFilterName;                // Name of field used for filtering
    protected $parentFilterType;                // Value of filter

    public function setLazyLoad($lazyLoad)
    {
        $this->lazyLoad = $lazyLoad;

        return $this;
    }

    
    /**
     * Load entire table into collection
     * @return Collection Loaded collection
     */
    public static function loadAll()
    {
        $calledClass    = get_called_class();
        $collection     = new $calledClass;

        $sqlParams      = array();

        $sql  = "SELECT *";
        $sql .= "   FROM `" . $collection::TABLE_NAME . "`";

        if ($collection->parentFilterType !== '' && $collection->parentFilterType != null) {
            $sql .= "WHERE " . $collection->parentFilterName . "=:type";
            $sqlParams['type'] = $collection->parentFilterType;
        }

        $results = Suricate::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $collection::ITEM_TYPE;
                $collection->addItem($itemName::instanciate($currentResult));
            }
        }

        return $collection;
    }

    /**
     * Static wrapper for loadFromSql
     * @param  string     $sql       SQL Statement
     * @param  array      $sqlParams SQL Parameters
     * @return Suricate\Collection Loaded collection
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
        $results = Suricate::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $this::ITEM_TYPE;
                $this->addItem($itemName::instanciate($currentResult));
            }
        }

        return $this;
    }

    public function lazyLoadFromSql($sql, $sqlParams = array())
    {
        $results = Suricate::Database()
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
            $dbHandler     = Suricate::Database(true);

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
                    $collection->addItem($itemName::instanciate($currentResult));
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

        Suricate::Database()->query($sql, $sqlParams);

        // 2nd step : save all current items
        foreach ($this->items as $currentItem) {
            $currentItem->save(true); // Force insert
        }
    }

    public function addItem(Interfaces\IDBObject $item)
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
}