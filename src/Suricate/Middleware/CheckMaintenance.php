<?php
namespace Suricate\Middleware;

class checkMaintenance
{
    public function call(&$response)
    {
        if (app()->inMaintenance()) {
            app()->abort(503, 'Maintenance in progress');
        }
    }
}