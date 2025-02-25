<?php

declare(strict_types=1);

namespace Suricate;

use Suricate\Traits\DBObjectRelations;
use Suricate\Traits\DBObjectProtected;
use Suricate\Traits\DBObjectExport;

use RuntimeException;
use InvalidArgumentException;
use ReflectionClass;

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
#[\AllowDynamicProperties]
class DBObject implements Interfaces\IDBObject
{
    use DBObjectRelations;
    use DBObjectProtected;
    use DBObjectExport;

    /** @var string Linked SQL Table */
    protected $tableName = '';

    /** @var string Unique ID of the SQL table */
    protected $tableIndex = '';

    /** @var string Database config name (optionnal) */
    protected $DBConfig = '';

    /**
     * @const RELATION_ONE_ONE : Relation one to one
     */
    const RELATION_ONE_ONE = 1;
    /**
     * @const RELATION_ONE_MANY : Relation one to many
     */
    const RELATION_ONE_MANY = 2;
    /**
     * @const RELATION_MANY_MANY : Relation many to many
     */
    const RELATION_MANY_MANY = 3;

    protected $loaded = false;
    protected $dbVariables = [];
    protected $dbValues = [];

    protected $readOnlyVariables = [];

    protected $dbLink = false;

    /**
     * INSERT IGNORE toggle flag
     *
     * @var boolean
     */
    protected $insertIgnore = false;

