<?php

declare(strict_types=1);

namespace Suricate\Console;

use Suricate\Console;
use Suricate\Suricate;

class Migration
{
    protected $app;

    public function __construct(Suricate $app)
    {
        $this->app = $app;
    }

    public function execute(array $arguments): int
    {
        $command = $arguments[0] ?? '';
        switch ($command) {
            case 'migrate':
                return $this->commandMigrate();
            case 'init':
                return $this->commandInit();
            case 'list':
                return $this->commandList();
            case 'create':
                return $this->commandCreate();
            default:
                return $this->commandHelp();
        }
    }

    private function commandMigrate()
    {
        Suricate::Migration()->doMigrations();
        return 0;
    }

    private function commandInit(): int
    {
        $result = Suricate::Migration()->initMigrationTable();
        switch ($result) {
            case -1:
                echo '❌ Migration table already exists!' . "\n";
                return 1;
            case 0:
                echo '✅ Migration table created successfully' . "\n";
                return 0;
            case 1:
                echo '❌ Unsupported database type' . "\n";
                return 1;
        }
    }

    private function commandList(): int
    {
        $migrations = Suricate::Migration()->listMigrations();
        if (count($migrations) === 0) {
            echo "No migration\n";
            return 0;
        }

        echo str_repeat("-", 76) . "\n";
        foreach ($migrations as $migrationKey=>$migrationDate) {
            echo "| " . str_pad(trim($migrationKey), 50, ' ', STR_PAD_RIGHT) . " | " . ($migrationDate !== false ? $migrationDate : str_pad('-', 19, ' ', STR_PAD_BOTH)). " |\n";
        }
        echo str_repeat("-", 76) . "\n";

        return 0;
    }

    private function commandCreate(): int
    {
       $migrationName = Suricate::Migration()->createMigration();
       if ($migrationName === false) {
            echo "Failed to create migration file\n";
            return 1;
       }

        echo "Migration $migrationName created successfully\n";
        
        return 0;
    }
    private function commandHelp(): int
    {
        $str = "Help:" . "\n";
        $str .= Console::coloredString('init:', 'green') . "\n";
        $str .= "\tInitialize the migrations table\n";
        $str .= Console::coloredString('list:', 'green') . "\n";
        $str .= "\tList migrations\n";
        $str .= Console::coloredString('migrate:', 'green') . "\n";
        $str .= "\tExecute pending migrations\n";

        echo $str;
        return 0;
    }
}
