<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

use Suricate\Request;

interface IMiddleware
{
    public function call(Request &$request, Request &$response);
}
