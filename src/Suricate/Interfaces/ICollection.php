<?php
namespace Suricate\Interfaces;

interface ICollection
{
    const SORT_ASC              = 'ASC';
    const SORT_DESC             = 'DESC';

    

    public function purgeItems();

    public function sort(\Closure $closure);

    public function getPossibleValuesFor($args, $withMapping = true);

    public function getValuesFor($name);

    public function getItems();


    
    public function getSlice($start, $nbItems = null);

    public function getFirstItem();
}
