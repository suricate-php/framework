<?php
namespace Suricate\Traits;

trait DatabaseSQLite
{
    private function configurePDOSQLite($params, &$pdoDsn, &$pdoUsername, &$pdoPassword)
    {
        $defaultParams = [
            'username'  => null,
            'password'  => null,
            'memory'    => null,
            'file'      => null,
        ];

        $params = array_merge($defaultParams, $params);
        
        $pdoDsn         = 'sqlite';

        if ($params['memory']) {
            $pdoDsn .= '::memory:';
        } else {
            if ($params['file'] !== null) {
                $pdoDsn .= ':' . $params['file'];
            } else {
                throw new \Exception("Missing SQLite file parameter");
            }
        }

        $pdoUsername    = $params['username'];
        $pdoPassword    = $params['password'];
    }
}