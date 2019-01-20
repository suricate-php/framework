<?php
namespace Suricate;

use Suricate\Traits\DBObjectRelations;
use Suricate\Traits\DBObjectProtected;
/**
 * DBObject, Pseudo ORM Class
 *
 * Two types of variables are available :
 * - $dbVariables, an array of fields contained in linked SQL table
 * - $protectedVariables, an array of variables not stored in SQL
 *     that can be triggered on access
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 */

class DBObject implements Interfaces\IDBObject
{
    use DBObjectRelations;
    use DBObjectProtected;

    /** @var string Linked SQL Table */
    protected $tableName = '';

    /** @var string Unique ID of the SQL table */
    protected $tableIndex = '';
    
    /** @var string Database config name */
    protected $DBConfig = '';

    /**
     * @const RELATION_ONE_ONE : Relation one to one
     */
    const RELATION_ONE_ONE      = 1;
    /**
     * @const RELATION_ONE_MANY : Relation one to many
     */
    const RELATION_ONE_MANY     = 2;
    /**
     * @const RELATION_MANY_MANY : Relation many to many
     */
    const RELATION_MANY_MANY    = 3;

    protected $dbVariables                  = [];
    protected $dbValues                     = [];

    protected $readOnlyVariables            = [];

    protected $exportedVariables            = [];

    protected $dbLink                       = false;

    protected $validatorMessages            = [];
    

