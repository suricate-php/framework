<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface IDBCollection
{
    public static function loadAll();

    /**
     * @return \Suricate\Collection
     */
    public static function buildFromSql($sql, $sqlParams = []);

    // FIXME: not used
    public static function loadForParentId($parentId);

    /**
     * @return \Suricate\Collection
     */
    public function loadFromSql($sql, $sqlParams = []);

    public function lazyLoadFromSql($sql, $sqlParams = []);

    public function setParentIdForAll($parentId);

    public function craftItem($itemData);

    public function save();

    public function addItemLink($linkId);

    public function addItem(IDBObject $item);

    public function getItemsType();

    public function getParentIdName();

    public function getParentId();
}
