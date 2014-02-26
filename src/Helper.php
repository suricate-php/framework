<?php
use Fwk\Fwk;

// Debug
// 
if (!function_exists('_p')) {
    function _p($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

if (!function_exists('_d')) {
    function _d($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

if (!function_exists('e')) {
    function e($str)
    {
        return htmlentities($str, ENT_COMPAT, 'UTF-8');
    }
}

// Arrays

if (!function_exists('head')) {
    function head($arr)
    {
        return reset($arr);
    }
}

if (!function_exists('last')) {
    function last($arr)
    {
        return end($arr);
    }
}

// Classes

if (!function_exists('classBasename')) {
    function classBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('with')) {
    function with($class)
    {
        return $class;
    }
}

// Strings

if (!function_exists('camelCase')) {
    function camelCase($str)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }
}

if (!function_exists('snakeCase')) {
    function snakeCase($str)
    {

    }
}

if (!function_exists('contains')) {
    function contains($haystack, $needles)
    {
        foreach ((array) $needles as $currentNeedle) {
            if (strpos($haystack, $currentNeedle) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('startsWith')) {
    function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $currentNeedle)
        {
            if (strpos($haystack, $currentNeedle) === 0) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('endsWith')) {
    function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $currentNeedle) {
            if ($currentNeedle == substr($haystack, strlen($haystack) - strlen($currentNeedle))) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('wordLimit')) {
    function wordLimit($str, $limit = 100, $end = '...')
    {
        if (strlen($str) < 100) {
            return $str;
        }

        $substr = substr($str, 0, 100);
        $spacePos = strrpos($substr, ' ');
        if ($spacePos !== false) {
            return substr($substr, 0, $spacePos);
        } else {
            return $substr;
        }
    }
}


if (!function_exists('slug')) {
    function slug($str, $isUtf8 = true)
    {
        if (!$isUtf8) {
           $str = strtr(
                $str,
                utf8_decode("ÀÁÂÃÄÅàáâãäåÇçÒÓÔÕÖØòóôõöøÈÉÊËèéêëÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ"),
                "AAAAAAaaaaaaCcOOOOOOooooooEEEEeeeeIIIIiiiiUUUUuuuuyNn"
            );
       } else {
            $str = strtr(
                utf8_decode($str),
                utf8_decode("ÀÁÂÃÄÅàáâãäåÇçÒÓÔÕÖØòóôõöøÈÉÊËèéêëÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ"),
                "AAAAAAaaaaaaCcOOOOOOooooooEEEEeeeeIIIIiiiiUUUUuuuuyNn"
            );
        }

        $str = preg_replace('/[^a-z0-9_-\s]/', '', strtolower($str));
        $str = preg_replace('/[\s]+/', ' ', trim($str));
        $str = str_replace(' ', '-', $str);

        return $str;
    }
}

if (!function_exists('app')) {
    function app()
    {
        return Fwk::App();
    }
}

if (!function_exists('app_path')) {
    function app_path($str = '')
    {
        return Fwk::App()->getParameter('url') . '/'
            . Fwk::App()->getParameter('root') . ($str ? '/' . $str : $str);
    }
}

if (!function_exists('url')) {
    function url($str = '/')
    {
        return Fwk::App()->getParameter('url') . $str;
    }
}

if (!function_exists('getPostParam')) {
    function getPostParam($param, $defaultValue = null)
    {
        return Fwk::Request()->getPostParam($param, $defaultValue);
    }
}

if (!function_exists('getParam')) {
    function getParam($param, $defaultValue = null)
    {
        return Fwk::Request()->getParam($param, $defaultValue);
    }
}

if (!function_exists('i18')) {
    function i18n() {
        return call_user_func_array(array(Fwk::I18n(), 'get'), func_get_args());
    }
}