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

    const RELATION_ONE_ONE  = 1;
    const RELATION_ONE_MANY = 2;

    protected $dbVariables = array();
    protected $dbValues = array();
    
    protected $protectedVariables           = array();
    protected $protectedValues              = array();
    protected $loadedProtectedVariables     = array();

    protected $relations                    = array();
    protected $relationValues               = array();
    protected $loadedRelations              = array();

    protected $dbLink = false;
    
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
        } else {
            throw new \RuntimeException("Unknown object variable : " . $name);
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
                return isset($this->relationValues[$name]);
            }
        } else {
            return false;
        }
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
            if ($this->relations[$name]['type'] == self::RELATION_ONE_ONE) {
                $target = $this->relations[$name]['target'];
                $this->relationValues[$name] = with(new $target)->load($this->relations[$name]['source']);
                
                return true;
            } elseif ($this->relations[$name]['type'] == self::RELATION_ONE_MANY) {
                $target = $this->relations[$name]['target'];
                $this->relationValues[$name] = $target::loadForParentId($this->relations[$name]['source']);
_p($this);
                return true;
            }
        }

        return false;
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
     * @return DBObject     Loaded object
     */
    public function load($id)
    {
        $this->connectDB();

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
    
    public function loadFromSql($sql, $sql_params = array())
    {
        $this->connectDB();
        
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
    public static function buildFromArray($data)
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
            $sql .= ' `' . $key . '`=:' . $key .', ';
            $sqlParams[$key] = $val;
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
        
        $sql  = 'INSERT INTO `' . static::TABLE_NAME . '`';
        $sql .= '(`';
        $sql .= implode('`, `', $this->dbVariables);
        $sql .= '`)';
        $sql .= ' VALUES (:';
        $sql .= implode(', :', $this->dbVariables);
        $sql .= ')';

        $sqlParams = array();
        foreach ($this->dbVariables as $field) {
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
}
