<?php

declare(strict_types=1);

namespace Suricate;

class AutoLoader
{
    public static function autoload($class)
    {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . $class . '.php';

        if (is_file($filename)) {
            include $filename;
        }
    }

    public static function loadClass($class)
    {
        if (!class_exists($class, false) && !interface_exists($class, false)) {
            self::autoload($class);
        }
    }

    public static function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(__NAMESPACE__ . '\Autoloader::loadClass');
    }

    public static function unRegister()
    {
        spl_autoload_unregister(__NAMESPACE__ . '\Autoloader::loadClass');
    }
}
