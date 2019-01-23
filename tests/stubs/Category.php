<?php

class Category extends \Suricate\DBObject
{
    protected $tableName = 'categories';
    protected $tableIndex = 'id';

    public function __construct()
    {
        parent::__construct();
        $database = new \Suricate\Database();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db',
        ]);
        $this->dbLink = $database;

        $this->dbVariables = [
            'id',
            'name',
            'parent_id',
        ];
    }
}
