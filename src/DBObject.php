<?php
namespace Fwk;

/**
 * DBObject, Pseudo ORM Class
 *
 * Two types of variables are available : 
 * - $dbVariables, an array of fields contained in linked SQL table
 * - $protectedVariables, an array of variables not stored in SQL
 *     that can be triggered on access
 *
 * @package Fwk
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
    const DB_CONFIG     = '';

    protected $dbVariables = array();
    protected $dbValues = array();
    
    protected $protectedVariables           = array();
    protected $protectedValues              = array();
    protected $loadedProtectedVariables     = array();

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
            if (isset($this->dbValues[$name])) {
                $returnValue = $this->dbValues[$name];
            } else {
                $returnValue = null;
            }
        } elseif ($this->isProtectedVariable($name)) {
            if (isset($this->protectedValues[$name]) && $this->isProtectedVariableLoaded($name)) {
                $returnValue = $this->protectedValues[$name];
            } else {
                if (!$this->isProtectedVariableLoaded($name)) {
                    $protectedAccessResult = $this->accessToProtectedVariable($name);

                    if ($protectedAccessResult) {
                        $this->markProtectedVariableAsLoaded($name);
                    }
                }
                if (isset($this->protectedValues[$name])) {
                    $returnValue = $this->protectedValues[$name];
                } else {
                    $returnValue = null;
                }
            }
        } else {
            throw new \InvalidArgumentException('Undefined property ' . $name);
            
            //$returnValue = null;
        }

        return $returnValue;
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
            // Load only one time protected var automatically
            if (!$this->isProtectedVariableLoaded($name)) {
                
                $protectedAccessResult = $this->accessToProtectedVariable($name);

                if ($protectedAccessResult) {
                    $this->markProtectedVariableAsLoaded($name);
                }
            }

            return isset($this->protectedValues[$name]);
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
     *     <li>legacy property</li>
     * </ul>
     * @param  string $property Property name
     * @return boolean           true if exists
     */
    public function propertyExists($property)
    {
        return $this->isDBVariable($property) || $this->isProtectedVariable($property) || property_exists($this, $property);
    }
   
    public function isProtectedVariable($variable_name)
    {
        return in_array($variable_name, $this->protectedVariables);
    }

    public function isDBVariable($variable_name)
    {
        return in_array($variable_name, $this->dbVariables);
    }


    public function markProtectedVariableAsLoaded($variable_name)
    {
        if ($this->isProtectedVariable($variable_name)) {
            $this->loadedProtectedVariables[$variable_name] = true;
        }
    }

    protected function isProtectedVariableLoaded($variable)
    {
        return isset($this->loadedProtectedVariables[$variable]);
    }

    /**
     * __sleep magic method, permits an inherited DBObject class to be serialized
     * @return Array of properties to serialize
     */
    public function __sleep()
    {
        $this->dbLink = false;
        $reflection = new \ReflectionClass($this);
        $props   = $reflection->getProperties();
        $result = array();
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
    
    public function loadFromSql($sql, $sql_params)
    {
        if ($this->dbLink === false) {
            $this->connectDB();
        }
        
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
        if (static::TABLE_INDEX != '') {
            $query  = "DELETE FROM `" . static::TABLE_NAME . "`";
            $query .= " WHERE `" . static::TABLE_INDEX . "` = :id";

            $queryParams = array();
            $queryParams['id'] = $this->{static::TABLE_INDEX};

            if ($this->dbLink === false) {
                $this->connectDB();
            }
            
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
            if ($this->dbLink === false) {
                $this->connectDB();
            }

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
        $sqlParams = array();

        $sql  = 'UPDATE `' . static::TABLE_NAME . '`';
        $sql .= ' SET ';
        
        foreach ($this->dbValues as $key => $val) {
            $sql .= ' `' . $key . '`=:' . $key .', ';
            $sqlParams[$key] = $val;
        }
        $sql  = substr($sql, 0, -2);
        $sql .= " WHERE `" . static::TABLE_INDEX . "` = :FwkTableIndex";

        $sqlParams[':FwkTableIndex'] = $this->{static::TABLE_INDEX};

        $this->dbLink->query($sql, $sqlParams);
    }

    /**
     * INSERT current object into database
     * @access  private
     * @return null
     */
    private function insert()
    {
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
        $this->dbLink = Fwk::Database();
        if (static::DB_CONFIG != '') {
            $this->dbLink->setConfig(static::DB_CONFIG);
        }
    }
    
    
    protected function accessToProtectedVariable($name)
    {

    }
}