    public function __construct()
    {
        $this->setRelations();
    }
    /**
     * Magic getter
     *
     * Try to get object property according this order :
     * <ul>
     *     <li>$dbVariable</li>
     *     <li>$protectedVariable (triggger call to accessToProtectedVariable()
     *         if not already loaded)</li>
     * </ul>
     *
     * @param  string $name     Property name
     * @return Mixed            Property value
     */
    public function __get($name)
    {
        if ($this->isDBVariable($name)) {
            return $this->getDBVariable($name);
        } elseif ($this->isProtectedVariable($name)) {
            return $this->getProtectedVariable($name);
        } elseif ($this->isRelation($name)) {
            return $this->getRelation($name);
        } elseif (!empty($this->$name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException('Undefined property ' . $name);
    }

        /**
     * Magic setter
     *
     * Set a property to defined value
     * Assignment in this order :
     * - $dbVariable
     * - $protectedVariable
     *  </ul>
     * @param string $name  variable name
     * @param mixed $value variable value
     * 
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->isDBVariable($name)) {
            $this->dbValues[$name] = $value;
            return;
        }

        if ($this->isProtectedVariable($name)) {
            $this->protectedValues[$name] = $value;
            return;
        }

        if ($this->isRelation($name)) {
            $this->relationValues[$name] = $value;
            return;
        }
        
        $this->$name = $value;
    }

    public function __isset($name)
    {
        if ($this->isDBVariable($name)) {
            return isset($this->dbValues[$name]);
        } elseif ($this->isProtectedVariable($name)) {
            // Load only one time protected variable automatically
            if (!$this->isProtectedVariableLoaded($name)) {
                $protectedAccessResult = $this->accessToProtectedVariable($name);

                if ($protectedAccessResult) {
                    $this->markProtectedVariableAsLoaded($name);
                }
            }
            return isset($this->protectedValues[$name]);
        } elseif ($this->isRelation($name)) {
            if (!$this->isRelationLoaded($name)) {
                $this->loadRelation($name);
                $this->markRelationAsLoaded($name);
            }
            return isset($this->relationValues[$name]);
        }

        return false;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getTableIndex()
    {
        return $this->tableIndex;
    }

    public function getDBConfig()
    {
        return $this->DBConfig;
    }

    /**
     * __sleep magic method, permits an inherited DBObject class to be serialized
     * @return Array of properties to serialize
     */
    public function __sleep()
    {
        $discardedProps = ['dbLink', 'relations'];
        $reflection     = new \ReflectionClass($this);
        $props          = $reflection->getProperties();
        $result         = [];
        foreach ($props as $currentProperty) {
            $result[] = $currentProperty->name;
        }
        
        return array_diff($result, $discardedProps);
    }

    public function __wakeup()
    {
        $this->dbLink = false;
        $this->setRelations();
    }
    
    /**
     * @param string $name
     */
    private function getDBVariable($name)
    {
        if (isset($this->dbValues[$name])) {
            return $this->dbValues[$name];
        }

        return null;
    }

    /**
     * Check if variable is from DB
     * @param  string  $name variable name
     * @return boolean
     */
    public function isDBVariable($name)
    {
        return in_array($name, $this->dbVariables);
    }

    /**
     * Define object exported variables
     *
     * @return DBObject
     */
    protected function setExportedVariables()
    {
        if (count($this->exportedVariables)) {
            return $this;
        }

        $dbMappingExport = [];
        foreach ($this->dbVariables as $field) {
            $dbMappingExport[$field] = $field;
        }
        $this->exportedVariables = $dbMappingExport;

        return $this;
    }

    /**
     * Export DBObject to array
     *
     * @return array
     */
    public function toArray()
    {
        $this->setExportedVariables();
        $result = [];
        foreach ($this->exportedVariables as $sourceName => $destinationName) {
            $omitEmpty  = false;
            $castType   = null;
            if (strpos($destinationName, ',') !== false) {
                $splitted   = explode(',', $destinationName);
                array_map(function ($item) use (&$castType, &$omitEmpty) {
                    if ($item === 'omitempty') {
                        $omitEmpty = true;
                        return;
                    }
                    if (substr($item, 0, 5) === 'type:') {
                        $castType = substr($item, 5);
                    }
                }, $splitted);

                $destinationName = $splitted[0];
            }

            if ($destinationName === '-') {
                continue;
            }

            if ($omitEmpty && empty($this->$sourceName)) {
                continue;
            }
            $value = $this->$sourceName;
            if ($castType !== null) {
                settype($value, $castType);
            }
            $result[$destinationName] = $value;
        }

        return $result;
    }

    /**
     * Export DBObject to JSON format
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private function resetLoadedVariables()
    {
        $this->loadedProtectedVariables = [];
        $this->loadedRelations          = [];

        return $this;
    }

    /**
     * Check if requested property exists
     *
     * Check in following order:
     * <ul>
     *     <li>$dbVariables</li>
     *     <li>$protectedVariables</li>
     *     <li>$relations</li>
     *     <li>legacy property</li>
     * </ul>
     * @param  string $property Property name
     * @return boolean           true if exists
     */
    public function propertyExists($property)
    {
        return $this->isDBVariable($property)
            || $this->isProtectedVariable($property)
            || $this->isRelation($property)
            || property_exists($this, $property);
    }

    /**
     * Load ORM from Database
     * @param  mixed $id SQL Table Unique id
     * @return mixed     Loaded object or false on failure
     */
    public function load($id)
    {
        $this->connectDB();
        $this->resetLoadedVariables();

        $query  = "SELECT *";
        $query .= " FROM `" . $this->getTableName() ."`";
        $query .= " WHERE";
        $query .= "     `" . $this->getTableIndex() . "` =  :id";
        
        $params         = [];
        $params['id']   = $id;

        return $this->loadFromSql($query, $params);
    }

    public function isLoaded()
    {
        return $this->{$this->getTableIndex()} !== null;
    }

    public function loadOrFail($index)
    {
        $this->load($index);
        if ($this->{$this->getTableIndex()} != $index) {
            throw (new Exception\ModelNotFoundException)->setModel(get_called_class());
        }

        return $this;
    }

    public static function loadOrCreate($arg)
    {
        $obj = static::loadOrInstanciate($arg);
        $obj->save();

        return $obj;
    }

    public static function loadOrInstanciate($arg)
    {
        $calledClass = get_called_class();
        $obj = new $calledClass;
        
        if (!is_array($arg)) {
            $arg = [$obj->getTableIndex() => $arg];
        }
        

        $sql = "SELECT *";
        $sql .= " FROM `" . $obj->getTableName() . "`";
        $sql .= " WHERE ";

        $sqlArray   = [];
        $params     = [];
        $i = 0;
        foreach ($arg as $key => $val) {
            if (is_null($val)) {
                $sqlArray[] = '`' . $key . '` IS :arg' . $i;
            } else {
                $sqlArray[] = '`' . $key . '`=:arg' . $i;
            }
            $params['arg' .$i] = $val;
            $i++;
        }
        $sql .= implode(' AND ', $sqlArray);

        if (!$obj->loadFromSql($sql, $params)) {
            foreach ($arg as $property => $value) {
                $obj->$property = $value;
            }
        }

        return $obj;
    }
    
    /**
     * @param string $sql
     */
    public function loadFromSql(string $sql, $sqlParams = [])
    {
        $this->connectDB();
        $this->resetLoadedVariables();
        
        $results = $this->dbLink->query($sql, $sqlParams)->fetch();

        if ($results !== false) {
            foreach ($results as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        }

        return false;
    }

    /**
     * Construct an DBObject from an array
     * @param  array $data  associative array
     * @return DBObject       Built DBObject
     */
    public static function instanciate($data = [])
    {
        $calledClass    = get_called_class();
        $orm            = new $calledClass;

        return $orm->hydrate($data);
    }

    public function hydrate($data = [])
    {
        foreach ($data as $key => $val) {
            if ($this->propertyExists($key)) {
                $this->$key = $val;
            }
        }

        return $this;
    }

    public static function create($data = [])
    {
        $obj = static::instanciate($data);
        $obj->save();

        return $obj;
    }
    
    /**
     * Delete record from SQL Table
     *
     * Delete record link to current object, according SQL Table unique id
     * @return null
     */
    public function delete()
    {
        $this->connectDB();

        if ($this->getTableIndex() !== '') {
            $query  = "DELETE FROM `" . $this->getTableName() . "`";
            $query .= " WHERE `" . $this->getTableIndex() . "` = :id";

            $queryParams = [];
            $queryParams['id'] = $this->{$this->getTableIndex()};
            
            $this->dbLink->query($query, $queryParams);
        }
    }
    
    /**
     * Save current object into db
     *
     * Call INSERT or UPDATE if unique index is set
     * @param  boolean $forceInsert true to force insert instead of update
     * @return null
     */
    public function save($forceInsert = false)
    {
        if (count($this->dbValues)) {
            $this->connectDB();

            if ($this->{$this->getTableIndex()} != '' && !$forceInsert) {
                $this->update();
                $insert = false;
            } else {
                $this->insert();
                $insert = true;
            }

            // Checking protected variables
            foreach ($this->protectedVariables as $variable) {
                // only if current protected_var is set
                if (isset($this->protectedValues[$variable]) && $this->isProtectedVariableLoaded($variable)) {
                    if ($this->protectedValues[$variable] instanceof Interfaces\ICollection) {
                        if ($insert) {
                            $this->protectedValues[$variable]->setParentIdForAll($this->{$this->getTableIndex()});
                        }
                        $this->protectedValues[$variable]->save();
                    }
                }
            }
        } else {
            throw new \RuntimeException("Object " . get_called_class() . " has no properties to save");
        }
    }

    /**
     * UPDATE current object into database
     * @return null
     */
    private function update()
    {
        $this->connectDB();

        $sqlParams = [];

        $sql  = 'UPDATE `' . $this->getTableName() . '`';
        $sql .= ' SET ';
        

        foreach ($this->dbValues as $key => $val) {
            if (!in_array($key, $this->readOnlyVariables)) {
                $sql .= ' `' . $key . '`=:' . $key .', ';
                $sqlParams[$key] = $val;
            }
        }
        $sql  = substr($sql, 0, -2);
        $sql .= " WHERE `" . $this->getTableIndex() . "` = :SuricateTableIndex";

        $sqlParams[':SuricateTableIndex'] = $this->{$this->getTableIndex()};

        $this->dbLink->query($sql, $sqlParams);
    }

    /**
     * INSERT current object into database
     * @access  private
     * @return null
     */
    private function insert()
    {
        $this->connectDB();
        
        $variables = array_diff($this->dbVariables, $this->readOnlyVariables);

        $sql  = 'INSERT INTO `' . $this->getTableName() . '`';
        $sql .= '(`';
        $sql .= implode('`, `', $variables);
        $sql .= '`)';
        $sql .= ' VALUES (:';
        $sql .= implode(', :', $variables);
        $sql .= ')';

        $sqlParams = [];
        foreach ($variables as $field) {
            $sqlParams[':' . $field] = $this->$field;
        }
        
        $this->dbLink->query($sql, $sqlParams);

        $this->{$this->getTableIndex()} = $this->dbLink->lastInsertId();
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

    public function validate()
    {
        return true;
    }

    public function getValidatorMessages()
    {
        return $this->validatorMessages;
    }
}
