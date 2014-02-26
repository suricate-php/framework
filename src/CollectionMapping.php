<?php
namespace Fwk;

class CollectionMapping extends Collection
{
    const SQL_RELATION_TABLE_NAME   = '';
    const MAPPING_ID_NAME           = '';

    protected $additionalMappingFieldList = array();

    public static function loadForParentId($parent_id)
    {

        $called_class   = get_called_class();
        $collection     = new $called_class;

        $item_name = $collection::ITEM_TYPE;

        $sql            = '';
        $sqlParams      = array();

        $sql .= "SELECT a.*";
        if (count($collection->additionalMappingFieldList)) {
            $sql .= ', b.' . implode(',b.', $collection->additionalMappingFieldList);
        }
        $sql .= " FROM `" . $collection::TABLE_NAME . "` a";
        $sql .= " RIGHT JOIN `" . $collection::SQL_RELATION_TABLE_NAME . "` b";
        $sql .= "   ON b." . $collection::MAPPING_ID_NAME . "=a." . $item_name::TABLE_INDEX;
        $sql .= " WHERE";
        $sql .= "   " . $collection::PARENT_ID_NAME . "=:parent_id";

        $sqlParams['parent_id'] = $parent_id;

        $results = Fwk::Database()->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $item_name = $collection::ITEM_TYPE;
                $collection->addItem($item_name::buildFromArray($currentResult));
            }
        }
        $collection->parent_id = $parent_id;

        return $collection;
    }

    public function setParentIdForAll($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function save()
    {
        $db_handler     = Fwk::Database(true);

        if ($this->parent_id != '') {
            // 1st step : delete all records for current parent_id
            $sql  = "DELETE FROM `" . static::SQL_RELATION_TABLE_NAME . "`";
            $sql .= " WHERE";
            $sql .= "   " . static::PARENT_ID_NAME . "=:parent_id";

            $sqlParams      = array();
            $sqlParams['parent_id'] = $this->parent_id;

            //echo "--> delete old items with : $sql<br/>";
            $db_handler->query($sql, $sqlParams);

            // 2nd step : create items that are not saved in db
            foreach ($this->items as &$current_item) {
                if ($current_item->{$current_item::TABLE_INDEX} == '') {
                    // 2nd step : create items that are not saved in db
                    //echo "Item missing id, saving id<br/>";
                    /*foreach ($this->additionalMappingFieldList as $additionalField) {
                        $current_item->$additionalField
                    }*/
                    $current_item->save();
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
                        $sqlParams[$additionalField] = $current_item->$additionalField;
                    }
                }
                
                $sql .= ")";

                
                $sqlParams['parent_id'] = $this->parent_id;
                $sqlParams['id']        = $current_item->id;
                //echo "<pre>SAVING RELATION : $sql <br/>";
                //print_r($sqlParams);
                //echo '</pre>';
                $db_handler->query($sql, $sqlParams);
            }
        }
    }

    public function craftItem($item_data)
    {
        $item_name = static::ITEM_TYPE;
        
        foreach ($item_data as $data) {
            $new_item       = new $item_name();
            $has_data       = false;
            $item_exists    = false;

            // One field contains item unique index, load from it
            if (isset($data[$new_item::TABLE_INDEX]) && $data[$new_item::TABLE_INDEX] != '') {
                $new_item->load($data[$new_item::TABLE_INDEX]);
                $item_exists = true;
            } else {
                // Build SQL query to load corresponding item
                $sqlData = array();
                foreach ($data as $field => $value) {
                    if ($new_item->isDBVariable($field)) {
                        $sqlData[$field] = $value;
                    }
                }

                $sql  = "SELECT *";
                $sql .= " FROM `" . $new_item::TABLE_NAME . "`";
                $sql .= " WHERE";
                foreach (array_keys($sqlData) as $field) {
                    $sql .= "   `" . $field . "`=:$field";
                }
                
                $sqlParams = array();
                $sqlParams[$field] = $value;

                $new_item->loadFromSql($sql, $sqlData);
            }

            // Assign properties to object
            foreach ($data as $field => $value) {
                $new_item->$field = $value;
                $has_data = $has_data || ($value != '');
            }
            // Object is not empty, adding it to collection
            if ($has_data) {
                $this->addItem($new_item);
            }

            /*
            foreach ($data as $field => $value) {
                $has_data = $has_data || ($value != '');
                

                if ($field == $new_item::TABLE_INDEX) {
                    $item_exists = $new_item->load($value);
                } else {

                    $sql  = "SELECT *";
                    $sql .= " FROM `" . $new_item::TABLE_NAME . "`";
                    $sql .= " WHERE";
                    $sql .= "   `" . $field . "`=:$field";
                    echo $sql . "<br/>\n";
                    $sqlParams = array();
                    $sqlParams[$field] = $value;

                    $item_exists = $new_item->loadFromSql($sql, $sqlParams);
                }
                if ($item_exists === false) {
                    //echo "NON existent item, building it / $field / $value<br/>";
                    $new_item->$field = $value;
                }
            }
            if ($has_data) {
                $this->addItem($new_item);
            }*/
        }
    }
}
