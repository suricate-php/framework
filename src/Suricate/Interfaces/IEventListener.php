<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface IEventListener
{
    /**
     * Event listener handle method
     *
     * @return boolean returning false stop event propagation
     */
    public function handle(): bool;
}
