<?php
namespace Fwk\Interfaces;

interface ICache
{
    public function getInstance();

    public function set($variable, $value, $expiry = null);

    public function get($variable);
}
