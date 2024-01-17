<?php

declare(strict_types=1);

namespace Suricate;

use Exception;
use Suricate\Interfaces\ISession;

/**
 * @property string $type Session type
 */
class Session extends Service implements ISession
{
    protected $parametersList = ['type'];
    private static $container;

    /**
     * Initialize session handler
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function init()
    {
        if (self::$container === null) {
            switch ($this->type) {
                case 'native':
                    self::$container = Suricate::SessionNative(true);
                    break;
                case 'none':
                    break;
                default:
                    throw new Exception("Unknown session type " . $this->type);
            }
        }
    }

    /**
     * Get instance of session driver used
     * @return Session driver instance
     */
    public function getInstance()
    {
        $this->init();
        return self::$container;
    }

    public function getId()
    {
        $this->init();
        return self::$container->getId();
    }
    public function regenerate()
    {
        $this->init();
        return self::$container->regenerate();
    }

    public function read($key)
    {
        $this->init();
        return self::$container->read($key);
    }

    public function write($key, $data)
    {
        $this->init();
        return self::$container->write($key, $data);
    }

    public function destroy($key)
    {
        $this->init();
        return self::$container->destroy($key);
    }

    public function close()
    {
        $this->init();
        return self::$container->close();
    }
}