    protected $validatorMessages = [];

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
        }
        if ($this->isProtectedVariable($name)) {
            return $this->getProtectedVariable($name);
        }
        if ($this->isRelation($name)) {
            return $this->getRelation($name);
        }
        if (!empty($this->$name)) {
            return $this->$name;
        }

        throw new InvalidArgumentException('Undefined property ' . $name);
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
            // Cast to string as PDO only handle string or NULL
            $this->dbValues[$name] = is_null($value) ? $value : (string) $value;
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
        }
        if ($this->isProtectedVariable($name)) {
            // Load only one time protected variable automatically
            if (!$this->isProtectedVariableLoaded($name)) {
                $protectedAccessResult = $this->accessToProtectedVariable(
                    $name
                );

                if ($protectedAccessResult) {
                    $this->markProtectedVariableAsLoaded($name);
                }
            }
            return isset($this->protectedValues[$name]);
        }
        if ($this->isRelation($name)) {
            if (!$this->isRelationLoaded($name)) {
                $this->loadRelation($name);
                $this->markRelationAsLoaded($name);
            }
            return isset($this->relationValues[$name]);
        }

        return false;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public static function tableName()
    {
        return with(new static())->getTableName();
    }

    /**
     * Get Table Index
     *
     * @return string
     */
    public function getTableIndex()
    {
        return $this->tableIndex;
    }

    /**
     * Get table index
     *
     * @return string
     */
    public static function tableIndex()
    {
        return with(new static())->getTableIndex();
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
        $reflection = new ReflectionClass($this);
        $props = $reflection->getProperties();
        $result = [];
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
    public function isDBVariable(string $name)
    {
        return in_array($name, $this->dbVariables);
    }

    private function resetLoadedVariables()
    {
        $this->loadedProtectedVariables = [];
        $this->loadedRelations = [];
        $this->loaded = false;

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
        return $this->isDBVariable($property) ||
            $this->isProtectedVariable($property) ||
            $this->isRelation($property) ||
            property_exists($this, $property);
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

        $query = "SELECT *";
        $query .= " FROM `" . $this->getTableName() . "`";
        $query .= " WHERE";
        $query .= "     `" . $this->getTableIndex() . "` =  :id";

        $params = [];
        $params['id'] = $id;

        return $this->loadFromSql($query, $params);
    }

    /**
     * Load an object according to fieldName=fieldValue
     *
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return static|bool
     */
    public function loadForField(string $fieldName, $fieldValue) {
        $this->connectDB();
        $this->resetLoadedVariables();

        $query = "SELECT *";
        $query .= " FROM `" . $this->getTableName() . "`";
        $query .= " WHERE";
        $query .= "     `" . $fieldName . "` =  :fieldValue";

        $params = [];
        $params['fieldValue'] = $fieldValue;

        return $this->loadFromSql($query, $params);
    }

    /**
     * Check if object is linked to entry in database
     *
     * @return boolean
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Mark object as loaded
     * Useful when hydrated from collection, as individual object is not loaded
     * via the load() method
     * @return static
     */
    public function setLoaded()
    {
        $this->loaded = true;

        return $this;
    }

    public function loadOrFail($index)
    {
        $this->load($index);
        if ($this->{$this->getTableIndex()} != $index) {
            throw (new Exception\ModelNotFoundException())->setModel(
                get_called_class()
            );
        }

        return $this;
    }

    public static function loadOrCreate($arg)
    {
        $obj = static::loadOrInstanciate($arg);
        $obj->save();

        return $obj;
    }

    /**
     * Load existing object by passing properties or instanciate if
     *
     * @param mixed $arg
     * @return static
     */
    public static function loadOrInstanciate($arg)
    {
        $calledClass = get_called_class();
        $obj = new $calledClass();

        // got only one parameter ? consider as table index value (id)
        if (!is_array($arg)) {
            $arg = [$obj->getTableIndex() => $arg];
        }

        $sql = "SELECT *";
        $sql .= " FROM `" . $obj->getTableName() . "`";
        $sql .= " WHERE ";

        $sqlArray = [];
        $params = [];
        $offset = 0;
        foreach ($arg as $key => $val) {
            if (is_null($val)) {
                $sqlArray[] = '`' . $key . '` IS :arg' . $offset;
            } else {
                $sqlArray[] = '`' . $key . '`=:arg' . $offset;
            }
            $params['arg' . $offset] = $val;
            $offset++;
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
     * @return static|bool
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
            $this->loaded = true;
            return $this;
        }

        return false;
    }

    /**
     * Construct an DBObject from an array
     * @param  array $data  associative array
     * @return static       Built DBObject
     */
    public static function instanciate(array $data = [])
    {
        $calledClass = get_called_class();
        $orm = new $calledClass();

        return $orm->hydrate($data);
    }

    /**
     * Hydrate object (set dbValues)
     *
     * @param array $data
     * @return static
     */
    public function hydrate(array $data = [])
    {
        foreach ($data as $key => $val) {
            if ($this->propertyExists($key)) {
                $this->$key = $val;
            }
        }

        return $this;
    }

    /**
     * Create an object and save it to database
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data = [])
    {
        $obj = static::instanciate($data);
        $obj->save();

        return $obj;
    }

    /**
     * Delete record from SQL Table
     *
     * Delete record link to current object, according SQL Table unique id
     * @return void
     */
    public function delete()
    {
        $this->connectDB();

        if ($this->getTableIndex() !== '') {
            $query = "DELETE FROM `" . $this->getTableName() . "`";
            $query .= " WHERE `" . $this->getTableIndex() . "` = :id";

            $queryParams = [];
            $queryParams['id'] = $this->{$this->getTableIndex()};

            $this->dbLink->query($query, $queryParams);
        }
    }

    public function setInsertIgnore(bool $flag)
    {
        $this->insertIgnore = $flag;

        return $this;
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

            if ($this->isLoaded() && !$forceInsert) {
                $this->update();
                return null;
            }

            $this->insert();
            return null;
        }

        throw new RuntimeException(
            "Object " . get_called_class() . " has no properties to save"
        );
    }

    /**
     * UPDATE current object into database
     * @return void
     */
    private function update()
    {
        $this->connectDB();

        $sqlParams = [];

        $sql = 'UPDATE `' . $this->getTableName() . '`';
        $sql .= ' SET ';

        foreach ($this->dbValues as $key => $val) {
            if (!in_array($key, $this->readOnlyVariables)) {
                $sql .= ' `' . $key . '`=:' . $key . ', ';
                $sqlParams[$key] = $val;
            }
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE `" . $this->getTableIndex() . "` = :SuricateTableIndex";

        $sqlParams[':SuricateTableIndex'] = $this->{$this->getTableIndex()};

        $this->dbLink->query($sql, $sqlParams);
    }

    /**
     * INSERT current object into database
     * @access  private
     * @return void
     */
    private function insert()
    {
        $this->connectDB();

        $variables = array_diff($this->dbVariables, $this->readOnlyVariables);
        $ignoreFlag = $this->insertIgnore ? 'IGNORE ' : '';

        $sql = 'INSERT ' . $ignoreFlag . 'INTO `' . $this->getTableName() . '`';
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
        $this->loaded = true;
        $this->{$this->getTableIndex()} = $this->dbLink->lastInsertId();
    }

    /**
     * Connect to DB layer
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function connectDB()
    {
        // FIXME: potential reuse of connection. If using >= 2 differents DB Config
        // the missing `true` in Database() call keeps querying the previously connected DB
        // Check if performance issue of passing `true` everytime
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

    public function getValidatorMessages(): array
    {
        return $this->validatorMessages;
    }
}
