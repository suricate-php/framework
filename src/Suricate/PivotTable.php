<?php declare(strict_types=1);
namespace Suricate;

class PivotTable extends DBObject
{
    protected $references = [];

    public function __construct()
    {
        parent::__construct();

        foreach ($this->references as $referenceName => $referenceData) {
            $this->relations[$referenceName] = [
                'type' => self::RELATION_ONE_ONE, 'source' => $referenceData['key'], 'target' => $referenceData['type']
            ];
        }
    }

    public static function loadFor($relation, $parentId, $target = null, $validate = null)
    {
        $pivot = new static;
        $pivot->connectDB();

        $items = [];
        if ($target !== null) {
            $className = $pivot->getTargetForRelation($target);
            $targetClass    = $className;
            $sourceField    = $pivot->getSourceFieldForRelation($target);
            
            $query  = "SELECT t.* FROM " . $className::tableName() . " t";
            $query .= " LEFT JOIN " . $pivot->getTableName() . " p";
            $query .= "     ON p.`" . $sourceField . "`=t." . $className::tableIndex();
            $query .= " WHERE";
            $query .= "     `" . $pivot->getSourceFieldForRelation($relation) . "` =  :id";
            $query .= " GROUP BY t." . $targetClass::getTableIndex();
        } else {
            $query  = "SELECT *";
            $query .= " FROM `" . $pivot->getTableName() ."`";
            $query .= " WHERE";
            $query .= "     `" . $pivot->getSourceFieldForRelation($relation) . "` =  :id";

            $targetClass = $pivot;
        }

        $params = ['id' => $parentId];

        $results = $pivot
            ->dbLink
            ->query($query, $params)
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $add = true;

            $itemToAdd = $targetClass::instanciate($result);
            
            if ($validate !== null && is_callable($validate)) {
                $add = $validate($itemToAdd);
            }
            if ($add) {
                $items[] = $itemToAdd;
            }
        }
    

        return new Collection($items);
    }

    public function getSourceFieldForRelation($relationName)
    {
        if (isset($this->relations[$relationName])) {
            return $this->relations[$relationName]['source'];
        }

        throw new \InvalidArgumentException('Cannot get field for relation "' . $relationName . '" : Unknown relation');
    }

    public function getTargetForRelation($relationName)
    {
        if (isset($this->relations[$relationName])) {
            return $this->relations[$relationName]['target'];
        }

        throw new \InvalidArgumentException('Cannot get target for relation "' . $relationName . '" : Unknown relation');
    }
}
