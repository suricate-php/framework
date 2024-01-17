<?php

declare(strict_types=1);

namespace Suricate;

use ReflectionClass;
use BadMethodCallException;

class DBCollection extends Collection
{
    /* @var string SQL table name */
    protected $tableName = '';
    /* @var string Item type stored in collection */
    protected $itemsType = '';
    /* @var string Database configuration identifier */
    protected $DBConfig = '';

    protected $mapping = [];
    protected $loadedValues = [];
    protected $lazyLoad = false;

    protected $dbLink = false;
    protected $itemOffset = 0;

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getItemsType(): string
    {
        return $this->itemsType;
    }

    public function getDBConfig(): string
    {
        return $this->DBConfig;
    }

    /**
     * __sleep magic method, permits an inherited DBObject class to be serialized
     * @return Array of properties to serialize
     */
    public function __sleep()
    {
        $discardedProps = ['dbLink'];
        $reflection = new ReflectionClass($this);
        $props = $reflection->getProperties();
        $result = [];
        foreach ($props as $currentProperty) {
            $result[] = $currentProperty->name;
        }

        return array_diff($result, $discardedProps);
    }

    /**
     * Wake up magic method
     * restore dblink to initial value
     */
    public function __wakeup()
    {
        $this->dbLink = false;
    }

    /**
     * Set lazyload flag
     *
     * @param bool $lazyLoad
     * @return DBCollection
     */
    public function setLazyLoad($lazyLoad)
    {
        $this->lazyLoad = $lazyLoad;

        return $this;
    }

    /**
     * Get lazyload flag
     *
     * @return boolean
     */
    public function getLazyLoad(): bool
    {
        return $this->lazyLoad;
    }

    public function purgeItems()
    {
        $this->items = [];
        $this->mapping = [];
        $this->itemOffset = 0;
    }

    /**
     * Implementation of ArrayAccess Interface
     * override parent get to lazyLoad item
     * @param  mixed $offset Offset to get
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->lazyLoad) {
            return parent::offsetGet($offset);
        }

        if (
            isset($this->loadedValues[$offset]) &&
            $this->loadedValues[$offset]
        ) {
            return $this->items[$offset];
        }

        $itemType = $this->itemsType;
        $itemToLoad = new $itemType();
        $itemToLoad->load($this->items[$offset]);

        $this->items[$offset] = $itemToLoad;
        $this->loadedValues[$offset] = true;

        return $this->items[$offset];
    }

    /**
     * Load entire table into collection
     * @return DBCollection Loaded collection
     */
    public static function loadAll()
    {
        $calledClass = get_called_class();
        $collection = new $calledClass();

        $sqlParams = [];

        $sql = "SELECT *";
        $sql .= "   FROM `" . $collection->getTableName() . "`";

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    /**
     * Static wrapper for loadFromSql
     * @param  string     $sql       SQL Statement
     * @param  array      $sqlParams SQL Parameters
     * @return DBCollection Loaded collection
     */
    public static function buildFromSql($sql, $sqlParams = [])
    {
        $calledClass = get_called_class();
        $collection = new $calledClass();

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    /**
     * Load collection from SQL query
     *
     * @param string $sql       SQL query
     * @param array  $sqlParams associative array of SQL params
     * @return DBCollection
     */
    public function loadFromSql($sql, $sqlParams = [])
    {
        if (
            !in_array(
                Interfaces\IDBObject::class,
                class_implements($this->itemsType)
            )
        ) {
            throw new BadMethodCallException(
                'Item type does not implement IDBObject interface'
            );
        }

        $this->connectDB();
        $results = $this->dbLink->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $itemName = $this->getItemsType();
                $item = $itemName::instanciate($currentResult);
                $item->setLoaded();
                $this->addItem($item);
            }
        }

        return $this;
    }

    protected function addItemLink($linkId)
    {
        $this->items[$this->itemOffset] = $linkId;
        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $linkId;
        $this->loadedValues[$this->itemOffset] = false;
        $this->itemOffset++;
    }

    public function lazyLoadFromSql($sql, $sqlParams = [])
    {
        $this->connectDB();
        $results = $this->dbLink->query($sql, $sqlParams)->fetchAll();

        if ($results !== false) {
            foreach ($results as $currentResult) {
                $this->addItemLink(current($currentResult));
            }
        }

        return $this;
    }

    public function craftItem($itemData)
    {
        $itemName = $this->itemsType;

        foreach ($itemData as $data) {
            $newItem = new $itemName();
            $hasData = false;
            foreach ($data as $field => $value) {
                $newItem->$field = $value;
                if ($value != '') {
                    $hasData = true;
                }
            }

            // Only add item if there's data inside
            if ($hasData) {
                $this->addItem($newItem);
            }
        }
    }

    public function save()
    {
        foreach ($this->items as $currentItem) {
            $currentItem->save(true); // Force insert
        }
    }

    public function addItem(Interfaces\IDBObject $item)
    {
        $key = $item->getTableIndex();
        // Add item to items pool
        $this->items[$this->itemOffset] = $item;

        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $item->$key;

        $this->itemOffset++;

        return $this;
    }

    /**
     * Connect to database layer
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
}
