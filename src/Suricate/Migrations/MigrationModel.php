<?php

declare(strict_types=1);

namespace Suricate\Migrations;

use Suricate\DBObject;
use Suricate\Suricate;

class MigrationModel extends DBObject
{
    protected $tableName = 'suricate_migrations';
    protected $tableIndex = 'name';

    public function __construct()
    {
        parent::__construct();

        $this->dbVariables = [
            'name',
            'date_added',
        ];

        $this->readOnlyVariables = ['date_added'];
    }

    public function setDBConfig(string $config): self
    {
        $this->DBConfig = $config;
        return $this;
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

    public function createMigrationTable(): int
    {
        $this->connectDB();
        if ($this->dbLink !== false) {

            $dbParameters = $this->dbLink->getConfigParameters();
            switch ($dbParameters['type']) {
                case 'mysql':
                    if (!$this->doesMigrationTableExists()) {
                        $this->createMysqlMigrationTable();
                        return 0;
                    }
                    return -1;

                case 'sqlite':
                    if (!$this->doesMigrationTableExists()) {
                        $this->createSqliteMigrationTable();
                        return 0;
                    }
                    return -1;
            }
        }

        return 0;
    }

    private function createMysqlMigrationTable()
    {
        $sql = <<<EOD
        CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
            `name` varchar(255) NOT NULL UNIQUE,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
EOD;

        $this->dbLink->query($sql);
    }

    private function createSqliteMigrationTable()
    {
        $sql = <<<EOD
        CREATE TABLE IF NOT EXISTS "{$this->tableName}" (
            "name"	TEXT NOT NULL UNIQUE,
            "date_added" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
EOD;
        $this->dbLink->query($sql);

        return $sql;
    }

    private function doesMigrationTableExists(): bool
    {
        $dbParameters = $this->dbLink->getConfigParameters();
        $dbType = $dbParameters['type'];

        if ($dbType === 'sqlite') {
            $sql = "SELECT count(*) FROM sqlite_master WHERE type='table' AND name=:table";
            $sqlParams = [
                "table" => $this->getTableName()
            ];

            $res = $this->dbLink->query($sql, $sqlParams)->fetchColumn();

            return ((int) $res) === 1;
        }


        if ($dbType === 'mysql') {
            $sql = "SELECT count(*) FROM information_schema.tables " .
                " WHERE table_schema=:schema " .
                " AND table_name =:tableName ";

            $sqlParams = [
                "schema" => $dbParameters['database'],
                "tableName" => $this->getTableName()
            ];
            $res = $this->dbLink->query($sql, $sqlParams)->fetchColumn();

            return ((int) $res) === 1;
        }

        return true;
    }
}
