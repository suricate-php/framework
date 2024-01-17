<?php

declare(strict_types=1);

namespace Suricate\Migrations;

use Suricate\DBCollection;

class MigrationModelList extends DBCollection
{
    protected $tableName = 'suricate_migrations';
    protected $itemsType = MigrationModel::class;
}
