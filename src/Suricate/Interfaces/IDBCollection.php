<?php
namespace Suricate\Interfaces;

interface IDBCollection
{
    public static function loadAll();

    /**
     * @return \Suricate\Collection
     */
    public static function buildFromSql($sql, $sqlParams = array());

    public static function loadForParentId($parentId);

    /**
     * @return \Suricate\Collection
     */
    public function loadFromSql($sql, $sqlParams = array());

    public function lazyLoadFromSql($sql, $sqlParams = array());

    public function setParentIdForAll($parentId);

    public function craftItem($itemData);

    public function save();

    public function addItemLink($link_id);

    public function addItem(IDBObject $item);

    public function getItemsType();

    public function getParentIdName();

    public function getParentId();
}
