<?php
namespace Suricate;

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
    /*
    * @const TABLE_NAME : Linked SQL Table
    */
    const TABLE_NAME    = '';

    /*
    * @const TABLE_INDEX : Unique Id of the SQL Table
    */
    const TABLE_INDEX   = '';

    /**
     * @const DB_CONFIG : Database configuration identifier
     */
    const DB_CONFIG     = '';

    const RELATION_ONE_ONE      = 1;
    const RELATION_ONE_MANY     = 2;
    const RELATION_MANY_MANY    = 3;

    protected $dbVariables = array();
    protected $dbValues = array();
    
    protected $protectedVariables           = array();
    protected $protectedValues              = array();
    protected $loadedProtectedVariables     = array();

    protected $readOnlyVariables            = array();

    protected $relations                    = array();
    protected $relationValues               = array();
    protected $loadedRelations              = array();

    protected $dbLink = false;

    protected $validatorMessages = array();
    
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
        } else {
            throw new \InvalidArgumentException('Undefined property ' . $name);
        }
    }
    
    private function getDBVariable($name)
    {
        if (isset($this->dbValues[$name])) {
            return $this->dbValues[$name];
        } else {
            return null;
        }
    }

    private function getProtectedVariable($name)
    {
        // Variable exists, and is already loaded
        if (isset($this->protectedValues[$name]) && $this->isProtectedVariableLoaded($name)) {
            return $this->protectedValues[$name];
        } else {
            // Variable has not been loaded
            if (!$this->isProtectedVariableLoaded($name)) {
                if ($this->accessToProtectedVariable($name)) {
                    $this->markProtectedVariableAsLoaded($name);
                }
            }

            if (isset($this->protectedValues[$name])) {
                return $this->protectedValues[$name];
            } else {
                return null;
            }
        }
    }

    private function getRelation($name)
    {
        if (isset($this->relationValues[$name]) && $this->isRelationLoaded($name)) {
            return $this->relationValues[$name];
        } else {
            if (!$this->isRelationLoaded($name)) {
                if ($this->loadRelation($name)) {
                    $this->markRelationAsLoaded($name);
                }
            }

            if (isset($this->relationValues[$name])) {
                return $this->relationValues[$name];
            } else {
                return null;
            }
        }
    }

    /**
     * Magic setter
     *
     * Set a property to defined value
     * Assignment in this order :
     * <ul>
     *     <li>$dbVariable</li>
     *     <li>$protectedVariable</li>
     *  </ul>
     * @param [type] $name  [description]
     * @param [type] $value [description]
     */
    public function __set($name, $value)
    {
        if ($this->isDBVariable($name)) {
            $this->dbValues[$name] = $value;
        } elseif ($this->isProtectedVariable($name)) {
            $this->protectedValues[$name] = $value;
        } elseif ($this->isRelation($name)) {
            $this->relationValues[$name] = $value;
        } else {
            $this->$name = $value;
        }
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
                $relationResult = $this->loadRelation($name);

                if ($relationResult) {
                    $this->markRelationAsLoaded($name);
                }
            }
            return isset($this->relationValues[$name]);
        } else {
            return false;
        }
    }

    private function resetLoadedVariables()
    {
        $this->loadedProtectedVariables = array();
        $this->loadedRelations          = array();

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
        return $this->isDBVariable($property) || $this->isProtectedVariable($property) || $this->isRelation($property) || property_exists($this, $property);
    }
   
   /**
    * Check if variable is a protected variable
    * @param  string  $name variable name
    * @return boolean
    */
    public function isProtectedVariable($name)
    {
        return in_array($name, $this->protectedVariables);
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
     * Check if variable is predefined relation
     * @param  string  $name variable name
     * @return boolean
     */
    protected function isRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * Mark a protected variable as loaded
     * @param  string $name varialbe name
     * @return void
     */
    public function markProtectedVariableAsLoaded($name)
    {
        if ($this->isProtectedVariable($name)) {
            $this->loadedProtectedVariables[$name] = true;
        }
    }

    /**
     * Mark a relation as loaded
     * @param  string $name varialbe name
     * @return void
     */
    protected function markRelationAsLoaded($name)
    {
        if ($this->isRelation($name)) {
            $this->loadedRelations[$name] = true;
        }
    }

    /**
     * Check if a protected variable already have been loaded
     * @param  string  $name Variable name
     * @return boolean
     */
    protected function isProtectedVariableLoaded($name)
    {
        return isset($this->loadedProtectedVariables[$name]);
    }

     /**
     * Check if a relation already have been loaded
     * @param  string  $name Variable name
     * @return boolean
     */
    protected function isRelationLoaded($name)
    {
        return isset($this->loadedRelations[$name]);
    }

    protected function loadRelation($name)
    {
        if (isset($this->relations[$name])) {
            switch ($this->relations[$name]['type']) {
                case self::RELATION_ONE_ONE:
                    return $this->loadRelationOneOne($name);
                case self::RELATION_ONE_MANY:
                    return $this->loadRelationOneMany($name);
                case self::RELATION_MANY_MANY:
                    return $this->loadRelationManyMany($name);
            }
        }

        return false;
    }

    private function loadRelationOneOne($name)
    {
        $target = $this->relations[$name]['target'];
        $source = $this->relations[$name]['source'];
        $this->relationValues[$name] = new $target();
        $this->relationValues[$name]->load($this->$source);
        
        return true;
    }

    private function loadRelationOneMany($name)
    {
        $target         = $this->relations[$name]['target'];
        $parentId       = $this->{$this->relations[$name]['source']};
        $parentIdField  = isset($this->relations[$name]['target_field']) ? $this->relations[$name]['target_field'] : null;
        $validate       = dataGet($this->relations[$name], 'validate', null);
        
        $this->relationValues[$name] = $target::loadForParentId($parentId, $parentIdField, $validate);

        return true;
    }

    private function loadRelationManyMany($name)
    {
        $pivot      = $this->relations[$name]['pivot'];
        $sourceType = $this->relations[$name]['source_type'];
        $target     = dataGet($this->relations[$name], 'target');
        $validate   = dataGet($this->relations[$name], 'validate', null);

        $this->relationValues[$name] = $pivot::loadFor($sourceType, $this->{$this->relations[$name]['source']}, $target, $validate);
        
        return true;
    }

    /**
     * __sleep magic method, permits an inherited DBObject class to be serialized
     * @return Array of properties to serialize
     */
    public function __sleep()
    {
        $this->dbLink   = false;
        $reflection     = new \ReflectionClass($this);
        $props          = $reflection->getProperties();
        $result         = array();
        foreach ($props as $currentProperty) {
            $result[] = $currentProperty->name;
        }
        return $result;
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

        if ($id != '') {
            $query  = "SELECT *";
            $query .= " FROM `" . static::TABLE_NAME ."`";
            $query .= " WHERE";
            $query .= "     `" . static::TABLE_INDEX . "` =  :id";
            
            $params         = array();
            $params['id']   = $id;

            return $this->loadFromSql($query, $params);
        } else {
            return $this;
        }
    }

    public function isLoaded()
    {
        return $this->{static::TABLE_INDEX} !== null;
    }

    public function loadOrFail($id)
    {
        $this->load($id);
        if ($id == '' || $this->{static::TABLE_INDEX} != $id) {
            throw (new Exception\ModelNotFoundException)->setModel(get_called_class());
        } else {
            return $this;
        }
    }

    public static function loadOrCreate($arg)
    {
        $obj = static::loadOrInstanciate($arg);
        $obj->save();

        return $obj;
    }

    public static function loadOrInstanciate($arg)
    {
        if (!is_array($arg)) {
            $arg = array(static::TABLE_INDEX => $arg);
        }

        $sql = "SELECT *";
        $sql .= " FROM " . static::TABLE_NAME;
        $sql .= " WHERE ";

        $sqlArray   = array();
        $params     = array();
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



        $calledClass = get_called_class();
        $obj = new $calledClass;
        if (!$obj->loadFromSql($sql, $params)) {
            foreach($arg as $property => $value) {
                $obj->$property = $value;
            }
        }

        return $obj;
    }
    
    public function loadFromSql($sql, $sql_params = array())
    {
        $this->connectDB();
        $this->resetLoadedVariables();
        
        $results = $this->dbLink->query($sql, $sql_params)->fetch();

        if ($results !== false) {
            foreach ($results as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        } else {
            return false;
        }
    }

    /**
     * Construct an DBObject from an array
     * @param  array $data  associative array
     * @return DBObject       Built DBObject
     */
    public static function instanciate($data = array())
    {
        $calledClass    = get_called_class();
        $orm            = new $calledClass;

        foreach ($data as $key => $val) {
            if ($orm->propertyExists($key)) {
                $orm->$key = $val;
            }
        }
        
        return $orm;
    }

    public static function create($data = array())
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

        if (static::TABLE_INDEX != '') {
            $query  = "DELETE FROM `" . static::TABLE_NAME . "`";
            $query .= " WHERE `" . static::TABLE_INDEX . "` = :id";

            $queryParams = array();
            $queryParams['id'] = $this->{static::TABLE_INDEX};
            
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

            if ($this->{static::TABLE_INDEX} != '' && !$forceInsert) {
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
                            $this->protectedValues[$variable]->setParentIdForAll($this->id);
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

        $sqlParams = array();

        $sql  = 'UPDATE `' . static::TABLE_NAME . '`';
        $sql .= ' SET ';
        

        foreach ($this->dbValues as $key => $val) {
            if (!in_array($key, $this->readOnlyVariables)) {
                $sql .= ' `' . $key . '`=:' . $key .', ';
                $sqlParams[$key] = $val;
            }
        }
        $sql  = substr($sql, 0, -2);
        $sql .= " WHERE `" . static::TABLE_INDEX . "` = :SuricateTableIndex";

        $sqlParams[':SuricateTableIndex'] = $this->{static::TABLE_INDEX};

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

        $sql  = 'INSERT INTO `' . static::TABLE_NAME . '`';
        $sql .= '(`';
        $sql .= implode('`, `', $variables);
        $sql .= '`)';
        $sql .= ' VALUES (:';
        $sql .= implode(', :', $variables);
        $sql .= ')';

        $sqlParams = array();
        foreach ($variables as $field) {
            $sqlParams[':' . $field] = $this->$field;
        }
        
        $this->dbLink->query($sql, $sqlParams);

        $this->{static::TABLE_INDEX} = $this->dbLink->lastInsertId();
    }
    
    protected function connectDB()
    {
        if (!$this->dbLink) {
            $this->dbLink = Suricate::Database();
            if (static::DB_CONFIG != '') {
                $this->dbLink->setConfig(static::DB_CONFIG);
            }
        }
    }
    
    
    protected function accessToProtectedVariable($name)
    {
        return false;
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
