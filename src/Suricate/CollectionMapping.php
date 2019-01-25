<?php declare(strict_types=1);
namespace Suricate;

class CollectionMapping extends Collection
{
    const SQL_RELATION_TABLE_NAME   = '';
    const MAPPING_ID_NAME           = '';

    protected $additionalMappingFieldList = array();

    public static function loadForParentId($parentId)
    {

        $calledClass   = get_called_class();
        $collection     = new $calledClass;

        $itemName = $collection::ITEM_TYPE;

        $sql            = '';
        $sqlParams      = array();

        $sql .= "SELECT a.*";
        if (count($collection->additionalMappingFieldList)) {
            $sql .= ', b.' . implode(',b.', $collection->additionalMappingFieldList);
        }
        $sql .= " FROM `" . $collection::TABLE_NAME . "` a";
        $sql .= " RIGHT JOIN `" . $collection::SQL_RELATION_TABLE_NAME . "` b";
        $sql .= "   ON b." . $collection::MAPPING_ID_NAME . "=a." . $itemName->getTableIndex();
        $sql .= " WHERE";
        $sql .= "   " . $collection::PARENT_ID_NAME . "=:parent_id";

        $sqlParams['parent_id'] = $parentId;

        $results = Suricate::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $collection::ITEM_TYPE;
                $collection->addItem($itemName::buildFromArray($currentResult));
            }
        }
        $collection->parent_id = $parentId;

        return $collection;
    }

    public function setParentIdForAll($parentId)
    {
        $this->parent_id = $parentId;
    }

    public function save()
    {
        $dbHandler     = Suricate::Database(true);

        if ($this->parent_id != '') {
            // 1st step : delete all records for current parent_id
            $sql  = "DELETE FROM `" . static::SQL_RELATION_TABLE_NAME . "`";
            $sql .= " WHERE";
            $sql .= "   " . static::PARENT_ID_NAME . "=:parent_id";

            $sqlParams      = array();
            $sqlParams['parent_id'] = $this->parent_id;

            $dbHandler->query($sql, $sqlParams);

            // 2nd step : create items that are not saved in db
            foreach ($this->items as &$currentItem) {
                if ($currentItem->{$currentItem->getTableIndex()} == '') {
                    $currentItem->save();
                }

                //3rd step : create the mapping
                $sqlParams = array();

                $sql  = "INSERT INTO `" . static::SQL_RELATION_TABLE_NAME . "`";
                $sql .= " (`" . static::PARENT_ID_NAME . "`, `" . static::MAPPING_ID_NAME. "`";
                if (count($this->additionalMappingFieldList)) {
                    $sql .= ', ' . implode(
                        ",",
                        array_map(
                            function ($s) {
                                return '`' . $s . '`';
                            },
                            $this->additionalMappingFieldList
                        )
                    );
                }
                
                $sql .= ")";
                $sql .= " VALUES";
                $sql .= "(:parent_id, :id";
                if (count($this->additionalMappingFieldList)) {
                    foreach ($this->additionalMappingFieldList as $additionalField) {
                        $sql .= ',:' . $additionalField;
                        $sqlParams[$additionalField] = $currentItem->$additionalField;
                    }
                }
                
                $sql .= ")";

                
                $sqlParams['parent_id'] = $this->parent_id;
                $sqlParams['id']        = $currentItem->id;

                $dbHandler->query($sql, $sqlParams);
            }
        }
    }

    public function craftItem($itemData)
    {
        $itemName = static::ITEM_TYPE;
        
        foreach ($itemData as $data) {
            $newItem       = new $itemName();
            $hasData       = false;

            // One field contains item unique index, load from it
            if (isset($data[$newItem->getTableIndex()]) && $data[$newItem->getTableIndex()] != '') {
                $newItem->load($data[$newItem->getTableIndex()]);
            } else {
                // Build SQL query to load corresponding item
                $sqlData = array();
                

                $sql  = "SELECT *";
                $sql .= " FROM `" . $newItem::TABLE_NAME . "`";
                $sql .= " WHERE";
                foreach ($data as $field => $value) {
                    if ($newItem->isDBVariable($field)) {
                        $sqlData[$field] = $value;
                        $sql .= "   `" . $field . "`=:$field";
                    }
                }

                $newItem->loadFromSql($sql, $sqlData);
            }

            // Assign properties to object
            foreach ($data as $field => $value) {
                $newItem->$field = $value;
                $hasData = $hasData || ($value != '');
            }
            // Object is not empty, adding it to collection
            if ($hasData) {
                $this->addItem($newItem);
            }
        }
    }
}
