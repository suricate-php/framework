<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface IDBObject
{
    public function getTableName();

    public function getTableIndex();

    public function getDBConfig();

    public function propertyExists($property);

    public function isProtectedVariable(string $variableName);

    public function isDBVariable(string $variableName);

    public function markProtectedVariableAsLoaded(string $variableName);

    public function load($uniqueId);

    public function loadFromSql(string $sql, $sqlParameters);

    public function delete();

    public function save($forceInsert = false);

    public static function instanciate(array $data = []);
}
