<?php

declare(strict_types=1);

namespace Suricate;

use InvalidArgumentException;
use BadMethodCallException;
use Exception;

/**
 * Validator
 * Inspired from Kieron Wilson PHP Validator
 *
 * @method Validator true(string $errorMessage)
 * @method Validator false(string $errorMessage)
 * @method Validator equalTo(mixed $toTest, string $errorMessage)
 * @method Validator identicalTo(mixed $toTest, string $errorMessage)
 * @method Validator lessThan(mixed $toTest, string $errorMessage)
 * @method Validator lessThanOrEqual(mixed $toTest, string $errorMessage)
 * @method Validator greaterThan(mixed $toTest, string $errorMessage)
 * @method Validator greaterThanOrEqual(mixed $toTest, string $errorMessage)
 * @method Validator blank(string $errorMessage)
 * @method Validator null(string $errorMessage)
 * @method Validator type(string $testType, string $errorMessage)
 * &method Validator email(string $errorMessage)
 * &method Validator url(string $errorMessage)
 * &method Validator ip(string $errorMessage)
 * &method Validator regexp(string $errorMessage)
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   Mathieu LESNIAK
 * @package     Suricate
 */
class Validator
{
    private $errors = [];
    private $checks = [];
    private $datas;
    private $value;
    private $index;
    private $stop = false;

    public function __construct($input)
    {
        $this->datas = $input;
        $this->value = $input;
        $this->createChecks();
    }

    /**
     * Initialize checks
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createChecks()
    {
        $this->checks['equalTo'] = function ($value, $compare) {
            return $value == $compare;
        };

        $this->checks['identicalTo'] = function ($value, $compare) {
            return $value === $compare;
        };

        $this->checks['lessThan'] = function ($value, $compare) {
            return $value < $compare;
        };

        $this->checks['lessThanOrEqual'] = function ($value, $compare) {
            return $value <= $compare;
        };

        $this->checks['greaterThan'] = function ($value, $compare) {
            return $value > $compare;
        };

        $this->checks['greaterThanOrEqual'] = function ($value, $compare) {
            return $value >= $compare;
        };

        $this->checks['blank'] = function ($value) {
            return $value == '';
        };

        $this->checks['null'] = function ($value) {
            return is_null($value);
        };

        $this->checks['true'] = function ($value) {
            return $value === true;
        };

        $this->checks['false'] = function ($value) {
            return !($value === true);
        };

        $this->checks['type'] = function ($value, $type) {
            switch ($type) {
                case 'array':
                    return is_array($value);
                case 'bool':
                    return is_bool($value);
                case 'callable':
                    return is_callable($value);
                case 'float':
                    return is_float($value);
                case 'int':
                    return is_int($value);
                case 'numeric':
                    return is_numeric($value);
                case 'object':
                    return is_object($value);
                case 'resource':
                    return is_resource($value);
                case 'scalar':
                    return is_scalar($value);
                case 'string':
                    return is_string($value);
                default:
                    throw new InvalidArgumentException(
                        'Unknown type to check ' . $type
                    );
            }
        };

        $this->checks['email'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        };

        $this->checks['url'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        };

        $this->checks['ip'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        };

        $this->checks['regexp'] = function ($value, $regexp) {
            return filter_var($value, FILTER_VALIDATE_REGEXP, [
                "options" => ['regexp' => $regexp]
            ]) !== false;
        };

        $this->checks['longerThan'] = function ($value, $length) {
            return strlen($value) > $length;
        };

        $this->checks['longerThanOrEqual'] = function ($value, $length) {
            return strlen($value) >= $length;
        };

        $this->checks['shorterThan'] = function ($value, $length) {
            return strlen($value) < $length;
        };

        $this->checks['shortThanOrEqual'] = function ($value, $length) {
            return strlen($value) <= $length;
        };

        $this->checks['contains'] = function ($value, $toFind) {
            return strpos($value, $toFind) !== false;
        };

        $this->checks['alnum'] = function ($value) {
            return ctype_alnum($value);
        };

        $this->checks['alpha'] = function ($value) {
            return ctype_alpha($value);
        };

        $this->checks['digit'] = function ($value) {
            return ctype_digit($value);
        };

        $this->checks['lower'] = function ($value) {
            return ctype_lower($value);
        };

        $this->checks['upper'] = function ($value) {
            return ctype_upper($value);
        };

        $this->checks['space'] = function ($value) {
            return ctype_space($value);
        };
    }

    public function validate($index = null)
    {
        if ($index === null) {
            $this->value = $this->datas;
            $this->index = null;
            return $this;
        }

        if (is_object($this->datas)) {
            try {
                $this->value = $this->datas->$index;
            } catch (Exception $e) {
                throw new InvalidArgumentException(
                    'class property "' . $index . '" does not exists'
                );
            }
            $this->index = $index;

            return $this;
        }

        if (array_key_exists($index, $this->datas)) {
            $this->value = $this->datas[$index];
            $this->index = $index;

            return $this;
        }

        throw new InvalidArgumentException(
            'Index / Property "' . $index . '" does not exists'
        );
    }

    public function callValidate()
    {
        $args = func_get_args();
        if (count($args) < 1) {
            throw new InvalidArgumentException('bad number of arguments');
        }

        $method = array_shift($args);
        if (is_callable($method)) {
            $this->index = null;
            $this->value = call_user_func_array($method, $args);

            return $this;
        }

        throw new InvalidArgumentException('Bad method');
    }

    public function __call($method, $parameters)
    {
        if (!$this->stop) {
            // Stop on error, ignore others tests if fails
            if (substr($method, 0, 4) == 'stop') {
                $stopOnError = true;
                $method = lcFirst(substr($method, 4));
            } else {
                $stopOnError = false;
            }

            // Negation check
            if (substr(strtolower($method), 0, 3) == 'not') {
                $negation = true;
                $method = lcFirst(substr($method, 3));
            } else {
                $negation = false;
            }

            if (!isset($this->checks[$method])) {
                throw new BadMethodCallException('Unknown check ' . $method);
            } else {
                $validator = $this->checks[$method];
            }

            $errorMessage = array_pop($parameters);

            array_unshift($parameters, $this->value);

            $validation = (bool) (call_user_func_array(
                $validator,
                $parameters
            ) ^ $negation);
            if (!$validation) {
                if ($stopOnError) {
                    $this->stop = true;
                }
                if ($this->index === null) {
                    $this->errors[] = $errorMessage;
                } else {
                    $this->errors[$this->index][] = $errorMessage;
                }
            }
        }

        return $this;
    }

    public function getErrors($index = null)
    {
        if ($index === null) {
            return $this->errors;
        } else {
            return isset($this->errors[$index]) ? $this->errors[$index] : [];
        }
    }

    public function pass()
    {
        return count($this->errors) == 0;
    }

    public function fails()
    {
        return !$this->pass();
    }
}
