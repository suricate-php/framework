<?php declare(strict_types=1);
namespace Suricate\Middleware;

class CheckMaintenance extends \Suricate\Middleware
{
    public function call(&$response)
    {
        if (app()->inMaintenance()) {
            app()->abort(503, 'Maintenance in progress');
        }
    }
}
