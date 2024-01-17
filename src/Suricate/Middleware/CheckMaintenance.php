<?php

declare(strict_types=1);

namespace Suricate\Middleware;

use Suricate\Middleware;

class CheckMaintenance extends Middleware
{
    public function call(&$request, &$response)
    {
        if (app()->inMaintenance()) {
            app()->abort(503, 'Maintenance in progress');
        }
    }
}
