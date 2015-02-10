<?php
namespace Suricate;

class PivotTable extends DBObject
{
    protected $references = array();

    public function __construct()
    {
        foreach ($this->references as $referenceName => $referenceData) {
            $this->relations[$referenceName] = array(
                'type' => self::RELATION_ONE_ONE, 'source' => $referenceData['key'], 'target' => $referenceData['type']
            );
        }
    }

    public static function loadFor($relation, $parentId, $target = null)
    {
        $pivot = new static;
        $pivot->connectDB();

        $items = array();
        $query  = "SELECT *";
        $query .= " FROM `" . static::TABLE_NAME ."`";
        $query .= " WHERE";
        $query .= "     `" . $pivot->getFieldForRelation($relation) . "` =  :id";

        $params         = array();
        $params['id']   = $parentId;

        $results = $pivot->dbLink->query($query, $params)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $items[] = static::instanciate($result)->$target;
        }
        
        return $items;
    }

    private function getFieldForRelation($relationName)
    {
        if (isset($this->relations[$relationName])) {
            return $this->relations[$relationName]['source'];
        } else {
            throw new \InvalidArgumentException('Cannot get field for relation "' . $relationName. '" : Unknown relation');
        }
    }
}