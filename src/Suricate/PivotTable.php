<?php
namespace Suricate;

class PivotTable extends DBObject
{
    protected $references = array();

    public function __construct()
    {
        parent::__construct();

        foreach ($this->references as $referenceName => $referenceData) {
            $this->relations[$referenceName] = array(
                'type' => self::RELATION_ONE_ONE, 'source' => $referenceData['key'], 'target' => $referenceData['type']
            );
        }
    }

    public static function loadFor($relation, $parentId, $target = null, $validate = null)
    {
        $pivot = new static;
        $pivot->connectDB();

        $items = array();
        if ($target !== null) {
            $targetType     = $pivot->getTargetForRelation($target);
            $sourceField    = $pivot->getSourceFieldForRelation($target);
            
            $query  = "SELECT t.* FROM " . $targetType::TABLE_NAME . " t";
            $query .= " LEFT JOIN " . static::TABLE_NAME . " p";
            $query .= "     ON p.`" . $sourceField . "`=t." . $targetType::TABLE_INDEX;
            $query .= " WHERE";
            $query .= "     `" . $pivot->getSourceFieldForRelation($relation) . "` =  :id";
            $query .= " GROUP BY t." . $targetType::TABLE_INDEX;
            
            $itemToAddType  = $targetType;
        } else {
            $query  = "SELECT *";
            $query .= " FROM `" . static::TABLE_NAME ."`";
            $query .= " WHERE";
            $query .= "     `" . $pivot->getSourceFieldForRelation($relation) . "` =  :id";

            $itemToAddType = get_called_class();
        }
        $params         = array();
        $params['id']   = $parentId;

        $results = $pivot->dbLink->query($query, $params)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $add = true;

            $itemToAdd = $itemToAddType::instanciate($result);
            
            if ($validate !== null && is_callable($validate)) {
                $add = $validate($itemToAdd);
            }
            if ($add) {
                $items[] = $itemToAdd;
            }
        }
    

        return new Collection($items);
    }

    private function getSourceFieldForRelation($relationName)
    {
        if (isset($this->relations[$relationName])) {
            return $this->relations[$relationName]['source'];
        }

        throw new \InvalidArgumentException('Cannot get field for relation "' . $relationName . '" : Unknown relation');
    }

    private function getTargetForRelation($relationName)
    {
        if (isset($this->relations[$relationName])) {
            return $this->relations[$relationName]['target'];
        }

        throw new \InvalidArgumentException('Cannot get target for relation "' . $relationName . '" : Unknown relation');
    }
}
