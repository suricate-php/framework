<?php declare(strict_types=1);
namespace Suricate\Interfaces;

interface IMiddleware
{
    public function call(&$response);
}
