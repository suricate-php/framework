<?php
namespace Suricate\Interfaces;

interface IMiddleware
{
    public function call(&$response);
}
