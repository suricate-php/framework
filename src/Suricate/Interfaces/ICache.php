<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface ICache
{
    public function getInstance();

    public function set(string $variable, $value, $expiry = null);

    public function get(string $variable);

    public function delete(string $variable);
}
