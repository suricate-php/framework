<?php

declare(strict_types=1);

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
    const DEBUG_MODE = 'debug';
    const DEVELOPMENT_MODE = 'development';
    const PRELIVE_MODE = 'prelive';
    const PRODUCTION_MODE = 'production';

    protected $parametersList = [
        'root',
        'mode',
        'url',
        'locale',
        'path.app',
        'path.public',
        'path.base',
        'base_uri'
    ];

    /**
     * Check if app is in Debug mode
     * @return bool
     */
    public function isDebug(): bool
    {
        return self::DEBUG_MODE == $this->mode;
    }

    /**
     * Check if app is in Development mode
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return self::DEVELOPMENT_MODE == $this->mode;
    }

    /**
     * Check if app is in Prelive mode
     * @return bool
     */
    public function isPrelive(): bool
    {
        return self::PRELIVE_MODE == $this->mode;
    }

    /**
     * Check if app is in Production mode
     * @return bool
     */
    public function isProduction(): bool
    {
        return self::PRODUCTION_MODE == $this->mode;
    }

    /**
     * Check if app is in Maintenance mode
     * @return bool
     */
    public function inMaintenance(): bool
    {
        return is_file($this->getParameter('path.app') . '/config/maintenance');
    }

    public function abort($httpCode, $message = '', $headers = [])
    {
        throw new Exception\HttpException($httpCode, $message, null, $headers);
    }
}
