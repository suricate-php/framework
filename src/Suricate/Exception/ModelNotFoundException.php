<?php

declare(strict_types=1);

namespace Suricate\Exception;

class ModelNotFoundException extends \RuntimeException
{
    protected $model;

    public function setModel($model)
    {
        $this->model = $model;
        $this->message = 'Model ' . $model . ' not found';
        return $this;
    }
}
