<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface IService
{
    public function configure($parameters = []);

    public function getParameter($parameter);
}
