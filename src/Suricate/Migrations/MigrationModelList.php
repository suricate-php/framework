<?php

declare(strict_types=1);

namespace Suricate\Migrations;

use Suricate\DBCollection;
use Suricate\Suricate;

class MigrationModelList extends DBCollection
{
    protected $tableName = 'suricate_migrations';
    protected $itemsType = MigrationModel::class;

    public function setDBConfig(string $config): self 
    {
        $this->DBConfig = $config;
        return $this;
    }

    public static function loadAllWithConfig(string $configName)
    {
        $calledClass = new self();
        $collection = new $calledClass();
        $collection->setDBConfig($configName);

        $sqlParams = [];

        $sql = "SELECT *";
        $sql .= "   FROM `" . $collection->getTableName() . "`";

        $collection->loadFromSql($sql, $sqlParams);

        return $collection;
    }

    protected function connectDB()
    {
        if (!$this->dbLink) {
            $this->dbLink = Suricate::Database(true);
            if ($this->getDBConfig() !== '') {
                $this->dbLink->setConfig($this->getDBConfig());
            }
            
        }
    }
}
