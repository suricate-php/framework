<?php
namespace Fwk\Interfaces;

interface IDBObject
{
    public function propertyExists($property);

    public function isProtectedVariable($variableName);

    public function isDBVariable($variableName);

    public function markProtectedVariableAsLoaded($variableName);

    public function load($uniqueId);

    public function loadFromSql($sql, $sqlParameters);

    public function delete();

    public function save($forceInsert = false);

    public static function buildFromArray($data);
}
