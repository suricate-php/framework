<?php

class CategoriesList extends \Suricate\DBCollection
{
    protected $tableName = 'categories';
    protected $itemsType = Category::class;
    protected $parentIdField = 'parent_id';

    public function __construct()
    {
        parent::__construct();
        $database = new \Suricate\Database();
        $database->configure([
            'type' => 'sqlite',
            'file' => '/tmp/test.db',
        ]);
        $this->dbLink = $database;
    }
}
