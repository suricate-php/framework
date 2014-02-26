<?php
namespace Fwk\Interfaces;

use Fwk;

interface ICollection
{
    const SORT_ASC              = 'ASC';
    const SORT_DESC             = 'DESC';
    
    public static function loadAll();
    
    public static function buildFromSql($sql, $sqlParams = array());

    public static function loadForParentId($parentId);

    public function loadFromSql($sql, $sqlParams = array());

    public function lazyLoadFromSql($sql, $sqlParams = array());

    public function setParentIdForAll($parentId);
    
    public function craftItem($itemData);
    
    public function save();

    public function purgeItems();

    public function sort($field, $order);
    
    public function getPossibleValuesFor($args, $withMapping = true);
    
    public function getValuesFor($name);
    
    public function getItems();
    
    public function addItemLink($link_id);

    public function addItem(Fwk\DBObject $item);
    
    public function getItemsType();
    
    public function getParentIdName();
    
    public function getParentId();
    
    public function getSlice($start, $nbItems = null);

    public function getFirstItem();
}
