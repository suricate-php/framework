<?php
namespace Fwk;

use Exception;

class Session extends Service implements Interfaces\ISession
{
    protected $parametersList = array('type');
    private static $container;

    
    protected function init()
    {
        if (static::$container === null) {
            switch ($this->type) {
                case 'native':
                    static::$container = Fwk::SessionNative(true);
                    break;
                case 'cookie':
                    static::$container = Fwk::SessionCookie(true);
                    break;
                case 'memcache':
                    static::$container = Fwk::SessionMemcache(true);
                    break;
                default:
                    throw new Exception("Unknown session type " . $this->type);
                    break;
            }
        }
    }
    
    /**
     * Get instance of session driver used
     * @return Sessiondriver instance 
     */
    public function getInstance()
    {
        $this->init();
        return static::$container;
    }

    public function read($key)
    {
        $this->init();
        return static::$container->read($key);
    }

    public function write($key, $data)
    {
        $this->init();
        return static::$container->write($key, $data);
    }

    public function destroy($key)
    {
        $this->init();
        return static::$container->destroy($key);
    }

    public function close()
    {
        $this->init();
        return static::$container->close();
    }


}