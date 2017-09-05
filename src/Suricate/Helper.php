<?php
use Suricate\Suricate;

// Debug
// 
if (!function_exists('_p')) {
    function _p()
    {
        echo '<pre>';
        foreach (func_get_args() as $var) {
            print_r($var);
            echo "\n";
        }
        echo '</pre>';
    }
}

if (!function_exists('_d')) {
    function _d()
    {
        echo '<pre>';
        foreach (func_get_args() as $var) {
            var_dump($var);
            echo "\n";
        }
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

if (!function_exists('flatten')) {
    function flatten(array $array)
    {
        $return = array();
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }
}

// Inspired from laravel helper
if (!function_exists('dataGet')) {
    function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        
        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof \ArrayAccess) {
                if (!isset($target[$segment])) {
                    return value($default);
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return value($default);
                }
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }
}

if (!function_exists('value')) {
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
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
    function snakeCase($str, $delimiter)
    {
        $replace = '$1' . $delimiter . '$2';

        return ctype_lower($str) ? $str : strtolower(preg_replace('/(.)([A-Z])/', $replace, $str));
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
        foreach ((array) $needles as $currentNeedle) {
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
        if (strlen($str) < $limit) {
            return $str;
        }

        $substr = substr($str, 0, $limit);
        $spacePos = strrpos($substr, ' ');
        if ($spacePos !== false) {
            return substr($substr, 0, $spacePos) . $end;
        } else {
            return $substr . $end;
        }
    }
}


if (!function_exists('slug')) {
    function slug($str, $isUtf8 = true)
    {
        if (class_exists('Transliterator')) {
            $translit = \Transliterator::create('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();');
            return preg_replace('/\s/', '-', $translit->transliterate($str));
        } else {
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
}

if (!function_exists('app')) {
    function app()
    {
        return Suricate::App();
    }
}

if (!function_exists('app_path')) {
    function app_path($str = '')
    {
        return Suricate::App()->getParameter('path.app')
            . ($str ? '/' . $str : $str);
    }
}

if (!function_exists('base_path')) {
    function base_path($str = '')
    {
        return Suricate::App()->getParameter('path.base')
            . ($str ? '/' . $str : $str);
    }
}

if (!function_exists('public_path')) {
    function public_path($str = '')
    {
        return Suricate::App()->getParameter('path.public')
            . ($str ? '/' . $str : $str);
    }
}

if (!function_exists('url')) {
    function url($str = '/')
    {
        return Suricate::App()->getParameter('url') . $str;
    }
}

if (!function_exists('getPostParam')) {
    function getPostParam($param, $defaultValue = null)
    {
        return Suricate::Request()->getPostParam($param, $defaultValue);
    }
}

if (!function_exists('getParam')) {
    function getParam($param, $defaultValue = null)
    {
        return Suricate::Request()->getParam($param, $defaultValue);
    }
}

if (!function_exists('i18n')) {
    function i18n()
    {
        return call_user_func_array(array(Suricate::I18n(), 'get'), func_get_args());
    }
}

if (!function_exists('generateUuid')) {
    // Via https://rogerstringer.com/2013/11/15/generate-uuids-php/
    function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

/**
 TODO : implements i18n
**/
if (!function_exists('niceTime')) {
    function niceTime($time)
    {
        $delta = time() - $time;
        if ($delta < 60) {
            return 'il y a moins d\'une minute.';
        } elseif ($delta < 120) {
            return 'il y a environ une minute.';
        } elseif ($delta < (45 * 60)) {
            return 'il y a ' . floor($delta / 60) . ' minutes.';
        } elseif ($delta < (90 * 60)) {
            return 'il y a environ une heure.';
        } elseif ($delta < (24 * 60 * 60)) {
            return 'il y a environ ' . floor($delta / 3600) . ' heures.';
        } elseif ($delta < (48 * 60 * 60)) {
            return 'hier';
        } elseif ($delta < 30 * 24 *3600) {
            return 'il y a ' . floor($delta / 86400) . ' jours.';
        } elseif ($delta < 365 * 24 * 3600) {
              return 'il y a ' . floor($delta / (24*3600*30)) . ' mois.';
        } else {
            $diff = floor($delta / (24*3600*365));

            if ($diff == 1) {
                return 'il y a plus d\'un an.';
            } else {
                return 'il y a plus de ' . $diff . ' ans.';
            }
        }
    }
}
