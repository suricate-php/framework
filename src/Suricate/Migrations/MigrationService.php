<?php

declare(strict_types=1);

namespace Suricate\Migrations;

use Exception;
use Suricate\Interfaces\IMigration;
use Suricate\Service;
use Suricate\Suricate;

/**
 * DB Migration extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 */

class MigrationService extends Service
{
    protected array $registeredMigrations = [];

    public function registerMigration(IMigration $migration)
    {
        $configName = $migration->getConfigName() === '' ? 'default' : $migration->getConfigName();
        $this->registeredMigrations[$configName][$migration->getName()] = $migration->getSQL();
    }

    public function scanForMigrations($path = null)
    {
        $qualifiedPath = $path;
        if ($path === null) {
            $qualifiedPath = app_path('migrations/');
        }

        $files = glob($qualifiedPath . '*.php');

        foreach ($files as $file) {
            $migrationClassName = str_replace('.php', '', basename($file));
            include($file);
            // Class is defined inside the file
            if (class_exists($migrationClassName)) {
                $migration = new $migrationClassName();
                if ($migration instanceof IMigration) {
                    $this->registerMigration($migration);
                }
            }
        }
    }

    public function initMigrationTable(string $configName): int
    {
        $migrationModel = new MigrationModel();
        $migrationModel->setDBConfig($configName);
        return $migrationModel->createMigrationTable();
    }

    public function listMigrations()
    {
        $db = Suricate::Database(true);
        $dbConfigs = $db->getConfigs();
        unset($db);
        $result = [];

        $this->scanForMigrations();
        $suricateServices = Suricate::listServices();

        foreach ($suricateServices as $service) {
            // Check all registered Suricate services if their
            // migration handler has migrations to register
            $serviceInstance = Suricate::$service();
            if ($serviceInstance instanceof Service) {
                $serviceInstance->registerMigrations();
            }
        }

        // Iterate through all databases configuration
        foreach (array_keys($dbConfigs) as $dbConfigName) {
            $result[$dbConfigName] = [];

            // Create migration table if needed
            $res = $this->initMigrationTable($dbConfigName);
            switch ($res) {
                case 0:
                    echo '[Migration] ✅ Migration table created successfully for config "' . $dbConfigName . '"' . "\n";
                    break;
                case 1:
                    echo '[Migration] ❌ Unsupported database type (config: "' . $dbConfigName . '")' . "\n";
                    break;
            }

            // Load all DB listed migration for config
            $migrations = MigrationModelList::loadAllWithConfig($dbConfigName);
            $alreadyMigrated = [];

            foreach ($migrations as $migration) {
                $alreadyMigrated[$migration->name] = true;
                $result[$dbConfigName][$migration->name] = $migration->date_added;
            }

            // 'ALL' config name for migrations that should me applied to all configs (eg: media-manager)
            $confChecks = ['ALL', $dbConfigName];
            foreach ($confChecks as $currentConfigName) {
                if (isset($this->registeredMigrations[$currentConfigName])) {
                    foreach (array_keys($this->registeredMigrations[$currentConfigName]) as $regMigrationName) {
                        if (isset($alreadyMigrated[$regMigrationName])) {
                            continue;
                        }
                        $result[$dbConfigName][$regMigrationName] = false;
                    }
                }
            }
            ksort($result[$dbConfigName]);
        }

        return $result;
    }

    public function doMigrations()
    {
        echo "[Migration] Starting migrations\n";

        $globalMigrations = $this->listMigrations();
        $migrationsToDo = [];
        foreach ($globalMigrations as $migrations) {
            foreach ($migrations as $migrationName => $migrationDate) {
                if ($migrationDate === false) {
                    $migrationsToDo[] = $migrationName;
                }
            }
        }

        if (count($migrationsToDo) === 0) {
            echo "[Migration] Nothing to migrate\n";
            return true;
        }
        foreach ($migrationsToDo as $migrationName) {
            echo "[Migration] Migration $migrationName:\n";
            $migration = new $migrationName();
            $sql = trim($migration->getSQL());
            if ($sql === '') {
                // Ignore
                continue;
            }
            $db = Suricate::Database(true);
            $db->setConfig($migration->getConfigName());
            try {
                $db->query($migration->getSQL());
            } catch (Exception $e) {
                echo "[Migration] ❌ Failed to execute migration: " . $e->getMessage() . "\n";
                continue;
            }

            echo "[Migration] ✅ migration OK\n";
            $migrationCheck = new MigrationModel();
            $migrationCheck->setDBConfig($migration->getConfigName());
            $migrationCheck->name = $migration->getName();
            $migrationCheck->save();
        }
    }

    public function createMigration(): string|bool
    {
        $migrationName = 'v' . date('YmdHis');

        $template = <<<EOD
<?php

use Suricate\Interfaces\IMigration;

class {$migrationName} implements IMigration
{
    public function getName(): string
    {
        return __CLASS__;
    }

    public function getSQL(): string
    {
        return '';
    }

    public function getConfigName(): string
    {
        return 'default';
    }
}
EOD;
        $filename = app_path('migrations/' . $migrationName . '.php');
        $directory = pathinfo($filename, PATHINFO_DIRNAME);
        if (!is_dir($directory)) {
            $ret = mkdir($directory, 0755, true);
            if (!$ret) {
                return false;
            }
        }
        $fp = fopen($filename, 'w');
        if ($fp === false) {
            return false;
        }
        $ret = fputs($fp, $template);
        fclose($fp);
        if ($ret !== false) {
            return $migrationName;
        }
        return false;
    }
}
