<?php declare(strict_types=1);

namespace Suricate;

class DBCollectionOneMany extends DBCollection
{
    /* @var string Name of parent identifier field */
    protected $parentIdField    = 'parent_id';

    /* @var mixed parent identifier */
    protected $parentId;

    /* @var string parent field filter name */
    protected $parentFilterName;

    /* @var mixed parent filtering value */
    protected $parentFilterType;

   
    public function getParentIdField(): string
    {
        return $this->parentIdField;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function getParentFilterName()
    {
        return $this->parentFilterName;
    }

    public function getParentFilterType()
    {
        return $this->parentFilterType;
    }

    /**
     * Load entire table into collection
     * @return Collection Loaded collection
     */
    public static function loadAll()
    {
        $calledClass    = get_called_class();
        $collection     = new $calledClass;

        $sql  = "SELECT *";
        $sql .= "   FROM `" . $collection->getTableName() . "`";
        $sql .= "WHERE " . $collection->parentFilterName . "=:type";

        $sqlParams = ['type' => $collection->parentFilterType];

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
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
        $this->parentId = $parentId;
        foreach (array_keys($this->items) as $key) {
            $this->items[$key]->{$this->parentIdField} = $parentId;
        }
        return $this;

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
        $sql .= " WHERE";
        $sql .= "   `" . $this->parentIdField . "`=:parent_id";

        $sqlParams = ['parent_id' => $this->parentId];
        
        $this->connectDB();
        $this->dbLink->query($sql, $sqlParams);

        // 2nd step : save all current items
        foreach ($this->items as $currentItem) {
            $currentItem->save(true); // Force insert
        }
    }
}
