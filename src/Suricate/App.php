<?php declare(strict_types=1);
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

    protected $parametersList   = [
        'root',
        'mode',
        'url',
        'locale',
        'path.app',
        'path.public',
        'path.base',
        'base_uri',
    ];

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

    public function inMaintenance()
    {
        return is_file($this->getParameter('path.app') . '/config/maintenance');
    }

    public function abort($httpCode, $message = '', $headers = [])
    {
        throw new Exception\HttpException($httpCode, $message, null, $headers);
    }
}
