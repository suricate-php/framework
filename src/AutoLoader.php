<?php
namespace Fwk;

class AutoLoader
{
    public static function autoload($class)
    {
        $class      = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $filename   = dirname(__DIR__) . DIRECTORY_SEPARATOR . $class . '.php';

        if (is_file($filename)) {
            include $filename;
        } else {
            //throw new \Exception("Object '$class' not found", 2);
        }

        /*
        $class              = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $convertedClassName = preg_replace('|([a-z])([A-Z])|', '$1_$2', $class);

        $trimmedClass = ltrim($class, DIRECTORY_SEPARATOR);

        if (($sepPos = strpos($trimmedClass, DIRECTORY_SEPARATOR)) !== false) {
            $classRootNameSpace = substr($trimmedClass, 0, $sepPos);
            $convertedClassName = substr(
                $trimmedClass,
                strrpos($trimmedClass, DIRECTORY_SEPARATOR) + 1
            );
        } else {
            $classRootNameSpace = '';
        }
        $splitted           = explode('_', $convertedClassName);
        $classType          = strtolower(array_pop($splitted));
        $classNameSpace     = strtolower(array_shift($splitted));
        $dir                = '';
        $thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__) . "<br/>";
        echo $thisClass;

        if (isset(self::$dirMap[$classType])) {
            $dir        = self::$dirMap[$classType];
            $filename   = self::$root . '/' . $dir . '/' . str_replace(ucFirst($classType), '', $class) . '.php';
        } elseif (isset(self::$dirMap[$classNameSpace])) {
            $dir        = self::$dirMap[$classNameSpace];
            $filename   = self::$root . '/' . $dir . '/' . $class . '.php';
        } elseif (isset(self::$dirMap[$classRootNameSpace])) {
            $dir        = self::$dirMap[$classRootNameSpace];
            $filename   = self::$root . '/' . $dir . '/' . str_replace(ucFirst($classRootNameSpace), '', $class) . '.php';
        } elseif (isset(self::$dirMap['default'])) {
            $dir        = self::$dirMap['default'];
            $filename   = self::$root . '/' . $dir . '/' . $class . '.php';
        }

        if ($dir != '') {
            if (is_file($filename)) {
                include $filename;
            } else {
                throw new \Exception("Object '$class' not found", 2);
            }
        } else {
            throw new \Exception("Unknown object type / No directory found", 1);
        }
        */
       
    }

    public static function loadClass($class)
    {
        if (!class_exists($class, false) && !interface_exists($class, false)) {
            self::autoload($class);
            /*
            if (!class_exists($class, false) && !interface_exists($class, false)) {
               throw new \Exception("File loaded, but '$class' does not exist");
            }
            */
        }
    }

    public static function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(__NAMESPACE__ .'\Autoloader::loadClass');
    }

    public static function unRegister()
    {
        spl_autoload_unregister(__NAMESPACE__ .'\Autoloader::loadClass');
    }
}
