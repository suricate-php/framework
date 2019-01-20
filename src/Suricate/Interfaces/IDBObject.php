<?php
namespace Suricate\Interfaces;

interface IDBObject
{
    public function getTableName();

    public function getTableIndex();

    public function getDBConfig();

    public function propertyExists($property);

    public function isProtectedVariable($variableName);

    public function isDBVariable($variableName);

    public function markProtectedVariableAsLoaded($variableName);

    public function load($uniqueId);

    public function loadFromSql(string $sql, $sqlParameters);

    public function delete();

    public function save($forceInsert = false);

    public static function instanciate($data = array());
}
