<?php
namespace Suricate;

class DBCollection extends Collection
{
    /* @var string SQL table name */
    protected $tableName        = '';
    /* @var string Item type stored in collection */
    protected $itemsType        = '';
    /* @var string Database configuration identifier */
    protected $DBConfig         = '';
    /* @var string Name of parent identifier field */
    protected $parentIdField    = 'parent_id';

    protected $mapping          = [];
    protected $lazyLoad = false;
    protected $parentId;                       // Id of the parent
    protected $parentFilterName;                // Name of field used for filtering
    protected $parentFilterType;                // Value of filter

    protected $dbLink               = false;
    protected $itemOffset           = 0;

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getItemsType(): string
    {
        return $this->itemsType;
    }

    public function getDBConfig(): string
    {
        return $this->DBConfig;
    }

    public function getParentIdField(): string
    {
        return $this->parentIdField;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set lazyload flag
     *
     * @param bool $lazyLoad
     * @return DBCollection
     */
    public function setLazyLoad($lazyLoad)
    {
        $this->lazyLoad = $lazyLoad;

        return $this;
    }

    /**
     * Get lazyload flag
     *
     * @return boolean
     */
    public function getLazyLoad(): bool
    {
        return $this->lazyLoad;
    }

    public function purgeItems()
    {
        $this->items        = [];
        $this->mapping      = [];
        $this->itemOffset   = 0;
    }

    /**
     * Load entire table into collection
     * @return Collection Loaded collection
     */
    public static function loadAll()
    {
        $calledClass    = get_called_class();
        $collection     = new $calledClass;

        $sqlParams      = [];

        $sql  = "SELECT *";
        $sql .= "   FROM `" . $collection->getTableName() . "`";

        if ($collection->parentFilterType !== '' && $collection->parentFilterType != null) {
            $sql .= "WHERE " . $collection->parentFilterName . "=:type";
            $sqlParams['type'] = $collection->parentFilterType;
        }

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    /**
     * Static wrapper for loadFromSql
     * @param  string     $sql       SQL Statement
     * @param  array      $sqlParams SQL Parameters
     * @return Collection Loaded collection
     */
    public static function buildFromSql($sql, $sqlParams = [])
    {
        $calledClass = get_called_class();
        $collection = new $calledClass;

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    /**
     * Load collection from SQL query
     *
     * @param string $sql       SQL query
     * @param array  $sqlParams associative array of SQL params
     * @return DBCollection
     */
    public function loadFromSql($sql, $sqlParams = [])
    {
        if (!in_array(Interfaces\IDBObject::class, class_implements($this->itemsType))) {
            throw new \BadMethodCallException('Item type does not implement IDBObject interface');
        }

        $this->connectDB();
        $results = $this->dbLink->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $this->getItemsType();
                $this->addItem($itemName::instanciate($currentResult));
            }
        }

        return $this;
    }

    public function addItemLink($linkId)
    {
         $this->items[$this->itemOffset] = $linkId;
         // add mapping between item->index and $position in items pool
         $this->mapping[$this->itemOffset] = $linkId;
         $this->itemOffset++;
    }

    public function lazyLoadFromSql($sql, $sqlParams = array())
    {
        $dbLink = Suricate::Database();
        if ($this->DBConfig !== '') {
            $dbLink->setConfig($this->DBConfig);
        }

        $results = $dbLink
            ->query($sql, $sqlParams)
            ->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $this->addItemLink(current($currentResult));
            }
        }

        return $this;
    }

    /**
     * Load items linked to a parentId
     * @param mixed        $parentId       Parent id description
     * @param string       $parentIdField  Name of parent id referencing field
     * @param \Closure|null $validate       Callback use to validate add to items collection
     */
    public static function loadForParentId($parentId, $parentIdField = null, $validate = null)
    {
        $calledClass   = get_called_class();
        $collection     = new $calledClass;

        if ($parentId != '') {
            $sqlParams     = [];
            $dbHandler     = Suricate::Database(true);

            if ($collection->getDBConfig() !== '') {
                $dbHandler->setConfig($collection->getDBConfig());
            }

            $sql  = "SELECT *";
            $sql .= " FROM `" . $collection->getTableName() . "`";
            $sql .= " WHERE";
            if ($parentIdField !== null) {
                $sql .= "`" . $parentIdField . "`=:parent_id";
            } else {
                $sql .= "`" . $collection->getParentIdField() . "`=:parent_id";
            }

            if ($collection->parentFilterType !== null) {
                $sql .= "   AND " . $collection->parentFilterName . "=:parent_type";
                $sqlParams['parent_type'] = $collection->parentFilterType;
            }

            $sqlParams['parent_id'] = $parentId;
            $results = $dbHandler->query($sql, $sqlParams)->fetchAll();

            if ($results !== false) {
                foreach ($results as $currentResult) {
                    $itemName = $collection->getItemsType();
                    $item = $itemName::instanciate($currentResult);
                    if ($validate === null || $validate($item)) {
                        $collection->addItem($item);
                    }
                }
            }

            $collection->parentId = $parentId;
        }

        return $collection;
    }

    public function setParentIdForAll($parentId)
    {
        if ($this->parentIdField !== null) {
            $this->parentId = $parentId;
            foreach (array_keys($this->items) as $key) {
                $this->items[$key]->{$this->parentIdField} = $parentId;
            }
            return $this;
        }

        throw new \BadMethodCallException('Collection does not have a parentId field');
    }

    public function craftItem($itemData)
    {
        $itemName = $this->itemsType;

        foreach ($itemData as $data) {
            $newItem = new $itemName();
            $newItem->{$this->parentIdField} = $this->parentId;
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
        $sql  = "DELETE FROM `" . $this->tableName . "`";
        if ($this->parentIdField !== '') {
            $sql .= " WHERE";
            $sql .= "   `" . $this->parentIdField . "`=:parent_id";

            $sqlParams = array('parent_id' => $this->parentId);
        } else {
            $sqlParams = array();
        }

        $dbLink = Suricate::Database();
        if ($this->DBConfig !== '') {
            $dbLink->setConfig($this->DBConfig);
        }
        
        $dbLink->query($sql, $sqlParams);

        // 2nd step : save all current items
        foreach ($this->items as $currentItem) {
            $currentItem->save(true); // Force insert
        }
    }

    public function addItem(Interfaces\IDBObject $item)
    {
        $key = $item->getTableIndex();
        // Add item to items pool
        $this->items[$this->itemOffset] = $item;

        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $item->$key;

        $this->itemOffset++;

        return $this;
    }

    protected function connectDB()
    {
        if (!$this->dbLink) {
            $this->dbLink = Suricate::Database();
            if ($this->getDBConfig() !== '') {
                $this->dbLink->setConfig($this->getDBConfig());
            }
        }
    }
}
