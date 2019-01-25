<?php declare(strict_types=1);
namespace Suricate;

class Registry
{
    protected static $data = [];
    protected static $context;

    public static function get($key, $defaultValue = null)
    {
        $data = &static::getFromContext();
        if (isset($data[$key])) {
            return $data[$key];
        }
        
        return $defaultValue;
    }

    public static function getProperty($key, $property, $defaultValue = null)
    {
        if ($object = static::get($key)) {
            if (isset($object->$property)) {
                return $object->$property;
            }
        }

        return $defaultValue;
    }

    public static function set($key, $value)
    {
        $data = &static::getFromContext();
        $data[$key] = $value;
    }

    public static function setContext($context)
    {
        static::$context = $context;
    }

    public static function getContext()
    {
        return static::$context;
    }

    public static function exists($key)
    {
        $data = &static::getFromContext();

        return isset($data[$key]);
    }

    private static function &getFromContext()
    {
        if (static::$context !== null) {
            if (!isset(static::$data[static::$context])) {
                static::$data[static::$context] = array();
            }
            return static::$data[static::$context];
        }

        return static::$data;
    }

    public static function clean()
    {
        $data = &static::getFromContext();
        $data = [];
    }
}
