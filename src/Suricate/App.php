<?php
namespace Suricate;

/**
 * App extension for Suricate
 *
 * @package Suricate
 * @author  Mathieu LESNIAK <mathieu@lesniak.fr>
 *
 * @property string $root 
 * @property string $mode 
 * @property string $url 
 * @property string $locale 
 */


class App extends Service
{
    const DEBUG_MODE        = 'debug';
    const DEVELOPMENT_MODE  = 'development';
    const PRELIVE_MODE      = 'prelive';
    const PRODUCTION_MODE   = 'production';

    protected $parametersList   = array(
        'root',
        'mode',
        'url',
        'locale',
        'path.app',
        'path.public',
        'path.base',
        );

    public function isDebug()
    {
        return self::DEBUG_MODE == $this->mode;
    }

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

    public function abort($httpCode, $message = '', $headers = array())
    {
        throw new Exception\HttpException($httpCode, $message, null, $headers);
    }
}
