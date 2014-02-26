<?php
namespace Suricate\Interfaces;

interface IService
{
    public function configure($parameters = array());

    public function getParameter($parameter);
}
