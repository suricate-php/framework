<?php
namespace Fwk;

use ErrorException;

class App extends Service
{
    const DEVELOPMENT_MODE  = 'development';
    const PRELIVE_MODE      = 'prelive';
    const PRODUCTION_MODE   = 'production';

    protected $parametersList   = array(
        'root',
        'mode',
        'url',
        'locale'
        );

    public function isDevelopment()
    {
        return self::DEVELOPMENT_MODE == $this->mode;
    }

    public function isPrelive()
    {
        return self::PRELIVE_MODE == $this->mode;
    }

    public function isProduction()
    {
        return self::PRODUCTION_MODE == $this->mode;
    }
}
