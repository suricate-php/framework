<?php

declare(strict_types=1);

namespace Suricate\Traits;

trait DatabaseMySQL
{
    private function configurePDOMySQL(
        $params,
        &$pdoDsn,
        &$pdoUsername,
        &$pdoPassword,
        &$pdoAttributes
    ) {
        $defaultParams = [
            'hostname' => null,
            'database' => null,
            'username' => null,
            'password' => null,
            'encoding' => null
        ];

        $params = array_merge($defaultParams, $params);

        $pdoDsn =
            'mysql:host=' .
            $params['hostname'] .
            ';dbname=' .
            $params['database'];
        $pdoUsername = $params['username'];
        $pdoPassword = $params['password'];
        if ($params['encoding'] != null) {
            $pdoAttributes[\PDO::MYSQL_ATTR_INIT_COMMAND] =
                "SET NAMES " . $params['encoding'];
        }
    }
}
