<?php

declare(strict_types=1);

namespace Suricate\Interfaces;

interface IMigration
{
    public function getName(): string;
    public function getSQL(): string;
}