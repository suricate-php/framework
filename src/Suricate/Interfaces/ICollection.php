<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface ICollection
{
    public function sort(\Closure $closure);

    public function getPossibleValuesFor($args, $withMapping = true);

    public function getValuesFor($name);

    public function getItems();
}
