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
        // FIXME: test with migration outside of migration folder (eg: media module )
        $this->registeredMigrations[$migration->getName()] = $migration->getSQL();
    }

    public function scanForMigrations()
    {
        $files = glob(app_path('migrations/*.php'));
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

    public function initMigrationTable(): int
    {
        $this->scanForMigrations();
        $migrationModel = new MigrationModel();


        return $migrationModel->createMigrationTable();
    }

    public function listMigrations()
    {
        $this->scanForMigrations();

        $migrations = MigrationModelList::loadAll();
        $alreadyMigrated = [];
        $result = [];
        foreach ($migrations as $migration) {
            $alreadyMigrated[$migration->name] = true;
            $result[$migration->name] = $migration->date_added;
        }
        foreach (array_keys($this->registeredMigrations) as $regMigrationName) {
            if (isset($alreadyMigrated[$regMigrationName])) {
                continue;
            }
            $result[$regMigrationName] = false;
        }
        ksort($result);
        return $result;
    }

    public function doMigrations()
    {
        echo "[Migration] Starting migrations\n";

        $migrations = $this->listMigrations();
        $migrationsToDo = array_filter($migrations, function ($item) {
            return $item === false;
        });

        if (count($migrationsToDo) === 0) {
            echo "[Migration] Nothing to migrate\n";
            return true;
        }
        foreach (array_keys($migrationsToDo) as $migrationName) {
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
                echo "[Migration] ❌ Failed to execute migration: ".$e->getMessage() . "\n";
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
        $migrationName = 'v' . date('Ymdhis');

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

    // Leave empty string if you want to use default config name
    public function getConfigName(): string
    {
        return '';
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
